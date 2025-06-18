<?php
session_start();
if(!isset($_SESSION['handler']) || $_SESSION['handler'] != "university"){
    header("Location: ../index.php");
}
include '../../db.php';
$data = json_decode(file_get_contents("php://input"), true);
$realcourse_id = $data['realcourse_id'];
$content = $data['content'];
$stmt = $pdo->prepare("SELECT * FROM entrance_exams WHERE id = ?");
$stmt->execute([$realcourse_id]);
if ($stmt->fetch()) {
    $update = $pdo->prepare("UPDATE entrance_exams SET content = ? WHERE id = ?");
    $update->execute([$content, $realcourse_id]);
    echo "Content updated.";
}
