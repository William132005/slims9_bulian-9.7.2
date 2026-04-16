<?php
define('INDEX_AUTH', '1');
require '../sysconfig.inc.php';
global $dbs;

$biblio_id = 1744; // From previous run
$test_image = 'cover_DIAGNOSTIC_TEST.jpg';

echo "--- ATTEMPTING UPDATE ---\n";
$q = "UPDATE biblio SET image = '$test_image' WHERE biblio_id = $biblio_id";
echo "SQL: $q\n";
$res = $dbs->query($q);
if ($res) {
    echo "UPDATE BIBLIO SUCCESS\n";
} else {
    echo "UPDATE BIBLIO FAILED: " . $dbs->error . "\n";
}

$q2 = "UPDATE search_biblio SET image = '$test_image' WHERE biblio_id = $biblio_id";
echo "SQL2: $q2\n";
$res2 = $dbs->query($q2);
if ($res2) {
    echo "UPDATE SEARCH_BIBLIO SUCCESS\n";
} else {
    echo "UPDATE SEARCH_BIBLIO FAILED: " . $dbs->error . "\n";
}

echo "\n--- VERIFYING ---\n";
$res3 = $dbs->query("SELECT image FROM biblio WHERE biblio_id = $biblio_id");
$row = $res3->fetch_assoc();
echo "FINAL BIBLIO IMAGE: [" . $row['image'] . "]\n";

$res4 = $dbs->query("SELECT image FROM search_biblio WHERE biblio_id = $biblio_id");
$row2 = $res4->fetch_assoc();
echo "FINAL SEARCH_BIBLIO IMAGE: [" . ($row2['image'] ?? 'NOT FOUND') . "]\n";
