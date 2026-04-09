<?php
$saved = !empty($_GET['saved']);
$trainedFlag = $_GET['trained'] ?? '';
$trainingSaved = $trainedFlag === '1';
$trainingFailed = $trainedFlag === '0';
$assistantInput = (string)($assistantInput ?? '');
$assistantTask = (string)($assistantTask ?? 'clean');
$assistantUseTraining = isset($assistantUseTraining) ? (bool)$assistantUseTraining : true;
$assistantResult = $assistantResult ?? null;
$assistantChatHistory = is_array($assistantChatHistory ?? null) ? $assistantChatHistory : [];
$assistantTrainingExamples = is_array($assistantTrainingExamples ?? null) ? $assistantTrainingExamples : [];
?>

<?php if ($saved): ?>
    <div class="notice success">Assistant memory updated.</div>
<?php endif; ?>
<?php if ($trainingSaved): ?>
    <div class="notice success">Training example saved.</div>
<?php endif; ?>
<?php if ($trainingFailed): ?>
    <div class="notice">Training example not saved. Add both input and output.</div>
<?php endif; ?>

<header class="section-head">
    <div class="library-header-copy">
        <div class="eyebrow">In-App AI</div>
        <h1>Local <span class="library-title-accent">Juan</span></h1>
        <p class="subtle">Run real local AI workflows for cleanup, chord detection, key inference, and trainable corrections.</p>
    </div>
    <div class="actions">
        <a class="button icon-button ghost" href="?action=settings" aria-label="Open settings" title="Open settings">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M12 8.75A3.25 3.25 0 1 0 12 15.25A3.25 3.25 0 0 0 12 8.75Z" fill="none" stroke="currentColor" stroke-width="1.8"/>
                <path d="M19 12A7 7 0 0 0 18.89 10.75L20.5 9.5L19 7L17 7.5A7.1 7.1 0 0 0 15.25 6.45L14.5 4.5H9.5L8.75 6.45A7.1 7.1 0 0 0 7 7.5L5 7L3.5 9.5L5.11 10.75A7 7 0 0 0 5 12C5 12.43 5.04 12.85 5.11 13.25L3.5 14.5L5 17L7 16.5C7.53 16.93 8.11 17.29 8.75 17.55L9.5 19.5H14.5L15.25 17.55C15.89 17.29 16.47 16.93 17 16.5L19 17L20.5 14.5L18.89 13.25C18.96 12.85 19 12.43 19 12Z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
            </svg>
        </a>
    </div>
</header>

<section class="glass-panel ai-chat-shell">
    <div class="ai-panel-head">
        <div>
            <div class="eyebrow">Chat</div>
            <h4>Juan</h4>
        </div>
        <a class="button ghost" href="?action=ai-chat-reset">Clear Chat</a>
    </div>

    <div class="ai-chat-thread">
        <?php if ($assistantChatHistory === []): ?>
            <article class="ai-chat-bubble is-assistant">
                <p>Hi, I am Juan. I stay scoped to SongShelf. Ask me to search songs in your library, or paste a messy chord sheet and say "clean this". If you correct my output, I learn from that feedback.</p>
            </article>
        <?php else: ?>
            <?php foreach ($assistantChatHistory as $entry): ?>
                <?php
                $role = (string)($entry['role'] ?? 'assistant');
                $message = trim((string)($entry['message'] ?? ''));
                $meta = is_array($entry['meta'] ?? null) ? $entry['meta'] : [];
                $outputText = trim((string)($meta['output'] ?? ''));
                ?>
                <?php if ($message !== ''): ?>
                    <article class="ai-chat-bubble <?= $role === 'user' ? 'is-user' : 'is-assistant' ?>">
                        <p><?= nl2br(h($message)) ?></p>
                        <?php if ($role === 'assistant' && $outputText !== ''): ?>
                            <pre class="ai-chat-output"><?= h($outputText) ?></pre>
                        <?php endif; ?>
                    </article>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <form method="post" action="?action=ai-chat" class="stack-form ai-chat-form">
        <label>Your message
            <textarea name="assistant_message" rows="4" placeholder="Examples: 'Search: Amazing Grace', 'Clean this chord sheet:', or 'Remember: keep section labels short'"></textarea>
        </label>
        <div class="actions">
            <button type="submit" class="button primary">Send</button>
        </div>
    </form>
</section>

<section class="ai-assistant-dashboard">
    <article class="glass-panel ai-dashboard-hero">
        <div class="ai-dashboard-hero-copy">
            <div class="eyebrow">Assistant Core</div>
            <h3>Actual in-app AI workflow</h3>
            <p class="subtle">Ask the assistant to clean songs, detect chords, infer keys, or structure messy text. Teach corrections and it will reuse those patterns in future runs.</p>
        </div>
        <a class="assistant-fab assistant-fab-orb assistant-fab-orb-inline" href="?action=ai-assistant" aria-label="Open Juan" title="Open Juan">
            <span class="assistant-orb-wrap" aria-hidden="true">
                <span class="assistant-orb-core"></span>
                <span class="assistant-orb-halo"></span>
                <span class="assistant-orb-ring assistant-orb-ring-a"></span>
                <span class="assistant-orb-ring assistant-orb-ring-b"></span>
            </span>
            <span class="assistant-fab-label">Juan</span>
        </a>
    </article>

    <div class="ai-dashboard-row ai-dashboard-row-tools ai-workbench-grid">
        <article class="glass-panel ai-dashboard-panel">
            <div class="ai-panel-head">
                <div>
                    <div class="eyebrow">Ask</div>
                    <h4>Assistant Workbench</h4>
                </div>
                <span class="pill ai-capability-pill">Live local run</span>
            </div>

            <form method="post" action="?action=ai-run" class="stack-form ai-memory-form">
                <div class="grid two ai-task-row">
                    <label>Task
                        <select name="assistant_task">
                            <option value="clean" <?= $assistantTask === 'clean' ? 'selected' : '' ?>>Clean song text</option>
                            <option value="structure" <?= $assistantTask === 'structure' ? 'selected' : '' ?>>Structure sections + metadata</option>
                            <option value="detect_chords" <?= $assistantTask === 'detect_chords' ? 'selected' : '' ?>>Detect chords</option>
                            <option value="infer_key" <?= $assistantTask === 'infer_key' ? 'selected' : '' ?>>Infer key</option>
                            <option value="coach" <?= $assistantTask === 'coach' ? 'selected' : '' ?>>Rehearsal coaching hints</option>
                        </select>
                    </label>
                    <label class="ai-training-toggle">
                        <span>Use learned training examples</span>
                        <input type="checkbox" name="use_training" value="1" <?= $assistantUseTraining ? 'checked' : '' ?>>
                    </label>
                </div>
                <label>Input text
                    <textarea name="assistant_input" rows="12" placeholder="Paste song text, lyrics with chords, or rough rehearsal draft..."><?= h($assistantInput) ?></textarea>
                </label>
                <div class="actions">
                    <button type="submit" class="button primary">Run Assistant</button>
                </div>
            </form>
        </article>

        <article class="glass-panel ai-dashboard-panel">
            <div class="ai-panel-head">
                <div>
                    <div class="eyebrow">Train</div>
                    <h4>Teach New Behavior</h4>
                </div>
                <span class="pill ai-capability-pill">On-device learning</span>
            </div>
            <form method="post" action="?action=ai-train" class="stack-form ai-memory-form">
                <div class="grid two ai-task-row">
                    <label>Training mode
                        <select name="training_type">
                            <option value="replacement">Correction (replace input with output)</option>
                            <option value="pair">Example pair (input to desired output)</option>
                        </select>
                    </label>
                    <label>Label
                        <input type="text" name="training_label" placeholder="e.g. chord typo fix">
                    </label>
                </div>
                <label>Input / wrong pattern
                    <textarea name="training_input" rows="4" placeholder="Example: AsEsA/D/D"></textarea>
                </label>
                <label>Output / desired pattern
                    <textarea name="training_output" rows="4" placeholder="Example: Asus4/D"></textarea>
                </label>
                <div class="actions">
                    <button type="submit" class="button primary">Save Training Example</button>
                </div>
            </form>
        </article>
    </div>

    <?php if (is_array($assistantResult)): ?>
        <article class="glass-panel ai-dashboard-panel ai-output-panel">
            <div class="ai-panel-head">
                <div>
                    <div class="eyebrow">Result</div>
                    <h4>Assistant Output</h4>
                </div>
                <span class="pill ai-capability-pill"><?= h(strtoupper((string)($assistantResult['task'] ?? ''))) ?></span>
            </div>
            <p class="subtle"><?= h((string)($assistantResult['summary'] ?? '')) ?></p>
            <div class="ai-output-metrics">
                <span class="pill">Detected key: <?= h((string)($assistantResult['detected_key'] ?? 'Unknown')) ?: 'Unknown' ?></span>
                <span class="pill">Chords: <?= count($assistantResult['detected_chords'] ?? []) ?></span>
                <?php if (!empty($assistantResult['matched_example']['label'])): ?>
                    <span class="pill">Matched training: <?= h((string)$assistantResult['matched_example']['label']) ?></span>
                <?php endif; ?>
            </div>
            <pre class="ai-output-block"><?= h((string)($assistantResult['output'] ?? '')) ?></pre>
        </article>
    <?php endif; ?>

    <article class="glass-panel ai-memory-panel">
        <div class="ai-panel-head">
            <div>
                <div class="eyebrow">Memory</div>
                <h4>Persistent Assistant Context</h4>
            </div>
            <span class="pill ai-capability-pill">Saved on device</span>
        </div>
        <form method="post" action="?action=save-ai-memory" class="stack-form ai-memory-form">
            <div class="ai-memory-grid">
                <label>Custom instructions
                    <textarea name="custom_instructions" rows="5" placeholder="Example: Keep section names short and worship-team friendly."><?= h($assistantMemory['custom_instructions'] ?? '') ?></textarea>
                </label>
                <label>Preferred terms / tags
                    <input type="text" name="preferred_terms" value="<?= h($assistantMemory['preferred_terms'] ?? '') ?>" placeholder="worship, communion, youth">
                </label>
            </div>
            <label>Repertoire notes
                <textarea name="repertoire_notes" rows="5" placeholder="Example: Usually played in G or D; keep capo visibility clear."><?= h($assistantMemory['repertoire_notes'] ?? '') ?></textarea>
            </label>
            <div class="actions">
                <button type="submit" class="button primary">Save Assistant Memory</button>
            </div>
        </form>
    </article>

    <article class="glass-panel ai-dashboard-panel">
        <div class="ai-panel-head">
            <div>
                <div class="eyebrow">Knowledge</div>
                <h4>Recent Training Examples</h4>
            </div>
            <span class="pill ai-capability-pill"><?= count($assistantTrainingExamples) ?> loaded</span>
        </div>
        <?php if ($assistantTrainingExamples === []): ?>
            <p class="subtle">No training examples yet. Add one from the Train panel above.</p>
        <?php else: ?>
            <div class="ai-training-list">
                <?php foreach ($assistantTrainingExamples as $example): ?>
                    <article class="ai-training-item">
                        <strong><?= h((string)($example['label'] ?: strtoupper((string)($example['example_type'] ?? 'pair')))) ?></strong>
                        <p class="subtle">Input: <?= h(mb_strimwidth((string)($example['input_text'] ?? ''), 0, 72, '...')) ?></p>
                        <p class="subtle">Output: <?= h(mb_strimwidth((string)($example['output_text'] ?? ''), 0, 72, '...')) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </article>
</section>
