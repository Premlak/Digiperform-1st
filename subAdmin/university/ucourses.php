<?php
session_start();
if(!isset($_SESSION['handler']) || $_SESSION['handler'] != "university"){
    header("Location: ../index.php");
}
include '../../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $type = $_POST['type'] ?? '';
    $stream_id = $_POST['stream_id'] ?? '';
    $update_id = $_POST['update_id'] ?? '';

    if ($name && $type && $stream_id) {
        if ($update_id) {
            $stmt = $pdo->prepare("UPDATE ucourses SET name = ?, type = ?, stream_id = ? WHERE id = ?");
            $stmt->execute([$name, $type, $stream_id, $update_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO ucourses (name, type, stream_id) VALUES (?, ?, ?)");
            $stmt->execute([$name, $type, $stream_id]);
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM ucourses WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$streams = $pdo->query("SELECT * FROM streams ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

$courses = $pdo->query("SELECT ucourses.*, streams.name AS stream_name FROM ucourses 
                        JOIN streams ON ucourses.stream_id = streams.id 
                        ORDER BY ucourses.id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Courses</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <?= include 'header.php';?>
<div class="max-w-2xl mx-auto">
    <form method="POST" class="bg-white p-6 rounded shadow mb-6">
        <h2 class="text-xl font-bold mb-4">Add / Update Course</h2>
        <input type="hidden" name="update_id" id="update_id">
        <div class="mb-4">
            <label class="block text-gray-700">Course Name</label>
            <input type="text" name="name" id="name" required class="w-full border px-4 py-2 rounded" />
        </div>
        <div class="mb-4">
            <label class="block text-gray-700">Course Type</label>
            <select name="type" id="type" required class="w-full border px-4 py-2 rounded">
                <option value="">-- Select --</option>
                <option value="degree">Degree</option>
                <option value="diploma">Diploma</option>
                <option value="certificate">Certificate</option>
            </select>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700">Stream</label>
            <select name="stream_id" id="stream_id" required class="w-full border px-4 py-2 rounded">
                <option value="">-- Select Stream --</option>
                <?php foreach ($streams as $stream): ?>
                    <option value="<?= $stream['id'] ?>"><?= htmlspecialchars($stream['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Submit</button>
    </form>

    <div class="bg-white p-6 rounded shadow">
        <h2 class="text-xl font-bold mb-4">Courses</h2>
        <table class="w-full border">
            <thead>
                <tr class="bg-gray-200 text-left">
                    <th class="p-2 border">Name</th>
                    <th class="p-2 border">Type</th>
                    <th class="p-2 border">Stream</th>
                    <th class="p-2 border">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $course): ?>
                    <tr>
                        <td class="p-2 border"><?= htmlspecialchars($course['name']) ?></td>
                        <td class="p-2 border"><?= $course['type'] ?></td>
                        <td class="p-2 border"><?= htmlspecialchars($course['stream_name']) ?></td>
                        <td class="p-2 border">
                            <button onclick="editCourse(<?= $course['id'] ?>, '<?= htmlspecialchars($course['name']) ?>', '<?= $course['type'] ?>', <?= $course['stream_id'] ?>)" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">Edit</button>
                            <a href="?delete=<?= $course['id'] ?>" onclick="return confirm('Delete this course?')" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($courses)): ?>
                    <tr><td colspan="4" class="text-center p-2 text-gray-500">No courses found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function editCourse(id, name, type, streamId) {
        document.getElementById('update_id').value = id;
        document.getElementById('name').value = name;
        document.getElementById('type').value = type;
        document.getElementById('stream_id').value = streamId;
    }
</script>
</body>
</html>
