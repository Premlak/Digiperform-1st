<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= htmlspecialchars($_GET['name']); ?></title>
  <link rel="icon" href="./assets/logo.png" type="image/png"/>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  
  <!-- MathJax Configuration -->
  <script>
    MathJax = {
      tex: {
        inlineMath: [['\\(', '\\)']]
      },
      svg: {
        fontCache: 'global'
      }
    };
  </script>
  <script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-svg.js"></script>
  
  <style>
    .option.selected.correct {
      background-color: #d1fae5;
      border-color: #10b981;
    }
    .option.selected.incorrect {
      background-color: #fee2e2;
      border-color: #ef4444;
    }
    .option.correct-answer {
      background-color: #d1fae5;
      border-color: #10b981;
    }
    .math-container {
      display: inline-block;
      margin: 2px 0;
    }
  </style>
</head>
<body class="relative w-full bg-gray-50">
<?php include './nav.php'; ?>
<div class="min-h-screen">
  <div class="container mx-auto px-4 py-8 max-w-4xl">
    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
      <div id="questions-container" class="p-6">
        <?php foreach ($questions as $i => $q): ?>
        <div class="question-container mb-8 border-b pb-8" data-question-index="<?= $i ?>">
          <div class="flex items-start mb-4">
            <span class="bg-indigo-100 text-indigo-800 font-bold rounded-full h-8 w-8 flex items-center justify-center mr-3 flex-shrink-0">
              <?= $i+1 ?>
            </span>
            <h3 class="text-lg font-medium text-gray-800">
              <span class="math-container"><?= $q['question'] ?></span>
            </h3>
          </div>
          <div class="options-container ml-11 space-y-3">
            <?php foreach (['opt1', 'opt2', 'opt3', 'opt4'] as $optIndex => $optKey): ?>
            <div 
              class="option p-3 border rounded-lg cursor-pointer transition-all hover:bg-indigo-50"
              data-option-index="<?= $optIndex ?>"
              data-correct-index="<?= $q['correctIndex'] ?>"
            >
              <div class="flex items-center">
                <span class="font-medium mr-3"><?= chr(65 + $optIndex) ?>. </span>
                <span class="math-container"><?= $q[$optKey] ?></span>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <div class="explanation mt-4 ml-11 hidden">
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
              <p class="font-medium text-blue-800">Explanation:</p>
              <p class="text-blue-700 math-container"><?= $q['explanation'] ?></p>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
<?php include './footer.php'; ?>
<script>
$(document).ready(function() {
  // Process math content after MathJax loads
  MathJax.startup.promise.then(() => {
    $('.math-container').each(function() {
      MathJax.typesetPromise([this]);
    });
  });

  $('.option').click(function() {
    const $option = $(this);
    const $question = $option.closest('.question-container');
    const correctIndex = parseInt($option.data('correct-index'));
    const selectedIndex = parseInt($option.data('option-index'));
    
    $question.find('.option').removeClass('selected correct incorrect correct-answer');
    $option.addClass('selected');
    
    if (selectedIndex === correctIndex) {
      $option.addClass('correct');
    } else {
      $option.addClass('incorrect');
      $question.find(`.option[data-option-index="${correctIndex}"]`).addClass('correct-answer');
    }
    
    $question.find('.explanation').removeClass('hidden').slideDown();
  });

  // Responsive adjustments
  function handleResize() {
    $('.options-container, .explanation').toggleClass('ml-11', $(window).width() >= 768);
  }
  
  handleResize();
  $(window).resize(handleResize);
});
</script>
</body>
</html>