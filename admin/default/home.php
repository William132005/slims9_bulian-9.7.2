<?php
/**
 *
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * some patches by hendro
 */

use SLiMS\DB;

// key to authenticate
if (!defined('INDEX_AUTH')) {
    define('INDEX_AUTH', '1');
}

if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    include_once '../../sysconfig.inc.php';
}
?>
<div class="menuBox adminHome">
    <div class="menuBoxInner">
        <div class="per_title">
            <h2><?php echo __('Library Administration'); ?></h2>
        </div>
    </div>
</div>
<div id="backupProccess" style="display: none">
    <div class="alert alert-info">
        <strong><?= __('Database backup process is running, please wait') ?></strong>
    </div>
</div>
<div class="contentDesc">
    <div class="container-fluid">

        <div id="alert-new-version" class="alert alert-info border-0 mt-3 hidden">
            <strong>News!</strong> New version of SLiMS (<code id="new_version"></code>) available to <a class="notAJAX"
                                                                                                         target="_blank"
                                                                                                         href="https://github.com/slims/slims9_bulian/releases/latest">download</a>.
        </div>

        <?php
        // generate warning messages
        $warnings = array();
        // check GD extension
        if (!extension_loaded('gd')) {
            $warnings[] = __('<strong>PHP GD</strong> extension is not installed. Please install it or application won\'t be able to create image thumbnail and barcode.');
        } else {
            // check GD Freetype
            if (!function_exists('imagettftext')) {
                $warnings[] = __('<strong>Freetype</strong> support is not enabled in PHP GD extension. Rebuild PHP GD extension with Freetype support or application won\'t be able to create barcode.');
            }
        }
        // check for overdue
        $overdue_q = $dbs->query('SELECT COUNT(loan_id) FROM loan AS l WHERE (l.is_lent=1 AND l.is_return=0 AND TO_DAYS(due_date) < TO_DAYS(\'' . date('Y-m-d') . '\')) GROUP BY member_id');
        $num_overdue = $overdue_q->num_rows;
        if ($num_overdue > 0) {
            $warnings[] = str_replace('{num_overdue}', $num_overdue, __('There are currently <strong>{num_overdue}</strong> library members having overdue. Please check at <b>Circulation</b> module at <b>Overdues</b> section for more detail')); //mfc
            $overdue_q->free_result();
        }
        // check for unbilled late returns
        $unbilled_q = $dbs->query('SELECT COUNT(loan_id) FROM loan WHERE is_return=0 AND is_billed=0 AND TO_DAYS(due_date) < TO_DAYS(\'' . date('Y-m-d') . '\') GROUP BY member_id');
        $num_unbilled = $unbilled_q->num_rows;
        if ($num_unbilled > 0) {
            $warnings[] = str_replace('{num}', $num_unbilled, __('Terdapat <strong>{num}</strong> anggota yang belum mengembalikan buku dan belum ditagih Semester ini.'));
        }
        // check if images dir is writable or not
        if (!is_writable(IMGBS) OR !is_writable(IMGBS . 'barcodes') OR !is_writable(IMGBS . 'persons') OR !is_writable(IMGBS . 'docs')) {
            $warnings[] = __('<strong>Images</strong> directory and directories under it is not writable. Make sure it is writable by changing its permission or you won\'t be able to upload any images and create barcodes');
        }
        // check if file repository dir is writable or not
        if (!is_writable(REPOBS)) {
            $warnings[] = __('<strong>Repository</strong> directory is not writable. Make sure it is writable (and all directories under it) by changing its permission or you won\'t be able to upload any bibliographic attachments.');
        }
        // check if file upload dir is writable or not
        if (!is_writable(UPLOAD)) {
            $warnings[] = __('<strong>File upload</strong> directory is not writable. Make sure it is writable (and all directories under it) by changing its permission or you won\'t be able to upload any file, create report files and create database backups.');
        }
        // check installer directory
        if (is_dir('../install/')) {
            $warnings[] = __('Installer folder is still exist inside your server. Please remove it or rename to another name for security reason.');
        }

        // check need to be repaired mysql database
        $query_of_tables = $dbs->query('SHOW TABLES');
        $num_of_tables = $query_of_tables->num_rows;
        $prevtable = '';
        $repair = '';
        $is_repaired = false;

        if ($_SESSION['uid'] === '1') {
            $warnings[] = __('<strong><i>You are logged in as Super User. With great power comes great responsibility.</i></strong>');
            if (isset ($_POST['do_repair'])) {
                if ($_POST['do_repair'] == 1) {
                    while ($row = $query_of_tables->fetch_row()) {
                        $sql_of_repair = 'REPAIR TABLE ' . $row[0];
                        $query_of_repair = $dbs->query($sql_of_repair);
                    }
                }
            }

            while ($row = $query_of_tables->fetch_row()) {
                $query_of_check = $dbs->query('CHECK TABLE `' . $row[0] . '`');
                if ($query_of_check) {
                    while ($rowcheck = $query_of_check->fetch_assoc()) {
                        if (!(($rowcheck['Msg_type'] == "status") && ($rowcheck['Msg_text'] == "OK"))) {
                            if ($row[0] != $prevtable) {
                                $repair .= '<li>' . __('Table') . ' ' . $row[0] . ' ' . __('might need to be repaired.') . '</li>';
                            }
                            $prevtable = $row[0];
                            $is_repaired = true;
                        }
                    }
                }
            }
            if (($is_repaired) && !isset($_POST['do_repair'])) {
                echo '<div class="message">';
                echo '<ul>';
                echo $repair;
                echo '</ul>';
                echo '</div>';
                echo ' <form method="POST" style="margin:0 10px;">
        <input type="hidden" name="do_repair" value="1">
        <input type="submit" value="' . __('Click Here To Repair The Tables') . '" class="button btn btn-block btn-default">
        </form>';
            }
        }

        // if there any warnings
        if ($warnings) {
            echo '<div class="alert alert-warning border-0 mt-3">';
            foreach ($warnings as $warning_msg) {
                echo '<div>' . $warning_msg . '</div>';
            }
            echo '</div>';
        }

        // admin page content
        if ($sysconf['admin_home']['mode'] == 'default') {
            require LIB . 'content.inc.php';
            $content = new content();
            $content_data = $content->get($dbs, 'adminhome');
            if ($content_data) {
                echo '<div class="contentDesc">' . $content_data['Content'] . '</div>';
                unset($content_data);
            }
        } else {
            $today = date('Y-m-d');

            // =============================================
            // QUERY: Statistik Harian
            // =============================================
            $q_loan_today = $dbs->query("SELECT COUNT(loan_id) as total FROM loan WHERE DATE(loan_date) = '$today' AND is_lent = 1");
            $loan_today = ($r = $q_loan_today->fetch_assoc()) ? (int)$r['total'] : 0;

            $q_return_today = $dbs->query("SELECT COUNT(loan_id) as total FROM loan WHERE DATE(return_date) = '$today' AND is_return = 1");
            $return_today = ($r = $q_return_today->fetch_assoc()) ? (int)$r['total'] : 0;

            // =============================================
            // QUERY: Daftar Pinjam Hari Ini
            // =============================================
            $q_loan_list = $dbs->query("
                SELECT
                    m.member_name,
                    b.title,
                    l.due_date,
                    l.item_code
                FROM loan l
                LEFT JOIN member m ON l.member_id = m.member_id
                LEFT JOIN item i ON l.item_code = i.item_code
                LEFT JOIN biblio b ON i.biblio_id = b.biblio_id
                WHERE DATE(l.loan_date) = '$today' AND l.is_lent = 1
                ORDER BY l.loan_date DESC
                LIMIT 10
            ");

            // =============================================
            // QUERY: Daftar Kembali Hari Ini
            // =============================================
            $q_return_list = $dbs->query("
                SELECT
                    m.member_name,
                    b.title,
                    l.return_date,
                    l.item_code
                FROM loan l
                LEFT JOIN member m ON l.member_id = m.member_id
                LEFT JOIN item i ON l.item_code = i.item_code
                LEFT JOIN biblio b ON i.biblio_id = b.biblio_id
                WHERE DATE(l.return_date) = '$today' AND l.is_return = 1
                ORDER BY l.return_date DESC
                LIMIT 10
            ");

            // =============================================
            // QUERY: Anggota Aktif Hari Ini (yang pinjam atau kembali)
            // =============================================
            $q_active_members = $dbs->query("
                SELECT DISTINCT
                    m.member_name,
                    m.member_id
                FROM loan l
                LEFT JOIN member m ON l.member_id = m.member_id
                WHERE DATE(l.loan_date) = '$today' OR DATE(l.return_date) = '$today'
                LIMIT 8
            ");
        ?>

        <!-- =====================================================
             4 WIDGET ATAS (koleksi, item, dipinjam, tersedia)
        ===================================================== -->
        <div class="row">
            <div class="col-xs-6 col-md-3 col-lg-3">
                <div class="card border-0">
                    <div class="card-body">
                        <div class="s-widget-icon"><i class="fa fa-bookmark"></i></div>
                        <div class="s-widget-value biblio_total_all">0</div>
                        <div class="s-widget-title"><?php echo __('Total of Collections') ?></div>
                    </div>
                </div>
            </div>
            <div class="col-xs-6 col-md-3 col-lg-3">
                <div class="card border-0">
                    <div class="card-body">
                        <div class="s-widget-icon"><i class="fa fa-barcode"></i></div>
                        <div class="s-widget-value item_total_all">0</div>
                        <div class="s-widget-title"><?php echo __('Total of Items') ?></div>
                    </div>
                </div>
            </div>
            <div class="col-xs-6 col-md-3 col-lg-3">
                <div class="card border-0">
                    <div class="card-body">
                        <div class="s-widget-icon"><i class="fa fa-archive"></i></div>
                        <div class="s-widget-value item_total_lent">0</div>
                        <div class="s-widget-title"><?php echo __('Lent') ?></div>
                    </div>
                </div>
            </div>
            <div class="col-xs-6 col-md-3 col-lg-3">
                <div class="card border-0">
                    <div class="card-body">
                        <div class="s-widget-icon"><i class="fa fa-check"></i></div>
                        <div class="s-widget-value item_total_available">0</div>
                        <div class="s-widget-title"><?php echo __('Available') ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =====================================================
             STATISTIK HARIAN — 2 card: Pinjam, Kembali
        ===================================================== -->
        <div class="row mt-3">
            <div class="col-xs-12 col-md-6">
                <div class="slims-dash-card slims-dash-card--green">
                    <div class="slims-dash-card__icon"><i class="fa fa-sign-out"></i></div>
                    <div class="slims-dash-card__body">
                        <div class="slims-dash-card__value"><?= $loan_today ?></div>
                        <div class="slims-dash-card__label"><?php echo __('Pinjam Hari Ini') ?></div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-md-6">
                <div class="slims-dash-card slims-dash-card--blue">
                    <div class="slims-dash-card__icon"><i class="fa fa-sign-in"></i></div>
                    <div class="slims-dash-card__body">
                        <div class="slims-dash-card__value"><?= $return_today ?></div>
                        <div class="slims-dash-card__label"><?php echo __('Kembali Hari Ini') ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =====================================================
             BARIS UTAMA: Pinjam Hari Ini + Kembali Hari Ini
        ===================================================== -->
        <div class="row mt-3">

            <!-- Pinjam Hari Ini -->
            <div class="col-md-6">
                <div class="slims-panel">
                    <div class="slims-panel__header">
                        <i class="fa fa-sign-out"></i>&nbsp; <?php echo __('Pinjam Hari Ini') ?>
                        <?php if ($loan_today > 0): ?>
                            <span class="slims-badge slims-badge--green" style="margin-left:auto;"><?= $loan_today ?> transaksi</span>
                        <?php endif; ?>
                    </div>
                    <div class="slims-panel__body p-0">
                        <table class="slims-table">
                            <thead>
                                <tr>
                                    <th><?php echo __('Anggota') ?></th>
                                    <th><?php echo __('Judul Buku') ?></th>
                                    <th><?php echo __('Kode Item') ?></th>
                                    <th><?php echo __('Tgl Kembali') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $has_loan = false;
                            if ($q_loan_list) {
                                while ($row = $q_loan_list->fetch_assoc()) {
                                    $has_loan = true;
                                    $member_name = htmlspecialchars($row['member_name'] ?? '-');
                                    $title_text  = htmlspecialchars(mb_strimwidth($row['title'] ?? '-', 0, 40, '…'));
                                    $item_code   = htmlspecialchars($row['item_code'] ?? '-');
                                    $due_date    = !empty($row['due_date']) ? date('d M Y', strtotime($row['due_date'])) : '-';
                                    echo "<tr>
                                        <td><i class='fa fa-user-circle' style='color:#6c757d;margin-right:5px;'></i>{$member_name}</td>
                                        <td>{$title_text}</td>
                                        <td><code style='font-size:0.78rem;'>{$item_code}</code></td>
                                        <td style='white-space:nowrap;color:#e67e22;font-weight:600;'>{$due_date}</td>
                                    </tr>";
                                }
                            }
                            if (!$has_loan) {
                                echo '<tr><td colspan="4" class="text-center text-muted" style="padding:24px 0;">Belum ada peminjaman hari ini.</td></tr>';
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Kembali Hari Ini -->
            <div class="col-md-6">
                <div class="slims-panel">
                    <div class="slims-panel__header">
                        <i class="fa fa-sign-in"></i>&nbsp; <?php echo __('Kembali Hari Ini') ?>
                        <?php if ($return_today > 0): ?>
                            <span class="slims-badge slims-badge--blue" style="margin-left:auto;"><?= $return_today ?> transaksi</span>
                        <?php endif; ?>
                    </div>
                    <div class="slims-panel__body p-0">
                        <table class="slims-table">
                            <thead>
                                <tr>
                                    <th><?php echo __('Anggota') ?></th>
                                    <th><?php echo __('Judul Buku') ?></th>
                                    <th><?php echo __('Kode Item') ?></th>
                                    <th><?php echo __('Tgl Kembali') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $has_return = false;
                            if ($q_return_list) {
                                while ($row = $q_return_list->fetch_assoc()) {
                                    $has_return = true;
                                    $member_name = htmlspecialchars($row['member_name'] ?? '-');
                                    $title_text  = htmlspecialchars(mb_strimwidth($row['title'] ?? '-', 0, 40, '…'));
                                    $item_code   = htmlspecialchars($row['item_code'] ?? '-');
                                    $ret_date    = !empty($row['return_date']) ? date('d M Y', strtotime($row['return_date'])) : '-';
                                    echo "<tr>
                                        <td><i class='fa fa-user-circle' style='color:#6c757d;margin-right:5px;'></i>{$member_name}</td>
                                        <td>{$title_text}</td>
                                        <td><code style='font-size:0.78rem;'>{$item_code}</code></td>
                                        <td style='white-space:nowrap;color:#28a745;font-weight:600;'>{$ret_date}</td>
                                    </tr>";
                                }
                            }
                            if (!$has_return) {
                                echo '<tr><td colspan="4" class="text-center text-muted" style="padding:24px 0;">Belum ada pengembalian hari ini.</td></tr>';
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Anggota Aktif Hari Ini -->
                <?php
                $active_members = [];
                if ($q_active_members) {
                    while ($am = $q_active_members->fetch_assoc()) {
                        $active_members[] = $am;
                    }
                }
                if (!empty($active_members)): ?>
                <div class="slims-panel mt-3">
                    <div class="slims-panel__header">
                        <i class="fa fa-users"></i>&nbsp; <?php echo __('Anggota Aktif Hari Ini') ?>
                    </div>
                    <div class="slims-panel__body p-0">
                        <table class="slims-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo __('ID Anggota') ?></th>
                                    <th><?php echo __('Nama Anggota') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $no = 1; foreach ($active_members as $am): ?>
                                <tr>
                                    <td style="color:#999;width:32px;"><?= $no++ ?></td>
                                    <td><code style="font-size:0.78rem;"><?= htmlspecialchars($am['member_id'] ?? '-') ?></code></td>
                                    <td><i class="fa fa-user-circle" style="color:#6c757d;margin-right:5px;"></i><?= htmlspecialchars($am['member_name'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

            </div><!-- /kembali hari ini -->
        </div><!-- /row utama -->

    </div>
</div>

<!-- =====================================================
     STYLES — Dashboard Custom Components
===================================================== -->
<style>
/* ---------- Daily Stats Cards ---------- */
.slims-dash-card {
    display: flex;
    align-items: center;
    gap: 18px;
    border-radius: 14px;
    padding: 20px 22px;
    margin-bottom: 4px;
    box-shadow: 0 3px 14px rgba(0,0,0,0.09);
    transition: transform 0.18s ease, box-shadow 0.18s ease;
}
.slims-dash-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.14);
}
.slims-dash-card--green { background: linear-gradient(135deg,#d4edda,#c3e6cb); border-left: 5px solid #28a745; }
.slims-dash-card--blue  { background: linear-gradient(135deg,#cce5ff,#b8daff); border-left: 5px solid #007bff; }
.slims-dash-card--red   { background: linear-gradient(135deg,#f8d7da,#f5c6cb); border-left: 5px solid #dc3545; }
.slims-dash-card__icon  { font-size: 2.2rem; opacity: 0.75; }
.slims-dash-card--green .slims-dash-card__icon { color: #155724; }
.slims-dash-card--blue  .slims-dash-card__icon { color: #004085; }
.slims-dash-card--red   .slims-dash-card__icon { color: #721c24; }
.slims-dash-card__value { font-size: 2.1rem; font-weight: 800; line-height: 1; }
.slims-dash-card--green .slims-dash-card__value { color: #155724; }
.slims-dash-card--blue  .slims-dash-card__value { color: #004085; }
.slims-dash-card--red   .slims-dash-card__value { color: #721c24; }
.slims-dash-card__label { font-size: 0.78rem; font-weight: 700; margin-top: 4px; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.05em; }

/* ---------- Panel ---------- */
.slims-panel {
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    border: 1px solid #e9ecef;
    overflow: hidden;
}
.slims-panel--danger { border-color: #f5c6cb; }
.slims-panel.mb-3   { margin-bottom: 18px; }
.slims-panel__header {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 12px 18px;
    font-weight: 700;
    font-size: 0.9rem;
    color: #343a40;
    background: linear-gradient(90deg,#f8f9fa,#fff);
    border-bottom: 1px solid #e9ecef;
}
.slims-panel__header--danger {
    background: linear-gradient(90deg,#f8d7da,#fff5f6);
    color: #721c24;
    border-color: #f5c6cb;
}
.slims-panel__body    { padding: 14px 18px; }
.slims-panel__body.p-0 { padding: 0; }

/* ---------- Table ---------- */
.slims-table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
.slims-table thead tr { background: #f8f9fa; }
.slims-table th {
    padding: 10px 14px;
    font-weight: 700;
    color: #495057;
    font-size: 0.76rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-bottom: 2px solid #dee2e6;
}
.slims-table td { padding: 9px 14px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; color: #495057; }
.slims-table tbody tr:last-child td { border-bottom: none; }
.slims-table tbody tr:hover { background: #fafafa; }

/* ---------- Badges ---------- */
.slims-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 9px;
    border-radius: 50px;
    font-size: 0.74rem;
    font-weight: 700;
    white-space: nowrap;
}
.slims-badge--green { background: #d4edda; color: #155724; }
.slims-badge--blue  { background: #cce5ff; color: #004085; }
.slims-badge--red   { background: #f8d7da; color: #721c24; }

/* ---------- Empty State ---------- */
.slims-empty-state { text-align: center; padding: 18px 10px; border-radius: 8px; }
.slims-empty-state--green { background: #d4edda; color: #155724; }
.slims-empty-state p { margin: 8px 0 0; font-weight: 700; font-size: 0.83rem; }
</style>

    <script>
        $(function () {
            async function getTotal(url, selector) {
                if (selector) $(selector).text('...');
                let res = await (await fetch(url, {headers: {'SLiMS-Http-Cache': 'cache'}})).json();
                if (selector) $(selector).text(new Intl.NumberFormat('id-ID').format(res.data));
                return res.data;
            }

            getTotal('<?= SWB ?>index.php?p=api/biblio/total/all', '.biblio_total_all');
            getTotal('<?= SWB ?>index.php?p=api/item/total/all',   '.item_total_all');
            getTotal('<?= SWB ?>index.php?p=api/item/total/lent',  '.item_total_lent');
            getTotal('<?= SWB ?>index.php?p=api/item/total/available', '.item_total_available');

        <?php if (utility::havePrivilege('system', 'r') && utility::havePrivilege('system', 'w')): ?>
            <?php if (config('database_backup.reminder') && !config('database_backup.auto')): ?>
                $('#backupproc').click(function(e) {
                    e.preventDefault();
                    let currentLabel = $(this).html();
                    $(this).removeClass('btn-primary').addClass('btn-secondary disabled');
                    $(this).html('<?= __('Please wait') ?>');
                    backupDatabase($(this).attr('href'), function(result) {
                        if (result.status) {
                            window.location.href = '<?= $_SERVER['PHP_SELF'] ?>';
                        } else {
                            console.error(result.message);
                            window.toastr.error(result.message, '<?= __('Error') ?>');
                        }
                    });
                });
            <?php endif; ?>

            function backupDatabase(href, callback) {
                $.post(href, {start:true, tkn:'<?= $_SESSION['token']??'' ?>', verbose:'no', response:'json'}, function(result) {
                    callback(JSON.parse(result));
                });
            }
        <?php endif; ?>

        <?php if ($_SESSION['uid'] === '1') : ?>
            fetch('https://api.github.com/repos/slims/slims9_bulian/releases/latest')
                .then(res => res.json())
                .then(res => {
                    if (res.tag_name > '<?= SENAYAN_VERSION_TAG; ?>') {
                        $('#new_version').text(res.tag_name);
                        $('#alert-new-version').removeClass('hidden');
                        $('#alert-new-version a').attr('href', res.html_url);
                    }
                });
        <?php endif; ?>
        });
    </script>
<?php } ?>
