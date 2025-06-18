<?php
session_start();
if(!isset($_SESSION['handler']) || $_SESSION['handler'] != "university"){
    header("Location: ../index.php");
}
require '../../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $id = $_POST['id'] ?? '';

    if ($name !== '') {
        if ($id) {
            $stmt = $pdo->prepare("UPDATE affiliations SET name = ? WHERE id = ?");
            $stmt->execute([$name, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO affiliations (name) VALUES (?)");
            $stmt->execute([$name]);
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM affiliations WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$stmt = $pdo->query("SELECT * FROM affiliations ORDER BY id DESC");
$affiliations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Affiliations</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <?= include 'header.php'; ?>
    <div class="max-w-xl mx-auto">
        <div class="bg-white p-6 rounded shadow mb-6">
            <h2 class="text-xl font-bold mb-4">Add / Edit Affiliation</h2>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="id" id="id">
                <div>
                    <label class="block text-gray-700">Affiliation Name</label>
                    <input type="text" name="name" id="name" required class="w-full border px-4 py-2 rounded" />
                </div>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Submit</button>
            </form>
        </div>

        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-xl font-bold mb-4">Existing Affiliations</h2>
            <table class="min-w-full border">
                <thead>
                    <tr class="bg-gray-200 text-left">
                        <th class="p-2 border">ID</th>
                        <th class="p-2 border">Name</th>
                        <th class="p-2 border">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($affiliations as $a): ?>
                        <tr>
                            <td class="p-2 border"><?= $a['id'] ?></td>
                            <td class="p-2 border"><?= htmlspecialchars($a['name']) ?></td>
                            <td class="p-2 border">
                                <button onclick="editAffiliation(<?= $a['id'] ?>, '<?= htmlspecialchars($a['name']) ?>')" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">Edit</button>
                                <a href="?delete=<?= $a['id'] ?>" onclick="return confirm('Are you sure?')" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($affiliations)): ?>
                        <tr><td colspan="3" class="text-center p-2 text-gray-500">No affiliations found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function editAffiliation(id, name) {
            document.getElementById('id').value = id;
            document.getElementById('name').value = name;
        }
    </script>
</body>
</html>
