<?php
session_start();
if(!isset($_SESSION['handler']) || $_SESSION['handler'] != "university"){
    header("Location: ../index.php");
}
require '../../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $update_id = $_POST['update_id'] ?? '';

    if ($name !== '' && $category_id !== '') {
        if ($update_id !== '') {
            $stmt = $pdo->prepare("UPDATE entrance_exams SET name = ?, category_id = ? WHERE id = ?");
            $stmt->execute([$name, $category_id, $update_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO entrance_exams (name, category_id) VALUES (?, ?)");
            $stmt->execute([$name, $category_id]);
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM entrance_exams WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$categories = $pdo->query("SELECT * FROM entrance_exam_categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$exams = $pdo->query("SELECT entrance_exams.*, entrance_exam_categories.name AS category_name FROM entrance_exams JOIN entrance_exam_categories ON entrance_exams.category_id = entrance_exam_categories.id ORDER BY entrance_exams.id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Entrance Exams</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <?= include 'header.php';?>
<div class="max-w-xl mx-auto">
    <form method="POST" class="bg-white p-6 rounded shadow mb-6">
        <h2 class="text-xl font-bold mb-4">Add / Update Entrance Exam</h2>
        <input type="hidden" name="update_id" id="update_id">
        <div class="mb-4">
            <label class="block text-gray-700">Exam Name</label>
            <input type="text" name="name" id="name" required class="w-full border px-4 py-2 rounded" />
        </div>
        <div class="mb-4">
            <label class="block text-gray-700">Category</label>
            <select name="category_id" id="category_id" required class="w-full border px-4 py-2 rounded">
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Submit</button>
    </form>

    <div class="bg-white p-6 rounded shadow">
        <h2 class="text-xl font-bold mb-4">Existing Exams</h2>
        <table class="min-w-full border">
            <thead>
                <tr class="bg-gray-200 text-left">
                    <th class="p-2 border">Name</th>
                    <th class="p-2 border">Category</th>
                    <th class="p-2 border">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($exams as $exam): ?>
                    <tr>
                        <td class="p-2 border"><?= htmlspecialchars($exam['name']) ?></td>
                        <td class="p-2 border"><?= htmlspecialchars($exam['category_name']) ?></td>
                        <td class="p-2 border">
                            <button onclick="editExam(<?= $exam['id'] ?>, '<?= htmlspecialchars($exam['name']) ?>', <?= $exam['category_id'] ?>)" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">Edit</button>
                            <a href="?delete=<?= $exam['id'] ?>" onclick="return confirm('Delete this exam?')" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">Delete</a>
                            <a href="examContent.php?id=<?= $exam['id'] ?>" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">Upload Content</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($exams)): ?>
                    <tr><td colspan="3" class="text-center p-2 text-gray-500">No exams found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
    function editExam(id, name, categoryId) {
        document.getElementById('update_id').value = id;
        document.getElementById('name').value = name;
        document.getElementById('category_id').value = categoryId;
    }
</script>
</body>
</html>
