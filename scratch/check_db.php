<?php
$config = require '../config/database.php';
$db = $config['nodes']['SLiMS'];
$mysqli = new mysqli($db['host'], $db['username'], $db['password'], $db['database'], $db['port']);

echo "<h3>Menghapus triggers yang konflik...</h3>";

$triggers = ['insert_loan_history', 'update_loan_history', 'delete_loan_history'];
foreach ($triggers as $trigger) {
    if ($mysqli->query("DROP TRIGGER IF EXISTS `$trigger`")) {
        echo "✅ Trigger <b>$trigger</b> berhasil dihapus<br>";
    } else {
        echo "❌ Gagal hapus trigger $trigger: " . $mysqli->error . "<br>";
    }
}

echo "<h3>Verifikasi — triggers tersisa:</h3><pre>";
$res2 = $mysqli->query("SHOW TRIGGERS WHERE `Table` = 'loan'");
if ($res2->num_rows == 0) {
    echo "✅ Tidak ada trigger lagi — bersih!\n";
} else {
    while ($row = $res2->fetch_assoc()) {
        echo "⚠️ Masih ada: " . $row['Trigger'] . " (" . $row['Event'] . ")\n";
    }
}
echo "</pre>";

echo "<h3>Test insert ke loan (setelah hapus trigger):</h3>";
$test = $mysqli->query("SELECT item_code FROM item LIMIT 1");
$item = $test->fetch_assoc();
$test2 = $mysqli->query("SELECT member_id FROM member LIMIT 1");
$member = $test2->fetch_assoc();

if ($item && $member) {
    $item_code = $mysqli->real_escape_string($item['item_code']);
    $member_id = $mysqli->real_escape_string($member['member_id']);
    $sql = "INSERT INTO loan (item_code, member_id, loan_date, due_date, renewed, is_lent, is_return, input_date, last_update, uid)
            VALUES ('$item_code', '$member_id', '2099-01-01', '2099-01-15', 0, 1, 0, NOW(), NOW(), 1)";
    if ($mysqli->query($sql)) {
        $loan_id = $mysqli->insert_id;
        echo "✅ INSERT loan berhasil! loan_id=$loan_id<br>";
        $mysqli->query("DELETE FROM loan WHERE loan_id=$loan_id");
        $mysqli->query("DELETE FROM loan_history WHERE loan_id=$loan_id");
        echo "✅ Data test berhasil dibersihkan<br>";
    } else {
        echo "❌ INSERT MASIH GAGAL: " . $mysqli->error . "<br>";
    }
} else {
    echo "Tidak ada item/member untuk ditest<br>";
}


echo "<h3>1. Kolom tabel loan:</h3><pre>";
$res = $mysqli->query("DESC `loan`");
while ($row = $res->fetch_assoc()) {
    echo $row['Field'] . " | " . $row['Type'] . " | Null:" . $row['Null'] . " | Default:" . $row['Default'] . "\n";
}
echo "</pre>";

echo "<h3>2. MySQL Triggers pada tabel loan:</h3><pre>";
$res2 = $mysqli->query("SHOW TRIGGERS WHERE `Table` = 'loan'");
if ($res2->num_rows == 0) {
    echo "Tidak ada trigger\n";
} else {
    while ($row = $res2->fetch_assoc()) {
        echo "Trigger: " . $row['Trigger'] . "\n";
        echo "Event  : " . $row['Event'] . "\n";
        echo "Timing : " . $row['Timing'] . "\n";
        echo "Statement:\n" . $row['Statement'] . "\n\n";
    }
}
echo "</pre>";

echo "<h3>3. Test insert ke loan (simulasi):</h3>";
$test = $mysqli->query("SELECT item_code FROM item LIMIT 1");
$item = $test->fetch_assoc();
$test2 = $mysqli->query("SELECT member_id FROM member LIMIT 1");
$member = $test2->fetch_assoc();

if ($item && $member) {
    $item_code = $mysqli->real_escape_string($item['item_code']);
    $member_id = $mysqli->real_escape_string($member['member_id']);
    $sql = "INSERT INTO loan (item_code, member_id, loan_date, due_date, renewed, is_lent, is_return, input_date, last_update, uid)
            VALUES ('$item_code', '$member_id', '2099-01-01', '2099-01-15', 0, 1, 0, NOW(), NOW(), 1)";
    if ($mysqli->query($sql)) {
        $loan_id = $mysqli->insert_id;
        echo "✅ Insert berhasil! loan_id=$loan_id<br>";
        // rollback
        $mysqli->query("DELETE FROM loan WHERE loan_id=$loan_id");
        echo "✅ Test data berhasil dihapus kembali<br>";
    } else {
        echo "❌ INSERT GAGAL: " . $mysqli->error . "<br>";
    }
} else {
    echo "Tidak ada item/member untuk ditest<br>";
}
