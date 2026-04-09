<?php
$instrument = 'piano';
$chords = $chords ?? chordLibrary('piano');
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

<header class="section-head">
    <div class="library-header-copy">
        <div class="eyebrow">Chord Diagram</div>
        <h1>Built-in <span class="library-title-accent">Piano Chords</span></h1>
        <p class="subtle">Browse piano chord diagrams across the full built-in root-and-quality set.</p>
    </div>
</header>

<section class="stats-strip chord-stats-strip">
    <article class="stat-card">
        <span class="stat-label">Diagrams</span>
        <strong><?= count($chords) ?></strong>
    </article>
    <article class="stat-card">
        <span class="stat-label">Showing</span>
        <strong>Piano only</strong>
    </article>
    <article class="stat-card">
        <span class="stat-label">Roots</span>
        <strong><?= count(chordLibraryRoots()) ?></strong>
    </article>
    <article class="stat-card">
        <span class="stat-label">Qualities</span>
        <strong><?= count(chordLibraryQualities()) ?></strong>
    </article>
</section>

<section class="chord-grid">
    <?php foreach ($chords as $chord): ?>
        <?php
        $diagram = $chord['diagram'];
        $activeKeys = array_flip($diagram['active_keys']);
        ?>
        <article class="card chord-card">
            <div class="chord-card-head">
                <div>
                    <div class="eyebrow">Piano chord</div>
                    <h3><?= h($chord['name']) ?></h3>
                    <p class="subtle chord-card-copy"><?= h($chord['quality_label']) ?></p>
                </div>
                <span class="chip chip-pdf">Piano</span>
            </div>

            <div class="piano-diagram" aria-label="<?= h($chord['name']) ?> piano chord diagram">
                <div class="piano-keys">
                    <div class="piano-white-keys">
                        <?php foreach ($whiteKeys as $key): ?>
                            <?php
                            $isActive = isset($activeKeys[$key['index']]);
                            $isRoot = $diagram['root_index'] === $key['index'];
                            ?>
                            <div class="piano-white-key<?= $isActive ? ' is-active' : '' ?><?= $isRoot ? ' is-root' : '' ?>">
                                <span><?= h($key['label']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php foreach ($blackKeys as $key): ?>
                        <?php
                        $isActive = isset($activeKeys[$key['index']]);
                        $isRoot = $diagram['root_index'] === $key['index'];
                        ?>
                        <div class="piano-black-key<?= $isActive ? ' is-active' : '' ?><?= $isRoot ? ' is-root' : '' ?>" style="left: calc(<?= $key['position'] ?> * 100%);">
                            <span><?= h($key['label']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="chord-note-list">
                    <?php foreach ($diagram['notes'] as $note): ?>
                        <span class="pill chord-note-pill"><?= h($note) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
</section>
