<?php
$documentType = $documentType ?? 'all';
$sort = $sort ?? 'title';
$aiHighlight = (string)($_GET['ai_highlight'] ?? '') === '1';
$assistantSearchQuery = trim((string)($_GET['ai_query'] ?? ''));
$assistantFoundCount = max(0, (int)($_GET['ai_found'] ?? 0));
$queryExtras = [];
if ($aiHighlight && $assistantSearchQuery !== '') {
    $queryExtras['ai_highlight'] = '1';
    $queryExtras['ai_query'] = $assistantSearchQuery;
    if ($assistantFoundCount > 0) {
        $queryExtras['ai_found'] = $assistantFoundCount;
    }
    if (isset($_GET['ai_source'])) {
        $queryExtras['ai_source'] = (string)$_GET['ai_source'];
    }
}
$toolbarSetlists = [];
try {
    $toolbarSetlists = db()->query('SELECT id, name FROM setlists ORDER BY updated_at DESC, name ASC')->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $error) {
    $toolbarSetlists = [];
}
$filterQuery = static function (string $type) use ($search, $sort, $queryExtras): string {
    return '?' . http_build_query([
        'action' => 'songs',
        'q' => $search,
        'type' => $type,
        'sort' => $sort,
    ] + $queryExtras);
};
$sortQuery = static function (string $value) use ($search, $documentType, $queryExtras): string {
    return '?' . http_build_query([
        'action' => 'songs',
        'q' => $search,
        'type' => $documentType,
        'sort' => $value,
    ] + $queryExtras);
};
$libraryQuery = static function (array $overrides = []) use ($search, $documentType, $sort, $queryExtras): string {
    return '?' . http_build_query(array_merge([
        'action' => 'songs',
        'q' => $search,
        'type' => $documentType,
        'sort' => $sort,
    ], $queryExtras, $overrides));
};
$matchesAssistantQuery = static function (array $song) use ($aiHighlight, $assistantSearchQuery): bool {
    if (!$aiHighlight || $assistantSearchQuery === '') {
        return false;
    }

    $needle = mb_strtolower($assistantSearchQuery);
    $haystack = mb_strtolower(trim(implode(' ', [
        (string)($song['title'] ?? ''),
        (string)($song['artist'] ?? ''),
        (string)($song['tags'] ?? ''),
    ])));

    return $needle !== '' && str_contains($haystack, $needle);
};

?>
<header class="section-head library-header">
    <div class="library-header-copy">
        <div class="eyebrow">Library</div>
        <h1>Your <span class="library-title-accent">Songs</span></h1>
        <p class="subtle">Centralized song library with optimized access for viewing, organizing, and performance workflow management.</p>
    </div>
    <div class="library-toolbar">
        <form method="get" id="library-search" class="search-form glass-panel compact-panel library-search<?= $search !== '' ? ' is-open' : '' ?>" data-library-search-form>
            <input type="hidden" name="action" value="songs">
            <input type="hidden" name="type" value="<?= h($documentType) ?>">
            <input
                type="search"
                name="q"
                value="<?= h($search) ?>"
                placeholder="Search title, artist or tags"
                data-library-search-input
                <?= $search !== '' ? '' : 'tabindex="-1"' ?>
            >
            <button type="submit" class="button icon-button primary" aria-label="Submit search" title="Search">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M10.5 4a6.5 6.5 0 1 0 4.03 11.6l4.43 4.44 1.41-1.42-4.43-4.43A6.5 6.5 0 0 0 10.5 4Zm0 2a4.5 4.5 0 1 1 0 9 4.5 4.5 0 0 1 0-9Z" fill="currentColor"/>
                </svg>
            </button>
        </form>
        <div class="actions library-actions">
            <button
                type="button"
                class="button icon-button ghost<?= $search !== '' ? ' is-active' : '' ?>"
                data-library-search-toggle
                aria-expanded="<?= $search !== '' ? 'true' : 'false' ?>"
                aria-controls="library-search"
                aria-label="Toggle search"
                title="Search"
            >
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M10.5 4a6.5 6.5 0 1 0 4.03 11.6l4.43 4.44 1.41-1.42-4.43-4.43A6.5 6.5 0 0 0 10.5 4Zm0 2a4.5 4.5 0 1 1 0 9 4.5 4.5 0 0 1 0-9Z" fill="currentColor"/>
                </svg>
            </button>
            <a class="button icon-button ghost" href="?action=import" aria-label="Paste or import songs" title="Paste / Import">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M9 3h6l1 2h3a2 2 0 0 1 2 2v11a3 3 0 0 1-3 3H8a3 3 0 0 1-3-3V7a2 2 0 0 1 2-2h3l1-2Zm0 4H7v11a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V7h-2v2H9V7Zm2-2v2h2V5h-2Zm1 6 4 4h-3v3h-2v-3H8l4-4Z" fill="currentColor"/>
                </svg>
            </a>
            <a class="button icon-button primary" href="?action=new-song" aria-label="Create new song" title="New Song">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M11 5h2v6h6v2h-6v6h-2v-6H5v-2h6V5Z" fill="currentColor"/>
                </svg>
            </a>
            <div class="library-filter-group glass-panel compact-panel" role="group" aria-label="Filter document type">
                <a class="button library-filter-button<?= $documentType === 'all' ? ' is-active' : '' ?>" href="<?= h($filterQuery('all')) ?>">All</a>
                <a class="button library-filter-button<?= $documentType === 'song' ? ' is-active' : '' ?>" href="<?= h($filterQuery('song')) ?>">Songs</a>
                <a class="button library-filter-button<?= $documentType === 'pdf' ? ' is-active' : '' ?>" href="<?= h($filterQuery('pdf')) ?>">PDFs</a>
            </div>
            <div class="library-filter-group glass-panel compact-panel" role="group" aria-label="Sort songs">
                <a class="button library-filter-button<?= $sort === 'title' ? ' is-active' : '' ?>" href="<?= h($sortQuery('title')) ?>">Title</a>
                <a class="button library-filter-button<?= $sort === 'artist' ? ' is-active' : '' ?>" href="<?= h($sortQuery('artist')) ?>">Artist</a>
                <a class="button library-filter-button<?= $sort === 'key' ? ' is-active' : '' ?>" href="<?= h($sortQuery('key')) ?>">Key</a>
                <a class="button library-filter-button<?= $sort === 'recent' ? ' is-active' : '' ?>" href="<?= h($sortQuery('recent')) ?>">Recent</a>
            </div>
            <div class="view-toggle glass-panel compact-panel" role="group" aria-label="Song layout">
                <button
                    type="button"
                    class="button icon-button ghost is-active"
                    data-library-view="cards"
                    aria-pressed="true"
                    aria-label="Card view"
                    title="Card View"
                >
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M4 5h7v6H4V5Zm9 0h7v6h-7V5ZM4 13h7v6H4v-6Zm9 0h7v6h-7v-6Z" fill="currentColor"/>
                    </svg>
                </button>
                <button
                    type="button"
                    class="button icon-button ghost"
                    data-library-view="list"
                    aria-pressed="false"
                    aria-label="List view"
                    title="List View"
                >
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M4 6h3v3H4V6Zm5 0h11v3H9V6ZM4 11h3v3H4v-3Zm5 0h11v3H9v-3ZM4 16h3v3H4v-3Zm5 0h11v3H9v-3Z" fill="currentColor"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</header>

<?php if (!empty($_GET['imported'])): ?>
    <div class="notice success">Imported <?= (int)$_GET['imported'] ?> song(s). <?= h($_GET['errors'] ?? '') ?></div>
<?php endif; ?>
<?php if ($aiHighlight && $assistantSearchQuery !== ''): ?>
    <div class="notice success">Juan search: "<?= h($assistantSearchQuery) ?>"<?= $assistantFoundCount > 0 ? ' · ' . $assistantFoundCount . ' match(es) highlighted' : '' ?>.</div>
<?php endif; ?>

<?php if ($songs): ?>
    <div class="library-view-stack" data-library-layout>
        <div class="cards cards-library library-panel" data-library-panel="cards">
            <?php foreach ($songs as $song): ?>
                <?php
                $songId = (int)$song['id'];
                $title = h($song['title']);
                $artist = h($song['artist']);
                $tags = h($song['tags']);
                $keyName = h($song['key_name']);
                $isPdf = songIsPdf($song);
                $hasAttachedPdf = songHasAttachedPdf($song);
                $pdfProfile = $isPdf ? pdfDisplayProfile($song) : null;
                $viewHref = '?action=view-song&id=' . $songId;
                $editHref = '?action=edit-song&id=' . $songId;
                $deleteHref = '?action=delete-song&id=' . $songId;
                $artistLine = $isPdf ? 'In-app page view' : ($artist !== '' ? $artist : ($hasAttachedPdf ? 'Converted from PDF' : 'Unknown artist'));
                $keyDisplay = $song['key_name'] !== '' ? 'Key: ' . $keyName : 'Key: —';
                $cardChip = $isPdf ? h((string)$pdfProfile['label']) : $keyDisplay;
                $isAiHit = $matchesAssistantQuery($song);
                ?>
                <article class="card song-card<?= $isAiHit ? ' ai-song-hit' : '' ?>">
                    <div class="song-card-top">
                        <div class="song-card-copy">
                            <div class="eyebrow song-type-row">
                                <span class="song-type-icon<?= $isPdf ? ' is-pdf' : '' ?>" aria-hidden="true">
                                    <?php if ($isPdf): ?>
                                        <svg viewBox="0 0 24 24">
                                            <path d="M7.5 3h6.8L20 8.6V19a2 2 0 0 1-2 2H7.5a2.5 2.5 0 0 1-2.5-2.5v-13A2.5 2.5 0 0 1 7.5 3Z" fill="currentColor"/>
                                            <path d="M14.2 3.2V8h4.6" fill="none" stroke="#fff" stroke-width="1.5" stroke-linejoin="round"/>
                                            <path d="M8.2 16.6c1.9-2.7 4.7-3.2 7.4-1.8-.8-2.4-.5-4.2 1.1-5.8-2.4.3-4 .9-5.3 2.8-.6-1.2-1.6-2.1-3.2-2.7 1 2 1 4-.1 7.5Z" fill="#fff"/>
                                        </svg>
                                    <?php else: ?>
                                        <svg viewBox="0 0 24 24">
                                            <path d="M6 3h8l5 5v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" fill="currentColor"/>
                                            <path d="M14 3v5h5" fill="none" stroke="#04140c" stroke-width="1.6" stroke-linejoin="round"/>
                                            <path d="M8 12h8M8 15h8" stroke="#04140c" stroke-width="1.6" stroke-linecap="round"/>
                                        </svg>
                                    <?php endif; ?>
                                </span>
                                <span><?= $isPdf ? 'PDF' : 'Song File' ?></span>
                            </div>
                            <h4><a href="<?= h($viewHref) ?>"><?= $title ?></a></h4>
                            <p class="artist-line"><?= $artistLine ?></p>
                            <div class="song-card-details">
                                <?php if (!$isPdf): ?>
                                    <p class="song-card-detail-line">Key: <?= $song['key_name'] !== '' ? $keyName : 'No key' ?> · Capo <?= (int)$song['capo'] ?></p>
                                <?php endif; ?>
                                <p class="song-card-detail-line">Tags: <?= $tags !== '' ? $tags : 'No tags' ?></p>
                            </div>
                        </div>
                        <span class="chip<?= $isPdf ? ' chip-pdf' : ' chip-song' ?>"><?= $cardChip ?></span>
                    </div>
                    <div class="actions split-actions icon-actions">
                        <a class="button icon-button primary" href="<?= h($viewHref) ?>" aria-label="Open <?= $title ?>" title="Open">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M5 4h11l3 3v13a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Zm8 1.5V8h2.5L13 5.5ZM7 12v2h8v-2H7Zm0 4v2h6v-2H7Z" fill="currentColor"/>
                            </svg>
                        </a>
                        <a class="button icon-button ghost" href="<?= h($editHref) ?>" aria-label="Edit <?= $title ?>" title="<?= $isPdf ? 'Edit Details' : 'Edit' ?>">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="m16.86 3.49 3.65 3.65-10.9 10.9L5 19l.96-4.61 10.9-10.9Zm-9.29 12.3-.39 1.82 1.82-.39 9.75-9.75-1.43-1.43-9.75 9.75Z" fill="currentColor"/>
                            </svg>
                        </a>
                        <?php if ($isPdf): ?>
                            <a class="button icon-button ghost" href="<?= h(songFileUrl($song)) ?>" target="_blank" rel="noopener" aria-label="Open original PDF for <?= $title ?>" title="Open PDF">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5Zm0 2.5L16.5 8H14V5.5ZM8 12h2.2a2.4 2.4 0 1 1 0 4.8H9.4V19H8v-7Zm1.4 1.3v2.2h.8a1.1 1.1 0 1 0 0-2.2h-.8Zm4-.3h2.1a2.5 2.5 0 0 1 0 5h-.7V19h-1.4v-6Zm1.4 1.3v2.4h.6a1.2 1.2 0 0 0 0-2.4h-.6Z" fill="currentColor"/>
                                </svg>
                            </a>
                        <?php endif; ?>
                        <a
                            class="button icon-button danger"
                            href="<?= h($deleteHref) ?>"
                            data-confirm-delete
                            data-confirm-title="Delete <?= $title ?>?"
                            data-confirm-message="This will permanently remove this song from your library."
                            data-confirm-action="Delete Song"
                            aria-label="Delete <?= $title ?>"
                            title="Delete"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M9 3h6l1 2h4v2H4V5h4l1-2Zm-1 6h2v8H8V9Zm6 0h2v8h-2V9ZM6 9h12l-1 11a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2L6 9Z" fill="currentColor"/>
                            </svg>
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <section class="library-list library-panel" data-library-panel="list" hidden>
            <?php foreach ($songs as $song): ?>
                <?php
                $songId = (int)$song['id'];
                $title = h($song['title']);
                $artist = h($song['artist']);
                $tags = h($song['tags']);
                $keyName = h($song['key_name']);
                $isPdf = songIsPdf($song);
                $hasAttachedPdf = songHasAttachedPdf($song);
                $pdfProfile = $isPdf ? pdfDisplayProfile($song) : null;
                $openHref = '?action=view-song&id=' . $songId;
                $editHref = '?action=edit-song&id=' . $songId;
                $deleteHref = '?action=delete-song&id=' . $songId;
                $artistLine = $isPdf ? 'In-app page view' : ($artist !== '' ? $artist : ($hasAttachedPdf ? 'Converted from PDF' : 'Unknown artist'));
                $keyDisplay = $song['key_name'] !== '' ? 'KEY: ' . $keyName : 'No key';
                $summary = $isPdf ? 'PDF document' : ($keyDisplay . ' · Capo ' . (int)$song['capo']);
                $summarySecondary = $tags !== '' ? $tags : 'No tags';
                $isAiHit = $matchesAssistantQuery($song);
                ?>
                <article
                    class="glass-panel library-list-item<?= $isAiHit ? ' ai-song-hit' : '' ?>"
                    data-song-list-item
                    data-open-href="<?= h($openHref) ?>"
                    tabindex="0"
                    aria-expanded="false"
                >
                    <div class="library-list-main" data-song-list-main>
                        <div class="library-list-copy">
                            <div class="eyebrow song-type-row">
                                <span class="song-type-icon<?= $isPdf ? ' is-pdf' : '' ?>" aria-hidden="true">
                                    <?php if ($isPdf): ?>
                                        <svg viewBox="0 0 24 24">
                                            <path d="M7.5 3h6.8L20 8.6V19a2 2 0 0 1-2 2H7.5a2.5 2.5 0 0 1-2.5-2.5v-13A2.5 2.5 0 0 1 7.5 3Z" fill="currentColor"/>
                                            <path d="M14.2 3.2V8h4.6" fill="none" stroke="#fff" stroke-width="1.5" stroke-linejoin="round"/>
                                            <path d="M8.2 16.6c1.9-2.7 4.7-3.2 7.4-1.8-.8-2.4-.5-4.2 1.1-5.8-2.4.3-4 .9-5.3 2.8-.6-1.2-1.6-2.1-3.2-2.7 1 2 1 4-.1 7.5Z" fill="#fff"/>
                                        </svg>
                                    <?php else: ?>
                                        <svg viewBox="0 0 24 24">
                                            <path d="M6 3h8l5 5v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" fill="currentColor"/>
                                            <path d="M14 3v5h5" fill="none" stroke="#04140c" stroke-width="1.6" stroke-linejoin="round"/>
                                            <path d="M8 12h8M8 15h8" stroke="#04140c" stroke-width="1.6" stroke-linecap="round"/>
                                        </svg>
                                    <?php endif; ?>
                                </span>
                                <span><?= $isPdf ? 'PDF' : 'Song File' ?></span>
                            </div>
                            <h4><?= $title ?></h4>
                            <p class="artist-line"><?= $artistLine ?></p>
                        </div>
                        <div class="library-list-meta">
                            <span class="chip<?= $isPdf ? ' chip-pdf' : ' chip-song' ?>"><?= $isPdf ? h((string)$pdfProfile['label']) : ('Key: ' . ($song['key_name'] !== '' ? $keyName : '—')) ?></span>
                            <div class="library-list-meta-stack">
                                <span class="library-list-summary"><?= $summary ?></span>
                                <span class="library-list-summary library-list-summary-secondary"><?= $summarySecondary ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="library-list-expand">
                        <div class="meta-grid compact-meta-grid">
                            <div><span><?= $isPdf ? 'Type' : 'Capo' ?></span><strong><?= $isPdf ? 'PDF' : (int)$song['capo'] ?></strong></div>
                            <div><span>Tags</span><strong><?= $tags ?: '—' ?></strong></div>
                        </div>
                        <div class="actions icon-actions">
                            <a class="button icon-button primary" href="<?= h($openHref) ?>" aria-label="Open <?= $title ?>" title="Open">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M5 4h11l3 3v13a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Zm8 1.5V8h2.5L13 5.5ZM7 12v2h8v-2H7Zm0 4v2h6v-2H7Z" fill="currentColor"/>
                                </svg>
                            </a>
                            <a class="button icon-button ghost" href="<?= h($editHref) ?>" aria-label="Edit <?= $title ?>" title="<?= $isPdf ? 'Edit Details' : 'Edit' ?>">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="m16.86 3.49 3.65 3.65-10.9 10.9L5 19l.96-4.61 10.9-10.9Zm-9.29 12.3-.39 1.82 1.82-.39 9.75-9.75-1.43-1.43-9.75 9.75Z" fill="currentColor"/>
                                </svg>
                            </a>
                            <?php if ($isPdf): ?>
                                <a class="button icon-button ghost" href="<?= h(songFileUrl($song)) ?>" target="_blank" rel="noopener" aria-label="Open original PDF for <?= $title ?>" title="Open PDF">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5Zm0 2.5L16.5 8H14V5.5ZM8 12h2.2a2.4 2.4 0 1 1 0 4.8H9.4V19H8v-7Zm1.4 1.3v2.2h.8a1.1 1.1 0 1 0 0-2.2h-.8Zm4-.3h2.1a2.5 2.5 0 0 1 0 5h-.7V19h-1.4v-6Zm1.4 1.3v2.4h.6a1.2 1.2 0 0 0 0-2.4h-.6Z" fill="currentColor"/>
                                    </svg>
                                </a>
                            <?php endif; ?>
                            <a
                                class="button icon-button danger"
                                href="<?= h($deleteHref) ?>"
                                data-confirm-delete
                                data-confirm-title="Delete <?= $title ?>?"
                                data-confirm-message="This will permanently remove this song from your library."
                                data-confirm-action="Delete Song"
                                aria-label="Delete <?= $title ?>"
                                title="Delete"
                            >
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M9 3h6l1 2h4v2H4V5h4l1-2Zm-1 6h2v8H8V9Zm6 0h2v8h-2V9ZM6 9h12l-1 11a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2L6 9Z" fill="currentColor"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    </div>
<?php else: ?>
    <div class="empty glass-panel">
        <h4>No songs yet</h4>
        <p class="subtle">Import files or create one manually to start building your library.</p>
        <div class="actions">
            <a class="button primary" href="?action=import">Import Files</a>
            <a class="button ghost" href="?action=new-song">Create Song</a>
        </div>
    </div>
<?php endif; ?>
