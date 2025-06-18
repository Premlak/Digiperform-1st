<?php
// Database connection (same as before)
$host = 'localhost';
$dbname = 'digiperform';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to database: " . $e->getMessage());
}
$data = json_decode(file_get_contents('php://input'), true);
$streamIds = $data['streamIds'] ?? [];

$query = "SELECT id, name FROM ucourses";
if (!empty($streamIds)) {
    $placeholders = implode(',', array_fill(0, count($streamIds), '?'));
    $query .= " WHERE stream_id IN ($placeholders)";
}

$stmt = $pdo->prepare($query);
$stmt->execute($streamIds);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($courses);
?>