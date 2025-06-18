<?php
session_start();
if (!isset($_SESSION['handler']) || $_SESSION['handler'] !== "news") {
    header("Location: ../index.php");
}
include '../../db.php';

$news_id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
$stmt->execute([$news_id]);
$row = $stmt->fetch();

if (!$row) {
    die("News not found.");
}

$title = $row['title'];
$content = $row['content'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit News Content</title>
  <link href="https://cdn.jsdelivr.net/npm/froala-editor@4.1.4/css/froala_editor.pkgd.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/froala-editor@4.1.4/js/froala_editor.pkgd.min.js"></script>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    
<?php include 'header.php'; ?>
<div class="max-w-4xl mx-auto py-12 px-4">
  <h1 class="text-2xl font-bold text-gray-800 mb-4">Edit News Content</h1>
  <div class="bg-white rounded-lg p-4 shadow">
    <input type="text" id="newsTitle" value="<?= htmlspecialchars($title) ?>" class="w-full px-4 py-2 border mb-4 rounded-md" placeholder="News Title">
    <div id="editor"><?= $content ?></div>
  </div>
  <div class="mt-6 text-right">
    <button id="saveBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-md shadow">Save</button>
  </div>
</div>

<script>
  let editorInstance;

  document.addEventListener("DOMContentLoaded", function () {
    editorInstance = new FroalaEditor('#editor', {
      imageUpload: true,
      imageAllowedTypes: ['jpeg', 'jpg', 'png', 'gif'],
      events: {
        'image.beforeUpload': function (files) {
          if (files.length) {
            const editor = this;
            const formData = new FormData();
            formData.append('image', files[0]);

            fetch("https://api.imgbb.com/1/upload?key=936acbba24db61752a486d8f9164a081", {
              method: "POST",
              body: formData
            })
            .then(res => res.json())
            .then(data => {
              if (data.success) {
                editor.image.insert(data.data.url);
              } else {
                alert("Upload failed.");
              }
            })
            .catch(() => alert("Error uploading image."));
          }
          return false;
        }
      }
    });

    document.getElementById('saveBtn').addEventListener('click', function () {
      const content = editorInstance.html.get();
      const title = document.getElementById('newsTitle').value;
      const id = <?= json_encode($news_id) ?>;

      fetch("saveNews.php", {
        method: "POST",
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, title, content })
      })
      .then(res => res.text())
      .then(data => alert(data))
      .catch(err => {
        alert("Error saving data.");
        console.error(err);
      });
    });
  });
</script>
</body>
</html>
