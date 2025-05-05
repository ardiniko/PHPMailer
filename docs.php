<?php
// PHPMailer UI - Documentation
// This file provides documentation and help for using PHPMailer
session_start();

// Check if config file exists, if not, redirect to setup
if (!file_exists('config/config.php')) {
    header('Location: setup.php');
    exit;
}

// Include the configuration file
require_once 'config/config.php';

// Page Title
$pageTitle = 'PHPMailer UI - Documentation';
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
        .doc-nav { position: sticky; top: 20px; }
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
                        <a class="nav-link" href="config-editor.php">Configuration</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="docs.php">Documentation</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <div class="doc-nav">
                    <div class="card">
                        <div class="card-header">
                            <h5>Navigation</h5>
                        </div>
                        <div class="card-body">
                            <nav class="nav flex-column">
                                <a class="nav-link" href="#intro">Introduction</a>
                                <a class="nav-link" href="#setup">Setup Instructions</a>
                                <a class="nav-link" href="#config">Configuration Options</a>
                                <a class="nav-link" href="#testing">Testing Email</a>
                                <a class="nav-link" href="#integration">Integration Guide</a>
                                <a class="nav-link" href="#troubleshooting">Troubleshooting</a>
                                <a class="nav-link" href="#smtp-providers">SMTP Providers</a>
                                <a class="nav-link" href="#api">API Reference</a>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="card" id="intro">
                    <div class="card-header">
                        <h5>Introduction to PHPMailer UI</h5>
                    </div>
                    <div class="card-body">
                        <p>PHPMailer UI is a user-friendly web interface for managing and testing your PHPMailer installation. It provides:</p>
                        <ul>
                            <li>Easy configuration of SMTP settings</li>
                            <li>Email testing tools</li>
                            <li>Connection diagnostics</li>
                            <li>Simple integration with your PHP applications</li>
                        </ul>
                        <p>
                            This interface helps you set up PHPMailer quickly and troubleshoot any issues with your email configuration.
                            PHPMailer is a feature-rich library for sending emails with PHP, supporting SMTP authentication, HTML content,
                            attachments, and more.
                        </p>
                    </div>
                </div>
                
                <div class="card" id="setup">
                    <div class="card-header">
                        <h5>Setup Instructions</h5>
                    </div>
                    <div class="card-body">
                        <h6>1. Installation</h6>
                        <p>To install PHPMailer UI:</p>
                        <ol>
                            <li>Make sure PHPMailer is installed via Composer</li>
                            <li>Place these UI files in your project directory</li>
                            <li>Visit the setup page to configure your email settings</li>
                        </ol>
                        
                        <h6>2. Requirements</h6>
                        <ul>
                            <li>PHP 5.5 or later (PHP 7+ recommended)</li>
                            <li>Composer (for installing PHPMailer)</li>
                            <li>PHPMailer library installed</li>
                            <li>Access to an SMTP server</li>
                        </ul>
                        
                        <h6>3. Installing PHPMailer via Composer</h6>
                        <p>If you haven't installed PHPMailer yet, run:</p>
                        <pre><code>composer require phpmailer/phpmailer</code></pre>
                    </div>
                </div>
                
                <div class="card" id="config">
                    <div class="card-header">
                        <h5>Configuration Options</h5>
                    </div>
                    <div class="card-body">
                        <p>The configuration page allows you to set the following options:</p>
                        
                        <h6>SMTP Server Settings</h6>
                        <ul>
                            <li><strong>SMTP Host:</strong> Your mail server's hostname (e.g., smtp.gmail.com)</li>
                            <li><strong>SMTP Port:</strong> The port to connect to (common: 25, 465, 587)</li>
                            <li><strong>SMTP Security:</strong> Type of encryption (None, SSL, TLS)</li>
                            <li><strong>SMTP Username:</strong> Authentication username for the SMTP server</li>
                            <li><strong>SMTP Password:</strong> Authentication password for the SMTP server</li>
                        </ul>
                        
                        <h6>Default Sender Settings</h6>
                        <ul>
                            <li><strong>From Email:</strong> Default sender email address</li>
                            <li><strong>From Name:</strong> Default sender name</li>
                        </ul>
                        
                        <div class="alert alert-info">
                            <strong>Note:</strong> The configuration is stored in the <code>config/config.php</code> file.
                        </div>
                    </div>
                </div>
                
                <div class="card" id="testing">
                    <div class="card-header">
                        <h5>Testing Email</h5>
                    </div>
                    <div class="card-body">
                        <p>The home page provides two testing tools:</p>
                        
                        <h6>SMTP Connection Test</h6>
                        <p>
                            This tests your SMTP connection without sending an email.
                            It verifies that your server can connect to the SMTP server and authenticate correctly.
                        </p>
                        <p>The test performs these steps:</p>
                        <ol>
                            <li>Connect to the SMTP server</li>
                            <li>Say hello (HELO/EHLO)</li>
                            <li>Start TLS if required</li>
                            <li>Authenticate if credentials are provided</li>
                        </ol>
                        
                        <h6>Send Test Email</h6>
                        <p>
                            This sends an actual test email to verify that your configuration works end-to-end.
                            Enter the recipient address, subject, and message to send a test email.
                        </p>
                    </div>
                </div>
                
                <div class="card" id="integration">
                    <div class="card-header">
                        <h5>Integration Guide</h5>
                    </div>
                    <div class="card-body">
                        <p>To integrate PHPMailer with your application using the configuration from this UI:</p>
                        
                        <h6>Basic Integration Code</h6>
                        <pre><code>&lt;?php
// Include the PHPMailer autoloader
require 'vendor/autoload.php';

// Include your configuration
require 'config/config.php';

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Create a new PHPMailer instance
$mail = new PHPMailer(true);

try {
    // Configure using your settings
    $mail->isSMTP();
    $mail->Host = $CONFIG['smtp_host'];
    $mail->Port = $CONFIG['smtp_port'];
    
    // Set encryption type
    if ($CONFIG['smtp_secure'] === 'ssl') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    } elseif ($CONFIG['smtp_secure'] === 'tls') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    }
    
    // Authentication
    $mail->SMTPAuth = !empty($CONFIG['smtp_username']) && !empty($CONFIG['smtp_password']);
    if ($mail->SMTPAuth) {
        $mail->Username = $CONFIG['smtp_username'];
        $mail->Password = $CONFIG['smtp_password'];
    }
    
    // Set default sender
    $mail->setFrom($CONFIG['from_email'], $CONFIG['from_name']);
    
    // Add recipients, content, etc.
    $mail->addAddress('recipient@example.com');
    $mail->Subject = 'Email Subject';
    $mail->Body = 'Email body content';
    
    // Send the email
    $mail->send();
    echo 'Message sent successfully';
} catch (Exception $e) {
    echo "Message could not be sent. Error: {$mail->ErrorInfo}";
}
</code></pre>
                    </div>
                </div>
                
                <div class="card" id="troubleshooting">
                    <div class="card-header">
                        <h5>Troubleshooting</h5>
                    </div>
                    <div class="card-body">
                        <h6>Common Issues</h6>
                        
                        <div class="accordion" id="troubleshootingAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#issue1">
                                        Connection to SMTP server failed
                                    </button>
                                </h2>
                                <div id="issue1" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                                    <div class="accordion-body">
                                        <p>Possible causes and solutions:</p>
                                        <ul>
                                            <li>Incorrect SMTP host or port - verify the settings</li>
                                            <li>Firewall blocking outgoing connections - check firewall settings</li>
                                            <li>Server not allowing outgoing SMTP - check with your host</li>
                                            <li>Try a different port (25, 465, 587)</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#issue2">
                                        Authentication failed
                                    </button>
                                </h2>
                                <div id="issue2" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                                    <div class="accordion-body">
                                        <p>Possible causes and solutions:</p>
                                        <ul>
                                            <li>Incorrect username or password</li>
                                            <li>Account security settings not allowing SMTP access</li>
                                            <li>For Gmail and other providers, you may need to create an app password</li>
                                            <li>2FA enabled but not using app password</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#issue3">
                                        SSL/TLS encryption issues
                                    </button>
                                </h2>
                                <div id="issue3" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                                    <div class="accordion-body">
                                        <p>Possible causes and solutions:</p>
                                        <ul>
                                            <li>Incorrect encryption type selected</li>
                                            <li>Using wrong port for the encryption type</li>
                                            <li>SSL certificate issues - update PHP's CA certificate bundle</li>
                                            <li>PHP OpenSSL extension not installed or enabled</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#issue4">
                                        Emails sent but not received
                                    </button>
                                </h2>
                                <div id="issue4" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                                    <div class="accordion-body">
                                        <p>Possible causes and solutions:</p>
                                        <ul>
                                            <li>Emails caught by spam filters</li>
                                            <li>DNS records missing (SPF, DKIM, DMARC)</li>
                                            <li>Using a non-verified domain as sender</li>
                                            <li>Check recipient's spam folder</li>
                                            <li>Verify the recipient address is correct</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card" id="smtp-providers">
                    <div class="card-header">
                        <h5>SMTP Providers</h5>
                    </div>
                    <div class="card-body">
                        <p>Common SMTP server configurations for popular email providers:</p>
                        
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Provider</th>
                                        <th>SMTP Host</th>
                                        <th>Port</th>
                                        <th>Security</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Gmail</td>
                                        <td>smtp.gmail.com</td>
                                        <td>587</td>
                                        <td>TLS</td>
                                        <td>Requires app password if 2FA is enabled</td>
                                    </tr>
                                    <tr>
                                        <td>Gmail (Alternative)</td>
                                        <td>smtp.gmail.com</td>
                                        <td>465</td>
                                        <td>SSL</td>
                                        <td>Alternative configuration</td>
                                    </tr>
                                    <tr>
                                        <td>Outlook/Hotmail</td>
                                        <td>smtp-mail.outlook.com</td>
                                        <td>587</td>
                                        <td>TLS</td>
                                        <td>Use your full email address as username</td>
                                    </tr>
                                    <tr>
                                        <td>Yahoo Mail</td>
                                        <td>smtp.mail.yahoo.com</td>
                                        <td>465</td>
                                        <td>SSL</td>
                                        <td>Requires app password if 2FA is enabled</td>
                                    </tr>
                                    <tr>
                                        <td>Office 365</td>
                                        <td>smtp.office365.com</td>
                                        <td>587</td>
                                        <td>TLS</td>
                                        <td>Use your full email address as username</td>
                                    </tr>
                                    <tr>
                                        <td>Zoho Mail</td>
                                        <td>smtp.zoho.com</td>
                                        <td>587</td>
                                        <td>TLS</td>
                                        <td>Use your full email address as username</td>
                                    </tr>
                                    <tr>
                                        <td>SendGrid</td>
                                        <td>smtp.sendgrid.net</td>
                                        <td>587</td>
                                        <td>TLS</td>
                                        <td>Requires SendGrid API key as password</td>
                                    </tr>
                                    <tr>
                                        <td>Mailgun</td>
                                        <td>smtp.mailgun.org</td>
                                        <td>587</td>
                                        <td>TLS</td>
                                        <td>Use SMTP credentials from Mailgun dashboard</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="card" id="api">
                    <div class="card-header">
                        <h5>API Reference</h5>
                    </div>
                    <div class="card-body">
                        <p>
                            PHPMailer provides a comprehensive API for sending emails with PHP.
                            Here are some of the most commonly used methods:
                        </p>
                        
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Method</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>isSMTP()</code></td>
                                        <td>Sets mailer to use SMTP</td>
                                    </tr>
                                    <tr>
                                        <td><code>setFrom($email, $name = '')</code></td>
                                        <td>Sets the sender email and name</td>
                                    </tr>
                                    <tr>
                                        <td><code>addAddress($email, $name = '')</code></td>
                                        <td>Adds a recipient email and name</td>
                                    </tr>
                                    <tr>
                                        <td><code>addCC($email, $name = '')</code></td>
                                        <td>Adds a CC recipient</td>
                                    </tr>
                                    <tr>
                                        <td><code>addBCC($email, $name = '')</code></td>
                                        <td>Adds a BCC recipient</td>
                                    </tr>
                                    <tr>
                                        <td><code>addReplyTo($email, $name = '')</code></td>
                                        <td>Adds a reply-to address</td>
                                    </tr>
                                    <tr>
                                        <td><code>addAttachment($path, $name = '')</code></td>
                                        <td>Adds an attachment from a path</td>
                                    </tr>
                                    <tr>
                                        <td><code>isHTML($isHtml = true)</code></td>
                                        <td>Sets email to be sent as HTML</td>
                                    </tr>
                                    <tr>
                                        <td><code>Subject</code></td>
                                        <td>Sets the email subject</td>
                                    </tr>
                                    <tr>
                                        <td><code>Body</code></td>
                                        <td>Sets the HTML message body</td>
                                    </tr>
                                    <tr>
                                        <td><code>AltBody</code></td>
                                        <td>Sets the plain text message body</td>
                                    </tr>
                                    <tr>
                                        <td><code>send()</code></td>
                                        <td>Sends the email</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <p>
                            For the complete API documentation, refer to the 
                            <a href="https://github.com/PHPMailer/PHPMailer" target="_blank">official PHPMailer GitHub repository</a>
                            or the <a href="https://phpmailer.github.io/PHPMailer/" target="_blank">API documentation</a>.
                        </p>
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
</body>
</html> 