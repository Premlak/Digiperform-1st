<?php
session_start();
if (!isset($_SESSION['handler']) || $_SESSION['handler'] !== "news") {
    http_response_code(403);
    echo "Unauthorized access.";
    exit;
}

include '../../db.php';

// Read JSON input
$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['id'], $input['title'], $input['content'])) {
    http_response_code(400);
    echo "Invalid request.";
    exit;
}

$id = $input['id'];
$title = trim($input['title']);
$content = trim($input['content']);

// Validate
if ($id <= 0 || $title === "" || $content === "") {
    http_response_code(422);
    echo "All fields are required.";
    exit;
}

// Update the news entry
$stmt = $pdo->prepare("UPDATE news SET title = ?, content = ? WHERE id = ?");
if ($stmt->execute([$title, $content, $id])) {
    echo "News updated successfully.";
} else {
    http_response_code(500);
    echo "Failed to update news.";
}
