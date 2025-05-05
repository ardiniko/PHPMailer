<?php
// PHPMailer UI - Automated Installation Script
// This script checks requirements and sets up the basic environment

// Function to check if a directory is writable
function isDirectoryWritable($dir) {
    if (!file_exists($dir)) {
        if (!@mkdir($dir, 0755, true)) {
            return false;
        }
    }
    return is_writable($dir);
}

// Function to check PHP version
function checkPhpVersion() {
    return version_compare(PHP_VERSION, '5.5.0', '>=');
}

// Function to check if a PHP extension is loaded
function checkExtension($name) {
    return extension_loaded($name);
}

// Function to check if a class exists (for PHPMailer)
function checkClass($class) {
    return class_exists($class);
}

// Function to check if Composer is installed
function composerInstalled() {
    return file_exists('vendor/autoload.php');
}

// Initialize variables
$errors = [];
$warnings = [];
$success = [];
$canProceed = true;
$installReady = false;

// Check PHP version
if (!checkPhpVersion()) {
    $errors[] = "PHP version 5.5.0 or higher is required. Current version: " . PHP_VERSION;
    $canProceed = false;
} else {
    $success[] = "PHP version check passed. Current version: " . PHP_VERSION;
}

// Check required extensions
$requiredExtensions = ['ctype', 'filter', 'hash', 'openssl', 'session'];
foreach ($requiredExtensions as $ext) {
    if (!checkExtension($ext)) {
        if ($ext === 'openssl') {
            $warnings[] = "PHP extension '$ext' is not installed. Secure connections (SSL/TLS) may not work.";
        } else {
            $errors[] = "Required PHP extension '$ext' is not installed.";
            $canProceed = false;
        }
    } else {
        $success[] = "PHP extension '$ext' is installed.";
    }
}

// Check optional extensions
$optionalExtensions = ['mbstring', 'json'];
foreach ($optionalExtensions as $ext) {
    if (!checkExtension($ext)) {
        $warnings[] = "Optional PHP extension '$ext' is not installed. Some features may not work correctly.";
    } else {
        $success[] = "Optional PHP extension '$ext' is installed.";
    }
}

// Check for Composer and PHPMailer
if (!composerInstalled()) {
    $errors[] = "Composer autoloader not found. Please run 'composer require phpmailer/phpmailer' in the project directory.";
    $canProceed = false;
} else {
    $success[] = "Composer autoloader found.";
    
    // Try to load PHPMailer classes
    require_once 'vendor/autoload.php';
    
    if (!checkClass('\PHPMailer\PHPMailer\PHPMailer')) {
        $errors[] = "PHPMailer class not found. Please run 'composer require phpmailer/phpmailer' in the project directory.";
        $canProceed = false;
    } else {
        $success[] = "PHPMailer class found.";
    }
}

// Check if config directory is writable
if (!isDirectoryWritable('config')) {
    $errors[] = "The 'config' directory is not writable. Please check permissions.";
    $canProceed = false;
} else {
    $success[] = "The 'config' directory is writable.";
}

// Check if all UI files exist
$requiredFiles = ['index.php', 'setup.php', 'config-editor.php', 'docs.php'];
$missingFiles = [];

foreach ($requiredFiles as $file) {
    if (!file_exists($file)) {
        $missingFiles[] = $file;
    }
}

if (!empty($missingFiles)) {
    $errors[] = "Some required files are missing: " . implode(', ', $missingFiles);
    $canProceed = false;
} else {
    $success[] = "All required UI files are present.";
}

// Determine if we can proceed with installation
$installReady = $canProceed;

// Page Title
$pageTitle = 'PHPMailer UI - Installation';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .container { max-width: 800px; }
        .card { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3><i class="bi bi-envelope"></i> PHPMailer UI - Installation</h3>
                <p class="mb-0">System Requirements Check</p>
            </div>
            <div class="card-body">
                <?php if ($installReady): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> All requirements are met! You can proceed with installation.
                </div>
                <?php else: ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> Some requirements are not met. Please fix the issues below before proceeding.
                </div>
                <?php endif; ?>
                
                <h5 class="mb-3">Requirement Check Results</h5>
                
                <?php if (!empty($errors)): ?>
                <div class="mb-4">
                    <h6 class="text-danger"><i class="bi bi-x-circle"></i> Errors (Must be fixed)</h6>
                    <ul class="list-group">
                        <?php foreach ($errors as $error): ?>
                        <li class="list-group-item list-group-item-danger"><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($warnings)): ?>
                <div class="mb-4">
                    <h6 class="text-warning"><i class="bi bi-exclamation-triangle"></i> Warnings (Recommended to fix)</h6>
                    <ul class="list-group">
                        <?php foreach ($warnings as $warning): ?>
                        <li class="list-group-item list-group-item-warning"><?php echo htmlspecialchars($warning); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                <div class="mb-4">
                    <h6 class="text-success"><i class="bi bi-check-circle"></i> Passed Checks</h6>
                    <ul class="list-group">
                        <?php foreach ($success as $item): ?>
                        <li class="list-group-item list-group-item-success"><?php echo htmlspecialchars($item); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <div class="d-grid gap-2">
                    <?php if ($installReady): ?>
                    <a href="setup.php" class="btn btn-primary">
                        <i class="bi bi-arrow-right"></i> Proceed to Setup
                    </a>
                    <?php else: ?>
                    <button type="button" class="btn btn-primary" onclick="window.location.reload();">
                        <i class="bi bi-arrow-clockwise"></i> Check Again
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="card shadow">
            <div class="card-header">
                <h5>Installation Instructions</h5>
            </div>
            <div class="card-body">
                <h6>1. Prerequisites</h6>
                <ul>
                    <li>PHP 5.5 or later</li>
                    <li>Required PHP extensions: ctype, filter, hash</li>
                    <li>Recommended PHP extensions: openssl, mbstring</li>
                    <li>Composer installed</li>
                </ul>
                
                <h6>2. Installation Steps</h6>
                <ol>
                    <li>Install Composer (if not already installed)</li>
                    <li>Install PHPMailer: <code>composer require phpmailer/phpmailer</code></li>
                    <li>Create a writable <code>config</code> directory: <code>mkdir config && chmod 755 config</code></li>
                    <li>Run this installation script to verify requirements</li>
                    <li>Follow the setup wizard to configure your email settings</li>
                </ol>
                
                <h6>3. Troubleshooting</h6>
                <ul>
                    <li>If you see permission errors, ensure the web server has write access to the <code>config</code> directory</li>
                    <li>For PHP extension errors, contact your server administrator or hosting provider</li>
                    <li>For Composer issues, refer to the <a href="https://getcomposer.org/doc/00-intro.md" target="_blank">Composer documentation</a></li>
                </ul>
            </div>
        </div>
    </div>
    
    <footer class="bg-light py-3 mt-4">
        <div class="container text-center">
            <p>PHPMailer UI - A friendly web interface for <a href="https://github.com/PHPMailer/PHPMailer" target="_blank">PHPMailer</a></p>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 