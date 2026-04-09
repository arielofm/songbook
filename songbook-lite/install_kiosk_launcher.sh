#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

APP_DIR="$(pwd)"

detect_target_home() {
  local user_name passwd_home
  user_name="$(id -un)"
  passwd_home="$(getent passwd "${user_name}" | cut -d: -f6 || true)"

  if [[ -n "${passwd_home}" && "${HOME}" == */snap/* && "${passwd_home}" != "${HOME}" ]]; then
    printf '%s\n' "${passwd_home}"
    return
  fi

  printf '%s\n' "${HOME}"
}

TARGET_HOME="$(detect_target_home)"

resolve_xdg_data_home() {
  local default_data_home candidate
  default_data_home="${TARGET_HOME}/.local/share"
  candidate="${XDG_DATA_HOME:-}"

  if [[ -z "${candidate}" || "${candidate}" == "${TARGET_HOME}"/snap/* ]]; then
    printf '%s\n' "${default_data_home}"
    return
  fi

  printf '%s\n' "${candidate}"
}

resolve_xdg_config_home() {
  local default_config_home candidate
  default_config_home="${TARGET_HOME}/.config"
  candidate="${XDG_CONFIG_HOME:-}"

  if [[ -z "${candidate}" || "${candidate}" == "${TARGET_HOME}"/snap/* ]]; then
    printf '%s\n' "${default_config_home}"
    return
  fi

  printf '%s\n' "${candidate}"
}

EFFECTIVE_XDG_DATA_HOME="$(resolve_xdg_data_home)"
EFFECTIVE_XDG_CONFIG_HOME="$(resolve_xdg_config_home)"

DESKTOP_DIR="${EFFECTIVE_XDG_DATA_HOME}/applications"
DESKTOP_FILE="${DESKTOP_DIR}/songshelf-kiosk.desktop"
AUTOSTART_DIR="${EFFECTIVE_XDG_CONFIG_HOME}/autostart"
AUTOSTART_FILE="${AUTOSTART_DIR}/songshelf-kiosk.desktop"
DESKTOP_SHORTCUT_FILE=""

ENABLE_AUTOSTART=false
for arg in "$@"; do
  case "${arg}" in
    --autostart)
      ENABLE_AUTOSTART=true
      ;;
    *)
      echo "Unknown option: ${arg}"
      echo "Usage: bash install_kiosk_launcher.sh [--autostart]"
      exit 1
      ;;
  esac
done

mkdir -p "${DESKTOP_DIR}"

if command -v xdg-user-dir >/dev/null 2>&1; then
  desktop_folder="$(xdg-user-dir DESKTOP 2>/dev/null || true)"
else
  desktop_folder="${TARGET_HOME}/Desktop"
fi

if [[ -n "${desktop_folder}" && -d "${desktop_folder}" ]]; then
  DESKTOP_SHORTCUT_FILE="${desktop_folder}/SongShelf Kiosk.desktop"
fi

cat > "${DESKTOP_FILE}" <<EOF
[Desktop Entry]
Version=1.0
Type=Application
Name=SongShelf Kiosk
Comment=Launch SongShelf in kiosk mode
Exec=${APP_DIR}/start_kiosk.sh
Path=${APP_DIR}
Icon=${APP_DIR}/public/assets/logo.png
Terminal=false
Categories=AudioVideo;Office;
EOF

chmod +x "${APP_DIR}/start_kiosk.sh" "${APP_DIR}/install_and_run.sh" "${APP_DIR}/install_kiosk_launcher.sh"
chmod +x "${DESKTOP_FILE}"

echo "Launcher installed at ${DESKTOP_FILE}"
echo "You can now search for 'SongShelf Kiosk' in your app launcher."

if [[ -n "${DESKTOP_SHORTCUT_FILE}" ]]; then
  cp "${DESKTOP_FILE}" "${DESKTOP_SHORTCUT_FILE}"
  chmod +x "${DESKTOP_SHORTCUT_FILE}"
  echo "Desktop shortcut installed at ${DESKTOP_SHORTCUT_FILE}"
fi

if [[ "${ENABLE_AUTOSTART}" == "true" ]]; then
  mkdir -p "${AUTOSTART_DIR}"
  cp "${DESKTOP_FILE}" "${AUTOSTART_FILE}"
  chmod +x "${AUTOSTART_FILE}"
  echo "Auto-start enabled at login: ${AUTOSTART_FILE}"
else
  echo "Tip: run 'bash install_kiosk_launcher.sh --autostart' to launch automatically on login."
fi
