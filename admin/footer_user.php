<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: index.php');
    exit;
}
require '../db.php';
$stmt = $pdo->prepare("SELECT email FROM footer_user ORDER BY id DESC");
$stmt->execute();
$emails = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Email Subscribers</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen py-10 px-4">
    <?php include 'header.php';  ?>
  <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md p-6">
    <h1 class="text-2xl font-bold mb-4 text-center text-blue-700">📧 Subscribed Emails</h1>
    <div class="overflow-x-auto">
      <table class="min-w-full border border-gray-300">
        <thead class="bg-blue-600 text-white">
          <tr>
            <th class="px-4 py-2 text-left">#</th>
            <th class="px-4 py-2 text-left">Email</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($emails) === 0): ?>
            <tr>
              <td colspan="3" class="px-4 py-4 text-center text-gray-500">No subscribers yet.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($emails as $index => $row): ?>
              <tr class="<?= $index % 2 === 0 ? 'bg-gray-100' : 'bg-white' ?>">
                <td class="px-4 py-2"><?= $index + 1 ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($row['email']) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</body>
</html>
