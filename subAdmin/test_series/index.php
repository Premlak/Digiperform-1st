<?php
session_start();
if(!isset($_SESSION['handler']) || $_SESSION['handler'] != "test_series"){
    header("Location: ../index.php");
}
include '../../db.php';
?>