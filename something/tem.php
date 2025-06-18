<?php
$data = [];
$host = 'localhost';
$db   = 'digiperform';
$user = 'root';
$pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}
$stmt = $pdo->query("
    SELECT 
        universities.name AS university_name,
        universities.logo,
        universities.type,
        universities.category,
        universities.rank,
        affiliations.name AS affiliation_name,
        universities.id AS universityId,
        states.name AS state_name,
        cities.name AS city_name
    FROM 
        universities
    JOIN affiliations ON universities.affiliation_id = affiliations.id
    JOIN states ON universities.state_id = states.id
    JOIN cities ON universities.city_id = cities.id
");
$universities = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($universities as $university) {
    echo "<div class='p-4 border-b'>";
    echo "<img src='".$university['logo'] ."'/>";
    echo "<h2 class='text-xl font-bold'>" . htmlspecialchars($university['university_name']) . " (ID: " . htmlspecialchars($university['universityId']) . ")</h2>";
    echo "<p>Type: " . htmlspecialchars($university['type']) . "</p>";
    echo "<p>Category: " . htmlspecialchars($university['category']) . "</p>";
    echo "<p>Rank: " . htmlspecialchars($university['rank']) . "</p>";
    echo "<p>Affiliation: " . htmlspecialchars($university['affiliation_name']) . "</p>";
    echo "<p>State: " . htmlspecialchars($university['state_name']) . "</p>";
    echo "<p>City: " . htmlspecialchars($university['city_name']) . "</p>";
    $streamStmt = $pdo->prepare("
        SELECT DISTINCT streams.id, streams.name
        FROM university_courses 
        JOIN ucourses ON university_courses.course_id = ucourses.id
        JOIN streams ON ucourses.stream_id = streams.id
        WHERE university_courses.university_id = ?
    ");
    $streamStmt->execute([$university['universityId']]);
    $streams = $streamStmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p><strong>Streams Offered:</strong></p>";
    if (!empty($streams)) {
        echo "<ul class='list-disc pl-6'>";
        foreach ($streams as $stream) {
            echo "<li>" . htmlspecialchars($stream['name']) . " (ID: " . htmlspecialchars($stream['id']) . ")";
            $courseStmt = $pdo->prepare("
                SELECT ucourses.id, ucourses.name 
                FROM ucourses 
                JOIN university_courses ON university_courses.course_id = ucourses.id 
                WHERE ucourses.stream_id = ? AND university_courses.university_id = ?
            ");
            $courseStmt->execute([$stream['id'], $university['universityId']]);
            $courses = $courseStmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($courses)) {
                echo "<ul class='list-disc pl-6'>";
                foreach ($courses as $course) {
                    echo "<li>" . htmlspecialchars($course['name']) . " (ID: " . htmlspecialchars($course['id']) . ")";
                    $subStreamStmt = $pdo->prepare("
                        SELECT DISTINCT ss.id AS id, ss.name AS name, uc.avg_fee_per_year, cd.duration
                        FROM university_course_substreams ucs
                        JOIN sub_streams ss ON ss.id = ucs.substream_id
                        JOIN university_courses uc ON uc.id = ucs.university_course_id
                        LEFT JOIN course_durations cd ON uc.duration_id = cd.id
                        WHERE ss.ucourse_id = ? AND uc.university_id = ?
                    ");
                    $subStreamStmt->execute([$course['id'], $university['universityId']]);
                    $subStreams = $subStreamStmt->fetchAll(PDO::FETCH_ASSOC);
                    if (!empty($subStreams)) {
                        echo "<ul class='list-disc pl-6'>";
                        foreach ($subStreams as $subStream) {
                            echo "<li>" . htmlspecialchars($subStream['name']) . " (ID: " . htmlspecialchars($subStream['id']) . ")";
                            echo " | Fee: ₹" . htmlspecialchars($subStream['avg_fee_per_year']) . " | Duration: " . htmlspecialchars($subStream['duration']);

                            // Entrance Exam(s)
                            $examStmt = $pdo->prepare("
    SELECT ee.name, ee.id
    FROM university_course_entrance_exams ucee
    JOIN entrance_exams ee ON ee.id = ucee.entrance_exam_id
    WHERE ucee.university_course_id = (
        SELECT id FROM university_courses
        WHERE university_id = ? AND course_id = ?
        LIMIT 1
    )
");
                            $examStmt->execute([$university['universityId'], $course['id']]);
                            $exams = $examStmt->fetchAll(PDO::FETCH_ASSOC);
                            if (!empty($exams)) {
    $examList = [];
    foreach ($exams as $exam) {
        $examList[] = htmlspecialchars($exam['name']) . " (ID: " . htmlspecialchars($exam['id']) . ")";
    }
    echo " | Entrance Exam(s): " . implode(', ', $examList);
}
                            $programStmt = $pdo->prepare("
    SELECT pt.name
    FROM university_course_program_types ucpt
    JOIN program_types pt ON pt.id = ucpt.program_type_id
    WHERE ucpt.university_course_id = (
        SELECT id FROM university_courses
        WHERE university_id = ? AND course_id = ?
        LIMIT 1
    )
");
                            $programStmt->execute([$university['universityId'], $course['id']]);
                            $programTypes = $programStmt->fetchAll(PDO::FETCH_COLUMN);
                            if (!empty($programTypes)) {
                                echo " | Program Type(s): " . htmlspecialchars(implode(', ', $programTypes));
                            }

                            echo "</li>";
                        }
                        echo "</ul>";
                    } else {
                        echo "<ul class='list-disc pl-6'><li>No sub-streams available.</li></ul>";
                    }
                    echo "</li>";
                }
                echo "</ul>";
            } else {
                echo "<ul class='list-disc pl-6'><li>No courses found under this stream.</li></ul>";
            }
            echo "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No streams found for this university.</p>";
    }
    echo "</div>";
}
