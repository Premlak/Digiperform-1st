<?php
// --- DATABASE STRUCTURE ---
// news_categories(id, name)
// news(id, title, content, category_id)

// --- PAGE 1: news.php (List + Add + Delete News) ---

session_start();
if (!isset($_SESSION['handler']) || $_SESSION['handler'] !== "news") {
    header("Location: ../index.php");
}
include '../../db.php';

$category_id = $_GET['category_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $pdo->prepare("INSERT INTO news (title, content, category_id) VALUES (?, '', ?)")->execute([$title, $category_id]);
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $pdo->prepare("DELETE FROM news WHERE id = ?")->execute([$id]);
}

$stmt = $pdo->prepare("SELECT * FROM news WHERE category_id = ?");
$stmt->execute([$category_id]);
$news = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Manage News</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-gray-100 min-h-screen">
    
<?php include 'header.php'; ?>
<div class="max-w-4xl mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4">Manage News</h2>
    <form method="post" class="flex gap-2 mb-6">
        <input type="text" name="title" required placeholder="News Title" class="w-full px-4 py-2 border rounded-md">
        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md">Add</button>
    </form>
    <div class="bg-white shadow rounded-md">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-200">
                <tr><th class="p-3">Title</th><th class="p-3">Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($news as $n) { ?>
                <tr class="border-t">
                    <td class="p-3"><?= htmlspecialchars($n['title']) ?></td>
                    <td class="p-3 space-x-2">
                        <a href="update_news.php?id=<?= $n['id'] ?>" class="text-yellow-600">Edit</a>
                        <a href="news.php?category_id=<?= $category_id ?>&delete=<?= $n['id'] ?>" class="text-red-600">Delete</a>
                        <a href="newscontent.php?id=<?= $n['id'] ?>" class="text-blue-600">Manage Content</a>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
