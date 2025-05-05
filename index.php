<?php
// PHPMailer UI - A friendly web interface for PHPMailer
// This file serves as the landing page for the PHPMailer UI
session_start();

// Check if config file exists, if not, redirect to setup
if (!file_exists('config/config.php')) {
    header('Location: setup.php');
    exit;
}

// Include the configuration file
require_once 'config/config.php';

// Include the autoloader for PHPMailer
// require_once 'vendor/autoload.php';
require_once 'src/Exception.php';
require_once 'src/PHPMailer.php';
require_once 'src/SMTP.php';

// Function to test SMTP connection
function testSMTPConnection($config) {
    $smtp = new \PHPMailer\PHPMailer\SMTP();
    $result = [];
    
    // Enable debug output
    $smtp->do_debug = \PHPMailer\PHPMailer\SMTP::DEBUG_SERVER;
    
    // Connect to the server
    ob_start();
    $connected = $smtp->connect($config['smtp_host'], $config['smtp_port'], 30);
    $result['connection_log'] = ob_get_clean();
    
    if (!$connected) {
        $result['success'] = false;
        $result['message'] = 'Connection failed';
        return $result;
    }
    
    // Say hello
    ob_start();
    $hello = $smtp->hello(gethostname());
    $result['hello_log'] = ob_get_clean();
    
    if (!$hello) {
        $result['success'] = false;
        $result['message'] = 'HELO failed: ' . $smtp->getError()['error'];
        $smtp->quit();
        return $result;
    }
    
    // Check if we need to use TLS
    if ($config['smtp_secure'] == 'tls') {
        ob_start();
        $tls = $smtp->startTLS();
        $result['tls_log'] = ob_get_clean();
        
        if (!$tls) {
            $result['success'] = false;
            $result['message'] = 'StartTLS failed: ' . $smtp->getError()['error'];
            $smtp->quit();
            return $result;
        }
        
        // Need to say hello again after TLS
        $smtp->hello(gethostname());
    }
    
    // Authenticate if needed
    if (!empty($config['smtp_username']) && !empty($config['smtp_password'])) {
        ob_start();
        $auth = $smtp->authenticate($config['smtp_username'], $config['smtp_password']);
        $result['auth_log'] = ob_get_clean();
        
        if (!$auth) {
            $result['success'] = false;
            $result['message'] = 'Authentication failed: ' . $smtp->getError()['error'];
            $smtp->quit();
            return $result;
        }
    }
    
    // All good
    $smtp->quit();
    $result['success'] = true;
    $result['message'] = 'SMTP connection test successful!';
    
    return $result;
}

// Handle AJAX requests
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'test_connection') {
        echo json_encode(testSMTPConnection($CONFIG));
        exit;
    }
    
    if ($_POST['action'] === 'send_test_email') {
        $to = filter_input(INPUT_POST, 'to', FILTER_SANITIZE_EMAIL);
        $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
        $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
        
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = $CONFIG['smtp_host'];
            $mail->SMTPAuth = !empty($CONFIG['smtp_username']) && !empty($CONFIG['smtp_password']);
            $mail->Username = $CONFIG['smtp_username'];
            $mail->Password = $CONFIG['smtp_password'];
            $mail->SMTPSecure = $CONFIG['smtp_secure'];
            $mail->Port = $CONFIG['smtp_port'];
            
            // Recipients
            $mail->setFrom($CONFIG['from_email'], $CONFIG['from_name']);
            $mail->addAddress($to);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;
            $mail->AltBody = strip_tags($message);
            
            $mail->send();
            echo json_encode(['success' => true, 'message' => 'Message has been sent']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
        }
        
        exit;
    }
}

// Page Title
$pageTitle = 'PHPMailer UI';
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
        .container { max-width: 1000px; }
        .card { margin-bottom: 20px; }
        pre { background-color: #f8f9fa; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-envelope"></i> 
                PHPMailer UI
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="config-editor.php">Configuration</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="docs.php">Documentation</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-info-circle"></i> Current Configuration</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <tbody>
                                    <tr>
                                        <th>SMTP Host:</th>
                                        <td><?php echo htmlspecialchars($CONFIG['smtp_host']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>SMTP Port:</th>
                                        <td><?php echo htmlspecialchars($CONFIG['smtp_port']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>SMTP Security:</th>
                                        <td><?php echo htmlspecialchars(strtoupper($CONFIG['smtp_secure'] ?: 'None')); ?></td>
                                    </tr>
                                    <tr>
                                        <th>SMTP Auth:</th>
                                        <td><?php echo !empty($CONFIG['smtp_username']) ? 'Yes' : 'No'; ?></td>
                                    </tr>
                                    <tr>
                                        <th>From Email:</th>
                                        <td><?php echo htmlspecialchars($CONFIG['from_email']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>From Name:</th>
                                        <td><?php echo htmlspecialchars($CONFIG['from_name']); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <button id="testConnection" class="btn btn-primary">
                            <i class="bi bi-hdd-network"></i> Test SMTP Connection
                        </button>
                        <a href="config-editor.php" class="btn btn-secondary">
                            <i class="bi bi-pencil"></i> Edit Configuration
                        </a>
                    </div>
                </div>
                
                <div id="connectionResult" class="card d-none">
                    <div class="card-header">
                        <h5><i class="bi bi-terminal"></i> Connection Test Results</h5>
                    </div>
                    <div class="card-body">
                        <div id="connectionMessage" class="alert"></div>
                        <div id="connectionLog"></div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-send"></i> Send Test Email</h5>
                    </div>
                    <div class="card-body">
                        <form id="testEmailForm">
                            <div class="mb-3">
                                <label for="to" class="form-label">To:</label>
                                <input type="email" class="form-control" id="to" name="to" required>
                            </div>
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject:</label>
                                <input type="text" class="form-control" id="subject" name="subject" 
                                       value="Test email from PHPMailer UI" required>
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">Message:</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required>This is a test email sent from the PHPMailer UI.</textarea>
                            </div>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-envelope"></i> Send Test Email
                            </button>
                        </form>
                    </div>
                </div>
                
                <div id="emailResult" class="card d-none">
                    <div class="card-header">
                        <h5><i class="bi bi-envelope-check"></i> Email Send Results</h5>
                    </div>
                    <div class="card-body">
                        <div id="emailMessage" class="alert"></div>
                    </div>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Test SMTP Connection
            document.getElementById('testConnection').addEventListener('click', function() {
                const resultCard = document.getElementById('connectionResult');
                const messageDiv = document.getElementById('connectionMessage');
                const logDiv = document.getElementById('connectionLog');
                
                resultCard.classList.remove('d-none');
                messageDiv.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"></div> Testing connection...';
                messageDiv.className = 'alert alert-info';
                logDiv.innerHTML = '';
                
                fetch('index.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=test_connection'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        messageDiv.className = 'alert alert-success';
                    } else {
                        messageDiv.className = 'alert alert-danger';
                    }
                    messageDiv.textContent = data.message;
                    
                    // Display logs
                    let logHtml = '';
                    for (const key in data) {
                        if (key.endsWith('_log') && data[key]) {
                            logHtml += `<h6>${key.replace('_log', '').toUpperCase()} Log:</h6>`;
                            logHtml += `<pre>${data[key]}</pre>`;
                        }
                    }
                    logDiv.innerHTML = logHtml;
                })
                .catch(error => {
                    messageDiv.className = 'alert alert-danger';
                    messageDiv.textContent = 'Error: ' + error.message;
                });
            });
            
            // Send Test Email
            document.getElementById('testEmailForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const to = document.getElementById('to').value;
                const subject = document.getElementById('subject').value;
                const message = document.getElementById('message').value;
                const resultCard = document.getElementById('emailResult');
                const messageDiv = document.getElementById('emailMessage');
                
                resultCard.classList.remove('d-none');
                messageDiv.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"></div> Sending email...';
                messageDiv.className = 'alert alert-info';
                
                fetch('index.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=send_test_email&to=${encodeURIComponent(to)}&subject=${encodeURIComponent(subject)}&message=${encodeURIComponent(message)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        messageDiv.className = 'alert alert-success';
                    } else {
                        messageDiv.className = 'alert alert-danger';
                    }
                    messageDiv.textContent = data.message;
                })
                .catch(error => {
                    messageDiv.className = 'alert alert-danger';
                    messageDiv.textContent = 'Error: ' + error.message;
                });
            });
        });
    </script>
</body>
</html> 