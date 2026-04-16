<?php
define('INDEX_AUTH', '1');
require '../sysconfig.inc.php';
global $dbs;

echo "--- RAW DB CHECK ---\n";
// Find the atlas book
$res = $dbs->query("SELECT biblio_id, title, image FROM biblio WHERE title LIKE '%ATLAS%' LIMIT 1");
$bib = $res->fetch_assoc();
if ($bib) {
    echo "BIBLIO ID: " . $bib['biblio_id'] . "\n";
    echo "BIBLIO IMAGE: [" . $bib['image'] . "]\n";
    
    $res2 = $dbs->query("SELECT image FROM search_biblio WHERE biblio_id = " . $bib['biblio_id']);
    $sb = $res2->fetch_assoc();
    if ($sb) {
        echo "SEARCH_BIBLIO IMAGE: [" . $sb['image'] . "]\n";
    } else {
        echo "SEARCH_BIBLIO RECORD MISSING!\n";
    }
} else {
    echo "BIBLIO RECORD NOT FOUND!\n";
}

echo "\n--- FILE SYSTEM CHECK ---\n";
$target = SB . 'images' . DS . 'bibliography';
if (is_dir($target)) {
   $files = scandir($target);
   echo "FILES IN $target:\n";
   foreach($files as $f) {
       if ($f !== '.' && $f !== '..') {
           echo " - $f\n";
       }
   }
} else {
    echo "DIRECTORY MISSING: $target\n";
}
