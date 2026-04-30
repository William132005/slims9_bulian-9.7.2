<?php
$config = require '../config/database.php';
$db = $config['nodes']['SLiMS'];
$mysqli = new mysqli($db['host'], $db['username'], $db['password'], $db['database'], $db['port']);

echo "<h3>Isi tabel mst_item_status:</h3><pre>";
$res = $mysqli->query("SELECT item_status_id, item_status_name FROM mst_item_status ORDER BY item_status_id");
if ($res->num_rows == 0) {
    echo "Tabel kosong!";
} else {
    echo str_pad("ID", 6) . " | " . "item_status_name\n";
    echo str_repeat("-", 40) . "\n";
    while ($row = $res->fetch_assoc()) {
        echo str_pad($row['item_status_id'], 6) . " | " . $row['item_status_name'] . "\n";
    }
}
echo "</pre>";
echo "<p>Total: {$res->num_rows} baris</p>";
