#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

HOST="127.0.0.1"
PORT="8080"
APP_URL="http://${HOST}:${PORT}"
PID_FILE="storage/songshelf-server.pid"
LOG_FILE="storage/songshelf-server.log"
KIOSK_LOG_FILE="storage/kiosk-launcher.log"

mkdir -p storage

log_kiosk() {
  local message="$1"
  local timestamp
  timestamp="$(date '+%Y-%m-%d %H:%M:%S')"
  printf '[%s] %s\n' "${timestamp}" "${message}" | tee -a "${KIOSK_LOG_FILE}"
}

link_assets() {
  mkdir -p public/assets
  ln -sfn ../../assets/style.css public/assets/style.css
  ln -sfn ../../assets/app.js public/assets/app.js
  ln -sfn ../../assets/logo-songshelf.svg public/assets/logo-songshelf.svg
}

ensure_requirements() {
  if ! command -v php >/dev/null 2>&1; then
    log_kiosk "PHP is not installed. On Ubuntu run: sudo apt update && sudo apt install -y php php-sqlite3"
    exit 1
  fi

  if ! php -m | grep -qi pdo_sqlite; then
    log_kiosk "The SQLite extension for PHP is missing. On Ubuntu run: sudo apt install -y php-sqlite3"
    exit 1
  fi
}

server_is_reachable() {
  php -r '
    $fp = @fsockopen("127.0.0.1", 8080, $errno, $errstr, 0.2);
    if ($fp) { fclose($fp); exit(0); }
    exit(1);
  ' >/dev/null 2>&1
}

ensure_server() {
  mkdir -p data storage storage/imports
  link_assets
  php -r "require 'app/bootstrap.php'; db(); echo 'Database ready at ' . DB_PATH . PHP_EOL;"

  if [[ -f "${PID_FILE}" ]]; then
    existing_pid="$(cat "${PID_FILE}")"
    if kill -0 "${existing_pid}" >/dev/null 2>&1; then
      return
    fi
    rm -f "${PID_FILE}"
  fi

  if server_is_reachable; then
    log_kiosk "Server is already reachable at ${APP_URL}; reusing existing process."
    return
  fi

  nohup php -S "${HOST}:${PORT}" -t public >> "${LOG_FILE}" 2>&1 &
  echo $! > "${PID_FILE}"
  log_kiosk "Started server process with PID $(cat "${PID_FILE}")"
}

wait_for_server() {
  for _ in $(seq 1 50); do
    if server_is_reachable; then
      return
    fi
    sleep 0.2
  done

  log_kiosk "SongShelf server did not start correctly. Check ${LOG_FILE}"
  tail -n 20 "${LOG_FILE}" >> "${KIOSK_LOG_FILE}" 2>/dev/null || true
  exit 1
}

launch_browser() {
  local name="$1"
  shift

  log_kiosk "Trying browser launcher: ${name}"
  "$@" >> "${KIOSK_LOG_FILE}" 2>&1 &
  local browser_pid=$!
  sleep 1

  if kill -0 "${browser_pid}" >/dev/null 2>&1; then
    log_kiosk "${name} launched successfully (PID ${browser_pid})."
    return 0
  fi

  wait "${browser_pid}" || true
  log_kiosk "${name} exited immediately; trying next launcher."
  return 1
}

open_browser() {
  if [[ -z "${DISPLAY:-}" && -z "${WAYLAND_DISPLAY:-}" ]]; then
    log_kiosk "No graphical session detected (DISPLAY/WAYLAND_DISPLAY unset). Cannot open kiosk browser."
    exit 1
  fi

  if command -v chromium >/dev/null 2>&1 && launch_browser "chromium" chromium --kiosk --app="${APP_URL}"; then
    return
  fi

  if command -v chromium-browser >/dev/null 2>&1 && launch_browser "chromium-browser" chromium-browser --kiosk --app="${APP_URL}"; then
    return
  fi

  if command -v google-chrome >/dev/null 2>&1 && launch_browser "google-chrome" google-chrome --kiosk --app="${APP_URL}"; then
    return
  fi

  if command -v firefox >/dev/null 2>&1 && launch_browser "firefox" firefox --new-window "${APP_URL}"; then
    return
  fi

  if command -v xdg-open >/dev/null 2>&1 && launch_browser "xdg-open" xdg-open "${APP_URL}"; then
    return
  fi

  log_kiosk "No supported browser launcher was able to start the app."
  exit 1
}

ensure_requirements
ensure_server
wait_for_server
open_browser
