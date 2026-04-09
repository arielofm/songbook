<?php
$isPdfSong = songIsPdf($song);
$formBpm = sanitizeSongBpm($song['bpm'] ?? 0);
if ($formBpm < 40) {
    $formBpm = 92;
}
$formTimeSignature = normalizeSongTimeSignature((string)($song['time_signature'] ?? '4/4'));
$formScrollSpeed = sanitizeSongScrollSpeed($song['scroll_speed'] ?? 0, $isPdfSong);
$formScrollStep = $isPdfSong ? '0.2' : '1';
$formScrollMin = $isPdfSong ? '0.2' : '1';
$formScrollMax = $isPdfSong ? '8' : '14';
$formScrollDisplay = $isPdfSong
    ? number_format($formScrollSpeed, 1, '.', '')
    : (string)((int)round($formScrollSpeed));
?>
<section class="editor-layout">
    <div class="glass-panel form-panel">
        <div class="eyebrow"><?= (int)$song['id'] > 0 ? 'Edit' : 'Create' ?></div>
        <h3><?= (int)$song['id'] > 0 ? ($isPdfSong ? 'Edit PDF Details' : 'Edit Song') : 'New Song' ?></h3>
        <form method="post" action="?action=save-song" class="stack-form" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= (int)$song['id'] ?>">
            <div class="grid two">
                <label>Title<input type="text" name="title" value="<?= h($song['title']) ?>"></label>
                <label>Artist<input type="text" name="artist" value="<?= h($song['artist']) ?>"></label>
                <?php if (!$isPdfSong): ?>
                    <label>Original key<input type="text" name="key_name" value="<?= h($song['key_name']) ?>" placeholder="Auto-detected if left blank"></label>
                    <label>Capo<input type="number" name="capo" value="<?= (int)$song['capo'] ?>" min="0" max="12"></label>
                <?php endif; ?>
            </div>
            <label>Tags<input type="text" name="tags" value="<?= h($song['tags']) ?>" placeholder="worship, acoustic, practice"></label>
            <div class="grid two">
                <label>Tempo (BPM)<input type="number" name="bpm" min="40" max="240" step="1" value="<?= (int)$formBpm ?>"></label>
                <label>Time signature
                    <select name="time_signature">
                        <?php foreach (songAllowedTimeSignatures() as $signature): ?>
                            <option value="<?= h($signature) ?>" <?= $signature === $formTimeSignature ? 'selected' : '' ?>><?= h($signature) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Scroll speed
                    <input
                        type="number"
                        name="scroll_speed"
                        min="<?= h($formScrollMin) ?>"
                        max="<?= h($formScrollMax) ?>"
                        step="<?= h($formScrollStep) ?>"
                        value="<?= h($formScrollDisplay) ?>"
                    >
                </label>
            </div>
            <?php if ($isPdfSong): ?>
                <p class="subtle">This page edits PDF metadata only. The original file content stays unchanged.</p>
            <?php else: ?>
                <label>Song content (chords over lyrics)
                    <div class="song-composer-tools glass-panel" data-song-composer>
                        <div class="eyebrow">Composer tools</div>
                        <div class="song-composer-toolbar" role="toolbar" aria-label="Song formatting tools">
                            <button type="button" class="button ghost song-tool-button" data-song-tool="title">Title</button>
                            <button type="button" class="button ghost song-tool-button" data-song-tool="artist">Artist</button>
                            <button type="button" class="button ghost song-tool-button" data-song-tool="chord">Chord</button>
                            <button type="button" class="button ghost song-tool-button" data-song-tool="intro">Intro</button>
                            <button type="button" class="button ghost song-tool-button" data-song-tool="verse">Verse</button>
                            <button type="button" class="button ghost song-tool-button" data-song-tool="refrain">Refrain</button>
                            <button type="button" class="button ghost song-tool-button" data-song-tool="chorus">Chorus</button>
                            <button type="button" class="button ghost song-tool-button" data-song-tool="bridge">Bridge</button>
                            <button type="button" class="button ghost song-tool-button" data-song-tool="coda">Coda</button>
                        </div>
                        <p class="subtle song-composer-hint">Section buttons write real section labels into the song output, and the app auto-detects the original key if you leave it blank.</p>
                    </div>
                    <textarea name="body" rows="22" class="editor"><?= h($song['body']) ?></textarea>
                </label>
            <?php endif; ?>
            <div class="actions">
                <button type="submit" class="button primary"><?= $isPdfSong ? 'Save PDF Details' : 'Save Song' ?></button>
                <a class="button ghost" href="?action=songs">Cancel</a>
            </div>
        </form>
    </div>

    <aside class="glass-panel helper-panel">
        <?php if ($isPdfSong): ?>
            <div class="eyebrow">PDF entry</div>
            <h4>Metadata editor</h4>
            <p class="subtle">Update title, artist, and tags to keep PDF entries organized with the rest of your library.</p>
        <?php else: ?>
            <div class="eyebrow">Editor status</div>
            <h4>Composer tools ready</h4>
            <p class="subtle">Use the toolbar to insert directives and chord tokens quickly while writing.</p>
            <p class="subtle">Open the lightbulb icon in the top bar for page-specific formatting tips.</p>
        <?php endif; ?>
    </aside>
</section>
