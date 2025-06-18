<?php
session_start();
if(!isset($_SESSION['handler']) || $_SESSION['handler'] != "university"){
    header("Location: ../index.php");
}
require '../../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
    $stmt->execute([$_POST['name']]);
}
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: manage_categories.php");
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_id'])) {
    $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?");
    $stmt->execute([$_POST['update_name'], $_POST['update_id']]);
    header("Location: manage_categories.php");
}
$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Categories</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 p-6">
    <?= include 'header.php';?>
    <div class="max-w-2xl mx-auto bg-white shadow-lg rounded-xl p-6">
        <h2 class="text-2xl font-bold mb-4">Add Category</h2>
        <form method="POST" class="mb-6 flex gap-4">
            <input type="text" name="name" required placeholder="Enter category name" class="border border-gray-300 p-2 rounded w-full">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Add</button>
        </form>

        <hr class="my-6">

        <h2 class="text-xl font-semibold mb-4">All Categories</h2>
        <table class="w-full table-auto border-collapse">
            <thead>
                <tr class="bg-gray-200">
                    <th class="p-3 border">ID</th>
                    <th class="p-3 border">Name</th>
                    <th class="p-3 border">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                <tr class="hover:bg-gray-100">
                    <td class="p-3 border text-center"><?= htmlspecialchars($cat['id']) ?></td>
                    <td class="p-3 border">
                        <form method="POST" class="flex gap-2">
                            <input type="hidden" name="update_id" value="<?= $cat['id'] ?>">
                            <input type="text" name="update_name" value="<?= htmlspecialchars($cat['name']) ?>" class="border border-gray-300 p-1 rounded w-full">
                            <button type="submit" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">Update</button>
                        </form>
                    </td>
                    <td class="p-3 border text-center">
                        <a href="?delete=<?= $cat['id'] ?>" onclick="return confirm('Delete this category?')" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</body>
</html>
