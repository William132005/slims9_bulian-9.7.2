<?php
define('INDEX_AUTH', '1');
require '../sysconfig.inc.php';
global $dbs;

echo "--- TABLE STRUCTURE ---\n";
$res = $dbs->query("DESCRIBE biblio");
while ($row = $res->fetch_assoc()) {
    if ($row['Field'] == 'image') {
        print_r($row);
    }
}

$res = $dbs->query("DESCRIBE search_biblio");
while ($row = $res->fetch_assoc()) {
    if ($row['Field'] == 'image') {
        print_r($row);
    }
}

echo "\n--- SAMPLE RECORD ---\n";
$res = $dbs->query("SELECT biblio_id, title, image FROM biblio WHERE title LIKE '%ATLAS%' LIMIT 1");
print_r($res->fetch_assoc());
