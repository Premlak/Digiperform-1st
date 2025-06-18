<?php
session_start();
require_once '../db.php';
if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    header('Location: home.php');
    exit;
}
if (isset($_POST['submit'])) {
    $id = $_POST['id'] ?? '';
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT * FROM admin WHERE email = :id AND password = :password');
    $stmt->execute(['id' => $id, 'password' => $password]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $_SESSION['login'] = true;
        header('Location: home.php');
        exit;
    } else {
        $error = 'Invalid ID or Password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Access</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex justify-center items-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-sm">
        <h2 class="text-2xl font-bold text-center text-gray-700 mb-6">Super Admin Login</h2>
        <?php if (isset($error)): ?>
            <div class="bg-red-500 text-white p-2 rounded mb-4 text-center"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" action="" class="space-y-4">
            <div>
                <label for="id" class="block text-gray-700">ID</label>
                <input type="text" id="id" name="id" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div>
                <label for="password" class="block text-gray-700">Password</label>
                <input type="password" id="password" name="password" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div>
                <button name="submit" type="submit" class="w-full py-2 bg-blue-500 text-white font-semibold rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    Login
                </button>
            </div>
        </form>
    </div>
</body>
</html>
