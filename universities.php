<?php
include './db.php';
$perPage = 2;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) $currentPage = 1;
$states = $pdo->query("SELECT id, name FROM states")->fetchAll(PDO::FETCH_ASSOC);
$streams = $pdo->query("SELECT id, name FROM streams")->fetchAll(PDO::FETCH_ASSOC);
$types = $pdo->query("SELECT DISTINCT type FROM universities")->fetchAll(PDO::FETCH_COLUMN);
$categories = $pdo->query("SELECT DISTINCT category FROM universities")->fetchAll(PDO::FETCH_COLUMN);
$programTypes = $pdo->query("SELECT DISTINCT name FROM program_types")->fetchAll(PDO::FETCH_COLUMN);
$affiliations = $pdo->query("SELECT id, name FROM affiliations")->fetchAll(PDO::FETCH_ASSOC);
$courseTypes = $pdo->query("SELECT DISTINCT type FROM ucourses WHERE type IS NOT NULL AND type != ''")->fetchAll(PDO::FETCH_COLUMN);
$durations = $pdo->query("SELECT DISTINCT duration FROM course_durations")->fetchAll(PDO::FETCH_COLUMN);
$exams = $pdo->query("SELECT DISTINCT name FROM entrance_exams")->fetchAll(PDO::FETCH_COLUMN);
$query = "
    SELECT 
        universities.id AS universityId,
        universities.name AS university_name,
        universities.logo,
        universities.type,
        universities.category,
        universities.rank,
        affiliations.name AS affiliation_name,
        affiliations.id AS affiliation_id,
        states.name AS state_name,
        cities.name AS city_name,
        states.id AS state_id,
        cities.id AS city_id,
        (SELECT GROUP_CONCAT(DISTINCT uc.type SEPARATOR ', ') 
         FROM university_courses ucr
         JOIN ucourses uc ON ucr.course_id = uc.id
         WHERE ucr.university_id = universities.id
        ) AS course_types
    FROM 
        universities
    JOIN affiliations ON universities.affiliation_id = affiliations.id
    JOIN states ON universities.state_id = states.id
    JOIN cities ON universities.city_id = cities.id
";
$countQuery = "
    SELECT COUNT(DISTINCT universities.id) AS total
    FROM universities
    JOIN affiliations ON universities.affiliation_id = affiliations.id
    JOIN states ON universities.state_id = states.id
    JOIN cities ON universities.city_id = cities.id
";
$filters = [];
$params = [];
if (isset($_GET['duration']) && $_GET['duration'] !== '') {
    $filters[] = "EXISTS (
        SELECT 1 FROM university_courses uc
        JOIN course_durations cd ON uc.duration_id = cd.id
        WHERE uc.university_id = universities.id AND cd.duration = ?
    )";
    $params[] = $_GET['duration'];
}
if (isset($_GET['entrance_exam']) && $_GET['entrance_exam'] !== '') {
    $filters[] = "EXISTS (
        SELECT 1 FROM university_courses uc
        JOIN university_course_entrance_exams ucee ON ucee.university_course_id = uc.id
        JOIN entrance_exams ee ON ee.id = ucee.entrance_exam_id
        WHERE uc.university_id = universities.id AND ee.name = ?
    )";
    $params[] = $_GET['entrance_exam'];
}
if (isset($_GET['state_id']) && $_GET['state_id'] !== '') {
    $filters[] = "universities.state_id = ?";
    $params[] = $_GET['state_id'];
}
if (isset($_GET['city_id']) && $_GET['city_id'] !== '') {
    $filters[] = "universities.city_id = ?";
    $params[] = $_GET['city_id'];
}
if (isset($_GET['stream_id']) && $_GET['stream_id'] !== '') {
    $filters[] = "ucourses.stream_id = ?";
    $params[] = $_GET['stream_id'];
}
if (isset($_GET['ucourse_id']) && $_GET['ucourse_id'] !== '') {
    $filters[] = "university_courses.course_id = ?";
    $params[] = $_GET['ucourse_id'];
}
if (isset($_GET['sub_stream_id']) && $_GET['sub_stream_id'] !== '') {
    $filters[] = "ucs.substream_id = ?";
    $params[] = $_GET['sub_stream_id'];
}
if (isset($_GET['type']) && $_GET['type'] !== '') {
    $filters[] = "universities.type = ?";
    $params[] = $_GET['type'];
}
if (isset($_GET['category']) && $_GET['category'] !== '') {
    $filters[] = "universities.category = ?";
    $params[] = $_GET['category'];
}
if (isset($_GET['affiliation_id']) && $_GET['affiliation_id'] !== '') {
    $filters[] = "universities.affiliation_id = ?";
    $params[] = $_GET['affiliation_id'];
}
if (isset($_GET['course_type']) && $_GET['course_type'] !== '') {
    $filters[] = "EXISTS (
        SELECT 1 FROM university_courses ucrs
        JOIN ucourses uc ON ucrs.course_id = uc.id
        WHERE ucrs.university_id = universities.id AND uc.type = ?
    )";
    $params[] = $_GET['course_type'];
}
if (!empty($filters)) {
    $joinClause = " LEFT JOIN university_courses ON university_courses.university_id = universities.id
                    LEFT JOIN ucourses ON university_courses.course_id = ucourses.id
                    LEFT JOIN university_course_substreams ucs ON ucs.university_course_id = university_courses.id";

    $query .= $joinClause . " WHERE " . implode(" AND ", $filters);
    $countQuery .= $joinClause . " WHERE " . implode(" AND ", $filters);
}
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($params);
$totalUniversities = $countStmt->fetchColumn();
$totalPages = $totalUniversities > 0 ? ceil($totalUniversities / $perPage) : 1;
if ($currentPage > $totalPages) $currentPage = $totalPages;
$offset = ($currentPage - 1) * $perPage;
$query .= " GROUP BY universities.id LIMIT ?, ?";
$stmt = $pdo->prepare($query);
foreach ($params as $i => $param) {
    $stmt->bindValue($i + 1, $param);
}
$stmt->bindValue(count($params) + 1, $offset, PDO::PARAM_INT);
$stmt->bindValue(count($params) + 2, $perPage, PDO::PARAM_INT);
$stmt->execute();
$universities = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Directory | Arctic Blue</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#2563eb',
                        secondary: '#0891b2',
                        accent: '#3b82f6',
                        dark: '#0f172a',
                        light: '#f0f9ff',
                        card: '#e0f2fe',
                        glacier: {
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1'
                        },
                        frost: {
                            100: '#f0f9ff',
                            200: '#e0f2fe',
                            300: '#bae6fd',
                            400: '#7dd3fc'
                        },
                        arctic: {
                            100: '#ecfeff',
                            200: '#cffafe',
                            300: '#a5f3fc',
                            400: '#67e8f9'
                        },
                        stream: '#dbeafe',
                        course: '#e0f2fe',
                        substream: '#e0f7fa'
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.3s ease-in',
                        'slide-down': 'slideDown 0.3s ease-out'
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': {
                                opacity: '0'
                            },
                            '100%': {
                                opacity: '1'
                            }
                        },
                        slideDown: {
                            '0%': {
                                transform: 'translateY(-10px)',
                                opacity: '0'
                            },
                            '100%': {
                                transform: 'translateY(0)',
                                opacity: '1'
                            }
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        * {
            font-family: 'Inter', sans-serif;
            scroll-behavior: smooth;
        }
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .filter-section {
            transition: all 0.3s ease;
        }
        .card-hover {
            transition: all 0.2s ease;
        }
        .card-hover:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .course-grid {
            display: grid;
            gap: 1.5rem;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        }
        @media (min-width: 1024px) {
            .course-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        @media (max-width: 1023px) and (min-width: 768px) {
            .course-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 767px) {
            .course-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }
            .hierarchy-line::before,
            .hierarchy-line::after {
                display: none;
            }
            .course-grid>div {
                padding: 1rem;
                margin-left: 0;
                border-left: none;
            }
            .course-grid>div>.flex {
                margin-left: 0;
            }
            .md\:flex {
                display: block;
            }
            .md\:w-1\/3 {
                width: 100%;
            }
            .md\:border-r {
                border-right: none;
            }
            .border-glacier-300 {
                border-color: transparent;
            }
            .p-6 {
                padding: 1.25rem;
            }
            .card-hover {
                padding: 0.5rem;
            }
            .bg-glacier-50 {
                background-color: transparent;
            }
        }
        @media (max-width: 768px) {
            .course-grid {
                grid-template-columns: 1fr;
            }

            .hierarchy-line::before,
            .hierarchy-line::after {
                display: none;
            }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-frost-100 to-frost-200 min-h-screen">
    <header class="bg-gradient-to-r from-glacier-600 to-glacier-700 text-white shadow-lg">
        <div class="container mx-auto px-4 py-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold"><i class="fas fa-graduation-cap mr-2"></i>University Directory</h1>
                    <p class="text-glacier-200 mt-1">Find your perfect educational path</p>
                </div>
                <div class="mt-4 md:mt-0 flex items-center space-x-4">
                </div>
            </div>
        </div>
    </header>
    <main class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8 animate-fade-in">
            <div class="bg-gradient-to-r from-glacier-100 to-glacier-200 px-6 py-4 border-b border-glacier-300">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-bold text-glacier-800 flex items-center">
                        <i class="fas fa-filter mr-2 text-glacier-600"></i>Filter Universities
                    </h2>
                </div>
            </div>
            <div id="filterSection" class="filter-section px-6 py-4 transition-all duration-300 bg-white">
                <div class="flex space-x-4 overflow-x-auto pb-2 scrollbar-hide">
                    <div class="min-w-[180px]">
                        <label class="block text-sm font-medium text-glacier-700 mb-1">State</label>
                        <select id="stateFilter" class="w-full rounded-xl border border-glacier-300 bg-white py-2.5 px-4 shadow-sm focus:ring-2 focus:ring-glacier-500 focus:border-glacier-500 transition-all" onchange="updateCities()">
                            <option value="">All States</option>
                            <?php foreach ($states as $state): ?>
                                <option value="<?= $state['id'] ?>" <?= isset($_GET['state_id']) && $_GET['state_id'] == $state['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($state['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="min-w-[180px]">
                        <label class="block text-sm font-medium text-glacier-700 mb-1">City</label>
                        <select id="cityFilter" class="w-full rounded-xl border border-glacier-300 bg-white py-2.5 px-4 shadow-sm focus:ring-2 focus:ring-glacier-500 focus:border-glacier-500 transition-all" onchange="applyFilters()">
                            <option value="">All Cities</option>
                            <?php if (isset($_GET['state_id']) && $_GET['state_id'] !== ''): ?>
                                <?php
                                $cities = $pdo->prepare("SELECT id, name FROM cities WHERE state_id = ?");
                                $cities->execute([$_GET['state_id']]);
                                $cities = $cities->fetchAll(PDO::FETCH_ASSOC);
                                ?>
                                <?php foreach ($cities as $city): ?>
                                    <option value="<?= $city['id'] ?>" <?= isset($_GET['city_id']) && $_GET['city_id'] == $city['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($city['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="min-w-[180px]">
                        <label class="block text-sm font-medium text-glacier-700 mb-1">Stream</label>
                        <select id="streamFilter" class="w-full rounded-xl border border-glacier-300 bg-white py-2.5 px-4 shadow-sm focus:ring-2 focus:ring-glacier-500 focus:border-glacier-500 transition-all" onchange="updateUCourses()">
                            <option value="">All Streams</option>
                            <?php foreach ($streams as $stream): ?>
                                <option value="<?= $stream['id'] ?>" <?= isset($_GET['stream_id']) && $_GET['stream_id'] == $stream['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($stream['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="min-w-[180px]">
                        <label class="block text-sm font-medium text-glacier-700 mb-1">Course</label>
                        <select id="ucourseFilter" class="w-full rounded-xl border border-glacier-300 bg-white py-2.5 px-4 shadow-sm focus:ring-2 focus:ring-glacier-500 focus:border-glacier-500 transition-all" onchange="updateSubStreams()">
                            <option value="">All Courses</option>
                            <?php if (isset($_GET['stream_id']) && $_GET['stream_id'] !== ''): ?>
                                <?php
                                $ucourses = $pdo->prepare("SELECT id, name FROM ucourses WHERE stream_id = ?");
                                $ucourses->execute([$_GET['stream_id']]);
                                $ucourses = $ucourses->fetchAll(PDO::FETCH_ASSOC);
                                ?>
                                <?php foreach ($ucourses as $ucourse): ?>
                                    <option value="<?= $ucourse['id'] ?>" <?= isset($_GET['ucourse_id']) && $_GET['ucourse_id'] == $ucourse['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($ucourse['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="min-w-[180px]">
                        <label class="block text-sm font-medium text-glacier-700 mb-1">Sub Stream</label>
                        <select id="subStreamFilter" class="w-full rounded-xl border border-glacier-300 bg-white py-2.5 px-4 shadow-sm focus:ring-2 focus:ring-glacier-500 focus:border-glacier-500 transition-all" onchange="applyFilters()">
                            <option value="">All Sub Streams</option>
                            <?php if (isset($_GET['ucourse_id']) && $_GET['ucourse_id'] !== ''): ?>
                                <?php
                                $subStreams = $pdo->prepare("SELECT id, name FROM sub_streams WHERE ucourse_id = ?");
                                $subStreams->execute([$_GET['ucourse_id']]);
                                $subStreams = $subStreams->fetchAll(PDO::FETCH_ASSOC);
                                ?>
                                <?php foreach ($subStreams as $subStream): ?>
                                    <option value="<?= $subStream['id'] ?>" <?= isset($_GET['sub_stream_id']) && $_GET['sub_stream_id'] == $subStream['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($subStream['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="min-w-[180px]">
                        <label class="block text-sm font-medium text-glacier-700 mb-1">Affiliation</label>
                        <select id="affiliationFilter" class="w-full rounded-xl border border-glacier-300 bg-white py-2.5 px-4 shadow-sm focus:ring-2 focus:ring-glacier-500 focus:border-glacier-500 transition-all" onchange="applyFilters()">
                            <option value="">All Affiliations</option>
                            <?php foreach ($affiliations as $affiliation): ?>
                                <option value="<?= $affiliation['id'] ?>" <?= isset($_GET['affiliation_id']) && $_GET['affiliation_id'] == $affiliation['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($affiliation['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="min-w-[180px]">
                        <label class="block text-sm font-medium text-glacier-700 mb-1">Course Type</label>
                        <select id="courseTypeFilter" class="w-full rounded-xl border border-glacier-300 bg-white py-2.5 px-4 shadow-sm focus:ring-2 focus:ring-glacier-500 focus:border-glacier-500 transition-all" onchange="applyFilters()">
                            <option value="">All Course Types</option>
                            <?php foreach ($courseTypes as $type): ?>
                                <option value="<?= $type ?>" <?= isset($_GET['course_type']) && $_GET['course_type'] == $type ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($type) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="min-w-[180px]">
                        <label class="block text-sm font-medium text-glacier-700 mb-1">Duration</label>
                        <select id="durationFilter" class="w-full rounded-xl border border-glacier-300 bg-white py-2.5 px-4 shadow-sm focus:ring-2 focus:ring-glacier-500 focus:border-glacier-500 transition-all" onchange="applyFilters()">
                            <option value="">All Durations</option>
                            <?php foreach ($durations as $duration): ?>
                                <option value="<?= $duration ?>" <?= isset($_GET['duration']) && $_GET['duration'] == $duration ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($duration) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="min-w-[180px]">
                        <label class="block text-sm font-medium text-glacier-700 mb-1">Entrance Exam</label>
                        <select id="entranceExamFilter" class="w-full rounded-xl border border-glacier-300 bg-white py-2.5 px-4 shadow-sm focus:ring-2 focus:ring-glacier-500 focus:border-glacier-500 transition-all" onchange="applyFilters()">
                            <option value="">All Exams</option>
                            <?php foreach ($exams as $exam): ?>
                                <option value="<?= $exam ?>" <?= isset($_GET['entrance_exam']) && $_GET['entrance_exam'] == $exam ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($exam) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="min-w-[180px]">
                        <label class="block text-sm font-medium text-glacier-700 mb-1">Type</label>
                        <select id="typeFilter" class="w-full rounded-xl border border-glacier-300 bg-white py-2.5 px-4 shadow-sm focus:ring-2 focus:ring-glacier-500 focus:border-glacier-500 transition-all" onchange="applyFilters()">
                            <option value="">All Types</option>
                            <?php foreach ($types as $type): ?>
                                <option value="<?= $type ?>" <?= isset($_GET['type']) && $_GET['type'] == $type ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($type) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="min-w-[180px]">
                        <label class="block text-sm font-medium text-glacier-700 mb-1">Category</label>
                        <select id="categoryFilter" class="w-full rounded-xl border border-glacier-300 bg-white py-2.5 px-4 shadow-sm focus:ring-2 focus:ring-glacier-500 focus:border-glacier-500 transition-all" onchange="applyFilters()">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category ?>" <?= isset($_GET['category']) && $_GET['category'] == $category ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mt-6 flex flex-col sm:flex-row justify-end gap-3">
                    <button onclick="resetFilters()" class="px-5 py-2.5 bg-glacier-200 hover:bg-glacier-300 text-glacier-800 font-medium rounded-xl transition-colors flex items-center justify-center">
                        <i class="fas fa-redo mr-2"></i> Reset Filters
                    </button>
                    <button onclick="applyFilters()" class="px-5 py-2.5 bg-glacier-600 hover:bg-glacier-700 text-white font-medium rounded-xl transition-colors flex items-center justify-center">
                        <i class="fas fa-filter mr-2"></i> Apply Filters
                    </button>
                </div>
            </div>
        </div>
        <div class="mb-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-glacier-800 flex items-center">
                    <i class="fas fa-university mr-2 text-glacier-600"></i>University Results
                </h2>
                <div class="text-sm text-glacier-600">
                    Showing <?= min(($currentPage - 1) * $perPage + 1, $totalUniversities) ?>
                    to <?= min($currentPage * $perPage, $totalUniversities) ?>
                    of <?= $totalUniversities ?> universities
                </div>
            </div>
            <?php if (empty($universities)): ?>
                <div class="bg-white rounded-2xl shadow-lg p-8 text-center animate-fade-in border border-glacier-200">
                    <div class="text-5xl text-glacier-300 mb-4">
                        <i class="fas fa-university"></i>
                    </div>
                    <h3 class="text-xl font-bold text-glacier-700 mb-2">No universities found</h3>
                    <p class="text-glacier-600 mb-6">Try adjusting your filters to see more results</p>
                    <button onclick="resetFilters()" class="px-5 py-2.5 bg-glacier-600 hover:bg-glacier-700 text-white font-medium rounded-xl transition-colors">
                        Reset Filters
                    </button>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 gap-6">
                    <?php foreach ($universities as $index => $university): ?>
                        <?php
                        $streamsCourses = $pdo->prepare("
                            SELECT 
                                streams.id AS stream_id,
                                streams.name AS stream_name,
                                GROUP_CONCAT(DISTINCT ucourses.name ORDER BY ucourses.name SEPARATOR ', ') AS course_names
                            FROM university_courses
                            JOIN ucourses ON university_courses.course_id = ucourses.id
                            JOIN streams ON ucourses.stream_id = streams.id
                            WHERE university_courses.university_id = ?
                            GROUP BY streams.id
                            ORDER BY streams.name
                        ");
                        $streamsCourses->execute([$university['universityId']]);
                        $streamsData = $streamsCourses->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        <div class="bg-white rounded-2xl shadow-lg overflow-hidden card-hover animate-fade-in border border-glacier-200" id="university-<?= $index ?>">
                            <div class="md:flex">
                                <div class="md:w-1/3 p-6 border-b md:border-b-0 md:border-r border-glacier-200 bg-white md:bg-glacier-50">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 h-16 w-16 bg-white border-2 border-glacier-300 rounded-xl overflow-hidden flex items-center justify-center shadow-sm">
                                            <?php if (!empty($university['logo'])): ?>
                                                <img src="<?= htmlspecialchars($university['logo']) ?>" alt="<?= htmlspecialchars($university['university_name']) ?>" class="h-full w-full object-contain">
                                            <?php else: ?>
                                                <div class="h-full w-full flex items-center justify-center bg-glacier-100">
                                                    <i class="fas fa-university text-glacier-400 text-2xl"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="ml-4">
                                            <h3 class="text-xl font-bold text-glacier-900"><?= htmlspecialchars($university['university_name']) ?></h3>
                                            <div class="flex flex-wrap gap-2 mt-3">
                                                <span class="px-3 py-1 bg-glacier-100 text-glacier-800 text-xs font-semibold rounded-full border border-glacier-300">
                                                    <?= htmlspecialchars($university['type']) ?>
                                                </span>
                                                <span class="px-3 py-1 bg-glacier-100 text-glacier-800 text-xs font-semibold rounded-full border border-glacier-300">
                                                    <?= htmlspecialchars($university['category']) ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-6 space-y-3">
                                        <div class="flex items-center">
                                            <div class="bg-white p-2 rounded-lg border border-glacier-200 shadow-sm">
                                                <i class="fas fa-map-marker-alt text-glacier-500"></i>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-xs text-glacier-500">Location</p>
                                                <p class="font-medium text-glacier-800"><?= htmlspecialchars($university['city_name']) ?>, <?= htmlspecialchars($university['state_name']) ?></p>
                                            </div>
                                        </div>
                                        <div class="flex items-center">
                                            <div class="bg-white p-2 rounded-lg border border-glacier-200 shadow-sm">
                                                <i class="fas fa-school text-glacier-500"></i>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-xs text-glacier-500">Affiliation</p>
                                                <p class="font-medium text-glacier-800"><?= htmlspecialchars($university['affiliation_name']) ?></p>
                                            </div>
                                        </div>
                                        <div class="flex items-center">
                                            <div class="bg-white p-2 rounded-lg border border-glacier-200 shadow-sm">
                                                <i class="fas fa-trophy text-glacier-500"></i>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-xs text-glacier-500">Rank</p>
                                                <p class="font-medium text-glacier-800">#<?= htmlspecialchars($university['rank']) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="md:w-2/3">
                                    <div class="p-6">
                                        <h4 class="text-lg font-semibold text-glacier-800 mb-4 flex items-center">
                                            <i class="fas fa-book-open mr-2 text-glacier-600"></i>Streams & Courses
                                        </h4>
                                        <?php if (!empty($streamsData)): ?>
                                            <div class="space-y-4 max-h-64 overflow-y-auto pr-2">
                                                <?php foreach ($streamsData as $stream): ?>
                                                    <div class="border-l-4 border-glacier-500 pl-4 py-2 bg-glacier-50 rounded-r-lg">
                                                        <h5 class="font-semibold text-glacier-800 flex items-center">
                                                            <i class="fas fa-layer-group text-glacier-600 mr-2"></i>
                                                            <?= htmlspecialchars($stream['stream_name']) ?>
                                                        </h5>
                                                        <div class="mt-2 flex flex-wrap gap-2">
                                                            <?php
                                                            $courses = explode(', ', $stream['course_names']);
                                                            foreach ($courses as $course): ?>
                                                                <span class="bg-glacier-100 text-glacier-800 text-xs px-3 py-1.5 rounded-full border border-glacier-300">
                                                                    <?= htmlspecialchars($course) ?>
                                                                </span>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-center py-4 text-glacier-500 bg-glacier-50 rounded-lg">
                                                <i class="fas fa-book-open text-2xl mb-2"></i>
                                                <p>No streams/courses available</p>
                                            </div>
                                        <?php endif; ?>
                                        <div class="mt-6 text-right">
                                            <button onclick="toggleDetails('details-<?= $university['universityId'] ?>', this, <?= $index ?>)"
                                                class="px-4 py-2 bg-glacier-100 hover:bg-glacier-200 text-glacier-800 font-medium rounded-lg transition-colors flex items-center border border-glacier-300">
                                                <span>View All Details</span>
                                                <i class="fas fa-chevron-down ml-2 text-xs"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="details-<?= $university['universityId'] ?>" class="hidden bg-glacier-50 border-t border-glacier-300">
                                <div class="p-6">
                                    <div class="flex justify-between items-center mb-6">
                                        <h3 class="text-xl font-bold text-glacier-800 flex items-center">
                                            <i class="fas fa-list-alt mr-2 text-glacier-600"></i>Programs Offered
                                        </h3>
                                        <button onclick="toggleDetails('details-<?= $university['universityId'] ?>', this)"
                                            class="px-3 py-1 bg-glacier-200 hover:bg-glacier-300 text-glacier-800 text-sm rounded-lg transition-colors">
                                            <i class="fas fa-times"></i> Close
                                        </button>
                                    </div>
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
                                    ?>
                                    <?php if (!empty($streams)): ?>
                                        <div class="space-y-8">
                                            <?php foreach ($streams as $stream): ?>
                                                <div class="bg-white rounded-xl shadow-sm p-5 border border-glacier-200 relative">
                                                    <div class="flex items-center mb-4">
                                                        <div class="bg-glacier-100 p-2 rounded-lg border border-glacier-300">
                                                            <i class="fas fa-layer-group text-glacier-600 text-lg"></i>
                                                        </div>
                                                        <h4 class="ml-3 text-lg font-semibold text-glacier-800">
                                                            <?= htmlspecialchars($stream['name']) ?>
                                                        </h4>
                                                    </div>
                                                    <?php
                                                    $courseStmt = $pdo->prepare("
                                                        SELECT DISTINCT ucourses.id, ucourses.name, ucourses.type
                                                        FROM ucourses
                                                        JOIN university_courses uc ON uc.course_id = ucourses.id
                                                        WHERE ucourses.stream_id = ? AND uc.university_id = ?
                                                        GROUP BY ucourses.id
                                                    ");
                                                    $courseStmt->execute([$stream['id'], $university['universityId']]);
                                                    $courses = $courseStmt->fetchAll(PDO::FETCH_ASSOC);
                                                    ?>
                                                    <?php if (!empty($courses)): ?>
                                                        <div class="grid grid-cols-1 gap-4 w-full md:grid-cols-2 lg:grid-cols-3">
                                                            <?php foreach ($courses as $courseIndex => $course): ?>
                                                                <div class="bg-glacier-50 rounded-lg p-4 border border-glacier-300 relative">
                                                                    <div class="flex items-center mb-3">
                                                                        <div class="bg-glacier-100 p-2 rounded-lg border border-glacier-300">
                                                                            <i class="fas fa-book text-glacier-600 text-lg"></i>
                                                                        </div>
                                                                        <h5 class="ml-3 font-medium text-glacier-800">
                                                                            <?= htmlspecialchars($course['name']) ?>
                                                                        </h5>
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
                                                                    ?>
                                                                    <?php if (!empty($subStreams)): ?>
                                                                        <div class="space-y-3 w-full">
                                                                            <?php foreach ($subStreams as $subStream): ?>
                                                                                <div class="w-full bg-white rounded-lg shadow-sm p-4 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
                                                                                    <div class="flex flex-col">
                                                                                        <div class="flex items-center">
                                                                                            <div class="bg-glacier-100 p-2 rounded-md border border-glacier-300">
                                                                                                <i class="fas fa-file-alt text-glacier-600 text-sm"></i>
                                                                                            </div>
                                                                                            <h6 class="ml-3 font-medium text-glacier-800">
                                                                                                <?= htmlspecialchars($subStream['name']) ?>
                                                                                            </h6>
                                                                                        </div>
                                                                                        <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3">
                                                                                            <div class="flex items-center text-sm text-glacier-700">
                                                                                                <span><i class="fas fa-clock text-glacier-600 mr-2"></i>Duration: <span class="font-medium"><?= htmlspecialchars($subStream['duration']) ?></span></span>
                                                                                            </div>
                                                                                            <div class="flex items-center text-sm text-glacier-700">
                                                                                                <span><i class="fas fa-money-bill-wave text-glacier-600 mr-2"></i>Fee: <span class="font-medium">₹<?= htmlspecialchars($subStream['avg_fee_per_year']) ?>/yr</span></span>
                                                                                            </div>
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
                                                                                        ?>
                                                                                        <?php if (!empty($exams)): ?>
                                                                                            <div class="mt-3">
                                                                                                <p class="text-xs text-glacier-500 mb-1">Entrance Exams:</p>
                                                                                                <div class="flex flex-wrap gap-1.5">
                                                                                                    <?php foreach ($exams as $exam): ?>
                                                                                                        <a href="exam.php?id=<?= $exam['id'] ?>&name=<?= $exam['name'] ?>" class="inline-block bg-red-100 hover:bg-red-200 text-red-800 px-2.5 py-1 rounded-full text-xs transition-colors">
                                                                                                            <?= htmlspecialchars($exam['name']) ?>
                                                                                                        </a>
                                                                                                    <?php endforeach; ?>
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
                                                                                        ?>
                                                                                        <?php if (!empty($programTypes)): ?>
                                                                                            <div class="mt-3">
                                                                                                <p class="text-xs text-glacier-500 mb-1">Program Types:</p>
                                                                                                <div class="flex flex-wrap gap-1.5">
                                                                                                    <?php foreach ($programTypes as $type): ?>
                                                                                                        <span class="inline-block bg-blue-100 text-blue-800 px-2.5 py-1 rounded-full text-xs">
                                                                                                            <?= htmlspecialchars($type) ?>
                                                                                                        </span>
                                                                                                    <?php endforeach; ?>
                                                                                                </div>
                                                                                            </div>
                                                                                        <?php endif; ?>
                                                                                    </div>
                                                                                </div>
                                                                            <?php endforeach; ?>
                                                                        </div>
                                                                    <?php else: ?>
                                                                        <div class="text-center py-3 text-glacier-500 italic bg-white rounded-lg p-3 border border-glacier-300">
                                                                            No sub-streams available for this course.
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="text-center py-4 text-glacier-500 italic bg-white rounded-lg p-4 border border-glacier-300">
                                                            No courses found under this stream.
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-8 text-glacier-500 bg-white rounded-xl p-6 border border-glacier-300">
                                            <i class="fas fa-book-open text-3xl mb-2 text-glacier-400"></i>
                                            <p class="text-lg">No streams found for this university.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if ($totalPages > 1): ?>
                    <?php
                    $queryParams = $_GET;
                    unset($queryParams['page']);
                    $queryString = http_build_query($queryParams);
                    $urlPage = $queryString ? $queryString . '&' : '';
                    ?>
                    <div class="mt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-sm text-glacier-600">
                            Page <?= $currentPage ?> of <?= $totalPages ?>
                        </div>
                        <nav class="flex items-center space-x-1">
                            <a href="?<?= $urlPage ?>page=<?= $currentPage - 1 ?>"
                                class="px-4 py-2 rounded-lg border border-glacier-300 <?= $currentPage == 1 ? 'text-glacier-300 cursor-not-allowed' : 'text-glacier-700 hover:bg-glacier-100' ?>">
                                <i class="fas fa-chevron-left mr-2"></i> Prev
                            </a>
                            <?php
                            $start = max(1, $currentPage - 2);
                            $end = min($totalPages, $currentPage + 2);

                            if ($start > 1): ?>
                                <a href="?<?= $urlPage ?>page=1" class="px-4 py-2 rounded-lg border border-glacier-300 text-glacier-700 hover:bg-glacier-100">1</a>
                                <?php if ($start > 2): ?>
                                    <span class="px-2 text-glacier-400">...</span>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php for ($i = $start; $i <= $end; $i++): ?>
                                <a href="?<?= $urlPage ?>page=<?= $i ?>"
                                    class="px-4 py-2 rounded-lg border <?= $i == $currentPage ? 'bg-glacier-600 text-white border-glacier-700' : 'border-glacier-300 text-glacier-700 hover:bg-glacier-100' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                            <?php if ($end < $totalPages): ?>
                                <?php if ($end < $totalPages - 1): ?>
                                    <span class="px-2 text-glacier-400">...</span>
                                <?php endif; ?>
                                <a href="?<?= $urlPage ?>page=<?= $totalPages ?>" class="px-4 py-2 rounded-lg border border-glacier-300 text-glacier-700 hover:bg-glacier-100"><?= $totalPages ?></a>
                            <?php endif; ?>
                            <a href="?<?= $urlPage ?>page=<?= $currentPage + 1 ?>"
                                class="px-4 py-2 rounded-lg border border-glacier-300 <?= $currentPage == $totalPages ? 'text-glacier-300 cursor-not-allowed' : 'text-glacier-700 hover:bg-glacier-100' ?>">
                                Next <i class="fas fa-chevron-right ml-2"></i>
                            </a>
                        </nav>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
    <script>
        let openDetailsId = null;
        let currentUniversityIndex = null;
        function toggleDetails(id, button, index) {
            const element = document.getElementById(id);
            const icon = button.querySelector('i');
            document.querySelectorAll('[id^="details-"]').forEach(detail => {
                if (detail.id !== id && !detail.classList.contains('hidden')) {
                    detail.classList.add('hidden');
                    const otherButton = detail.previousElementSibling.querySelector('button');
                    if (otherButton) {
                        const otherIcon = otherButton.querySelector('i');
                        otherIcon.classList.remove('fa-chevron-up');
                        otherIcon.classList.add('fa-chevron-down');
                    }
                }
            });
            element.classList.toggle('hidden');
            if (element.classList.contains('hidden')) {
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
                openDetailsId = null;
                currentUniversityIndex = null;
            } else {
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
                openDetailsId = id;
                currentUniversityIndex = index;
                const universityCard = document.getElementById(`university-${index}`);
                universityCard.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest'
                });
            }
        }
        // document.getElementById('toggleAdvancedFilters').addEventListener('click', function() {
        //     const advancedFilters = document.getElementById('advancedFilters');
        //     const icon = document.getElementById('advancedFilterIcon');
        //     advancedFilters.classList.toggle('hidden');
        //     icon.classList.toggle('fa-chevron-down');
        //     icon.classList.toggle('fa-chevron-up');
        // });
        // document.getElementById('toggleFilters').addEventListener('click', function() {
        //     const filterSection = document.getElementById('filterSection');
        //     filterSection.classList.toggle('hidden');
        // });
        function toggleDetails(id, button, index) {
            const element = document.getElementById(id);
            if (!element) return; // Safety check if element doesn't exist
            const icon = button?.querySelector('i');
            document.querySelectorAll('[id^="details-"]').forEach(detail => {
                if (detail.id !== id && !detail.classList.contains('hidden')) {
                    detail.classList.add('hidden');
                    const otherButton = detail.previousElementSibling?.querySelector('button');
                    if (otherButton) {
                        const otherIcon = otherButton.querySelector('i');
                        if (otherIcon) {
                            otherIcon.classList.remove('fa-chevron-up');
                            otherIcon.classList.add('fa-chevron-down');
                        }
                    }
                }
            });
            element.classList.toggle('hidden');
            if (icon) {
                if (element.classList.contains('hidden')) {
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                    openDetailsId = null;
                    currentUniversityIndex = null;
                } else {
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');
                    openDetailsId = id;
                    currentUniversityIndex = index;
                    const universityCard = document.getElementById(`university-${index}`);
                    if (universityCard) {
                        universityCard.scrollIntoView({
                            behavior: 'smooth',
                            block: 'nearest'
                        });
                    }
                }
            }
        }
        function updateCities() {
            const stateId = document.getElementById('stateFilter').value;
            const cityFilter = document.getElementById('cityFilter');
            cityFilter.innerHTML = '<option value="">All Cities</option>';
            if (stateId) {
                fetch(`get_cities.php?state_id=${stateId}`)
                    .then(response => response.json())
                    .then(cities => {
                        cities.forEach(city => {
                            const option = document.createElement('option');
                            option.value = city.id;
                            option.textContent = city.name;
                            cityFilter.appendChild(option);
                        });
                        applyFilters();
                    });
            } else {
                applyFilters();
            }
        }
        function updateUCourses() {
            const streamId = document.getElementById('streamFilter').value;
            const ucourseFilter = document.getElementById('ucourseFilter');
            ucourseFilter.innerHTML = '<option value="">All Courses</option>';
            if (streamId) {
                fetch(`get_ucourses.php?stream_id=${streamId}`)
                    .then(response => response.json())
                    .then(courses => {
                        courses.forEach(course => {
                            const option = document.createElement('option');
                            option.value = course.id;
                            option.textContent = course.name;
                            ucourseFilter.appendChild(option);
                        });
                        applyFilters();
                    });
            } else {
                applyFilters();
            }
        }
        function updateSubStreams() {
            const ucourseId = document.getElementById('ucourseFilter').value;
            const subStreamFilter = document.getElementById('subStreamFilter');
            subStreamFilter.innerHTML = '<option value="">All Sub Streams</option>';
            if (ucourseId) {
                fetch(`get_sub_streams.php?ucourse_id=${ucourseId}`)
                    .then(response => response.json())
                    .then(subStreams => {
                        subStreams.forEach(subStream => {
                            const option = document.createElement('option');
                            option.value = subStream.id;
                            option.textContent = subStream.name;
                            subStreamFilter.appendChild(option);
                        });
                        applyFilters();
                    });
            } else {
                applyFilters();
            }
        }
        function applyFilters() {
            const params = new URLSearchParams();
            const stateId = document.getElementById('stateFilter').value;
            const cityId = document.getElementById('cityFilter').value;
            const streamId = document.getElementById('streamFilter').value;
            const ucourseId = document.getElementById('ucourseFilter').value;
            const subStreamId = document.getElementById('subStreamFilter').value;
            const type = document.getElementById('typeFilter').value;
            const category = document.getElementById('categoryFilter').value;
            const affiliationId = document.getElementById('affiliationFilter').value;
            const courseType = document.getElementById('courseTypeFilter').value;
            const duration = document.getElementById('durationFilter').value;
            const entranceExam = document.getElementById('entranceExamFilter').value;
            if (stateId) params.append('state_id', stateId);
            if (cityId) params.append('city_id', cityId);
            if (streamId) params.append('stream_id', streamId);
            if (ucourseId) params.append('ucourse_id', ucourseId);
            if (subStreamId) params.append('sub_stream_id', subStreamId);
            if (type) params.append('type', type);
            if (category) params.append('category', category);
            if (affiliationId) params.append('affiliation_id', affiliationId);
            if (courseType) params.append('course_type', courseType);
            if (duration) params.append('duration', duration);
            if (entranceExam) params.append('entrance_exam', entranceExam);
            window.history.replaceState(null, '', `?${params.toString()}`);
            window.location.reload();
        }
        function resetFilters() {
            document.getElementById('stateFilter').value = '';
            document.getElementById('cityFilter').innerHTML = '<option value="">All Cities</option>';
            document.getElementById('streamFilter').value = '';
            document.getElementById('ucourseFilter').innerHTML = '<option value="">All Courses</option>';
            document.getElementById('subStreamFilter').innerHTML = '<option value="">All Sub Streams</option>';
            document.getElementById('typeFilter').value = '';
            document.getElementById('categoryFilter').value = '';
            document.getElementById('affiliationFilter').value = '';
            document.getElementById('courseTypeFilter').value = '';
            document.getElementById('durationFilter').value = '';
            document.getElementById('entranceExamFilter').value = '';
            applyFilters();
        }
        // if (window.innerWidth >= 768) {
        //     document.getElementById('advancedFilters').classList.remove('hidden');
        //     document.getElementById('advancedFilterIcon').classList.remove('fa-chevron-down');
        //     document.getElementById('advancedFilterIcon').classList.add('fa-chevron-up');
        // }
    </script>
</body>
</html>