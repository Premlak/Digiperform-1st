<?php
if (!isset($_GET['id']) || !isset($_GET['title'])) {
    header("Location: index.php");
    exit;
}
include 'db.php';
$conTent = $pdo->prepare("SELECT content FROM news WHERE id = ?");
$conTent->execute([$_GET['id']]);
$row = $conTent->fetch(PDO::FETCH_ASSOC);
$contentOutput = '';
if ($row && !empty(trim($row['content']))) {
    $contentOutput = $row['content'];
}else{
    echo "<script>alert('No Data Uploaded For This Course Yet'); window.location.assign('./index.php');</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= htmlspecialchars($_GET['title']); ?></title>
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
<div class="min-h-screen">
  <div class="p-4 flex justify-center align-center content-center">
    <?php if (!empty($contentOutput)): ?>
      <?= $contentOutput ?>
    <?php endif; ?>
  </div>
</div>
<?php include './footer.php'; ?>
</body>
</html>
