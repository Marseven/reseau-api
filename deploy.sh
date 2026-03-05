#!/bin/bash
# ============================================================
#  ReseauApp API (Laravel) - Script de déploiement
#  Serveur : reseau-api.jobs-conseil.host
#  Usage   : bash deploy.sh [setup|update|rollback]
# ============================================================

set -e

# ── Configuration ──
REPO_URL="https://github.com/Marseven/reseau-api.git"
BRANCH="main"
DOMAIN="reseau-api.jobs-conseil.host"
PUBLIC_HTML="$HOME/domains/$DOMAIN/public_html"
REPO_DIR="$HOME/reseau-api"
BACKUP_DIR="$HOME/backups/reseau-api"

# ── Auto-détection PHP 8.2+ ──
# Sur hébergement mutualisé, le binaire par défaut est souvent PHP 7.x
# On cherche PHP 8.2+ dans les chemins courants (Hostinger, CloudLinux, cPanel)
detect_php() {
    local candidates=(
        "/usr/bin/php8.2"
        "/usr/bin/php8.3"
        "/usr/bin/php8.4"
        "/opt/alt/php82/usr/bin/php"
        "/opt/alt/php83/usr/bin/php"
        "/opt/alt/php84/usr/bin/php"
        "/usr/local/bin/php8.2"
        "/usr/local/bin/php8.3"
        "/usr/local/bin/php8.4"
        "$HOME/bin/php"
    )
    for bin in "${candidates[@]}"; do
        if [ -x "$bin" ]; then
            local ver=$("$bin" -r 'echo PHP_MAJOR_VERSION;' 2>/dev/null)
            if [ "$ver" -ge 8 ] 2>/dev/null; then
                echo "$bin"
                return 0
            fi
        fi
    done
    # Fallback : vérifier si le php par défaut est 8.2+
    local default_php=$(which php 2>/dev/null)
    if [ -n "$default_php" ]; then
        local ver=$("$default_php" -r 'echo PHP_MAJOR_VERSION;' 2>/dev/null)
        if [ "$ver" -ge 8 ] 2>/dev/null; then
            echo "$default_php"
            return 0
        fi
    fi
    echo ""
    return 1
}

PHP_BIN=$(detect_php)

# ── Couleurs ──
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
CYAN='\033[0;36m'
NC='\033[0m'

log()   { echo -e "${GREEN}[✓]${NC} $1"; }
warn()  { echo -e "${YELLOW}[!]${NC} $1"; }
error() { echo -e "${RED}[✗]${NC} $1"; exit 1; }
info()  { echo -e "${CYAN}[→]${NC} $1"; }

COMMAND="${1:-update}"

echo ""
echo -e "${CYAN}══════════════════════════════════════════════${NC}"
echo -e "${CYAN}   ReseauApp API - Déploiement Laravel${NC}"
echo -e "${CYAN}══════════════════════════════════════════════${NC}"
echo -e "  Mode : ${YELLOW}${COMMAND}${NC}"
echo ""

# ── Vérifications ──
info "Recherche de PHP 8.2+..."
if [ -z "$PHP_BIN" ]; then
    echo ""
    error "PHP 8.2+ introuvable ! Laravel 11 requiert PHP >= 8.2.

  Vérifiez les versions disponibles sur votre serveur :
    ls /usr/bin/php*
    ls /opt/alt/php*/usr/bin/php

  Puis définissez le chemin manuellement :
    export PHP_BIN=/chemin/vers/php8.2
    bash deploy.sh $COMMAND

  Ou sur Hostinger, activez PHP 8.2 dans le panel."
fi

PHP_VERSION=$($PHP_BIN -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION.".".PHP_RELEASE_VERSION;' 2>/dev/null || echo "inconnu")
PHP_MAJOR=$($PHP_BIN -r 'echo PHP_MAJOR_VERSION;' 2>/dev/null || echo "0")
PHP_MINOR=$($PHP_BIN -r 'echo PHP_MINOR_VERSION;' 2>/dev/null || echo "0")

if [ "$PHP_MAJOR" -lt 8 ] || ([ "$PHP_MAJOR" -eq 8 ] && [ "$PHP_MINOR" -lt 2 ]); then
    error "PHP $PHP_VERSION détecté ($PHP_BIN) - Laravel 11 requiert PHP >= 8.2"
fi

log "PHP $PHP_VERSION détecté ($PHP_BIN)"

# ── Composer (utilise le même PHP) ──
if ! command -v composer &>/dev/null; then
    if [ ! -f "$HOME/composer.phar" ]; then
        warn "Composer non trouvé, installation locale..."
        curl -sS https://getcomposer.org/installer | $PHP_BIN -- --install-dir="$HOME" --filename=composer.phar
    fi
    COMPOSER="$PHP_BIN $HOME/composer.phar"
else
    # Forcer composer à utiliser le bon PHP
    COMPOSER="$PHP_BIN $(which composer)"
fi
log "Composer disponible"

# ══════════════════════════════════════════════
#  SETUP : Première installation
# ══════════════════════════════════════════════
if [ "$COMMAND" = "setup" ]; then

    # ── Étape 1 : Cloner le dépôt ──
    if [ -d "$REPO_DIR/.git" ]; then
        warn "Le dépôt existe déjà dans $REPO_DIR"
    else
        info "Clonage du dépôt..."
        git clone --branch "$BRANCH" "$REPO_URL" "$REPO_DIR"
        log "Dépôt cloné"
    fi

    cd "$REPO_DIR"

    # ── Étape 2 : Installer les dépendances ──
    info "Installation des dépendances (production)..."
    $COMPOSER install --no-dev --optimize-autoloader --no-interaction
    log "Dépendances installées"

    # ── Étape 3 : Fichier .env ──
    if [ ! -f ".env" ]; then
        info "Création du fichier .env..."
        cp .env.example .env

        # Générer la clé
        $PHP_BIN artisan key:generate --force

        echo ""
        echo -e "${YELLOW}══════════════════════════════════════════════${NC}"
        echo -e "${YELLOW}  CONFIGURATION REQUISE${NC}"
        echo -e "${YELLOW}══════════════════════════════════════════════${NC}"
        echo ""
        echo -e "  Éditez ${CYAN}$REPO_DIR/.env${NC} avec :"
        echo ""
        echo "  APP_ENV=production"
        echo "  APP_DEBUG=false"
        echo "  APP_URL=https://reseau-api.jobs-conseil.host"
        echo ""
        echo "  DB_CONNECTION=mysql"
        echo "  DB_HOST=127.0.0.1"
        echo "  DB_PORT=3306"
        echo "  DB_DATABASE=votre_base"
        echo "  DB_USERNAME=votre_user"
        echo "  DB_PASSWORD=votre_password"
        echo ""
        echo "  SANCTUM_STATEFUL_DOMAINS=reseau.jobs-conseil.host"
        echo "  SESSION_DOMAIN=.jobs-conseil.host"
        echo ""
        echo -e "  Puis relancez : ${GREEN}bash deploy.sh setup${NC}"
        echo ""
        exit 0
    fi

    log "Fichier .env présent"

    # ── Étape 4 : Permissions storage ──
    info "Configuration des permissions..."
    mkdir -p storage/framework/{cache,sessions,views}
    mkdir -p storage/logs
    mkdir -p bootstrap/cache
    chmod -R 775 storage bootstrap/cache
    log "Permissions configurées"

    # ── Étape 5 : Base de données ──
    info "Exécution des migrations..."
    $PHP_BIN artisan migrate --force
    log "Migrations exécutées"

    info "Exécution du seeder..."
    $PHP_BIN artisan db:seed --force
    log "Seeder exécuté"

    # ── Étape 6 : Optimisation ──
    info "Optimisation pour la production..."
    $PHP_BIN artisan config:cache
    $PHP_BIN artisan route:cache
    $PHP_BIN artisan view:cache
    log "Caches générés (config, routes, views)"

    # ── Étape 7 : Lier public_html → public/ ──
    info "Configuration du document root..."

    # Sauvegarder le contenu original de public_html si nécessaire
    if [ -d "$PUBLIC_HTML" ] && [ ! -L "$PUBLIC_HTML" ]; then
        if [ "$(ls -A "$PUBLIC_HTML" 2>/dev/null)" ]; then
            mv "$PUBLIC_HTML" "${PUBLIC_HTML}_original"
            warn "Ancien public_html sauvegardé dans public_html_original"
        else
            rmdir "$PUBLIC_HTML"
        fi
    fi

    # Créer le symlink
    if [ -L "$PUBLIC_HTML" ]; then
        rm "$PUBLIC_HTML"
    fi
    ln -s "$REPO_DIR/public" "$PUBLIC_HTML"
    log "Symlink créé : public_html → $REPO_DIR/public"

    # ── Étape 8 : .htaccess CORS + Sécurité ──
    info "Configuration .htaccess avec CORS..."
    cat > "$REPO_DIR/public/.htaccess" << 'HTACCESS'
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Redirection HTTPS
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# ── CORS Headers ──
<IfModule mod_headers.c>
    Header always set Access-Control-Allow-Origin "https://reseau.jobs-conseil.host"
    Header always set Access-Control-Allow-Methods "GET, POST, PUT, PATCH, DELETE, OPTIONS"
    Header always set Access-Control-Allow-Headers "Content-Type, Authorization, X-Requested-With, Accept"
    Header always set Access-Control-Allow-Credentials "true"
    Header always set Access-Control-Max-Age "86400"

    # Preflight OPTIONS
    RewriteCond %{REQUEST_METHOD} OPTIONS
    RewriteRule ^(.*)$ $1 [R=200,L]
</IfModule>

# ── Sécurité ──
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "DENY"
    Header set X-XSS-Protection "1; mode=block"
    Header set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# ── Compression ──
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE application/json text/html text/css application/javascript
</IfModule>

# ── Bloquer fichiers sensibles ──
<FilesMatch "\.(env|git|gitignore|md|sh|lock|yml|yaml)$">
    Order allow,deny
    Deny from all
</FilesMatch>
HTACCESS

    log ".htaccess configuré (CORS, HTTPS, sécurité)"

    # ── Résumé setup ──
    echo ""
    echo -e "${GREEN}══════════════════════════════════════════════${NC}"
    echo -e "${GREEN}   Installation terminée !${NC}"
    echo -e "${GREEN}══════════════════════════════════════════════${NC}"
    echo ""
    echo -e "  ${CYAN}API URL${NC}  : https://reseau-api.jobs-conseil.host"
    echo -e "  ${CYAN}Health${NC}   : https://reseau-api.jobs-conseil.host/up"
    echo -e "  ${CYAN}Repo${NC}     : $REPO_DIR"
    echo -e "  ${CYAN}Symlink${NC}  : public_html → $REPO_DIR/public"
    echo ""
    echo -e "  Pour les prochaines mises à jour : ${GREEN}bash deploy.sh${NC}"
    echo ""
    exit 0
fi

# ══════════════════════════════════════════════
#  UPDATE : Mise à jour (par défaut)
# ══════════════════════════════════════════════
if [ "$COMMAND" = "update" ]; then

    [ -d "$REPO_DIR/.git" ] || error "Le dépôt n'existe pas. Lancez d'abord : bash deploy.sh setup"
    cd "$REPO_DIR"

    # ── Backup base de données ──
    info "Backup de la base de données..."
    mkdir -p "$BACKUP_DIR"
    TIMESTAMP=$(date +%Y%m%d_%H%M%S)

    DB_CONN=$($PHP_BIN artisan tinker --execute="echo config('database.default');" 2>/dev/null | tail -1)
    if [ "$DB_CONN" = "mysql" ]; then
        DB_NAME=$($PHP_BIN artisan tinker --execute="echo config('database.connections.mysql.database');" 2>/dev/null | tail -1)
        DB_USER=$($PHP_BIN artisan tinker --execute="echo config('database.connections.mysql.username');" 2>/dev/null | tail -1)
        DB_PASS=$($PHP_BIN artisan tinker --execute="echo config('database.connections.mysql.password');" 2>/dev/null | tail -1)
        DB_HOST=$($PHP_BIN artisan tinker --execute="echo config('database.connections.mysql.host');" 2>/dev/null | tail -1)

        if mysqldump -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_DIR/db_${TIMESTAMP}.sql" 2>/dev/null; then
            gzip "$BACKUP_DIR/db_${TIMESTAMP}.sql"
            log "Backup DB : db_${TIMESTAMP}.sql.gz"
        else
            warn "Backup DB échoué (mysqldump indisponible ?)"
        fi
    elif [ "$DB_CONN" = "sqlite" ]; then
        DB_PATH=$($PHP_BIN artisan tinker --execute="echo config('database.connections.sqlite.database');" 2>/dev/null | tail -1)
        if [ -f "$DB_PATH" ]; then
            cp "$DB_PATH" "$BACKUP_DIR/db_${TIMESTAMP}.sqlite"
            log "Backup DB : db_${TIMESTAMP}.sqlite"
        fi
    fi

    # Garder les 5 derniers backups
    ls -t "$BACKUP_DIR"/db_* 2>/dev/null | tail -n +6 | xargs rm -f 2>/dev/null || true

    # ── Mode maintenance ──
    info "Activation du mode maintenance..."
    $PHP_BIN artisan down --refresh=15 --retry=60 2>/dev/null || true
    log "Mode maintenance activé"

    # ── Pull ──
    info "Mise à jour du code..."
    PREV_COMMIT=$(git rev-parse HEAD 2>/dev/null || echo "unknown")
    git fetch origin "$BRANCH" --quiet
    git reset --hard "origin/$BRANCH" 2>/dev/null
    NEW_COMMIT=$(git rev-parse HEAD)
    log "Code mis à jour ($(git log -1 --format='%h - %s'))"

    # ── Dépendances ──
    info "Mise à jour des dépendances..."
    $COMPOSER install --no-dev --optimize-autoloader --no-interaction
    log "Dépendances à jour"

    # ── Migrations ──
    info "Exécution des migrations..."
    $PHP_BIN artisan migrate --force
    log "Migrations exécutées"

    # ── Cache ──
    info "Reconstruction des caches..."
    $PHP_BIN artisan config:cache
    $PHP_BIN artisan route:cache
    $PHP_BIN artisan view:cache
    $PHP_BIN artisan event:cache 2>/dev/null || true
    log "Caches reconstruits"

    # ── Permissions ──
    chmod -R 775 storage bootstrap/cache 2>/dev/null || true

    # ── Fin maintenance ──
    info "Désactivation du mode maintenance..."
    $PHP_BIN artisan up
    log "Application en ligne"

    # ── Résumé ──
    echo ""
    echo -e "${GREEN}══════════════════════════════════════════════${NC}"
    echo -e "${GREEN}   Mise à jour terminée !${NC}"
    echo -e "${GREEN}══════════════════════════════════════════════${NC}"
    echo ""
    echo -e "  ${CYAN}Commit${NC}  : $(git log -1 --format='%h %s')"
    echo -e "  ${CYAN}Date${NC}    : $(date '+%d/%m/%Y %H:%M:%S')"
    echo -e "  ${CYAN}Backup${NC}  : db_${TIMESTAMP}"
    if [ "$PREV_COMMIT" != "$NEW_COMMIT" ]; then
        echo -e "  ${CYAN}Diff${NC}    : $PREV_COMMIT → ${NEW_COMMIT:0:7}"
    else
        echo -e "  ${YELLOW}Aucun changement de code${NC}"
    fi
    echo ""
    exit 0
fi

# ══════════════════════════════════════════════
#  ROLLBACK : Retour en arrière
# ══════════════════════════════════════════════
if [ "$COMMAND" = "rollback" ]; then

    [ -d "$REPO_DIR/.git" ] || error "Le dépôt n'existe pas."
    cd "$REPO_DIR"

    info "Activation du mode maintenance..."
    $PHP_BIN artisan down --refresh=15 2>/dev/null || true

    info "Rollback au commit précédent..."
    git reset --hard HEAD~1

    info "Mise à jour des dépendances..."
    $COMPOSER install --no-dev --optimize-autoloader --no-interaction

    info "Rollback des migrations..."
    $PHP_BIN artisan migrate:rollback --force

    info "Reconstruction des caches..."
    $PHP_BIN artisan config:cache
    $PHP_BIN artisan route:cache
    $PHP_BIN artisan view:cache

    info "Remise en ligne..."
    $PHP_BIN artisan up

    # Restaurer le dernier backup DB si dispo
    LATEST_BACKUP=$(ls -t "$BACKUP_DIR"/db_*.sql.gz 2>/dev/null | head -1)
    if [ -n "$LATEST_BACKUP" ]; then
        echo ""
        echo -e "  ${YELLOW}Backup DB disponible :${NC} $LATEST_BACKUP"
        echo -e "  Pour restaurer manuellement :"
        echo -e "  ${CYAN}gunzip < $LATEST_BACKUP | mysql -u USER -p DB_NAME${NC}"
    fi

    echo ""
    echo -e "${GREEN}══════════════════════════════════════════════${NC}"
    echo -e "${GREEN}   Rollback terminé !${NC}"
    echo -e "${GREEN}══════════════════════════════════════════════${NC}"
    echo -e "  ${CYAN}Commit${NC}  : $(git log -1 --format='%h %s')"
    echo ""
    exit 0
fi

error "Commande inconnue : $COMMAND. Utilisez : setup | update | rollback"
