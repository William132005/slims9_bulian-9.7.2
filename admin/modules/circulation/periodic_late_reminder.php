<?php
/**
 * Daftar Keterlambatan & Tagihan Akhir Semester (Gabungan)
 *
 * Menggabungkan dua halaman:
 *   - Daftar Keterlambatan (overdued_list) → Tab 1
 *   - Tagihan Keterlambatan Akhir Semester  → Tab 2
 */

// key to authenticate
define('INDEX_AUTH', '1');

// main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB . 'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-circulation');
// start the session
require SB . 'admin/default/session.inc.php';
require SB . 'admin/default/session_check.inc.php';
require SIMBIO . 'simbio_UTILS/simbio_date.inc.php';
require MDLBS . 'membership/member_base_lib.inc.php';
require MDLBS . 'circulation/circulation_base_lib.inc.php';

// privileges checking
$can_read  = utility::havePrivilege('circulation', 'r');
$can_write = utility::havePrivilege('circulation', 'w');

if (!$can_read) {
    die('<div class="errorBox">' . __('You don\'t have enough privileges to access this area!') . '</div>');
}

require SIMBIO . 'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO . 'simbio_GUI/form_maker/simbio_form_element.inc.php';
require SIMBIO . 'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO . 'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require MDLBS . 'reporting/report_dbgrid.inc.php';

$page_title   = 'Daftar Keterlambatan & Tagihan Akhir Semester';
$num_recs_show = 20;

// ==========================================================
// HANDLE POST: Generate Tagihan Akhir Semester (AJAX)
// ==========================================================
if (isset($_POST['generateBill'])) {
    if (!$can_write) {
        die(json_encode(['status' => false, 'message' => __('You don\'t have enough privileges!')]));
    }

    $startDate   = $dbs->real_escape_string($_POST['startDate'] ?? '');
    $untilDate   = $dbs->real_escape_string($_POST['untilDate'] ?? '');

    $criteria = "due_date < '" . date('Y-m-d') . "' AND is_return = 0 AND is_billed = 0";
    if ($startDate && $untilDate) {
        $criteria .= " AND loan_date BETWEEN '$startDate' AND '$untilDate'";
    }

    $dbs->query("UPDATE loan SET is_billed = 1, billed_date = NOW() WHERE $criteria");
    $dbs->query("UPDATE loan_history SET is_billed = 1, billed_date = NOW() WHERE $criteria");

    echo json_encode(['status' => true, 'message' => 'Berhasil! Tagihan telah di-generate untuk data yang sesuai.']);
    exit;
}

// ==========================================================
// Tentukan mode tampilan berdasarkan GET reportView
// ==========================================================
$reportView = $_GET['reportView'] ?? false;

// ----------------------------------------------------------
// IFRAME VIEW: Tab 1 — Daftar Keterlambatan (per anggota)
// ----------------------------------------------------------
if ($reportView === 'overdued') {
    ob_start();

    $table_spec = 'member AS m
        LEFT JOIN loan AS l ON m.member_id = l.member_id';

    $reportgrid = new report_datagrid();
    $reportgrid->setSQLColumn('m.member_id AS \'' . __('Member ID') . '\'');
    $reportgrid->setSQLorder('MAX(l.due_date) DESC');
    $reportgrid->sql_group_by = 'm.member_id';

    $overdue_criteria = ' (l.is_lent=1 AND l.is_return=0 AND TO_DAYS(due_date) < TO_DAYS(\'' . date('Y-m-d') . '\')) ';
    $date_criteria = '';

    // Filter by member ID / Name
    if (!empty($_GET['id_name'])) {
        $keyword = $dbs->real_escape_string(trim($_GET['id_name']));
        $words   = explode(' ', $keyword);
        if (count($words) > 1) {
            $concat_sql = ' (';
            foreach ($words as $word) {
                $concat_sql .= " (m.member_id LIKE '%$word%' OR m.member_name LIKE '%$word%') AND";
            }
            $concat_sql = substr_replace($concat_sql, '', -3) . ') ';
            $overdue_criteria .= ' AND ' . $concat_sql;
        } else {
            $overdue_criteria .= " AND (m.member_id LIKE '%$keyword%' OR m.member_name LIKE '%$keyword%')";
        }
    }

    // Filter by loan date range
    if (!empty($_GET['startDate']) && !empty($_GET['untilDate'])) {
        $date_criteria = sprintf(
            ' AND (TO_DAYS(l.loan_date) BETWEEN TO_DAYS(\'%s\') AND TO_DAYS(\'%s\'))',
            $dbs->real_escape_string($_GET['startDate']),
            $dbs->real_escape_string($_GET['untilDate'])
        );
        $overdue_criteria .= $date_criteria;
    }

    if (!empty($_GET['recsEachPage'])) {
        $r = (int)$_GET['recsEachPage'];
        $num_recs_show = ($r >= 5 && $r <= 200) ? $r : $num_recs_show;
    }

    $reportgrid->setSQLCriteria($overdue_criteria);
    $reportgrid->table_attr        = 'align="center" class="dataListPrinted" cellpadding="5" cellspacing="0"';
    $reportgrid->table_header_attr = 'class="dataListHeaderPrinted"';
    $reportgrid->column_width      = ['1' => '80%'];

    function showOverduedList($obj_db, $array_data)
    {
        global $date_criteria, $sysconf;

        $circulation = new circulation($obj_db, $array_data[0]);
        $circulation->ignore_holidays_fine_calc = $sysconf['ignore_holidays_fine_calc'];
        $circulation->holiday_dayname           = $_SESSION['holiday_dayname'];
        $circulation->holiday_date              = $_SESSION['holiday_date'];

        $member_q = $obj_db->query(
            'SELECT m.member_name, m.member_email, m.member_phone, m.member_mail_address, mmt.fine_each_day
             FROM member m
             LEFT JOIN mst_member_type mmt ON m.member_type_id = mmt.member_type_id
             WHERE m.member_id=\'' . $array_data[0] . '\''
        );
        $member_d = $member_q->fetch_row();
        $member_name         = $member_d[0];
        $member_mail_address = $member_d[3];
        unset($member_q);

        $ovd_title_q = $obj_db->query(
            'SELECT l.loan_id, l.item_code, i.price, i.price_currency,
              b.title, l.loan_date, l.due_date,
              (TO_DAYS(DATE(NOW()))-TO_DAYS(due_date)) AS \'Overdue Days\',
              mlr.fine_each_day
             FROM loan AS l
               LEFT JOIN item AS i     ON l.item_code=i.item_code
               LEFT JOIN biblio AS b   ON i.biblio_id=b.biblio_id
               LEFT JOIN mst_loan_rules mlr ON l.loan_rules_id = mlr.loan_rules_id
             WHERE (l.is_lent=1 AND l.is_return=0
               AND TO_DAYS(due_date) < TO_DAYS(\'' . date('Y-m-d') . '\'))
               AND l.member_id=\'' . $array_data[0] . '\''
            . (!empty($date_criteria) ? $date_criteria : '')
        );

        $_buf = '<div style="font-weight:bold;color:#000;font-size:10pt;margin-bottom:3px;">'
              . htmlspecialchars($member_name) . ' (' . $array_data[0] . ')</div>';

        if (!empty($member_d[3])) {
            $_buf .= '<div style="color:#555;font-size:9pt;margin-bottom:3px;">'
                   . htmlspecialchars($member_mail_address) . '</div>';
        }

        if (!empty($member_d[1])) {
            $_buf .= '<div style="font-size:9pt;margin-bottom:3px;">'
                   . '<div id="' . $array_data[0] . 'emailStatus"></div>'
                   . __('E-mail') . ': <a href="mailto:' . htmlspecialchars($member_d[1]) . '">'
                   . htmlspecialchars($member_d[1]) . '</a>'
                   . ' &mdash; <a class="usingAJAX btn btn-xs btn-outline-primary"'
                   . ' href="' . MWB . 'membership/overdue_mail.php"'
                   . ' postdata="memberID=' . $array_data[0] . '"'
                   . ' loadcontainer="' . $array_data[0] . 'emailStatus">'
                   . '<i class="fa fa-paper-plane-o"></i>&nbsp;' . __('Send Notification e-mail') . '</a><br/>'
                   . __('Phone Number') . ': ' . htmlspecialchars($member_d[2]) . '</div>';
        }

        $_buf .= '<table width="100%" cellspacing="0" style="margin-top:5px;">';

        while ($row = $ovd_title_q->fetch_assoc()) {
            $overdue_days = $circulation->countOverdueValue($row['loan_id'], date('Y-m-d'))['days'];
            $overdue_days = !is_numeric($overdue_days) ? 0 : (int)$overdue_days;
            $fines = currency($overdue_days * $member_d[4]);
            if (!is_null($row['fine_each_day'])) {
                $fines = currency($overdue_days * $row['fine_each_day']);
            }
            $overdue_days_fmt = number_format($overdue_days, 0, ',', '.');

            $_buf .= '<tr>';
            $_buf .= '<td valign="top" width="10%">' . htmlspecialchars($row['item_code']) . '</td>';
            $_buf .= '<td valign="top" width="40%">' . htmlspecialchars($row['title'])
                   . '<div style="font-size:9pt;">' . __('Book Price') . ': ' . currency($row['price']) . '</div></td>';
            $_buf .= '<td width="20%">'
                   . '<div>' . __('Overdue') . ': <b>' . $overdue_days_fmt . '</b> ' . __('day(s)') . '</div>'
                   . '<div>' . __('Fines') . ': ' . $fines . '</div></td>';
            $_buf .= '<td width="30%">'
                   . __('Loan Date') . ': ' . $row['loan_date']
                   . ' &nbsp; ' . __('Due Date') . ': ' . $row['due_date'] . '</td>';
            $_buf .= '</tr>';
        }

        $_buf .= '</table>';
        return $_buf;
    }

    $reportgrid->modifyColumnContent(0, 'callback{showOverduedList}');
    echo $reportgrid->createDataGrid($dbs, $table_spec, $num_recs_show);

    ?>
    <script type="text/javascript" src="<?php echo JWB . 'jquery.js'; ?>"></script>
    <script type="text/javascript" src="<?php echo JWB . 'updater.js'; ?>"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            parent.$('#pagingBoxOverdued').html('<?php echo str_replace(["\n", "\r", "\t"], '', $reportgrid->paging_set); ?>');
            $('a.usingAJAX').click(function (evt) {
                evt.preventDefault();
                var anchor        = $(this);
                var url           = anchor.attr('href');
                var postData      = anchor.attr('postdata');
                var loadContainer = anchor.attr('loadcontainer');
                if (loadContainer) {
                    var container = jQuery('#' + loadContainer);
                    container.html('<div class="alert alert-info"><?= __('Please wait') ?>...</div>');
                    if (postData) {
                        container.simbioAJAX(url, { method: 'post', addData: postData });
                    } else {
                        container.simbioAJAX(url, { addData: { ajaxload: 1 } });
                    }
                }
            });
        });
    </script>
    <?php
    $content = ob_get_clean();
    require SB . '/admin/' . $sysconf['admin_template']['dir'] . '/printed_page_tpl.php';

// ----------------------------------------------------------
// IFRAME VIEW: Tab 2 — Tagihan Akhir Semester (tabel flat)
// ----------------------------------------------------------
} elseif ($reportView === 'billing') {
    ob_start();

    $table_spec = 'loan AS l
        LEFT JOIN member AS m  ON l.member_id  = m.member_id
        LEFT JOIN item   AS i  ON l.item_code  = i.item_code
        LEFT JOIN biblio AS b  ON i.biblio_id  = b.biblio_id';

    $reportgrid = new report_datagrid();
    $reportgrid->setSQLColumn(
        'm.member_name AS \'' . __('Nama Anggota') . '\'',
        'b.title       AS \'' . __('Judul Buku') . '\'',
        'l.loan_date   AS \'' . __('Tgl Pinjam') . '\'',
        'l.due_date    AS \'' . __('Jatuh Tempo') . '\'',
        'TO_DAYS(DATE(NOW())) - TO_DAYS(l.due_date) AS \'' . __('Hari Terlambat') . '\'',
        'IF(l.is_return=1, \'' . __('Kembali') . '\', \'' . __('Pinjam') . '\') AS \'' . __('Status') . '\'',
        'l.is_billed'
    );

    $criteria = "l.due_date < '" . date('Y-m-d') . "' AND l.is_return = 0";
    if (!empty($_GET['startDate']) && !empty($_GET['untilDate'])) {
        $criteria .= " AND l.loan_date BETWEEN '"
            . $dbs->real_escape_string($_GET['startDate']) . "' AND '"
            . $dbs->real_escape_string($_GET['untilDate']) . "'";
    }

    $reportgrid->setSQLCriteria($criteria);
    $reportgrid->setSQLorder('l.due_date ASC');

    function showBillingStatus($obj_db, $array_data)
    {
        if ($array_data[6] == 1) {
            return '<span style="background:#28a745;color:#fff;padding:2px 7px;border-radius:3px;font-size:11px;">'
                 . __('Sudah Ditagih') . '</span>';
        }
        return '<span style="background:#dc3545;color:#fff;padding:2px 7px;border-radius:3px;font-size:11px;">'
             . __('Belum Ditagih') . '</span>';
    }

    $reportgrid->modifyColumnContent(6, 'callback{showBillingStatus}');
    $reportgrid->table_attr        = 'align="center" class="dataListPrinted" cellpadding="5" cellspacing="0"';
    $reportgrid->table_header_attr = 'class="dataListHeaderPrinted"';

    echo $reportgrid->createDataGrid($dbs, $table_spec, $num_recs_show);

    ?>
    <script type="text/javascript">
        parent.$('#pagingBoxBilling').html('<?php echo str_replace(["\n", "\r", "\t"], '', $reportgrid->paging_set); ?>');
    </script>
    <?php
    $content = ob_get_clean();
    require SB . '/admin/' . $sysconf['admin_template']['dir'] . '/printed_page_tpl.php';

// ----------------------------------------------------------
// MAIN VIEW: Halaman utama dengan 2 Tab
// ----------------------------------------------------------
} else {
    ?>
    <style>
        /* ─── Tab navigation (pakai button bukan <a> agar tidak diintercept oleh SLiMS admin JS) ─── */
        #overdueTabNav { border-bottom: 2px solid #ddd; margin-bottom: 0; padding: 0; list-style: none; }
        #overdueTabNav > li { display: inline-block; margin-bottom: -2px; }
        #overdueTabNav > li > button.overdue-tab-btn {
            font-size: 13px; font-weight: 600; color: #555;
            background: #f5f5f5;
            border: 1px solid #ddd; border-bottom: none;
            border-radius: 6px 6px 0 0;
            padding: 9px 18px; cursor: pointer;
            transition: background .2s, color .2s;
        }
        #overdueTabNav > li.active > button.overdue-tab-btn {
            color: #1a73e8; background: #fff; border-color: #ddd;
        }
        #overdueTabNav > li > button.overdue-tab-btn:hover { background: #eaeaea; }
        #overdueTabNav > li > button.overdue-tab-btn .fa { margin-right: 5px; }

        /* ─── Tab panels ─── */
        .overdue-tab-content { background: #fff; border: 1px solid #ddd; border-top: none;
            padding: 15px; border-radius: 0 0 6px 6px; }

        /* ─── Filter boxes ─── */
        .overdue-filter-box { background: #f9f9f9; border: 1px solid #e5e5e5;
            border-radius: 5px; padding: 14px 16px; margin-bottom: 12px; }

        /* ─── Status message ─── */
        #statusMessageBilling { margin: 8px 0; }

        /* ─── Paging box ─── */
        .overdue-paging { padding: 4px 2px; min-height: 20px; }

        /* ─── iframes ─── */
        .overdue-iframe { width: 100%; height: 600px; border: none; margin-top: 6px; }
    </style>

    <div class="per_title">
        <h2><?php echo __('Daftar Keterlambatan & Tagihan Akhir Semester'); ?></h2>
    </div>

    <!-- ═══ Tab Navigation ═══ -->
    <ul class="nav" id="overdueTabNav">
        <li class="active">
            <button type="button" class="overdue-tab-btn" data-target="#tab-overdued">
                <i class="fa fa-list-alt"></i> <?php echo __('Daftar Keterlambatan'); ?>
            </button>
        </li>
        <li>
            <button type="button" class="overdue-tab-btn" data-target="#tab-billing">
                <i class="fa fa-file-text-o"></i> <?php echo __('Tagihan Akhir Semester'); ?>
            </button>
        </li>
    </ul>

    <div class="tab-content overdue-tab-content">

        <!-- ══ TAB 1: Daftar Keterlambatan ══ -->
        <div class="tab-pane active" id="tab-overdued">
            <div class="overdue-filter-box">
                <form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>"
                      target="iframeOverdued" id="formOverdued">
                    <input type="hidden" name="reportView" value="overdued"/>

                    <div class="divRow" style="margin-bottom:8px;">
                        <div class="divRowLabel">
                            <?php echo __('Member ID') . '/' . __('Member Name'); ?>
                        </div>
                        <div class="divRowContent">
                            <?php echo simbio_form_element::textField(
                                'text', 'id_name', '',
                                'class="form-control" style="width:50%;" placeholder="Cari ID atau Nama Anggota..."'
                            ); ?>
                        </div>
                    </div>

                    <div class="divRow" style="margin-bottom:8px;">
                        <div class="divRowLabel"><?php echo __('Tanggal Pinjam'); ?></div>
                        <div class="divRowContent">
                            <input type="text" name="startDate" value="2000-01-01"
                                   class="form-control" style="width:140px;display:inline-block;">
                            <span style="margin:0 6px;"><?= __('to') ?></span>
                            <input type="text" name="untilDate" value="<?= date('Y-m-d') ?>"
                                   class="form-control" style="width:140px;display:inline-block;">
                        </div>
                    </div>

                    <div style="padding-top:8px;">
                        <button type="submit" class="btn btn-primary" name="applyFilter">
                            <i class="fa fa-search"></i> <?php echo __('Terapkan Filter'); ?>
                        </button>
                    </div>
                </form>
            </div>

            <div class="overdue-paging dataListHeader">
                <span id="pagingBoxOverdued"></span>
            </div>
            <iframe name="iframeOverdued" id="iframeOverdued" class="overdue-iframe"
                    src="<?php echo $_SERVER['PHP_SELF'] . '?reportView=overdued'; ?>">
            </iframe>
        </div><!-- /#tab-overdued -->

        <!-- ══ TAB 2: Tagihan Akhir Semester ══ -->
        <div class="tab-pane" id="tab-billing">
            <div class="overdue-filter-box">
                <form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>"
                      target="iframeBilling" id="formBilling">
                    <input type="hidden" name="reportView" value="billing"/>

                    <div class="divRow" style="margin-bottom:8px;">
                        <div class="divRowLabel"><?php echo __('Tanggal Pinjam Dari'); ?></div>
                        <div class="divRowContent">
                            <input type="text" name="startDate" id="billingStartDate"
                                   value="<?= date('Y') ?>-01-01"
                                   class="form-control" style="width:140px;display:inline-block;">
                            <span style="margin:0 6px;"><?= __('to') ?></span>
                            <input type="text" name="untilDate" id="billingUntilDate"
                                   value="<?= date('Y-m-d') ?>"
                                   class="form-control" style="width:140px;display:inline-block;">
                        </div>
                    </div>


                        <button type="submit" class="btn btn-primary" name="applyFilter">
                            <i class="fa fa-search"></i> <?php echo __('Tampilkan Daftar'); ?>
                        </button>
                        <?php if ($can_write): ?>
                        <button type="button" id="btnGenerate" class="btn btn-success" style="margin-left:6px;">
                            <i class="fa fa-money"></i> <?php echo __('Generate Tagihan Akhir Semester'); ?>
                        </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div id="statusMessageBilling"></div>

            <div class="overdue-paging dataListHeader">
                <span id="pagingBoxBilling"></span>
            </div>
            <iframe name="iframeBilling" id="iframeBilling" class="overdue-iframe"
                    src="<?php echo $_SERVER['PHP_SELF'] . '?reportView=billing'; ?>">
            </iframe>
        </div><!-- /#tab-billing -->

    </div><!-- /.tab-content -->

    <script>
    $(document).ready(function () {

        // ── Custom tab switching (pakai button agar tidak diintercept SLiMS admin JS) ──
        function showTab(target) {
            // Update active state pada nav button
            $('#overdueTabNav li').removeClass('active');
            $('#overdueTabNav button[data-target="' + target + '"]').closest('li').addClass('active');

            // Tampilkan panel yang dipilih, sembunyikan yang lain
            $('.overdue-tab-content .tab-pane').removeClass('active').hide();
            $(target).addClass('active').show();

            // Simpan ke localStorage
            localStorage.setItem('overdueActiveTab', target);
        }

        // Klik tab button
        $('.overdue-tab-btn').on('click', function () {
            var target = $(this).data('target');
            showTab(target);
        });

        // ── Restore active tab dari localStorage atau URL param ──
        var urlParams = new URLSearchParams(window.location.search);
        var tabParam  = urlParams.get('tab');
        var stored    = localStorage.getItem('overdueActiveTab');
        var activeTab = tabParam ? '#tab-' + tabParam : (stored || '#tab-overdued');
        showTab(activeTab);

        // ── Generate Tagihan Akhir Semester (AJAX POST) ──
        $('#btnGenerate').on('click', function () {
            if (!confirm('Apakah Anda yakin ingin men-generate tagihan untuk semua data terlambat yang belum ditagih sesuai filter?')) {
                return;
            }
            var $btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Memproses...');

            $.post('<?= $_SERVER['PHP_SELF'] ?>', {
                generateBill : 1,
                startDate    : $('#billingStartDate').val(),
                untilDate    : $('#billingUntilDate').val()
            }, function (res) {
                try {
                    var data = JSON.parse(res);
                    if (data.status) {
                        $('#statusMessageBilling').html(
                            '<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + data.message + '</div>'
                        );
                        // Reload billing iframe dengan filter yang sama
                        var src = '<?= $_SERVER['PHP_SELF'] ?>?reportView=billing'
                            + '&startDate=' + encodeURIComponent($('#billingStartDate').val())
                            + '&untilDate='  + encodeURIComponent($('#billingUntilDate').val());
                        document.getElementById('iframeBilling').src = src;
                    } else {
                        $('#statusMessageBilling').html(
                            '<div class="alert alert-danger"><i class="fa fa-times-circle"></i> ' + data.message + '</div>'
                        );
                    }
                } catch (e) {
                    $('#statusMessageBilling').html(
                        '<div class="alert alert-danger">Terjadi kesalahan. Silakan coba lagi.</div>'
                    );
                }
                $btn.prop('disabled', false).html('<i class="fa fa-money"></i> <?= __('Generate Tagihan Akhir Semester') ?>');
            }).fail(function () {
                $('#statusMessageBilling').html(
                    '<div class="alert alert-danger">Gagal terhubung ke server.</div>'
                );
                $btn.prop('disabled', false).html('<i class="fa fa-money"></i> <?= __('Generate Tagihan Akhir Semester') ?>');
            });
        });

        // ── Auto-dismiss status message setelah 6 detik ──
        $(document).on('click', '#statusMessageBilling .alert', function () { $(this).fadeOut(); });
        setTimeout(function () { $('#statusMessageBilling .alert').fadeOut('slow'); }, 6000);
    });
    </script>
    <?php
}
?>
