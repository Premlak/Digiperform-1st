<?php
session_start();
if(!isset($_SESSION['handler']) || $_SESSION['handler'] != "news"){
    header("Location: ../index.php");
}
include '../../db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $stmt = $pdo->prepare("INSERT INTO news_categories (name) VALUES (?)");
    $stmt->execute([$name]);
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $pdo->query("DELETE FROM news_categories WHERE id=$id");
}

$result = $pdo->query("SELECT * FROM news_categories");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>News Categories</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
<?php include 'header.php'; ?>
<div class="max-w-4xl mx-auto p-4">
  <h2 class="text-xl font-bold mb-4">Add News Category</h2>
  <form method="post" class="flex gap-2 mb-6">
    <input type="text" name="name" required placeholder="News Category Name" class="w-full px-4 py-2 border rounded-md">
    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Add</button>
  </form>
  <h2 class="text-xl font-bold mb-4">All News Categories</h2>
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
            <a href="?delete=<?= $row['id'] ?>" class="text-red-600">Delete</a>
            <a href="update_news_category.php?id=<?= $row['id'] ?>" class="text-yellow-600">Update</a>
            <a href="news.php?category_id=<?= $row['id'] ?>" class="text-blue-600">Manage</a>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
