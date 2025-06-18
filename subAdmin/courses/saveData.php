<?php
session_start();
if (!isset($_SESSION['handler']) || $_SESSION['handler'] != "courses") {
    http_response_code(403);
    echo "Unauthorized";
    exit;
}
include '../../db.php';

$data = json_decode(file_get_contents("php://input"), true);
$realcourse_id = $data['realcourse_id'];
$content = $data['content'];

// Check if record exists
$stmt = $pdo->prepare("SELECT * FROM coursedata WHERE realcourse_id = ?");
$stmt->execute([$realcourse_id]);

if ($stmt->fetch()) {
    // Update
    $update = $pdo->prepare("UPDATE coursedata SET content = ? WHERE realcourse_id = ?");
    $update->execute([$content, $realcourse_id]);
    echo "Content updated.";
} else {
    // Insert
    $insert = $pdo->prepare("INSERT INTO coursedata (realcourse_id, content) VALUES (?, ?)");
    $insert->execute([$realcourse_id, $content]);
    echo "Content saved.";
}
