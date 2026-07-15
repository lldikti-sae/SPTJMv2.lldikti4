<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=sptjmv2', 'root', '');
$stmt = $pdo->query("SELECT * FROM s_tunjangan_kinerja LIMIT 5");
file_put_contents('test.txt', print_r($stmt->fetchAll(PDO::FETCH_ASSOC), true));
