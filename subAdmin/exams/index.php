<?php
session_start();
if (!isset($_SESSION['handler']) || $_SESSION['handler'] != "exams") {
    header("Location: ../index.php");
}
include '../../db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $exam_id = $_POST['exam_id'] ?? '';
    $update_id = $_POST['update_id'] ?? '';

    if ($name && $exam_id) {
        if ($update_id) {
            $stmt = $pdo->prepare("UPDATE questions SET name = ?, exam_id = ? WHERE id = ?");
            $stmt->execute([$name, $exam_id, $update_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO questions (name, exam_id) VALUES (?, ?)");
            $stmt->execute([$name, $exam_id]);
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$categories = $pdo->query("SELECT * FROM entrance_exam_categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$exams = $pdo->query("SELECT * FROM entrance_exams ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$questions = $pdo->query("
    SELECT questions.*, entrance_exams.name AS exam_name, entrance_exam_categories.name AS category_name, entrance_exam_categories.id AS category_id
    FROM questions 
    JOIN entrance_exams ON questions.exam_id = entrance_exams.id 
    JOIN entrance_exam_categories ON entrance_exams.category_id = entrance_exam_categories.id
    ORDER BY questions.id DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Manage Questions</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function filterExamsByCategory() {
            const categoryId = document.getElementById('category_select').value;
            const examOptions = document.querySelectorAll('#exam_select option');
            examOptions.forEach(option => {
                option.style.display = option.getAttribute('data-category') == categoryId ? 'block' : 'none';
            });
        }

        function editQuestion(id, name, exam_id, category_id) {
            document.getElementById('update_id').value = id;
            document.getElementById('name').value = name;
            document.getElementById('category_select').value = category_id;
            filterExamsByCategory();
            document.getElementById('exam_select').value = exam_id;
        }
    </script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-2xl mx-auto">
        <form method="POST" class="bg-white p-6 rounded shadow mb-6">
            <h2 class="text-xl font-bold mb-4">Add / Update Question</h2>
            <input type="hidden" name="update_id" id="update_id">

            <div class="mb-4">
                <label class="block text-gray-700">Exam Category</label>
                <select id="category_select" onchange="filterExamsByCategory()" class="w-full border px-4 py-2 rounded">
                    <option value="">-- Select Category --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700">Exam</label>
                <select name="exam_id" id="exam_select" class="w-full border px-4 py-2 rounded" required>
                    <option value="">-- Select Exam --</option>
                    <?php foreach ($exams as $exam): ?>
                        <option value="<?= $exam['id'] ?>" data-category="<?= $exam['category_id'] ?>">
                            <?= htmlspecialchars($exam['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700">Question Name</label>
                <input type="text" name="name" id="name" required class="w-full border px-4 py-2 rounded">
            </div>

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Submit</button>
        </form>

        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-xl font-bold mb-4">Questions</h2>
            <table class="w-full border">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="p-2 border">Question</th>
                        <th class="p-2 border">Exam</th>
                        <th class="p-2 border">Category</th>
                        <th class="p-2 border">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($questions as $q): ?>
                        <tr>
                            <td class="p-2 border"><?= htmlspecialchars($q['name']) ?></td>
                            <td class="p-2 border"><?= htmlspecialchars($q['exam_name']) ?></td>
                            <td class="p-2 border"><?= htmlspecialchars($q['category_name']) ?></td>
                            <td class="p-2 border">
                                <button onclick="editQuestion(
                                    <?= $q['id'] ?>, 
                                    '<?= htmlspecialchars($q['name'], ENT_QUOTES) ?>', 
                                    <?= $q['exam_id'] ?>, 
                                    <?= $q['category_id'] ?>
                                )" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">Edit</button>
                                <a href="?delete=<?= $q['id'] ?>" onclick="return confirm('Delete this question?')" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">Delete</a>
                                <a href="questions.php?id=<?= $q['id'] ?>"  class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">Questions</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($questions)): ?>
                        <tr><td colspan="4" class="text-center p-2 text-gray-500">No questions found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
