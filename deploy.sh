#!/usr/bin/env bash
set -eu

: "${SFTP_HOST:?Define SFTP_HOST}"
: "${SFTP_USER:?Define SFTP_USER}"
: "${SFTP_PASSWORD:?Define SFTP_PASSWORD}"
: "${SFTP_TARGET:?Define SFTP_TARGET con la carpeta remota de la aplicación}"

SFTP_PORT="${SFTP_PORT:-22}"

if ! command -v lftp >/dev/null 2>&1; then
  echo "Falta lftp. Instálalo antes de desplegar." >&2
  exit 1
fi

case "$SFTP_TARGET" in
  ""|"/") echo "SFTP_TARGET no puede ser la raíz remota." >&2; exit 1 ;;
esac

lftp -u "$SFTP_USER","$SFTP_PASSWORD" "sftp://$SFTP_HOST:$SFTP_PORT" <<EOF
set sftp:auto-confirm no
set net:max-retries 2
mkdir -p "$SFTP_TARGET/public/assets"
mkdir -p "$SFTP_TARGET/src/views"
mkdir -p "$SFTP_TARGET/config"
mkdir -p "$SFTP_TARGET/data"
mirror --reverse --only-newer --verbose public/ "$SFTP_TARGET/public/"
mirror --reverse --only-newer --verbose src/ "$SFTP_TARGET/src/"
mirror --reverse --only-newer --verbose config/ "$SFTP_TARGET/config/"
put data/.htaccess -o "$SFTP_TARGET/data/.htaccess"
bye
EOF

echo "Código desplegado. .env y los ficheros SQLite no se han enviado."
