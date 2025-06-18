<?php
include 'db.php';
$courseData = [];
$coursesQuery = $pdo->query("SELECT * FROM courses");
$courses = $coursesQuery->fetchAll(PDO::FETCH_ASSOC);
$newsData = [];
$ExmaData = [];
$entranceExamCategoryQuery = $pdo->query("SELECT * FROM entrance_exam_categories");
$examCategoryes = $entranceExamCategoryQuery->fetchAll(mode: PDO::FETCH_ASSOC);
$newsCategoriesQuery = $pdo->query("SELECT * FROM news_categories");
$newsCategories = $newsCategoriesQuery->fetchAll(PDO::FETCH_ASSOC);
foreach ($newsCategories as $newsCategory) {
    $newsId = $newsCategory["id"];
    $newsStmt = $pdo->prepare("SELECT * FROM news WHERE category_id = ?");
    $newsStmt->execute([$newsId]);
    $newsList = $newsStmt->fetchAll(PDO::FETCH_ASSOC);
    $newsCategory['subNews'] = $newsList;
    $newsData[] = $newsCategory;
}
foreach ($examCategoryes as $examCategory) {
    $newsId = $examCategory["id"];
    $newsStmt = $pdo->prepare("SELECT * FROM entrance_exams WHERE category_id = ?");
    $newsStmt->execute([$newsId]);
    $newsList = $newsStmt->fetchAll(PDO::FETCH_ASSOC);
    $examCategory['subNews'] = $newsList;
    $ExmaData[] = $examCategory;
}
foreach ($courses as $course) {
    $courseId = $course['id'];
    $course['subcategories'] = [];
    $subcatStmt = $pdo->prepare("SELECT * FROM subcategories WHERE course_id = ?");
    $subcatStmt->execute([$courseId]);
    $subcategories = $subcatStmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($subcategories as $subcat) {
        $subcatId = $subcat['id'];
        $subcat['real_courses'] = [];
        $realCoursesStmt = $pdo->prepare("SELECT * FROM realcourses WHERE subcategory_id = ?");
        $realCoursesStmt->execute([$subcatId]);
        $realCourses = $realCoursesStmt->fetchAll(PDO::FETCH_ASSOC);
        $subcat['real_courses'] = $realCourses;
        $course['subcategories'][] = $subcat;
    }
    $courseData[] = $course;
}
$careerData = [];
$ccategoriesQuery = $pdo->query("SELECT * FROM ccategory");
$ccategories = $ccategoriesQuery->fetchAll(PDO::FETCH_ASSOC);
foreach ($ccategories as $ccat) {
    $ccatId = $ccat['id'];
    $ccat['subcategories'] = [];
    $subcatStmt = $pdo->prepare("SELECT * FROM subcCategory WHERE ccategory_id = ?");
    $subcatStmt->execute([$ccatId]);
    $subcategories = $subcatStmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($subcategories as $subcat) {
        $subcatId = $subcat['id'];
        $subcat['children'] = [];
        $childStmt = $pdo->prepare("SELECT * FROM childcCategory WHERE subcCategory_id = ?");
        $childStmt->execute([$subcatId]);
        $children = $childStmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($children as $child) {
            $childId = $child['id'];
            $jobStmt = $pdo->prepare("SELECT * FROM realJob WHERE childcCategory_id = ?");
            $jobStmt->execute([$childId]);
            $child['realJobs'] = $jobStmt->fetchAll(PDO::FETCH_ASSOC);

            $subcat['children'][] = $child;
        }
        $ccat['subcategories'][] = $subcat;
    }
    $careerData[] = $ccat;
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#2563eb',
                        secondary: '#0ea5e9',
                        dark: '#1e293b',
                        light: '#f8fafc'
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.3s ease-in',
                        'slide-down': 'slideDown 0.3s ease-out',
                        'slide-right': 'slideRight 0.3s ease-out',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite'
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
                        },
                        slideRight: {
                            '0%': {
                                transform: 'translateX(-10px)',
                                opacity: '0'
                            },
                            '100%': {
                                transform: 'translateX(0)',
                                opacity: '1'
                            }
                        }
                    }
                }
            }
        }
    </script>
</head>
<style>
    .mobile-menu {
        transform: translateX(100%);
        transition: transform 0.4s cubic-bezier(0.23, 1, 0.32, 1);
    }
    .mobile-menu.active {
        transform: translateX(0);
    }
    .backdrop {
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
    }
    .backdrop.active {
        opacity: 1;
        pointer-events: all;
    }
</style>
<body class="bg-gradient-to-br from-slate-50 to-slate-100">
    <div class="sticky top-0 z-50 hidden lg:block p-4 rounded-lg">
        <nav class="bg-white shadow-2xl border border-gray-100 mt-4 rounded-lg max-w-screen-xl mx-auto px-2 py-2 relative">
            <div class="flex items-center justify-between flex-wrap p-2 md:px-4">
                <a href="index.php" class="min-w-48"><img class="h-10 min-w-full" src="./assets/logo.png" alt="Logo" /></a>
                <button id="menu-toggle" class="md:hidden text-gray-500 hover:text-gray-900 focus:outline-none">
                    <i class="fas fa-bars"></i>
                </button>
                <div id="desktop-menu" class="hidden md:flex items-center space-x-4">
                    <ul class="flex items-center space-x-4">
                        <li>
                            <a href="index.php" class="px-4 py-2.5 rounded-lg text-gray-700 hover:text-white font-medium transition-all duration-300 hover:bg-primary flex items-center space-x-1">
                                <i class="fas fa-home text-sm"></i>
                                <span>Home</span>
                            </a>
                        </li>
                                                <li class="relative">
                            <button class="peer px-4 py-2.5 rounded-lg text-gray-700 hover:text-white font-medium transition-all duration-300 hover:bg-primary flex items-center space-x-1">
                                    <i class="fas fa-briefcase text-sm"></i>
                                    <span>Career Counseling</span>
                                    <svg class="w-3 h-3 ml-1 transition-transform duration-300 group-hover:rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M19 9l-7 7-7-7" />
                                    </svg>
                            </button>
                            <ul class="absolute z-10 hidden peer-focus:flex hover:flex flex-col bg-white border shadow-md rounded mt-1 whitespace-nowrap">
                                <?php foreach ($careerData as $ccat): ?>
                                    <li class="relative">
                                        <button class="peer px-4 py-2 w-full text-left hover:bg-gray-100">
                                            <?= htmlspecialchars($ccat['name']) ?>
                                        </button>
                                        <?php if (!empty($ccat['subcategories'])): ?>
                                            <ul class="absolute top-0 left-full ml-2 hidden peer-focus:flex hover:flex flex-col bg-white border shadow-md rounded whitespace-nowrap">
                                                <?php foreach ($ccat['subcategories'] as $subcat): ?>
                                                    <li class="relative">
                                                        <button class="peer px-4 py-2 w-full text-left hover:bg-gray-100 mr-1">
                                                            <?= htmlspecialchars($subcat['name']) ?>
                                                        </button>
                                                        <?php if (!empty($subcat['children'])): ?>
                                                            <ul class="absolute top-0 left-full ml-2 hidden peer-focus:flex hover:flex flex-col bg-white border shadow-md rounded whitespace-nowrap">
                                                                <?php foreach ($subcat['children'] as $child): ?>
                                                                    <li class="relative">
                                                                        <button class="peer px-4 py-2 w-full text-left hover:bg-gray-100">
                                                                            Job Name<?= htmlspecialchars($child['name']) ?>
                                                                        </button>
                                                                        <?php if (!empty($child['realJobs'])): ?>
                                                                            <ul class="ml-1 absolute top-0 left-full hidden peer-focus:flex hover:flex flex-col bg-white border shadow-md rounded whitespace-nowrap">
                                                                                <?php foreach ($child['realJobs'] as $job): ?>
                                                                                    <li onclick="window.location.assign('job.php?id=<?= $job['id']; ?>&name=<?= urlencode($job['name']); ?>')" class="cursor-pointer grid grid-col-1 px-4 py-2 hover:bg-gray-100">
                                                                                        <a href="job.php?id=<?= $job['id']; ?>&name=<?= urlencode($job['name']); ?>" class="block">
                                                                                            Name:<?= htmlspecialchars($job['name']) ?>
                                                                                        </a>
                                                                                        <div class="flex flex-col block">
                                                                                            <span class="text-gray-500 text-sm">Qualification: <?= htmlspecialchars($job['qualification']) ?></span>
                                                                                            <span class="text-gray-500 text-sm">Annual Salary: (<?= htmlspecialchars($job['salary']) ?>)</span>
                                                                                            <?php
                                                                                            if ($job['exam_required'] != "") {
                                                                                            ?>
                                                                                                <span class="text-gray-500 text-sm">Entrance Exam: <?= htmlspecialchars($job['exam_required']) ?></span>
                                                                                            <?php
                                                                                            }
                                                                                            ?>
                                                                                        </div>
                                                                                    </li>
                                                                                <?php endforeach; ?>
                                                                            </ul>
                                                                        <?php endif; ?>
                                                                    </li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        <?php endif; ?>

                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                        <li class="relative">
                            <button class="peer px-4 py-2.5 rounded-lg text-gray-700 hover:text-white font-medium transition-all duration-300 hover:bg-primary flex items-center space-x-1">
                                    <i class="fas fa-book text-sm"></i>
                                    <span>Courses</span>
                                    <svg class="w-3 h-3 ml-1 transition-transform duration-300 group-hover:rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            <ul class="absolute z-10 hidden peer-focus:flex hover:flex flex-col bg-white border shadow-md rounded mt-1 whitespace-nowrap">
                                <?php foreach ($courseData as $course): ?>
                                    <li class="relative">
                                        <button class="peer px-4 py-2 w-full text-left hover:bg-gray-100">
                                            <?= htmlspecialchars($course['name']) ?>
                                        </button>

                                        <?php if (!empty($course['subcategories'])): ?>
                                            <ul class="absolute top-0 right-full hidden peer-focus:flex hover:flex flex-col bg-white border shadow-md rounded mr-1 whitespace-nowrap">
                                                <?php foreach ($course['subcategories'] as $subcategory): ?>
                                                    <li class="relative">
                                                        <button class="peer px-4 py-2 w-full text-left hover:bg-gray-100">
                                                            <?= htmlspecialchars($subcategory['name']) ?>
                                                        </button>
                                                        <?php if (!empty($subcategory['real_courses'])): ?>
                                                            <ul class="absolute top-0 right-full hidden peer-focus:flex hover:flex flex-col bg-white border shadow-md rounded mr-1 whitespace-nowrap max-h-64 overflow-auto">
                                                                <?php foreach ($subcategory['real_courses'] as $real): ?>
                                                                    <li class="px-4 py-2 hover:bg-gray-100">
                                                                        <a href="course.php?id=<?= $real['id']; ?>&name=<?= urlencode($real['name']); ?>" class="block">
                                                                            <?= htmlspecialchars($real['name']) ?>
                                                                            <span class="text-gray-500 text-lg">(<?= htmlspecialchars($real['duration']) ?>)</span>
                                                                        </a>
                                                                    </li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        <?php endif; ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                        <li class="relative">
                            <button class="peer px-4 py-2.5 rounded-lg text-gray-700 hover:text-white font-medium transition-all duration-300 hover:bg-primary flex items-center space-x-1">
                                    <i class="fas fa-newspaper text-sm"></i>
                                    <span>News</span>
                                    <svg class="w-3 h-3 ml-1 transition-transform duration-300 group-hover:rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M19 9l-7 7-7-7" />
                                    </svg>
                            </button>
                            <ul class="absolute z-10 hidden peer-focus:flex hover:flex flex-col bg-white border shadow-md rounded mt-1 whitespace-nowrap">
                                <?php foreach ($newsData as $newsCategory): ?>
                                    <li class="relative">
                                        <button class="peer px-4 py-2 w-full text-left hover:bg-gray-100">
                                            <?= htmlspecialchars($newsCategory['name']) ?>
                                        </button>
                                        <?php if (!empty($newsCategory['subNews'])): ?>
                                            <ul class="absolute top-0 right-full hidden peer-focus:flex hover:flex flex-col bg-white border shadow-md rounded mr-1 whitespace-nowrap max-h-64 overflow-auto">
                                                <?php foreach ($newsCategory['subNews'] as $newsItem): ?>
                                                    <li>
                                                        <a href="news.php?id=<?= $newsItem['id'] ?>&title=<?= $newsItem['title']; ?>" class="px-4 py-2 block hover:bg-gray-100">
                                                            <?= htmlspecialchars($newsItem['title']) ?>
                                                        </a>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                        <li class="relative">
                             <button class="peer px-4 py-2.5 rounded-lg text-gray-700 hover:text-white font-medium transition-all duration-300 hover:bg-primary flex items-center space-x-1">
                                    <i class="fas fa-file-alt text-sm"></i>
                                    <span>Exams</span>
                                    <svg class="w-3 h-3 ml-1 transition-transform duration-300 group-hover:rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            <ul class="absolute z-10 hidden peer-focus:flex hover:flex flex-col bg-white border shadow-md rounded mt-1 whitespace-nowrap">
                                <?php foreach ($ExmaData as $newsCategory): ?>
                                    <li class="relative">
                                        <button class="peer px-4 py-2 w-full text-left hover:bg-gray-100">
                                            <?= htmlspecialchars($newsCategory['name']) ?>
                                        </button>
                                        <?php if (!empty($newsCategory['subNews'])): ?>
                                            <ul class="absolute top-0 right-full hidden peer-focus:flex hover:flex flex-col bg-white border shadow-md rounded mr-1 whitespace-nowrap max-h-64 overflow-auto">
                                                <?php foreach ($newsCategory['subNews'] as $newsItem): ?>
                                                    <li>
                                                        <a href="exams.php?id=<?= $newsItem['id'] ?>&name=<?= $newsItem['name']; ?>" class="px-4 py-2 block hover:bg-gray-100">
                                                            <?= htmlspecialchars($newsItem['name']) ?>
                                                        </a>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>
    <header class="sticky top-0 z-50 flex items-center justify-between px-4 py-3 bg-white shadow-md md:hidden block lg:hidden">
        <a href="index.php" class="flex items-center space-x-2">
            <img class="h-10" src="./assets/logo.png" alt="Logo" />
        </a>
        <button id="menu-expand-btn" aria-label="Expand menu" class="text-slate-700 focus:outline-none">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 8h16M4 16h16" />
            </svg>
        </button>
    </header>
    <div id="backdrop" class="backdrop fixed inset-0 bg-black bg-opacity-50 z-40"></div>
    <div id="mobile-menu" x-data="{ openTopMenu: null }" class="mobile-menu fixed inset-y-0 right-0 w-5/6 max-w-sm bg-white z-50 overflow-y-auto shadow-2xl">
        <div class="sticky top-0 flex items-center justify-end px-4 py-3 bg-white shadow-sm">
            <button id="menu-close-btn" aria-label="Close menu" class="text-slate-700 focus:outline-none">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <nav class="py-4 px-4">
            <ul>
                <li class="mb-1">
                    <a href="index.php" class="block w-full text-left p-3 font-semibold text-slate-800 hover:bg-slate-50 rounded-lg transition-colors duration-200">
                        <i class="fas fa-home mr-3 text-primary"></i> Home
                    </a>
                </li>
                <li class="border-b border-slate-100">
                    <button @click="openTopMenu = (openTopMenu === 'career' ? null : 'career')" class="w-full text-left p-3 font-semibold flex justify-between items-center text-slate-800 hover:bg-slate-50 rounded-lg">
                        <div>
                            <i class="fas fa-graduation-cap mr-3 text-primary"></i> Career Counseling
                        </div>
                        <svg :class="{ 'rotate-180': openTopMenu === 'career' }" class="w-5 h-5 transition-transform duration-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <ul x-show="openTopMenu === 'career'" x-transition class="pl-8 mt-1 pb-2 space-y-1">
                        <?php foreach ($careerData as $ccat): ?>
                            <li class="py-1" x-data="{ open: false }">
                                <button @click="open = !open" class="w-full text-left font-medium text-slate-700 hover:text-primary flex justify-between items-center">
                                    <?= htmlspecialchars($ccat['name']) ?>
                                    <svg :class="{ 'rotate-180': open }" class="w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <ul x-show="open" x-transition class="pl-4 mt-1 space-y-1">
                                    <?php foreach ($ccat['subcategories'] as $subcat): ?>
                                        <li class="py-1" x-data="{ open: false }">
                                            <button @click="open = !open" class="w-full text-left text-slate-600 hover:text-primary flex justify-between items-center">
                                                <?= htmlspecialchars($subcat['name']) ?>
                                                <svg :class="{ 'rotate-180': open }" class="w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                            <ul x-show="open" x-transition class="pl-4 mt-1 space-y-1">
                                                <?php foreach ($subcat['children'] as $child): ?>
                                                    <li class="py-1" x-data="{ open: false }">
                                                        <button @click="open = !open" class="w-full text-left text-slate-500 hover:text-primary flex justify-between items-center">
                                                            <?= htmlspecialchars($child['name']) ?>
                                                            <svg :class="{ 'rotate-180': open }" class="w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                                <path d="M19 9l-7 7-7-7" />
                                                            </svg>
                                                        </button>
                                                        <ul x-show="open" x-transition class="pl-4 mt-1 space-y-1">
                                                            <?php foreach ($child['realJobs'] as $job): ?>
                                                                <li class="py-1 cursor-pointer flex flex-col" onclick="window.location.assign('job.php?id=<?= $job['id']; ?>&name=<?= urlencode($job['name']); ?>')">
                                                                    <a href="job.php?id=<?= $job['id']; ?>&name=<?= urlencode($job['name']); ?>" class="block text-slate-500 hover:text-primary">
                                                                        <div class="flex justify-between items-center">
                                                                            <span><?= htmlspecialchars($job['name']) ?></span>
                                                                            <span class="text-xs text-slate-400 bg-slate-100 px-2 py-0.5 rounded"><?= htmlspecialchars($job['qualification']) ?></span>
                                                                        </div>
                                                                    </a>
                                                                    <div class="flex flex-col block">
                                                                        <span class="text-gray-500 text-sm">Qualification: <?= htmlspecialchars($job['qualification']) ?></span>
                                                                        <span class="text-gray-500 text-sm">Annual Salary: (<?= htmlspecialchars($job['salary']) ?>)</span>
                                                                        <?php
                                                                        if ($job['exam_required'] != "") {
                                                                        ?>
                                                                            <span class="text-gray-500 text-sm">Entrance Exam: <?= htmlspecialchars($job['exam_required']) ?></span>
                                                                        <?php
                                                                        }
                                                                        ?>
                                                                    </div>
                                                                </li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                                <li class="border-b border-slate-100">
                    <button @click="openTopMenu = (openTopMenu === 'courses' ? null : 'courses')" class="w-full text-left p-3 font-semibold flex justify-between items-center text-slate-800 hover:bg-slate-50 rounded-lg">
                        <div>
                            <i class="fas fa-book mr-3 text-primary"></i> Courses
                        </div>
                        <svg :class="{ 'rotate-180': openTopMenu === 'courses' }" class="w-5 h-5 transition-transform duration-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <ul x-show="openTopMenu === 'courses'" x-transition class="pl-8 mt-1 pb-2 space-y-1">
                        <?php foreach ($courseData as $cIndex => $course): ?>
                            <li class="py-1" x-data="{ open: false }">
                                <button @click="open = !open" class="w-full text-left font-medium text-slate-700 hover:text-primary flex justify-between items-center">
                                    <?= htmlspecialchars($course['name']) ?>
                                    <svg :class="{ 'rotate-180': open }" class="w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <ul x-show="open" x-transition class="pl-4 mt-1 space-y-1">
                                    <?php foreach ($course['subcategories'] as $subcategory): ?>
                                        <li class="py-1" x-data="{ open: false }">
                                            <button @click="open = !open" class="w-full text-left text-slate-600 hover:text-primary flex justify-between items-center">
                                                <?= htmlspecialchars($subcategory['name']) ?>
                                                <svg :class="{ 'rotate-180': open }" class="w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                            <ul x-show="open" x-transition class="pl-4 mt-1 space-y-1">
                                                <?php foreach ($subcategory['real_courses'] as $real): ?>
                                                    <li class="py-1">
                                                        <a href="course.php?id=<?= $real['id']; ?>&name=<?= urlencode($real['name']); ?>" class="block text-slate-500 hover:text-primary">
                                                            <div class="flex justify-between items-center">
                                                                <span><?= htmlspecialchars($real['name']) ?></span>
                                                                <span class="text-xs text-slate-400 bg-slate-100 px-2 py-0.5 rounded"><?= htmlspecialchars($real['duration']) ?></span>
                                                            </div>
                                                        </a>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <li class="border-b border-slate-100">
                    <button @click="openTopMenu = (openTopMenu === 'news' ? null : 'news')" class="w-full text-left p-3 font-semibold flex justify-between items-center text-slate-800 hover:bg-slate-50 rounded-lg">
                        <div>
                            <i class="fas fa-newspaper mr-3 text-primary"></i> News
                        </div>
                        <svg :class="{ 'rotate-180': openTopMenu === 'news' }" class="w-5 h-5 transition-transform duration-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <ul x-show="openTopMenu === 'news'" x-transition class="pl-8 mt-1 pb-2 space-y-1">
                        <?php foreach ($newsData as $newsCategory): ?>
                            <li class="py-1" x-data="{ open: false }">
                                <button @click="open = !open" class="w-full text-left font-medium text-slate-700 hover:text-primary flex justify-between items-center">
                                    <?= htmlspecialchars($newsCategory['name']) ?>
                                    <svg :class="{ 'rotate-180': open }" class="w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <ul x-show="open" x-transition class="pl-4 mt-1 space-y-1">
                                    <?php foreach ($newsCategory['subNews'] as $news): ?>
                                        <li class="py-1">
                                            <a href="news.php?id=<?= $news['id'] ?>&title=<?= $news['title']; ?>" class="block text-slate-600 hover:text-primary">
                                                <?= htmlspecialchars($news['title']) ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <li class="border-b border-slate-100">
                    <button @click="openTopMenu = (openTopMenu === 'exams' ? null : 'exams')" class="w-full text-left p-3 font-semibold flex justify-between items-center text-slate-800 hover:bg-slate-50 rounded-lg">
                        <div>
                            <i class="fas fa-file-alt mr-3 text-primary"></i> Exams
                        </div>
                        <svg :class="{ 'rotate-180': openTopMenu === 'exams' }" class="w-5 h-5 transition-transform duration-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <ul x-show="openTopMenu === 'exams'" x-transition class="pl-8 mt-1 pb-2 space-y-1">
                        <?php foreach ($ExmaData as $examCategory): ?>
                            <li class="py-1" x-data="{ open: false }">
                                <button @click="open = !open" class="w-full text-left font-medium text-slate-700 hover:text-primary flex justify-between items-center">
                                    <?= htmlspecialchars($examCategory['name']) ?>
                                    <svg :class="{ 'rotate-180': open }" class="w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <ul x-show="open" x-transition class="pl-4 mt-1 space-y-1">
                                    <?php foreach ($examCategory['subNews'] as $exam): ?>
                                        <li class="py-1">
                                            <a href="exams.php?id=<?= $exam['id'] ?>&name=<?= $exam['name']; ?>" class="block text-slate-600 hover:text-primary">
                                                <?= htmlspecialchars($exam['name']) ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            </ul>
        </nav>
    </div>
    <script>
        const mobileMenu = document.getElementById('mobile-menu');
        const closeBtn = document.getElementById('menu-close-btn');
        const expandBtn = document.getElementById('menu-expand-btn');
        const backdrop = document.getElementById('backdrop');
        function openMenu() {
            mobileMenu.classList.add('active');
            backdrop.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        function closeMenu() {
            mobileMenu.classList.remove('active');
            backdrop.classList.remove('active');
            document.body.style.overflow = '';
        }
        expandBtn.addEventListener('click', openMenu);
        closeBtn.addEventListener('click', closeMenu);
        backdrop.addEventListener('click', closeMenu);
        document.addEventListener('click', (e) => {
            if (!mobileMenu.contains(e.target) && !expandBtn.contains(e.target) && mobileMenu.classList.contains('active')) {
                closeMenu();
            }
        });
    </script>
</body>
</html>