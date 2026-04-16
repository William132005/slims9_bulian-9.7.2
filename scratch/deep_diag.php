<?php
define('INDEX_AUTH', '1');
require '../sysconfig.inc.php';
global $dbs;

$biblio_id = 1744;
echo "--- DEEP CHECK BIBLIO ID: $biblio_id ---\n";
$res = $dbs->query("SELECT * FROM biblio WHERE biblio_id = $biblio_id");
$row = $res->fetch_assoc();
print_r($row);

echo "\n--- DEEP CHECK SEARCH_BIBLIO ---\n";
$res2 = $dbs->query("SELECT * FROM search_biblio WHERE biblio_id = $biblio_id");
$row2 = $res2->fetch_assoc();
print_r($row2);

echo "\n--- SEARCH RESULTS FOR TITLE ---\n";
$res3 = $dbs->query("SELECT biblio_id, title, image FROM search_biblio WHERE title LIKE '%ATLAS%'");
while($r = $res3->fetch_assoc()) print_r($r);
