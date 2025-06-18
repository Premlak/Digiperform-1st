<?php
session_start();
if(!isset($_SESSION['handler']) || $_SESSION['handler'] !== "counseling"){
    header("Location: ../index.php");
}
require '../../db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $update_id = $_POST['update_id'] ?? null;

    if ($update_id) {
        $stmt = $pdo->prepare("UPDATE ccategory SET name = ? WHERE id = ?");
        $stmt->execute([$name, $update_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO ccategory (name) VALUES (?)");
        $stmt->execute([$name]);
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM ccategory WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
$categories = $pdo->query("SELECT * FROM ccategory ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Categories</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
<?= include 'header.php'; ?>
<div class="max-w-2xl mx-auto">
    <form method="POST" class="bg-white p-6 rounded shadow-md mb-6">
        <h2 class="text-xl font-bold mb-4">Add / Update Category</h2>
        <input type="hidden" name="update_id" id="update_id">
        <div class="mb-4">
            <label class="block text-gray-700">Category Name</label>
            <input type="text" name="name" id="name" required class="w-full px-4 py-2 border rounded" />
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Submit</button>
    </form>
    <div class="bg-white p-6 rounded shadow-md">
        <h2 class="text-xl font-bold mb-4">Category List</h2>
        <table class="w-full border">
            <thead>
                <tr class="bg-gray-200">
                    <th class="p-2 border">Name</th>
                    <th class="p-2 border">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                    <tr class="border-t">
                        <td class="p-2 border"><?= htmlspecialchars($cat['name']) ?></td>
                        <td class="p-2 border">
                            <button onclick="editCategory(<?= $cat['id'] ?>, '<?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>')" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">Edit</button>
                            <a href="?delete=<?= $cat['id'] ?>" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 ml-2" onclick="return confirm('Delete this category?')">Delete</a>
                            <a href="subcCategory.php?id=<?=$cat['id']?>" class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 ml-2">Manage</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($categories)): ?>
                    <tr><td colspan="2" class="text-center p-2 text-gray-500">No categories yet</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
    function editCategory(id, name) {
        document.getElementById('update_id').value = id;
        document.getElementById('name').value = name;
    }
</script>
</body>
</html>
