<?php
    $songIsPdf = songIsPdf($song);
    $songPlaybackBpm = sanitizeSongBpm($song['bpm'] ?? 0);
    if ($songPlaybackBpm < 40) {
        $songPlaybackBpm = 92;
    }
    $songPlaybackSignature = normalizeSongTimeSignature((string)($song['time_signature'] ?? '4/4'));
    $songPlaybackScroll = sanitizeSongScrollSpeed($song['scroll_speed'] ?? 0, $songIsPdf);
    $songPlaybackScrollDisplay = $songIsPdf
        ? number_format($songPlaybackScroll, 1, '.', '')
        : (string)((int)round($songPlaybackScroll));
?>
<div
    class="viewer-layout"
    data-song-playback
    data-song-id="<?= (int)$song['id'] ?>"
    data-song-kind="<?= $songIsPdf ? 'pdf' : 'song' ?>"
    data-song-bpm="<?= (int)$songPlaybackBpm ?>"
    data-song-signature="<?= h($songPlaybackSignature) ?>"
    data-song-scroll-speed="<?= h((string)$songPlaybackScroll) ?>"
>
    <?php
    $buildSongViewHref = static function (array $overrides = []) use ($song, $transpose): string {
        return '?' . http_build_query(array_merge([
            'action' => 'view-song',
            'id' => (int)$song['id'],
            'transpose' => $transpose,
        ], $overrides));
    };
    ?>
    <div class="focus-bar glass-panel">
        <button type="button" class="button icon-button focus-bar-button" data-toggle-focus aria-label="Exit focus mode" title="Exit focus mode">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M15 6L9 12L15 18" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
        <div class="focus-bar-copy">
            <strong><?= h($song['title']) ?></strong>
            <span><?= $songIsPdf ? 'Focused document view' : 'Focused performance view' ?></span>
        </div>
        <div class="focus-bar-actions">
            <button type="button" class="button icon-button focus-bar-button tip-toggle-button" data-tips-open aria-label="Open tips" title="Open tips">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M12 3.8C8.56 3.8 5.78 6.5 5.78 9.83c0 2.08 1.12 3.89 2.83 4.98.45.29.73.78.73 1.31v.48h5.32v-.48c0-.53.27-1.02.72-1.31 1.72-1.09 2.84-2.9 2.84-4.98 0-3.33-2.79-6.03-6.22-6.03Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                    <path d="M9.8 18.1h4.4M10.45 20.2h3.1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </button>
            <a class="button icon-button focus-bar-button" href="?action=settings" aria-label="Open settings" title="Open settings">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M12 8.75A3.25 3.25 0 1 0 12 15.25A3.25 3.25 0 0 0 12 8.75Z" stroke="currentColor" stroke-width="1.8"/>
                    <path d="M19 12A7 7 0 0 0 18.89 10.75L20.5 9.5L19 7L17 7.5A7.1 7.1 0 0 0 15.25 6.45L14.5 4.5H9.5L8.75 6.45A7.1 7.1 0 0 0 7 7.5L5 7L3.5 9.5L5.11 10.75A7 7 0 0 0 5 12C5 12.43 5.04 12.85 5.11 13.25L3.5 14.5L5 17L7 16.5C7.53 16.93 8.11 17.29 8.75 17.55L9.5 19.5H14.5L15.25 17.55C15.89 17.29 16.47 16.93 17 16.5L19 17L20.5 14.5L18.89 13.25C18.96 12.85 19 12.43 19 12Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                </svg>
            </a>
        </div>
    </div>
    <header class="section-head viewer-head glass-panel">
        <div>
            <div class="eyebrow"><?= songIsPdf($song) ? 'PDF view' : 'Performance view' ?></div>
            <h3 class="viewer-title"><?= h($song['title']) ?></h3>
            <p class="subtle">
                <?php if ($songIsPdf): ?>
                    Imported PDF document shown as in-app pages
                <?php else: ?>
                    <?= h($song['artist']) ?: 'Unknown artist' ?> · Original key: <?= h($song['key_name']) ?: '—' ?> · Capo: <?= (int)$song['capo'] ?>
                <?php endif; ?>
            </p>
        </div>
        <div class="toolbar viewer-toolbar">
            <?php if (songIsPdf($song)): ?>
                <button type="button" class="button primary icon-button viewer-action-button button-overlay-play" data-scroll-toggle aria-label="Start auto scroll" title="Start auto scroll">
                    <span class="button-overlay-glyph" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M8.5 6.5V17.5L17 12L8.5 6.5Z" fill="currentColor"/>
                        </svg>
                    </span>
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M8.5 6.5V17.5L17 12L8.5 6.5Z" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round"/>
                    </svg>
                </button>
                <label class="speed-control speed-control-fine">
                    <span>Speed</span>
                    <input type="range" min="0.2" max="8" step="0.2" value="<?= h($songPlaybackScrollDisplay) ?>" data-scroll-speed>
                    <strong class="speed-value" data-scroll-speed-value><?= h($songPlaybackScrollDisplay) ?></strong>
                </label>
                <button type="button" class="button ghost icon-button viewer-action-button pdf-night-toggle" data-pdf-night-toggle aria-pressed="false" aria-label="Enable night mode" title="Enable night mode">
                    <span class="pdf-night-toggle-icon pdf-night-toggle-icon-moon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M17 14.5A6.5 6.5 0 0 1 9.5 7a6.5 6.5 0 1 0 7.5 7.5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span class="pdf-night-toggle-icon pdf-night-toggle-icon-sun" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M12 4V6.5M12 17.5V20M4 12H6.5M17.5 12H20M6.35 6.35L8.1 8.1M15.9 15.9L17.65 17.65M17.65 6.35L15.9 8.1M8.1 15.9L6.35 17.65M15.5 12A3.5 3.5 0 1 1 8.5 12A3.5 3.5 0 0 1 15.5 12Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </button>
                <a class="button ghost icon-button viewer-action-button" href="?action=edit-song&id=<?= (int)$song['id'] ?>" aria-label="Edit details" title="Edit details">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M4 20H8L18 10C18.5523 9.44772 18.5523 8.55228 18 8L16 6C15.4477 5.44772 14.5523 5.44772 14 6L4 16V20Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                        <path d="M12.5 7.5L16.5 11.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </a>
                <a class="button ghost icon-button viewer-action-button" href="<?= h(songFileUrl($song)) ?>" target="_blank" rel="noopener" aria-label="Open original file" title="Open original file">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M8 3.5H13L18 8.5V19C18 20.1046 17.1046 21 16 21H8C6.89543 21 6 20.1046 6 19V5.5C6 4.39543 6.89543 3.5 8 3.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                        <path d="M13 3.5V8.5H18" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                    </svg>
                </a>
                <button type="button" class="button ghost icon-button viewer-action-button" data-toggle-focus aria-label="Enter focus mode" title="Enter focus mode">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M2.75 12C4.7 8.4 8.05 6.25 12 6.25C15.95 6.25 19.3 8.4 21.25 12C19.3 15.6 15.95 17.75 12 17.75C8.05 17.75 4.7 15.6 2.75 12Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                        <circle cx="12" cy="12" r="2.75" stroke="currentColor" stroke-width="1.8"/>
                    </svg>
                </button>
            <?php else: ?>
                <?php if (songHasAttachedPdf($song)): ?>
                    <a class="button ghost icon-button viewer-action-button" href="<?= h(songFileUrl($song)) ?>" target="_blank" rel="noopener" aria-label="Open original PDF" title="Open original PDF">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M8 3.5H13L18 8.5V19C18 20.1046 17.1046 21 16 21H8C6.89543 21 6 20.1046 6 19V5.5C6 4.39543 6.89543 3.5 8 3.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            <path d="M13 3.5V8.5H18" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                        </svg>
                    </a>
                <?php endif; ?>
                <a class="button ghost icon-button viewer-action-button" href="<?= h('?action=print-song&id=' . (int)$song['id'] . '&transpose=' . $transpose) ?>" target="_blank" rel="noopener" aria-label="Print song" title="Print song">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M7 8V4.5H17V8M7.5 18.5H16.5V14H7.5V18.5ZM6.5 9.5H17.5C18.6 9.5 19.5 10.4 19.5 11.5V15H16.5V13H7.5V15H4.5V11.5C4.5 10.4 5.4 9.5 6.5 9.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                    </svg>
                </a>
                <a class="button ghost icon-button viewer-action-button" href="?action=edit-song&id=<?= (int)$song['id'] ?>" aria-label="Edit song" title="Edit song">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M4 20H8L18 10C18.5523 9.44772 18.5523 8.55228 18 8L16 6C15.4477 5.44772 14.5523 5.44772 14 6L4 16V20Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                        <path d="M12.5 7.5L16.5 11.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </a>
                <a class="button ghost icon-button viewer-action-button transpose-symbol-button" href="<?= h($buildSongViewHref(['transpose' => $transpose - 1])) ?>" aria-label="Transpose down" title="Transpose down">
                    <span aria-hidden="true">♭</span>
                </a>
                <span class="pill">Transpose <?= $transpose > 0 ? '+' : '' ?><?= $transpose ?></span>
                <a class="button ghost icon-button viewer-action-button transpose-symbol-button" href="<?= h($buildSongViewHref(['transpose' => $transpose + 1])) ?>" aria-label="Transpose up" title="Transpose up">
                    <span aria-hidden="true">♯</span>
                </a>
                <button type="button" class="button primary icon-button viewer-action-button button-overlay-play" data-scroll-toggle aria-label="Start auto scroll" title="Start auto scroll">
                    <span class="button-overlay-glyph" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M8.5 6.5V17.5L17 12L8.5 6.5Z" fill="currentColor"/>
                        </svg>
                    </span>
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M8.5 6.5V17.5L17 12L8.5 6.5Z" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round"/>
                    </svg>
                </button>
                <label class="speed-control">
                    <span>Speed</span>
                    <input type="range" min="1" max="14" value="<?= h($songPlaybackScrollDisplay) ?>" data-scroll-speed>
                    <strong class="speed-value" data-scroll-speed-value><?= h($songPlaybackScrollDisplay) ?></strong>
                </label>
                <button type="button" class="button ghost" data-font-step="up">A+</button>
                <button type="button" class="button ghost" data-font-step="down">A-</button>
                <button type="button" class="button ghost icon-button viewer-action-button" data-toggle-focus aria-label="Enter focus mode" title="Enter focus mode">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M2.75 12C4.7 8.4 8.05 6.25 12 6.25C15.95 6.25 19.3 8.4 21.25 12C19.3 15.6 15.95 17.75 12 17.75C8.05 17.75 4.7 15.6 2.75 12Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                        <circle cx="12" cy="12" r="2.75" stroke="currentColor" stroke-width="1.8"/>
                    </svg>
                </button>
            <?php endif; ?>
        </div>
    </header>

    <div class="focus-performance-shell">
        <aside class="glass-panel focus-side-panel focus-chord-panel" data-focus-helper-panel>
            <div class="focus-panel-head">
                <div>
                    <div class="eyebrow">Chord Guide</div>
                    <h4><?= songIsPdf($song) ? 'Detected Chords' : 'Song Chords' ?></h4>
                </div>
                <span class="pill"><?= count($songChordReferences ?? []) ?> unique</span>
            </div>
            <p class="subtle focus-panel-copy">
                <?= songIsPdf($song)
                    ? 'Detected from the PDF text when chord lines or chord tokens are available. Repeated chords only appear once.'
                    : 'Repeated chords only appear once. Diagrams follow the current transpose view.' ?>
            </p>
            <?php if (!empty($songChordReferences)): ?>
                <div class="focus-chord-list">
                    <?php
                    $whiteKeys = [
                        ['label' => 'C', 'index' => 0],
                        ['label' => 'D', 'index' => 2],
                        ['label' => 'E', 'index' => 4],
                        ['label' => 'F', 'index' => 5],
                        ['label' => 'G', 'index' => 7],
                        ['label' => 'A', 'index' => 9],
                        ['label' => 'B', 'index' => 11],
                    ];
                    $blackKeys = [
                        ['label' => 'C#', 'index' => 1, 'position' => 0.095],
                        ['label' => 'Eb', 'index' => 3, 'position' => 0.238],
                        ['label' => 'F#', 'index' => 6, 'position' => 0.523],
                        ['label' => 'Ab', 'index' => 8, 'position' => 0.666],
                        ['label' => 'Bb', 'index' => 10, 'position' => 0.809],
                    ];
                    ?>
                    <?php foreach ($songChordReferences as $chordReference): ?>
                        <?php
                        $diagram = $chordReference['diagram'];
                        $activeKeys = array_flip($diagram['active_keys']);
                        ?>
                        <article class="focus-chord-card">
                            <div class="focus-chord-card-head">
                                <strong><?= h($chordReference['name']) ?></strong>
                                <span><?= h($chordReference['quality_label']) ?></span>
                            </div>
                            <div class="focus-chord-piano" aria-label="<?= h($chordReference['name']) ?> piano chord diagram">
                                <div class="focus-chord-piano-keys">
                                    <div class="focus-chord-white-keys">
                                        <?php foreach ($whiteKeys as $key): ?>
                                            <?php
                                            $isActive = isset($activeKeys[$key['index']]);
                                            $isRoot = $diagram['root_index'] === $key['index'];
                                            ?>
                                            <div class="focus-chord-white-key<?= $isActive ? ' is-active' : '' ?><?= $isRoot ? ' is-root' : '' ?>"></div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php foreach ($blackKeys as $key): ?>
                                        <?php
                                        $isActive = isset($activeKeys[$key['index']]);
                                        $isRoot = $diagram['root_index'] === $key['index'];
                                        ?>
                                        <div class="focus-chord-black-key<?= $isActive ? ' is-active' : '' ?><?= $isRoot ? ' is-root' : '' ?>" style="left: calc(<?= $key['position'] ?> * 100%);"></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty focus-panel-empty">
                    <h4>No supported piano chords</h4>
                    <p class="subtle">
                        <?= songIsPdf($song)
                            ? 'No recognizable chord lines or chord tokens were detected from this PDF.'
                            : 'Inline chords like <code>[C]</code>, <code>[Am]</code>, <code>[G7]</code>, <code>[Fsus4]</code>, or <code>[Dadd9]</code> will appear here.' ?>
                    </p>
                </div>
            <?php endif; ?>
        </aside>

    <?php if ($songIsPdf): ?>
        <section class="song-sheet pdf-sheet glass-panel" data-song-sheet data-pdf-sheet>
            <div class="focus-overlay-controls" aria-label="Focused playback controls">
                <button type="button" class="button primary icon-button focus-overlay-button button-overlay-play" data-focus-scroll-toggle aria-label="Start auto scroll" title="Start auto scroll">
                    <span class="button-overlay-glyph" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M8.5 6.5V17.5L17 12L8.5 6.5Z" fill="currentColor"/>
                        </svg>
                    </span>
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M8.5 6.5V17.5L17 12L8.5 6.5Z" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round"/>
                    </svg>
                </button>
                <button type="button" class="button ghost icon-button focus-overlay-button" data-focus-metronome-toggle aria-label="Start metronome" title="Start metronome">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M10 4.5H14M12 4.5V7.5M8 20H16L14.5 9H9.5L8 20Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12 12L14.5 14.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
            <?php if (!empty($pdfPages)): ?>
                <div class="pdf-pages">
                    <?php foreach ($pdfPages as $index => $pageFile): ?>
                        <figure class="pdf-page">
                            <img src="<?= h(songPageImageUrl($song, $index + 1)) ?>" alt="<?= h($song['title']) ?> page <?= $index + 1 ?>" loading="lazy">
                        </figure>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="pdf-empty">
                    <p>PDF pages could not be rendered.</p>
                    <a class="button primary" href="<?= h(songFileUrl($song)) ?>" target="_blank" rel="noopener">Open Original File</a>
                </div>
            <?php endif; ?>
        </section>
    <?php else: ?>
        <section class="song-sheet glass-panel is-over-mode" data-song-sheet data-font-size="18">
            <div class="focus-overlay-controls" aria-label="Focused playback controls">
                <button type="button" class="button primary icon-button focus-overlay-button button-overlay-play" data-focus-scroll-toggle aria-label="Start auto scroll" title="Start auto scroll">
                    <span class="button-overlay-glyph" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M8.5 6.5V17.5L17 12L8.5 6.5Z" fill="currentColor"/>
                        </svg>
                    </span>
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M8.5 6.5V17.5L17 12L8.5 6.5Z" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round"/>
                    </svg>
                </button>
                <button type="button" class="button ghost icon-button focus-overlay-button" data-focus-metronome-toggle aria-label="Start metronome" title="Start metronome">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M10 4.5H14M12 4.5V7.5M8 20H16L14.5 9H9.5L8 20Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12 12L14.5 14.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
            <?= $rendered ?>
        </section>
    <?php endif; ?>
        <aside class="glass-panel focus-side-panel focus-metronome-panel" data-focus-helper-panel>
            <?php $metronomePanelClass = 'focus-side-metronome'; require APP_ROOT . '/views/partials/metronome.php'; ?>
        </aside>
    </div>
</div>
