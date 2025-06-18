<style>
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    .scrollable-container {
        overflow-x: auto;
    }
</style>

<?php 
echo '<div class="bg-gray-800 p-4 mt-4 mb-4">
    <div class="scrollable-container no-scrollbar">
        <div class="flex justify-start space-x-6 text-sm sm:text-base">
            <a href="index.php" class="text-white px-6 py-2 rounded-md hover:bg-blue-600 transition">Home</a>
            <a href="states.php" class="text-white px-6 py-2 rounded-md hover:bg-blue-600 transition">States</a>
            <a href="cities.php" class="text-white px-6 py-2 rounded-md hover:bg-blue-600 transition">Cities</a>
            <a href="streams.php" class="text-white px-6 py-2 rounded-md hover:bg-blue-600 transition">Streams</a>
            <a href="ucourses.php" class="text-white px-6 py-2 rounded-md hover:bg-blue-600 transition">Courses</a>
            <a href="substream.php" class="text-white px-6 py-2 rounded-md hover:bg-blue-600 transition">Sub-Stream</a>
            <a href="course_durations.php" class="text-white px-6 py-2 rounded-md hover:bg-blue-600 transition">Durations</a>
            <a href="program_types.php" class="text-white px-6 py-2 rounded-md hover:bg-blue-600 transition">Program Types</a>
            <a href="exam_categories.php" class="text-white px-6 py-2 rounded-md hover:bg-blue-600 transition">Exam Categories</a>
            <a href="exams.php" class="text-white px-6 py-2 rounded-md hover:bg-blue-600 transition">Entrance Exams</a>
            <a href="manage_categories.php" class="text-white px-6 py-2 rounded-md hover:bg-blue-600 transition">University Category</a>
            <a href="affiliation.php" class="text-white px-6 py-2 rounded-md hover:bg-blue-600 transition">Affiliation</a>
            <a href="university_course.php" class="text-white px-6 py-2 rounded-md hover:bg-blue-600 transition">University Courses</a>
        </div>
    </div>
</div>';
?>
