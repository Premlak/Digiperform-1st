<?php
if (!isset($_GET['id']) || !isset($_GET['name'])) {
    header("Location: index.php");
    exit;
}
include 'db.php';
$conTent = $pdo->prepare("SELECT content FROM entrance_exams WHERE id = ?");
$conTent->execute([$_GET['id']]);
$row = $conTent->fetch(PDO::FETCH_ASSOC);
$contentOutput = '';
$exams = $pdo->prepare("SELECT id, name FROM questions WHERE exam_id = ?");
$exams->execute([$_GET['id']]);
$examList = $exams->fetchAll(PDO::FETCH_ASSOC);
if ($row && !empty(trim($row['content']))) {
    $contentOutput = $row['content'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= htmlspecialchars($_GET['name']); ?></title>
  <link rel="icon" href="./assets/logo.png" type="image/png"/>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    ::-webkit-scrollbar {
    display: none;
  }
  html {
    scrollbar-width: none;
  }
  body {
    overflow-x: visible;
  }
  </style>
</head>
<body class="relative w-full overflow-visible">
<?php include './nav.php'; ?>
<div class="min-h-screen p-4">
  <div class="flex justify-center align-center max-w-4xl mx-auto">
    <?php if (!empty($contentOutput)): ?>
      <?= $contentOutput ?>
    <?php else: ?>
      <p class="text-center">No content available for this entrance exam.</p>
    <?php endif; ?>
  </div>
  <div class="mb-6">
    <h2 class="text-center text-2xl font-semibold mb-4">List of Exams</h2>
    <div class="overflow-x-auto whitespace-nowrap space-x-4 flex px-4 py-2 scrollbar-thin scroll-smooth">
      <?php foreach ($examList as $exam): ?>
        <a href="test.php?id=<?= $exam['id'] ?>&name=<?=$exam['name'] ?>"
           class="inline-block min-w-[200px] max-w-xs bg-white rounded-xl shadow-md hover:bg-blue-500 hover:text-white transition-all duration-300 p-4 text-center text-lg font-medium cursor-pointer">
          <?= htmlspecialchars($exam['name']) ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php include './footer.php'; ?>
</body>
</html>
