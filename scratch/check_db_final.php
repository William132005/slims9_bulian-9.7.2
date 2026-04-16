<?php
define('INDEX_AUTH', '1');
require 'sysconfig.inc.php';
global $dbs;

$res = $dbs->query("SELECT biblio_id, title, image FROM biblio WHERE title LIKE '%ATLAS INDONESIA%'");
$row = $res->fetch_assoc();
echo "BIBLIO TABLE:\n";
print_r($row);

$res = $dbs->query("SELECT biblio_id, title, image FROM search_biblio WHERE title LIKE '%ATLAS INDONESIA%'");
$row = $res->fetch_assoc();
echo "\nSEARCH_BIBLIO TABLE:\n";
print_r($row);
