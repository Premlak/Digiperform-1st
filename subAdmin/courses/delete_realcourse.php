<?php
session_start();
if (!isset($_SESSION['handler']) || $_SESSION['handler'] !== "courses") {
    header("Location: ../index.php");
}
include '../../db.php';
$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT subcategory_id FROM realcourses WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch();
$pdo->query("DELETE FROM realcourses WHERE id=$id");
header("Location: realcourses.php?subcategory_id=" . $row['subcategory_id']);
