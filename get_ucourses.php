<?php
header('Content-Type: application/json');
include './db.php';
if (isset($_GET['stream_id']) && is_numeric($_GET['stream_id'])) {
    $streamId = (int)$_GET['stream_id'];
    $stmt = $pdo->prepare("SELECT id, name FROM ucourses WHERE stream_id = ?");
    $stmt->execute([$streamId]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($courses);
} else {
    echo json_encode([]);
}
?>