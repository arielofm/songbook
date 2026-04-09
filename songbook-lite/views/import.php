<?php $message = trim($_GET['message'] ?? ''); ?>
<?php if ($message !== ''): ?>
    <div class="notice success"><?= h($message) ?></div>
<?php endif; ?>

<section class="import-grid import-grid-double">
    <div class="glass-panel form-panel">
        <div class="eyebrow">Paste a song</div>
        <h3>Quick add from your browser or notes</h3>
        <p class="subtle">Paste a messy chord sheet, plain lyrics with chord lines, or SongbookPro-style chord-over-lyrics text. SongShelf will identify chords, detect sections and metadata, then clean the result before saving.</p>
        <form method="post" action="?action=paste-song" class="stack-form">
            <div class="grid two">
                <label>Title<input type="text" name="title" placeholder="Leave blank if already inside the pasted text"></label>
                <label>Artist<input type="text" name="artist" placeholder="Optional"></label>
                <label>Key<input type="text" name="key_name" placeholder="Optional"></label>
                <label>Capo<input type="number" name="capo" min="0" max="12" placeholder="0"></label>
            </div>
            <label>Tags<input type="text" name="tags" placeholder="mass, worship, entrance"></label>
            <label>Pasted song content
                <textarea name="raw_song" rows="18" class="editor" placeholder="Example:\nAmazing Grace\nKey: G\n\nVerse 1\nG            D\nAmazing grace how sweet the sound\n\nor\n(Em) Lord I come, (C) I confess"></textarea>
            </label>
            <div class="actions">
                <button type="submit" class="button primary">Analyze, Convert, and Save</button>
            </div>
        </form>
    </div>

    <div class="glass-panel form-panel">
        <div class="eyebrow">Import files</div>
        <h3>Bring in your chord files</h3>
        <p class="subtle">Supported in this build: .txt, .pro, .cho, .chopro, .onsong, .crd, and .pdf files. Text imports go through the same chord-identification cleanup as pasted songs. PDFs stay intact and are shown inside the app as rendered pages.</p>
        <form method="post" action="?action=import" enctype="multipart/form-data" class="stack-form">
            <label class="upload-dropzone">Select one or more files
                <input type="file" name="songs[]" multiple accept=".txt,.pro,.cho,.chopro,.onsong,.crd,.pdf,application/pdf">
            </label>
            <button type="submit" class="button primary">Import Files</button>
        </form>
    </div>
</section>
