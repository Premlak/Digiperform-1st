<?php
session_start();
if (!isset($_SESSION['handler']) || $_SESSION['handler'] !== "counseling") {
    header("Location: ../index.php");
}
include '../../db.php';
$input = json_decode(file_get_contents("php://input"), true);
if (!isset($input['id'], $input['content'])) {
    http_response_code(400);
    echo "Invalid request.";
    exit;
}
$id = $input['id'];
$content = trim($input['content']);
if ($id <= 0 || $content === "") {
    http_response_code(422);
    echo "All fields are required.";
    exit;
}
$stmt = $pdo->prepare("UPDATE realjob SET content = ? WHERE id = ?");
if ($stmt->execute([$content,$id])) {
    echo "Data updated successfully.";
} else {
    http_response_code(500);
    echo "Failed to update news.";
}
