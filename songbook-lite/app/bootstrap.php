<?php
declare(strict_types=1);

const APP_ROOT = __DIR__ . '/..';
const DB_PATH = APP_ROOT . '/data/songbook.sqlite';
const PDF_ANALYSIS_SCHEMA_VERSION = 2;

date_default_timezone_set('Asia/Manila');

function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    if (!is_dir(APP_ROOT . '/data')) {
        mkdir(APP_ROOT . '/data', 0777, true);
    }

    $needsInit = !file_exists(DB_PATH);
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA journal_mode = WAL');
    $pdo->exec('PRAGMA synchronous = NORMAL');
    $pdo->exec('PRAGMA temp_store = MEMORY');
    $pdo->exec('PRAGMA cache_size = -20000');

    $schema = file_get_contents(APP_ROOT . '/config/schema.sql');
    $pdo->exec($schema);
    migrateDatabase($pdo);

    if ($needsInit) {
        seedDatabase($pdo);
    }

    return $pdo;
}

function seedDatabase(PDO $pdo): void {
    $now = date('c');
    $body = <<<TXT
{title: Amazing Grace}
{artist: Traditional}
{key: G}
{capo: 0}
[ G ]Amazing grace how sweet the [ C ]sound
That [ G ]saved a wretch like [ D ]me
I [ G ]once was lost but [ C ]now am found
Was [ G ]blind but [ D ]now I [ G ]see
TXT;

    $stmt = $pdo->prepare('INSERT INTO songs (title, artist, key_name, capo, tags, source_format, body, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute(['Amazing Grace', 'Traditional', 'G', 0, 'sample,worship', 'chordpro', $body, $now, $now]);
}

function h(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

function view(string $template, array $data = []): void {
    extract($data, EXTR_SKIP);
    require APP_ROOT . '/views/layout.php';
}

function appTipsByTemplate(): array {
    return [
        'songs' => [
            'title' => 'Library Tips',
            'tips' => [
                'Use `Search` for title, artist, or tags before creating duplicates.',
                'Use `Recent` sort before rehearsal to surface newly edited songs.',
                'Filter to `PDFs` when you need original chart pages quickly.',
                'Use card view for scanning, list view for faster keyboard browsing.',
            ],
        ],
        'song-form' => [
            'title' => 'Song Editor Tips',
            'tips' => [
                'Write chords above lyrics for the cleanest editor and live-view results.',
                'Stacked chord/lyric lines are preserved during save and import.',
                'Leave key blank if unsure; SongShelf auto-detects from chord tokens.',
                'Use section directives like `{section: Verse}` to keep output structured.',
            ],
        ],
        'song-view' => [
            'title' => 'Viewer Tips',
            'tips' => [
                'Use transpose controls for live key adjustments without editing the song.',
                'Enable focus mode for cleaner projection and fewer distractions.',
                'Toggle metronome from the viewer to keep rehearsal tempo consistent.',
                'Use print view for a static copy when offline backup is needed.',
            ],
        ],
        'import' => [
            'title' => 'Import Tips',
            'tips' => [
                'Text files with clear section headings convert with better structure.',
                'Chord-only lines above lyrics are merged into inline chord output.',
                'Parenthesized chords like `(Am)` are normalized to `[Am]`.',
                'PDF imports keep original pages and are searchable in your library.',
            ],
        ],
        'setlists' => [
            'title' => 'Setlist Tips',
            'tips' => [
                'Create separate setlists per event to keep rehearsal context clean.',
                'Put key transitions early so transposition decisions happen sooner.',
                'Review tags while selecting songs to balance tempo and section flow.',
            ],
        ],
        'setlist-form' => [
            'title' => 'Setlist Builder Tips',
            'tips' => [
                'Use card mode for visual selection, list mode for dense scanning.',
                'Select songs first, then reorder in the setlist view for final flow.',
                'Keep notes short and actionable for the whole team.',
            ],
        ],
        'setlist-view' => [
            'title' => 'Setlist View Tips',
            'tips' => [
                'Use drag reorder after soundcheck to match live flow changes.',
                'Open songs from the setlist directly to keep transitions fast.',
                'Keep one backup song at the end for extended sets.',
            ],
        ],
        'settings' => [
            'title' => 'Settings Tips',
            'tips' => [
                'Use `Lite` performance on slower devices for smoother scrolling.',
                'Theme and color changes affect display only, not saved song data.',
                'Export JSON regularly before large library edits.',
                'Use backup downloads before system updates or migrations.',
            ],
        ],
        'chord-diagrams' => [
            'title' => 'Chord Diagram Tips',
            'tips' => [
                'Use this page as reference when validating parsed chord quality.',
                'Check enharmonic spellings before printing charts for your team.',
                'Transpose in song view, then compare resulting shapes here.',
            ],
        ],
        'ai-assistant' => [
            'title' => 'Assistant Tips',
            'tips' => [
                'Save reusable preferences so each cleanup starts with your defaults.',
                'Use preferred terms to keep section labels consistent across songs.',
                'Keep custom instructions concise for more predictable output.',
            ],
        ],
        'about-developer' => [
            'title' => 'Developer Profile Tips',
            'tips' => [
                'Use this page to keep your professional profile details up to date.',
                'Add your latest achievements and links for better collaborator context.',
                'Keep bio and about sections concise but specific for clarity.',
            ],
        ],
    ];
}

function appTipsForTemplate(string $template): array {
    $map = appTipsByTemplate();
    return $map[$template] ?? [
        'title' => 'Quick Tips',
        'tips' => [
            'Use the sidebar to move quickly between library, import, and settings.',
            'Theme and viewer choices are saved locally for your device.',
            'Keep tags consistent so filtering stays reliable across pages.',
        ],
    ];
}

function route(): string {
    return $_GET['action'] ?? 'songs';
}

function now(): string {
    return date('c');
}

function sanitizeSongBpm(mixed $value): int {
    return max(0, min(240, (int)$value));
}

function songAllowedTimeSignatures(): array {
    return ['2/4', '3/4', '4/4', '5/4', '6/8'];
}

function normalizeSongTimeSignature(?string $value): string {
    $signature = trim((string)$value);
    return in_array($signature, songAllowedTimeSignatures(), true) ? $signature : '4/4';
}

function sanitizeSongScrollSpeed(mixed $value, bool $isPdf): float {
    $numeric = is_numeric($value) ? (float)$value : 0.0;
    if (!is_finite($numeric) || $numeric <= 0) {
        return $isPdf ? 1.6 : 4.0;
    }

    if ($isPdf) {
        $clamped = max(0.2, min(8.0, $numeric));
        return round($clamped, 1);
    }

    $clamped = max(1.0, min(14.0, $numeric));
    return (float)round($clamped);
}

function migrateDatabase(PDO $pdo): void {
    $columns = $pdo->query('PRAGMA table_info(songs)')->fetchAll(PDO::FETCH_ASSOC);
    $known = array_column($columns, 'name');

    if (!in_array('file_path', $known, true)) {
        $pdo->exec("ALTER TABLE songs ADD COLUMN file_path TEXT DEFAULT ''");
    }

    if (!in_array('mime_type', $known, true)) {
        $pdo->exec("ALTER TABLE songs ADD COLUMN mime_type TEXT DEFAULT ''");
    }

    if (!in_array('notation_style', $known, true)) {
        $pdo->exec("ALTER TABLE songs ADD COLUMN notation_style TEXT DEFAULT 'chordpro'");
    }

    if (!in_array('audio_source_type', $known, true)) {
        $pdo->exec("ALTER TABLE songs ADD COLUMN audio_source_type TEXT DEFAULT ''");
    }

    if (!in_array('audio_url', $known, true)) {
        $pdo->exec("ALTER TABLE songs ADD COLUMN audio_url TEXT DEFAULT ''");
    }

    if (!in_array('audio_title', $known, true)) {
        $pdo->exec("ALTER TABLE songs ADD COLUMN audio_title TEXT DEFAULT ''");
    }

    if (!in_array('audio_file_path', $known, true)) {
        $pdo->exec("ALTER TABLE songs ADD COLUMN audio_file_path TEXT DEFAULT ''");
    }

    if (!in_array('audio_mime_type', $known, true)) {
        $pdo->exec("ALTER TABLE songs ADD COLUMN audio_mime_type TEXT DEFAULT ''");
    }

    if (!in_array('bpm', $known, true)) {
        $pdo->exec("ALTER TABLE songs ADD COLUMN bpm INTEGER DEFAULT 0");
    }

    if (!in_array('time_signature', $known, true)) {
        $pdo->exec("ALTER TABLE songs ADD COLUMN time_signature TEXT DEFAULT '4/4'");
    }

    if (!in_array('scroll_speed', $known, true)) {
        $pdo->exec("ALTER TABLE songs ADD COLUMN scroll_speed REAL DEFAULT 0");
    }

    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_songs_title ON songs(title COLLATE NOCASE)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_songs_artist ON songs(artist COLLATE NOCASE)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_songs_key_name ON songs(key_name COLLATE NOCASE)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_songs_updated_at ON songs(updated_at DESC)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_songs_source_format ON songs(source_format)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_setlists_updated_at ON setlists(updated_at DESC)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_setlist_songs_setlist_position ON setlist_songs(setlist_id, position)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS assistant_memory (
        id INTEGER PRIMARY KEY CHECK (id = 1),
        custom_instructions TEXT DEFAULT \'\',
        preferred_terms TEXT DEFAULT \'\',
        repertoire_notes TEXT DEFAULT \'\',
        updated_at TEXT NOT NULL
    )');
    $pdo->prepare('INSERT OR IGNORE INTO assistant_memory (id, custom_instructions, preferred_terms, repertoire_notes, updated_at) VALUES (1, \'\', \'\', \'\', ?)')->execute([now()]);
    $pdo->exec('CREATE TABLE IF NOT EXISTS assistant_training_examples (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        example_type TEXT NOT NULL DEFAULT \'pair\',
        input_text TEXT NOT NULL,
        output_text TEXT NOT NULL,
        label TEXT DEFAULT \'\',
        usage_count INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL
    )');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_assistant_training_type_created ON assistant_training_examples(example_type, created_at DESC)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS assistant_chat_messages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        role TEXT NOT NULL,
        message TEXT NOT NULL,
        meta_json TEXT DEFAULT \'\',
        created_at TEXT NOT NULL
    )');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_assistant_chat_created ON assistant_chat_messages(created_at DESC, id DESC)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS developer_photos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        category TEXT NOT NULL DEFAULT \'general\',
        title TEXT DEFAULT \'\',
        mime_type TEXT NOT NULL,
        blob_data BLOB NOT NULL,
        created_at TEXT NOT NULL
    )');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_developer_photos_category_created ON developer_photos(category, created_at DESC)');
}

function uploadDir(): string {
    $dir = APP_ROOT . '/data/uploads';
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    return $dir;
}

function relativeDataPath(string $path): string {
    $prefix = APP_ROOT . '/data/';
    if (str_starts_with($path, $prefix)) {
        return substr($path, strlen($prefix));
    }
    return ltrim($path, '/');
}

function absoluteDataPath(string $path): string {
    return APP_ROOT . '/data/' . ltrim($path, '/');
}

function pdfRenderDir(int $songId): string {
    $dir = APP_ROOT . '/data/pdf-pages/' . $songId;
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    return $dir;
}

function songIsPdf(array $song): bool {
    return ($song['source_format'] ?? '') === 'pdf';
}

function songFileUrl(array $song): string {
    return '?action=song-file&id=' . (int)$song['id'];
}

function songHasAttachedPdf(array $song): bool {
    return (($song['mime_type'] ?? '') === 'application/pdf') && trim((string)($song['file_path'] ?? '')) !== '';
}

function audioUploadMimeTypes(): array {
    return [
        'audio/mpeg',
        'audio/mp3',
        'audio/wav',
        'audio/x-wav',
        'audio/wave',
        'audio/aac',
        'audio/mp4',
        'audio/x-m4a',
        'audio/ogg',
        'audio/webm',
        'audio/flac',
        'audio/x-flac',
    ];
}

function isAllowedAudioMimeType(string $mimeType): bool {
    return in_array(strtolower(trim($mimeType)), audioUploadMimeTypes(), true);
}

function songHasLocalAudio(array $song): bool {
    return trim((string)($song['audio_file_path'] ?? '')) !== '';
}

function songHasAttachedAudio(array $song): bool {
    return songHasLocalAudio($song) || trim((string)($song['audio_url'] ?? '')) !== '';
}

function songAudioFileUrl(array $song): string {
    return '?action=song-audio-file&id=' . (int)($song['id'] ?? 0);
}

function songSearchQuery(array $song): string {
    return trim(implode(' ', array_filter([
        trim((string)($song['title'] ?? '')),
        trim((string)($song['artist'] ?? '')),
    ])));
}

function songYoutubeSearchUrl(array $song): string {
    return 'https://www.youtube.com/results?search_query=' . rawurlencode(songSearchQuery($song));
}

function songSpotifySearchUrl(array $song): string {
    return 'https://open.spotify.com/search/' . rawurlencode(songSearchQuery($song));
}

function developerPhotoAllowedMimeTypes(): array {
    return [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/webp',
        'image/gif',
    ];
}

function isAllowedDeveloperPhotoMimeType(string $mimeType): bool {
    return in_array(strtolower(trim($mimeType)), developerPhotoAllowedMimeTypes(), true);
}

function parseYouTubeVideoId(string $url): string {
    $url = trim($url);
    if ($url === '') {
        return '';
    }

    if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([A-Za-z0-9_-]{11})~', $url, $matches)) {
        return $matches[1];
    }

    return '';
}

function parseSpotifyEmbedUrl(string $url): string {
    $url = trim($url);
    if ($url === '') {
        return '';
    }

    if (preg_match('~https?://open\.spotify\.com/(track|album|playlist|episode)/([A-Za-z0-9]+)~', $url, $matches)) {
        return 'https://open.spotify.com/embed/' . $matches[1] . '/' . $matches[2];
    }

    return '';
}

function extractPdfText(string $pdfPath): string {
    $outputPath = tempnam(sys_get_temp_dir(), 'songshelf_pdf_');
    if ($outputPath === false) {
        return '';
    }

    $command = sprintf(
        'pdftotext -layout %s %s 2>/dev/null',
        escapeshellarg($pdfPath),
        escapeshellarg($outputPath)
    );
    exec($command, $unused, $code);
    if ($code !== 0 || !is_file($outputPath)) {
        @unlink($outputPath);
        return '';
    }

    $text = file_get_contents($outputPath);
    @unlink($outputPath);
    return $text === false ? '' : $text;
}

function clearDirectoryFiles(string $dir): void {
    foreach (glob($dir . '/*') ?: [] as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
}

function renderPdfPages(array $song): array {
    if (!songHasAttachedPdf($song)) {
        return [];
    }

    $pdfPath = absoluteDataPath((string)$song['file_path']);
    if (!is_file($pdfPath)) {
        return [];
    }

    $renderDir = pdfRenderDir((int)$song['id']);
    $stampPath = $renderDir . '/.stamp';
    $stamp = filesize($pdfPath) . ':' . filemtime($pdfPath);
    $cachedStamp = is_file($stampPath) ? trim((string)file_get_contents($stampPath)) : '';

    if ($cachedStamp !== $stamp) {
        clearDirectoryFiles($renderDir);
        $prefix = $renderDir . '/page';
        $command = sprintf(
            'pdftoppm -png -r 144 %s %s 2>/dev/null',
            escapeshellarg($pdfPath),
            escapeshellarg($prefix)
        );
        exec($command, $unused, $code);
        if ($code !== 0) {
            clearDirectoryFiles($renderDir);
            return [];
        }
        file_put_contents($stampPath, $stamp);
    }

    $pages = glob($renderDir . '/page-*.png') ?: [];
    natsort($pages);
    return array_values(array_map(static fn(string $path): string => basename($path), $pages));
}

function songPageImageUrl(array $song, int $page): string {
    return '?action=song-page-image&id=' . (int)$song['id'] . '&page=' . $page;
}

function convertPdfToSong(string $pdfPath, string $fallbackTitle): array {
    $command = sprintf(
        'python3 %s --pdf %s --title %s',
        escapeshellarg(APP_ROOT . '/app/pdf_to_chordpro.py'),
        escapeshellarg($pdfPath),
        escapeshellarg($fallbackTitle)
    );

    exec($command . ' 2>/dev/null', $output, $code);
    if ($code !== 0) {
        return [
            'body' => '',
            'title' => $fallbackTitle,
            'raw_text' => extractPdfText($pdfPath),
            'chord_line_pairs' => 0,
            'chords_detected' => 0,
            'line_count' => 0,
            'notation_detected' => false,
            'notation_signal' => 0,
        ];
    }

    $json = implode("\n", $output);
    $decoded = json_decode($json, true);
    if (!is_array($decoded)) {
        return [
            'body' => '',
            'title' => $fallbackTitle,
            'raw_text' => extractPdfText($pdfPath),
            'chord_line_pairs' => 0,
            'chords_detected' => 0,
            'line_count' => 0,
            'notation_detected' => false,
            'notation_signal' => 0,
        ];
    }

    return [
        'body' => (string)($decoded['body'] ?? ''),
        'title' => (string)($decoded['title'] ?? $fallbackTitle),
        'raw_text' => (string)($decoded['raw_text'] ?? ''),
        'chord_line_pairs' => (int)($decoded['chord_line_pairs'] ?? 0),
        'chords_detected' => (int)($decoded['chords_detected'] ?? 0),
        'line_count' => (int)($decoded['line_count'] ?? 0),
        'notation_detected' => !empty($decoded['notation_detected']),
        'notation_signal' => (int)($decoded['notation_signal'] ?? 0),
    ];
}

function normalizeSongBody(string $text, bool $preserveLayout = false): string {
    $text = str_replace(["\r\n", "\r"], "\n", trim($text));
    $text = str_replace("\u{00A0}", ' ', $text);
    $text = preg_replace("/\t+/", '    ', $text) ?? $text;
    $text = preg_replace("/[ ]{2,}$/m", '', $text) ?? $text;
    $text = convertPlainMetadataToDirectives($text);
    $text = convertSectionHeadingsToDirectives($text);
    $text = normalizeInlineChordSpacing($text);

    if (!$preserveLayout) {
        $text = convertChordLinesToInline($text);
        $text = convertLooseInlineChords($text);
        $text = convertBareInlineChords($text);
        $text = collapseAdjacentDuplicateLyricLines($text);
    }

    $text = normalizeInlineChordSpacing($text);
    $text = prependDetectedTitleDirective($text);
    return preg_replace("/\n{3,}/", "\n\n", trim($text)) ?? trim($text);
}

function normalizeSongBodyForDisplay(string $text): string {
    $text = str_replace(["\r\n", "\r"], "\n", trim($text));
    $text = str_replace("\u{00A0}", ' ', $text);
    $text = normalizeInlineChordSpacing($text);
    $text = convertChordLinesToInline($text);
    $text = convertLooseInlineChords($text);
    $text = convertBareInlineChords($text);
    $text = collapseAdjacentDuplicateLyricLines($text);
    $text = normalizeInlineChordSpacing($text);
    return preg_replace("/\n{3,}/", "\n\n", trim($text)) ?? trim($text);
}

function inferNotationStyle(string $text): string {
    $lines = preg_split('/\R/', $text) ?: [];
    foreach ($lines as $index => $line) {
        $next = $lines[$index + 1] ?? '';
        if (isChordOnlyLine($line) && trim($next) !== '' && !isChordOnlyLine($next) && !isDirectiveLine(trim($next))) {
            return 'chords_over_lyrics';
        }
    }

    return 'chordpro';
}

function convertChordLinesToInline(string $text): string {
    $lines = preg_split('/\n/', $text) ?: [];
    $converted = [];

    for ($i = 0, $count = count($lines); $i < $count; $i++) {
        $line = rtrim($lines[$i]);
        $next = $lines[$i + 1] ?? null;

        if ($next !== null && isChordOnlyLine($line) && !isChordOnlyLine($next) && trim($next) !== '' && !isDirectiveLine($next)) {
            $converted[] = mergeChordAndLyricLines($line, rtrim($next));
            $i++;
            continue;
        }

        $converted[] = $line;
    }

    return implode("\n", $converted);
}

function convertPlainMetadataToDirectives(string $text): string {
    $lines = preg_split('/\n/', $text) ?: [];
    $converted = [];

    foreach ($lines as $line) {
        $trim = trim($line);
        if ($trim === '' || isDirectiveLine($trim)) {
            $converted[] = $line;
            continue;
        }

        if (preg_match('/^(title|artist|key|capo|tags?)\s*:\s*(.+)$/i', $trim, $matches)) {
            $label = strtolower($matches[1]);
            $value = trim($matches[2]);
            if ($value !== '') {
                if ($label === 'tag') {
                    $label = 'tags';
                }
                $converted[] = '{' . $label . ': ' . $value . '}';
                continue;
            }
        }

        $converted[] = $line;
    }

    return implode("\n", $converted);
}

function convertSectionHeadingsToDirectives(string $text): string {
    $lines = preg_split('/\n/', $text) ?: [];
    $converted = [];

    foreach ($lines as $line) {
        $trim = trim($line);
        if ($trim !== '' && !isDirectiveLine($trim) && isSectionHeading($trim)) {
            $converted[] = '{section: ' . trim($trim, "[]() \t") . '}';
            continue;
        }
        $converted[] = $line;
    }

    return implode("\n", $converted);
}

function normalizeInlineChordSpacing(string $text): string {
    $text = preg_replace_callback('/\[\[\s*([^\]\r\n]+?)\s*\]\]/', static function (array $matches): string {
        $chord = trim($matches[1]);
        return isChordToken($chord) ? '[' . $chord . ']' : $matches[0];
    }, $text) ?? $text;

    return preg_replace_callback('/\[\s*([^\]\r\n]+?)\s*\]/', static function (array $matches): string {
        $chord = trim($matches[1]);
        return isChordToken($chord) ? '[' . $chord . ']' : $matches[0];
    }, $text) ?? $text;
}

function convertLooseInlineChords(string $text): string {
    $lines = preg_split('/\n/', $text) ?: [];
    $converted = [];

    foreach ($lines as $line) {
        $trim = trim($line);
        if (
            $trim === ''
            || isDirectiveLine($trim)
            || isSectionHeading($trim)
            || str_contains($line, '[')
            || isChordOnlyLine($trim)
        ) {
            $converted[] = $line;
            continue;
        }

        $converted[] = preg_replace_callback(
            '/(?<!\w)\(([A-G](?:#|b)?(?:maj|min|m|sus|dim|aug|add|no|omit|M)?[0-9A-Za-z#b\/()+-]*)\)/',
            static fn(array $matches): string => isChordToken($matches[1]) ? '[' . $matches[1] . ']' : $matches[0],
            $line
        ) ?? $line;
    }

    return implode("\n", $converted);
}

function extractBareChordCandidatesFromLine(string $line): array {
    preg_match_all(
        '/(^|[\s\-–—,;:])([A-G](?:#|b)?(?:maj|min|m|sus|dim|aug|add|no|omit|M)?[0-9A-Za-z#b\/()+-]*)(?=$|[\s\-–—,;:!?.,])/u',
        $line,
        $matches,
        PREG_SET_ORDER | PREG_OFFSET_CAPTURE
    );

    $candidates = [];
    foreach ($matches as $match) {
        $prefix = $match[1][0] ?? '';
        $prefixOffset = (int)($match[1][1] ?? 0);
        $token = trim((string)($match[2][0] ?? ''));
        $tokenOffset = (int)($match[2][1] ?? ($prefixOffset + strlen($prefix)));

        if ($token === '' || !isLikelyBareInlineChordToken($token)) {
            continue;
        }

        $candidates[] = [
            'prefix' => $prefix,
            'prefix_offset' => $prefixOffset,
            'token' => $token,
            'token_offset' => $tokenOffset,
        ];
    }

    return $candidates;
}

function isLikelyBareInlineChordToken(string $token): bool {
    $clean = trim($token);
    if (!isChordToken($clean)) {
        return false;
    }

    return (bool)preg_match(
        '/^(?:N\.?C\.?|[A-G](?:#|b)?(?:maj(?:7|9|11|13)?|min(?:7|9|11|13)?|m(?:aj7|6|7|9|11|13)?|sus(?:2|4)?|dim(?:7)?|aug|\+|add\d+|no\d+|omit\d+|M(?:7|9|11|13)?|2|4|5|6|7|9|11|13)*(?:\([^)]+\))?(?:\/[A-G](?:#|b)?)?)$/i',
        $clean
    );
}

function convertBareInlineChords(string $text): string {
    $lines = preg_split('/\n/', $text) ?: [];
    $converted = [];

    foreach ($lines as $line) {
        $trim = trim($line);
        if (
            $trim === ''
            || isDirectiveLine($trim)
            || isSectionHeading($trim)
            || isChordOnlyLine($trim)
        ) {
            $converted[] = $line;
            continue;
        }

        $candidates = extractBareChordCandidatesFromLine($line);
        $hasBracketedChord = preg_match('/\[[^\]]+\]/', $line) === 1;
        $hasStrongChordEvidence = $hasBracketedChord || count($candidates) >= 2;

        if (!$hasStrongChordEvidence || $candidates === []) {
            $converted[] = $line;
            continue;
        }

        $rewritten = $line;
        usort($candidates, static fn(array $a, array $b): int => $b['token_offset'] <=> $a['token_offset']);

        foreach ($candidates as $candidate) {
            $token = $candidate['token'];
            if ($token === 'A' && count($candidates) < 3) {
                continue;
            }

            $offset = $candidate['token_offset'];
            $before = $offset > 0 ? $rewritten[$offset - 1] : '';
            $afterIndex = $offset + strlen($token);
            $after = $afterIndex < strlen($rewritten) ? $rewritten[$afterIndex] : '';

            if ($before === '[' || $after === ']') {
                continue;
            }

            $rewritten = substr($rewritten, 0, $offset) . '[' . $token . ']' . substr($rewritten, $afterIndex);
        }

        $converted[] = $rewritten;
    }

    return implode("\n", $converted);
}

function prependDetectedTitleDirective(string $text): string {
    $lines = preg_split('/\n/', $text) ?: [];
    $firstMeaningfulIndex = null;
    $meaningful = [];

    foreach ($lines as $index => $line) {
        if (trim($line) === '') {
            continue;
        }
        if ($firstMeaningfulIndex === null) {
            $firstMeaningfulIndex = $index;
        }
        $meaningful[] = trim($line);
    }

    if ($firstMeaningfulIndex === null || hasDirectiveValue($lines, 'title')) {
        return $text;
    }

    $candidate = $meaningful[0] ?? '';
    if (
        $candidate === ''
        || isDirectiveLine($candidate)
        || isChordOnlyLine($candidate)
        || isSectionHeading($candidate)
        || preg_match('/^\[[^\]]+\]/', $candidate)
        || strlen($candidate) > 80
    ) {
        return $text;
    }

    $hasChordEvidence = false;
    foreach ($meaningful as $line) {
        if (str_contains($line, '[') || isChordOnlyLine($line)) {
            $hasChordEvidence = true;
            break;
        }
    }

    if (!$hasChordEvidence) {
        return $text;
    }

    unset($lines[$firstMeaningfulIndex]);
    array_unshift($lines, '{title: ' . $candidate . '}');
    return implode("\n", array_values($lines));
}

function isDirectiveLine(string $line): bool {
    return (bool)preg_match('/^\{[^}]+\}$/', trim($line));
}

function isSectionHeading(string $line): bool {
    $value = trim($line, "[]() \t");
    return (bool)preg_match('/^(intro|verse(?:\s+\d+)?|chorus|refrain|bridge|coda|pre-chorus|post-chorus|tag|outro|ending|instrumental)(?:[:\s-].*)?$/i', $value);
}

function isChordToken(string $token): bool {
    $clean = normalizeChordTokenText($token);
    if ($clean === '' || in_array($clean, ['|', '||', '|||', '/', '//', '///'], true)) {
        return false;
    }

    return (bool)preg_match(
        '/^(?:N\.?C\.?|[A-G](?:#|b)?(?:maj(?:7|9|11|13)?|M(?:7|9|11|13)?|m(?:aj7|6|7|9|11|13)?|min(?:7|9|11|13)?|sus(?:2|4)?|dim(?:7)?|aug|\+|add\d+|no\d+|omit\d+|2|4|5|6|7|9|11|13)?(?:\([^)]+\))?(?:\/[A-G](?:#|b)?)?)$/i',
        $clean
    );
}

function normalizeChordTokenText(string $token): string {
    $clean = trim($token);
    if ($clean === '') {
        return '';
    }

    if (preg_match('/^\[([^\]]+)\]$/', $clean, $matches)) {
        $clean = trim((string)$matches[1]);
    }

    $clean = trim($clean, "[]{}<>()");
    $clean = rtrim($clean, ".,;:!?");
    return trim($clean);
}

function isChordOnlyLine(string $line): bool {
    $trim = trim($line);
    if ($trim === '' || isDirectiveLine($trim) || isSectionHeading($trim)) {
        return false;
    }
    $tokens = preg_split('/\s+/', $trim) ?: [];
    if (count($tokens) === 0) {
        return false;
    }
    $matches = 0;
    foreach ($tokens as $token) {
        if (isChordToken($token)) {
            $matches++;
        }
    }
    return $matches > 0 && ($matches / count($tokens)) >= 0.75;
}

function extractChordTokensFromText(string $text): array {
    $tokens = [];
    $lines = preg_split('/\R/', $text) ?: [];

    foreach ($lines as $line) {
        preg_match_all('/\[([^\]]+)\]/', $line, $bracketMatches);
        foreach (($bracketMatches[1] ?? []) as $token) {
            $tokens[] = trim((string)$token);
        }

        preg_match_all('/(?<!\w)\(([A-G](?:#|b)?(?:maj|min|m|sus|dim|aug|add|no|omit|M)?[0-9A-Za-z#b\/()+-]*)\)/', $line, $parenMatches);
        foreach (($parenMatches[1] ?? []) as $token) {
            $tokens[] = trim((string)$token);
        }

        foreach (extractBareChordCandidatesFromLine($line) as $candidate) {
            $tokens[] = $candidate['token'];
        }

        if (!isChordOnlyLine($line)) {
            continue;
        }

        $lineTokens = preg_split('/\s+/', trim($line)) ?: [];
        foreach ($lineTokens as $token) {
            if (isChordToken($token)) {
                $tokens[] = rtrim(trim($token, "[]{}<>() \t"), ".,;:!?");
            }
        }
    }

    return array_values(array_filter(array_map(static fn($token): string => trim((string)$token), $tokens), static fn($token): bool => $token !== ''));
}

function detectSongKey(string $text): string {
    $tokens = extractChordTokensFromText($text);
    if ($tokens === []) {
        return '';
    }

    $roots = [];
    foreach ($tokens as $token) {
        if (preg_match('/^([A-G](?:#|b)?)/', trim($token), $matches)) {
            $roots[] = normalizeNote($matches[1]);
        }
    }

    if ($roots === []) {
        return '';
    }

    $rootCounts = array_count_values($roots);
    $firstRoot = $roots[0] ?? '';
    $lastRoot = $roots[count($roots) - 1] ?? '';
    $bestKey = '';
    $bestScore = -1;

    foreach (chordLibraryRoots() as $candidate) {
        $majorScale = [
            $candidate,
            indexToNote(noteToIndex($candidate) + 2),
            indexToNote(noteToIndex($candidate) + 4),
            indexToNote(noteToIndex($candidate) + 5),
            indexToNote(noteToIndex($candidate) + 7),
            indexToNote(noteToIndex($candidate) + 9),
            indexToNote(noteToIndex($candidate) + 11),
        ];

        $score = 0;
        foreach ($rootCounts as $root => $count) {
            if (in_array($root, $majorScale, true)) {
                $score += $count;
            }
        }
        if ($firstRoot === $candidate) {
            $score += 3;
        }
        if ($lastRoot === $candidate) {
            $score += 4;
        }
        if (($rootCounts[$candidate] ?? 0) > 0) {
            $score += 2;
        }

        if ($score > $bestScore) {
            $bestScore = $score;
            $bestKey = $candidate;
        }
    }

    return $bestKey;
}

function mergeChordAndLyricLines(string $chordLine, string $lyricLine): string {
    preg_match_all('/\S+/', $chordLine, $matches, PREG_OFFSET_CAPTURE);
    $merged = $lyricLine;
    $lyricLength = strlen($lyricLine);
    $insertions = [];

    foreach ($matches[0] as $match) {
        $chord = normalizeChordTokenText((string)$match[0]);
        if (!isChordToken($chord)) {
            continue;
        }
        $pos = (int)$match[1];
        $insertions[] = ['pos' => $pos, 'chord' => $chord];
    }

    usort($insertions, static fn(array $a, array $b): int => $b['pos'] <=> $a['pos']);
    foreach ($insertions as $item) {
        $pos = min($item['pos'], $lyricLength);
        $merged = substr($merged, 0, $pos) . '[' . $item['chord'] . ']' . substr($merged, $pos);
    }

    return preg_replace('/\[(.*?)\]\s+/', '[$1]', $merged) ?? $merged;
}

function collapseAdjacentDuplicateLyricLines(string $text): string {
    $lines = preg_split('/\n/', $text) ?: [];
    $collapsed = [];

    foreach ($lines as $line) {
        $current = rtrim($line);
        $previousIndex = count($collapsed) - 1;
        $previous = $previousIndex >= 0 ? $collapsed[$previousIndex] : null;

        if ($previous !== null && shouldCollapseDuplicateLyricLine($previous, $current)) {
            if (lineChordDensity($current) > lineChordDensity($previous)) {
                $collapsed[$previousIndex] = $current;
            }
            continue;
        }

        $collapsed[] = $current;
    }

    return implode("\n", $collapsed);
}

function shouldCollapseDuplicateLyricLine(string $previous, string $current): bool {
    $prevTrim = trim($previous);
    $currentTrim = trim($current);
    if ($prevTrim === '' || $currentTrim === '') {
        return false;
    }
    if (isDirectiveLine($prevTrim) || isDirectiveLine($currentTrim) || isSectionHeading($prevTrim) || isSectionHeading($currentTrim)) {
        return false;
    }

    $prevComparable = comparableLyricLine($previous);
    $currentComparable = comparableLyricLine($current);
    if ($prevComparable === '' || $currentComparable === '' || $prevComparable !== $currentComparable) {
        return false;
    }

    return str_contains($previous, '[') || str_contains($current, '[');
}

function comparableLyricLine(string $line): string {
    $withoutChords = preg_replace('/\[[^\]]+\]/', ' ', $line) ?? $line;
    $normalized = preg_replace('/\s+/', ' ', trim($withoutChords)) ?? trim($withoutChords);
    return mb_strtolower($normalized);
}

function lineChordDensity(string $line): int {
    preg_match_all('/\[[^\]]+\]/', $line, $matches);
    return count($matches[0]);
}

function parseMetadataFromBody(string $body): array {
    $meta = ['title' => '', 'artist' => '', 'key_name' => '', 'capo' => 0, 'tags' => ''];
    foreach (preg_split('/\n/', $body) as $line) {
        if (preg_match('/^\{\s*title\s*:\s*(.+?)\s*\}$/i', trim($line), $m)) {
            $meta['title'] = trim($m[1]);
        }
        if (preg_match('/^\{\s*artist\s*:\s*(.+?)\s*\}$/i', trim($line), $m)) {
            $meta['artist'] = trim($m[1]);
        }
        if (preg_match('/^\{\s*key\s*:\s*(.+?)\s*\}$/i', trim($line), $m)) {
            $meta['key_name'] = trim($m[1]);
        }
        if (preg_match('/^\{\s*capo\s*:\s*(\d+)\s*\}$/i', trim($line), $m)) {
            $meta['capo'] = (int)$m[1];
        }
        if (preg_match('/^\{\s*(?:tag|tags)\s*:\s*(.+?)\s*\}$/i', trim($line), $m)) {
            $meta['tags'] = trim($m[1]);
        }
    }
    return $meta;
}

function hasDirectiveValue(array $lines, string $name): bool {
    foreach ($lines as $line) {
        if (preg_match('/^\{\s*' . preg_quote($name, '/') . '\s*:\s*(.+?)\s*\}$/i', trim($line), $matches)) {
            if (trim($matches[1]) !== '') {
                return true;
            }
        }
    }
    return false;
}

function getAssistantMemory(): array {
    $stmt = db()->query('SELECT * FROM assistant_memory WHERE id = 1 LIMIT 1');
    $memory = $stmt->fetch(PDO::FETCH_ASSOC);
    return $memory ?: [
        'id' => 1,
        'custom_instructions' => '',
        'preferred_terms' => '',
        'repertoire_notes' => '',
        'updated_at' => now(),
    ];
}

function saveAssistantMemory(string $customInstructions, string $preferredTerms, string $repertoireNotes): void {
    $stmt = db()->prepare('UPDATE assistant_memory SET custom_instructions = ?, preferred_terms = ?, repertoire_notes = ?, updated_at = ? WHERE id = 1');
    $stmt->execute([
        trim($customInstructions),
        trim($preferredTerms),
        trim($repertoireNotes),
        now(),
    ]);
}

function addAssistantTrainingExample(string $inputText, string $outputText, string $label = '', string $exampleType = 'pair'): void {
    $type = in_array($exampleType, ['pair', 'replacement'], true) ? $exampleType : 'pair';
    $stmt = db()->prepare('INSERT INTO assistant_training_examples (example_type, input_text, output_text, label, usage_count, created_at) VALUES (?, ?, ?, ?, 0, ?)');
    $stmt->execute([
        $type,
        trim($inputText),
        trim($outputText),
        trim($label),
        now(),
    ]);
}

function addAssistantChatMessage(string $role, string $message, array $meta = []): void {
    $safeRole = in_array($role, ['user', 'assistant'], true) ? $role : 'assistant';
    $content = trim($message);
    if ($content === '') {
        return;
    }

    $metaJson = json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($metaJson === false) {
        $metaJson = '{}';
    }

    $stmt = db()->prepare('INSERT INTO assistant_chat_messages (role, message, meta_json, created_at) VALUES (?, ?, ?, ?)');
    $stmt->execute([$safeRole, $content, $metaJson, now()]);
}

function getAssistantChatMessages(int $limit = 40): array {
    $safeLimit = max(1, min(200, $limit));
    $stmt = db()->prepare('SELECT id, role, message, meta_json, created_at FROM assistant_chat_messages ORDER BY id DESC LIMIT ?');
    $stmt->bindValue(1, $safeLimit, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    $rows = array_reverse($rows);

    return array_map(static function (array $row): array {
        $meta = [];
        $decoded = json_decode((string)($row['meta_json'] ?? ''), true);
        if (is_array($decoded)) {
            $meta = $decoded;
        }

        return [
            'id' => (int)($row['id'] ?? 0),
            'role' => (string)($row['role'] ?? 'assistant'),
            'message' => (string)($row['message'] ?? ''),
            'meta' => $meta,
            'created_at' => (string)($row['created_at'] ?? ''),
        ];
    }, $rows);
}

function clearAssistantChatMessages(): void {
    db()->exec('DELETE FROM assistant_chat_messages');
}

function getAssistantTrainingExamples(int $limit = 12): array {
    $safeLimit = max(1, min(100, $limit));
    $stmt = db()->prepare('SELECT * FROM assistant_training_examples ORDER BY created_at DESC, id DESC LIMIT ?');
    $stmt->bindValue(1, $safeLimit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function assistantTokenizeText(string $text): array {
    preg_match_all('/[A-Za-z0-9#\/\-\']{2,}/u', mb_strtolower($text), $matches);
    return array_values(array_unique($matches[0] ?? []));
}

function assistantBestTrainingMatch(string $text, string $exampleType = 'pair'): ?array {
    $inputTokens = assistantTokenizeText($text);
    if ($inputTokens === []) {
        return null;
    }

    $type = in_array($exampleType, ['pair', 'replacement'], true) ? $exampleType : 'pair';
    $stmt = db()->prepare('SELECT * FROM assistant_training_examples WHERE example_type = ? ORDER BY created_at DESC LIMIT 120');
    $stmt->execute([$type]);
    $examples = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $best = null;
    $bestScore = 0.0;
    foreach ($examples as $example) {
        $exampleTokens = assistantTokenizeText((string)($example['input_text'] ?? ''));
        if ($exampleTokens === []) {
            continue;
        }

        $intersection = array_intersect($inputTokens, $exampleTokens);
        $union = array_unique(array_merge($inputTokens, $exampleTokens));
        $score = count($union) > 0 ? count($intersection) / count($union) : 0.0;

        if ($score > $bestScore) {
            $bestScore = $score;
            $best = $example;
        }
    }

    if ($best === null || $bestScore < 0.25) {
        return null;
    }

    $best['match_score'] = $bestScore;
    return $best;
}

function assistantApplyLearnedReplacements(string $text): string {
    $stmt = db()->prepare('SELECT input_text, output_text FROM assistant_training_examples WHERE example_type = ? ORDER BY created_at DESC LIMIT 150');
    $stmt->execute(['replacement']);
    $examples = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    if ($examples === []) {
        return $text;
    }

    $result = $text;
    foreach ($examples as $example) {
        $from = trim((string)($example['input_text'] ?? ''));
        $to = trim((string)($example['output_text'] ?? ''));
        if ($from === '' || $to === '' || mb_strlen($from) > 120 || mb_strlen($to) > 120) {
            continue;
        }
        $result = str_replace($from, $to, $result);
    }

    return $result;
}

function assistantBuildHintsFromMemory(array $memory): array {
    $hints = [];
    $preferredTerms = trim((string)($memory['preferred_terms'] ?? ''));
    if ($preferredTerms !== '') {
        $hints[] = 'Preferred terms: ' . $preferredTerms;
    }

    $customInstructions = trim((string)($memory['custom_instructions'] ?? ''));
    if ($customInstructions !== '') {
        $hints[] = 'Style rule: ' . $customInstructions;
    }

    $repertoireNotes = trim((string)($memory['repertoire_notes'] ?? ''));
    if ($repertoireNotes !== '') {
        $hints[] = 'Team context: ' . $repertoireNotes;
    }

    return $hints;
}

function runLocalAssistant(string $input, string $task, array $memory, bool $useTraining = true): array {
    $cleanInput = str_replace(["\r\n", "\r"], "\n", trim($input));
    $taskKey = in_array($task, ['clean', 'detect_chords', 'infer_key', 'structure', 'coach'], true) ? $task : 'clean';
    $workingInput = $useTraining ? assistantApplyLearnedReplacements($cleanInput) : $cleanInput;
    $memoryHints = assistantBuildHintsFromMemory($memory);
    $matchedExample = $useTraining ? assistantBestTrainingMatch($workingInput, 'pair') : null;
    $output = $workingInput;
    $summary = 'No changes applied.';

    if ($taskKey === 'clean') {
        $output = normalizeSongBody($workingInput, true);
        $output = assistantApplyLearnedReplacements($output);
        $summary = 'Cleaned formatting, normalized metadata/sections, and preserved chords-over-lyrics structure.';
    } elseif ($taskKey === 'detect_chords') {
        $chords = array_values(array_unique(extractChordTokensFromText($workingInput)));
        $output = $chords === [] ? 'No chords detected.' : implode(', ', $chords);
        $summary = 'Detected unique chord tokens from the input.';
    } elseif ($taskKey === 'infer_key') {
        $detectedKey = detectSongKey($workingInput);
        $output = $detectedKey !== '' ? $detectedKey : 'Unable to infer key from current input.';
        $summary = 'Estimated key from detected chord roots.';
    } elseif ($taskKey === 'structure') {
        $output = convertSectionHeadingsToDirectives(convertPlainMetadataToDirectives($workingInput));
        $output = normalizeSongBody($output, true);
        $summary = 'Structured metadata and section headers into reusable directives.';
    } elseif ($taskKey === 'coach') {
        $key = detectSongKey($workingInput);
        $chords = array_values(array_unique(extractChordTokensFromText($workingInput)));
        $coachLines = [];
        $coachLines[] = 'Detected key: ' . ($key !== '' ? $key : 'Unknown');
        $coachLines[] = 'Chord count: ' . count($chords);
        if ($memoryHints !== []) {
            $coachLines[] = '';
            $coachLines[] = 'Memory hints';
            foreach ($memoryHints as $hint) {
                $coachLines[] = '- ' . $hint;
            }
        }
        $output = implode("\n", $coachLines);
        $summary = 'Generated rehearsal coaching hints from song content plus saved memory.';
    }

    if (is_array($matchedExample) && (float)($matchedExample['match_score'] ?? 0) >= 0.55 && trim((string)($matchedExample['output_text'] ?? '')) !== '') {
        $output = trim((string)$matchedExample['output_text']);
        $summary .= ' Applied a strong learned example match.';
        $stmt = db()->prepare('UPDATE assistant_training_examples SET usage_count = usage_count + 1 WHERE id = ?');
        $stmt->execute([(int)$matchedExample['id']]);
    }

    return [
        'task' => $taskKey,
        'input' => $cleanInput,
        'output' => $output,
        'summary' => $summary,
        'detected_key' => detectSongKey($output),
        'detected_chords' => array_values(array_unique(extractChordTokensFromText($output))),
        'memory_hints' => $memoryHints,
        'matched_example' => $matchedExample,
    ];
}

function appExportPackage(PDO $pdo): array {
    $songs = $pdo->query('SELECT * FROM songs ORDER BY title COLLATE NOCASE')->fetchAll(PDO::FETCH_ASSOC);
    $setlists = $pdo->query('SELECT * FROM setlists ORDER BY updated_at DESC')->fetchAll(PDO::FETCH_ASSOC);
    $setlistSongs = $pdo->query('SELECT * FROM setlist_songs ORDER BY setlist_id, position')->fetchAll(PDO::FETCH_ASSOC);

    return [
        'app' => 'SongShelf',
        'version' => '2.2',
        'exported_at' => now(),
        'songs' => $songs,
        'setlists' => $setlists,
        'setlist_songs' => $setlistSongs,
        'assistant_memory' => getAssistantMemory(),
        'assistant_training_examples' => getAssistantTrainingExamples(250),
    ];
}

function addDirectoryToZip(ZipArchive $zip, string $directory, string $prefix = ''): void {
    if (!is_dir($directory)) {
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        $pathName = $item->getPathname();
        $relativePath = ltrim($prefix . '/' . substr($pathName, strlen($directory)), '/');

        if ($item->isDir()) {
            $zip->addEmptyDir($relativePath);
            continue;
        }

        $zip->addFile($pathName, $relativePath);
    }
}

function semitoneMap(): array {
    return ['C', 'C#', 'D', 'Eb', 'E', 'F', 'F#', 'G', 'Ab', 'A', 'Bb', 'B'];
}

function semitoneMapSharps(): array {
    return ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'];
}

function semitoneMapFlats(): array {
    return ['C', 'Db', 'D', 'Eb', 'E', 'F', 'Gb', 'G', 'Ab', 'A', 'Bb', 'B'];
}

function normalizeNote(string $note): string {
    $map = [
        'DB' => 'C#', 'D♭' => 'C#',
        'D#' => 'Eb', 'EB' => 'Eb', 'E♭' => 'Eb',
        'FB' => 'E',
        'E#' => 'F',
        'GB' => 'F#', 'G♭' => 'F#',
        'G#' => 'Ab', 'A♭' => 'Ab',
        'AB' => 'Ab',
        'A#' => 'Bb', 'B♭' => 'Bb', 'BB' => 'Bb',
        'CB' => 'B',
        'B#' => 'C'
    ];
    $u = strtoupper(str_replace(['♯', '♭'], ['#', 'b'], trim($note)));
    return $map[$u] ?? ucfirst(strtolower($u));
}

function transposeChord(string $chord, int $steps): string {
    if ($steps === 0) {
        return $chord;
    }
    if (!preg_match('/^([A-G](?:#|b)?)(.*)$/', $chord, $m)) {
        return $chord;
    }
    $notes = semitoneMap();
    $rootToken = (string)$m[1];
    $root = normalizeNote($rootToken);
    $suffix = $m[2] ?? '';
    $idx = array_search($root, $notes, true);
    if ($idx === false) {
        return $chord;
    }
    $preferSharps = str_contains($rootToken, '#');
    $preferFlats = str_contains($rootToken, 'b');
    $targetScale = $preferFlats ? semitoneMapFlats() : ($preferSharps ? semitoneMapSharps() : semitoneMap());
    $newRoot = $targetScale[($idx + $steps % 12 + 12) % 12];
    $suffix = preg_replace_callback('/(?:\/)([A-G](?:#|b)?)/', function ($matches) use ($steps, $notes) {
        $bassToken = (string)$matches[1];
        $bass = normalizeNote($bassToken);
        $idx = array_search($bass, $notes, true);
        if ($idx === false) {
            return $matches[0];
        }
        $preferSharps = str_contains($bassToken, '#');
        $preferFlats = str_contains($bassToken, 'b');
        $targetScale = $preferFlats ? semitoneMapFlats() : ($preferSharps ? semitoneMapSharps() : semitoneMap());
        return '/' . $targetScale[($idx + $steps % 12 + 12) % 12];
    }, $suffix) ?? $suffix;
    return $newRoot . $suffix;
}

function chordLibraryRoots(): array {
    return ['C', 'C#', 'D', 'Eb', 'E', 'F', 'F#', 'G', 'Ab', 'A', 'Bb', 'B'];
}

function chordLibraryQualities(): array {
    return [
        'major' => [
            'suffix' => '',
            'label' => 'Major',
            'intervals' => [0, 4, 7],
            'guitar_shapes' => ['E', 'A'],
        ],
        'minor' => [
            'suffix' => 'm',
            'label' => 'Minor',
            'intervals' => [0, 3, 7],
            'guitar_shapes' => ['E', 'A'],
        ],
        'dominant7' => [
            'suffix' => '7',
            'label' => 'Dominant 7',
            'intervals' => [0, 4, 7, 10],
            'guitar_shapes' => ['E', 'A'],
        ],
        'major7' => [
            'suffix' => 'maj7',
            'label' => 'Major 7',
            'intervals' => [0, 4, 7, 11],
            'guitar_shapes' => ['E', 'A'],
        ],
        'minor7' => [
            'suffix' => 'm7',
            'label' => 'Minor 7',
            'intervals' => [0, 3, 7, 10],
            'guitar_shapes' => ['E', 'A'],
        ],
        'sus4' => [
            'suffix' => 'sus4',
            'label' => 'Suspended 4',
            'intervals' => [0, 5, 7],
            'guitar_shapes' => ['E', 'A'],
        ],
        'sus2' => [
            'suffix' => 'sus2',
            'label' => 'Suspended 2',
            'intervals' => [0, 2, 7],
            'guitar_shapes' => [],
        ],
        'diminished' => [
            'suffix' => 'dim',
            'label' => 'Diminished',
            'intervals' => [0, 3, 6],
            'guitar_shapes' => [],
        ],
        'augmented' => [
            'suffix' => 'aug',
            'label' => 'Augmented',
            'intervals' => [0, 4, 8],
            'guitar_shapes' => [],
        ],
        'add9' => [
            'suffix' => 'add9',
            'label' => 'Add 9',
            'intervals' => [0, 4, 7, 2],
            'guitar_shapes' => [],
        ],
    ];
}

function noteIndexMap(): array {
    static $map = null;
    if (is_array($map)) {
        return $map;
    }

    $map = [];
    foreach (semitoneMap() as $index => $note) {
        $map[$note] = $index;
    }

    return $map;
}

function noteToIndex(string $note): int {
    $normalized = normalizeNote($note);
    $map = noteIndexMap();
    return $map[$normalized] ?? 0;
}

function indexToNote(int $index): string {
    $notes = semitoneMap();
    return $notes[(($index % 12) + 12) % 12];
}

function chordPianoDiagram(string $root, array $quality): array {
    $rootIndex = noteToIndex($root);
    $notes = [];
    $active = [];

    foreach ($quality['intervals'] as $interval) {
        $noteIndex = ($rootIndex + $interval) % 12;
        $notes[] = indexToNote($noteIndex);
        $active[] = $noteIndex;
    }

    return [
        'root_index' => $rootIndex,
        'active_keys' => array_values(array_unique($active)),
        'notes' => $notes,
    ];
}

function guitarShapeRootFret(string $root, string $shape): int {
    $rootIndex = noteToIndex($root);
    if ($shape === 'E') {
        $openStringRoot = noteToIndex('E');
        return ($rootIndex - $openStringRoot + 12) % 12;
    }

    $openStringRoot = noteToIndex('A');
    return ($rootIndex - $openStringRoot + 12) % 12;
}

function guitarShapeTemplates(): array {
    return [
        'E' => [
            'major' => [0, 2, 2, 1, 0, 0],
            'minor' => [0, 2, 2, 0, 0, 0],
            'dominant7' => [0, 2, 0, 1, 0, 0],
            'major7' => [0, 2, 1, 1, 0, 0],
            'minor7' => [0, 2, 0, 0, 0, 0],
            'sus4' => [0, 2, 2, 2, 0, 0],
        ],
        'A' => [
            'major' => ['x', 0, 2, 2, 2, 0],
            'minor' => ['x', 0, 2, 2, 1, 0],
            'dominant7' => ['x', 0, 2, 0, 2, 0],
            'major7' => ['x', 0, 2, 1, 2, 0],
            'minor7' => ['x', 0, 2, 0, 1, 0],
            'sus4' => ['x', 0, 2, 2, 3, 0],
        ],
    ];
}

function chordGuitarDiagram(string $root, string $qualityKey, array $quality): array {
    $templates = guitarShapeTemplates();
    $best = null;

    foreach ($quality['guitar_shapes'] as $shape) {
        $rootFret = guitarShapeRootFret($root, $shape);
        $score = $rootFret === 0 ? 0 : $rootFret;
        if ($best === null || $score < $best['score']) {
            $best = [
                'shape' => $shape,
                'root_fret' => $rootFret,
                'template' => $templates[$shape][$qualityKey] ?? [],
                'score' => $score,
            ];
        }
    }

    $positions = [];
    foreach ($best['template'] as $position) {
        if ($position === 'x') {
            $positions[] = 'x';
            continue;
        }
        $positions[] = $best['root_fret'] + (int)$position;
    }

    $fretted = array_values(array_filter($positions, static fn($position): bool => is_int($position) && $position > 0));
    $lowestFretted = $fretted ? min($fretted) : 1;
    $hasOpenStrings = in_array(0, $positions, true);
    $baseFret = ($hasOpenStrings || $lowestFretted <= 4) ? 1 : $lowestFretted;

    return [
        'shape' => $best['shape'],
        'positions' => $positions,
        'base_fret' => max(1, $baseFret),
        'fret_count' => 5,
    ];
}

function chordLibrary(string $instrument = 'all'): array {
    $instrument = in_array($instrument, ['all', 'guitar', 'piano'], true) ? $instrument : 'all';
    $qualities = chordLibraryQualities();
    $items = [];

    foreach (chordLibraryRoots() as $root) {
        foreach ($qualities as $qualityKey => $quality) {
            $name = $root . $quality['suffix'];

            if (($instrument === 'all' || $instrument === 'guitar') && !empty($quality['guitar_shapes'])) {
                $items[] = [
                    'name' => $name,
                    'root' => $root,
                    'quality_key' => $qualityKey,
                    'quality_label' => $quality['label'],
                    'instrument' => 'guitar',
                    'diagram' => chordGuitarDiagram($root, $qualityKey, $quality),
                ];
            }

            if ($instrument === 'all' || $instrument === 'piano') {
                $items[] = [
                    'name' => $name,
                    'root' => $root,
                    'quality_key' => $qualityKey,
                    'quality_label' => $quality['label'],
                    'instrument' => 'piano',
                    'diagram' => chordPianoDiagram($root, $quality),
                ];
            }
        }
    }

    return $items;
}

function supportedPianoChordQuality(string $suffix): ?array {
    $normalized = trim($suffix);
    $normalized = preg_replace('/\/[a-g](?:#|b)?$/i', '', $normalized) ?? $normalized;
    $normalized = str_replace(['△', '°'], ['maj', 'dim'], $normalized);
    if ($normalized === '') {
        return ['quality_key' => 'major', 'display_suffix' => ''];
    }

    if (preg_match('/^M7$/', $normalized)) {
        return ['quality_key' => 'major7', 'display_suffix' => 'M7'];
    }
    if (preg_match('/^m7$/', $normalized)) {
        return ['quality_key' => 'minor7', 'display_suffix' => 'm7'];
    }
    if (preg_match('/^m$/', $normalized)) {
        return ['quality_key' => 'minor', 'display_suffix' => 'm'];
    }

    $lower = strtolower($normalized);
    $map = [
        'min' => ['quality_key' => 'minor', 'display_suffix' => 'm'],
        '7' => ['quality_key' => 'dominant7', 'display_suffix' => '7'],
        'maj7' => ['quality_key' => 'major7', 'display_suffix' => 'maj7'],
        'min7' => ['quality_key' => 'minor7', 'display_suffix' => 'm7'],
        'sus' => ['quality_key' => 'sus4', 'display_suffix' => 'sus4'],
        'sus4' => ['quality_key' => 'sus4', 'display_suffix' => 'sus4'],
        'sus2' => ['quality_key' => 'sus2', 'display_suffix' => 'sus2'],
        'dim' => ['quality_key' => 'diminished', 'display_suffix' => 'dim'],
        'diminished' => ['quality_key' => 'diminished', 'display_suffix' => 'dim'],
        'aug' => ['quality_key' => 'augmented', 'display_suffix' => 'aug'],
        '+' => ['quality_key' => 'augmented', 'display_suffix' => 'aug'],
        'augmented' => ['quality_key' => 'augmented', 'display_suffix' => 'aug'],
        'add9' => ['quality_key' => 'add9', 'display_suffix' => 'add9'],
    ];

    return $map[$lower] ?? null;
}

function pianoChordDiagramForToken(string $token): ?array {
    $clean = trim($token);
    if ($clean === '') {
        return null;
    }

    if (!preg_match('/^([A-G](?:#|b)?)(.*)$/', $clean, $matches)) {
        return null;
    }

    $root = normalizeNote($matches[1]);
    $qualityMeta = supportedPianoChordQuality($matches[2] ?? '');
    if ($qualityMeta === null) {
        return null;
    }

    $qualities = chordLibraryQualities();
    $quality = $qualities[$qualityMeta['quality_key']] ?? null;
    if ($quality === null) {
        return null;
    }

    $bass = '';
    if (preg_match('/\/([A-G](?:#|b)?)$/', $clean, $bassMatch)) {
        $bass = normalizeNote((string)$bassMatch[1]);
    }

    $baseName = $root . $qualityMeta['display_suffix'];
    $isInversion = $bass !== '' && $bass !== $root;

    return [
        'name' => $isInversion ? ($baseName . '/' . $bass) : $baseName,
        'root' => $root,
        'bass' => $bass,
        'is_inversion' => $isInversion,
        'quality_key' => $qualityMeta['quality_key'],
        'quality_label' => $isInversion ? ($quality['label'] . ' inversion') : $quality['label'],
        'diagram' => chordPianoDiagram($root, $quality),
    ];
}

function extractSongChordReferences(string $body, int $transpose = 0): array {
    return extractChordReferencesFromTokens(extractChordTokensFromText(normalizeSongBodyForDisplay($body)), $transpose);
}

function extractChordReferencesFromTokens(array $tokens, int $transpose = 0): array {
    $references = [];
    $seen = [];

    foreach ($tokens as $token) {
        $transposed = transposeChord(trim((string)$token), $transpose);
        $reference = pianoChordDiagramForToken($transposed);
        if ($reference === null) {
            continue;
        }

        $key = $reference['name'];
        if (isset($seen[$key])) {
            continue;
        }

        $seen[$key] = true;
        $references[] = $reference;
    }

    return $references;
}

function extractChordReferencesFromText(string $text, int $transpose = 0): array {
    return extractChordReferencesFromTokens(extractChordTokensFromText($text), $transpose);
}

function pdfChordAnalysisCache(array $song): array {
    if (!songIsPdf($song) || !songHasAttachedPdf($song)) {
        return [
            'body' => '',
            'raw_text' => '',
            'chord_line_pairs' => 0,
            'chords_detected' => 0,
            'line_count' => 0,
            'notation_detected' => false,
            'notation_signal' => 0,
        ];
    }

    $pdfPath = absoluteDataPath((string)$song['file_path']);
    if (!is_file($pdfPath)) {
        return [
            'body' => '',
            'raw_text' => '',
            'chord_line_pairs' => 0,
            'chords_detected' => 0,
            'line_count' => 0,
            'notation_detected' => false,
            'notation_signal' => 0,
        ];
    }

    $cacheDir = pdfRenderDir((int)$song['id']);
    $stamp = PDF_ANALYSIS_SCHEMA_VERSION . ':' . filesize($pdfPath) . ':' . filemtime($pdfPath);
    $stampPath = $cacheDir . '/.analysis-stamp';
    $cachePath = $cacheDir . '/analysis.json';
    $cachedStamp = is_file($stampPath) ? trim((string)file_get_contents($stampPath)) : '';

    if ($cachedStamp === $stamp && is_file($cachePath)) {
        $cached = json_decode((string)file_get_contents($cachePath), true);
        if (is_array($cached)) {
            return [
                'body' => (string)($cached['body'] ?? ''),
                'raw_text' => (string)($cached['raw_text'] ?? ''),
                'chord_line_pairs' => (int)($cached['chord_line_pairs'] ?? 0),
                'chords_detected' => (int)($cached['chords_detected'] ?? 0),
                'line_count' => (int)($cached['line_count'] ?? 0),
                'notation_detected' => !empty($cached['notation_detected']),
                'notation_signal' => (int)($cached['notation_signal'] ?? 0),
            ];
        }
    }

    $analysis = convertPdfToSong($pdfPath, (string)($song['title'] ?? 'Untitled Song'));
    file_put_contents($cachePath, json_encode($analysis, JSON_UNESCAPED_SLASHES));
    file_put_contents($stampPath, $stamp);

    return $analysis;
}

function pdfDisplayProfile(array $song): array {
    $analysis = pdfChordAnalysisCache($song);
    $hasNotation = !empty($analysis['notation_detected']);
    $hasChords = (int)($analysis['chords_detected'] ?? 0) > 0 || (int)($analysis['chord_line_pairs'] ?? 0) > 0;
    $rawText = trim((string)($analysis['raw_text'] ?? ''));
    $hasLyrics = preg_match_all('/\b[[:alpha:]][[:alpha:]\']{2,}\b/u', $rawText, $matches) >= 8;

    if ($hasNotation) {
        return [
            'mode' => 'notation',
            'label' => 'Notation',
            'summary' => 'Notation detected',
            'format' => 'Sheet notation PDF',
        ];
    }

    if ($hasChords) {
        return [
            'mode' => 'chords_lyrics',
            'label' => 'Chords & Lyrics',
            'summary' => 'Chords + lyrics detected',
            'format' => 'Chord chart PDF',
        ];
    }

    if ($hasLyrics) {
        return [
            'mode' => 'lyrics',
            'label' => 'Lyrics',
            'summary' => 'Lyrics detected',
            'format' => 'Lyrics PDF',
        ];
    }

    return [
        'mode' => 'notation',
        'label' => 'Notation',
        'summary' => 'Rendered document',
        'format' => 'Sheet notation PDF',
    ];
}

function extractPdfSongChordReferences(array $song, int $transpose = 0): array {
    $analysis = pdfChordAnalysisCache($song);
    $bodyReferences = extractSongChordReferences((string)($analysis['body'] ?? ''), $transpose);
    $textReferences = extractChordReferencesFromText((string)($analysis['raw_text'] ?? ''), $transpose);

    $merged = [];
    $seen = [];
    foreach (array_merge($bodyReferences, $textReferences) as $reference) {
        $key = (string)($reference['name'] ?? '');
        if ($key === '' || isset($seen[$key])) {
            continue;
        }
        $seen[$key] = true;
        $merged[] = $reference;
    }

    return $merged;
}

function renderSongHtml(string $body, int $transpose = 0, string $displayMode = 'over') : string {
    $body = normalizeSongBodyForDisplay($body);

    if ($displayMode === 'inline') {
        return renderSongInlineHtml($body, $transpose);
    }

    return renderSongChordOverLyricsHtml($body, $transpose);
}

function renderSongInlineHtml(string $body, int $transpose = 0): string {
    $lines = preg_split('/\n/', $body) ?: [];
    $html = '';

    foreach ($lines as $line) {
        $trim = trim($line);
        if ($trim === '') {
            $html .= '<div class="song-gap"></div>';
            continue;
        }

        if (preg_match('/^\{\s*(title|artist|section|key|capo|comment|comment_italic|comment_box|soc|eoc|start_of_chorus|end_of_chorus|tag|tags)\s*:?\s*(.*?)\s*\}$/i', $trim, $m)) {
            $directive = strtolower($m[1]);
            $value = trim($m[2]);
            if ($directive === 'title' && $value !== '') {
                $html .= '<div class="song-title-line">' . h($value) . '</div>';
            } elseif ($directive === 'artist' && $value !== '') {
                $html .= '<div class="song-artist-line">' . h($value) . '</div>';
            } elseif ($directive === 'section' && $value !== '') {
                $html .= '<div class="song-section-label">' . h($value) . '</div>';
            }
            if (in_array($directive, ['comment', 'comment_italic', 'comment_box'], true)) {
                $html .= '<div class="song-comment">' . h($value) . '</div>';
            }
            continue;
        }

        $renderedLine = preg_replace_callback('/\[([^\]]+)\]/', static function (array $matches) use ($transpose): string {
            return '<span class="inline-chord">' . h(transposeChord($matches[1], $transpose)) . '</span>';
        }, h($line));

        $html .= '<div class="lyrics-only lyrics-inline">' . $renderedLine . '</div>';
    }

    return $html;
}

function renderSongChordOverLyricsHtml(string $body, int $transpose = 0): string {
    $lines = preg_split('/\n/', $body) ?: [];
    $html = '';

    foreach ($lines as $line) {
        $trim = trim($line);
        if ($trim === '') {
            $html .= '<div class="song-gap"></div>';
            continue;
        }

        if (preg_match('/^\{\s*(title|artist|section|key|capo|comment|comment_italic|comment_box|soc|eoc|start_of_chorus|end_of_chorus|tag|tags)\s*:?\s*(.*?)\s*\}$/i', $trim, $m)) {
            $directive = strtolower($m[1]);
            $value = trim($m[2]);
            if ($directive === 'title' && $value !== '') {
                $html .= '<div class="song-title-line">' . h($value) . '</div>';
            } elseif ($directive === 'artist' && $value !== '') {
                $html .= '<div class="song-artist-line">' . h($value) . '</div>';
            } elseif ($directive === 'section' && $value !== '') {
                $html .= '<div class="song-section-label">' . h($value) . '</div>';
            }
            if (in_array($directive, ['comment', 'comment_italic', 'comment_box'], true)) {
                $html .= '<div class="song-comment">' . h($value) . '</div>';
            }
            continue;
        }

        preg_match_all('/\[([^\]]+)\]/', $line, $matches, PREG_OFFSET_CAPTURE);
        if (!$matches[0]) {
            $html .= '<div class="lyrics-only">' . h($line) . '</div>';
            continue;
        }

        $lyricLine = '';
        $insertions = [];
        $cursor = 0;
        foreach ($matches[0] as $idx => $match) {
            $full = $match[0];
            $pos = $match[1];
            $lyricLine .= substr($line, $cursor, $pos - $cursor);
            $chordText = $matches[1][$idx][0];
            $insertions[] = [
                'pos' => strlen($lyricLine),
                'text' => transposeChord($chordText, $transpose),
            ];
            $cursor = $pos + strlen($full);
        }
        $lyricLine .= substr($line, $cursor);

        if (trim($lyricLine) === '') {
            $chordsOnly = [];
            foreach ($matches[1] as $chordMatch) {
                $candidate = trim((string)($chordMatch[0] ?? ''));
                if ($candidate === '') {
                    continue;
                }
                $chordsOnly[] = transposeChord($candidate, $transpose);
            }
            $html .= '<div class="song-line"><div class="chords">' . h(implode(' ', $chordsOnly)) . '</div><div class="lyrics">&nbsp;</div></div>';
            continue;
        }

        usort($insertions, static fn(array $a, array $b): int => $a['pos'] <=> $b['pos']);
        $resolvedInsertions = [];
        foreach ($insertions as $insertion) {
            $pos = (int)$insertion['pos'];
            $text = (string)$insertion['text'];
            $length = strlen($text);
            if ($resolvedInsertions !== [] && $length > 0) {
                $last = $resolvedInsertions[count($resolvedInsertions) - 1];
                if ($pos <= $last['end'] + 1) {
                    $pos = $last['end'] + 2;
                }
            }
            $resolvedInsertions[] = [
                'pos' => $pos,
                'text' => $text,
                'end' => $pos + max($length - 1, 0),
            ];
        }

        $chordBufferLength = max(strlen($lyricLine), 1);
        foreach ($resolvedInsertions as $insertion) {
            $chordBufferLength = max($chordBufferLength, $insertion['pos'] + strlen($insertion['text']));
        }

        $chordBuffer = array_fill(0, $chordBufferLength, ' ');
        foreach ($resolvedInsertions as $insertion) {
            $chars = str_split($insertion['text']);
            foreach ($chars as $offset => $char) {
                $chordBuffer[$insertion['pos'] + $offset] = $char;
            }
        }
        $chordLine = rtrim(implode('', $chordBuffer));

        $html .= '<div class="song-line"><div class="chords">' . h(rtrim($chordLine)) . '</div><div class="lyrics">' . h(rtrim($lyricLine)) . '</div></div>';
    }

    return $html;
}
