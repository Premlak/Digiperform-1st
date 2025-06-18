<?php
ob_clean();
header('Content-Type: application/xml; charset=utf-8');
include 'db.php';

function urlEntry($loc, $freq = 'hourly', $pri = '0.6') {
    $loc = htmlspecialchars($loc, ENT_QUOTES, 'UTF-8');
    return "<url><loc>{$loc}</loc><changefreq>{$freq}</changefreq><priority>{$pri}</priority></url>";
}

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

try {
    echo urlEntry('https://mycareeridea.com/', 'hourly', '1.0');

    $queries = [
        ["SELECT id, name FROM entrance_exams", "exams.php", "exam.php"],
        ["SELECT id, name FROM realcourses", "course.php", null],
        ["SELECT id, title as name FROM news", "news.php", null],
        ["SELECT id, name FROM realjob", "job.php", null],
        ["SELECT id, name FROM questions", "test.php", null],
    ];

    foreach ($queries as [$sql, $page1, $page2]) {
        $stmt = $pdo->query($sql);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $id = (int)$row['id'];
            $name = rawurlencode($row['name']);
            echo urlEntry("https://mycareeridea.com/{$page1}?id={$id}&name={$name}", 'hourly', '0.6');
            if ($page2) {
                echo urlEntry("https://mycareeridea.com/{$page2}?id={$id}&name={$name}", 'hourly', '0.6');
            }
        }
    }
} catch (Exception $e) {
   
}

echo '</urlset>';
