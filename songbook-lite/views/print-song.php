<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($song['title'] ?: 'Print Song') ?></title>
    <style>
        :root {
            color-scheme: light;
        }
        body {
            margin: 0;
            font-family: "Georgia", "Times New Roman", serif;
            color: #111827;
            background: #f7f7f5;
        }
        .print-shell {
            max-width: 900px;
            margin: 0 auto;
            padding: 32px 24px 56px;
        }
        .print-toolbar {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 24px;
        }
        .print-button {
            text-decoration: none;
            border-radius: 12px;
            border: 1px solid #d1d5db;
            padding: 10px 14px;
            color: #111827;
            background: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
        }
        .print-sheet {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            padding: 28px;
        }
        .print-meta {
            margin-bottom: 22px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 16px;
        }
        .print-meta h1 {
            margin: 0 0 8px;
            font-size: 2rem;
        }
        .print-meta p {
            margin: 0;
            color: #4b5563;
        }
        .song-sheet {
            font-family: "Courier New", monospace;
            font-size: 16px;
            line-height: 1.5;
        }
        .song-line {
            margin-bottom: 8px;
        }
        .chords {
            color: #8a5a00;
            white-space: pre;
            font-weight: 700;
        }
        .lyrics, .lyrics-only {
            white-space: pre-wrap;
        }
        .song-title-line {
            margin: 0 0 10px;
            color: #111827;
            font-size: 1.35em;
            font-weight: 800;
            letter-spacing: 0.01em;
        }
        .song-artist-line {
            margin: 0 0 12px;
            color: #1d4ed8;
            font-weight: 700;
        }
        .song-section-label {
            margin: 14px 0 8px;
            color: #0f766e;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        .song-comment {
            margin: 8px 0;
            padding: 8px 10px;
            border-left: 3px solid #0f766e;
            background: #f0fdfa;
            border-radius: 8px;
        }
        .song-gap {
            height: 12px;
        }
        @media print {
            body {
                background: #fff;
            }
            .print-toolbar {
                display: none;
            }
            .print-shell {
                max-width: none;
                padding: 0;
            }
            .print-sheet {
                border: 0;
                border-radius: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="print-shell">
        <div class="print-toolbar">
            <a class="print-button" href="?action=view-song&id=<?= (int)$song['id'] ?>&transpose=<?= (int)$transpose ?>">Back to Song</a>
            <button class="print-button" type="button" onclick="window.print()">Print</button>
        </div>
        <article class="print-sheet">
            <header class="print-meta">
                <h1><?= h($song['title']) ?></h1>
                <p><?= h($song['artist'] ?: 'Unknown artist') ?> · Original key: <?= h($song['key_name'] ?: '—') ?> · Capo: <?= (int)$song['capo'] ?></p>
            </header>
            <section class="song-sheet">
                <?= $rendered ?>
            </section>
        </article>
    </div>
</body>
</html>
