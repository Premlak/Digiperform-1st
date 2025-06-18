<?php
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
$streams = $pdo->query("SELECT * FROM streams")->fetchAll(PDO::FETCH_ASSOC);
$states = $pdo->query("SELECT * FROM states")->fetchAll(PDO::FETCH_ASSOC);
$affiliations = $pdo->query("SELECT * FROM affiliations")->fetchAll(PDO::FETCH_ASSOC);
$examCategories = $pdo->query("SELECT * FROM entrance_exam_categories")->fetchAll(PDO::FETCH_ASSOC);
$programTypes = $pdo->query("SELECT * FROM program_types")->fetchAll(PDO::FETCH_ASSOC);
$durations = $pdo->query("SELECT * FROM course_durations")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Course Finder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#2563eb',
                        secondary: '#1d4ed8',
                        dark: '#1e293b',
                        light: '#f8fafc'
                    }
                }
            }
        }
    </script>
    <style>
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .course-card {
            transition: all 0.3s ease;
        }
        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .accordion-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        .accordion-toggle:checked + .accordion-label + .accordion-content {
            max-height: 1000px;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <header class="bg-gradient-to-r from-primary to-secondary text-white shadow-lg">
            <div class="container mx-auto px-4 py-6">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="flex items-center mb-4 md:mb-0">
                        <i class="fas fa-graduation-cap text-3xl mr-3"></i>
                        <h1 class="text-2xl font-bold">University Course Finder</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <input type="text" placeholder="Search universities..." class="px-4 py-2 rounded-full bg-white/20 placeholder-white/70 text-white focus:outline-none focus:ring-2 focus:ring-white">
                            <i class="fas fa-search absolute right-3 top-3 text-white"></i>
                        </div>
                        <button class="bg-white text-primary px-4 py-2 rounded-full font-medium hover:bg-gray-100 transition">
                            <i class="fas fa-user mr-2"></i>Login
                        </button>
                    </div>
                </div>
            </div>
        </header>
        <main class="container mx-auto px-4 py-8">
            <div class="mb-8 bg-white rounded-xl shadow-md p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-dark">Filter Courses</h2>
                    <button class="text-primary hover:text-secondary">
                        <i class="fas fa-sync-alt mr-2"></i>Reset Filters
                    </button>
                </div>
                <div class="flex overflow-x-auto pb-4 mb-6 scrollbar-hide space-x-4">
                    <div class="flex-shrink-0">
                        <select class="w-48 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Select State</option>
                            <?php foreach ($states as $state): ?>
                                <option value="<?= $state['id'] ?>"><?= $state['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex-shrink-0">
                        <select class="w-48 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Select City</option>
                        </select>
                    </div>
                    <div class="flex-shrink-0">
                        <select class="w-48 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Select Affiliation</option>
                            <?php foreach ($affiliations as $affiliation): ?>
                                <option value="<?= $affiliation['id'] ?>"><?= $affiliation['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex-shrink-0">
                        <select class="w-48 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">University Type</option>
                            <option value="private">Private</option>
                            <option value="public">Public</option>
                        </select>
                    </div>
                    <div class="flex-shrink-0">
                        <select class="w-48 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">University Category</option>
                            <option value="IIT">IIT</option>
                            <option value="NIT">NIT</option>
                            <option value="AIIMS">AIIMS</option>
                            <option value="IIM">IIM</option>
                            <option value="IIIT">IIIT</option>
                        </select>
                    </div>
                    <div class="flex-shrink-0">
                        <select class="w-48 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Exam Category</option>
                            <?php foreach ($examCategories as $category): ?>
                                <option value="<?= $category['id'] ?>"><?= $category['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex-shrink-0">
                        <select class="w-48 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Program Type</option>
                            <?php foreach ($programTypes as $type): ?>
                                <option value="<?= $type['id'] ?>"><?= $type['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex-shrink-0">
                        <select class="w-48 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Duration</option>
                            <?php foreach ($durations as $duration): ?>
                                <option value="<?= $duration['id'] ?>"><?= $duration['duration'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="border border-gray-200 rounded-xl overflow-hidden">
                    <h3 class="bg-gray-100 px-4 py-3 font-semibold text-dark">Browse by Stream</h3>
                    <div class="p-4">
                        <?php foreach ($streams as $stream): ?>
                        <div class="mb-3 border rounded-lg overflow-hidden">
                            <input type="checkbox" id="stream-<?= $stream['id'] ?>" class="accordion-toggle hidden">
                            <label for="stream-<?= $stream['id'] ?>" class="accordion-label bg-gray-50 px-4 py-3 flex justify-between items-center cursor-pointer hover:bg-gray-100">
                                <div class="flex items-center">
                                    <i class="fas fa-book text-primary mr-3"></i>
                                    <span class="font-medium"><?= $stream['name'] ?></span>
                                </div>
                                <i class="fas fa-chevron-down transform transition-transform duration-300"></i>
                            </label>
                            <div class="accordion-content">
                                <?php 
                                $courses = $pdo->prepare("SELECT * FROM ucourses WHERE stream_id = ?");
                                $courses->execute([$stream['id']]);
                                $courses = $courses->fetchAll(PDO::FETCH_ASSOC);
                                ?>
                                <?php foreach ($courses as $course): ?>
                                <div class="ml-8 border-l-2 border-gray-200 pl-4">
                                    <input type="checkbox" id="course-<?= $course['id'] ?>" class="accordion-toggle hidden">
                                    <label for="course-<?= $course['id'] ?>" class="accordion-label px-4 py-2 flex justify-between items-center cursor-pointer hover:bg-gray-50">
                                        <div class="flex items-center">
                                            <i class="fas fa-graduation-cap text-secondary mr-3"></i>
                                            <span><?= $course['name'] ?> (<?= strtoupper($course['type']) ?>)</span>
                                        </div>
                                        <i class="fas fa-chevron-down text-sm transform transition-transform duration-300"></i>
                                    </label>
                                    <div class="accordion-content">
                                        <?php 
                                        $subStreams = $pdo->prepare("SELECT * FROM sub_streams WHERE ucourse_id = ?");
                                        $subStreams->execute([$course['id']]);
                                        $subStreams = $subStreams->fetchAll(PDO::FETCH_ASSOC);
                                        ?>
                                        <?php if (count($subStreams) > 0): ?>
                                            <?php foreach ($subStreams as $sub): ?>
                                            <div class="ml-8 border-l-2 border-gray-200 pl-4 py-2">
                                                <div class="px-4 py-2 flex justify-between items-center hover:bg-gray-50 cursor-pointer sub-stream" data-course="<?= $course['id'] ?>" data-sub="<?= $sub['id'] ?>">
                                                    <div class="flex items-center">
                                                        <i class="fas fa-code-branch text-green-500 mr-3"></i>
                                                        <span><?= $sub['name'] ?></span>
                                                    </div>
                                                    <i class="fas fa-arrow-right text-xs text-gray-400"></i>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="ml-8 pl-4 py-3 text-gray-500 italic">
                                                No sub-streams available
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-dark">Course Details</h2>
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-600">23 courses found</span>
                        <div class="flex">
                            <button class="px-3 py-1 border border-gray-300 rounded-l-lg hover:bg-gray-100">
                                <i class="fas fa-th"></i>
                            </button>
                            <button class="px-3 py-1 border border-gray-300 border-l-0 rounded-r-lg bg-gray-100">
                                <i class="fas fa-list"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="courseResults">
                    <div class="col-span-full text-center py-12">
                        <i class="fas fa-book-open text-5xl text-gray-300 mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-500">Select a Sub-stream to View Course Details</h3>
                        <p class="text-gray-400 mt-2">Browse through the streams hierarchy to explore available courses</p>
                    </div>
                    <div class="course-card hidden bg-white border border-gray-200 rounded-xl overflow-hidden shadow">
                        <div class="p-5 border-b border-gray-100">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-bold text-lg text-dark">Computer Science & Engineering</h3>
                                    <p class="text-gray-600">Bachelor of Technology (B.Tech)</p>
                                </div>
                                <div class="bg-gray-100 p-2 rounded-lg">
                                    <div class="w-10 h-10 bg-gray-300 rounded"></div>
                                </div>
                            </div>
                        </div>
                        <div class="p-5">
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <p class="text-xs text-gray-500">Duration</p>
                                    <p class="font-medium">4 Years</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Avg Fee/Year</p>
                                    <p class="font-medium">₹1,25,000</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Entrance Exam</p>
                                    <p class="font-medium">JEE Main</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Program Type</p>
                                    <p class="font-medium">Full Time</p>
                                </div>
                            </div>
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-xs text-gray-500">University</p>
                                    <p class="font-medium">Indian Institute of Technology</p>
                                </div>
                                <button class="text-primary hover:text-secondary">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-5 py-3 flex justify-between items-center">
                            <span class="text-sm font-medium bg-blue-100 text-blue-800 px-2 py-1 rounded">NIRF Rank: 5</span>
                            <button class="text-primary hover:text-secondary font-medium">
                                Apply Now <i class="fas fa-external-link-alt ml-1"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="mt-8 flex justify-center">
                    <nav class="inline-flex">
                        <a href="#" class="py-2 px-3 ml-0 rounded-l-lg border border-gray-300 bg-white text-gray-500 hover:bg-gray-100">
                            <i class="fas fa-angle-left"></i>
                        </a>
                        <a href="#" class="py-2 px-4 border border-gray-300 bg-white text-primary font-medium hover:bg-gray-100">1</a>
                        <a href="#" class="py-2 px-4 border border-gray-300 bg-white text-gray-500 hover:bg-gray-100">2</a>
                        <a href="#" class="py-2 px-4 border border-gray-300 bg-white text-gray-500 hover:bg-gray-100">3</a>
                        <a href="#" class="py-2 px-4 border border-gray-300 bg-white text-gray-500 hover:bg-gray-100">4</a>
                        <a href="#" class="py-2 px-4 border border-gray-300 bg-white text-gray-500 hover:bg-gray-100">5</a>
                        <a href="#" class="py-2 px-3 rounded-r-lg border border-gray-300 bg-white text-gray-500 hover:bg-gray-100">
                            <i class="fas fa-angle-right"></i>
                        </a>
                    </nav>
                </div>
            </div>
        </main>
    </div>
    <script>
        document.querySelectorAll('.accordion-toggle').forEach(toggle => {
            toggle.addEventListener('change', function() {
                const label = this.nextElementSibling;
                const icon = label.querySelector('.fa-chevron-down');
                if (this.checked) {
                    icon.classList.add('rotate-180');
                } else {
                    icon.classList.remove('rotate-180');
                }
            });
        });
        document.querySelectorAll('.sub-stream').forEach(item => {
            item.addEventListener('click', function() {
                const courseId = this.dataset.course;
                const subId = this.dataset.sub;
                document.getElementById('courseResults').innerHTML = '';
                for (let i = 0; i < 3; i++) {
                    const card = document.createElement('div');
                    card.className = 'course-card bg-white border border-gray-200 rounded-xl overflow-hidden shadow';
                    card.innerHTML = `
                        <div class="p-5 border-b border-gray-100">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-bold text-lg text-dark">${this.textContent.trim()}</h3>
                                    <p class="text-gray-600">Bachelor of Technology (B.Tech)</p>
                                </div>
                                <div class="bg-gray-100 p-2 rounded-lg">
                                    <div class="w-10 h-10 bg-gray-300 rounded"></div>
                                </div>
                            </div>
                        </div>
                        <div class="p-5">
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <p class="text-xs text-gray-500">Duration</p>
                                    <p class="font-medium">4 Years</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Avg Fee/Year</p>
                                    <p class="font-medium">₹${(120000 + i*5000).toLocaleString('en-IN')}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Entrance Exam</p>
                                    <p class="font-medium">JEE Main</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Program Type</p>
                                    <p class="font-medium">Full Time</p>
                                </div>
                            </div>
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-xs text-gray-500">University</p>
                                    <p class="font-medium">Indian Institute of Technology</p>
                                </div>
                                <button class="text-primary hover:text-secondary">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-5 py-3 flex justify-between items-center">
                            <span class="text-sm font-medium bg-blue-100 text-blue-800 px-2 py-1 rounded">NIRF Rank: ${5+i}</span>
                            <button class="text-primary hover:text-secondary font-medium">
                                Apply Now <i class="fas fa-external-link-alt ml-1"></i>
                            </button>
                        </div>
                    `;
                    document.getElementById('courseResults').appendChild(card);
                }
                document.querySelectorAll('.sub-stream').forEach(el => {
                    el.classList.remove('bg-blue-50', 'border-l-2', 'border-primary');
                });
                this.classList.add('bg-blue-50', 'border-l-2', 'border-primary');
            });
        });
    </script>
</body>
</html>