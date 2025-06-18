<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Career Idea</title>
  <link rel="icon" href="./assets/logo.png" type="image"/>
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
<div class="min-h-screen">
  <?php include './nav.php'; ?>
  <div>
    <div class="pe-3 pl-3">
    <?php include './universities.php';?>
    </div>
    <?php include './footer.php'; ?>
  </div>
  </div>
</div>
</body>
</html>
