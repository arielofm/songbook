<?php
$metronomePanelClass = $metronomePanelClass ?? 'sidebar-metronome';
$showFocusTimer = str_contains($metronomePanelClass, 'focus-side-metronome');
$metronomeBpm = 92;
$metronomeSignature = '4/4';
$allowedSignatures = songAllowedTimeSignatures();
if (isset($song) && is_array($song)) {
    $candidateBpm = sanitizeSongBpm($song['bpm'] ?? 0);
    if ($candidateBpm >= 40) {
        $metronomeBpm = $candidateBpm;
    }
    $metronomeSignature = normalizeSongTimeSignature((string)($song['time_signature'] ?? '4/4'));
}
?>
<section class="sidebar-panel metronome-panel <?= h($metronomePanelClass) ?>" data-metronome-panel>
    <div class="metronome-head">
        <div>
            <div class="eyebrow">Practice Clock</div>
            <h4>Metronome</h4>
            <!-- <p class="metronome-caption subtle">Precision pulse control for rehearsal and performance timing.</p> -->
        </div>
        <div class="metronome-transport">
            <button type="button" class="button primary" data-metronome-start>Start</button>
            <button type="button" class="button ghost" data-metronome-stop disabled>Stop</button>
        </div>
    </div>
    <div class="metronome-grid">
        <label class="metronome-field">
            <span>Tempo</span>
            <div class="metronome-control metronome-tempo-row">
                <input class="metronome-slider" type="range" min="40" max="240" step="1" value="<?= (int)$metronomeBpm ?>" data-metronome-bpm>
                <div class="metronome-number-wrap modern-input">
                    <input type="number" min="40" max="240" step="1" value="<?= (int)$metronomeBpm ?>" data-metronome-bpm-input>
                    <span>BPM</span>
                </div>
            </div>
        </label>
        <label class="metronome-field">
            <span>Time Signature</span>
            <div class="modern-input">
                <select data-metronome-signature>
                    <?php foreach ($allowedSignatures as $signature): ?>
                        <option value="<?= h($signature) ?>" <?= $signature === $metronomeSignature ? 'selected' : '' ?>><?= h($signature) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </label>
    </div>
    <div class="metronome-status-row">
        <div class="metronome-status-stack">
            <div class="metronome-pulse" data-metronome-pulse aria-live="polite">Stopped</div>
            <div class="metronome-marking" data-metronome-marking>Larghetto</div>
        </div>
        <div class="metronome-beats" data-metronome-beats></div>
    </div>
    <?php if ($showFocusTimer): ?>
        <div class="focus-session-timer" aria-label="Focus session timer">
            <span class="focus-session-timer-label">Timer</span>
            <strong data-focus-elapsed>00:00</strong>
        </div>
    <?php endif; ?>
</section>
