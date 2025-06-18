<?php
session_start();
if(!isset($_SESSION['handler']) || $_SESSION['handler'] != "university"){
    header("Location: ../index.php");
}
require '../../db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $update_id = $_POST['update_id'] ?? null;

    if ($update_id) {
        $stmt = $pdo->prepare("UPDATE states SET name = ? WHERE id = ?");
        $stmt->execute([$name, $update_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO states (name) VALUES (?)");
        $stmt->execute([$name]);
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM states WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch all states
$states = $pdo->query("SELECT * FROM states ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>States</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <?= include 'header.php';?>
    <div class="max-w-xl mx-auto">
        <form method="POST" class="bg-white p-6 rounded shadow-md mb-6">
            <h2 class="text-xl font-bold mb-4">Add / Update State</h2>
            <input type="hidden" name="update_id" id="update_id">
            <div class="mb-4">
                <label class="block text-gray-700">State Name</label>
                <input type="text" name="name" id="name" required class="w-full px-4 py-2 border rounded" />
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Submit</button>
        </form>

        <div class="bg-white p-6 rounded shadow-md">
            <h2 class="text-xl font-bold mb-4">States List</h2>
            <table class="w-full border">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="p-2 border">State</th>
                        <th class="p-2 border">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($states as $state): ?>
                        <tr class="border-t">
                            <td class="p-2 border"><?= htmlspecialchars($state['name']) ?></td>
                            <td class="p-2 border">
                                <button onclick="editState(<?= $state['id'] ?>, '<?= htmlspecialchars($state['name'], ENT_QUOTES) ?>')" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">Edit</button>
                                <a href="?delete=<?= $state['id'] ?>" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 ml-2" onclick="return confirm('Delete this state?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($states)): ?>
                        <tr><td colspan="2" class="text-center p-2 text-gray-500">No states yet</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function editState(id, name) {
            document.getElementById('update_id').value = id;
            document.getElementById('name').value = name;
        }
    </script>
</body>
</html>
