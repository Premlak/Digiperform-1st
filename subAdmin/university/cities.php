<?php
session_start();
if(!isset($_SESSION['handler']) || $_SESSION['handler'] != "university"){
    header("Location: ../index.php");
}
require '../../db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $state_id = $_POST['state_id'];
    $update_id = $_POST['update_id'] ?? null;

    if ($update_id) {
        $stmt = $pdo->prepare("UPDATE cities SET name = ?, state_id = ? WHERE id = ?");
        $stmt->execute([$name, $state_id, $update_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO cities (name, state_id) VALUES (?, ?)");
        $stmt->execute([$name, $state_id]);
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM cities WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
$states = $pdo->query("SELECT * FROM states ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$cities = $pdo->query("SELECT cities.*, states.name AS state_name FROM cities JOIN states ON cities.state_id = states.id ORDER BY cities.name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cities</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <?= include 'header.php';?>
    <div class="max-w-2xl mx-auto">
        <form method="POST" class="bg-white p-6 rounded shadow-md mb-6">
            <h2 class="text-xl font-bold mb-4">Add / Update City</h2>
            <input type="hidden" name="update_id" id="update_id">
            <div class="mb-4">
                <label class="block text-gray-700">City Name</label>
                <input type="text" name="name" id="name" required class="w-full px-4 py-2 border rounded" />
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Select State</label>
                <select name="state_id" id="state_id" required class="w-full px-4 py-2 border rounded">
                    <option value="">-- Select State --</option>
                    <?php foreach ($states as $state): ?>
                        <option value="<?= $state['id'] ?>"><?= htmlspecialchars($state['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Submit</button>
        </form>

        <div class="bg-white p-6 rounded shadow-md">
            <h2 class="text-xl font-bold mb-4">Cities List</h2>
            <table class="w-full border">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="p-2 border">City</th>
                        <th class="p-2 border">State</th>
                        <th class="p-2 border">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cities as $city): ?>
                        <tr class="border-t">
                            <td class="p-2 border"><?= htmlspecialchars($city['name']) ?></td>
                            <td class="p-2 border"><?= htmlspecialchars($city['state_name']) ?></td>
                            <td class="p-2 border">
                                <button onclick="editCity(<?= $city['id'] ?>, '<?= htmlspecialchars($city['name'], ENT_QUOTES) ?>', <?= $city['state_id'] ?>)" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">Edit</button>
                                <a href="?delete=<?= $city['id'] ?>" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 ml-2" onclick="return confirm('Delete this city?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($cities)): ?>
                        <tr><td colspan="3" class="text-center p-2 text-gray-500">No cities yet</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        function editCity(id, name, state_id) {
            document.getElementById('update_id').value = id;
            document.getElementById('name').value = name;
            document.getElementById('state_id').value = state_id;
        }
    </script>
</body>
</html>
