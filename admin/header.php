<style>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
<?php
echo '<div class="bg-gray-800 p-4 mt-4 mb-4">
    <div class="overflow-x-auto no-scrollbar">
        <div class="flex justify-center space-x-6">
            <a href="index.php" class="text-white px-6 py-2 rounded-md hover:bg-blue-600 transition">Home</a>
            <a href="footer_user.php" class="text-white px-6 py-2 rounded-md hover:bg-blue-600 transition">News Letter Subscribers</a>
        </div>
    </div>
</div>';
?>
