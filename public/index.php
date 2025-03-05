<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use src\Comparers\LineByLineComparer;
use src\IO\FileReader;
use src\IO\FileWriter;
use src\Logging\FileLogger;
use src\Services\ComparisonService;
use src\Exceptions\FileException;

$error = "";
$success = "";
$summary = "";
$uniqueLines1 = [];
$uniqueLines2 = [];
$hasResults = false;

// Start session for storing results
session_start();

// Create dependencies
$logger = new FileLogger();
$fileReader = new FileReader($logger);
$fileWriter = new FileWriter($logger);
$comparer = new LineByLineComparer();
$service = new ComparisonService($fileReader, $fileWriter, $comparer, $logger);

// Handle file downloads
if (isset($_GET['download']) && isset($_SESSION['comparison_results'])) {
    $fileIndex = (int)$_GET['download'];
    
    if ($fileIndex === 1 || $fileIndex === 2) {
        $content = $_SESSION['comparison_results'][$fileIndex] ?? '';
        $fileName = ($fileIndex === 1) ? "unique_lines_file1.txt" : "unique_lines_file2.txt";
        
        // Set headers for file download
        header('Content-Description: File Transfer');
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . strlen($content));
        echo $content;
        exit;
    }
}

// Clear results if requested
if (isset($_GET['clear'])) {
    unset($_SESSION['comparison_results']);
    unset($_SESSION['summary']);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Check if we have results from a previous comparison
if (isset($_SESSION['comparison_results']) && isset($_SESSION['summary'])) {
    $hasResults = true;
    $summary = $_SESSION['summary'];
    $success = "Comparison complete. You can download the results below.";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['inputFile1']) && isset($_FILES['inputFile2'])) {
    try {
        // Create temporary files
        $tempFile1 = tempnam(sys_get_temp_dir(), 'compare1_');
        $tempFile2 = tempnam(sys_get_temp_dir(), 'compare2_');
        $outputFile1 = tempnam(sys_get_temp_dir(), 'output1_');
        $outputFile2 = tempnam(sys_get_temp_dir(), 'output2_');
        
        // Move uploaded files to temporary locations
        move_uploaded_file($_FILES['inputFile1']['tmp_name'], $tempFile1);
        move_uploaded_file($_FILES['inputFile2']['tmp_name'], $tempFile2);
        
        // Compare files
        $result = $service->compareFiles($tempFile1, $tempFile2, $outputFile1, $outputFile2);
        
        // Read the output files to get the unique lines
        $uniqueLines1 = file($outputFile1, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        $uniqueLines2 = file($outputFile2, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        
        // Create a summary
        $summary = "Comparison Results:\n";
        $summary .= "- Unique lines in first file: " . count($uniqueLines1) . "\n";
        $summary .= "- Unique lines in second file: " . count($uniqueLines2) . "\n";
        $summary .= "- Total lines in first file: " . $result['totalLines1'] . "\n";
        $summary .= "- Total lines in second file: " . $result['totalLines2'] . "\n";
        
        // Store results in session for download
        $_SESSION['comparison_results'] = [
            1 => implode("\n", $uniqueLines1),
            2 => implode("\n", $uniqueLines2)
        ];
        $_SESSION['summary'] = $summary;
        
        // Clean up temporary files
        @unlink($tempFile1);
        @unlink($tempFile2);
        @unlink($outputFile1);
        @unlink($outputFile2);
        
        $logger->log("Web UI: Comparison completed successfully", "INFO");
        
        // Redirect to prevent form resubmission
        header('Location: ' . $_SERVER['PHP_SELF'] . '?success=1');
        exit;
        
    } catch (Exception $e) {
        $error = "Error: " . htmlspecialchars($e->getMessage());
        $logger->log("Web UI Error: " . $e->getMessage(), "ERROR");
    }
}

// Set success message if redirected after successful comparison
if (isset($_GET['success'])) {
    $success = "Comparison complete. You can download the results below.";
    $hasResults = true;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Comparison Tool</title>
    <style>
        :root {
            --primary-color: #2196F3;
            --success-color: #4CAF50;
            --error-color: #f44336;
            --background-color: #f5f5f5;
            --card-background: #ffffff;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            max-width: 1000px;
            margin: 40px auto;
            padding: 30px;
            background-color: var(--background-color);
            line-height: 1.6;
        }

        .card {
            background-color: var(--card-background);
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
        }

        .error { 
            color: var(--error-color);
            padding: 10px;
            border-radius: 4px;
            background-color: rgba(244, 67, 54, 0.1);
        }

        .success { 
            color: var(--success-color);
            padding: 10px;
            border-radius: 4px;
            background-color: rgba(76, 175, 80, 0.1);
        }

        pre { 
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            max-height: 300px;
            overflow: auto;
            border: 1px solid #e9ecef;
        }

        .file-input-container {
            margin-bottom: 20px;
        }

        .file-input-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-input-wrapper input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 2px dashed #ccc;
            border-radius: 6px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }

        .file-input-wrapper input[type="file"]:hover {
            border-color: var(--primary-color);
        }

        .file-input-wrapper.drag-over input[type="file"] {
            border-color: var(--primary-color);
            background-color: rgba(33, 150, 243, 0.05);
        }

        .file-name-display {
            margin-top: 5px;
            font-size: 0.9em;
            color: #666;
        }

        button[type="submit"] {
            background-color: var(--primary-color);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.2s;
        }

        button[type="submit"]:hover {
            background-color: #1976D2;
        }

        button[type="submit"]:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        .download-links {
            margin-top: 30px;
        }

        .download-btn {
            display: inline-block;
            background-color: var(--success-color);
            color: white;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 6px;
            margin-right: 15px;
            margin-bottom: 15px;
            transition: background-color 0.2s;
        }

        .download-btn:hover {
            background-color: #388E3C;
        }

        .clear-btn {
            display: inline-block;
            background-color: var(--error-color);
            color: white;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 15px;
            transition: background-color 0.2s;
        }

        .clear-btn:hover {
            background-color: #d32f2f;
        }

        .result-container {
            margin-top: 30px;
        }

        h2, h3 {
            color: #333;
            margin-bottom: 20px;
        }

        .description {
            color: #666;
            margin-bottom: 25px;
        }

        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .spinner {
            border: 5px solid #f3f3f3;
            border-top: 5px solid var(--primary-color);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .theme-toggle-container {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .theme-toggle {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 24px;
        }

        .theme-icon {
            margin-right: 5px;
        }

        .theme-text {
            font-size: 14px;
        }

        body.dark-theme {
            --background-color: #121212;
            --card-background: #1e1e1e;
            --primary-color: #64b5f6;
            color: #e0e0e0;
        }

        body.dark-theme h2, 
        body.dark-theme h3 {
            color: #e0e0e0;
        }

        body.dark-theme .description {
            color: #b0b0b0;
        }

        body.dark-theme pre {
            background: #2d2d2d;
            border-color: #444;
            color: #e0e0e0;
        }
    </style>
</head>
<body>
    <div class="theme-toggle-container">
        <button class="theme-toggle" id="themeToggle" title="Toggle dark/light mode">
            <span class="theme-icon">🌓</span>
            <span class="theme-text">Toggle Dark Mode</span>
        </button>
    </div>
    
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
        <p>Processing files...</p>
    </div>

    <div class="card">
        <h2>File Comparison Tool</h2>
        <p class="description">Compare two lexicographically sorted text files to identify unique lines in each file. Upload your files below to get started.</p>

        <?php if ($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>
        <?php if ($success): ?><p class="success"><?php echo $success; ?></p><?php endif; ?>

        <form id="fileForm" action="" method="post" enctype="multipart/form-data">
            <div class="file-input-container">
                <label class="file-input-label">First File</label>
                <div class="file-input-wrapper" id="fileWrapper1">
                    <input type="file" name="inputFile1" id="inputFile1" required>
                </div>
                <div class="file-name-display" id="fileName1"></div>
            </div>

            <div class="file-input-container">
                <label class="file-input-label">Second File</label>
                <div class="file-input-wrapper" id="fileWrapper2">
                    <input type="file" name="inputFile2" id="inputFile2" required>
                </div>
                <div class="file-name-display" id="fileName2"></div>
            </div>

            <button type="submit" id="submitBtn">Compare Files</button>
        </form>
    </div>

    <?php if ($hasResults): ?>
    <div class="card">
        <div class="download-links">
            <h3>Download Results</h3>
            <a href="?download=1" class="download-btn">Download Unique Lines from File 1</a>
            <a href="?download=2" class="download-btn">Download Unique Lines from File 2</a>
            <div>
                <a href="?clear=1" class="clear-btn">Clear Results</a>
            </div>
        </div>
        
        <div class="result-container">
            <h3>Comparison Results</h3>
            <pre><?php echo htmlspecialchars($summary); ?></pre>
        </div>
    </div>
    <?php endif; ?>

    <script>
        // File input enhancement
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput1 = document.getElementById('inputFile1');
            const fileInput2 = document.getElementById('inputFile2');
            const fileName1 = document.getElementById('fileName1');
            const fileName2 = document.getElementById('fileName2');
            const fileWrapper1 = document.getElementById('fileWrapper1');
            const fileWrapper2 = document.getElementById('fileWrapper2');
            const submitBtn = document.getElementById('submitBtn');
            const fileForm = document.getElementById('fileForm');
            const loadingOverlay = document.getElementById('loadingOverlay');
            const themeToggle = document.getElementById('themeToggle');
            
            // Check if dark mode is saved in localStorage
            if (localStorage.getItem('darkMode') === 'enabled') {
                document.body.classList.add('dark-theme');
            }
            
            // Theme toggle functionality
            themeToggle.addEventListener('click', function() {
                document.body.classList.toggle('dark-theme');
                if (document.body.classList.contains('dark-theme')) {
                    localStorage.setItem('darkMode', 'enabled');
                } else {
                    localStorage.setItem('darkMode', 'disabled');
                }
            });

            // Display file name when selected
            fileInput1.addEventListener('change', function() {
                updateFileName(this, fileName1);
                validateForm();
            });

            fileInput2.addEventListener('change', function() {
                updateFileName(this, fileName2);
                validateForm();
            });

            // Drag and drop functionality
            setupDragAndDrop(fileWrapper1, fileInput1);
            setupDragAndDrop(fileWrapper2, fileInput2);

            // Form submission
            fileForm.addEventListener('submit', function() {
                loadingOverlay.style.display = 'flex';
            });

            // Helper functions
            function updateFileName(input, display) {
                if (input.files && input.files[0]) {
                    const file = input.files[0];
                    display.textContent = `Selected: ${file.name} (${formatFileSize(file.size)})`;
                } else {
                    display.textContent = '';
                }
            }

            function formatFileSize(bytes) {
                if (bytes < 1024) return bytes + ' bytes';
                else if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
                else return (bytes / 1048576).toFixed(1) + ' MB';
            }

            function validateForm() {
                submitBtn.disabled = !(fileInput1.files.length > 0 && fileInput2.files.length > 0);
            }

            function setupDragAndDrop(wrapper, input) {
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    wrapper.addEventListener(eventName, preventDefaults, false);
                });

                function preventDefaults(e) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                ['dragenter', 'dragover'].forEach(eventName => {
                    wrapper.addEventListener(eventName, highlight, false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    wrapper.addEventListener(eventName, unhighlight, false);
                });

                function highlight() {
                    wrapper.classList.add('drag-over');
                }

                function unhighlight() {
                    wrapper.classList.remove('drag-over');
                }

                wrapper.addEventListener('drop', handleDrop, false);

                function handleDrop(e) {
                    const dt = e.dataTransfer;
                    const files = dt.files;
                    
                    if (files.length) {
                        input.files = files;
                        const event = new Event('change');
                        input.dispatchEvent(event);
                    }
                }
            }

            // Initial form validation
            validateForm();
        });
    </script>
</body>
</html>
