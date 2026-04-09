<?php /** @var string $template */ ?>
<?php
$styleVersion = (string) @filemtime(APP_ROOT . '/assets/style.css');
$scriptVersion = (string) @filemtime(APP_ROOT . '/assets/app.js');
$tipsPayload = appTipsForTemplate($template);
$tipsTitle = (string)($tipsPayload['title'] ?? 'Quick Tips');
$tips = is_array($tipsPayload['tips'] ?? null) ? $tipsPayload['tips'] : [];
$assistantPopupHistory = function_exists('assistantChatHistory') ? array_slice(assistantChatHistory(), -24) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SongShelf v5.3.5</title>
    <script>
        (function () {
            try {
                var raw = window.localStorage.getItem('songshelf_settings');
                var settings = raw ? JSON.parse(raw) : {};
                var themeSetting = settings && typeof settings.theme === 'string' ? settings.theme : 'dark';
                var prefersDark = !!(window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
                var theme = themeSetting === 'light' || themeSetting === 'dark'
                    ? themeSetting
                    : (prefersDark ? 'dark' : 'light');
                var performanceMode = settings && typeof settings.performanceMode === 'string' ? settings.performanceMode : 'lite';
                document.documentElement.setAttribute('data-theme', theme);
                var lowMemory = typeof navigator.deviceMemory === 'number' && navigator.deviceMemory <= 4;
                var lowCpu = typeof navigator.hardwareConcurrency === 'number' && navigator.hardwareConcurrency <= 4;
                var saveData = !!(navigator.connection && navigator.connection.saveData);
                if (performanceMode === 'lite') {
                    document.documentElement.setAttribute('data-performance', 'lite');
                } else if (performanceMode === 'auto' && (lowMemory || lowCpu || saveData)) {
                    document.documentElement.setAttribute('data-performance', 'lite');
                }
            } catch (_error) {
                document.documentElement.setAttribute('data-theme', 'dark');
                document.documentElement.setAttribute('data-performance', 'lite');
            }
        }());
    </script>
    <link rel="stylesheet" href="/assets/style.css?v=<?= h($styleVersion) ?>">
    <script defer src="/assets/app.js?v=<?= h($scriptVersion) ?>"></script>
</head>
<body class="<?= h($template) ?>">
    <div class="app-shell">
        <aside class="sidebar" data-sidebar>
            <div class="brand-block">
                <img class="brand-mark" src="/assets/logo.png" alt="SongShelf logo">
                <div class="brand-copy">
                    <h1 class="brand-wordmark">SONG<span class="brand-wordmark-accent">Shelf</span></h1>
                    <p class="subtle">By John Ariel Rullan</p>
                    <p class="brand-code">1137-DTA-JARC.J-11008CML29</p>
                </div>
            </div>

            <nav class="main-nav">
                <a href="?action=songs">
                    <span class="nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M6 5.5H18M6 12H18M6 18.5H14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <span class="nav-label">Library</span>
                </a>
                <a href="?action=new-song">
                    <span class="nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M12 4V20M4 12H20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <span class="nav-label">Write Song</span>
                </a>
                <a href="?action=import">
                    <span class="nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M12 4V14M8.5 10.5L12 14L15.5 10.5M5 18.5H19" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span class="nav-label">Import & Paste</span>
                </a>
                <a href="?action=setlists">
                    <span class="nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M8.5 7H19M8.5 12H19M8.5 17H19M5 7H5.01M5 12H5.01M5 17H5.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <span class="nav-label">Setlists</span>
                </a>
                <a href="?action=chord-diagrams">
                    <span class="nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M6 6.5C6 5.67 6.67 5 7.5 5H16.5C17.33 5 18 5.67 18 6.5V17.5C18 18.33 17.33 19 16.5 19H7.5C6.67 19 6 18.33 6 17.5V6.5Z" stroke="currentColor" stroke-width="1.8"/>
                            <path d="M9 5V19M12 5V19M15 5V19M6 9.5H18M6 14.5H18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <span class="nav-label">Chord Diagram</span>
                </a>
                <a href="?action=settings">
                    <span class="nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M12 8.75A3.25 3.25 0 1 0 12 15.25A3.25 3.25 0 0 0 12 8.75Z" stroke="currentColor" stroke-width="1.8"/>
                            <path d="M19 12A7 7 0 0 0 18.89 10.75L20.5 9.5L19 7L17 7.5A7.1 7.1 0 0 0 15.25 6.45L14.5 4.5H9.5L8.75 6.45A7.1 7.1 0 0 0 7 7.5L5 7L3.5 9.5L5.11 10.75A7 7 0 0 0 5 12C5 12.43 5.04 12.85 5.11 13.25L3.5 14.5L5 17L7 16.5C7.53 16.93 8.11 17.29 8.75 17.55L9.5 19.5H14.5L15.25 17.55C15.89 17.29 16.47 16.93 17 16.5L19 17L20.5 14.5L18.89 13.25C18.96 12.85 19 12.43 19 12Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span class="nav-label">Settings</span>
                </a>
                <a href="?action=new-setlist">
                    <span class="nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M8.5 7H16.5M8.5 12H16.5M8.5 17H13M18 14V20M15 17H21M6 4.5H18C19.1046 4.5 20 5.39543 20 6.5V12M6 4.5C4.89543 4.5 4 5.39543 4 6.5V17.5C4 18.6046 4.89543 19.5 6 19.5H11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span class="nav-label">Build Setlist</span>
                </a>
            </nav>

            <?php if ($template === 'song-view'): ?>
                <?php require APP_ROOT . '/views/partials/metronome.php'; ?>
            <?php endif; ?>

            <footer class="sidebar-footer">
                <a class="sidebar-footer-name" href="?action=about-developer" title="About the developer">John Ariel Rullan</a>
                <div class="sidebar-footer-links">
                    <a href="#" aria-label="Facebook profile">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M14.5 8.5H16.8V5.5H14.1C11.6 5.5 10 7 10 9.7V12H7.5V15H10V21H13.2V15H16.2L16.7 12H13.2V10.1C13.2 9.1 13.7 8.5 14.5 8.5Z" fill="currentColor"/>
                        </svg>
                    </a>
                    <a href="#" aria-label="X profile">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5 4H8.7L12.6 9.6L17.1 4H19.4L13.6 11L19.8 20H16.1L11.9 14L6.8 20H4.5L10.8 12.5L5 4Z" fill="currentColor"/>
                        </svg>
                    </a>
                    <a href="#" aria-label="Instagram profile">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <rect x="4.5" y="4.5" width="15" height="15" rx="4" stroke="currentColor" stroke-width="1.8"/>
                            <circle cx="12" cy="12" r="3.5" stroke="currentColor" stroke-width="1.8"/>
                            <circle cx="17" cy="7.2" r="1" fill="currentColor"/>
                        </svg>
                    </a>
                    <a href="#" aria-label="TikTok profile">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M14 4C14.4 5.8 15.5 7 17.5 7.7V10.2C16.2 10.2 15 9.8 14 9V14.3C14 17.1 12 19 9.3 19C6.7 19 4.8 17 4.8 14.6C4.8 12 6.8 10.1 9.4 10.1C9.8 10.1 10.1 10.1 10.5 10.2V12.8C10.2 12.7 9.9 12.6 9.6 12.6C8.2 12.6 7.2 13.5 7.2 14.7C7.2 16 8.1 16.8 9.3 16.8C10.7 16.8 11.5 15.8 11.5 14.3V4H14Z" fill="currentColor"/>
                        </svg>
                    </a>
                    <a href="#" aria-label="LinkedIn profile">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M6.4 8.2C7.3 8.2 8 7.5 8 6.6C8 5.7 7.3 5 6.4 5C5.5 5 4.8 5.7 4.8 6.6C4.8 7.5 5.5 8.2 6.4 8.2Z" fill="currentColor"/>
                            <path d="M5.1 9.8H7.7V18.8H5.1V9.8Z" fill="currentColor"/>
                            <path d="M9.4 9.8H11.9V11C12.5 10.1 13.5 9.5 14.8 9.5C17.3 9.5 18.7 11.1 18.7 14V18.8H16.1V14.4C16.1 12.9 15.5 12 14.2 12C13 12 12 12.9 12 14.5V18.8H9.4V9.8Z" fill="currentColor"/>
                        </svg>
                    </a>
                </div>
            </footer>
        </aside>
        <main class="main-stage">
            <div class="topbar">
                <div class="topbar-head">
                    <button
                        type="button"
                        class="sidebar-toggle"
                        data-sidebar-toggle
                        aria-label="Collapse sidebar"
                        title="Collapse sidebar"
                    >
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M4.5 6.5H19.5M4.5 12H14.5M4.5 17.5H19.5" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>
                        </svg>
                    </button>
                    <div>
                        <div class="eyebrow">v5.3.5</div>
                        <!-- <h2 class="topbar-title">Your personal Song SetList Organizer</h2> -->
                    </div>
                </div>
                <div class="topbar-actions">
                    <div class="session-clock" data-session-clock aria-label="Session timer and current time">
                        <div class="session-clock-item">
                            <!-- <span class="session-clock-label">Timer</span> -->
                            <strong class="session-clock-value session-clock-value-elapsed" data-session-elapsed>00:00</strong>
                        </div>
                        <span class="session-clock-divider" aria-hidden="true"></span>
                        <div class="session-clock-item">
                            <!-- <span class="session-clock-label">Time</span> -->
                            <strong class="session-clock-value session-clock-value-time" data-system-time>--:--</strong>
                        </div>
                    </div>
                    <div class="topbar-menu" data-topbar-menu>
                        <button
                            type="button"
                            class="topbar-menu-toggle"
                            data-topbar-menu-toggle
                            aria-label="Open quick actions"
                            aria-haspopup="true"
                            aria-expanded="false"
                            title="Quick Actions"
                        >
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M4.5 6.5H19.5M4.5 12H19.5M4.5 17.5H19.5" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>
                            </svg>
                        </button>
                        <div class="topbar-menu-panel glass-panel" data-topbar-menu-panel hidden>
                            <button type="button" class="topbar-menu-item" data-tips-open aria-label="Open tips" title="Open tips">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M12 3.8C8.56 3.8 5.78 6.5 5.78 9.83c0 2.08 1.12 3.89 2.83 4.98.45.29.73.78.73 1.31v.48h5.32v-.48c0-.53.27-1.02.72-1.31 1.72-1.09 2.84-2.9 2.84-4.98 0-3.33-2.79-6.03-6.22-6.03Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                    <path d="M9.8 18.1h4.4M10.45 20.2h3.1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                                <span>Tips</span>
                            </button>
                            <button type="button" class="topbar-menu-item theme-toggle-button" data-theme-toggle aria-label="Switch to light mode" title="Switch to light mode">
                                <span class="theme-toggle-icon theme-toggle-icon-moon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none">
                                        <path d="M15.7 4.35A8.8 8.8 0 1 0 19.65 12a7 7 0 0 1-3.95-7.65Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <span class="theme-toggle-icon theme-toggle-icon-sun" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none">
                                        <circle cx="12" cy="12" r="4.2" stroke="currentColor" stroke-width="1.8"/>
                                        <path d="M12 3V5.2M12 18.8V21M3 12H5.2M18.8 12H21M5.64 5.64L7.2 7.2M16.8 16.8L18.36 18.36M5.64 18.36L7.2 16.8M16.8 7.2L18.36 5.64" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                </span>
                                <span>Theme</span>
                            </button>
                            <a class="topbar-menu-item" href="?action=settings" aria-label="Open settings" title="Open settings">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M12 8.75A3.25 3.25 0 1 0 12 15.25A3.25 3.25 0 0 0 12 8.75Z" stroke="currentColor" stroke-width="1.8"/>
                                    <path d="M19 12A7 7 0 0 0 18.89 10.75L20.5 9.5L19 7L17 7.5A7.1 7.1 0 0 0 15.25 6.45L14.5 4.5H9.5L8.75 6.45A7.1 7.1 0 0 0 7 7.5L5 7L3.5 9.5L5.11 10.75A7 7 0 0 0 5 12C5 12.43 5.04 12.85 5.11 13.25L3.5 14.5L5 17L7 16.5C7.53 16.93 8.11 17.29 8.75 17.55L9.5 19.5H14.5L15.25 17.55C15.89 17.29 16.47 16.93 17 16.5L19 17L20.5 14.5L18.89 13.25C18.96 12.85 19 12.43 19 12Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                                </svg>
                                <span>Settings</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <?php require APP_ROOT . '/views/' . $template . '.php'; ?>
        </main>
    </div>
    <?php if ($template !== 'ai-assistant'): ?>
        <a class="assistant-fab assistant-fab-mascot" href="?action=ai-assistant" data-assistant-open data-assistant-draggable aria-label="Open Juan" title="Open Juan">
            <span class="assistant-mascot" aria-hidden="true">
                <span class="assistant-mascot-shadow"></span>
                <span class="assistant-mascot-body">
                    <span class="assistant-mascot-antenna"></span>
                    <span class="assistant-mascot-ear assistant-mascot-ear-left"></span>
                    <span class="assistant-mascot-ear assistant-mascot-ear-right"></span>

                    <span class="assistant-mascot-face">
                        <span class="assistant-mascot-eye"></span>
                        <span class="assistant-mascot-eye"></span>
                    </span>

                    <span class="assistant-mascot-blush assistant-mascot-blush-left"></span>
                    <span class="assistant-mascot-blush assistant-mascot-blush-right"></span>
                    <span class="assistant-mascot-mouth"></span>
                </span>
            </span>
        </a>

        <div class="assistant-chat-modal" data-assistant-popup hidden>
            <div class="assistant-chat-backdrop" data-assistant-close></div>
            <section class="assistant-chat-panel glass-panel" data-assistant-panel role="dialog" aria-modal="true" aria-labelledby="assistant-chat-title">
                <header class="assistant-chat-head">
                    <div>
                        <div class="eyebrow">In-App AI</div>
                        <h3 id="assistant-chat-title">Juan</h3>
                    </div>
                    <div class="assistant-chat-head-actions">
                        <a class="button ghost assistant-chat-full-link" href="?action=ai-assistant">Full page</a>
                        <button type="button" class="button icon-button ghost" data-assistant-close aria-label="Close Juan chat" title="Close">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M6 6L18 18M18 6L6 18" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>
                </header>

                <div class="assistant-chat-thread" data-assistant-thread>
                    <?php if ($assistantPopupHistory === []): ?>
                        <article class="assistant-chat-bubble is-assistant">
                            <p>Hi, I am Juan. I can chat naturally, but I stay scoped to SongShelf. Ask me to find songs, clean chord sheets, or learn a preference.</p>
                        </article>
                    <?php else: ?>
                        <?php foreach ($assistantPopupHistory as $entry): ?>
                            <?php
                            $role = (string)($entry['role'] ?? 'assistant');
                            $message = trim((string)($entry['message'] ?? ''));
                            $meta = is_array($entry['meta'] ?? null) ? $entry['meta'] : [];
                            $output = trim((string)($meta['output'] ?? ''));
                            ?>
                            <?php if ($message !== ''): ?>
                                <article class="assistant-chat-bubble <?= $role === 'user' ? 'is-user' : 'is-assistant' ?>">
                                    <p><?= nl2br(h($message)) ?></p>
                                    <?php if ($role === 'assistant' && $output !== ''): ?>
                                        <pre class="assistant-chat-output"><?= h($output) ?></pre>
                                    <?php endif; ?>
                                </article>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <form class="assistant-chat-form" data-assistant-form method="post" action="?action=ai-chat">
                    <label class="assistant-chat-input-wrap">
                        <textarea
                            name="assistant_message"
                            data-assistant-input
                            rows="3"
                            placeholder="Ask about your SongShelf library. Press Enter to send, Shift+Enter for newline."
                            required
                        ></textarea>
                    </label>
                    <div class="assistant-chat-actions">
                        <button type="button" class="button ghost" data-assistant-clear>Clear</button>
                        <button type="submit" class="button primary assistant-chat-send" data-assistant-send aria-label="Send message" title="Send message">
                            <span>Send</span>
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M3.8 11.9L19.5 4.6C20 4.38 20.56 4.82 20.44 5.34L17.6 18.17C17.48 18.72 16.83 18.97 16.38 18.64L11.1 14.71L8.3 18.63C8.04 18.99 7.45 18.87 7.36 18.43L6.28 13.48L3.72 12.74C3.22 12.6 3.17 12.2 3.8 11.9Z" stroke="currentColor" stroke-width="1.45" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>
                </form>
            </section>
        </div>
    <?php endif; ?>
    <div class="confirm-modal" data-confirm-modal hidden>
        <div class="confirm-backdrop" data-confirm-cancel></div>
        <div class="confirm-panel glass-panel" role="dialog" aria-modal="true" aria-labelledby="confirm-title">
            <div class="eyebrow">Confirm action</div>
            <h3 id="confirm-title">Delete item?</h3>
            <p class="subtle confirm-copy" data-confirm-message>This action cannot be undone.</p>
            <div class="actions confirm-actions">
                <button type="button" class="button ghost" data-confirm-cancel>Cancel</button>
                <a class="button danger" href="#" data-confirm-accept>Delete</a>
            </div>
        </div>
    </div>
    <div class="tips-modal" data-tips-modal hidden>
        <div class="tips-backdrop" data-tips-close></div>
        <div class="tips-panel glass-panel" role="dialog" aria-modal="true" aria-labelledby="tips-title">
            <div class="tips-modal-orb" aria-hidden="true">
                <span class="tips-modal-orb-core"></span>
                <span class="tips-modal-orb-glow"></span>
            </div>
            <div class="eyebrow">Quick tips</div>
            <h3 id="tips-title"><?= h($tipsTitle) ?></h3>
            <ul class="sidebar-list tips-modal-list">
                <?php foreach ($tips as $tip): ?>
                    <li><?= h((string)$tip) ?></li>
                <?php endforeach; ?>
            </ul>
            <div class="actions confirm-actions">
                <button type="button" class="button ghost" data-tips-close>Close</button>
            </div>
        </div>
    </div>
</body>
</html>
