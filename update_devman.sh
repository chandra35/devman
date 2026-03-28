#!/bin/bash

# ==============================================
# DEVMAN - UPDATE SCRIPT
# ==============================================
# Usage:
#   ./update_devman.sh           → update biasa
#   ./update_devman.sh --seed    → update + migrate:fresh --seed
# ==============================================

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

APP_DIR="/home/manmetr1/devman"
SEED=false

# Parse arguments
for arg in "$@"; do
    case $arg in
        --seed) SEED=true ;;
    esac
done

info()    { echo -e "${BLUE}[INFO]${NC} $1"; }
success() { echo -e "${GREEN}[OK]${NC} $1"; }
warn()    { echo -e "${YELLOW}[WARN]${NC} $1"; }
fail()    { echo -e "${RED}[FAIL]${NC} $1"; }

echo ""
echo -e "${GREEN}==============================================${NC}"
echo -e "${GREEN}   DEVMAN - UPDATE SCRIPT${NC}"
echo -e "${GREEN}==============================================${NC}"
echo ""

# Masuk direktori
cd $APP_DIR || { fail "Gagal masuk $APP_DIR"; exit 1; }
success "Direktori: $(pwd)"

# Maintenance mode ON
info "Maintenance mode ON..."
php artisan down --retry=60 2>/dev/null || true

# Reset composer.lock (beda PHP lokal vs hosting)
info "Reset composer.lock untuk PHP hosting..."
git checkout -- composer.lock 2>/dev/null || true

# Stash perubahan lokal lainnya (misal .env)
if [[ -n $(git status --porcelain) ]]; then
    warn "Ada perubahan lokal, auto-stash..."
    git stash --quiet
fi

# Pull dari GitHub
info "Pull dari GitHub..."
git pull origin main
if [ $? -ne 0 ]; then
    fail "Gagal pull dari GitHub!"
    php artisan up 2>/dev/null || true
    exit 1
fi
success "Pull berhasil"

# Composer install (hapus lock, resolve ulang utk PHP hosting)
info "Install dependencies Composer..."
rm -f composer.lock
composer install --no-dev --optimize-autoloader --no-interaction 2>&1 | tail -5
success "Composer done"

# Migrasi database
if [ "$SEED" = true ]; then
    warn "Menjalankan migrate:fresh --seed (RESET DATABASE)..."
    php artisan migrate:fresh --seed --force
else
    info "Menjalankan migrate..."
    php artisan migrate --force
fi
success "Database OK"

# Clear & rebuild cache
info "Clear & rebuild cache..."
php artisan config:clear --quiet
php artisan route:clear --quiet
php artisan view:clear --quiet
php artisan event:clear --quiet 2>/dev/null || true
php artisan config:cache --quiet
php artisan route:cache --quiet
php artisan view:cache --quiet
success "Cache rebuilt"

# Storage link
if [ ! -L "public/storage" ]; then
    php artisan storage:link --quiet
    success "Storage link dibuat"
fi

# Permission
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# Maintenance mode OFF
php artisan up
success "Maintenance mode OFF"

# Summary
echo ""
echo -e "${GREEN}==============================================${NC}"
echo -e "${GREEN}   UPDATE SELESAI!${NC}"
echo -e "${GREEN}==============================================${NC}"
echo ""
info "Laravel: $(php artisan --version 2>/dev/null)"
info "PHP: $(php -v 2>/dev/null | head -1)"
info "Waktu: $(date '+%Y-%m-%d %H:%M:%S')"
info "Commit: $(git log -1 --pretty=format:'%h - %s (%cr)')"
echo ""
