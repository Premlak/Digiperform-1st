<?php
include 'db.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['email'])) {
    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "❌ Invalid email address.";
    } else {
        $check = $pdo->prepare("SELECT 1 FROM footer_user WHERE email = ?");
        $check->execute([$email]);

        if ($check->fetch()) {
            $message = "✅ You're already subscribed!";
        } else {
            $insert = $pdo->prepare("INSERT INTO footer_user (email) VALUES (?)");
            if ($insert->execute([$email])) {
                $message = "✅ Thanks for subscribing!";
            } else {
                $message = "❌ Something went wrong. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Footer Newsletter</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
      min-height: 100vh;
    }
  </style>
</head>
<body>

<footer class="bg-slate-800 text-white pt-16 pb-8">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <div>
        <h3 class="text-xl font-bold mb-6">Digiperform</h3>
        <p class="text-slate-300 mb-6">
          Your comprehensive guide to education and career planning.
        </p>
        <div class="flex space-x-4">
          <a href="#" class="w-10 h-10 rounded-full bg-slate-700 flex items-center justify-center hover:bg-primary transition-colors duration-300">
            <i class="fab fa-facebook-f"></i>
          </a>
          <a href="#" class="w-10 h-10 rounded-full bg-slate-700 flex items-center justify-center hover:bg-primary transition-colors duration-300">
            <i class="fab fa-twitter"></i>
          </a>
          <a href="#" class="w-10 h-10 rounded-full bg-slate-700 flex items-center justify-center hover:bg-primary transition-colors duration-300">
            <i class="fab fa-instagram"></i>
          </a>
          <a href="#" class="w-10 h-10 rounded-full bg-slate-700 flex items-center justify-center hover:bg-primary transition-colors duration-300">
            <i class="fab fa-linkedin-in"></i>
          </a>
        </div>
      </div>
      <div>
        <h4 class="text-lg font-semibold mb-6">Newsletter</h4>
        <p class="text-slate-300 mb-4">Subscribe for latest updates and resources.</p>
        <form method="POST" class="flex flex-col space-y-3">
          <input type="email" name="email" placeholder="Enter your email" required class="px-4 py-2.5 rounded-lg bg-slate-700 border border-slate-600 focus:outline-none focus:ring-2 focus:ring-primary">
          <button type="submit" class="px-4 py-2.5 bg-gradient-to-r from-primary to-secondary text-white rounded-lg font-medium hover:opacity-90 transition-opacity duration-300">
            Subscribe
          </button>
        </form>
      </div>
    </div>
    <div class="border-t border-slate-700 mt-12 pt-8 text-center text-slate-400">
      <p>&copy; DigiPerform. All rights reserved.</p>
    </div>
  </div>
</footer>

<?php if (!empty($message)): ?>
<script>
  alert("<?= addslashes($message) ?>");
</script>
<?php endif; ?>

</body>
</html>
