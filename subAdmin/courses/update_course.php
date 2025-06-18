<?php
session_start();
if (!isset($_SESSION['handler']) || $_SESSION['handler'] !== "courses") {
    header("Location: ../index.php");
}
include '../../db.php';
$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$id]);
$course = $stmt->fetch();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $update = $pdo->prepare("UPDATE courses SET name = ? WHERE id = ?");
    $update->execute([$name, $id]);
    header("Location: index.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Update Course</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-gray-100 min-h-screen">
<?php include 'header.php'; ?>
<div class="max-w-xl mx-auto p-4">
  <h2 class="text-xl font-bold mb-4">Update Course</h2>
  <form method="post" class="space-y-4">
    <input type="text" name="name" value="<?= htmlspecialchars($course['name']) ?>" required class="w-full px-4 py-2 border rounded-md">
    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Update</button>
  </form>
</div>
</body>
</html>
