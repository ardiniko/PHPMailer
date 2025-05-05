<?php
// PHPMailer UI - Setup
// This file handles the initial configuration setup
session_start();

// Check if the config directory exists, if not create it
if (!is_dir('config')) {
    mkdir('config', 0755, true);
}

// Check if installation is already complete
if (file_exists('config/config.php') && !isset($_GET['force'])) {
    header('Location: index.php');
    exit;
}

// Define default config values
$defaultConfig = [
    'smtp_host' => 'localhost',
    'smtp_port' => '25',
    'smtp_secure' => '',
    'smtp_username' => '',
    'smtp_password' => '',
    'from_email' => '',
    'from_name' => ''
];

// Initialize config variables
$config = $defaultConfig;
$errors = [];
$success = false;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    $config['smtp_host'] = filter_input(INPUT_POST, 'smtp_host', FILTER_SANITIZE_STRING);
    $config['smtp_port'] = filter_input(INPUT_POST, 'smtp_port', FILTER_SANITIZE_NUMBER_INT);
    $config['smtp_secure'] = filter_input(INPUT_POST, 'smtp_secure', FILTER_SANITIZE_STRING);
    $config['smtp_username'] = filter_input(INPUT_POST, 'smtp_username', FILTER_SANITIZE_STRING);
    $config['smtp_password'] = filter_input(INPUT_POST, 'smtp_password', FILTER_UNSAFE_RAW);
    $config['from_email'] = filter_input(INPUT_POST, 'from_email', FILTER_SANITIZE_EMAIL);
    $config['from_name'] = filter_input(INPUT_POST, 'from_name', FILTER_SANITIZE_STRING);
    
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
        $configContent .= "// Generated on " . date('Y-m-d H:i:s') . "\n\n";
        $configContent .= "\$CONFIG = [\n";
        
        foreach ($config as $key => $value) {
            $configContent .= "    '$key' => '" . addslashes($value) . "',\n";
        }
        
        $configContent .= "];\n";
        
        // Save the configuration file
        if (file_put_contents('config/config.php', $configContent)) {
            $success = true;
        } else {
            $errors[] = 'Failed to write configuration file. Check permissions.';
        }
    }
}

// Page Title
$pageTitle = 'PHPMailer UI - Setup';
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
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3><i class="bi bi-envelope"></i> PHPMailer UI - Setup</h3>
                <p class="mb-0">Configure your PHPMailer settings</p>
            </div>
            <div class="card-body">
                <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> Configuration saved successfully! 
                    <a href="index.php" class="btn btn-sm btn-primary">Go to Dashboard</a>
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
                               value="<?php echo htmlspecialchars($config['smtp_host']); ?>" required>
                        <div class="form-text">The hostname of your SMTP server (e.g., smtp.gmail.com)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="smtp_port" class="form-label required">SMTP Port</label>
                        <input type="number" class="form-control" id="smtp_port" name="smtp_port" 
                               value="<?php echo htmlspecialchars($config['smtp_port']); ?>" required>
                        <div class="form-text">Common ports: 25 (no encryption), 465 (SSL), 587 (TLS)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="smtp_secure" class="form-label">SMTP Security</label>
                        <select class="form-select" id="smtp_secure" name="smtp_secure">
                            <option value="" <?php if ($config['smtp_secure'] === '') echo 'selected'; ?>>None</option>
                            <option value="ssl" <?php if ($config['smtp_secure'] === 'ssl') echo 'selected'; ?>>SSL</option>
                            <option value="tls" <?php if ($config['smtp_secure'] === 'tls') echo 'selected'; ?>>TLS</option>
                        </select>
                        <div class="form-text">For secure connections, choose SSL or TLS</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="smtp_username" class="form-label">SMTP Username</label>
                        <input type="text" class="form-control" id="smtp_username" name="smtp_username" 
                               value="<?php echo htmlspecialchars($config['smtp_username']); ?>">
                        <div class="form-text">Leave empty if your SMTP server does not require authentication</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="smtp_password" class="form-label">SMTP Password</label>
                        <input type="password" class="form-control" id="smtp_password" name="smtp_password" 
                               value="<?php echo htmlspecialchars($config['smtp_password']); ?>">
                        <div class="form-text">Leave empty if your SMTP server does not require authentication</div>
                    </div>
                    
                    <h5 class="mb-3 mt-4">Default Sender Settings</h5>
                    
                    <div class="mb-3">
                        <label for="from_email" class="form-label required">From Email</label>
                        <input type="email" class="form-control" id="from_email" name="from_email" 
                               value="<?php echo htmlspecialchars($config['from_email']); ?>" required>
                        <div class="form-text">Default sender email address</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="from_name" class="form-label">From Name</label>
                        <input type="text" class="form-control" id="from_name" name="from_name" 
                               value="<?php echo htmlspecialchars($config['from_name']); ?>">
                        <div class="form-text">Default sender name</div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save Configuration
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
        
        <div class="card shadow">
            <div class="card-header">
                <h5>Common SMTP Server Settings</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Provider</th>
                                <th>SMTP Host</th>
                                <th>Port</th>
                                <th>Security</th>
                                <th>Auth</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Gmail</td>
                                <td>smtp.gmail.com</td>
                                <td>587</td>
                                <td>TLS</td>
                                <td>Yes</td>
                            </tr>
                            <tr>
                                <td>Yahoo</td>
                                <td>smtp.mail.yahoo.com</td>
                                <td>465</td>
                                <td>SSL</td>
                                <td>Yes</td>
                            </tr>
                            <tr>
                                <td>Outlook/Hotmail</td>
                                <td>smtp-mail.outlook.com</td>
                                <td>587</td>
                                <td>TLS</td>
                                <td>Yes</td>
                            </tr>
                            <tr>
                                <td>Office 365</td>
                                <td>smtp.office365.com</td>
                                <td>587</td>
                                <td>TLS</td>
                                <td>Yes</td>
                            </tr>
                            <tr>
                                <td>Local SMTP</td>
                                <td>localhost</td>
                                <td>25</td>
                                <td>None</td>
                                <td>No</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-info small mb-0">
                    <i class="bi bi-lightbulb"></i> <strong>Note:</strong> 
                    For Gmail and other providers, you may need to create an app password or enable less secure apps.
                </div>
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