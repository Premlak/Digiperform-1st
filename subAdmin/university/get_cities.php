<?php
require '../../db.php';
$state_id = $_GET['state_id'] ?? 0;
$cities = [];

if ($state_id) {
    $stmt = $pdo->prepare("SELECT id, name FROM cities WHERE state_id = ?");
    $stmt->execute([$state_id]);
    $cities = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

header('Content-Type: application/json');
echo json_encode($cities);
