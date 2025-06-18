<?php
session_start();
if (!isset($_SESSION['handler']) || $_SESSION['handler'] != "exams") {
    header("Location: ../index.php");
    exit;
}
include '../../db.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}
$examId = $_GET['id'];
$message = '';
$error = '';
if (isset($_POST['submit'])) {
    $questions = [];
    for ($i = 1; isset($_POST["question$i"]); $i++) {
        if (isset($_POST["deleted$i"]) && $_POST["deleted$i"] == "1") {
            continue;
        }
        $q = $_POST["question$i"];
        $opts = $_POST["options$i"];
        $exp = $_POST["explanation$i"];
        $correct = $_POST["correct-answer$i"];
        $questions[] = [
            "question" => $q,
            "opt1" => $opts[0],
            "opt2" => $opts[1],
            "opt3" => $opts[2],
            "opt4" => $opts[3],
            "correctIndex" => (int)$correct,
            "explanation" => $exp
        ];
    }
    $jsonData = json_encode($questions, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    $stmt = $pdo->prepare("UPDATE questions SET content = ? WHERE id = ?");
    if ($stmt->execute([$jsonData, $examId])) {
       echo '<script>alert("Questions saved successfully!");</script>';
    } else {
        $_SESSION['error'] = "Failed to save questions.";
    }
    exit;
}
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("UPDATE questions SET content = '' WHERE id = ?");
    $stmt->execute([$examId]);
    $_SESSION['success'] = "All questions deleted successfully!";
    header("Location: " . $_SERVER['PHP_SELF'] . "?id=$examId");
    exit;
}
$existingQuestions = [];
$stmt = $pdo->prepare("SELECT content FROM questions WHERE id = ?");
$stmt->execute([$examId]);
$jsonContent = $stmt->fetchColumn();
if ($jsonContent) {
    $existingQuestions = json_decode($jsonContent, true);
}

if (isset($_SESSION['success'])) {
    $message = $_SESSION['success'];
    unset($_SESSION['success']);
} 
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Exam</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- MathJax Configuration -->
    <script>
        MathJax = {
            tex: {
                inlineMath: [['\\(', '\\)']],
                packages: ['base', 'ams', 'boldsymbol']
            },
            loader: {
                load: ['[tex]/ams', '[tex]/boldsymbol']
            },
            startup: {
                ready: () => {
                    MathJax.startup.defaultReady();
                    MathJax.startup.promise.then(() => {
                        document.querySelectorAll('.math-preview').forEach(elem => {
                            MathJax.typesetPromise([elem]);
                        });
                    });
                }
            }
        };
    </script>
    <script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-chtml.js"></script>
    <style>
        .math-preview {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 10px;
            margin-top: 5px;
            min-height: 40px;
        }
        .symbol-btn {
            padding: 2px 5px;
            margin: 2px;
            font-size: 0.8rem;
        }
        .symbol-palette {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin: 10px 0;
            padding: 10px;
            background: #e9ecef;
            border-radius: 5px;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="container mt-4">
    <h2>Create Exam Questions</h2>
    
    <?php if ($message): ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <a href="?id=<?= $examId ?>&delete=true" class="btn btn-danger mb-3" onclick="return confirm('Are you sure you want to delete ALL questions?')">Delete All Questions</a>
    
    <form method="post" id="mcq-form">
        <div id="questions-container"></div>
        <button type="button" id="new-question-btn" class="btn btn-info mt-3">Add New Question</button>
        <button type="submit" name="submit" class="btn btn-primary mt-3">Save All Questions</button>
    </form>
</div>

<script>
// Extended symbol mapping including those in your example
const symbolMap = {
    // Greek letters
    "α": "\\alpha", "β": "\\beta", "γ": "\\gamma", "δ": "\\delta", "ε": "\\varepsilon",
    "ζ": "\\zeta", "η": "\\eta", "θ": "\\theta", "ι": "\\iota", "κ": "\\kappa",
    "λ": "\\lambda", "μ": "\\mu", "ν": "\\nu", "ξ": "\\xi", "π": "\\pi",
    "ρ": "\\rho", "σ": "\\sigma", "τ": "\\tau", "υ": "\\upsilon", "φ": "\\phi",
    "χ": "\\chi", "ψ": "\\psi", "ω": "\\omega",
    
    // Uppercase Greek
    "Α": "\\Alpha", "Β": "\\Beta", "Γ": "\\Gamma", "Δ": "\\Delta", "Ε": "\\Epsilon",
    "Ζ": "\\Zeta", "Η": "\\Eta", "Θ": "\\Theta", "Ι": "\\Iota", "Κ": "\\Kappa",
    "Λ": "\\Lambda", "Μ": "\\Mu", "Ν": "\\Nu", "Ξ": "\\Xi", "Π": "\\Pi",
    "Ρ": "\\Rho", "Σ": "\\Sigma", "Τ": "\\Tau", "Υ": "\\Upsilon", "Φ": "\\Phi",
    "Χ": "\\Chi", "Ψ": "\\Psi", "Ω": "\\Omega",
    
    // Mathematical operators
    "∫": "\\int", "∬": "\\iint", "∭": "\\iiint", "∮": "\\oint", "∂": "\\partial",
    "∇": "\\nabla", "∞": "\\infty", "≈": "\\approx", "≠": "\\neq", "≤": "\\leq",
    "≥": "\\geq", "×": "\\times", "÷": "\\div", "±": "\\pm", "→": "\\to",
    "⇒": "\\Rightarrow", "∈": "\\in", "∉": "\\notin", "∑": "\\sum", "∏": "\\prod",
    "√": "\\sqrt", "∀": "\\forall", "∃": "\\exists", "∄": "\\nexists", "∅": "\\emptyset",
    
    // Special symbols from your example
    "": "\\Rightarrow", // Rightwards double arrow
    "": "\\lambda",     // Lambda
    "": "\\prime",       // Prime
    "": "\\:",           // Space
    "–": "-",             // En dash to minus
    
    // Fractions and operators
    "⁄": "/",             // Fraction slash
    "⅓": "\\frac{1}{3}", "⅔": "\\frac{2}{3}", "¼": "\\frac{1}{4}", "½": "\\frac{1}{2}"
};

// Common LaTeX templates
const latexTemplates = {
    fraction: '\\frac{}{}',
    integral: '\\int_{}^{}',
    sqrt: '\\sqrt{}',
    power: '^{}',
    subscript: '_{}',
    sum: '\\sum_{}^{}',
    limit: '\\lim_{}',
    vector: '\\vec{}'
};

const preloadedQuestions = <?= json_encode($existingQuestions, JSON_UNESCAPED_UNICODE); ?>;
document.addEventListener("DOMContentLoaded", () => {
    const container = document.getElementById("questions-container");
    const newQuestionBtn = document.getElementById("new-question-btn");
    let questionCount = 0;
    
    // Convert symbols to MathJax format
    function convertSymbols(text) {
        // Convert special symbols to LaTeX
        let converted = text;
        for (const [symbol, latex] of Object.entries(symbolMap)) {
            converted = converted.replace(new RegExp(symbol, 'g'), latex);
        }
        
        // Handle superscripts (e.g., x²)
        converted = converted.replace(/(\w+)([²³⁴⁵⁶⁷⁸⁹⁺⁻⁼⁽⁾ⁿ])/g, (match, base, exp) => {
            return `${base}^{${exp}}`;
        });
        
        // Handle subscripts (e.g., x₁)
        converted = converted.replace(/(\w+)([₁₂₃₄₅₆₇₈₉₊₋₌₍₎])/g, (match, base, sub) => {
            return `${base}_{${sub}}`;
        });
        
        return converted;
    }

    // Insert text at cursor position
    function insertAtCursor(textarea, text) {
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const value = textarea.value;
        
        textarea.value = value.substring(0, start) + text + value.substring(end);
        textarea.selectionStart = textarea.selectionEnd = start + text.length;
        textarea.focus();
        
        // Trigger input event for preview update
        const event = new Event('input', { bubbles: true });
        textarea.dispatchEvent(event);
    }

    // Create symbol palette
    function createSymbolPalette(textareaId) {
        const palette = document.createElement('div');
        palette.className = 'symbol-palette';
        
        // Add common symbols
        const commonSymbols = ['λ', '∫', '∂', '∑', '∏', '√', '∞', '→', '⇒', 'α', 'β', 'γ', 'θ'];
        commonSymbols.forEach(symbol => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-sm btn-outline-secondary symbol-btn';
            btn.textContent = symbol;
            btn.onclick = () => {
                const textarea = document.querySelector(`textarea[name="${textareaId}"]`);
                insertAtCursor(textarea, symbol);
            };
            palette.appendChild(btn);
        });
        
        // Add LaTeX templates
        const templateNames = {
            fraction: 'a/b',
            integral: '∫',
            sqrt: '√',
            power: 'x^y',
            subscript: 'x_y',
            sum: '∑',
            limit: 'lim',
            vector: 'v⃗'
        };
        
        Object.entries(templateNames).forEach(([key, label]) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-sm btn-outline-primary symbol-btn';
            btn.textContent = label;
            btn.title = latexTemplates[key];
            btn.onclick = () => {
                const textarea = document.querySelector(`textarea[name="${textareaId}"]`);
                insertAtCursor(textarea, latexTemplates[key]);
            };
            palette.appendChild(btn);
        });
        
        return palette;
    }

    // Render question block
    function renderQuestion(data = {}) {
        questionCount++;
        const {
            question = '',
            opt1 = '',
            opt2 = '',
            opt3 = '',
            opt4 = '',
            correctIndex = 0,
            explanation = ''
        } = data;
        
        const div = document.createElement("div");
        div.classList.add("border", "p-3", "mb-3", "bg-light", "position-relative");
        div.setAttribute("data-question-id", questionCount);
        
        div.innerHTML = `
            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 mt-2 me-2 delete-question-btn">
                Delete
            </button>
            <input type="hidden" name="deleted${questionCount}" value="0" class="delete-flag">
            <h4>Question ${questionCount}</h4>
            
            <div class="mb-3">
                <label>Question</label>
                <div class="math-input-container">
                    <textarea name="question${questionCount}" class="form-control" rows="2" required>${question}</textarea>
                </div>
                <div class="math-preview" id="preview-question${questionCount}"></div>
            </div>
            
            <div class="mb-3">
                <label>Option 1</label>
                <div class="math-input-container">
                    <textarea name="options${questionCount}[]" class="form-control" required>${opt1}</textarea>
                </div>
                <div class="math-preview" id="preview-opt1-${questionCount}"></div>
            </div>
            
            <div class="mb-3">
                <label>Option 2</label>
                <div class="math-input-container">
                    <textarea name="options${questionCount}[]" class="form-control" required>${opt2}</textarea>
                </div>
                <div class="math-preview" id="preview-opt2-${questionCount}"></div>
            </div>
            
            <div class="mb-3">
                <label>Option 3</label>
                <div class="math-input-container">
                    <textarea name="options${questionCount}[]" class="form-control" required>${opt3}</textarea>
                </div>
                <div class="math-preview" id="preview-opt3-${questionCount}"></div>
            </div>
            
            <div class="mb-3">
                <label>Option 4</label>
                <div class="math-input-container">
                    <textarea name="options${questionCount}[]" class="form-control" required>${opt4}</textarea>
                </div>
                <div class="math-preview" id="preview-opt4-${questionCount}"></div>
            </div>
            
            <div class="mb-3">
                <label class="mt-2">Correct Answer</label>
                <select name="correct-answer${questionCount}" class="form-select" required>
                    <option value="0" ${correctIndex == 0 ? 'selected' : ''}>Option 1</option>
                    <option value="1" ${correctIndex == 1 ? 'selected' : ''}>Option 2</option>
                    <option value="2" ${correctIndex == 2 ? 'selected' : ''}>Option 3</option>
                    <option value="3" ${correctIndex == 3 ? 'selected' : ''}>Option 4</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="mt-2">Explanation</label>
                <div class="math-input-container">
                    <textarea name="explanation${questionCount}" class="form-control" rows="2" required>${explanation}</textarea>
                </div>
                <div class="math-preview" id="preview-explanation${questionCount}"></div>
            </div>
        `;
        container.appendChild(div);
        
        // Add symbol palettes
        const fields = [
            `question${questionCount}`,
            `options${questionCount}[]`,
            `explanation${questionCount}`
        ];
        
        fields.forEach((field, idx) => {
            const container = div.querySelector(`textarea[name="${field}"]`).parentNode;
            const palette = createSymbolPalette(field);
            container.appendChild(palette);
        });
        
        // Add delete event
        div.querySelector('.delete-question-btn').addEventListener('click', function() {
            const deleteFlag = div.querySelector('.delete-flag');
            deleteFlag.value = "1";
            div.style.display = 'none';
        });

        // Initialize input handlers
        div.querySelectorAll('textarea').forEach(input => {
            input.addEventListener('input', updatePreview);
            input.addEventListener('paste', handlePaste);
        });
        
        // Initial preview
        updatePreview.call(div);
    }

    // Handle paste events
    function handlePaste(event) {
        event.preventDefault();
        const text = (event.clipboardData || window.clipboardData).getData('text');
        const converted = convertSymbols(text);
        document.execCommand('insertText', false, converted);
        updatePreview.call(this);
    }

    // Update preview
    function updatePreview() {
        const container = this.closest('[data-question-id]');
        if (!container) return;
        
        const qid = container.getAttribute('data-question-id');
        
        // Update question preview
        const questionText = container.querySelector(`textarea[name="question${qid}"]`).value;
        document.getElementById(`preview-question${qid}`).innerHTML = 
            convertSymbols(questionText).replace(/\n/g, '<br>');
        
        // Update options preview
        const options = container.querySelectorAll(`textarea[name="options${qid}[]"]`);
        options.forEach((opt, i) => {
            document.getElementById(`preview-opt${i+1}-${qid}`).innerHTML = 
                convertSymbols(opt.value);
        });
        
        // Update explanation preview
        const expText = container.querySelector(`textarea[name="explanation${qid}"]`).value;
        document.getElementById(`preview-explanation${qid}`).innerHTML = 
            convertSymbols(expText).replace(/\n/g, '<br>');
        
        // Render MathJax
        MathJax.typesetPromise();
    }

    // Load existing questions
    if (Array.isArray(preloadedQuestions)) {
        preloadedQuestions.forEach(q => renderQuestion(q));
    }
    
    // New question button
    newQuestionBtn.addEventListener("click", () => renderQuestion());
    
    // Form submission handler
    document.getElementById('mcq-form').addEventListener('submit', function() {
        // Convert all content before submission
        this.querySelectorAll('textarea').forEach(input => {
            input.value = convertSymbols(input.value);
        });
    });
});
</script>
</body>
</html>