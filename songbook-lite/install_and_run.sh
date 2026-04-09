#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")"

link_assets() {
  mkdir -p public/assets
  ln -sfn ../../assets/style.css public/assets/style.css
  ln -sfn ../../assets/app.js public/assets/app.js
  ln -sfn ../../assets/logo-songshelf.svg public/assets/logo-songshelf.svg
}

if ! command -v php >/dev/null 2>&1; then
  echo "PHP is not installed. On Ubuntu run: sudo apt update && sudo apt install -y php php-sqlite3"
  exit 1
fi

if ! php -m | grep -qi pdo_sqlite; then
  echo "The SQLite extension for PHP is missing. On Ubuntu run: sudo apt install -y php-sqlite3"
  exit 1
fi

mkdir -p data storage/imports public/assets
link_assets
php -r "require 'app/bootstrap.php'; db(); echo 'Database ready at ' . DB_PATH . PHP_EOL;"

echo "Starting SongShelf at http://127.0.0.1:8080"
php -S 127.0.0.1:8080 -t public
