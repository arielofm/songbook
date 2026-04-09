<header class="section-head">
    <div>
        <div class="eyebrow">Setlists</div>
        <h3>Build performance flows</h3>
        <p class="subtle">Group songs in order for practice nights, gigs, worship sets, or rehearsals.</p>
    </div>
    <a class="button icon-button primary" href="?action=new-setlist" aria-label="Create setlist" title="Create Setlist">
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M12 5v14M5 12h14" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
    </a>
</header>
<div class="cards cards-library">
    <?php foreach ($setlists as $setlist): ?>
        <article class="card song-card">
            <div class="song-card-top">
                <div>
                    <div class="eyebrow">Setlist</div>
                    <h4><a href="?action=view-setlist&id=<?= (int)$setlist['id'] ?>"><?= h($setlist['name']) ?></a></h4>
                    <p class="artist-line"><?= h($setlist['notes']) ?: 'No notes' ?></p>
                </div>
            </div>
            <div class="actions icon-actions">
                <a class="button icon-button primary" href="?action=view-setlist&id=<?= (int)$setlist['id'] ?>" aria-label="Open <?= h($setlist['name']) ?>" title="Open">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M5 4h11l3 3v13a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Zm8 1.5V8h2.5L13 5.5ZM7 12v2h8v-2H7Zm0 4v2h6v-2H7Z" fill="currentColor"/>
                    </svg>
                </a>
                <a class="button icon-button ghost" href="?action=edit-setlist&id=<?= (int)$setlist['id'] ?>" aria-label="Edit <?= h($setlist['name']) ?>" title="Edit">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="m16.86 3.49 3.65 3.65-10.9 10.9L5 19l.96-4.61 10.9-10.9Zm-9.29 12.3-.39 1.82 1.82-.39 9.75-9.75-1.43-1.43-9.75 9.75Z" fill="currentColor"/>
                    </svg>
                </a>
                <a
                    class="button icon-button danger"
                    href="?action=delete-setlist&id=<?= (int)$setlist['id'] ?>"
                    data-confirm-delete
                    data-confirm-title="Delete <?= h($setlist['name']) ?>?"
                    data-confirm-message="This will permanently remove this setlist and its saved song order."
                    data-confirm-action="Delete Setlist"
                    aria-label="Delete <?= h($setlist['name']) ?>"
                    title="Delete"
                >
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M9 3h6l1 2h4v2H4V5h4l1-2Zm-1 6h2v8H8V9Zm6 0h2v8h-2V9ZM6 9h12l-1 11a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2L6 9Z" fill="currentColor"/>
                    </svg>
                </a>
            </div>
        </article>
    <?php endforeach; ?>
    <?php if (!$setlists): ?>
        <div class="empty glass-panel">
            <h4>No setlists yet</h4>
            <p class="subtle">Create one to organize your songs in the exact order you want.</p>
        </div>
    <?php endif; ?>
</div>
