<?php
session_start();
if(!isset($_SESSION['handler']) || $_SESSION['handler'] != "university"){
    header("Location: ../index.php");
}
include '../../db.php';

$id = $_GET['id'] ?? 0;
if (!$id) die('No university ID provided.');

$stmt = $pdo->prepare("SELECT * FROM universities WHERE id = ?");
$stmt->execute([$id]);
$uni = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$uni) die('University not found.');

$affiliations = $pdo->query("SELECT id, name FROM affiliations ORDER BY name")->fetchAll();
$states = $pdo->query("SELECT id, name FROM states ORDER BY name")->fetchAll();
$cities = $pdo->prepare("SELECT id, name FROM cities WHERE state_id = ?");
$cities->execute([$uni['state_id']]);
$cities = $cities->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $affiliation_id = $_POST['affiliation_id'];
    $type = $_POST['type'];
    $category = $_POST['category'];
    $rank = $_POST['rank'];
    $state_id = $_POST['state_id'];
    $city_id = $_POST['city_id'];

    // Logo logic (optional)
    $logo_url = $uni['logo'];
    if (!empty($_FILES['logo']['tmp_name'])) {
        $imageData = base64_encode(file_get_contents($_FILES['logo']['tmp_name']));
        $key = '936acbba24db61752a486d8f9164a081';
        $response = file_get_contents("https://api.imgbb.com/1/upload?key=$key", false, stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded",
                'content' => http_build_query(['image' => $imageData]),
            ]
        ]));
        $resData = json_decode($response, true);
        if (isset($resData['data']['url'])) {
            $logo_url = $resData['data']['url'];
        }
    }

    $update = $pdo->prepare("UPDATE universities SET name=?, logo=?, affiliation_id=?, type=?, category=?, rank=?, state_id=?, city_id=? WHERE id=?");
    $update->execute([$name, $logo_url, $affiliation_id, $type, $category, $rank, $state_id, $city_id, $id]);

    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit University</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    async function fetchCities() {
        const stateId = document.getElementById('state_id').value;
        const res = await fetch(`./get_cities.php?state_id=${stateId}`);
        const cities = await res.json();
        const citySelect = document.getElementById('city_id');
        citySelect.innerHTML = "";
        cities.forEach(city => {
            const opt = document.createElement("option");
            opt.value = city.id;
            opt.textContent = city.name;
            if (city.id == <?= json_encode($uni['city_id']) ?>) opt.selected = true;
            citySelect.appendChild(opt);
        });
    }
    </script>
</head>
<body class="p-8">
    <?= include'header.php';?>
    <div class="max-w-2xl mx-auto bg-white p-6 rounded shadow">
        <h2 class="text-xl font-bold mb-4">Edit University</h2>
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <input name="name" class="w-full border p-2 rounded" value="<?= htmlspecialchars($uni['name']) ?>" required>

            <input type="file" name="logo" accept="image/*" class="w-full border p-2 rounded">
            <?php if ($uni['logo']): ?>
                <img src="<?= $uni['logo'] ?>" class="h-10">
            <?php endif; ?>

            <select name="affiliation_id" class="w-full border p-2 rounded" required>
                <option disabled>Select Affiliation</option>
                <?php foreach ($affiliations as $a): ?>
                    <option value="<?= $a['id'] ?>" <?= $a['id'] == $uni['affiliation_id'] ? 'selected' : '' ?>><?= $a['name'] ?></option>
                <?php endforeach; ?>
            </select>

            <select name="type" class="w-full border p-2 rounded" required>
    <option disabled selected>Select Type</option>
    <option value="Private" <?= $uni['type'] === 'private' ? 'selected' : '' ?>>Private</option>
    <option value="Government" <?= $uni['type'] === 'government' ? 'selected' : '' ?>>Government</option>
</select>


            <select name="category" class="w-full border p-2 rounded" required>
                <?php
                $catStmt = $pdo->query("SELECT name FROM categories");
                while ($cat = $catStmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value=\"" . htmlspecialchars($cat['name']) . "\">" . htmlspecialchars($cat['name']) . "</option>";
                }
                ?>
            </select>

            <input type="number" name="rank" class="w-full border p-2 rounded" value="<?= $uni['rank'] ?>" required>

            <select name="state_id" id="state_id" class="w-full border p-2 rounded" onchange="fetchCities()" required>
                <?php foreach ($states as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= $s['id'] == $uni['state_id'] ? 'selected' : '' ?>><?= $s['name'] ?></option>
                <?php endforeach; ?>
            </select>

            <select name="city_id" id="city_id" class="w-full border p-2 rounded" required>
                <?php foreach ($cities as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $c['id'] == $uni['city_id'] ? 'selected' : '' ?>><?= $c['name'] ?></option>
                <?php endforeach; ?>
            </select>

            <button class="bg-blue-600 text-white px-4 py-2 rounded">Update</button>
        </form>
    </div>
</body>
</html>
