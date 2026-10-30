#!/bin/sh
# Uploads the whole project to the server over FTPS. Usage: ./deploy.sh [--dry-run]
set -eu
cd "$(dirname "$0")"

# Kept one folder above the project so it can never be committed or uploaded.
ENV_FILE=../.env.deploy

if [ ! -f "$ENV_FILE" ]; then
  echo "Missing $ENV_FILE (needs FTP_HOST, FTP_USER, FTP_PASSWORD, FTP_DIR)." >&2
  exit 1
fi

env_value() { grep "^$1=" "$ENV_FILE" | head -n1 | cut -d= -f2- || true; }

HOST=$(env_value FTP_HOST)
USER=$(env_value FTP_USER)
PASS=$(env_value FTP_PASSWORD)
DIR=$(env_value FTP_DIR)

if [ -z "$HOST" ] || [ -z "$USER" ] || [ -z "$PASS" ] || [ -z "$DIR" ]; then
  echo "Set FTP_HOST, FTP_USER, FTP_PASSWORD and FTP_DIR in $ENV_FILE first." >&2
  exit 1
fi

DRY_RUN=""
[ "${1:-}" = "--dry-run" ] && DRY_RUN="--dry-run"

# Excluded paths are neither uploaded nor deleted on the server, so the live-only files survive.
lftp <<EOF
set ftp:ssl-force true
set ssl:verify-certificate yes
open -u "$USER","$PASS" "$HOST"
mirror -R --delete --verbose --parallel=4 $DRY_RUN \
  -x '^\.git/' \
  -x '^\.env$' \
  -x '(^|/)\.DS_Store$' \
  -x '^vendor/' \
  -x '^config/config\.production\.php$' \
  -x '^config/config\.local\.php$' \
  -x '^public/assets/uploads/' \
  . "$DIR"
EOF
