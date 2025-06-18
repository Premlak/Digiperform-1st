<?php
session_start();
if (!isset($_SESSION['handler']) || $_SESSION['handler'] !== "counseling") {
    header("Location: ../index.php");
}
require '../../db.php';

// Handle Create / Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $ccategory_id = $_POST['ccategory_id'];
    $update_id = $_POST['update_id'] ?? null;

    if ($update_id) {
        $stmt = $pdo->prepare("UPDATE subcCategory SET name = ?, ccategory_id = ? WHERE id = ?");
        $stmt->execute([$name, $ccategory_id, $update_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO subcCategory (name, ccategory_id) VALUES (?, ?)");
        $stmt->execute([$name, $ccategory_id]);
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM subcCategory WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch all categories
$categories = $pdo->query("SELECT * FROM ccategory ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all sub-categories
$subcategories = $pdo->query("
    SELECT subcCategory.*, ccategory.name AS parent_name
    FROM subcCategory
    LEFT JOIN ccategory ON subcCategory.ccategory_id = ccategory.id
    ORDER BY subcCategory.name ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Subcategories</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
<?= include 'header.php'; ?>
<div class="max-w-2xl mx-auto">
    <form method="POST" class="bg-white p-6 rounded shadow-md mb-6">
        <h2 class="text-xl font-bold mb-4">Add / Update Subcategory</h2>
        <input type="hidden" name="update_id" id="update_id">
        <div class="mb-4">
            <label class="block text-gray-700">Subcategory Name</label>
            <input type="text" name="name" id="name" required class="w-full px-4 py-2 border rounded" />
        </div>
        <div class="mb-4">
            <label class="block text-gray-700">Select Parent Category</label>
            <select name="ccategory_id" id="ccategory_id" required class="w-full px-4 py-2 border rounded">
                <option value="">-- Select --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Submit</button>
    </form>

    <div class="bg-white p-6 rounded shadow-md">
        <h2 class="text-xl font-bold mb-4">Subcategory List</h2>
        <table class="w-full border">
            <thead>
                <tr class="bg-gray-200">
                    <th class="p-2 border">Subcategory</th>
                    <th class="p-2 border">Parent</th>
                    <th class="p-2 border">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subcategories as $sub): ?>
                    <tr class="border-t">
                        <td class="p-2 border"><?= htmlspecialchars($sub['name']) ?></td>
                        <td class="p-2 border"><?= htmlspecialchars($sub['parent_name']) ?></td>
                        <td class="p-2 border">
                            <button onclick="editItem(<?= $sub['id'] ?>, '<?= htmlspecialchars($sub['name'], ENT_QUOTES) ?>', <?= $sub['ccategory_id'] ?>)" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">Edit</button>
                            <a href="?delete=<?= $sub['id'] ?>" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 ml-2" onclick="return confirm('Delete this subcategory?')">Delete</a>
                            <a href="childcCategory.php?id=<?=$sub['id']?>" class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 ml-2">Manage</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($subcategories)): ?>
                    <tr><td colspan="3" class="text-center p-2 text-gray-500">No subcategories yet</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function editItem(id, name, parentId) {
        document.getElementById('update_id').value = id;
        document.getElementById('name').value = name;
        document.getElementById('ccategory_id').value = parentId;
    }
</script>
</body>
</html>
