<?php
require '../../db.php';
header('Content-Type: application/json');
$courseId = $_GET['course_id'] ?? 0;
$stmt = $pdo->prepare("SELECT id, name FROM sub_streams WHERE ucourse_id = ?");
$stmt->execute([$courseId]);
$substreams = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($substreams);
