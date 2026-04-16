<?php
define('INDEX_AUTH', '1');
require 'sysconfig.inc.php';
global $dbs;

$res = $dbs->query("SELECT biblio_id, title, image FROM biblio WHERE title LIKE '%ATLAS INDONESIA%'");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
