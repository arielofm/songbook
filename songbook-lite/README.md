# SongShelf v2.2

SongShelf is a local-first songbook and setlist web app built with PHP, SQLite, HTML, CSS, and vanilla JavaScript. It is designed for personal rehearsal, worship teams, live performance preparation, and kiosk-style use on laptops, mini PCs, or dedicated display stations.

It runs entirely on your machine, stores its data locally, and focuses on fast song access, clean performance views, offline-friendly tools, and practical workflow features instead of cloud dependencies.

## Interface Preview

The README gallery uses the following screenshot files:

- `docs/screenshots/Screenshot from 2026-04-09 09-56-40.png`
- `docs/screenshots/Screenshot from 2026-04-09 09-56-57.png`
- `docs/screenshots/Screenshot from 2026-04-09 09-57-23.png`
- `docs/screenshots/Screenshot from 2026-04-09 09-57-41.png`
- `docs/screenshots/Screenshot from 2026-04-09 09-57-57.png`
- `docs/screenshots/Screenshot from 2026-04-09 09-58-14.png`
- `docs/screenshots/Screenshot from 2026-04-09 09-58-31.png`
- `docs/screenshots/Screenshot from 2026-04-09 09-58-58.png`
- `docs/screenshots/Screenshot from 2026-04-09 09-59-31.png`

### Library (Light Mode)

![Song Library Light Mode](docs/screenshots/Screenshot%20from%202026-04-09%2009-56-40.png)

### Library (Dark Mode)

![Song Library Dark Mode](docs/screenshots/Screenshot%20from%202026-04-09%2009-56-57.png)

### Song Performance View

![Song Performance View](docs/screenshots/Screenshot%20from%202026-04-09%2009-57-23.png)

### Focused Performance View

![Focused Performance View](docs/screenshots/Screenshot%20from%202026-04-09%2009-57-41.png)

### Chord Diagram Browser

![Chord Diagram Browser](docs/screenshots/Screenshot%20from%202026-04-09%2009-57-57.png)

### Build Setlist

![Build Setlist View](docs/screenshots/Screenshot%20from%202026-04-09%2009-58-14.png)

### Import and Paste

![Import and Paste View](docs/screenshots/Screenshot%20from%202026-04-09%2009-58-31.png)

### Local Assistant

![Local Assistant View](docs/screenshots/Screenshot%20from%202026-04-09%2009-58-58.png)

### Library List View (Dark Mode)

![Library List View Dark Mode](docs/screenshots/Screenshot%20from%202026-04-09%2009-59-31.png)

## What SongShelf Does

SongShelf helps you:

- store songs in a local searchable library
- write and edit songs using chords-over-lyrics formatting
- import plain text chord sheets and clean them into a more usable format
- import PDF charts and display them inside the app
- transpose songs on the fly
- auto-scroll songs and PDFs during practice or performance
- use a metronome in both the sidebar and focus mode
- build setlists and rearrange performance order
- browse built-in piano chord diagrams
- run a local in-app assistant workbench for cleanup, key/chord analysis, and structure fixes
- train assistant behavior with saved correction examples and reusable input-output pairs
- detect repeated chords used in a song and show them in focus mode
- detect likely chords inside PDF files and summarize them in focus mode when available
- switch between dark mode and light mode
- save useful UI settings locally on the device

## Core Capabilities

### 1. Song Library

- Search by title, artist, or tags
- Sort by title, artist, key, or recent changes
- Filter the library by song files or PDFs
- Open songs in a performance-oriented viewer

### 2. Song Writing and Editing

- Create songs with chords placed above lyric lines
- Edit title, artist, key, capo, and tags
- Insert common section labels quickly from the editor toolbar
- Save songs into a local SQLite database

### 3. Smart Import and Local “In-App AI”

SongShelf includes an offline-first text cleanup pipeline. It is not a cloud AI service. It works locally and focuses on structure detection and chord cleanup.

It can:

- recognize chord lines placed above lyrics
- convert parenthesized chords like `(Am)` into inline chord tokens
- detect and normalize metadata like `Title:`, `Artist:`, `Key:`, `Capo:`, and `Tags:`
- convert section labels such as `Verse`, `Chorus`, or `Bridge` into cleaner output
- normalize spacing in messy text-based chord sheets

### 4. PDF Support

- Import `.pdf` files into the library
- Render PDF pages inside the app for viewing
- Open the original PDF if needed
- Detect chord-like content from PDF text when possible
- Show detected unique piano chords in focus mode for PDF songs

PDF chord detection depends on whether the PDF contains extractable text. Scanned image PDFs without selectable text may render correctly but provide limited or no chord detection.

### 5. Performance View

- Clean song viewer for lyrics and chords
- Inline transpose up/down controls
- Adjustable font size
- Auto-scroll for text songs
- Auto-scroll for PDF pages
- Focus mode for less distraction during rehearsal or live use

### 6. Focus Mode

Focus mode is optimized for practice and live performance.

It includes:

- a large centered song or PDF panel
- a side chord guide with unique piano chord diagrams
- a side metronome panel
- floating playback controls
- remembered focus preferences when enabled in settings

### 7. Metronome

- Start and stop controls
- Tempo slider and numeric BPM input
- Time signature selector
- Beat indicators
- Tempo marking display such as `Andante`, `Allegro`, and similar terms
- Shared state between sidebar metronome and focus-mode metronome

### 8. Chord Diagrams

The built-in chord browser currently focuses on piano chord diagrams.

Supported qualities include:

- major
- minor
- dominant 7
- major 7
- minor 7
- sus4
- sus2
- diminished
- augmented
- add9

### 9. Setlists

- Create named setlists
- Add notes to a setlist
- Select songs from card or list view
- Reorder songs using drag and drop
- Open and manage a setlist as a performance sequence

### 10. Device-Level Preferences

The app stores settings locally in the browser on that device, including:

- theme
- default song font size
- song auto-scroll speed
- PDF auto-scroll speed
- remembered focus mode
- helper panel visibility
- PDF night mode
- library default view

## Technology Stack

- PHP
- SQLite
- Vanilla JavaScript
- Custom CSS
- Local filesystem storage
- `pdftotext` and `pdftoppm` for PDF support
- Python 3 for PDF chord-text conversion helper

## Project Structure

```text
songbook-lite/
├── app/
│   ├── bootstrap.php
│   └── pdf_to_chordpro.py
├── assets/
│   ├── app.js
│   ├── style.css
│   ├── metronome-accent.mp3
│   └── metronome-beat.mp3
├── config/
│   └── schema.sql
├── data/
│   ├── songbook.sqlite
│   ├── uploads/
│   └── pdf-pages/
├── docs/
│   └── screenshots/
├── public/
│   ├── index.php
│   └── assets/
├── storage/
│   ├── imports/
│   ├── songshelf-server.log
│   └── songshelf-server.pid
├── views/
├── install_and_run.sh
├── install_kiosk_launcher.sh
└── start_kiosk.sh
```

## Requirements

### Minimum Runtime Requirements

- PHP 8.x recommended
- PHP SQLite extension (`pdo_sqlite`)
- SQLite
- A modern desktop browser

### For PDF Features

- Python 3
- `pdftotext`
- `pdftoppm`

### Linux Package Example

On Ubuntu or Debian-based systems:

```bash
sudo apt update
sudo apt install -y php php-sqlite3 poppler-utils python3
```

`poppler-utils` provides both `pdftotext` and `pdftoppm`.

## Quick Start

From inside the `songbook-lite` folder:

```bash
bash install_and_run.sh
```

Then open:

```text
http://127.0.0.1:8080
```

The startup script will:

- verify PHP is installed
- verify the SQLite PHP extension is enabled
- create required folders
- link local assets into `public/assets`
- initialize the SQLite database automatically
- start PHP’s built-in development server on port `8080`

## Manual Setup

If you prefer to start it manually:

### 1. Enter the project folder

```bash
cd songbook-lite
```

### 2. Make sure required folders exist

```bash
mkdir -p data storage/imports public/assets
```

### 3. Link the frontend assets

```bash
ln -sfn ../../assets/style.css public/assets/style.css
ln -sfn ../../assets/app.js public/assets/app.js
ln -sfn ../../assets/logo-songshelf.svg public/assets/logo-songshelf.svg
```

### 4. Initialize the database

```bash
php -r "require 'app/bootstrap.php'; db(); echo 'Database ready at ' . DB_PATH . PHP_EOL;"
```

### 5. Start the local server

```bash
php -S 127.0.0.1:8080 -t public
```

## Setup on Windows

There is no dedicated `.bat` launcher in this repo yet, but SongShelf runs fine on Windows with PHP installed.

### Option A: Use PHP directly

#### 1. Install dependencies

Install:

- PHP for Windows
- Python 3
- Poppler for Windows

Make sure these commands are available in `PATH`:

- `php`
- `python`
  or `python3`
- `pdftotext`
- `pdftoppm`

#### 2. Open PowerShell in the project folder

```powershell
cd path\to\songbook-lite
```

#### 3. Create the required folders

```powershell
mkdir data, storage, storage\imports, public\assets -Force
```

#### 4. Initialize the database

```powershell
php -r "require 'app/bootstrap.php'; db(); echo 'Database ready at ' . DB_PATH . PHP_EOL;"
```

#### 5. Start the server

```powershell
php -S 127.0.0.1:8080 -t public
```

#### 6. Open the app

In a browser, visit:

```text
http://127.0.0.1:8080
```

### Option B: Use XAMPP, Laragon, or another local PHP stack

If you already use a Windows PHP stack:

- point the document root to `songbook-lite/public`
- ensure SQLite is enabled
- make sure `pdftotext`, `pdftoppm`, and Python are available
- open the local URL in your browser

## How to Use SongShelf

### Add a Song

1. Open `Write Song`
2. Enter title, artist, key, capo, and tags
3. Type or paste your song with inline chords
4. Save the song

### Import Songs

1. Open `Import & Paste`
2. Upload supported files or paste raw song text
3. Let SongShelf normalize the content
4. Save and review the result

### Import a PDF

1. Open `Import & Paste`
2. Upload one or more PDF files
3. Open the imported PDF from the library
4. Use focus mode to view rendered pages
5. If chords are detectable in the PDF text, SongShelf will summarize unique chords in focus mode

### Build a Setlist

1. Open `Build Setlist`
2. Name the setlist
3. Select songs
4. Save
5. Open the setlist and drag songs into the final order

### Use Focus Mode

1. Open any song or PDF
2. Press the focus-mode button
3. Use the side panels for chord reference and metronome tools
4. Use auto-scroll and metronome controls during practice or live use

## Supported File Types

### Text-Based

- `.txt`
- `.pro`
- `.cho`
- `.chopro`
- `.onsong`
- `.crd`

### PDF

- `.pdf`

## Data and Storage

### Main Database

The database lives at:

```text
data/songbook.sqlite
```

### Uploaded PDFs

Stored in:

```text
data/uploads/
```

### Rendered PDF Pages and Analysis Cache

Stored in:

```text
data/pdf-pages/
```

### Temporary Runtime Files

Stored in:

```text
storage/
```

## Kiosk Mode on Linux

The repo already includes Linux kiosk helper scripts:

- `install_kiosk_launcher.sh`
- `start_kiosk.sh`

### Fast Linux Kiosk Install

From inside `songbook-lite`:

```bash
bash install_kiosk_launcher.sh
```

This installs a desktop launcher named `SongShelf Kiosk`.
It also adds a desktop shortcut when your Desktop folder is available, so you can launch with a click.

If you also want SongShelf to open automatically after login:

```bash
bash install_kiosk_launcher.sh --autostart
```

### What the Linux Kiosk Launcher Does

When launched, it will:

- ensure required folders exist
- link assets
- initialize the database if needed
- start the local PHP server in the background
- wait until the server is ready
- open SongShelf in kiosk mode using Chromium when available
- fall back to Chromium Browser, Google Chrome, Firefox, or `xdg-open`

### Step-by-Step Linux Kiosk Setup

#### 1. Install system packages

```bash
sudo apt update
sudo apt install -y php php-sqlite3 poppler-utils python3 chromium-browser
```

Depending on your distro, Chromium may be named `chromium` instead of `chromium-browser`.

#### 2. Open the app folder

```bash
cd /path/to/songbook-lite
```

#### 3. Install the kiosk launcher

```bash
bash install_kiosk_launcher.sh
```

#### 4. Launch SongShelf Kiosk

Use your desktop app launcher and search for:

```text
SongShelf Kiosk
```

Or run:

```bash
bash start_kiosk.sh
```

#### 5. Optional: Auto-start on login

Create an autostart desktop file:

```bash
mkdir -p ~/.config/autostart
cp ~/.local/share/applications/songshelf-kiosk.desktop ~/.config/autostart/
```

Now SongShelf Kiosk will start automatically when that user logs in.

### Recommended Linux Kiosk Tips

- Use a dedicated local user account for performance mode
- Disable screen sleep if this machine is stage-facing
- Use Chromium for the cleanest kiosk-style experience
- Keep the machine on a fixed local IP only if you plan to access it remotely from another device

## Kiosk Mode on Windows

Windows kiosk mode is not scripted in this repo yet, but it is very doable. Below is a practical step-by-step approach using PHP plus Microsoft Edge or Google Chrome.

### Windows Kiosk Goal

You want Windows to:

- start a local SongShelf server
- wait briefly for the server to become available
- open the app full-screen or kiosk-style
- optionally start that flow automatically when Windows logs in

### Step-by-Step Windows Kiosk Setup

#### 1. Install dependencies

Install all of the following:

- PHP
- Python 3
- Poppler for Windows
- Microsoft Edge or Google Chrome

Make sure these commands work in PowerShell:

```powershell
php -v
python --version
pdftotext -v
pdftoppm -v
```

#### 2. Put SongShelf in a stable folder

Example:

```text
C:\SongShelf\songbook-lite
```

Avoid putting a kiosk install inside a temporary Downloads path.

#### 3. Initialize SongShelf once

Open PowerShell in the app folder:

```powershell
cd C:\SongShelf\songbook-lite
mkdir data, storage, storage\imports, public\assets -Force
php -r "require 'app/bootstrap.php'; db(); echo 'Database ready at ' . DB_PATH . PHP_EOL;"
```

#### 4. Create a Windows startup script

Create a file called `start_kiosk_windows.bat` in the `songbook-lite` folder with this content:

```bat
@echo off
cd /d C:\SongShelf\songbook-lite

if not exist storage mkdir storage
if not exist storage\imports mkdir storage\imports
if not exist data mkdir data
if not exist public\assets mkdir public\assets

start "" /min php -S 127.0.0.1:8080 -t public
timeout /t 3 /nobreak >nul

start "" msedge.exe --kiosk http://127.0.0.1:8080 --edge-kiosk-type=fullscreen
```

If you prefer Chrome, use:

```bat
start "" chrome.exe --kiosk http://127.0.0.1:8080
```

#### 5. Test the kiosk script manually

Double-click:

```text
start_kiosk_windows.bat
```

You should see SongShelf open in a kiosk-style browser window.

#### 6. Make it start automatically on login

Press `Win + R`, then open:

```text
shell:startup
```

Place a shortcut to `start_kiosk_windows.bat` in that Startup folder.

Now SongShelf should launch automatically when that Windows user logs in.

#### 7. Optional: Use Windows Assigned Access

For a stricter kiosk setup:

1. Create a dedicated Windows local account
2. Configure automatic login for that account if appropriate
3. Use Windows kiosk features or Assigned Access
4. Launch Edge in kiosk mode pointing to `http://127.0.0.1:8080`

This is more locked down, but the simple startup-script approach is usually easier for personal or rehearsal use.

### Recommended Windows Kiosk Tips

- Use a dedicated browser profile for the kiosk machine
- Disable sleep or screen timeout if needed
- Disable browser restore prompts
- Pin PHP and Poppler tools in `PATH`
- Use a dedicated Windows user for performance mode

## Suggested Kiosk Hardware Setup

SongShelf works well on:

- an old laptop near a music stand
- a Windows mini PC connected to a monitor
- an Ubuntu mini PC connected to a touchscreen
- a rehearsal room machine with keyboard and mouse

For best results:

- use a 1080p or better display
- keep zoom at 100% unless you intentionally want larger UI
- use Chromium or Edge for kiosk-style full-screen launch

## Troubleshooting

### PHP says SQLite is missing

Install the SQLite extension for PHP.

On Ubuntu:

```bash
sudo apt install -y php-sqlite3
```

### PDFs render poorly or do not show pages

Make sure `pdftoppm` is installed.

On Ubuntu:

```bash
sudo apt install -y poppler-utils
```

### PDF chord summary does not appear

Possible causes:

- the PDF is image-only and has no extractable text
- the PDF text layout is too messy for chord detection
- the file contains lyrics only and no chord lines

The PDF may still render fine even when chord detection is limited.

### Auto-scroll or settings do not persist between devices

Settings are stored locally in the browser, not in the SQLite database. Another browser or another device will have its own settings state.

### The server is already running

If you are using kiosk scripts and want to stop the background server, remove the PID file or stop the PHP process manually.

The PID file is usually here:

```text
storage/songshelf-server.pid
```

## Security and Usage Notes

- SongShelf is designed for local or trusted-network use
- It does not include user authentication
- It is best treated as a personal app, rehearsal-room app, or single-team local tool
- If you expose it over a network, do so only behind your own security controls

## Why This App Is Useful

SongShelf is especially practical when you want:

- a personal digital songbook without cloud dependence
- a church or rehearsal song viewer on a local machine
- a simple kiosk-style stage-side reference system
- PDF charts and text songs in one interface
- chord-aware performance tools without a complex stack

## Nice Things Already Included

- local-first architecture
- fast startup
- responsive custom UI
- dark and light theme support
- focus mode optimized for performance use
- built-in metronome
- built-in piano chord reference browser
- local PDF chord detection and caching
- no framework build step required

## Future Ideas You May Want to Add

If you keep evolving the project, strong next additions could be:

- export and backup tools
- print-friendly setlists
- remote control for auto-scroll from another device
- chord search by root or quality
- per-song BPM and time signature presets
- richer PDF OCR fallback for scanned charts
- multi-user or shared-library support

## License / Usage

This repository appears to be intended for personal or project-specific use. Add your preferred license if you plan to distribute it publicly.

## Quick Command Summary

Run normally:

```bash
bash install_and_run.sh
```

Install Linux launcher:

```bash
bash install_kiosk_launcher.sh
```

Launch Linux kiosk:

```bash
bash start_kiosk.sh
```

Open app:

```text
http://127.0.0.1:8080
```
