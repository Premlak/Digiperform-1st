<?php
session_start();
if (!isset($_SESSION['handler']) || $_SESSION['handler'] !== "courses") {
    header("Location: ../index.php");
}
include '../../db.php';
$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM realcourses WHERE id = ?");
$stmt->execute([$id]);
$course = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $duration = $_POST['duration'];
    $update = $pdo->prepare("UPDATE realcourses SET name = ?, duration = ? WHERE id = ?");
    $update->execute([$name, $duration, $id]);
    header("Location: realcourses.php?subcategory_id=" . $course['subcategory_id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Update Real Course</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
<?php include 'header.php'; ?>
<div class="max-w-xl mx-auto p-4">
  <h2 class="text-xl font-bold mb-4">Update Real Course</h2>
  <form method="post" class="space-y-4">
    <input type="text" name="name" value="<?= htmlspecialchars($course['name']) ?>" required class="w-full px-4 py-2 border rounded-md">
    <select name="duration" required class="w-full px-4 py-2 border rounded-md">
      <option disabled>Select Duration</option>
      <?php
      $durations = ["3 months", "6 months", "9 months", "1 year", "1.5 year", "2 year", "2.5 year", "3 year", "3.5 year", "4 year", "4.5 year", "5 year", "5.5 year", "6 years"];
      foreach ($durations as $d) {
          $selected = ($course['duration'] === $d) ? "selected" : "";
          echo "<option $selected>$d</option>";
      }
      ?>
    </select>
    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Update</button>
  </form>
</div>
</body>
</html>
