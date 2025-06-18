<?php
session_start();
if(!isset($_SESSION['handler']) || $_SESSION['handler'] != "university"){
    header("Location: ../index.php");
}
require '../../db.php';
$stream_id = $_GET['stream_id'] ?? 0;
header('Content-Type: application/json');
if (!$stream_id) {
    echo json_encode([]);
    exit;
}
$stmt = $pdo->prepare("SELECT id, name FROM ucourses WHERE stream_id = ? ORDER BY name");
$stmt->execute([$stream_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($courses);
