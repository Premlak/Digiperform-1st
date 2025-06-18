<?php
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
$data = json_decode(file_get_contents('php://input'), true);
$stateIds = $data['stateIds'] ?? [];

$query = "SELECT id, name FROM cities";
if (!empty($stateIds)) {
    $placeholders = implode(',', array_fill(0, count($stateIds), '?'));
    $query .= " WHERE state_id IN ($placeholders)";
}

$stmt = $pdo->prepare($query);
$stmt->execute($stateIds);
$cities = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($cities);
?>