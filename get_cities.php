<?php
header('Content-Type: application/json');
include './db.php';
if (isset($_GET['state_id']) && is_numeric($_GET['state_id'])) {
    $stateId = (int)$_GET['state_id'];
    $stmt = $pdo->prepare("SELECT id, name FROM cities WHERE state_id = ?");
    $stmt->execute([$stateId]);
    $cities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($cities);
} else {
    echo json_encode([]);
}
?>