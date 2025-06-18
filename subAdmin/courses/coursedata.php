<?php
session_start();
if (!isset($_SESSION['handler']) || $_SESSION['handler'] != "courses") {
    header("Location: ../index.php");
}
include '../../db.php';

$realcourse_id = $_GET['realcourse_id'] ?? 0;
$content = "";
$stmt = $pdo->prepare("SELECT * FROM coursedata WHERE realcourse_id = ?");
$stmt->execute([$realcourse_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row) {
    $content = $row['content'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Course Content</title>
    <link href="https://cdn.jsdelivr.net/npm/froala-editor@4.1.4/css/froala_editor.pkgd.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/froala-editor@4.1.4/js/froala_editor.pkgd.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Edit Course Content</h1>

        <div class="bg-white shadow-md rounded-lg p-4 border border-gray-200">
            <div id="editor"><?= $content ?></div>
        </div>

        <div class="mt-6 text-right">
            <button id="saveBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-md shadow">
                Save
            </button>
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
                        const imageFile = files[0];
                        const formData = new FormData();
                        formData.append('image', imageFile);
                        fetch("https://api.imgbb.com/1/upload?key=936acbba24db61752a486d8f9164a081", {
                            method: "POST",
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const imageUrl = data.data.url;
                                editor.image.insert(imageUrl, null, null, editor.image.get());
                            } else {
                                alert("Image upload failed.");
                            }
                        })
                        .catch(err => {
                            console.error("Upload error", err);
                            alert("Error uploading image.");
                        });
                    }
                    return false;
                }
            }
        }, function () {
            console.log("Editor is ready");
        });
        document.getElementById('saveBtn').addEventListener('click', function () {
            const content = editorInstance.html.get();
            const realcourse_id = <?= json_encode($realcourse_id) ?>;

            fetch("saveData.php", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ realcourse_id, content })
            })
            .then(res => res.text())
            .then(data => {
                alert(data);
            })
            .catch(err => {
                alert("Error saving data.");
                console.error(err);
            });
        });
    });
</script>
</body>
</html>
