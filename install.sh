#!/bin/bash

# ============================================================================
# FastClient CRM - Installation Script
# Target OS: Debian 12
# ============================================================================

set -e

# ----------------------------------------------------------------------------
# Color Definitions
# ----------------------------------------------------------------------------
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
BOLD='\033[1m'
NC='\033[0m'

# ----------------------------------------------------------------------------
# Configuration
# ----------------------------------------------------------------------------
INSTALL_PATH="/var/www/fastclient"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
TOTAL_STEPS=9

# ----------------------------------------------------------------------------
# Helper Functions
# ----------------------------------------------------------------------------
print_success() {
    echo -e "  ${GREEN}✓${NC} $1"
}

print_error() {
    echo -e "  ${RED}✗${NC} $1"
}

print_warning() {
    echo -e "  ${YELLOW}⚠${NC} $1"
}

print_info() {
    echo -e "  ${BLUE}→${NC} $1"
}

print_step() {
    local step=$1
    local title=$2
    echo ""
    echo -e "${BOLD}[Step ${step}/${TOTAL_STEPS}] ${title}${NC}"
}

ask_input() {
    local prompt=$1
    local default=$2
    local result

    if [ -n "$default" ]; then
        read -rp "  $prompt [$default]: " result
        echo "${result:-$default}"
    else
        read -rp "  $prompt: " result
        echo "$result"
    fi
}

ask_password() {
    local prompt=$1
    local result

    read -rsp "  $prompt: " result
    echo ""
    echo "$result"
}

confirm() {
    local prompt=$1
    local response

    read -rp "  $prompt [y/N]: " response
    case "$response" in
        [yY][eE][sS]|[yY])
            return 0
            ;;
        *)
            return 1
            ;;
    esac
}

check_command() {
    command -v "$1" >/dev/null 2>&1
}

# ----------------------------------------------------------------------------
# Banner
# ----------------------------------------------------------------------------
print_banner() {
    echo ""
    echo -e "${BLUE}╔══════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${BLUE}║${NC}${BOLD}           FastClient CRM - Installation Script               ${NC}${BLUE}║${NC}"
    echo -e "${BLUE}╚══════════════════════════════════════════════════════════════╝${NC}"
    echo ""
}

# ----------------------------------------------------------------------------
# Pre-flight Checks
# ----------------------------------------------------------------------------
preflight_checks() {
    echo -e "${BOLD}Running pre-flight checks...${NC}"

    # Check running as root
    if [ "$EUID" -ne 0 ]; then
        print_error "This script must be run as root or with sudo"
        exit 1
    fi
    print_success "Running as root"

    # Check Debian version
    if [ -f /etc/os-release ]; then
        . /etc/os-release
        if [ "$ID" != "debian" ]; then
            print_error "This script is designed for Debian. Detected: $ID"
            exit 1
        fi
        if [ "$VERSION_ID" != "12" ]; then
            print_warning "This script is designed for Debian 12. Detected: Debian $VERSION_ID"
            if ! confirm "Continue anyway?"; then
                exit 1
            fi
        fi
        print_success "Debian $VERSION_ID detected"
    else
        print_error "Cannot detect OS version"
        exit 1
    fi

    # Check script location has required files
    if [ ! -f "$SCRIPT_DIR/.env.example" ]; then
        print_error ".env.example not found in $SCRIPT_DIR"
        exit 1
    fi
    print_success "Project files found"

    if [ ! -f "$SCRIPT_DIR/nginx.conf.template" ]; then
        print_error "nginx.conf.template not found in $SCRIPT_DIR"
        exit 1
    fi
    print_success "Nginx template found"

    if [ ! -f "$SCRIPT_DIR/migrate.php" ]; then
        print_error "migrate.php not found in $SCRIPT_DIR"
        exit 1
    fi
    print_success "Migration script found"

    if [ ! -f "$SCRIPT_DIR/package.json" ]; then
        print_error "package.json not found in $SCRIPT_DIR"
        exit 1
    fi
    print_success "package.json found"
}

# ----------------------------------------------------------------------------
# Step 1: Install PHP 8.4
# ----------------------------------------------------------------------------
install_php() {
    print_step 1 "Installing PHP 8.4..."

    # Check if PHP 8.4 is already installed
    if check_command php && php -v | grep -q "PHP 8.4"; then
        print_success "PHP 8.4 is already installed"
    else
        print_info "Adding sury.org PHP repository..."
        apt-get update -qq
        apt-get install -y -qq apt-transport-https lsb-release ca-certificates curl gnupg >/dev/null 2>&1

        curl -fsSL https://packages.sury.org/php/apt.gpg | gpg --dearmor -o /usr/share/keyrings/php-archive-keyring.gpg
        echo "deb [signed-by=/usr/share/keyrings/php-archive-keyring.gpg] https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list
        print_success "Repository added"

        print_info "Installing PHP packages..."
        apt-get update -qq
        apt-get install -y -qq php8.4 php8.4-fpm php8.4-pgsql php8.4-mbstring php8.4-cli >/dev/null 2>&1
        print_success "PHP packages installed"
    fi

    # Verify installation
    local php_version
    php_version=$(php -v | head -n 1 | awk '{print $2}')
    print_success "PHP $php_version installed successfully"

    # Enable and start PHP-FPM
    systemctl enable php8.4-fpm >/dev/null 2>&1
    systemctl start php8.4-fpm
    print_success "PHP-FPM enabled and started"
}

# ----------------------------------------------------------------------------
# Step 2: Install Bun
# ----------------------------------------------------------------------------
install_bun() {
    print_step 2 "Installing Bun..."

    if check_command bun; then
        local bun_version
        bun_version=$(bun --version)
        print_success "Bun $bun_version is already installed"
    else
        print_info "Downloading Bun installer..."
        curl -fsSL https://bun.sh/install | bash >/dev/null 2>&1

        # Source bun for current session
        export BUN_INSTALL="$HOME/.bun"
        export PATH="$BUN_INSTALL/bin:$PATH"

        # Also add to root's profile
        if ! grep -q "BUN_INSTALL" ~/.bashrc 2>/dev/null; then
            echo 'export BUN_INSTALL="$HOME/.bun"' >> ~/.bashrc
            echo 'export PATH="$BUN_INSTALL/bin:$PATH"' >> ~/.bashrc
        fi

        local bun_version
        bun_version=$(bun --version)
        print_success "Bun $bun_version installed successfully"
    fi
}

# ----------------------------------------------------------------------------
# Step 3: Configure PostgreSQL
# ----------------------------------------------------------------------------
configure_postgresql() {
    print_step 3 "Configuring PostgreSQL..."

    print_info "Checking PostgreSQL status..."

    # Check if PostgreSQL is installed
    if ! check_command psql; then
        print_warning "PostgreSQL client not found"
        if confirm "Install PostgreSQL?"; then
            apt-get install -y -qq postgresql postgresql-contrib >/dev/null 2>&1
            systemctl enable postgresql >/dev/null 2>&1
            systemctl start postgresql
            print_success "PostgreSQL installed and started"
        else
            print_error "PostgreSQL is required for FastClient CRM"
            exit 1
        fi
    fi

    # Check if PostgreSQL is running
    if ! systemctl is-active --quiet postgresql; then
        print_warning "PostgreSQL is not running"
        systemctl start postgresql
        print_success "PostgreSQL started"
    fi

    # Check port 5432
    if ss -tuln | grep -q ":5432 "; then
        print_success "PostgreSQL running on port 5432"
    else
        print_error "PostgreSQL is not listening on port 5432"
        exit 1
    fi

    echo ""
    # Prompt for database credentials
    DB_USERNAME=$(ask_input "Database username" "fastclient")
    DB_PASSWORD=$(ask_password "Database password")
    DB_DATABASE=$(ask_input "Database name" "fastclient")
    DB_HOST="localhost"
    DB_PORT="5432"
    echo ""

    # Test connection
    print_info "Testing connection..."

    # Check if user exists
    local user_exists
    user_exists=$(sudo -u postgres psql -tAc "SELECT 1 FROM pg_roles WHERE rolname='$DB_USERNAME'" 2>/dev/null || echo "0")

    if [ "$user_exists" != "1" ]; then
        print_warning "User '$DB_USERNAME' does not exist"
        if confirm "Create user '$DB_USERNAME'?"; then
            sudo -u postgres psql -c "CREATE USER $DB_USERNAME WITH PASSWORD '$DB_PASSWORD';" >/dev/null 2>&1
            print_success "User '$DB_USERNAME' created"
        else
            print_error "Database user is required"
            exit 1
        fi
    else
        print_success "User '$DB_USERNAME' exists"
    fi

    # Check if database exists
    local db_exists
    db_exists=$(sudo -u postgres psql -tAc "SELECT 1 FROM pg_database WHERE datname='$DB_DATABASE'" 2>/dev/null || echo "0")

    if [ "$db_exists" != "1" ]; then
        print_warning "Database '$DB_DATABASE' does not exist"
        if confirm "Create database '$DB_DATABASE'?"; then
            sudo -u postgres psql -c "CREATE DATABASE $DB_DATABASE OWNER $DB_USERNAME;" >/dev/null 2>&1
            print_success "Database '$DB_DATABASE' created"
        else
            print_error "Database is required"
            exit 1
        fi
    else
        print_success "Database '$DB_DATABASE' exists"
    fi

    # Grant privileges
    sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE $DB_DATABASE TO $DB_USERNAME;" >/dev/null 2>&1
    print_success "Privileges granted"

    # Test actual connection with credentials
    if PGPASSWORD="$DB_PASSWORD" psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" -d "$DB_DATABASE" -c "SELECT 1;" >/dev/null 2>&1; then
        print_success "Connection successful"
    else
        print_error "Failed to connect with provided credentials"
        print_info "Make sure pg_hba.conf allows password authentication for local connections"
        exit 1
    fi
}

# ----------------------------------------------------------------------------
# Step 4: Configure Application
# ----------------------------------------------------------------------------
configure_application() {
    print_step 4 "Configuring Application..."

    echo ""
    DOMAIN=$(ask_input "Domain name (e.g., crm.example.com)")
    echo ""

    if [ -z "$DOMAIN" ]; then
        print_error "Domain name is required"
        exit 1
    fi

    # Copy project files to install path
    if [ "$SCRIPT_DIR" != "$INSTALL_PATH" ]; then
        print_info "Copying project to $INSTALL_PATH..."

        if [ -d "$INSTALL_PATH" ]; then
            print_warning "Directory $INSTALL_PATH already exists"
            if confirm "Remove existing installation?"; then
                rm -rf "$INSTALL_PATH"
            else
                print_error "Cannot proceed with existing installation"
                exit 1
            fi
        fi

        mkdir -p "$INSTALL_PATH"
        # Copy all files except node_modules (will be reinstalled)
        rsync -a --exclude='node_modules' --exclude='.git' "$SCRIPT_DIR/" "$INSTALL_PATH/"
        print_success "Project files copied"
    else
        print_info "Installing in current directory"
    fi

    # Create .env from .env.example
    print_info "Creating .env configuration..."
    cp "$INSTALL_PATH/.env.example" "$INSTALL_PATH/.env"

    # Update .env values
    sed -i "s|^APP_ENV=.*|APP_ENV=production|" "$INSTALL_PATH/.env"
    sed -i "s|^APP_DEBUG=.*|APP_DEBUG=false|" "$INSTALL_PATH/.env"
    sed -i "s|^APP_URL=.*|APP_URL=https://$DOMAIN|" "$INSTALL_PATH/.env"
    sed -i "s|^DB_HOST=.*|DB_HOST=$DB_HOST|" "$INSTALL_PATH/.env"
    sed -i "s|^DB_PORT=.*|DB_PORT=$DB_PORT|" "$INSTALL_PATH/.env"
    sed -i "s|^DB_DATABASE=.*|DB_DATABASE=$DB_DATABASE|" "$INSTALL_PATH/.env"
    sed -i "s|^DB_USERNAME=.*|DB_USERNAME=$DB_USERNAME|" "$INSTALL_PATH/.env"
    sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=$DB_PASSWORD|" "$INSTALL_PATH/.env"

    print_success "Environment configured"
}

# ----------------------------------------------------------------------------
# Step 5: Install Dependencies
# ----------------------------------------------------------------------------
install_dependencies() {
    print_step 5 "Installing Dependencies..."

    cd "$INSTALL_PATH"

    print_info "Running bun install..."
    bun install >/dev/null 2>&1
    print_success "Dependencies installed"

    print_info "Building assets..."
    bun run build >/dev/null 2>&1
    print_success "Assets built successfully"

    # Verify assets exist
    if [ -f "$INSTALL_PATH/public/assets/css/app.css" ]; then
        print_success "CSS assets verified"
    else
        print_warning "CSS assets not found - build may have failed"
    fi

    if [ -f "$INSTALL_PATH/public/assets/js/app.js" ]; then
        print_success "JS assets verified"
    else
        print_warning "JS assets not found - build may have failed"
    fi
}

# ----------------------------------------------------------------------------
# Step 6: Run Migrations
# ----------------------------------------------------------------------------
run_migrations() {
    print_step 6 "Running Migrations..."

    cd "$INSTALL_PATH"

    print_info "Executing migrations..."
    php migrate.php
    print_success "Migrations completed"
}

# ----------------------------------------------------------------------------
# Step 7: Set Permissions
# ----------------------------------------------------------------------------
set_permissions() {
    print_step 7 "Setting Permissions..."

    print_info "Setting ownership to www-data..."
    chown -R www-data:www-data "$INSTALL_PATH"
    print_success "Ownership set"

    print_info "Setting directory permissions..."
    find "$INSTALL_PATH" -type d -exec chmod 755 {} \;
    print_success "Directory permissions set"

    print_info "Setting file permissions..."
    find "$INSTALL_PATH" -type f -exec chmod 644 {} \;
    print_success "File permissions set"

    # Make sure .env is not world-readable
    chmod 640 "$INSTALL_PATH/.env"
    print_success "Secured .env file"
}

# ----------------------------------------------------------------------------
# Step 8: Configure Nginx
# ----------------------------------------------------------------------------
configure_nginx() {
    print_step 8 "Configuring Nginx..."

    # Check if nginx is installed
    if ! check_command nginx; then
        print_info "Installing Nginx..."
        apt-get install -y -qq nginx >/dev/null 2>&1
        print_success "Nginx installed"
    else
        print_success "Nginx is already installed"
    fi

    # Generate nginx config from template
    print_info "Generating Nginx configuration..."
    local nginx_config="/etc/nginx/sites-available/$DOMAIN"

    sed -e "s|{{DOMAIN}}|$DOMAIN|g" \
        -e "s|{{PROJECT_PATH}}|$INSTALL_PATH|g" \
        "$INSTALL_PATH/nginx.conf.template" > "$nginx_config"
    print_success "Configuration generated"

    # Create symlink
    print_info "Enabling site..."
    if [ -L "/etc/nginx/sites-enabled/$DOMAIN" ]; then
        rm "/etc/nginx/sites-enabled/$DOMAIN"
    fi
    ln -s "$nginx_config" "/etc/nginx/sites-enabled/$DOMAIN"
    print_success "Site enabled"

    # Remove default site if it exists
    if [ -L "/etc/nginx/sites-enabled/default" ]; then
        print_info "Removing default site..."
        rm "/etc/nginx/sites-enabled/default"
        print_success "Default site removed"
    fi

    # Test nginx config
    print_info "Testing Nginx configuration..."
    if nginx -t 2>&1 | grep -q "successful"; then
        print_success "Configuration valid"
    else
        print_error "Nginx configuration test failed"
        nginx -t
        exit 1
    fi

    # Reload nginx
    print_info "Reloading Nginx..."
    systemctl enable nginx >/dev/null 2>&1
    systemctl reload nginx
    print_success "Nginx reloaded"
}

# ----------------------------------------------------------------------------
# Step 9: SSL with Certbot
# ----------------------------------------------------------------------------
setup_ssl() {
    print_step 9 "Setting up SSL Certificate..."

    # Check if certbot is installed
    if ! check_command certbot; then
        print_info "Installing Certbot..."
        apt-get install -y -qq certbot python3-certbot-nginx >/dev/null 2>&1
        print_success "Certbot installed"
    else
        print_success "Certbot is already installed"
    fi

    echo ""
    print_warning "Certbot will attempt to obtain an SSL certificate for $DOMAIN"
    print_info "Make sure your domain's DNS is pointed to this server"
    echo ""

    if confirm "Proceed with SSL certificate installation?"; then
        print_info "Running Certbot..."
        if certbot --nginx -d "$DOMAIN" --non-interactive --agree-tos --register-unsafely-without-email; then
            print_success "SSL certificate installed"

            # Verify HTTPS
            print_info "Verifying HTTPS..."
            if curl -sI "https://$DOMAIN" >/dev/null 2>&1; then
                print_success "HTTPS is working"
            else
                print_warning "Could not verify HTTPS - DNS propagation may still be in progress"
            fi
        else
            print_warning "Certbot failed - you can run it manually later with:"
            print_info "certbot --nginx -d $DOMAIN"
        fi
    else
        print_warning "SSL setup skipped - run 'certbot --nginx -d $DOMAIN' later"
    fi
}

# ----------------------------------------------------------------------------
# Final Summary
# ----------------------------------------------------------------------------
print_summary() {
    echo ""
    echo -e "${GREEN}╔══════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${GREEN}║${NC}${BOLD}           Installation Complete!                             ${NC}${GREEN}║${NC}"
    echo -e "${GREEN}╚══════════════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo -e "${BOLD}Access URL:${NC}"
    echo -e "  https://$DOMAIN"
    echo ""
    echo -e "${BOLD}Important Locations:${NC}"
    echo -e "  Application:    $INSTALL_PATH"
    echo -e "  Configuration:  $INSTALL_PATH/.env"
    echo -e "  Nginx Config:   /etc/nginx/sites-available/$DOMAIN"
    echo -e "  PHP-FPM Socket: /var/run/php/php8.4-fpm.sock"
    echo ""
    echo -e "${BOLD}Useful Commands:${NC}"
    echo -e "  View logs:      tail -f /var/log/nginx/error.log"
    echo -e "  Restart PHP:    systemctl restart php8.4-fpm"
    echo -e "  Restart Nginx:  systemctl restart nginx"
    echo ""
    echo -e "${YELLOW}Note:${NC} If this is a new installation, you may need to create an admin user."
    echo ""
}

# ----------------------------------------------------------------------------
# Main Execution
# ----------------------------------------------------------------------------
main() {
    print_banner
    preflight_checks
    install_php
    install_bun
    configure_postgresql
    configure_application
    install_dependencies
    run_migrations
    set_permissions
    configure_nginx
    setup_ssl
    print_summary
}

main "$@"
