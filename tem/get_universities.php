<?php
// Database connection
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
$filters = $data['filters'] ?? [];
$sort = $data['sort'] ?? 'rank';
$direction = $data['direction'] ?? 'ASC';
$direction = in_array(strtoupper($direction), ['ASC', 'DESC']) ? $direction : 'ASC';

// Build base query
$query = "SELECT SQL_CALC_FOUND_ROWS 
            u.id, u.name, u.logo, u.rank, 
            a.name AS affiliation, u.category,
            s.name AS state, c.name AS city,
            GROUP_CONCAT(DISTINCT str.name) AS streams,
            GROUP_CONCAT(DISTINCT CONCAT(ucrs.id, '|', ucrs.name)) AS courses
          FROM universities u
          LEFT JOIN affiliations a ON u.affiliation_id = a.id
          LEFT JOIN states s ON u.state_id = s.id
          LEFT JOIN cities c ON u.city_id = c.id
          LEFT JOIN university_streams us ON u.id = us.university_id
          LEFT JOIN streams str ON us.stream_id = str.id
          LEFT JOIN university_courses uco ON u.id = uco.university_id
          LEFT JOIN ucourses ucrs ON uco.course_id = ucrs.id";

// Apply filters
$conditions = [];
$params = [];

// Stream filter
if (!empty($filters['streams'])) {
    $placeholders = implode(',', array_fill(0, count($filters['streams']), '?'));
    $conditions[] = "str.id IN ($placeholders)";
    $params = array_merge($params, $filters['streams']);
}

// Course filter
if (!empty($filters['courses'])) {
    $placeholders = implode(',', array_fill(0, count($filters['courses']), '?'));
    $conditions[] = "ucrs.id IN ($placeholders)";
    $params = array_merge($params, $filters['courses']);
}

// State filter
if (!empty($filters['states'])) {
    $placeholders = implode(',', array_fill(0, count($filters['states']), '?'));
    $conditions[] = "u.state_id IN ($placeholders)";
    $params = array_merge($params, $filters['states']);
}

// City filter
if (!empty($filters['cities'])) {
    $placeholders = implode(',', array_fill(0, count($filters['cities']), '?'));
    $conditions[] = "u.city_id IN ($placeholders)";
    $params = array_merge($params, $filters['cities']);
}

// Affiliation filter
if (!empty($filters['affiliations'])) {
    $placeholders = implode(',', array_fill(0, count($filters['affiliations']), '?'));
    $conditions[] = "u.affiliation_id IN ($placeholders)";
    $params = array_merge($params, $filters['affiliations']);
}

// Category filter
if (!empty($filters['categories'])) {
    $placeholders = "'" . implode("','", $filters['categories']) . "'";
    $conditions[] = "u.category IN ($placeholders)";
}

// Combine conditions
if (!empty($conditions)) {
    $query .= " WHERE " . implode(' AND ', $conditions);
}

// Add grouping
$query .= " GROUP BY u.id";

// Add sorting
$validSortColumns = ['rank', 'name', 'affiliation', 'category', 'state', 'city'];
$sort = in_array($sort, $validSortColumns) ? $sort : 'rank';
$query .= " ORDER BY $sort $direction";

// Execute query
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$universities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count
$total = $pdo->query("SELECT FOUND_ROWS()")->fetchColumn();

header('Content-Type: application/json');
echo json_encode([
    'universities' => $universities,
    'total' => $total
]);
?>