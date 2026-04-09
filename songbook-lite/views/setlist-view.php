<header class="section-head viewer-head glass-panel">
    <div>
        <div class="eyebrow">Setlist</div>
        <h1><?= h($setlist['name']) ?></h1>
        <p class="subtle"><?= h($setlist['notes']) ?: 'No notes' ?></p>
    </div>
    <div class="actions">
        <a class="button icon-button ghost" href="?action=edit-setlist&id=<?= (int)$setlist['id'] ?>" aria-label="Edit songs" title="Edit Songs">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="m16.86 3.49 3.65 3.65-10.9 10.9L5 19l.96-4.61 10.9-10.9Zm-9.29 12.3-.39 1.82 1.82-.39 9.75-9.75-1.43-1.43-9.75 9.75Z" fill="currentColor"/>
            </svg>
        </a>
    </div>
</header>

<section class="glass-panel setlist-arrangement-panel">
    <div class="setlist-arrangement-head">
        <div>
            <div class="eyebrow">Arrangement</div>
            <h4>Drag songs into your custom sequence</h4>
            <p class="subtle">Reorder the set by dragging each row with the handle, then save the arrangement.</p>
        </div>
        <span class="pill"><?= count($songs) ?> song<?= count($songs) === 1 ? '' : 's' ?></span>
    </div>

    <?php if ($songs): ?>
        <form method="post" action="?action=reorder-setlist" class="stack-form" data-setlist-arrangement-form>
            <input type="hidden" name="id" value="<?= (int)$setlist['id'] ?>">
            <ol class="setlist-arrangement-list" data-setlist-sortable-list>
                <?php foreach ($songs as $position => $song): ?>
                    <li class="setlist-arrangement-item" draggable="true" data-setlist-sortable-item data-song-id="<?= (int)$song['id'] ?>">
                        <input type="hidden" name="song_ids[]" value="<?= (int)$song['id'] ?>">
                        <button type="button" class="setlist-drag-handle" aria-label="Drag <?= h($song['title']) ?>" title="Drag to reorder">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M9 6.5A1.5 1.5 0 1 1 9 3.5a1.5 1.5 0 0 1 0 3Zm6 0A1.5 1.5 0 1 1 15 3.5a1.5 1.5 0 0 1 0 3ZM9 13.5A1.5 1.5 0 1 1 9 10.5a1.5 1.5 0 0 1 0 3Zm6 0A1.5 1.5 0 1 1 15 10.5a1.5 1.5 0 0 1 0 3ZM9 20.5A1.5 1.5 0 1 1 9 17.5a1.5 1.5 0 0 1 0 3Zm6 0A1.5 1.5 0 1 1 15 17.5a1.5 1.5 0 0 1 0 3Z" fill="currentColor"/>
                            </svg>
                        </button>
                        <span class="setlist-order-badge" data-setlist-order-badge><?= $position + 1 ?></span>
                        <div class="setlist-arrangement-copy">
                            <strong><a href="?action=view-song&id=<?= (int)$song['id'] ?>"><?= h($song['title']) ?></a></strong>
                            <span><?= h($song['artist']) ?: 'Unknown artist' ?></span>
                            <span class="setlist-song-tags"><?= h($song['tags']) ?: 'No tags' ?></span>
                        </div>
                        <a
                            class="setlist-song-menu button icon-button ghost"
                            href="?action=remove-setlist-song&id=<?= (int)$setlist['id'] ?>&song_id=<?= (int)$song['id'] ?>"
                            data-confirm-delete
                            data-confirm-title="Remove <?= h($song['title']) ?> from this setlist?"
                            data-confirm-message="This will remove the song from this setlist only. The song will stay in your library and you can add it back later."
                            data-confirm-action="Remove Song"
                            aria-label="More options for <?= h($song['title']) ?>"
                            title="Remove from setlist"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 7a1.75 1.75 0 1 1 0-3.5A1.75 1.75 0 0 1 12 7Zm0 7a1.75 1.75 0 1 1 0-3.5A1.75 1.75 0 0 1 12 14Zm0 7a1.75 1.75 0 1 1 0-3.5A1.75 1.75 0 0 1 12 21Z" fill="currentColor"/>
                            </svg>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ol>
            <div class="actions">
                <button type="submit" class="button primary">Save Arrangement</button>
            </div>
        </form>
    <?php else: ?>
        <div class="empty glass-panel">
            <h4>No songs in this setlist yet</h4>
            <p class="subtle">Edit the setlist to add songs, then drag them here into performance order.</p>
        </div>
    <?php endif; ?>
</section>
