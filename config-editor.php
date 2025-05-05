<?php
// PHPMailer UI - Configuration Editor
// This file allows editing of the configuration settings
session_start();

// Check if config file exists, if not, redirect to setup
if (!file_exists('config/config.php')) {
    header('Location: setup.php');
    exit;
}

// Include the configuration file
require_once 'config/config.php';

// Initialize variables
$errors = [];
$success = false;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    $config = [
        'smtp_host' => filter_input(INPUT_POST, 'smtp_host', FILTER_SANITIZE_STRING),
        'smtp_port' => filter_input(INPUT_POST, 'smtp_port', FILTER_SANITIZE_NUMBER_INT),
        'smtp_secure' => filter_input(INPUT_POST, 'smtp_secure', FILTER_SANITIZE_STRING),
        'smtp_username' => filter_input(INPUT_POST, 'smtp_username', FILTER_SANITIZE_STRING),
        'smtp_password' => filter_input(INPUT_POST, 'smtp_password', FILTER_UNSAFE_RAW),
        'from_email' => filter_input(INPUT_POST, 'from_email', FILTER_SANITIZE_EMAIL),
        'from_name' => filter_input(INPUT_POST, 'from_name', FILTER_SANITIZE_STRING)
    ];
    
    // If password field is empty, use the existing password (prevents clearing on edit)
    if (empty($config['smtp_password'])) {
        $config['smtp_password'] = $CONFIG['smtp_password'];
    }
    
    // Validation
    if (empty($config['smtp_host'])) {
        $errors[] = 'SMTP Host is required';
    }
    
    if (empty($config['smtp_port']) || !is_numeric($config['smtp_port'])) {
        $errors[] = 'SMTP Port must be a valid number';
    }
    
    if (empty($config['from_email']) || !filter_var($config['from_email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'From Email must be a valid email address';
    }
    
    // If no errors, save configuration
    if (empty($errors)) {
        $configContent = "<?php\n";
        $configContent .= "// PHPMailer UI Configuration\n";
        $configContent .= "// Updated on " . date('Y-m-d H:i:s') . "\n\n";
        $configContent .= "\$CONFIG = [\n";
        
        foreach ($config as $key => $value) {
            $configContent .= "    '$key' => '" . addslashes($value) . "',\n";
        }
        
        $configContent .= "];\n";
        
        // Save the configuration file
        if (file_put_contents('config/config.php', $configContent)) {
            $success = true;
            // Reload the configuration
            require_once 'config/config.php';
        } else {
            $errors[] = 'Failed to write configuration file. Check permissions.';
        }
    }
}

// Page Title
$pageTitle = 'PHPMailer UI - Configuration Editor';
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
        .required::after { content: " *"; color: red; }
        .btn-back { margin-right: 10px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-envelope"></i> 
                PHPMailer UI
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="config-editor.php">Configuration</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="docs.php">Documentation</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="card shadow">
            <div class="card-header">
                <h5><i class="bi bi-gear"></i> Edit Configuration</h5>
            </div>
            <div class="card-body">
                <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> Configuration updated successfully!
                </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <strong><i class="bi bi-exclamation-triangle"></i> Error:</strong>
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <form method="post" action="">
                    <h5 class="mb-3">SMTP Server Settings</h5>
                    
                    <div class="mb-3">
                        <label for="smtp_host" class="form-label required">SMTP Host</label>
                        <input type="text" class="form-control" id="smtp_host" name="smtp_host" 
                               value="<?php echo htmlspecialchars($CONFIG['smtp_host']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="smtp_port" class="form-label required">SMTP Port</label>
                        <input type="number" class="form-control" id="smtp_port" name="smtp_port" 
                               value="<?php echo htmlspecialchars($CONFIG['smtp_port']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="smtp_secure" class="form-label">SMTP Security</label>
                        <select class="form-select" id="smtp_secure" name="smtp_secure">
                            <option value="" <?php if ($CONFIG['smtp_secure'] === '') echo 'selected'; ?>>None</option>
                            <option value="ssl" <?php if ($CONFIG['smtp_secure'] === 'ssl') echo 'selected'; ?>>SSL</option>
                            <option value="tls" <?php if ($CONFIG['smtp_secure'] === 'tls') echo 'selected'; ?>>TLS</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="smtp_username" class="form-label">SMTP Username</label>
                        <input type="text" class="form-control" id="smtp_username" name="smtp_username" 
                               value="<?php echo htmlspecialchars($CONFIG['smtp_username']); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="smtp_password" class="form-label">SMTP Password</label>
                        <input type="password" class="form-control" id="smtp_password" name="smtp_password" 
                               placeholder="<?php echo empty($CONFIG['smtp_password']) ? '' : '••••••••••••'; ?>">
                        <div class="form-text">Leave empty to keep the current password</div>
                    </div>
                    
                    <h5 class="mb-3 mt-4">Default Sender Settings</h5>
                    
                    <div class="mb-3">
                        <label for="from_email" class="form-label required">From Email</label>
                        <input type="email" class="form-control" id="from_email" name="from_email" 
                               value="<?php echo htmlspecialchars($CONFIG['from_email']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="from_name" class="form-label">From Name</label>
                        <input type="text" class="form-control" id="from_name" name="from_name" 
                               value="<?php echo htmlspecialchars($CONFIG['from_name']); ?>">
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="index.php" class="btn btn-secondary btn-back">
                            <i class="bi bi-arrow-left"></i> Back to Dashboard
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
            <div class="card-footer">
                <p class="text-muted small mb-0">
                    <i class="bi bi-info-circle"></i> 
                    Fields marked with <span class="text-danger">*</span> are required.
                </p>
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