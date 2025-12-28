#!/bin/bash

###############################################################################
# User Seeder Runner Script
# Wrapper script untuk menjalankan user seeder dengan berbagai opsi
#
# Usage:
#   ./run_seeder.sh [options]
#
# Options:
#   --test      Run unit tests only
#   --seed      Run seeder only
#   --all       Run tests then seeder (default)
#   --help      Show this help message
#
# Author: Kiro AI Assistant
# Version: 1.0.0
###############################################################################

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
ROOT_DIR="$(dirname "$SCRIPT_DIR")"

# Functions
print_header() {
    echo -e "${BLUE}"
    echo "==========================================="
    echo "  $1"
    echo "==========================================="
    echo -e "${NC}"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ $1${NC}"
}

check_php() {
    if ! command -v php &> /dev/null; then
        print_error "PHP is not installed or not in PATH"
        exit 1
    fi
    
    PHP_VERSION=$(php -r "echo PHP_VERSION;")
    print_success "PHP version: $PHP_VERSION"
}

check_database() {
    print_info "Checking database connection..."
    
    php -r "
    require '$ROOT_DIR/config/database.php';
    try {
        \$db = Database::getInstance()->getConnection();
        echo 'OK';
    } catch (Exception \$e) {
        echo 'FAIL: ' . \$e->getMessage();
        exit(1);
    }
    "
    
    if [ $? -eq 0 ]; then
        print_success "Database connection OK"
    else
        print_error "Database connection failed"
        exit 1
    fi
}

run_tests() {
    print_header "Running Unit Tests"
    
    php "$SCRIPT_DIR/test_user_seeder.php"
    
    if [ $? -eq 0 ]; then
        print_success "All tests passed"
        return 0
    else
        print_error "Some tests failed"
        return 1
    fi
}

run_seeder() {
    print_header "Running User Seeder"
    
    php "$SCRIPT_DIR/seed_users.php"
    
    if [ $? -eq 0 ]; then
        print_success "Seeder completed successfully"
        return 0
    else
        print_error "Seeder failed"
        return 1
    fi
}

show_help() {
    cat << EOF
User Seeder Runner Script

Usage:
    $0 [options]

Options:
    --test      Run unit tests only
    --seed      Run seeder only
    --all       Run tests then seeder (default)
    --help      Show this help message

Examples:
    $0                  # Run tests and seeder
    $0 --test          # Run tests only
    $0 --seed          # Run seeder only

For more information, see scripts/README.md
EOF
}

# Main execution
main() {
    local mode="all"
    
    # Parse arguments
    while [[ $# -gt 0 ]]; do
        case $1 in
            --test)
                mode="test"
                shift
                ;;
            --seed)
                mode="seed"
                shift
                ;;
            --all)
                mode="all"
                shift
                ;;
            --help|-h)
                show_help
                exit 0
                ;;
            *)
                print_error "Unknown option: $1"
                show_help
                exit 1
                ;;
        esac
    done
    
    # Print banner
    print_header "JAGAPADI User Seeder"
    
    # Check prerequisites
    print_info "Checking prerequisites..."
    check_php
    check_database
    echo ""
    
    # Execute based on mode
    case $mode in
        test)
            run_tests
            exit $?
            ;;
        seed)
            run_seeder
            exit $?
            ;;
        all)
            run_tests
            if [ $? -eq 0 ]; then
                echo ""
                run_seeder
                exit $?
            else
                print_warning "Skipping seeder due to test failures"
                exit 1
            fi
            ;;
    esac
}

# Run main function
main "$@"
