<?php
session_start();
if (!isset($_SESSION['handler']) || $_SESSION['handler'] !== "counseling") {
    header("Location: ../index.php");
}
require '../../db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $subcCategory_id = $_POST['subcCategory_id'];
    $update_id = $_POST['update_id'] ?? null;

    if ($update_id) {
        $stmt = $pdo->prepare("UPDATE childcCategory SET name = ?, subcCategory_id = ? WHERE id = ?");
        $stmt->execute([$name, $subcCategory_id, $update_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO childcCategory (name, subcCategory_id) VALUES (?, ?)");
        $stmt->execute([$name, $subcCategory_id]);
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM childcCategory WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
$subcategories = $pdo->query("
    SELECT subcCategory.*, ccategory.name AS parent_name
    FROM subcCategory
    LEFT JOIN ccategory ON subcCategory.ccategory_id = ccategory.id
    ORDER BY subcCategory.name ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all child categories
$childCategories = $pdo->query("
    SELECT childcCategory.*, subcCategory.name AS sub_name, ccategory.name AS parent_name
    FROM childcCategory
    LEFT JOIN subcCategory ON childcCategory.subcCategory_id = subcCategory.id
    LEFT JOIN ccategory ON subcCategory.ccategory_id = ccategory.id
    ORDER BY childcCategory.name ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Child Categories</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
<?= include 'header.php'; ?>
<div class="max-w-3xl mx-auto">
    <form method="POST" class="bg-white p-6 rounded shadow-md mb-6">
        <h2 class="text-xl font-bold mb-4">Add / Update Child Category</h2>
        <input type="hidden" name="update_id" id="update_id">
        <div class="mb-4">
            <label class="block text-gray-700">Child Category Name</label>
            <input type="text" name="name" id="name" required class="w-full px-4 py-2 border rounded" />
        </div>
        <div class="mb-4">
            <label class="block text-gray-700">Select Subcategory</label>
            <select name="subcCategory_id" id="subcCategory_id" required class="w-full px-4 py-2 border rounded">
                <option value="">-- Select --</option>
                <?php foreach ($subcategories as $sub): ?>
                    <option value="<?= $sub['id'] ?>">
                        <?= htmlspecialchars($sub['name']) ?> (<?= htmlspecialchars($sub['parent_name']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Submit</button>
    </form>

    <div class="bg-white p-6 rounded shadow-md">
        <h2 class="text-xl font-bold mb-4">Child Category List</h2>
        <table class="w-full border">
            <thead>
                <tr class="bg-gray-200">
                    <th class="p-2 border">Child Category</th>
                    <th class="p-2 border">Subcategory</th>
                    <th class="p-2 border">Main Category</th>
                    <th class="p-2 border">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($childCategories as $child): ?>
                    <tr class="border-t">
                        <td class="p-2 border"><?= htmlspecialchars($child['name']) ?></td>
                        <td class="p-2 border"><?= htmlspecialchars($child['sub_name']) ?></td>
                        <td class="p-2 border"><?= htmlspecialchars($child['parent_name']) ?></td>
                        <td class="p-2 border">
                            <button onclick="editItem(<?= $child['id'] ?>, '<?= htmlspecialchars($child['name'], ENT_QUOTES) ?>', <?= $child['subcCategory_id'] ?>)" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">Edit</button>
                            <a href="?delete=<?= $child['id'] ?>" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 ml-2" onclick="return confirm('Delete this child category?')">Delete</a>
                            <a href="realJob.php?id=<?=$child['id']?>" class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 ml-2">Manage</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($childCategories)): ?>
                    <tr><td colspan="4" class="text-center p-2 text-gray-500">No child categories yet</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
    function editItem(id, name, subId) {
        document.getElementById('update_id').value = id;
        document.getElementById('name').value = name;
        document.getElementById('subcCategory_id').value = subId;
    }
</script>
</body>
</html>
