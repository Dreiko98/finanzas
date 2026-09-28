#!/usr/bin/env bash
set -eu

: "${SFTP_HOST:?Define SFTP_HOST}"
: "${SFTP_USER:?Define SFTP_USER}"
: "${SFTP_PASSWORD:?Define SFTP_PASSWORD}"
: "${SFTP_TARGET:?Define SFTP_TARGET con la carpeta remota de la aplicación}"

SFTP_PORT="${SFTP_PORT:-22}"
SFTP_LAYOUT="${SFTP_LAYOUT:-flat}"
SFTP_ALLOW_ROOT="${SFTP_ALLOW_ROOT:-no}"

if ! command -v lftp >/dev/null 2>&1; then
  echo "Falta lftp. Instálalo antes de desplegar." >&2
  exit 1
fi

case "$SFTP_TARGET" in
  "") echo "SFTP_TARGET no puede estar vacío." >&2; exit 1 ;;
  "/")
    if [ "$SFTP_ALLOW_ROOT" != "yes" ]; then
      echo "Para desplegar en la raíz remota define SFTP_ALLOW_ROOT=yes." >&2
      exit 1
    fi
    ;;
esac

REMOTE_PREFIX="${SFTP_TARGET%/}"

if [ "$SFTP_LAYOUT" = "flat" ]; then
  lftp -u "$SFTP_USER","$SFTP_PASSWORD" "sftp://$SFTP_HOST:$SFTP_PORT" <<EOF
set sftp:auto-confirm no
set net:max-retries 2
mkdir -p "$REMOTE_PREFIX/assets"
mkdir -p "$REMOTE_PREFIX/src/views"
mkdir -p "$REMOTE_PREFIX/config"
mkdir -p "$REMOTE_PREFIX/data"
mirror --reverse --verbose public/ "$REMOTE_PREFIX/"
mirror --reverse --verbose src/ "$REMOTE_PREFIX/src/"
mirror --reverse --verbose config/ "$REMOTE_PREFIX/config/"
put data/.htaccess -o "$REMOTE_PREFIX/data/.htaccess"
bye
EOF
elif [ "$SFTP_LAYOUT" = "parent" ]; then
  lftp -u "$SFTP_USER","$SFTP_PASSWORD" "sftp://$SFTP_HOST:$SFTP_PORT" <<EOF
set sftp:auto-confirm no
set net:max-retries 2
mkdir -p "$REMOTE_PREFIX/public/assets"
mkdir -p "$REMOTE_PREFIX/src/views"
mkdir -p "$REMOTE_PREFIX/config"
mkdir -p "$REMOTE_PREFIX/data"
mirror --reverse --verbose public/ "$REMOTE_PREFIX/public/"
mirror --reverse --verbose src/ "$REMOTE_PREFIX/src/"
mirror --reverse --verbose config/ "$REMOTE_PREFIX/config/"
put data/.htaccess -o "$REMOTE_PREFIX/data/.htaccess"
bye
EOF
else
  echo "SFTP_LAYOUT debe ser flat o parent." >&2
  exit 1
fi

echo "Código desplegado. .env y los ficheros SQLite no se han enviado."
