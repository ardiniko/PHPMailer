#!/bin/bash
# PHPMailer UI - Automated Installation Script
# This script helps set up PHPMailer UI automatically

# ANSI color codes for better output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored messages
print_message() {
    local color=$1
    local message=$2
    echo -e "${color}${message}${NC}"
}

# Function to check if a command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Title
echo "========================================================="
print_message $BLUE "PHPMailer UI - Automated Installation Script"
echo "========================================================="
echo ""

# Check if running with sufficient permissions
if [[ $EUID -ne 0 ]]; then
    print_message $YELLOW "Warning: This script is not running with root privileges."
    print_message $YELLOW "Some operations might fail due to permission issues."
    echo ""
    read -p "Do you want to continue anyway? (y/n): " continue_anyway
    if [[ "$continue_anyway" != "y" ]]; then
        print_message $RED "Installation aborted."
        exit 1
    fi
fi

# Check for PHP
print_message $BLUE "Checking PHP installation..."
if ! command_exists php; then
    print_message $RED "Error: PHP is not installed or not in the PATH."
    print_message $RED "Please install PHP 5.5 or later and try again."
    exit 1
else
    php_version=$(php -r "echo PHP_VERSION;")
    if [[ $(php -r "echo version_compare(PHP_VERSION, '5.5.0', '>=') ? 'true' : 'false';") == "false" ]]; then
        print_message $RED "Error: PHP 5.5.0 or later is required."
        print_message $RED "Current version: $php_version"
        exit 1
    else
        print_message $GREEN "PHP $php_version is installed."
    fi
fi

# Check for Composer
print_message $BLUE "Checking Composer installation..."
if ! command_exists composer; then
    print_message $YELLOW "Composer is not installed or not in the PATH."
    print_message $YELLOW "Would you like to install Composer? (y/n): "
    read install_composer
    
    if [[ "$install_composer" == "y" ]]; then
        print_message $BLUE "Installing Composer..."
        php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
        php composer-setup.php --quiet
        php -r "unlink('composer-setup.php');"
        
        if [[ $EUID -eq 0 ]]; then
            mv composer.phar /usr/local/bin/composer
            chmod +x /usr/local/bin/composer
        else
            print_message $YELLOW "Running without root privileges. Composer will be installed locally."
            print_message $YELLOW "You'll need to use 'php composer.phar' instead of 'composer'."
            COMPOSER_CMD="php composer.phar"
        fi
        
        if [[ ! -f composer.phar && ! $(command -v composer) ]]; then
            print_message $RED "Error: Failed to install Composer."
            exit 1
        else
            print_message $GREEN "Composer installed successfully."
        fi
    else
        print_message $RED "Composer is required for installation. Aborting."
        exit 1
    fi
else
    print_message $GREEN "Composer is installed."
    COMPOSER_CMD="composer"
fi

# Set the composer command based on installation type
if [[ -z "$COMPOSER_CMD" ]]; then
    if command_exists composer; then
        COMPOSER_CMD="composer"
    else
        COMPOSER_CMD="php composer.phar"
    fi
fi

# Install PHPMailer
print_message $BLUE "Installing PHPMailer via Composer..."
$COMPOSER_CMD require phpmailer/phpmailer

if [[ $? -ne 0 ]]; then
    print_message $RED "Error: Failed to install PHPMailer."
    exit 1
else
    print_message $GREEN "PHPMailer installed successfully."
fi

# Create and set permissions for config directory
print_message $BLUE "Creating config directory..."
mkdir -p config
chmod 755 config

if [[ ! -d config ]]; then
    print_message $RED "Error: Failed to create config directory."
    exit 1
else
    print_message $GREEN "Config directory created successfully."
fi

# Create .htaccess file if it doesn't exist
if [[ ! -f config/.htaccess ]]; then
    print_message $BLUE "Creating .htaccess protection for config directory..."
    cat > config/.htaccess << 'EOL'
# Prevent direct access to the config directory
# This helps protect sensitive information like SMTP credentials

# Deny access to all files
<FilesMatch ".*">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Block viewing of this .htaccess file
<Files .htaccess>
    Order Allow,Deny
    Deny from all
</Files>

# Disable directory browsing
Options -Indexes

# Disable PHP execution in this directory
<FilesMatch "\.php$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Return a 403 Forbidden error
RedirectMatch 403 ^/config/?$
EOL
    print_message $GREEN ".htaccess file created successfully."
else
    print_message $YELLOW "Config directory .htaccess file already exists."
fi

# Check if all required PHP files exist
required_files=("index.php" "setup.php" "config-editor.php" "docs.php" "install.php")
missing_files=()

for file in "${required_files[@]}"; do
    if [[ ! -f "$file" ]]; then
        missing_files+=("$file")
    fi
done

if [[ ${#missing_files[@]} -gt 0 ]]; then
    print_message $RED "Error: The following required files are missing:"
    for file in "${missing_files[@]}"; do
        print_message $RED "  - $file"
    done
    print_message $YELLOW "Please make sure all required files are in the current directory."
else
    print_message $GREEN "All required UI files are present."
fi

# Setup complete
echo ""
echo "========================================================="
print_message $GREEN "PHPMailer UI installation completed!"
echo "========================================================="
echo ""
print_message $BLUE "Next steps:"
echo "1. Run the PHP built-in server to test the installation:"
echo "   php -S localhost:8000"
echo ""
echo "2. Open your browser and navigate to:"
echo "   http://localhost:8000/install.php"
echo ""
echo "3. Follow the on-screen instructions to complete the setup."
echo ""
print_message $YELLOW "For production use, move these files to your web server's document root."
echo ""

# Exit successfully
exit 0 