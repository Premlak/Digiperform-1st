<?php
session_start();
if (!isset($_GET['handler'])) {
    header("Location: home.php"); 
    exit;
}
$handler = $_GET['handler'];
$_SESSION['SubAdmin'] = $handler;
switch ($handler) {
    case 'courses':
        $_SESSION['handler'] = "courses";
        header('Location: ../subAdmin/courses/index.php');
        break;
    case 'exams':
        $_SESSION['handler'] = "exams";
        header('Location: ../subAdmin/exams/index.php');
        break;
    case 'test_series':
        $_SESSION['handler'] = "test_series";
        header('Location: ../subAdmin/test_series/index.php');
        break;
    case 'counseling':
        $_SESSION['handler'] = "counseling";
        header('Location: ../subAdmin/counseling/index.php');
        break;
    case 'university':
        $_SESSION['handler'] = "university";
        header('Location: ../subAdmin/university/index.php');
        break;
    case "news":
        $_SESSION['handler'] = "news";
        header('Location: ../subAdmin/news/index.php');
        break;    
    default:
        header("Location: super_admin.php");
        break;
}
exit;
?>
