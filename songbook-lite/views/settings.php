<section class="settings-shell">
  <header class="settings-topbar">
    <div>
      <p class="settings-kicker">Settings</p>
      <h1>Control Center</h1>
      <p class="settings-intro">
        Configure interface, playback, appearance, behavior, and local data preferences.
      </p>
    </div>

    <div class="settings-topbar-actions">
      <button type="submit" class="settings-save-icon-button" form="settingsForm" aria-label="Save settings" title="Save settings">
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <path d="M6 4.5H16.6L19.5 7.4V19.5H4.5V6A1.5 1.5 0 0 1 6 4.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
          <path d="M8 4.5V10H15V4.5" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
          <path d="M8 15.5H16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
      </button>
    </div>
  </header>

  <div class="settings-layout">
    <aside class="settings-sidebar">
      <nav class="settings-nav">
        <a href="#interface" class="active">Interface</a>
        <a href="#playback">Playback</a>
        <a href="#appearance">Appearance</a>
        <a href="#behavior">Behavior</a>
        <a href="#data">Data & Backup</a>
      </nav>

      <div class="settings-note-card">
        <span class="badge">System scope</span>
        <p>
          Changes here affect local app experience, viewer defaults, and rehearsal preferences.
        </p>
      </div>
    </aside>

    <main class="settings-content">
      <form class="settings-stack" id="settingsForm" data-settings-form>
        <section class="settings-card" id="interface">
          <div class="section-heading">
            <div>
              <p class="section-eyebrow">Interface</p>
              <h2>Theme and global view mode</h2>
              <p>Choose the visual foundation and default browsing experience.</p>
            </div>
            <span class="badge">Global</span>
          </div>

          <div class="theme-picker">
            <label class="theme-option">
              <input type="radio" name="settings_theme" value="light" data-setting-field="theme">
              <span>
                <strong>Light</strong>
                <small>Bright workspace for daytime use</small>
              </span>
            </label>

            <label class="theme-option">
              <input type="radio" name="settings_theme" value="dark" data-setting-field="theme">
              <span>
                <strong>Dark</strong>
                <small>Low-glare interface for stage and focus work</small>
              </span>
            </label>

            <label class="theme-option">
              <input type="radio" name="settings_theme" value="system" data-setting-field="theme">
              <span>
                <strong>System</strong>
                <small>Match operating system preference</small>
              </span>
            </label>
          </div>

          <div class="field-grid two-col">
            <label class="field">
              <span>Performance mode</span>
              <select data-setting-field="performanceMode">
                <option value="lite">Lite</option>
                <option value="auto">Auto</option>
                <option value="full">Full visuals</option>
              </select>
            </label>

            <label class="field">
              <span>Default library view</span>
              <select data-setting-field="libraryView">
                <option value="cards">Cards</option>
                <option value="list">List</option>
              </select>
            </label>
          </div>
        </section>

        <section class="settings-card" id="playback">
          <div class="section-heading">
            <div>
              <p class="section-eyebrow">Playback</p>
              <h2>Viewer defaults</h2>
              <p>Set your preferred reading size and automatic scroll behavior.</p>
            </div>
            <span class="badge">Readers</span>
          </div>

          <div class="range-list">
            <label class="range-field">
              <div class="range-label-row">
                <span>Default song font size</span>
                <strong data-setting-output="songFontSize">18px</strong>
              </div>
              <input type="range" min="14" max="28" value="18" data-range="songFontSize" data-suffix="px" data-setting-field="songFontSize">
            </label>

            <label class="range-field">
              <div class="range-label-row">
                <span>Song auto-scroll speed</span>
                <strong data-setting-output="songScrollSpeed">4</strong>
              </div>
              <input type="range" min="1" max="14" value="4" data-range="songScrollSpeed" data-setting-field="songScrollSpeed">
            </label>

            <label class="range-field">
              <div class="range-label-row">
                <span>PDF auto-scroll speed</span>
                <strong data-setting-output="pdfScrollSpeed">1.6</strong>
              </div>
              <input type="range" min="0.2" max="8" step="0.2" value="1.6" data-range="pdfScrollSpeed" data-setting-field="pdfScrollSpeed">
            </label>
          </div>
        </section>

        <section class="settings-card" id="appearance">
          <div class="section-heading">
            <div>
              <p class="section-eyebrow">Appearance</p>
              <h2>Song sheet visual profile</h2>
              <p>Adjust type, colors, and readability for rehearsal and presentation.</p>
            </div>
            <span class="badge">Visuals</span>
          </div>

          <div class="field-grid one-col">
            <label class="field">
              <span>Preferred song font</span>
              <select data-setting-field="songFontFamily">
                <option value="mono">Stage Mono</option>
                <option value="serif">Classic Serif</option>
                <option value="stage">Modern Sans</option>
              </select>
            </label>
          </div>

          <div class="color-grid">
            <label class="color-field">
              <span>Lyrics</span>
              <input type="color" value="#f9fbff" data-setting-field="lyricsColor">
            </label>
            <label class="color-field">
              <span>Chord</span>
              <input type="color" value="#f7d774" data-setting-field="chordColor">
            </label>
            <label class="color-field">
              <span>Title</span>
              <input type="color" value="#f3f7ff" data-setting-field="titleColor">
            </label>
            <label class="color-field">
              <span>Artist</span>
              <input type="color" value="#9ad2ff" data-setting-field="artistColor">
            </label>
            <label class="color-field">
              <span>Section label</span>
              <input type="color" value="#86efac" data-setting-field="sectionColor">
            </label>
            <label class="color-field">
              <span>Background</span>
              <input type="color" value="#0a1321" data-setting-field="songBackground">
            </label>
          </div>
        </section>

        <section class="settings-card" id="behavior">
          <div class="section-heading">
            <div>
              <p class="section-eyebrow">Behavior</p>
              <h2>Navigation and rehearsal mode</h2>
              <p>Choose how the app behaves during reading, focus mode, and PDF viewing.</p>
            </div>
            <span class="badge">Device</span>
          </div>

          <div class="toggle-list">
            <label class="toggle-row">
              <div>
                <strong>Remember focus mode on song pages</strong>
                <small>Restore previous focus mode automatically.</small>
              </div>
              <input type="checkbox" data-setting-field="rememberFocusMode">
            </label>

            <label class="toggle-row">
              <div>
                <strong>Show focus helper side panels</strong>
                <small>Keep contextual controls visible while rehearsing.</small>
              </div>
              <input type="checkbox" data-setting-field="focusPanels">
            </label>

            <label class="toggle-row">
              <div>
                <strong>Open PDFs in night mode by default</strong>
                <small>Improve readability in low-light environments.</small>
              </div>
              <input type="checkbox" data-setting-field="pdfNightMode">
            </label>
          </div>
        </section>

        <section class="settings-card" id="data">
          <div class="section-heading">
            <div>
              <p class="section-eyebrow">Data hub</p>
              <h2>Import, export, and backup</h2>
              <p>Manage content portability and keep your workspace protected.</p>
            </div>
            <span class="badge">Local data</span>
          </div>

          <div class="action-grid">
            <a href="?action=import" class="action-tile">
              <strong>Import Songs</strong>
              <span>Bring songs into your local library.</span>
            </a>

            <a href="?action=export-library" class="action-tile">
              <strong>Export Library JSON</strong>
              <span>Download your current library structure.</span>
            </a>

            <a href="?action=backup-app" class="action-tile">
              <strong>Download Secure Backup</strong>
              <span>Save a full local snapshot of your preferences.</span>
            </a>

            <a href="?action=ai-assistant" class="action-tile">
              <strong>Open Juan</strong>
              <span>Get help configuring the workspace.</span>
            </a>
          </div>
        </section>

        <div class="form-footer">
          <button type="button" class="btn btn-muted" id="resetBtn" data-settings-reset>Reset Defaults</button>
          <p class="form-status" id="formStatus" data-settings-status aria-live="polite"></p>
        </div>
      </form>
    </main>
  </div>
</section>
