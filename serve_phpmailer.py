#!/usr/bin/env python3
"""
PHPMailer UI Server - Python Web Server for PHPMailer UI
This script serves the PHPMailer UI application on port 80 by:
1. Starting a PHP built-in server in the background
2. Forwarding requests from port 80 to the PHP server
"""

import http.server
import socketserver
import urllib.request
import urllib.error
import subprocess
import os
import sys
import time
import socket
import threading
import argparse
import signal

# Default settings
PHP_PORT = 8000
PROXY_PORT = 80
PHP_BIN = "php"
DOCUMENT_ROOT = os.path.dirname(os.path.abspath(__file__))

# Colors for terminal output
class Colors:
    HEADER = '\033[95m'
    BLUE = '\033[94m'
    GREEN = '\033[92m'
    WARNING = '\033[93m'
    FAIL = '\033[91m'
    ENDC = '\033[0m'
    BOLD = '\033[1m'

# Global flag to track server status
running = True
php_process = None

def is_port_in_use(port):
    """Check if a port is in use."""
    with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
        return s.connect_ex(('localhost', port)) == 0

def start_php_server(port, document_root):
    """Start the PHP built-in server."""
    print(f"{Colors.BLUE}Starting PHP server on port {port}...{Colors.ENDC}")
    # Change to the document root directory
    os.chdir(document_root)
    # Start the PHP server
    php_cmd = [PHP_BIN, "-S", f"localhost:{port}", "-t", document_root]
    try:
        process = subprocess.Popen(
            php_cmd,
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE,
            universal_newlines=True
        )
        print(f"{Colors.GREEN}PHP server started successfully!{Colors.ENDC}")
        # Wait for the server to be ready
        time.sleep(2)
        return process
    except Exception as e:
        print(f"{Colors.FAIL}Failed to start PHP server: {e}{Colors.ENDC}")
        sys.exit(1)

def check_php_server_health(port):
    """Periodically check if the PHP server is still running."""
    global running, php_process
    
    while running:
        try:
            # Try to connect to the PHP server
            urllib.request.urlopen(f"http://localhost:{port}/", timeout=1)
            time.sleep(5)  # Check every 5 seconds
        except urllib.error.URLError:
            if running:  # Only report if we haven't initiated a shutdown
                print(f"{Colors.FAIL}PHP server seems to be down. Restarting...{Colors.ENDC}")
                # Kill the old process if it's still running
                if php_process and php_process.poll() is None:
                    php_process.terminate()
                    php_process.wait(timeout=5)
                # Start a new PHP server
                php_process = start_php_server(port, DOCUMENT_ROOT)
        except Exception as e:
            if running:
                print(f"{Colors.WARNING}Health check error: {e}{Colors.ENDC}")
        time.sleep(5)

class ProxyHandler(http.server.SimpleHTTPRequestHandler):
    """Handler that forwards requests to the PHP server."""
    
    def do_GET(self):
        self.proxy_request("GET")
    
    def do_POST(self):
        self.proxy_request("POST")
    
    def proxy_request(self, method):
        """Forward the request to the PHP server."""
        php_url = f"http://localhost:{PHP_PORT}{self.path}"
        
        try:
            # Create the request to the PHP server
            request = urllib.request.Request(php_url)
            
            # Copy headers from the original request
            for header in self.headers:
                request.add_header(header, self.headers[header])
            
            # Forward request body for POST requests
            data = None
            if method == "POST":
                content_length = int(self.headers.get('Content-Length', 0))
                data = self.rfile.read(content_length)
            
            # Send the request to the PHP server
            with urllib.request.urlopen(request, data=data) as response:
                # Copy status code and headers from the PHP response
                self.send_response(response.status)
                for header, value in response.getheaders():
                    if header.lower() not in ('server', 'date', 'transfer-encoding'):
                        self.send_header(header, value)
                self.end_headers()
                
                # Copy the response body
                self.wfile.write(response.read())
                
        except urllib.error.URLError as e:
            self.send_error(502, f"Bad Gateway: {e}")
        except Exception as e:
            self.send_error(500, f"Internal Server Error: {e}")
    
    # Suppress default server log messages
    def log_message(self, format, *args):
        if args[0].startswith('2'):  # Only log non-200 responses
            return
        super().log_message(format, *args)

def clean_up():
    """Clean up resources and processes before exiting."""
    global running, php_process
    
    running = False
    print(f"\n{Colors.BLUE}Shutting down servers...{Colors.ENDC}")
    
    if php_process:
        try:
            php_process.terminate()
            php_process.wait(timeout=5)
            print(f"{Colors.GREEN}PHP server stopped successfully.{Colors.ENDC}")
        except Exception as e:
            print(f"{Colors.WARNING}Error stopping PHP server: {e}{Colors.ENDC}")
            try:
                php_process.kill()
            except:
                pass

def signal_handler(sig, frame):
    """Handle signals like Ctrl+C."""
    clean_up()
    sys.exit(0)

def main():
    global PHP_PORT, PROXY_PORT, PHP_BIN, DOCUMENT_ROOT, php_process

    # Parse command line arguments
    parser = argparse.ArgumentParser(description='Serve PHPMailer UI application using Python and PHP.')
    parser.add_argument('--php-port', type=int, default=PHP_PORT, help=f'Port for PHP server (default: {PHP_PORT})')
    parser.add_argument('--proxy-port', type=int, default=PROXY_PORT, help=f'Port for Python proxy server (default: {PROXY_PORT})')
    parser.add_argument('--php-bin', default=PHP_BIN, help=f'PHP binary path (default: "{PHP_BIN}")')
    parser.add_argument('--document-root', default=DOCUMENT_ROOT, help=f'Document root directory (default: current directory)')
    
    args = parser.parse_args()
    
    PHP_PORT = args.php_port
    PROXY_PORT = args.proxy_port
    PHP_BIN = args.php_bin
    DOCUMENT_ROOT = args.document_root
    
    print(f"{Colors.HEADER}{Colors.BOLD}PHPMailer UI Server{Colors.ENDC}")
    print(f"{Colors.BLUE}Document Root: {DOCUMENT_ROOT}{Colors.ENDC}")
    
    # Check if PHP executable exists
    try:
        subprocess.run([PHP_BIN, "--version"], check=True, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
    except (subprocess.CalledProcessError, FileNotFoundError):
        print(f"{Colors.FAIL}Error: PHP executable not found at '{PHP_BIN}'.{Colors.ENDC}")
        print(f"{Colors.FAIL}Please ensure PHP is installed and in your PATH, or specify its location with --php-bin.{Colors.ENDC}")
        sys.exit(1)
    
    # Check if ports are available
    if is_port_in_use(PHP_PORT):
        print(f"{Colors.WARNING}Warning: Port {PHP_PORT} is already in use. Another server might be running.{Colors.ENDC}")
        response = input(f"Do you want to try stopping any service on port {PHP_PORT}? (y/n): ")
        if response.lower() == 'y':
            if sys.platform == 'win32':
                subprocess.run(f"FOR /F \"tokens=5\" %P IN ('netstat -ano ^| findstr :{PHP_PORT}') DO taskkill /F /PID %P", shell=True)
            else:
                subprocess.run(f"lsof -ti tcp:{PHP_PORT} | xargs -r kill -9", shell=True)
            time.sleep(1)
            if is_port_in_use(PHP_PORT):
                print(f"{Colors.FAIL}Failed to free port {PHP_PORT}. Please choose a different port.{Colors.ENDC}")
                sys.exit(1)
        else:
            print(f"{Colors.FAIL}Please choose a different port with --php-port or stop the service using port {PHP_PORT}.{Colors.ENDC}")
            sys.exit(1)
    
    if is_port_in_use(PROXY_PORT):
        print(f"{Colors.WARNING}Warning: Port {PROXY_PORT} is already in use. Another server might be running.{Colors.ENDC}")
        response = input(f"Do you want to try stopping any service on port {PROXY_PORT}? (y/n): ")
        if response.lower() == 'y':
            if sys.platform == 'win32':
                subprocess.run(f"FOR /F \"tokens=5\" %P IN ('netstat -ano ^| findstr :{PROXY_PORT}') DO taskkill /F /PID %P", shell=True)
            else:
                subprocess.run(f"lsof -ti tcp:{PROXY_PORT} | xargs -r kill -9", shell=True)
            time.sleep(1)
            if is_port_in_use(PROXY_PORT):
                print(f"{Colors.FAIL}Failed to free port {PROXY_PORT}. Please choose a different port.{Colors.ENDC}")
                sys.exit(1)
        else:
            print(f"{Colors.FAIL}Please choose a different port with --proxy-port or stop the service using port {PROXY_PORT}.{Colors.ENDC}")
            sys.exit(1)
    
    # Handle termination signals
    signal.signal(signal.SIGINT, signal_handler)
    signal.signal(signal.SIGTERM, signal_handler)
    
    # Start the PHP server in the background
    php_process = start_php_server(PHP_PORT, DOCUMENT_ROOT)
    
    # Start a thread to monitor the PHP server
    health_thread = threading.Thread(target=check_php_server_health, args=(PHP_PORT,), daemon=True)
    health_thread.start()
    
    # Set up a privilege warning for low ports
    if PROXY_PORT < 1024:
        if os.geteuid() != 0:  # Not root
            print(f"{Colors.WARNING}Warning: Port {PROXY_PORT} is a privileged port (below 1024).{Colors.ENDC}")
            print(f"{Colors.WARNING}You may need to run this script with sudo/administrator privileges.{Colors.ENDC}")
    
    # Start the proxy server
    try:
        print(f"{Colors.BLUE}Starting proxy server on port {PROXY_PORT}...{Colors.ENDC}")
        with socketserver.TCPServer(("", PROXY_PORT), ProxyHandler) as httpd:
            print(f"{Colors.GREEN}Server started at http://localhost:{PROXY_PORT}/{Colors.ENDC}")
            print(f"{Colors.BOLD}Press Ctrl+C to stop the servers.{Colors.ENDC}")
            httpd.serve_forever()
    except PermissionError:
        print(f"{Colors.FAIL}Error: Permission denied for port {PROXY_PORT}.{Colors.ENDC}")
        print(f"{Colors.FAIL}Try running with sudo/administrator privileges or use a port number above 1024.{Colors.ENDC}")
        clean_up()
        sys.exit(1)
    except OSError as e:
        if e.errno == 98:  # Port already in use
            print(f"{Colors.FAIL}Error: Port {PROXY_PORT} is already in use.{Colors.ENDC}")
        else:
            print(f"{Colors.FAIL}Error: {e}{Colors.ENDC}")
        clean_up()
        sys.exit(1)
    except KeyboardInterrupt:
        pass
    finally:
        clean_up()

if __name__ == "__main__":
    main() 