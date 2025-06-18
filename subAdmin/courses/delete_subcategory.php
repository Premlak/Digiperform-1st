<?php
session_start();
if (!isset($_SESSION['handler']) || $_SESSION['handler'] !== "courses") {
    header("Location: ../index.php");
}
include '../../db.php';
$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT course_id FROM subcategories WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch();
$pdo->query("DELETE FROM subcategories WHERE id=$id");
header("Location: subcategories.php?course_id=" . $row['course_id']);
