<?php
header('Content-Type: application/json');
include './db.php';
if (isset($_GET['ucourse_id']) && is_numeric($_GET['ucourse_id'])) {
    $ucourseId = (int)$_GET['ucourse_id'];
    $stmt = $pdo->prepare("SELECT id, name FROM sub_streams WHERE ucourse_id = ?");
    $stmt->execute([$ucourseId]);
    $subStreams = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($subStreams);
} else {
    echo json_encode([]);
}
?>