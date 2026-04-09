<?php
$selected = $selected ?? [];
$selectedMap = array_fill_keys(array_map('intval', $selected), true);
$selectedCount = count($selected);
?>
<section class="editor-layout setlist-builder-layout">
    <div class="glass-panel form-panel setlist-builder-panel">
        <div class="eyebrow"><?= (int)$setlist['id'] > 0 ? 'Edit' : 'Create' ?></div>
        <h3><?= (int)$setlist['id'] > 0 ? 'Build Setlist' : 'Build New Setlist' ?></h3>
        <form method="post" action="?action=save-setlist" class="stack-form" data-setlist-form>
            <input type="hidden" name="id" value="<?= (int)$setlist['id'] ?>">
            <div data-setlist-hidden-inputs>
                <?php foreach ($selected as $songId): ?>
                    <input type="hidden" name="song_ids[]" value="<?= (int)$songId ?>">
                <?php endforeach; ?>
            </div>
            <div class="grid two">
                <label>Name<input type="text" name="name" value="<?= h($setlist['name']) ?>" required></label>
                <label>Notes<textarea name="notes" rows="4"><?= h($setlist['notes']) ?></textarea></label>
            </div>

            <section class="setlist-picker-shell">
                <div class="setlist-picker-head">
                    <div>
                        <div class="eyebrow">Song Selection</div>
                        <h4>Choose songs for this setlist</h4>
                        <p class="subtle">Use the circular selector to include songs. Card and list views stay synced automatically.</p>
                    </div>
                    <div class="setlist-picker-tools">
                        <span class="pill setlist-selected-pill" data-setlist-selected-count><?= $selectedCount ?> selected</span>
                        <div class="view-toggle glass-panel compact-panel" role="group" aria-label="Song picker layout">
                            <button
                                type="button"
                                class="button icon-button ghost is-active"
                                data-setlist-picker-view="cards"
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
                                data-setlist-picker-view="list"
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

                <div class="setlist-selection-panels">
                    <div class="setlist-selection-grid" data-setlist-picker-panel="cards">
                        <?php foreach ($songs as $song): ?>
                            <?php $songId = (int)$song['id']; ?>
                            <?php $isSelected = isset($selectedMap[$songId]); ?>
                            <label class="setlist-song-option card<?= $isSelected ? ' is-selected' : '' ?>" data-setlist-song-option>
                                <input
                                    class="setlist-song-input"
                                    type="checkbox"
                                    value="<?= $songId ?>"
                                    data-song-id="<?= $songId ?>"
                                    <?= $isSelected ? 'checked' : '' ?>
                                >
                                <span class="setlist-song-radio" aria-hidden="true" title="Select song">
                                    <span class="setlist-song-radio-dot"></span>
                                </span>
                                <span class="setlist-song-body">
                                    <strong><?= h($song['title']) ?></strong>
                                    <span><?= h($song['artist']) ?: 'Unknown artist' ?></span>
                                    <span class="setlist-song-tags"><?= h($song['tags']) ?: 'No tags' ?></span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <div class="setlist-selection-list library-panel" data-setlist-picker-panel="list" hidden>
                        <?php foreach ($songs as $song): ?>
                            <?php $songId = (int)$song['id']; ?>
                            <?php $isSelected = isset($selectedMap[$songId]); ?>
                            <label class="setlist-song-row glass-panel<?= $isSelected ? ' is-selected' : '' ?>" data-setlist-song-option>
                                <input
                                    class="setlist-song-input"
                                    type="checkbox"
                                    value="<?= $songId ?>"
                                    data-song-id="<?= $songId ?>"
                                    <?= $isSelected ? 'checked' : '' ?>
                                >
                                <span class="setlist-song-radio" aria-hidden="true" title="Select song">
                                    <span class="setlist-song-radio-dot"></span>
                                </span>
                                <span class="setlist-song-row-copy">
                                    <strong><?= h($song['title']) ?></strong>
                                    <span><?= h($song['artist']) ?: 'Unknown artist' ?></span>
                                </span>
                                <span class="setlist-song-tags"><?= h($song['tags']) ?: 'No tags' ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <div class="actions">
                <button type="submit" class="button primary">Save Setlist</button>
                <a class="button ghost" href="?action=setlists">Cancel</a>
            </div>
        </form>
    </div>

    <aside class="glass-panel helper-panel">
        <div class="eyebrow">Workflow</div>
        <h4>Builder ready</h4>
        <p class="subtle">Choose songs here, then finalize running order on the setlist view page.</p>
        <p class="subtle">Open the top-bar lightbulb for page-specific tips.</p>
    </aside>
</section>
