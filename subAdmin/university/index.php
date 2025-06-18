<?php
session_start();
if (!isset($_SESSION['handler']) || $_SESSION['handler'] != "university") {
    header("Location: ../index.php");
    exit;
}

include '../../db.php';
if (isset($_POST['delete_university_id'])) {
    $id = (int)$_POST['delete_university_id'];
    $stmt = $pdo->prepare("SELECT id FROM university_courses WHERE university_id = ?");
    $stmt->execute([$id]);
    $courseMappings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($courseMappings as $course) {
        $cid = $course['id'];
        $pdo->prepare("DELETE FROM university_course_program_types WHERE university_course_id = ?")->execute([$cid]);
        $pdo->prepare("DELETE FROM university_course_entrance_exams WHERE university_course_id = ?")->execute([$cid]);
        $pdo->prepare("DELETE FROM university_courses WHERE id = ?")->execute([$cid]);
    }

    // Then delete the university itself
    $pdo->prepare("DELETE FROM universities WHERE id = ?")->execute([$id]);

    header("Location: index.php");
    exit;
}
$universities_list = $pdo->query("
    SELECT u.*, s.name AS state_name, c.name AS city_name, a.name AS affiliation_name
    FROM universities u
    LEFT JOIN states s ON u.state_id = s.id
    LEFT JOIN cities c ON u.city_id = c.id
    LEFT JOIN affiliations a ON u.affiliation_id = a.id
    ORDER BY u.id DESC
")->fetchAll();

$affiliations = $pdo->query("SELECT id, name FROM affiliations ORDER BY name")->fetchAll();
$states = $pdo->query("SELECT id, name FROM states ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $affiliation_id = $_POST['affiliation_id'];
    $type = $_POST['type'];
    $category = $_POST['category'];
    $rank = $_POST['rank'];
    $state_id = $_POST['state_id'];
    $city_id = $_POST['city_id'];

    // === IMGBB Upload ===
    $image_url = '';
    if (!empty($_FILES['logo']['tmp_name'])) {
        $imageData = base64_encode(file_get_contents($_FILES['logo']['tmp_name']));
        $key = '936acbba24db61752a486d8f9164a081'; // replace with your real API key if needed

        $response = file_get_contents("https://api.imgbb.com/1/upload?key=$key", false, stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded",
                'content' => http_build_query(['image' => $imageData]),
            ]
        ]));

        $resData = json_decode($response, true);
        if (isset($resData['data']['url'])) {
            $image_url = $resData['data']['url'];
        } else {
            die("Image upload failed.");
        }
    }

    $stmt = $pdo->prepare("INSERT INTO universities (name, logo, affiliation_id, type, category, rank, state_id, city_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $image_url, $affiliation_id, $type, $category, $rank, $state_id, $city_id]);

    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Create University</title>
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
                citySelect.appendChild(opt);
            });
        }
    </script>
</head>

<body>
    <?= include 'header.php' ?>
    <div class="max-w-2xl mx-auto bg-white p-6 rounded shadow mt-6">
        <h2 class="text-xl font-bold mb-4">Create New University</h2>
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <input required name="name" class="w-full border p-2 rounded" placeholder="University Name">

            <input type="file" name="logo" accept="image/*" class="w-full border p-2 rounded" required>

            <select name="affiliation_id" class="w-full border p-2 rounded" required>
                <option disabled selected>Select Affiliation</option>
                <?php foreach ($affiliations as $a): ?>
                    <option value="<?= $a['id'] ?>"><?= $a['name'] ?></option>
                <?php endforeach; ?>
            </select>

            <select name="type" class="w-full border p-2 rounded" required>
                <option disabled selected>Select Type</option>
                <option value="Private">Private</option>
                <option value="Government">Government</option>
            </select>

            <select name="category" class="w-full border p-2 rounded" required>
                <option disabled selected>Select Category</option>
                <?php
                $catStmt = $pdo->query("SELECT name FROM categories");
                while ($cat = $catStmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value=\"" . htmlspecialchars($cat['name']) . "\">" . htmlspecialchars($cat['name']) . "</option>";
                }
                ?>
            </select>

            <input type="number" name="rank" class="w-full border p-2 rounded" placeholder="Rank" required>

            <select name="state_id" id="state_id" class="w-full border p-2 rounded" onchange="fetchCities()" required>
                <option disabled selected>Select State</option>
                <?php foreach ($states as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= $s['name'] ?></option>
                <?php endforeach; ?>
            </select>

            <select name="city_id" id="city_id" class="w-full border p-2 rounded" required>
                <option disabled selected>Select City</option>
            </select>

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Create University</button>
        </form>
    </div>
    <div class="mt-10">
        <h2 class="text-xl font-bold mb-4">All Universities</h2>
        <table class="w-full text-sm border border-gray-300">
            <thead class="bg-gray-200">
                <tr>
                    <th class="p-2 border">Logo</th>
                    <th class="p-2 border">Name</th>
                    <th class="p-2 border">Affiliation</th>
                    <th class="p-2 border">Type</th>
                    <th class="p-2 border">Category</th>
                    <th class="p-2 border">Rank</th>
                    <th class="p-2 border">State</th>
                    <th class="p-2 border">City</th>
                    <th class="p-2 border">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($universities_list as $u): ?>
                    <tr class="border-t">
                        <td class="p-2 border">
                            <?php if ($u['logo']): ?>
                                <img src="<?= htmlspecialchars($u['logo']) ?>" alt="Logo" class="h-10">
                            <?php endif; ?>
                        </td>
                        <td class="p-2 border"><?= htmlspecialchars($u['name']) ?></td>
                        <td class="p-2 border"><?= htmlspecialchars($u['affiliation_name']) ?></td>
                        <td class="p-2 border"><?= htmlspecialchars($u['type']) ?></td>
                        <td class="p-2 border"><?= htmlspecialchars($u['category']) ?></td>
                        <td class="p-2 border"><?= htmlspecialchars($u['rank']) ?></td>
                        <td class="p-2 border"><?= htmlspecialchars($u['state_name']) ?></td>
                        <td class="p-2 border"><?= htmlspecialchars($u['city_name']) ?></td>
                        <td class="p-2 border text-red-600 flex gap-2 justify-center">
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this university and all related courses?');">
                                <input type="hidden" name="delete_university_id" value="<?= $u['id'] ?>">
                                <button class="underline text-red-600">Delete</button>
                            </form>
                            <a href="edit_university.php?id=<?= $u['id'] ?>" class="underline text-blue-600">Edit</a>
                        </td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</body>

</html>