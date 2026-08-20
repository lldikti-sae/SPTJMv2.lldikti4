<?php
$hostname = "127.0.0.1";
$username = "root";
$password = "";
$database = "lldikti4_sptjm";

$conn = new mysqli($hostname, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "ALTER TABLE s_tunjangan_kinerja ADD COLUMN No_SP2D VARCHAR(100) NULL, ADD COLUMN Tanggal_SP2D DATE NULL;";
if ($conn->query($sql) === TRUE) {
    echo "Columns added successfully";
} else {
    echo "Error adding columns: " . $conn->error;
}
$conn->close();
?>
