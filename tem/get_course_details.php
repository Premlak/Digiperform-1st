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
$universityId = $data['universityId'] ?? 0;
$courseId = $data['courseId'] ?? 0;

$query = "SELECT 
            cd.duration,
            uco.avg_fee_per_year,
            GROUP_CONCAT(DISTINCT pt.name) AS program_types,
            GROUP_CONCAT(DISTINCT ee.name) AS entrance_exams
          FROM university_courses uco
          JOIN ucourses ucrs ON uco.course_id = ucrs.id
          LEFT JOIN course_durations cd ON uco.duration_id = cd.id
          LEFT JOIN university_course_program_types ucpt ON uco.id = ucpt.university_course_id
          LEFT JOIN program_types pt ON ucpt.program_type_id = pt.id
          LEFT JOIN university_course_entrance_exams ucee ON uco.id = ucee.university_course_id
          LEFT JOIN entrance_exams ee ON ucee.entrance_exam_id = ee.id
          WHERE uco.university_id = ? AND uco.course_id = ?
          GROUP BY uco.id";

$stmt = $pdo->prepare($query);
$stmt->execute([$universityId, $courseId]);
$details = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($details);
?>