<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: index.php');
    exit;
}
require '../db.php';
$options = [
    'courses' => 'Courses & Streams',
    'exams' => 'Exams',
    // 'test_series' => 'Test Series',
    'counseling' => 'Career Counseling',
    'university' => 'College & University',
    'news' => "News"
];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $handler = $_POST['handler'] ?? '';
    if (isset($_POST['update_id']) && $_POST['update_id'] !== '') {
        $update_id = $_POST['update_id'];
        $stmt = $pdo->prepare("UPDATE subadmin SET email = ?, password = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$email, $password, $update_id]);
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM subadmin WHERE handler = ?");
        $stmt->execute([$handler]);
        if ($stmt->fetchColumn() == 0) {
            $stmt = $pdo->prepare("INSERT INTO subadmin (email, password, handler, updated_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$email, $password, $handler]);
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
if (isset($_GET['loginAs'])) {
    $_SESSION['subAdmin'] = $_GET['loginAs'];
    header("Location: home.php");
    exit;
}
$stmt = $pdo->query("SELECT * FROM subadmin ORDER BY id ASC LIMIT 5");
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Super Admin Table</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <?php include 'header.php'; ?>
    <div class="max-w-2xl mx-auto">
        <form method="POST" class="bg-white p-6 rounded shadow-md mb-6">
            <h2 class="text-xl font-bold mb-4">Create / Update Sub Admin</h2>
            <input type="hidden" name="update_id" id="update_id">
            <div class="mb-4">
                <label class="block text-gray-700">Email</label>
                <input type="email" name="email" id="email" required class="w-full px-4 py-2 border rounded" />
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Password</label>
                <input type="text" name="password" id="password" required class="w-full px-4 py-2 border rounded" />
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Enrolled For</label>
                <select name="handler" id="handler" required class="w-full px-4 py-2 border rounded">
                    <option value="">-- Select Option --</option>
                    <?php foreach ($options as $val => $label): ?>
                        <option value="<?= $val ?>"><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Submit</button>
        </form>
        <div class="bg-white p-6 rounded shadow-md">
            <h2 class="text-xl font-bold mb-4">Sub Admins</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left border whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="p-2 border">Email</th>
                            <th class="p-2 border">Password</th>
                            <th class="p-2 border">Enrolled For</th>
                            <th class="p-2 border">Updated At</th>
                            <th class="p-2 border">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($entries as $entry): ?>
                            <tr class="border-t">
                                <td class="p-2 border"><?= htmlspecialchars($entry['email']) ?></td>
                                <td class="p-2 border"><?= htmlspecialchars($entry['password']) ?></td>
                                <td class="p-2 border"><?= $options[$entry['handler']] ?? $entry['handler'] ?></td>
                                <td class="p-2 border"><?= $entry['updated_at'] ?></td>
                                <td class="p-2 border">
                                    <div class="flex space-x-2">
                                        <button onclick="editEntry(<?= $entry['id'] ?>, '<?= $entry['email'] ?>', '<?= $entry['password'] ?>', '<?= $entry['handler'] ?>')" class="bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-600">Update</button>
                                        <button onclick="redirectAsSubAdmin('<?= $entry['handler'] ?>')" class="bg-green-600 text-white px-2 py-1 rounded hover:bg-green-700" > Login As</button>

                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($entries)): ?>
                            <tr><td colspan="5" class="p-2 text-center text-gray-500">No entries yet</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        function editEntry(id, email, password, handler) {
            document.getElementById('update_id').value = id;
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
            document.getElementById('handler').value = handler;
        }
        function redirectAsSubAdmin(handler) {
        window.location.href = 'subadmin_redirect.php?handler=' + encodeURIComponent(handler);
        }
    </script>
</body>
</html>
