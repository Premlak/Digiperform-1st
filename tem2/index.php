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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Directory</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1d4ed8',
                        secondary: '#0ea5e9',
                        accent: '#8b5cf6',
                        dark: '#0f172a',
                        light: '#f8fafc'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <main class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="bg-gray-100 px-6 py-4 flex justify-between items-center">
                <h2 class="text-xl font-bold text-gray-800">Universities List</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">University</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Affiliation</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Streams</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($universities as $university): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-12 w-12 bg-gray-200 border-2 border-dashed rounded-xl overflow-hidden">
                                            <?php if (!empty($university['logo'])): ?>
                                                <img src="<?= htmlspecialchars($university['logo']) ?>" alt="<?= htmlspecialchars($university['university_name']) ?>" class="h-full w-full object-contain">
                                            <?php else: ?>
                                                <div class="h-full w-full flex items-center justify-center bg-gray-100">
                                                    <i class="fas fa-university text-gray-400"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($university['university_name']) ?></div>
                                            <div class="text-xs text-gray-500">ID: <?= htmlspecialchars($university['universityId']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900"><?= htmlspecialchars($university['type']) ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        <?= htmlspecialchars($university['category']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900"><?= htmlspecialchars($university['city_name']) ?></div>
                                    <div class="text-xs text-gray-500"><?= htmlspecialchars($university['state_name']) ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900"><?= htmlspecialchars($university['affiliation_name']) ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                        #<?= htmlspecialchars($university['rank']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <button onclick="toggleDetails('details-<?= $university['universityId'] ?>')" class="flex items-center text-sm font-medium text-secondary hover:text-primary">
                                        <span>View Streams</span>
                                        <i class="fas fa-chevron-down ml-1 text-xs"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr id="details-<?= $university['universityId'] ?>" class="hidden">
                                <td colspan="7" class="px-6 py-4 bg-gray-50">
                                    <div class="pl-12 pr-4 pb-4">
                                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Streams Offered</h3>
                                        <?php
                                        $streamStmt = $pdo->prepare("
                                        SELECT DISTINCT streams.id, streams.name
                                        FROM university_courses 
                                        JOIN ucourses ON university_courses.course_id = ucourses.id
                                        JOIN streams ON ucourses.stream_id = streams.id
                                        WHERE university_courses.university_id = ?
                                    ");
                                        $streamStmt->execute([$university['universityId']]);
                                        $streams = $streamStmt->fetchAll(PDO::FETCH_ASSOC);
                                        if (!empty($streams)): ?>
                                            <div class="space-y-4">
                                                <?php foreach ($streams as $stream): ?>
                                                    <div class="border-l-2 border-primary pl-4 py-2">
                                                        <div class="flex items-center">
                                                            <i class="fas fa-book text-primary mr-2"></i>
                                                            <h4 class="font-medium text-gray-800"><?= htmlspecialchars($stream['name']) ?> <span class="text-xs text-gray-500">(ID: <?= htmlspecialchars($stream['id']) ?>)</span></h4>
                                                        </div>
                                                        <?php
                                                        $courseStmt = $pdo->prepare("
                                                            SELECT DISTINCT ucourses.id, ucourses.name
                                                            FROM ucourses
                                                            JOIN university_courses uc ON uc.course_id = ucourses.id
                                                            WHERE ucourses.stream_id = ? AND uc.university_id = ?
                                                            GROUP BY ucourses.id
                                                        ");
                                                        $courseStmt->execute([$stream['id'], $university['universityId']]);
                                                        $courses = $courseStmt->fetchAll(PDO::FETCH_ASSOC);
                                                        if (!empty($courses)): ?>
                                                            <div class="mt-2 ml-6 space-y-3">
                                                                <?php foreach ($courses as $course): ?>
                                                                    <div class="border-l-2 border-secondary pl-4 py-2">
                                                                        <div class="flex items-center">
                                                                            <i class="fas fa-bookmark text-secondary mr-2"></i>
                                                                            <h5 class="font-medium text-gray-700"><?= htmlspecialchars($course['name']) ?> <span class="text-xs text-gray-500">(ID: <?= htmlspecialchars($course['id']) ?>)</span></h5>
                                                                        </div>
                                                                        <?php
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

                                                                        if (!empty($subStreams)): ?>
                                                                            <div class="mt-2 ml-6 grid grid-cols-1 md:grid-cols-2 gap-3">
                                                                                <?php foreach ($subStreams as $subStream): ?>
                                                                                    <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                                                                        <div class="flex items-center">
                                                                                            <i class="fas fa-file-alt text-accent mr-2"></i>
                                                                                            <h6 class="font-medium text-gray-700"><?= htmlspecialchars($subStream['name']) ?> <span class="text-xs text-gray-500">(ID: <?= htmlspecialchars($subStream['id']) ?>)</span></h6>
                                                                                        </div>
                                                                                        <div class="mt-2 grid grid-cols-2 gap-2 text-sm">
                                                                                            <div class="flex items-center">
                                                                                                <i class="fas fa-money-bill-wave text-green-500 mr-2"></i>
                                                                                                <span>Fee: ₹<?= htmlspecialchars($subStream['avg_fee_per_year']) ?>/yr</span>
                                                                                            </div>
                                                                                            <div class="flex items-center">
                                                                                                <i class="fas fa-clock text-purple-500 mr-2"></i>
                                                                                                <span>Duration: <?= htmlspecialchars($subStream['duration']) ?></span>
                                                                                            </div>
                                                                                            <?php
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
                                                                                            if (!empty($exams)): ?>
                                                                                                <div class="col-span-2 mt-1">
                                                                                                    <div class="flex items-start">
                                                                                                        <i class="fas fa-pen-alt text-red-500 mr-2 mt-1"></i>
                                                                                                        <div>
                                                                                                            <span class="font-medium">Entrance Exam(s):</span>
                                                                                                            <div class="flex flex-wrap gap-1 mt-1">
                                                                                                                <?php foreach ($exams as $exam): ?>
                                                                                                                    <a href="exam.php?id=<?= $exam['id'] ?>" class="inline-block bg-red-100 hover:bg-red-200 text-red-800 px-2 py-1 rounded text-xs transition-colors">
                                                                                                                        <?= htmlspecialchars($exam['name']) ?>
                                                                                                                    </a>
                                                                                                                <?php endforeach; ?>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            <?php endif; ?>
                                                                                            <?php
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
                                                                                            if (!empty($programTypes)): ?>
                                                                                                <div class="col-span-2 mt-1">
                                                                                                    <div class="flex items-center">
                                                                                                        <i class="fas fa-graduation-cap text-blue-500 mr-2"></i>
                                                                                                        <span class="font-medium">Program Type(s):</span>
                                                                                                        <span class="ml-1"><?= htmlspecialchars(implode(', ', $programTypes)) ?></span>
                                                                                                    </div>
                                                                                                </div>
                                                                                            <?php endif; ?>
                                                                                        </div>
                                                                                    </div>
                                                                                <?php endforeach; ?>
                                                                            </div>
                                                                        <?php else: ?>
                                                                            <div class="mt-2 ml-6 text-sm text-gray-500 italic">
                                                                                No sub-streams available for this course.
                                                                            </div>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="mt-2 ml-6 text-sm text-gray-500 italic">
                                                                No courses found under this stream.
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-center py-6 text-gray-500">
                                                <i class="fas fa-book-open text-3xl mb-2"></i>
                                                <p>No streams found for this university.</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <script>
        function toggleDetails(id) {
            const element = document.getElementById(id);
            element.classList.toggle('hidden');
            const button = event.currentTarget;
            const icon = button.querySelector('i');
            if (element.classList.contains('hidden')) {
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            } else {
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            }
        }
    </script>
</body>
</html>