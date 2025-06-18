<?php
session_start();
if (!isset($_SESSION['handler']) || $_SESSION['handler'] != "university") {
    header("Location: ../index.php");
    exit;
}

require '../../db.php';

if (isset($_POST['delete_mapping_id'])) {
    $id = (int)$_POST['delete_mapping_id'];

    // Delete from child tables first
    $pdo->prepare("DELETE FROM university_course_program_types WHERE university_course_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM university_course_entrance_exams WHERE university_course_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM university_course_substreams WHERE university_course_id = ?")->execute([$id]);

    $pdo->prepare("DELETE FROM university_courses WHERE id = ?")->execute([$id]);
    header("Location: university_course.php");
    exit;
}

// Fetch initial data
$universities = $pdo->query("
    SELECT 
        universities.id, 
        universities.name, 
        states.name AS state_name, 
        cities.name AS city_name
    FROM universities
    LEFT JOIN states ON states.id = universities.state_id
    LEFT JOIN cities ON cities.id = universities.city_id
    ORDER BY universities.name
")->fetchAll();
$streams = $pdo->query("SELECT id, name FROM streams ORDER BY name")->fetchAll();
$durations = $pdo->query("SELECT id, duration FROM course_durations ORDER BY duration")->fetchAll();
$program_types = $pdo->query("SELECT id, name FROM program_types ORDER BY name")->fetchAll();
$exams = $pdo->query("SELECT id, name FROM entrance_exams ORDER BY name")->fetchAll();

// Handle Insert
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $university_id = $_POST['university_id'];
    $stream_id = $_POST['stream_id'];
    $course_id = $_POST['course_id'];
    $fee = $_POST['fee'];
    $duration_id = $_POST['duration_id'];
    $program_type_ids = $_POST['program_type_ids'] ?? [];
    $exam_ids = $_POST['exam_ids'] ?? [];
    $substream_id = $_POST['substream_ids'] ?? null;

    // Insert university_course
    $stmt = $pdo->prepare("INSERT INTO university_courses (university_id, course_id, avg_fee_per_year, duration_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$university_id, $course_id, $fee, $duration_id]);
    $university_course_id = $pdo->lastInsertId();

    // Insert many-to-many mappings
    foreach ($program_type_ids as $pid) {
        $pdo->prepare("INSERT INTO university_course_program_types (university_course_id, program_type_id) VALUES (?, ?)")->execute([$university_course_id, $pid]);
    }

    foreach ($exam_ids as $eid) {
        $pdo->prepare("INSERT INTO university_course_entrance_exams (university_course_id, entrance_exam_id) VALUES (?, ?)")->execute([$university_course_id, $eid]);
    }

    if ($substream_id) {
    $pdo->prepare("INSERT INTO university_course_substreams (university_course_id, substream_id) VALUES (?, ?)")->execute([$university_course_id, $substream_id]);
    }

    header("Location: university_course.php?success=1");
    exit;
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Map University Course</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    async function fetchCourses() {
    const streamId = document.getElementById('stream_id').value;
    const res = await fetch(`get_courses.php?stream_id=${streamId}`);
    const courses = await res.json();
    const courseSelect = document.getElementById('course_id');
    courseSelect.innerHTML = "";

    courses.forEach(course => {
        const opt = document.createElement("option");
        opt.value = course.id;
        opt.textContent = course.name;
        courseSelect.appendChild(opt);
    });
    if (courses.length > 0) {
        courseSelect.selectedIndex = 0;
        fetchSubstreams(); // ✅ Trigger substream fetch right away
    }
}

    </script>
</head>
<body class="p-6 bg-gray-100">
    <?= include 'header.php';?>
<div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">
    <h2 class="text-2xl font-bold mb-4">Map Course to University</h2>

    <form method="POST" class="space-y-4">
        <div>
            <label>University</label>
            <select name="university_id" class="w-full border p-2 rounded" required>
                <?php foreach ($universities as $u): ?>
                    <option value="<?= $u['id'] ?>"><?= $u['name'] ?> -> <?= $u['state_name'] ?> -> <?= $u['city_name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label>Stream</label>
            <select name="stream_id" id="stream_id" class="w-full border p-2 rounded" required onchange="fetchCourses()">
                <option disabled selected>Select stream</option>
                <?php foreach ($streams as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= $s['name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>

       <div>
    <label>Course</label>
    <select name="course_id" id="course_id" class="w-full border p-2 rounded" required onchange="fetchSubstreams()">
    </select>
</div>

<div id="substream_container" class="hidden">
    <label>Substream</label>
<select required name="substream_ids" id="substreams" class="w-full border p-2 rounded">
        <option disabled selected>Select substream</option>
    </select>
</div>



        <div>
            <label>Course Fee (per year)</label>
            <input type="number" name="fee" class="w-full border p-2 rounded" required>
        </div>

        <div>
            <label>Duration</label>
            <select name="duration_id" class="w-full border p-2 rounded" required>
                <?php foreach ($durations as $d): ?>
                    <option value="<?= $d['id'] ?>"><?= $d['duration'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label>Program Types</label>
            <div class="grid grid-cols-2 gap-2">
                <?php foreach ($program_types as $pt): ?>
                    <label><input type="checkbox" name="program_type_ids[]" value="<?= $pt['id'] ?>"> <?= $pt['name'] ?></label>
                <?php endforeach; ?>
            </div>
        </div>

        <div>
            <label>Entrance Exams</label>
            <div class="grid grid-cols-2 gap-2">
                <?php foreach ($exams as $ex): ?>
                    <label><input type="checkbox" name="exam_ids[]" value="<?= $ex['id'] ?>"> <?= $ex['name'] ?></label>
                <?php endforeach; ?>
            </div>
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save Course Mapping</button>
    </form>
    <?php
$mappings = $pdo->query("
    SELECT 
        uc.id, 
        u.name AS university_name, 
        c.name AS course_name, 
        d.duration, 
        uc.avg_fee_per_year, 
        st.name AS streamName,
        GROUP_CONCAT(ss.name SEPARATOR ', ') AS substreams
    FROM university_courses uc
    JOIN universities u ON u.id = uc.university_id
    JOIN ucourses c ON c.id = uc.course_id
    JOIN course_durations d ON d.id = uc.duration_id
    JOIN streams st ON st.id = c.stream_id
    LEFT JOIN university_course_substreams ucs ON uc.id = ucs.university_course_id
    LEFT JOIN sub_streams ss ON ss.id = ucs.substream_id
    GROUP BY uc.id
    ORDER BY uc.id DESC
")->fetchAll();
?>

<div class="mt-10">
    <h2 class="text-xl font-bold mb-4">Mapped Courses</h2>
    <table class="w-full text-sm text-left border border-gray-300">
        <thead class="bg-gray-200">
            <tr>
                <th class="p-2 border">University</th>
                <th class="p-2 border">Stream</th>
                <th class="p-2 border">Course</th>
                <th class="p-2 border">Substreams</th>
                <th class="p-2 border">Duration</th>
                <th class="p-2 border">Fee/Year</th>
                <th class="p-2 border">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($mappings as $m): ?>
            <tr class="border-t">
                <td class="p-2 border"><?= htmlspecialchars($m['university_name']) ?></td>
                <td class="p-2 border"><?= htmlspecialchars($m['streamName']) ?></td>
                <td class="p-2 border"><?= htmlspecialchars($m['course_name']) ?></td>
                <td class="p-2 border"><?= htmlspecialchars($m['substreams'] ?? '-') ?></td>
                <td class="p-2 border"><?= htmlspecialchars($m['duration']) ?></td>
                <td class="p-2 border">₹<?= number_format($m['avg_fee_per_year']) ?></td>
                <td class="p-2 border text-red-600">
                    <form method="POST" onsubmit="return confirm('Delete this mapping?');">
                        <input type="hidden" name="delete_mapping_id" value="<?= $m['id'] ?>">
                        <button class="underline">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</div>
</body>
<script>
async function fetchSubstreams() {
    const courseId = document.getElementById('course_id').value;
    console.log(courseId)
    const res = await fetch(`get_substreams.php?course_id=${courseId}`);
    const substreams = await res.json();
    const substreamContainer = document.getElementById('substream_container');
    const substreamSelect = document.getElementById('substreams');
    substreamSelect.innerHTML = '<option disabled selected>Select substream</option>';
    if (substreams.length > 0) {
        substreamContainer.classList.remove('hidden');
        substreams.forEach(sub => {
            const option = document.createElement("option");
            option.value = sub.id;
            option.textContent = sub.name;
            substreamSelect.appendChild(option);
        });
    } else {
        substreamContainer.classList.add('hidden');
    }
}
</script>
</html>
