<?php
session_start();
if(!isset($_SESSION['handler']) || $_SESSION['handler'] != "university"){
    header("Location: ../index.php");
}
require '../../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $duration = $_POST['duration'] ?? '';
    $update_id = $_POST['update_id'] ?? '';

    if ($duration) {
        if ($update_id) {
            $stmt = $pdo->prepare("UPDATE course_durations SET duration = ? WHERE id = ?");
            $stmt->execute([$duration, $update_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO course_durations (duration) VALUES (?)");
            $stmt->execute([$duration]);
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM course_durations WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$durations = $pdo->query("SELECT * FROM course_durations ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Course Durations</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <?= include 'header.php';?>
<div class="max-w-xl mx-auto">
    <form method="POST" class="bg-white p-6 rounded shadow mb-6">
        <h2 class="text-xl font-bold mb-4">Add / Update Course Duration</h2>
        <input type="hidden" name="update_id" id="update_id">
        <div class="mb-4">
            <label class="block text-gray-700">Duration (e.g. 3 Months)</label>
            <input type="text" name="duration" id="duration" required class="w-full border px-4 py-2 rounded" />
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Submit</button>
    </form>

    <div class="bg-white p-6 rounded shadow">
        <h2 class="text-xl font-bold mb-4">Durations</h2>
        <table class="w-full border">
            <thead>
                <tr class="bg-gray-200 text-left">
                    <th class="p-2 border">Duration</th>
                    <th class="p-2 border">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($durations as $duration): ?>
                    <tr>
                        <td class="p-2 border"><?= htmlspecialchars($duration['duration']) ?></td>
                        <td class="p-2 border">
                            <button onclick="editDuration(<?= $duration['id'] ?>, '<?= htmlspecialchars($duration['duration']) ?>')" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">Edit</button>
                            <a href="?delete=<?= $duration['id'] ?>" onclick="return confirm('Delete this duration?')" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($durations)): ?>
                    <tr><td colspan="2" class="p-2 text-center text-gray-500">No durations found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function editDuration(id, duration) {
        document.getElementById('update_id').value = id;
        document.getElementById('duration').value = duration;
    }
</script>
</body>
</html>
