<?php
$config = require '../config/database.php';
$db = $config['nodes']['SLiMS'];
$mysqli = new mysqli($db['host'], $db['username'], $db['password'], $db['database'], $db['port']);

echo "MST_ITEM_STATUS:\n";
$res = $mysqli->query("SELECT * FROM mst_item_status");
while ($row = $res->fetch_assoc()) {
    echo json_encode($row) . "\n";
}
