<?php
/**
 * Periodic Late Return Reminder / Semester Billing
 * Developed for SLiMS
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
$can_read = utility::havePrivilege('circulation', 'r');
$can_write = utility::havePrivilege('circulation', 'w');

if (!$can_read) {
    die('<div class="errorBox">' . __('You don\'t have enough privileges to access this area!') . '</div>');
}

require SIMBIO . 'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO . 'simbio_GUI/form_maker/simbio_form_element.inc.php';
require SIMBIO . 'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO . 'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require MDLBS . 'reporting/report_dbgrid.inc.php';

$page_title = 'Tagihan Keterlambatan Akhir Semester';

// Handle Generate Bill Action
if (isset($_POST['generateBill'])) {
    if (!$can_write) {
        die(json_encode(['status' => false, 'message' => __('You don\'t have enough privileges!')]));
    }
    
    $startDate = $dbs->real_escape_string($_POST['startDate']);
    $untilDate = $dbs->real_escape_string($_POST['untilDate']);
    $semesterTag = $dbs->real_escape_string($_POST['semesterTag']);
    
    if (empty($semesterTag)) {
        die(json_encode(['status' => false, 'message' => __('Semester Tag is required!')]));
    }

    // Logic to update all overdue items in the range that are not yet billed
    $criteria = "due_date < '" . date('Y-m-d') . "' AND is_return = 0 AND is_billed = 0";
    if ($startDate && $untilDate) {
        $criteria .= " AND loan_date BETWEEN '$startDate' AND '$untilDate'";
    }

    $update_sql = "UPDATE loan SET is_billed = 1, billed_date = NOW(), semester_tag = '$semesterTag' WHERE $criteria";
    $dbs->query($update_sql);
    
    // Also update loan_history for matches
    $update_hist_sql = "UPDATE loan_history SET is_billed = 1, billed_date = NOW(), semester_tag = '$semesterTag' WHERE $criteria";
    $dbs->query($update_hist_sql);

    echo json_encode(['status' => true, 'message' => __('Success! Bill generated for matching records.')]);
    exit;
}

$reportView = false;
$num_recs_show = 20;
if (isset($_GET['reportView'])) {
    $reportView = true;
}

if (!$reportView) {
    ?>
    <div>
        <div class="per_title">
            <h2><?php echo __('Tagihan Keterlambatan Akhir Semester'); ?></h2>
        </div>
        <div class="infoBox">
          <?php echo __('Filter Berdasarkan Periode Peminjaman'); ?>
        </div>
        <div class="sub_section">
            <form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>" target="reportView" id="filterForm">
                <div class="form-group divRow">
                    <div class="divRowContent">
                        <div>
                            <label style="width: 195px;"><?php echo __('Tanggal Pinjam Dari'); ?></label>
                            <label><?php echo __('Sampai'); ?></label>
                        </div>
                        <div id="range">
                            <input type="text" name="startDate" id="startDate" value="<?= date('Y') ?>-01-01" class="form-control" style="width: 150px; display: inline-block;">
                            <span><?= __('to') ?></span>
                            <input type="text" name="untilDate" id="untilDate" value="<?= date('Y-m-d') ?>" class="form-control" style="width: 150px; display: inline-block;">
                        </div>
                    </div>
                </div>
                <div class="divRow">
                    <div class="divRowLabel"><?php echo __('Semester Tag'); ?></div>
                    <div class="divRowContent">
                        <input type="text" name="semesterTag" id="semesterTag" class="form-control" placeholder="Contoh: Ganjil 2023/2024" style="width: 300px;">
                    </div>
                </div>
                <div style="padding-top: 10px; clear: both;">
                    <input type="submit" class="btn btn-primary" name="applyFilter" value="<?php echo __('Tampilkan Daftar'); ?>"/>
                    <?php if ($can_write): ?>
                    <button type="button" id="btnGenerate" class="btn btn-success"><i class="fa fa-money"></i> <?php echo __('Generate Tagihan Akhir Semester'); ?></button>
                    <?php endif; ?>
                    <input type="hidden" name="reportView" value="true"/>
                </div>
            </form>
        </div>
    </div>
    <div id="statusMessage" style="margin-top: 10px;"></div>
    <div class="dataListHeader" style="padding: 3px;"><span id="pagingBox"></span></div>
    <iframe name="reportView" id="reportView" src="<?php echo $_SERVER['PHP_SELF'] . '?reportView=true'; ?>"
            frameborder="0" style="width: 100%; height: 600px;"></iframe>
    
    <script>
        $(document).ready(function(){
            $('#btnGenerate').click(function(){
                if (!confirm('Apakah Anda yakin ingin melakukan generate tagihan untuk semua data yang tampil?')) return;
                
                const semesterTag = $('#semesterTag').val();
                if (!semesterTag) {
                    alert('Mohon isi Semester Tag terlebih dahulu!');
                    return;
                }

                $.post('<?= $_SERVER['PHP_SELF'] ?>', {
                    generateBill: 1,
                    startDate: $('#startDate').val(),
                    untilDate: $('#untilDate').val(),
                    semesterTag: semesterTag
                }, function(res){
                    const data = JSON.parse(res);
                    if (data.status) {
                        $('#statusMessage').html('<div class="alert alert-success">'+data.message+'</div>');
                        document.getElementById('reportView').contentWindow.location.reload();
                    } else {
                        alert(data.message);
                    }
                });
            });
        });
    </script>
    <?php
} else {
    ob_start();
    
    // table spec - we use loan for active overdues
    $table_spec = 'loan AS l 
        LEFT JOIN member AS m ON l.member_id = m.member_id
        LEFT JOIN item AS i ON l.item_code = i.item_code
        LEFT JOIN biblio AS b ON i.biblio_id = b.biblio_id';

    $reportgrid = new report_datagrid();
    
    // Select columns
    $reportgrid->setSQLColumn(
        'm.member_name AS \'' . __('Nama Anggota') . '\'',
        'b.title AS \'' . __('Judul Buku') . '\'',
        'l.loan_date AS \'' . __('Tgl Pinjam') . '\'',
        'l.due_date AS \'' . __('Jatuh Tempo') . '\'',
        'TO_DAYS(DATE(NOW())) - TO_DAYS(l.due_date) AS \'' . __('Hari Terlambat') . '\'',
        'IF(l.is_return=1, \''.__('Kembali').'\', \''.__('Pinjam').'\') AS \'' . __('Status') . '\'',
        'l.is_billed'
    );
    
    // Criteria: must be overdue (due_date < today) and matches date filter
    $criteria = "l.due_date < '" . date('Y-m-d') . "' AND l.is_return = 0";
    
    if (isset($_GET['startDate']) && isset($_GET['untilDate']) && $_GET['startDate'] && $_GET['untilDate']) {
        $criteria .= " AND l.loan_date BETWEEN '".$dbs->real_escape_string($_GET['startDate'])."' AND '".$dbs->real_escape_string($_GET['untilDate'])."'";
    }

    $reportgrid->setSQLCriteria($criteria);
    $reportgrid->setSQLorder('l.due_date ASC');
    
    // Customization for Billing Status column
    function showBillingStatus($obj_db, $array_data) {
        $status = $array_data[6];
        if ($status == 1) {
            return '<span class="label label-success" style="background-color: #28a745; color: white; padding: 2px 5px; border-radius: 3px;">' . __('Sudah Ditagih') . '</span>';
        } else {
            return '<span class="label label-danger" style="background-color: #dc3545; color: white; padding: 2px 5px; border-radius: 3px;">' . __('Belum Ditagih') . '</span>';
        }
    }

    $reportgrid->modifyColumnContent(6, 'callback{showBillingStatus}');
    
    // set table and table header attributes
    $reportgrid->table_attr = 'align="center" class="dataListPrinted" cellpadding="5" cellspacing="0"';
    $reportgrid->table_header_attr = 'class="dataListHeaderPrinted"';
    
    // Excel & PDF Export
    
    echo $reportgrid->createDataGrid($dbs, $table_spec, $num_recs_show);
    
    ?>
    <script type="text/javascript">
        parent.$('#pagingBox').html('<?php echo str_replace(array("\n", "\r", "\t"), '', $reportgrid->paging_set) ?>');
    </script>
    <?php
    
    $content = ob_get_clean();
    require SB . '/admin/' . $sysconf['admin_template']['dir'] . '/printed_page_tpl.php';
}
?>
