<?php
session_start();
if(!isset($_SESSION['handler']) || $_SESSION['handler'] != "university"){
    header("Location: ../index.php");
}
require '../../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';

    if ($name) {
        $stmt = $pdo->prepare("INSERT INTO program_types (name) VALUES (?)");
        $stmt->execute([$name]);
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM program_types WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$programs = $pdo->query("SELECT * FROM program_types ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Program Types</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <?= include 'header.php';?>
<div class="max-w-xl mx-auto">
    <form method="POST" class="bg-white p-6 rounded shadow mb-6">
        <h2 class="text-xl font-bold mb-4">Add Program Type</h2>
        <div class="mb-4">
            <label class="block text-gray-700">Program Type (e.g. Full Time)</label>
            <input type="text" name="name" required class="w-full border px-4 py-2 rounded" />
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Submit</button>
    </form>

    <div class="bg-white p-6 rounded shadow">
        <h2 class="text-xl font-bold mb-4">Program Types</h2>
        <table class="w-full border">
            <thead>
                <tr class="bg-gray-200 text-left">
                    <th class="p-2 border">Name</th>
                    <th class="p-2 border">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($programs as $program): ?>
                    <tr>
                        <td class="p-2 border"><?= htmlspecialchars($program['name']) ?></td>
                        <td class="p-2 border">
                            <a href="?delete=<?= $program['id'] ?>" onclick="return confirm('Delete this program type?')" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($programs)): ?>
                    <tr><td colspan="2" class="text-center p-2 text-gray-500">No program types found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
