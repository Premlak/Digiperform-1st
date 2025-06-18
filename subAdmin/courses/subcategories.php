<?php
session_start();
if(!isset($_SESSION['handler']) || $_SESSION['handler'] != "courses"){
    header("Location: ../index.php");
}
include '../../db.php';
$course_id = $_GET['course_id'] ?? 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $stmt = $pdo->prepare("INSERT INTO subcategories (name, course_id) VALUES (?, ?)");
    $stmt->execute([$name, $course_id]);
}
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $pdo->query("DELETE FROM subcategories WHERE id=$id");
}
$result = $pdo->query("SELECT * FROM subcategories WHERE course_id=$course_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Sub Categories</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
<?php include 'header.php'; ?>
<div class="max-w-4xl mx-auto p-4">
  <h2 class="text-xl font-bold mb-4">Add Subcategory</h2>
  <form method="post" class="flex gap-2 mb-6">
    <input type="text" name="name" required placeholder="Subcategory Name" class="w-full px-4 py-2 border rounded-md">
    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Add</button>
  </form>
  <h2 class="text-xl font-bold mb-4">Subcategories</h2>
  <div class="overflow-x-auto bg-white rounded-md shadow">
    <table class="min-w-full text-sm text-left">
      <thead class="bg-gray-200">
        <tr>
          <th class="p-3">Name</th>
          <th class="p-3">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = $result->fetch()) { ?>
        <tr class="border-t">
          <td class="p-3"><?= htmlspecialchars($row['name']) ?></td>
          <td class="p-3 space-x-2">
            <a href="?course_id=<?= $course_id ?>&delete=<?= $row['id'] ?>" class="text-red-600">Delete</a>
            <a href="update_subcategory.php?id=<?= $row['id'] ?>" class="text-yellow-600">Update</a>
            <a href="realcourses.php?subcategory_id=<?= $row['id'] ?>" class="text-blue-600">Manage</a>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
