<?php
session_start();
if (!isset($_SESSION['handler']) || $_SESSION['handler'] !== "counseling") {
    header("Location: ../index.php");
}
require '../../db.php';

// Get child category ID
$childId = $_GET['id'] ?? null;
if (!$childId) {
    echo "Invalid child category ID.";
    exit;
}

// Handle Insert / Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $salary = trim($_POST['salary']);
    $qualification = trim($_POST['qualification']);
    $exam_required = trim($_POST['exam_required']);
    $update_id = $_POST['update_id'] ?? null;

    if ($update_id) {
        $stmt = $pdo->prepare("UPDATE realjob SET name = ?, salary = ?, qualification = ?, exam_required = ? WHERE id = ?");
        $stmt->execute([$name, $salary, $qualification, $exam_required, $update_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO realjob (name, salary, qualification, exam_required, childcCategory_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $salary, $qualification, $exam_required, $childId]);
    }
    header("Location: realJob.php?id=$childId");
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM realjob WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: realJob.php?id=$childId");
    exit;
}

// Fetch Entrance Exams
$exams = $pdo->query("SELECT name FROM entrance_exams")->fetchAll(PDO::FETCH_ASSOC);

// Fetch Jobs
$stmt = $pdo->prepare("SELECT * FROM realjob WHERE childcCategory_id = ?");
$stmt->execute([$childId]);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Real Jobs</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
<div class="max-w-4xl mx-auto">
    <form method="POST" class="bg-white p-6 rounded shadow-md mb-6">
        <h2 class="text-xl font-bold mb-4">Add / Update Real Job</h2>
        <input type="hidden" name="update_id" id="update_id">
        <div class="mb-4">
            <label class="block text-gray-700">Job Name</label>
            <input type="text" name="name" id="name" required class="w-full px-4 py-2 border rounded" />
        </div>
        <div class="mb-4">
            <label class="block text-gray-700">Salary</label>
            <input type="text" name="salary" id="salary" required class="w-full px-4 py-2 border rounded" />
        </div>
        <div class="mb-4">
            <label class="block text-gray-700">Qualification</label>
            <input type="text" name="qualification" id="qualification" required class="w-full px-4 py-2 border rounded" />
        </div>
        <div class="mb-4">
            <label class="block text-gray-700">Entrance Exam</label>
            <select name="exam_required" id="exam_required" required class="w-full px-4 py-2 border rounded">
                <option value="">-- Select Exam --</option>
                <?php foreach ($exams as $exam): ?>
                    <option value="<?= htmlspecialchars($exam['name']) ?>"><?= htmlspecialchars($exam['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Submit</button>
    </form>

    <div class="bg-white p-6 rounded shadow-md">
        <h2 class="text-xl font-bold mb-4">Jobs in This Category</h2>
        <table class="w-full border">
            <thead>
                <tr class="bg-gray-200">
                    <th class="p-2 border">Name</th>
                    <th class="p-2 border">Salary</th>
                    <th class="p-2 border">Qualification</th>
                    <th class="p-2 border">Exam Required</th>
                    <th class="p-2 border">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jobs as $job): ?>
                    <tr class="border-t">
                        <td class="p-2 border"><?= htmlspecialchars($job['name']) ?></td>
                        <td class="p-2 border"><?= htmlspecialchars($job['salary']) ?></td>
                        <td class="p-2 border"><?= htmlspecialchars($job['qualification']) ?></td>
                        <td class="p-2 border"><?= htmlspecialchars($job['exam_required']) ?></td>
                        <td class="p-2 border space-x-2">
                            <button onclick="editItem(
                                <?= $job['id'] ?>,
                                '<?= htmlspecialchars($job['name'], ENT_QUOTES) ?>',
                                '<?= htmlspecialchars($job['salary'], ENT_QUOTES) ?>',
                                '<?= htmlspecialchars($job['qualification'], ENT_QUOTES) ?>',
                                '<?= htmlspecialchars($job['exam_required'], ENT_QUOTES) ?>'
                            )" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">Edit</button>
                            <a href="realJob.php?id=<?= $childId ?>&delete=<?= $job['id'] ?>" onclick="return confirm('Delete this job?')" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Delete</a>
                            <a href="content.php?id=<?= $job['id'] ?>" class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">Manage</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($jobs)): ?>
                    <tr><td colspan="5" class="text-center text-gray-500 p-2">No jobs found for this category.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function editItem(id, name, salary, qualification, exam) {
        document.getElementById('update_id').value = id;
        document.getElementById('name').value = name;
        document.getElementById('salary').value = salary;
        document.getElementById('qualification').value = qualification;
        document.getElementById('exam_required').value = exam;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
</script>
</body>
</html>
