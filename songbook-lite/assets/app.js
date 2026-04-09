document.addEventListener('DOMContentLoaded', () => {
  const settingsStorageKey = 'songshelf_settings';
  const sheet = document.querySelector('[data-song-sheet]');
  const scrollToggles = document.querySelectorAll('[data-scroll-toggle], [data-focus-scroll-toggle]');
  const speedInput = document.querySelector('[data-scroll-speed]');
  const speedValue = document.querySelector('[data-scroll-speed-value]');
  const pdfSheet = document.querySelector('[data-pdf-sheet]');
  const pdfNightToggle = document.querySelector('[data-pdf-night-toggle]');
  const themeToggleButtons = document.querySelectorAll('[data-theme-toggle]');
  const fontButtons = document.querySelectorAll('[data-font-step]');
  const focusToggles = document.querySelectorAll('[data-toggle-focus]');
  const metronomePanels = document.querySelectorAll('[data-metronome-panel]');
  const metronomeStarts = document.querySelectorAll('[data-metronome-start]');
  const metronomeStops = document.querySelectorAll('[data-metronome-stop]');
  const focusMetronomeToggles = document.querySelectorAll('[data-focus-metronome-toggle]');
  const metronomeBpms = document.querySelectorAll('[data-metronome-bpm]');
  const metronomeBpmInputs = document.querySelectorAll('[data-metronome-bpm-input]');
  const metronomeSignatures = document.querySelectorAll('[data-metronome-signature]');
  const metronomePulses = document.querySelectorAll('[data-metronome-pulse]');
  const metronomeMarkings = document.querySelectorAll('[data-metronome-marking]');
  const metronomeBeatsGroups = document.querySelectorAll('[data-metronome-beats]');
  const elapsedClocks = document.querySelectorAll('[data-session-elapsed]');
  const focusElapsedClocks = document.querySelectorAll('[data-focus-elapsed]');
  const systemTimeClocks = document.querySelectorAll('[data-system-time]');
  const modal = document.querySelector('[data-confirm-modal]');
  const confirmButtons = document.querySelectorAll('[data-confirm-delete]');
  const modalTitle = document.querySelector('#confirm-title');
  const modalMessage = document.querySelector('[data-confirm-message]');
  const modalAccept = document.querySelector('[data-confirm-accept]');
  const modalCancels = document.querySelectorAll('[data-confirm-cancel]');
  const tipsModal = document.querySelector('[data-tips-modal]');
  const tipsOpenButtons = document.querySelectorAll('[data-tips-open]');
  const tipsCloseButtons = document.querySelectorAll('[data-tips-close]');
  const assistantPopup = document.querySelector('[data-assistant-popup]');
  const assistantOpenButtons = document.querySelectorAll('[data-assistant-open]');
  const assistantCloseButtons = document.querySelectorAll('[data-assistant-close]');
  const assistantChatPanel = document.querySelector('[data-assistant-panel]');
  const assistantChatThread = document.querySelector('[data-assistant-thread]');
  const assistantChatForm = document.querySelector('[data-assistant-form]');
  const assistantChatInput = document.querySelector('[data-assistant-input]');
  const assistantChatClearButton = document.querySelector('[data-assistant-clear]');
  const assistantChatSendButton = document.querySelector('[data-assistant-send]');
  const topbarMenu = document.querySelector('[data-topbar-menu]');
  const topbarMenuToggle = document.querySelector('[data-topbar-menu-toggle]');
  const topbarMenuPanel = document.querySelector('[data-topbar-menu-panel]');
  const librarySearchForm = document.querySelector('[data-library-search-form]');
  const librarySearchToggle = document.querySelector('[data-library-search-toggle]');
  const librarySearchInput = document.querySelector('[data-library-search-input]');
  const libraryViewLayout = document.querySelector('[data-library-layout]');
  const libraryPanels = document.querySelectorAll('[data-library-panel]');
  const libraryViewButtons = document.querySelectorAll('[data-library-view]');
  const settingsForm = document.querySelector('[data-settings-form]');
  const settingsFields = document.querySelectorAll('[data-setting-field]');
  const settingsOutputs = document.querySelectorAll('[data-setting-output]');
  const settingsReset = document.querySelector('[data-settings-reset]');
  const settingsStatus = document.querySelector('[data-settings-status]');
  const setlistForm = document.querySelector('[data-setlist-form]');
  const setlistHiddenInputs = document.querySelector('[data-setlist-hidden-inputs]');
  const setlistSongInputs = document.querySelectorAll('.setlist-song-input[data-song-id]');
  const setlistPickerPanels = document.querySelectorAll('[data-setlist-picker-panel]');
  const setlistPickerButtons = document.querySelectorAll('[data-setlist-picker-view]');
  const setlistSelectedCount = document.querySelector('[data-setlist-selected-count]');
  const setlistSortableList = document.querySelector('[data-setlist-sortable-list]');
  const setlistSortableItems = document.querySelectorAll('[data-setlist-sortable-item]');
  const aiBotCard = document.querySelector('.ai-assistant-card');
  const aiBotFigures = aiBotCard ? aiBotCard.querySelectorAll('[data-ai-bot-figure]') : [];
  const aiBotMessageTitle = aiBotCard ? aiBotCard.querySelector('[data-ai-bot-message-title]') : null;
  const aiBotMessageBody = aiBotCard ? aiBotCard.querySelector('[data-ai-bot-message-body]') : null;
  const aiBotEmotionPills = aiBotCard ? aiBotCard.querySelectorAll('[data-ai-bot-emotion-pill]') : [];
  const songBodyEditor = document.querySelector('textarea[name="body"]');
  const songTitleInput = document.querySelector('input[name="title"]');
  const songArtistInput = document.querySelector('input[name="artist"]');
  const songToolButtons = document.querySelectorAll('[data-song-tool]');
  const songForm = songBodyEditor ? songBodyEditor.closest('form') : null;
  const sidebar = document.querySelector('[data-sidebar]');
  const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
  const songPlaybackRoot = document.querySelector('[data-song-playback]');

  let timer = null;
  let sessionClockTimer = null;
  let aiBotMessageTimer = null;
  let lastFocusedElement = null;
  let metronomeAudioContext = null;
  let metronomeBuffers = null;
  let metronomeIsRunning = false;
  let metronomeCurrentBeat = 0;
  let metronomeScheduledBeat = 0;
  let metronomeNextNoteTime = 0;
  let metronomeSchedulerId = null;
  let metronomeVisualTimeouts = [];
  const metronomeScheduleAheadTime = 0.12;
  const metronomeLookaheadMs = 25;
  const metronomeState = {
    bpm: 92,
    signature: '4/4',
  };
  const metronomeSampleUrls = {
    accent: '/assets/metronome-accent.mp3',
    beat: '/assets/metronome-beat.mp3',
  };
  const pdfNightModeStorageKey = 'songshelf_pdf_night_mode';
  const libraryViewStorageKey = 'songshelf_library_view';
  const sidebarCollapsedStorageKey = 'songshelf_sidebar_collapsed';
  const assistantFabPositionStorageKey = 'songshelf_assistant_fab_position';
  const appSessionStartedAtStorageKey = 'songshelf_app_session_started_at';
  const defaultSettings = {
    theme: 'dark',
    performanceMode: 'lite',
    songFontSize: 18,
    songScrollSpeed: 4,
    pdfScrollSpeed: 1.6,
    songFontFamily: 'mono',
    lyricsColor: '#f9fbff',
    chordColor: '#f7d774',
    titleColor: '#f3f7ff',
    artistColor: '#9ad2ff',
    sectionColor: '#86efac',
    songBackground: '#0a1321',
    pdfNightMode: false,
    libraryView: 'cards',
    rememberFocusMode: false,
    focusPanels: true,
  };
  let activeDraggedItem = null;
  let sidebarCollapsed = false;
  let appSessionStartedAtMs = Date.now();
  let focusSessionStartedAtMs = null;
  let songPlaybackPersistTimer = null;
  const songPlaybackState = {
    songId: 0,
    isSongView: false,
    isPdf: Boolean(pdfSheet),
    bpm: 92,
    signature: '4/4',
    scrollSpeed: pdfSheet ? 1.6 : 4,
  };

  function readSettings() {
    try {
      const saved = JSON.parse(window.localStorage.getItem(settingsStorageKey) || '{}');
      return {
        ...defaultSettings,
        ...saved,
      };
    } catch (_error) {
      return { ...defaultSettings };
    }
  }

  function writeSettings(nextSettings) {
    try {
      window.localStorage.setItem(settingsStorageKey, JSON.stringify(nextSettings));
    } catch (_error) {
      // Ignore persistence failures.
    }
  }

  function readAppSessionStartedAt() {
    try {
      const raw = window.sessionStorage.getItem(appSessionStartedAtStorageKey);
      const parsed = raw ? Number(raw) : NaN;
      if (Number.isFinite(parsed) && parsed > 0) return parsed;
      const now = Date.now();
      window.sessionStorage.setItem(appSessionStartedAtStorageKey, String(now));
      return now;
    } catch (_error) {
      return Date.now();
    }
  }

  let appSettings = readSettings();
  appSessionStartedAtMs = readAppSessionStartedAt();

  function isSongPlaybackActive() {
    return songPlaybackState.isSongView && songPlaybackState.songId > 0;
  }

  function normalizeMetronomeSignature(signature) {
    const allowed = ['2/4', '3/4', '4/4', '5/4', '6/8'];
    return allowed.includes(String(signature || '').trim()) ? String(signature).trim() : '4/4';
  }

  function getSongScrollBounds() {
    if (songPlaybackState.isPdf) {
      return {
        min: 0.2,
        max: 8,
        step: 0.2,
        fallback: 1.6,
      };
    }
    return {
      min: 1,
      max: 14,
      step: 1,
      fallback: 4,
    };
  }

  function sanitizeSongScrollSpeedValue(nextValue) {
    const bounds = getSongScrollBounds();
    const parsed = Number(nextValue);
    const candidate = Number.isFinite(parsed) && parsed > 0 ? parsed : bounds.fallback;
    const clamped = clamp(candidate, bounds.min, bounds.max);
    return bounds.step < 1 ? Math.round(clamped * 10) / 10 : Math.round(clamped);
  }

  function parseSongPlaybackState() {
    if (!(songPlaybackRoot instanceof HTMLElement)) return;
    const songId = Number(songPlaybackRoot.dataset.songId || 0);
    if (!Number.isFinite(songId) || songId <= 0) return;
    songPlaybackState.songId = Math.round(songId);
    songPlaybackState.isSongView = true;
    songPlaybackState.isPdf = (songPlaybackRoot.dataset.songKind || '').toLowerCase() === 'pdf' || Boolean(pdfSheet);
    const parsedBpm = Number(songPlaybackRoot.dataset.songBpm || 0);
    songPlaybackState.bpm = clamp(
      Number.isFinite(parsedBpm) && parsedBpm > 0 ? parsedBpm : songPlaybackState.bpm,
      40,
      240,
    );
    songPlaybackState.signature = normalizeMetronomeSignature(songPlaybackRoot.dataset.songSignature || songPlaybackState.signature);
    songPlaybackState.scrollSpeed = sanitizeSongScrollSpeedValue(songPlaybackRoot.dataset.songScrollSpeed || songPlaybackState.scrollSpeed);
  }

  function persistSongPlaybackState() {
    if (!isSongPlaybackActive() || typeof window.fetch !== 'function') return Promise.resolve();
    return window.fetch('?action=save-song-playback', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
      },
      body: new URLSearchParams({
        id: String(songPlaybackState.songId),
        bpm: String(Math.round(songPlaybackState.bpm)),
        time_signature: songPlaybackState.signature,
        scroll_speed: String(songPlaybackState.scrollSpeed),
      }).toString(),
    }).then((response) => response.json())
      .then((payload) => {
        if (!payload || payload.ok !== true) return;
        if (Number.isFinite(Number(payload.bpm))) {
          songPlaybackState.bpm = clamp(Number(payload.bpm), 40, 240);
        }
        songPlaybackState.signature = normalizeMetronomeSignature(payload.time_signature || songPlaybackState.signature);
        songPlaybackState.scrollSpeed = sanitizeSongScrollSpeedValue(payload.scroll_speed);
        metronomeState.bpm = songPlaybackState.bpm;
        metronomeState.signature = songPlaybackState.signature;
        if (speedInput) {
          speedInput.value = String(songPlaybackState.scrollSpeed);
          updateSpeedValue();
        }
        syncMetronomeControls();
      })
      .catch(() => {
        // Ignore persistence failures in view controls.
      });
  }

  function queueSongPlaybackPersist() {
    if (!isSongPlaybackActive()) return;
    if (songPlaybackPersistTimer) {
      window.clearTimeout(songPlaybackPersistTimer);
    }
    songPlaybackPersistTimer = window.setTimeout(() => {
      songPlaybackPersistTimer = null;
      persistSongPlaybackState();
    }, 240);
  }

  parseSongPlaybackState();

  function normalizeTheme(theme) {
    if (theme === 'light' || theme === 'dark' || theme === 'system') return theme;
    return defaultSettings.theme;
  }

  function resolveTheme(theme) {
    const normalizedTheme = normalizeTheme(theme);
    if (normalizedTheme === 'system') {
      const prefersDark = Boolean(window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
      return prefersDark ? 'dark' : 'light';
    }
    return normalizedTheme;
  }

  function normalizePerformanceMode(mode) {
    return ['lite', 'auto', 'full'].includes(mode) ? mode : 'lite';
  }

  function getAutoLitePerformance() {
    const lowMemory = typeof navigator.deviceMemory === 'number' && navigator.deviceMemory <= 4;
    const lowCpu = typeof navigator.hardwareConcurrency === 'number' && navigator.hardwareConcurrency <= 4;
    const saveData = Boolean(navigator.connection && navigator.connection.saveData);
    return lowMemory || lowCpu || saveData;
  }

  function isLitePerformanceMode() {
    return document.documentElement.getAttribute('data-performance') === 'lite';
  }

  function applyPerformanceMode(mode) {
    const normalizedMode = normalizePerformanceMode(String(mode || defaultSettings.performanceMode));
    appSettings.performanceMode = normalizedMode;
    const shouldUseLite = normalizedMode === 'lite' || (normalizedMode === 'auto' && getAutoLitePerformance());
    if (shouldUseLite) {
      document.documentElement.setAttribute('data-performance', 'lite');
    } else {
      document.documentElement.removeAttribute('data-performance');
    }
  }

  function syncThemeButtons() {
    const theme = resolveTheme(appSettings.theme);
    themeToggleButtons.forEach((button) => {
      button.classList.toggle('is-light', theme === 'light');
      button.setAttribute('aria-label', theme === 'light' ? 'Switch to dark mode' : 'Switch to light mode');
      button.setAttribute('title', theme === 'light' ? 'Switch to dark mode' : 'Switch to light mode');
    });
  }

  function applyTheme(theme) {
    const normalizedTheme = normalizeTheme(theme);
    appSettings.theme = normalizedTheme;
    const resolvedTheme = resolveTheme(normalizedTheme);
    document.documentElement.setAttribute('data-theme', resolvedTheme);
    document.body.classList.toggle('dark-mode', resolvedTheme === 'dark');
    syncThemeButtons();
  }

  function formatSettingOutput(field, value) {
    if (field === 'songFontSize') return `${value}px`;
    return String(value);
  }

  function resolveSongFontFamily(fontKey) {
    const fonts = {
      mono: '"JetBrains Mono", "Fira Code", monospace',
      serif: '"Georgia", "Times New Roman", serif',
      stage: '"Trebuchet MS", "Segoe UI", sans-serif',
    };
    return fonts[fontKey] || fonts.mono;
  }

  function resolveViewerPalette() {
    const isLightTheme = resolveTheme(appSettings.theme) === 'light';
    const defaultPalette = isLightTheme
      ? {
          lyricsColor: '#101b2b',
          chordColor: '#8a5a00',
          titleColor: '#122033',
          artistColor: '#1d4ed8',
          sectionColor: '#0d6b54',
          songBackground: '#ffffff',
        }
      : {
          lyricsColor: defaultSettings.lyricsColor,
          chordColor: defaultSettings.chordColor,
          titleColor: defaultSettings.titleColor,
          artistColor: defaultSettings.artistColor,
          sectionColor: defaultSettings.sectionColor,
          songBackground: defaultSettings.songBackground,
        };

    const usingDefaultLyrics = String(appSettings.lyricsColor) === String(defaultSettings.lyricsColor);
    const usingDefaultChords = String(appSettings.chordColor) === String(defaultSettings.chordColor);
    const usingDefaultTitle = String(appSettings.titleColor) === String(defaultSettings.titleColor);
    const usingDefaultArtist = String(appSettings.artistColor) === String(defaultSettings.artistColor);
    const usingDefaultSection = String(appSettings.sectionColor) === String(defaultSettings.sectionColor);
    const usingDefaultBackground = String(appSettings.songBackground) === String(defaultSettings.songBackground);

    return {
      lyricsColor: usingDefaultLyrics ? defaultPalette.lyricsColor : String(appSettings.lyricsColor || defaultPalette.lyricsColor),
      chordColor: usingDefaultChords ? defaultPalette.chordColor : String(appSettings.chordColor || defaultPalette.chordColor),
      titleColor: usingDefaultTitle ? defaultPalette.titleColor : String(appSettings.titleColor || defaultPalette.titleColor),
      artistColor: usingDefaultArtist ? defaultPalette.artistColor : String(appSettings.artistColor || defaultPalette.artistColor),
      sectionColor: usingDefaultSection ? defaultPalette.sectionColor : String(appSettings.sectionColor || defaultPalette.sectionColor),
      songBackground: usingDefaultBackground ? defaultPalette.songBackground : String(appSettings.songBackground || defaultPalette.songBackground),
    };
  }

  function normalizeEditorChordBrackets(text) {
    return text.replace(/\[\[\s*([^\]\r\n]+?)\s*\]\]/g, (_match, chord) => `[${String(chord).trim()}]`);
  }

  function normalizeSongEditorBody() {
    if (!songBodyEditor) return;
    const normalized = normalizeEditorChordBrackets(songBodyEditor.value);
    if (normalized !== songBodyEditor.value) {
      songBodyEditor.value = normalized;
    }
  }

  function isDirectiveEditorLine(line) {
    return /^\{[^}]+\}$/.test(String(line || '').trim());
  }

  function isSectionHeadingLine(line) {
    const value = String(line || '').trim().replace(/^[[(\s]+|[\])\s]+$/g, '');
    return /^(intro|verse(?:\s+\d+)?|chorus|refrain|bridge|coda|pre-chorus|post-chorus|tag|outro|ending|instrumental)(?:[:\s-].*)?$/i.test(value);
  }

  function isChordTokenValue(token) {
    const clean = normalizeChordTokenValue(token);
    if (!clean || ['|', '||', '|||', '/', '//', '///'].includes(clean)) return false;
    return /^(?:N\.?C\.?|[A-G](?:#|b)?(?:maj(?:7|9|11|13)?|M(?:7|9|11|13)?|m(?:aj7|6|7|9|11|13)?|min(?:7|9|11|13)?|sus(?:2|4)?|dim(?:7)?|aug|\+|add\d+|no\d+|omit\d+|2|4|5|6|7|9|11|13)?(?:\([^)]+\))?(?:\/[A-G](?:#|b)?)?)$/i.test(clean);
  }

  function normalizeChordTokenValue(token) {
    return String(token || '')
      .trim()
      .replace(/^\[([^\]]+)\]$/g, '$1')
      .replace(/^[\[{(<]+|[\]})>]+$/g, '')
      .replace(/[.,;:!?]+$/g, '')
      .trim();
  }

  function isChordOnlyEditorLine(line) {
    const trim = String(line || '').trim();
    if (!trim || isDirectiveEditorLine(trim) || isSectionHeadingLine(trim)) return false;
    const tokens = trim.match(/\[[^\]\r\n]+\]|\S+/g) || [];
    if (!tokens.length) return false;
    return tokens.every((token) => {
      if (/^\[[^\]\r\n]+\]$/.test(token)) {
        return isChordTokenValue(token.slice(1, -1));
      }
      return isChordTokenValue(token);
    });
  }

  function splitLeadingWhitespace(line) {
    const match = String(line || '').match(/^(\s*)(.*)$/);
    return {
      indent: match ? match[1] : '',
      content: match ? match[2] : String(line || ''),
    };
  }

  function convertInlineLineToChordOverPair(line) {
    const { indent, content } = splitLeadingWhitespace(line);
    const matches = Array.from(content.matchAll(/\[([^\]\r\n]+)\]/g));
    if (!matches.length) return [line];

    let lyricLine = '';
    let cursor = 0;
    const insertions = [];

    matches.forEach((match) => {
      const full = match[0];
      const chord = normalizeChordTokenValue(match[1] || '');
      const index = match.index != null ? match.index : cursor;
      lyricLine += content.slice(cursor, index);
      insertions.push({
        pos: lyricLine.length,
        text: chord,
      });
      cursor = index + full.length;
    });

    lyricLine += content.slice(cursor);
    if (!insertions.length || lyricLine.trim() === '') return [line];

    const chordLineLength = insertions.reduce((max, insertion) => Math.max(max, insertion.pos + insertion.text.length), lyricLine.length || 1);
    const chordChars = Array.from({ length: Math.max(chordLineLength, 1) }, () => ' ');

    insertions.forEach((insertion) => {
      Array.from(insertion.text).forEach((char, offset) => {
        chordChars[insertion.pos + offset] = char;
      });
    });

    return [
      `${indent}${chordChars.join('').replace(/\s+$/g, '')}`,
      `${indent}${lyricLine}`,
    ];
  }

  function convertEditorBodyToChordOverLyrics(text) {
    return String(text || '')
      .split('\n')
      .flatMap((line) => {
        const trim = line.trim();
        if (!trim || isDirectiveEditorLine(trim) || isSectionHeadingLine(trim) || isChordOnlyEditorLine(trim)) {
          return [line];
        }
        return convertInlineLineToChordOverPair(line);
      })
      .join('\n');
  }

  function applyNotationStyleToEditor() {
    if (!songBodyEditor) return;
    const currentValue = normalizeEditorChordBrackets(songBodyEditor.value);
    const converted = convertEditorBodyToChordOverLyrics(currentValue);

    if (converted !== songBodyEditor.value) {
      songBodyEditor.value = converted;
      songBodyEditor.dispatchEvent(new Event('input', { bubbles: true }));
    }
  }

  function formatElapsedTime(totalSeconds) {
    const safeSeconds = Math.max(0, Math.floor(totalSeconds));
    const hours = Math.floor(safeSeconds / 3600);
    const minutes = Math.floor((safeSeconds % 3600) / 60);
    const seconds = safeSeconds % 60;
    if (hours > 0) {
      return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    }
    return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
  }

  function formatSystemTime(date) {
    return new Intl.DateTimeFormat(undefined, {
      hour: 'numeric',
      minute: '2-digit',
      second: isLitePerformanceMode() ? undefined : '2-digit',
    }).format(date);
  }

  function triggerClockWobble(node) {
    if (!(node instanceof HTMLElement)) return;
    node.classList.remove('is-wobbling');
    // Force reflow so animation can restart on each value change.
    // eslint-disable-next-line no-unused-expressions
    node.offsetWidth;
    node.classList.add('is-wobbling');
  }

  function startSessionClock() {
    if (!elapsedClocks.length && !systemTimeClocks.length && !focusElapsedClocks.length) return;
    const clockIntervalMs = 1000;
    const renderClock = () => {
      const now = new Date();
      const elapsed = formatElapsedTime((Date.now() - appSessionStartedAtMs) / 1000);
      elapsedClocks.forEach((node) => {
        if (node.textContent !== elapsed) {
          node.textContent = elapsed;
          triggerClockWobble(node);
        }
      });
      const focusElapsed = focusSessionStartedAtMs === null
        ? '00:00'
        : formatElapsedTime((Date.now() - focusSessionStartedAtMs) / 1000);
      focusElapsedClocks.forEach((node) => {
        if (node.textContent !== focusElapsed) {
          node.textContent = focusElapsed;
          triggerClockWobble(node);
        }
      });
      const currentTime = formatSystemTime(now);
      systemTimeClocks.forEach((node) => {
        if (node.textContent !== currentTime) {
          node.textContent = currentTime;
          triggerClockWobble(node);
        }
      });
    };
    renderClock();
    if (sessionClockTimer) window.clearInterval(sessionClockTimer);
    sessionClockTimer = window.setInterval(renderClock, clockIntervalMs);
  }

  function syncSettingsForm() {
    if (!settingsFields.length) return;
    settingsFields.forEach((field) => {
      const key = field.dataset.settingField || '';
      if (!(key in appSettings)) return;
      if (field instanceof HTMLInputElement && field.type === 'checkbox') {
        field.checked = Boolean(appSettings[key]);
      } else if (field instanceof HTMLInputElement && field.type === 'radio') {
        field.checked = String(field.value) === String(appSettings[key]);
      } else {
        field.value = String(appSettings[key]);
      }
    });
    settingsOutputs.forEach((output) => {
      const key = output.dataset.settingOutput || '';
      if (!(key in appSettings)) return;
      output.textContent = formatSettingOutput(key, appSettings[key]);
    });
  }

  function applyAppSettings() {
    applyTheme(appSettings.theme);
    applyPerformanceMode(appSettings.performanceMode);
    document.body.classList.toggle('focus-panels-off', !appSettings.focusPanels);
    const viewerPalette = resolveViewerPalette();
    document.documentElement.style.setProperty('--viewer-font-family', resolveSongFontFamily(String(appSettings.songFontFamily || defaultSettings.songFontFamily)));
    document.documentElement.style.setProperty('--viewer-lyrics', viewerPalette.lyricsColor);
    document.documentElement.style.setProperty('--viewer-chords', viewerPalette.chordColor);
    document.documentElement.style.setProperty('--viewer-title', viewerPalette.titleColor);
    document.documentElement.style.setProperty('--viewer-artist', viewerPalette.artistColor);
    document.documentElement.style.setProperty('--viewer-section', viewerPalette.sectionColor);
    document.documentElement.style.setProperty('--viewer-sheet-bg', viewerPalette.songBackground);

    if (sheet && !pdfSheet) {
      const fontSize = Math.min(28, Math.max(14, Number(appSettings.songFontSize || defaultSettings.songFontSize)));
      sheet.dataset.fontSize = String(fontSize);
      sheet.style.fontSize = `${fontSize}px`;
    }

    if (speedInput) {
      const preferredSpeed = isSongPlaybackActive()
        ? songPlaybackState.scrollSpeed
        : (pdfSheet ? appSettings.pdfScrollSpeed : appSettings.songScrollSpeed);
      speedInput.value = String(sanitizeSongScrollSpeedValue(preferredSpeed));
      updateSpeedValue();
    }

    if (document.body.classList.contains('song-view')) {
      setFocusModeState(Boolean(appSettings.rememberFocusMode), { persist: false });
    }

    syncFocusButtons();
    syncThemeButtons();
    syncSettingsForm();
    syncSidebarState();
    startSessionClock();
  }

  function canCollapseSidebar() {
    return !metronomeIsRunning;
  }

  function readSidebarState() {
    if (!sidebar) return;
    try {
      sidebarCollapsed = window.localStorage.getItem(sidebarCollapsedStorageKey) === '1';
    } catch (_error) {
      sidebarCollapsed = false;
    }
  }

  function persistSidebarState() {
    try {
      window.localStorage.setItem(sidebarCollapsedStorageKey, sidebarCollapsed ? '1' : '0');
    } catch (_error) {
      // Ignore persistence failures.
    }
  }

  function syncSidebarState() {
    if (!sidebar || !sidebarToggle) return;
    const allowCollapse = canCollapseSidebar();
    if (!allowCollapse && sidebarCollapsed) {
      sidebarCollapsed = false;
      persistSidebarState();
    }

    sidebar.classList.toggle('is-collapsed', sidebarCollapsed && allowCollapse);
    sidebar.classList.toggle('is-locked-open', !allowCollapse);
    document.body.classList.toggle('sidebar-collapsed', sidebarCollapsed && allowCollapse);
    sidebarToggle.classList.toggle('is-collapsed', sidebarCollapsed && allowCollapse);
    sidebarToggle.disabled = !allowCollapse;
    sidebarToggle.classList.toggle('is-disabled', !allowCollapse);
    sidebarToggle.setAttribute('aria-label', sidebarCollapsed && allowCollapse ? 'Expand sidebar' : 'Collapse sidebar');
    sidebarToggle.setAttribute('title', !allowCollapse
      ? 'Sidebar stays open while the metronome is running'
      : (sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'));
    sidebarToggle.setAttribute('aria-pressed', sidebarCollapsed && allowCollapse ? 'true' : 'false');
    sidebarToggle.setAttribute('aria-expanded', sidebarCollapsed && allowCollapse ? 'false' : 'true');
  }

  function insertAtCursor(text, options = {}) {
    if (!songBodyEditor) return;
    const start = songBodyEditor.selectionStart != null ? songBodyEditor.selectionStart : songBodyEditor.value.length;
    const end = songBodyEditor.selectionEnd != null ? songBodyEditor.selectionEnd : start;
    const before = songBodyEditor.value.slice(0, start);
    const after = songBodyEditor.value.slice(end);
    songBodyEditor.value = `${before}${text}${after}`;
    const caretOffset = options.caretOffset != null ? options.caretOffset : text.length;
    const nextCaret = start + caretOffset;
    songBodyEditor.focus();
    songBodyEditor.setSelectionRange(nextCaret, nextCaret);
    songBodyEditor.dispatchEvent(new Event('input', { bubbles: true }));
  }

  function ensureSeparatedInsert(text) {
    if (!songBodyEditor) return;
    const start = songBodyEditor.selectionStart != null ? songBodyEditor.selectionStart : songBodyEditor.value.length;
    const before = songBodyEditor.value.slice(0, start);
    const needsLeadingBreak = before.length > 0 && !before.endsWith('\n\n');
    const prefix = needsLeadingBreak ? '\n\n' : '';
    insertAtCursor(`${prefix}${text}`);
  }

  function insertChordToken() {
    if (!songBodyEditor) return;
    const rawChord = window.prompt('Enter a chord', '');
    if (rawChord === null) return;
    const chord = rawChord.trim();
    if (!chord) return;
    insertAtCursor(`[${chord}]`);
  }

  function bindSongComposerTools() {
    if (!songBodyEditor || !songToolButtons.length) return;

    const toolMap = {
      title: () => {
        const value = ((songTitleInput && songTitleInput.value) || '').trim();
        ensureSeparatedInsert(`{title: ${value}}`);
      },
      artist: () => {
        const value = ((songArtistInput && songArtistInput.value) || '').trim();
        ensureSeparatedInsert(`{artist: ${value}}`);
      },
      chord: () => {
        insertChordToken();
      },
      intro: () => {
        ensureSeparatedInsert('{section: Intro}');
      },
      verse: () => {
        ensureSeparatedInsert('{section: Verse}');
      },
      refrain: () => {
        ensureSeparatedInsert('{section: Refrain}');
      },
      chorus: () => {
        ensureSeparatedInsert('{section: Chorus}');
      },
      bridge: () => {
        ensureSeparatedInsert('{section: Bridge}');
      },
      coda: () => {
        ensureSeparatedInsert('{section: Coda}');
      },
    };

    songToolButtons.forEach((button) => {
      button.addEventListener('click', () => {
        const handler = toolMap[button.dataset.songTool || ''];
        if (handler) {
          handler();
        }
      });
    });
  }

  function bindAssistantBotPresence() {
    if (!aiBotFigures.length) return;

    const moods = [
      {
        emotion: 'calm',
        title: 'Ready to help',
        body: 'I can help clean a song, organize labels, and keep your notation easy to read.',
      },
      {
        emotion: 'happy',
        title: 'Nice to see you',
        body: 'Want to start a new song, tidy up a pasted sheet, or polish your library details?',
      },
      {
        emotion: 'thinking',
        title: 'Thinking with you',
        body: 'I am especially good at structure, section labels, key cleanup, and formatting fixes.',
      },
      {
        emotion: 'excited',
        title: 'Let us build something',
        body: 'Try the workbench, import a draft, or save team preferences so the next session feels faster.',
      },
    ];

    let activeIndex = 0;

    const applyMood = (index) => {
      const mood = moods[index % moods.length];
      activeIndex = index % moods.length;
      aiBotFigures.forEach((figure) => {
        figure.setAttribute('data-ai-bot-emotion', mood.emotion);
      });
      if (aiBotMessageTitle) aiBotMessageTitle.textContent = mood.title;
      if (aiBotMessageBody) aiBotMessageBody.textContent = mood.body;
      aiBotEmotionPills.forEach((pill) => {
        pill.classList.toggle('is-active', pill.dataset.aiBotEmotionPill === mood.emotion);
      });
    };

    applyMood(activeIndex);

    if (aiBotMessageTitle || aiBotMessageBody) {
      aiBotMessageTimer = window.setInterval(() => {
        applyMood(activeIndex + 1);
      }, 5200);
    }

    aiBotEmotionPills.forEach((pill, index) => {
      pill.addEventListener('mouseenter', () => {
        applyMood(index);
      });
      pill.addEventListener('focus', () => {
        applyMood(index);
      });
    });

    aiBotFigures.forEach((figure) => {
      figure.addEventListener('mouseenter', () => {
        applyMood((activeIndex + 1) % moods.length);
      });
      figure.addEventListener('focus', () => {
        applyMood((activeIndex + 1) % moods.length);
      });
      figure.addEventListener('click', () => {
        applyMood((activeIndex + 1) % moods.length);
      });
    });
  }

  function setLibrarySearchOpen(open) {
    if (!librarySearchForm || !librarySearchToggle || !librarySearchInput) return;
    librarySearchForm.classList.toggle('is-open', open);
    librarySearchToggle.classList.toggle('is-active', open);
    librarySearchToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (open) {
      librarySearchInput.removeAttribute('tabindex');
      window.setTimeout(() => {
        librarySearchInput.focus();
        librarySearchInput.select();
      }, 50);
    } else {
      librarySearchInput.setAttribute('tabindex', '-1');
      if (librarySearchInput.value.trim() === '') {
        librarySearchInput.blur();
      }
    }
  }

  function applyLibraryView(view) {
    if (!libraryPanels.length || !libraryViewButtons.length) return;
    const nextView = view === 'list' ? 'list' : 'cards';
    libraryPanels.forEach((panel) => {
      panel.hidden = panel.dataset.libraryPanel !== nextView;
    });
    libraryViewButtons.forEach((button) => {
      const active = button.dataset.libraryView === nextView;
      button.classList.toggle('is-active', active);
      button.setAttribute('aria-pressed', active ? 'true' : 'false');
    });
    try {
      window.localStorage.setItem(libraryViewStorageKey, nextView);
    } catch (_error) {
      // Ignore persistence failures.
    }
    appSettings.libraryView = nextView;
    writeSettings(appSettings);
    syncSettingsForm();
  }

  function readLibraryView() {
    if (!libraryPanels.length || !libraryViewButtons.length) return;
    let savedView = appSettings.libraryView || 'cards';
    try {
      savedView = window.localStorage.getItem(libraryViewStorageKey) || savedView;
    } catch (_error) {
      savedView = appSettings.libraryView || 'cards';
    }
    applyLibraryView(savedView);
  }

  function getLibraryListItems() {
    return document.querySelectorAll('[data-song-list-item]');
  }

  function setExpandedListItem(nextItem) {
    getLibraryListItems().forEach((item) => {
      const expanded = item === nextItem;
      item.classList.toggle('is-expanded', expanded);
      item.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    });
  }

  function openListItem(item) {
    if (!(item instanceof HTMLElement)) return;
    const href = item.dataset.openHref;
    if (href) {
      window.location.href = href;
    }
  }

  function applySetlistPickerView(view) {
    if (!setlistPickerPanels.length || !setlistPickerButtons.length) return;
    const nextView = view === 'list' ? 'list' : 'cards';
    setlistPickerPanels.forEach((panel) => {
      panel.hidden = panel.dataset.setlistPickerPanel !== nextView;
    });
    setlistPickerButtons.forEach((button) => {
      const active = button.dataset.setlistPickerView === nextView;
      button.classList.toggle('is-active', active);
      button.setAttribute('aria-pressed', active ? 'true' : 'false');
    });
  }

  function syncSetlistSongVisuals(songId, checked) {
    setlistSongInputs.forEach((input) => {
      if (input.dataset.songId !== songId) return;
      input.checked = checked;
      const option = input.closest('[data-setlist-song-option]');
      if (option) {
        option.classList.toggle('is-selected', checked);
      }
    });
  }

  function rebuildSetlistHiddenInputs() {
    if (!setlistHiddenInputs) return;
    const orderedIds = [];
    const seen = new Set();

    setlistSongInputs.forEach((input) => {
      const songId = input.dataset.songId || '';
      if (!input.checked || !songId || seen.has(songId)) return;
      seen.add(songId);
      orderedIds.push(songId);
    });

    setlistHiddenInputs.innerHTML = orderedIds
      .map((songId) => `<input type="hidden" name="song_ids[]" value="${songId}">`)
      .join('');

    if (setlistSelectedCount) {
      setlistSelectedCount.textContent = `${orderedIds.length} selected`;
    }
  }

  function bindSetlistPicker() {
    if (!setlistForm || !setlistSongInputs.length) return;

    setlistSongInputs.forEach((input) => {
      input.addEventListener('change', () => {
        const songId = input.dataset.songId || '';
        syncSetlistSongVisuals(songId, input.checked);
        rebuildSetlistHiddenInputs();
      });
    });

    setlistPickerButtons.forEach((button) => {
      button.addEventListener('click', () => {
        applySetlistPickerView(button.dataset.setlistPickerView || 'cards');
      });
    });

    applySetlistPickerView('cards');
    rebuildSetlistHiddenInputs();
  }

  function updateSetlistArrangementOrder() {
    if (!setlistSortableList) return;
    Array.from(setlistSortableList.children).forEach((item, index) => {
      const badge = item.querySelector('[data-setlist-order-badge]');
      if (badge) {
        badge.textContent = String(index + 1);
      }
    });
  }

  function getDragAfterElement(container, y) {
    const draggableElements = Array.from(container.querySelectorAll('[data-setlist-sortable-item]:not(.is-dragging)'));

    return draggableElements.reduce((closest, child) => {
      const box = child.getBoundingClientRect();
      const offset = y - box.top - box.height / 2;
      if (offset < 0 && offset > closest.offset) {
        return { offset, element: child };
      }
      return closest;
    }, { offset: Number.NEGATIVE_INFINITY, element: null }).element;
  }

  function bindSetlistArrangementDrag() {
    if (!setlistSortableList || !setlistSortableItems.length) return;

    setlistSortableItems.forEach((item) => {
      item.addEventListener('dragstart', () => {
        activeDraggedItem = item;
        item.classList.add('is-dragging');
      });

      item.addEventListener('dragend', () => {
        item.classList.remove('is-dragging');
        activeDraggedItem = null;
        updateSetlistArrangementOrder();
      });
    });

    setlistSortableList.addEventListener('dragover', (event) => {
      event.preventDefault();
      if (!activeDraggedItem) return;
      const afterElement = getDragAfterElement(setlistSortableList, event.clientY);
      if (!afterElement) {
        setlistSortableList.appendChild(activeDraggedItem);
      } else if (afterElement !== activeDraggedItem) {
        setlistSortableList.insertBefore(activeDraggedItem, afterElement);
      }
    });

    updateSetlistArrangementOrder();
  }

  function clamp(value, min, max) {
    return Math.min(max, Math.max(min, value));
  }

  function syncScrollButtons() {
    scrollToggles.forEach((button) => {
      const running = timer !== null;
      button.classList.toggle('is-active', running);
      button.setAttribute('aria-label', running ? 'Stop auto scroll' : 'Start auto scroll');
      button.setAttribute('title', running ? 'Stop auto scroll' : 'Start auto scroll');
    });
  }

  function stop() {
    if (timer) {
      clearInterval(timer);
      timer = null;
    }
    syncScrollButtons();
  }

  function isVisibleElement(element) {
    if (!(element instanceof HTMLElement)) return false;
    const rect = element.getBoundingClientRect();
    return rect.width > 0 && rect.height > 0;
  }

  function canElementScroll(element) {
    return element instanceof HTMLElement && element.scrollHeight - element.clientHeight > 2;
  }

  function getScrollContainer() {
    const candidateSheets = Array.from(document.querySelectorAll('[data-song-sheet]'));
    const activeSheet = candidateSheets.find((candidate) => isVisibleElement(candidate) && canElementScroll(candidate));
    if (activeSheet) return activeSheet;

    const visibleSheet = candidateSheets.find((candidate) => isVisibleElement(candidate));
    if (visibleSheet) return visibleSheet;

    if (canElementScroll(sheet)) return sheet;
    return document.scrollingElement instanceof HTMLElement ? document.scrollingElement : null;
  }

  function start() {
    const scrollContainer = getScrollContainer();
    if (!scrollContainer || !speedInput) return;
    stop();
    const speed = Number(speedInput.value || 4);
    timer = setInterval(() => {
      const nextOffset = Math.max(0.5, speed / 2);
      scrollContainer.scrollTop += nextOffset;
      if (scrollContainer.scrollTop + scrollContainer.clientHeight >= scrollContainer.scrollHeight - 2) {
        stop();
      }
    }, 80);
    syncScrollButtons();
  }

  function updateFont(delta) {
    if (!sheet) return;
    const current = Number(sheet.dataset.fontSize || 18);
    const next = Math.min(28, Math.max(14, current + delta));
    sheet.dataset.fontSize = String(next);
    sheet.style.fontSize = `${next}px`;
    appSettings.songFontSize = next;
    writeSettings(appSettings);
    syncSettingsForm();
  }

  function updateSpeedValue() {
    if (!speedInput || !speedValue) return;
    const step = Number(speedInput.step || 1);
    const digits = step < 1 ? 1 : 0;
    speedValue.textContent = Number(speedInput.value || 0).toFixed(digits);
  }

  function applyPdfNightMode(enabled) {
    if (!pdfSheet || !pdfNightToggle) return;
    pdfSheet.classList.toggle('is-night-mode', enabled);
    pdfNightToggle.classList.toggle('is-active', enabled);
    pdfNightToggle.setAttribute('aria-pressed', enabled ? 'true' : 'false');
    pdfNightToggle.setAttribute('aria-label', enabled ? 'Enable day mode' : 'Enable night mode');
    pdfNightToggle.setAttribute('title', enabled ? 'Enable day mode' : 'Enable night mode');
    try {
      window.localStorage.setItem(pdfNightModeStorageKey, enabled ? '1' : '0');
    } catch (_error) {
      // Ignore persistence failures.
    }
    appSettings.pdfNightMode = enabled;
    writeSettings(appSettings);
    syncSettingsForm();
  }

  function readPdfNightMode() {
    if (!pdfSheet || !pdfNightToggle) return;
    let enabled = Boolean(appSettings.pdfNightMode);
    try {
      const stored = window.localStorage.getItem(pdfNightModeStorageKey);
      if (stored !== null) {
        enabled = stored === '1';
      }
    } catch (_error) {
      enabled = Boolean(appSettings.pdfNightMode);
    }
    applyPdfNightMode(enabled);
  }

  function getBeatsPerBar(signature) {
    const top = Number((signature || '4/4').split('/')[0]);
    return Number.isFinite(top) && top > 0 ? top : 4;
  }

  function getTempoMarking(bpm) {
    if (bpm < 45) return 'Grave';
    if (bpm < 60) return 'Largo';
    if (bpm < 66) return 'Larghetto';
    if (bpm < 76) return 'Adagio';
    if (bpm < 108) return 'Andante';
    if (bpm < 120) return 'Moderato';
    if (bpm < 156) return 'Allegro';
    if (bpm < 176) return 'Vivace';
    if (bpm < 200) return 'Presto';
    return 'Prestissimo';
  }

  function readMetronomeState() {
    if (isSongPlaybackActive()) {
      metronomeState.bpm = clamp(Number(songPlaybackState.bpm || metronomeState.bpm), 40, 240);
      metronomeState.signature = normalizeMetronomeSignature(songPlaybackState.signature || metronomeState.signature);
      return;
    }
    try {
      const saved = JSON.parse(window.localStorage.getItem('songshelf_metronome') || '{}');
      metronomeState.bpm = clamp(Number(saved.bpm || metronomeState.bpm), 40, 240);
      metronomeState.signature = normalizeMetronomeSignature(typeof saved.signature === 'string' ? saved.signature : metronomeState.signature);
    } catch (_error) {
      // Ignore malformed localStorage values.
    }
  }

  function persistMetronomeState() {
    if (isSongPlaybackActive()) {
      songPlaybackState.bpm = clamp(Number(metronomeState.bpm || songPlaybackState.bpm), 40, 240);
      songPlaybackState.signature = normalizeMetronomeSignature(metronomeState.signature || songPlaybackState.signature);
      queueSongPlaybackPersist();
      return;
    }
    try {
      window.localStorage.setItem('songshelf_metronome', JSON.stringify(metronomeState));
    } catch (_error) {
      // Ignore persistence failures.
    }
  }

  function clearMetronomeVisualTimeouts() {
    metronomeVisualTimeouts.forEach((timeoutId) => {
      window.clearTimeout(timeoutId);
    });
    metronomeVisualTimeouts = [];
  }

  function renderMetronomeBeats(activeBeat = -1) {
    const beats = getBeatsPerBar(metronomeState.signature);
    metronomeBeatsGroups.forEach((group) => {
      if (group.children.length !== beats) {
        group.innerHTML = '';
        for (let index = 0; index < beats; index += 1) {
          const beat = document.createElement('button');
          beat.type = 'button';
          beat.className = 'metronome-beat';
          beat.textContent = String(index + 1);
          beat.setAttribute('aria-label', `Beat ${index + 1}`);
          if (index === 0) beat.classList.add('is-accent');
          group.appendChild(beat);
        }
      }

      Array.from(group.children).forEach((beat, index) => {
        beat.classList.toggle('is-active', index === activeBeat);
      });
    });
  }

  function queueMetronomeVisualBeat(time, beatInBar) {
    const audioContext = ensureMetronomeAudioContext();
    if (!audioContext) return;
    const delay = Math.max(0, (time - audioContext.currentTime) * 1000);
    const timeoutId = window.setTimeout(() => {
      if (!metronomeIsRunning) return;
      metronomeCurrentBeat = beatInBar;
      renderMetronomeBeats(beatInBar);
      if (sheet) {
        sheet.classList.remove('metronome-glow', 'metronome-glow-accent');
        sheet.classList.add(beatInBar === 0 ? 'metronome-glow-accent' : 'metronome-glow');
        const clearGlowId = window.setTimeout(() => {
          sheet.classList.remove('metronome-glow', 'metronome-glow-accent');
        }, 150);
        metronomeVisualTimeouts.push(clearGlowId);
      }
      metronomePulses.forEach((pulse) => {
        pulse.textContent = `${metronomeState.bpm} BPM · ${metronomeState.signature}`;
      });
    }, delay);
    metronomeVisualTimeouts.push(timeoutId);
    if (metronomeVisualTimeouts.length > 64) {
      const staleId = metronomeVisualTimeouts.shift();
      window.clearTimeout(staleId);
    }
  }

  function syncMetronomeControls() {
    metronomeBpms.forEach((input) => {
      input.value = String(metronomeState.bpm);
    });
    metronomeBpmInputs.forEach((input) => {
      input.value = String(metronomeState.bpm);
    });
    metronomeSignatures.forEach((select) => {
      select.value = metronomeState.signature;
    });
    metronomePanels.forEach((panel) => {
      panel.hidden = false;
    });
    metronomeStarts.forEach((button) => {
      button.disabled = metronomeIsRunning;
    });
    metronomeStops.forEach((button) => {
      button.disabled = !metronomeIsRunning;
    });
    focusMetronomeToggles.forEach((button) => {
      button.classList.toggle('is-active', metronomeIsRunning);
      button.setAttribute('aria-label', metronomeIsRunning ? 'Stop metronome' : 'Start metronome');
      button.setAttribute('title', metronomeIsRunning ? 'Stop metronome' : 'Start metronome');
    });

    metronomePulses.forEach((pulse) => {
      if (!(window.AudioContext || window.webkitAudioContext)) {
        pulse.textContent = 'Audio unavailable';
        pulse.classList.remove('is-running');
      } else {
        pulse.textContent = metronomeIsRunning ? `${metronomeState.bpm} BPM · ${metronomeState.signature}` : 'Stopped';
        pulse.classList.toggle('is-running', metronomeIsRunning);
      }
    });
    metronomeMarkings.forEach((marking) => {
      marking.textContent = getTempoMarking(metronomeState.bpm);
    });

    renderMetronomeBeats(metronomeIsRunning ? metronomeCurrentBeat : -1);
    syncSidebarState();
  }

  function updateMetronomeBpm(nextBpm) {
    const bpm = clamp(Number(nextBpm || metronomeState.bpm), 40, 240);
    metronomeState.bpm = Math.round(bpm);
    persistMetronomeState();
    syncMetronomeControls();
  }

  function setSongMetronomeBpm(nextBpm) {
    const bpm = clamp(Number(nextBpm || 0), 40, 240);
    if (!Number.isFinite(bpm)) return;
    updateMetronomeBpm(Math.round(bpm));
  }

  function commitMetronomeBpmInput() {
    const input = metronomeBpmInputs[0];
    if (!input) return;
    updateMetronomeBpm(input.value);
  }

  function updateMetronomeSignature(nextSignature) {
    metronomeState.signature = normalizeMetronomeSignature(nextSignature || '4/4');
    metronomeCurrentBeat = 0;
    persistMetronomeState();
    syncMetronomeControls();
  }

  function ensureMetronomeAudioContext() {
    if (metronomeAudioContext) return metronomeAudioContext;
    const AudioContextCtor = window.AudioContext || window.webkitAudioContext;
    if (!AudioContextCtor) return null;
    metronomeAudioContext = new AudioContextCtor();
    return metronomeAudioContext;
  }

  function loadMetronomeBuffers() {
    const audioContext = ensureMetronomeAudioContext();
    if (!audioContext) return null;
    if (metronomeBuffers) return metronomeBuffers;

    metronomeBuffers = Promise.all([
      fetch(metronomeSampleUrls.accent).then((response) => response.arrayBuffer()),
      fetch(metronomeSampleUrls.beat).then((response) => response.arrayBuffer()),
    ]).then(([accentArrayBuffer, beatArrayBuffer]) => Promise.all([
      audioContext.decodeAudioData(accentArrayBuffer.slice(0)),
      audioContext.decodeAudioData(beatArrayBuffer.slice(0)),
    ])).then((decoded) => ({
      accent: decoded[0],
      beat: decoded[1],
    })).catch(() => null);

    return metronomeBuffers;
  }

  function playFallbackClick(audioContext, time, accentBeat) {
    const oscillator = audioContext.createOscillator();
    const gain = audioContext.createGain();

    oscillator.type = accentBeat ? 'square' : 'triangle';
    oscillator.frequency.setValueAtTime(accentBeat ? 1760 : 880, time);
    oscillator.frequency.exponentialRampToValueAtTime(accentBeat ? 1320 : 760, time + (accentBeat ? 0.05 : 0.035));
    gain.gain.setValueAtTime(0.0001, time);
    gain.gain.exponentialRampToValueAtTime(accentBeat ? 0.38 : 0.12, time + 0.0015);
    gain.gain.exponentialRampToValueAtTime(0.0001, time + (accentBeat ? 0.08 : 0.045));

    oscillator.connect(gain);
    gain.connect(audioContext.destination);
    oscillator.start(time);
    oscillator.stop(time + (accentBeat ? 0.09 : 0.05));
  }

  function playBufferAtTime(audioContext, buffer, time, accentBeat) {
    if (!buffer) {
      playFallbackClick(audioContext, time, accentBeat);
      return;
    }

    const source = audioContext.createBufferSource();
    const gain = audioContext.createGain();
    source.buffer = buffer;
    gain.gain.setValueAtTime(accentBeat ? 0.95 : 0.85, time);
    source.connect(gain);
    gain.connect(audioContext.destination);
    source.start(time);
  }

  function scheduleMetronomeClick(time, beatInBar) {
    const audioContext = ensureMetronomeAudioContext();
    if (!audioContext) return;

    const accentBeat = beatInBar === 0;
    Promise.resolve(loadMetronomeBuffers()).then((buffers) => {
      if (!metronomeIsRunning) return;
      const buffer = accentBeat ? (buffers && buffers.accent) : (buffers && buffers.beat);
      playBufferAtTime(audioContext, buffer || null, time, accentBeat);
    });

    queueMetronomeVisualBeat(time, beatInBar);
  }

  function nextMetronomeNote() {
    const beatsPerBar = getBeatsPerBar(metronomeState.signature);
    const secondsPerBeat = 60 / metronomeState.bpm;
    metronomeNextNoteTime += secondsPerBeat;
    metronomeScheduledBeat = (metronomeScheduledBeat + 1) % beatsPerBar;
  }

  function scheduler() {
    const audioContext = ensureMetronomeAudioContext();
    if (!audioContext) return;
    while (metronomeNextNoteTime < audioContext.currentTime + metronomeScheduleAheadTime) {
      scheduleMetronomeClick(metronomeNextNoteTime, metronomeScheduledBeat);
      nextMetronomeNote();
    }
  }

  function startMetronome() {
    const audioContext = ensureMetronomeAudioContext();
    if (!audioContext) {
      syncMetronomeControls();
      return Promise.resolve();
    }
    const resumePromise = audioContext.state === 'suspended' ? audioContext.resume() : Promise.resolve();
    return Promise.resolve(resumePromise)
      .then(() => loadMetronomeBuffers())
      .then(() => {
        if (metronomeIsRunning) return;

        metronomeIsRunning = true;
        metronomeCurrentBeat = 0;
        metronomeScheduledBeat = 0;
        metronomeNextNoteTime = audioContext.currentTime + 0.05;
        clearMetronomeVisualTimeouts();
        syncMetronomeControls();
        scheduler();
        metronomeSchedulerId = window.setInterval(scheduler, metronomeLookaheadMs);
      });
  }

  function stopMetronome() {
    metronomeIsRunning = false;
    if (metronomeSchedulerId) {
      window.clearInterval(metronomeSchedulerId);
      metronomeSchedulerId = null;
    }
    clearMetronomeVisualTimeouts();
    if (sheet) {
      sheet.classList.remove('metronome-glow', 'metronome-glow-accent');
    }
    syncMetronomeControls();
  }

  scrollToggles.forEach((button) => {
    button.addEventListener('click', () => {
      if (timer) stop(); else start();
    });
  });
  syncScrollButtons();

  if (speedInput) {
    speedInput.addEventListener('input', () => {
      const nextSpeed = sanitizeSongScrollSpeedValue(speedInput.value);
      speedInput.value = String(nextSpeed);
      updateSpeedValue();
      if (isSongPlaybackActive()) {
        songPlaybackState.scrollSpeed = nextSpeed;
        queueSongPlaybackPersist();
      } else if (pdfSheet) {
        appSettings.pdfScrollSpeed = Number(nextSpeed || defaultSettings.pdfScrollSpeed);
      } else {
        appSettings.songScrollSpeed = Number(nextSpeed || defaultSettings.songScrollSpeed);
      }
      if (!isSongPlaybackActive()) {
        writeSettings(appSettings);
        syncSettingsForm();
      }
      if (timer) {
        start();
      }
    });
  }

  if (pdfNightToggle) {
    readPdfNightMode();
    pdfNightToggle.addEventListener('click', () => {
      applyPdfNightMode(!pdfSheet.classList.contains('is-night-mode'));
    });
  }

  readMetronomeState();
  syncMetronomeControls();

  metronomeBpms.forEach((slider) => {
    slider.addEventListener('input', () => {
      updateMetronomeBpm(slider.value);
    });
  });

  metronomeBpmInputs.forEach((input) => {
    input.addEventListener('input', () => {
      const nextValue = Number(input.value);
      if (Number.isFinite(nextValue) && nextValue >= 40 && nextValue <= 240) {
        metronomeBpms.forEach((slider) => {
          slider.value = String(Math.round(nextValue));
        });
      }
    });
    input.addEventListener('blur', () => {
      updateMetronomeBpm(input.value);
    });
    input.addEventListener('change', () => {
      updateMetronomeBpm(input.value);
    });
    input.addEventListener('keydown', (event) => {
      if (event.key === 'Enter') {
        updateMetronomeBpm(input.value);
        input.blur();
      }
    });
  });

  metronomeSignatures.forEach((select) => {
    select.addEventListener('change', () => {
      updateMetronomeSignature(select.value);
    });
  });

  metronomeStarts.forEach((button) => {
    button.addEventListener('click', () => {
      startMetronome();
    });
  });

  metronomeStops.forEach((button) => {
    button.addEventListener('click', () => {
      stopMetronome();
    });
  });

  focusMetronomeToggles.forEach((button) => {
    button.addEventListener('click', () => {
      if (metronomeIsRunning) {
        stopMetronome();
      } else {
        startMetronome();
      }
    });
  });

  fontButtons.forEach((button) => {
    button.addEventListener('click', () => {
      updateFont(button.dataset.fontStep === 'up' ? 1 : -1);
    });
  });

  function syncFocusButtons() {
    const inFocus = document.body.classList.contains('focus-mode');
    focusToggles.forEach((button) => {
      button.setAttribute('aria-label', inFocus ? 'Exit focus mode' : 'Enter focus mode');
      button.setAttribute('title', inFocus ? 'Exit focus mode' : 'Enter focus mode');
    });
  }

  function setFocusModeState(enabled, options = {}) {
    const shouldEnable = Boolean(enabled);
    const persist = options.persist !== false;
    const currentlyEnabled = document.body.classList.contains('focus-mode');

    if (shouldEnable) {
      // Start timer on first entry (or recover if it was missing).
      if (!currentlyEnabled || focusSessionStartedAtMs === null) {
        focusSessionStartedAtMs = Date.now();
      }
    } else {
      // Reset timer when focus mode is closed.
      focusSessionStartedAtMs = null;
    }

    document.body.classList.toggle('focus-mode', shouldEnable);

    if (persist) {
      appSettings.rememberFocusMode = shouldEnable;
      writeSettings(appSettings);
      syncSettingsForm();
    }

    syncFocusButtons();
    startSessionClock();
  }

  focusToggles.forEach((button) => {
    button.addEventListener('click', () => {
      const nextEnabled = !document.body.classList.contains('focus-mode');
      setFocusModeState(nextEnabled, { persist: true });
      stop();
    });
  });

  if (librarySearchToggle) {
    librarySearchToggle.addEventListener('click', () => {
      const shouldOpen = !librarySearchForm.classList.contains('is-open');
      setLibrarySearchOpen(shouldOpen);
    });
  }

  if (librarySearchInput && librarySearchInput.value.trim() !== '') {
    setLibrarySearchOpen(true);
  }

  libraryViewButtons.forEach((button) => {
    button.addEventListener('click', () => {
      applyLibraryView(button.dataset.libraryView || 'cards');
    });
  });

  readLibraryView();
  bindSetlistPicker();
  bindSetlistArrangementDrag();
  bindSongComposerTools();
  bindAssistantBotPresence();
  bindAssistantFabDrag();
  applyAppSettings();
  startSessionClock();

  if (window.matchMedia) {
    const systemThemeQuery = window.matchMedia('(prefers-color-scheme: dark)');
    const handleSystemThemeChange = () => {
      if (normalizeTheme(appSettings.theme) !== 'system') return;
      applyAppSettings();
    };
    if (typeof systemThemeQuery.addEventListener === 'function') {
      systemThemeQuery.addEventListener('change', handleSystemThemeChange);
    } else if (typeof systemThemeQuery.addListener === 'function') {
      systemThemeQuery.addListener(handleSystemThemeChange);
    }
  }

  themeToggleButtons.forEach((button) => {
    button.addEventListener('click', () => {
      const nextTheme = resolveTheme(appSettings.theme) === 'light' ? 'dark' : 'light';
      appSettings.theme = nextTheme;
      writeSettings(appSettings);
      applyAppSettings();
      if (settingsStatus) {
        settingsStatus.textContent = `Theme switched to ${nextTheme}.`;
      }
    });
  });

  settingsFields.forEach((field) => {
    field.addEventListener('input', () => {
      const key = field.dataset.settingField || '';
      if (!(key in appSettings)) return;
      const value = field instanceof HTMLInputElement && field.type === 'checkbox'
        ? field.checked
        : field.value;
      appSettings[key] = ['songFontSize', 'songScrollSpeed', 'pdfScrollSpeed'].includes(key)
        ? Number(value)
        : value;
      syncSettingsForm();
      applyAppSettings();
    });

    if (field instanceof HTMLInputElement && field.type === 'radio') {
      field.addEventListener('change', () => {
        const key = field.dataset.settingField || '';
        if (!(key in appSettings) || !field.checked) return;
        appSettings[key] = field.value;
        syncSettingsForm();
        applyAppSettings();
      });
    }
  });

  if (settingsForm) {
    settingsForm.addEventListener('submit', (event) => {
      event.preventDefault();
      writeSettings(appSettings);
      try {
        window.localStorage.setItem(libraryViewStorageKey, String(appSettings.libraryView));
        window.localStorage.setItem(pdfNightModeStorageKey, appSettings.pdfNightMode ? '1' : '0');
      } catch (_error) {
        // Ignore persistence failures.
      }
      applyAppSettings();
      if (settingsStatus) {
        settingsStatus.textContent = 'Settings saved.';
      }
    });
  }

  if (settingsReset) {
    settingsReset.addEventListener('click', () => {
      appSettings = { ...defaultSettings };
      writeSettings(appSettings);
      try {
        window.localStorage.setItem(libraryViewStorageKey, defaultSettings.libraryView);
        window.localStorage.setItem(pdfNightModeStorageKey, defaultSettings.pdfNightMode ? '1' : '0');
      } catch (_error) {
        // Ignore persistence failures.
      }
      applyAppSettings();
      if (settingsStatus) {
        settingsStatus.textContent = 'Settings reset to defaults.';
      }
    });
  }

  // Compatibility binding for id-based settings controls (themeToggle/settingsForm/resetBtn/formStatus/data-range).
  const settingsThemeToggle = document.getElementById('themeToggle');
  const settingsFormById = document.getElementById('settingsForm');
  const settingsResetById = document.getElementById('resetBtn');
  const settingsStatusById = document.getElementById('formStatus');
  const settingsRangeFields = document.querySelectorAll('[data-range]');

  function setLegacySettingsStatus(message) {
    if (settingsStatusById) settingsStatusById.textContent = message;
  }

  function updateRangeOutputsByDataAttribute() {
    settingsRangeFields.forEach((input) => {
      const key = input.dataset.range || '';
      if (!key) return;
      const suffix = input.dataset.suffix || '';
      const output = document.querySelector(`[data-output="${key}"]`);
      if (output) output.textContent = `${input.value}${suffix}`;
    });
  }

  if (settingsThemeToggle && document.body.classList.contains('settings')) {
    settingsThemeToggle.addEventListener('click', () => {
      const nextTheme = resolveTheme(appSettings.theme) === 'light' ? 'dark' : 'light';
      appSettings.theme = nextTheme;
      writeSettings(appSettings);
      applyAppSettings();
      setLegacySettingsStatus(`Theme switched to ${nextTheme} mode.`);
    });
  }

  settingsRangeFields.forEach((input) => {
    input.addEventListener('input', updateRangeOutputsByDataAttribute);
  });

  if (settingsFormById && settingsFormById !== settingsForm) {
    settingsFormById.addEventListener('submit', (event) => {
      event.preventDefault();
      setLegacySettingsStatus('Settings saved successfully.');
    });
  }

  if (settingsResetById && settingsResetById !== settingsReset) {
    settingsResetById.addEventListener('click', () => {
      if (settingsFormById) settingsFormById.reset();
      updateRangeOutputsByDataAttribute();
      setLegacySettingsStatus('Settings restored to defaults.');
    });
  }

  if (document.body.classList.contains('settings')) {
    const persistedTheme = resolveTheme(appSettings.theme);
    document.body.classList.toggle('dark-mode', persistedTheme === 'dark');
    updateRangeOutputsByDataAttribute();
  }

  if (songForm && songBodyEditor) {
    normalizeSongEditorBody();
    applyNotationStyleToEditor();

    songBodyEditor.addEventListener('paste', () => {
      window.setTimeout(() => {
        normalizeSongEditorBody();
      }, 0);
    });

    songBodyEditor.addEventListener('blur', () => {
      normalizeSongEditorBody();
    });

    songForm.addEventListener('submit', () => {
      applyNotationStyleToEditor();
      normalizeSongEditorBody();
    });
  }

  readSidebarState();
  syncSidebarState();

  if (sidebarToggle) {
    sidebarToggle.addEventListener('click', () => {
      if (!canCollapseSidebar()) return;
      sidebarCollapsed = !sidebarCollapsed;
      persistSidebarState();
      syncSidebarState();
    });
  }

  if (libraryViewLayout) {
    libraryViewLayout.addEventListener('click', (event) => {
      if (!(event.target instanceof Element) || event.target.closest('a, button')) return;
      const item = event.target.closest('[data-song-list-item]');
      if (!item) return;
      setExpandedListItem(item.classList.contains('is-expanded') ? null : item);
    });

    libraryViewLayout.addEventListener('dblclick', (event) => {
      if (!(event.target instanceof Element) || event.target.closest('a, button')) return;
      const item = event.target.closest('[data-song-list-item]');
      if (!item) return;
      openListItem(item);
    });

    libraryViewLayout.addEventListener('keydown', (event) => {
      if (!(event.target instanceof HTMLElement)) return;
      const item = event.target.closest('[data-song-list-item]');
      if (!item) return;
      if (event.key === 'Enter') {
        if (item.classList.contains('is-expanded')) {
          openListItem(item);
        } else {
          setExpandedListItem(item);
        }
      }
      if (event.key === ' ') {
        event.preventDefault();
        setExpandedListItem(item.classList.contains('is-expanded') ? null : item);
      }
    });
  }

  function syncModalOpenState() {
    const hasConfirmOpen = Boolean(modal && !modal.hidden);
    const hasTipsOpen = Boolean(tipsModal && !tipsModal.hidden);
    const hasAssistantOpen = Boolean(assistantPopup && !assistantPopup.hidden);
    document.body.classList.toggle('modal-open', hasConfirmOpen || hasTipsOpen || hasAssistantOpen);
  }

  function closeConfirmModal() {
    if (!modal) return;
    modal.hidden = true;
    syncModalOpenState();
    if (modalTitle) modalTitle.textContent = 'Delete item?';
    if (modalMessage) modalMessage.textContent = 'This action cannot be undone.';
    if (modalAccept) {
      modalAccept.textContent = 'Delete';
      modalAccept.href = '#';
    }
    if (lastFocusedElement instanceof HTMLElement) {
      lastFocusedElement.focus();
    }
  }

  function openConfirmModal(trigger) {
    if (!modal || !modalTitle || !modalMessage || !modalAccept) return;
    lastFocusedElement = trigger;
    modalTitle.textContent = trigger.dataset.confirmTitle || 'Delete item?';
    modalMessage.textContent = trigger.dataset.confirmMessage || 'This action cannot be undone.';
    modalAccept.textContent = trigger.dataset.confirmAction || 'Delete';
    modalAccept.href = trigger.getAttribute('href') || '#';
    modal.hidden = false;
    syncModalOpenState();
    modalAccept.focus();
  }

  function closeTipsModal() {
    if (!tipsModal) return;
    tipsModal.hidden = true;
    syncModalOpenState();
    if (lastFocusedElement instanceof HTMLElement) {
      lastFocusedElement.focus();
    }
  }

  function openTipsModal(trigger) {
    if (!tipsModal) return;
    lastFocusedElement = trigger;
    tipsModal.hidden = false;
    syncModalOpenState();
    const closeButton = tipsModal.querySelector('[data-tips-close]');
    if (closeButton instanceof HTMLElement) {
      closeButton.focus();
    }
  }

  function closeTopbarMenu() {
    if (!topbarMenuToggle || !topbarMenuPanel) return;
    topbarMenuPanel.hidden = true;
    topbarMenuToggle.setAttribute('aria-expanded', 'false');
  }

  function openTopbarMenu() {
    if (!topbarMenuToggle || !topbarMenuPanel) return;
    topbarMenuPanel.hidden = false;
    topbarMenuToggle.setAttribute('aria-expanded', 'true');
    const firstItem = topbarMenuPanel.querySelector('button, a');
    if (firstItem instanceof HTMLElement) {
      firstItem.focus();
    }
  }

  function createAssistantBubble(entry) {
    const role = entry && entry.role === 'user' ? 'user' : 'assistant';
    const message = entry && typeof entry.message === 'string' ? entry.message.trim() : '';
    if (!message) return null;

    const bubble = document.createElement('article');
    bubble.className = `assistant-chat-bubble ${role === 'user' ? 'is-user' : 'is-assistant'}`;
    if (entry && entry.pending) {
      bubble.classList.add('is-pending');
    }

    const text = document.createElement('p');
    text.textContent = message;
    bubble.appendChild(text);

    const meta = entry && typeof entry.meta === 'object' && entry.meta ? entry.meta : {};
    const output = typeof meta.output === 'string' ? meta.output.trim() : '';
    if (role === 'assistant' && output) {
      const outputBlock = document.createElement('pre');
      outputBlock.className = 'assistant-chat-output';
      outputBlock.textContent = output;
      bubble.appendChild(outputBlock);
    }

    return bubble;
  }

  function scrollAssistantThreadToBottom() {
    if (!assistantChatThread) return;
    assistantChatThread.scrollTop = assistantChatThread.scrollHeight;
  }

  function renderAssistantHistory(history) {
    if (!assistantChatThread) return;
    assistantChatThread.innerHTML = '';

    const entries = Array.isArray(history) ? history : [];
    if (entries.length === 0) {
      const introBubble = createAssistantBubble({
        role: 'assistant',
        message: 'Hi, I am Juan. I can chat naturally, but I stay scoped to SongShelf. Ask me to find songs, clean chord sheets, or learn a preference.',
      });
      if (introBubble) assistantChatThread.appendChild(introBubble);
      scrollAssistantThreadToBottom();
      return;
    }

    entries.forEach((entry) => {
      const bubble = createAssistantBubble(entry);
      if (bubble) assistantChatThread.appendChild(bubble);
    });

    scrollAssistantThreadToBottom();
  }

  function setAssistantChatBusy(isBusy) {
    if (!(assistantChatForm instanceof HTMLFormElement)) return;
    const formControls = assistantChatForm.querySelectorAll('button, textarea');
    formControls.forEach((control) => {
      if (control instanceof HTMLButtonElement || control instanceof HTMLTextAreaElement) {
        control.disabled = isBusy;
      }
    });
  }

  function syncAssistantSendState() {
    if (!(assistantChatInput instanceof HTMLTextAreaElement) || !(assistantChatSendButton instanceof HTMLButtonElement)) return;
    assistantChatSendButton.disabled = assistantChatInput.value.trim() === '' || assistantChatInput.disabled;
  }

  function dockAssistantFabsToPopupTop() {
    if (!assistantChatPanel || !assistantPopup || assistantPopup.hidden) return;
    const panelRect = assistantChatPanel.getBoundingClientRect();
    const draggableFabs = Array.from(document.querySelectorAll('[data-assistant-draggable]'));
    if (!draggableFabs.length) return;

    draggableFabs.forEach((fab) => {
      if (!(fab instanceof HTMLElement)) return;
      if (fab.dataset.assistantDocked === '1') return;

      fab.dataset.assistantRestoreLeft = fab.style.left || '';
      fab.dataset.assistantRestoreTop = fab.style.top || '';
      fab.dataset.assistantRestoreRight = fab.style.right || '';
      fab.dataset.assistantRestoreBottom = fab.style.bottom || '';
      fab.dataset.assistantRestoreZ = fab.style.zIndex || '';

      const fabRect = fab.getBoundingClientRect();
      const margin = 8;
      const unclampedLeft = panelRect.left + ((panelRect.width - fabRect.width) / 2);
      const maxLeft = Math.max(margin, window.innerWidth - fabRect.width - margin);
      const targetLeft = clamp(unclampedLeft, margin, maxLeft);
      const targetTop = Math.max(margin, panelRect.top - (fabRect.height / 2));

      fab.style.left = `${targetLeft}px`;
      fab.style.top = `${targetTop}px`;
      fab.style.right = 'auto';
      fab.style.bottom = 'auto';
      fab.style.zIndex = '44';
      fab.dataset.assistantDocked = '1';
      fab.classList.add('is-docked');
    });
  }

  function restoreAssistantFabsAfterPopup() {
    const draggableFabs = Array.from(document.querySelectorAll('[data-assistant-draggable]'));
    if (!draggableFabs.length) return;

    draggableFabs.forEach((fab) => {
      if (!(fab instanceof HTMLElement)) return;
      if (fab.dataset.assistantDocked !== '1') return;

      fab.style.left = fab.dataset.assistantRestoreLeft || '';
      fab.style.top = fab.dataset.assistantRestoreTop || '';
      fab.style.right = fab.dataset.assistantRestoreRight || '';
      fab.style.bottom = fab.dataset.assistantRestoreBottom || '';
      fab.style.zIndex = fab.dataset.assistantRestoreZ || '';

      delete fab.dataset.assistantRestoreLeft;
      delete fab.dataset.assistantRestoreTop;
      delete fab.dataset.assistantRestoreRight;
      delete fab.dataset.assistantRestoreBottom;
      delete fab.dataset.assistantRestoreZ;
      delete fab.dataset.assistantDocked;
      fab.classList.remove('is-docked');
    });
  }

  function closeAssistantPopup() {
    if (!assistantPopup) return;
    assistantPopup.hidden = true;
    restoreAssistantFabsAfterPopup();
    syncModalOpenState();
    if (lastFocusedElement instanceof HTMLElement) {
      lastFocusedElement.focus();
    }
  }

  function openAssistantPopup(trigger) {
    if (!assistantPopup) return;
    lastFocusedElement = trigger;
    assistantPopup.hidden = false;
    window.requestAnimationFrame(() => {
      dockAssistantFabsToPopupTop();
    });
    syncModalOpenState();
    if (assistantChatInput instanceof HTMLElement) {
      assistantChatInput.focus();
    }
    scrollAssistantThreadToBottom();
  }

  function bindAssistantFabDrag() {
    const draggableFabs = Array.from(document.querySelectorAll('[data-assistant-draggable]'));
    if (!draggableFabs.length) return;

    let savedPosition = null;
    try {
      const raw = window.localStorage.getItem(assistantFabPositionStorageKey);
      const parsed = raw ? JSON.parse(raw) : null;
      if (parsed && Number.isFinite(parsed.left) && Number.isFinite(parsed.top)) {
        savedPosition = {
          left: Number(parsed.left),
          top: Number(parsed.top),
          side: parsed.side === 'left' || parsed.side === 'right' ? parsed.side : '',
        };
      }
    } catch (_error) {
      savedPosition = null;
    }

    const edgeMargin = 8;

    const clampFabPosition = (fab, left, top, dimensions = null) => {
      const rect = dimensions || fab.getBoundingClientRect();
      const margin = 8;
      const maxLeft = Math.max(margin, window.innerWidth - rect.width - margin);
      const maxTop = Math.max(margin, window.innerHeight - rect.height - margin);
      return {
        left: clamp(left, margin, maxLeft),
        top: clamp(top, margin, maxTop),
      };
    };

    const applyFabPosition = (fab, left, top, dimensions = null) => {
      const bounded = clampFabPosition(fab, left, top, dimensions);
      fab.style.left = `${bounded.left}px`;
      fab.style.top = `${bounded.top}px`;
      fab.style.right = 'auto';
      fab.style.bottom = 'auto';
      return bounded;
    };

    draggableFabs.forEach((fab) => {
      if (!(fab instanceof HTMLElement)) return;
      fab.setAttribute('draggable', 'false');
      fab.addEventListener('dragstart', (event) => {
        event.preventDefault();
      });

      if (savedPosition) {
        const rect = fab.getBoundingClientRect();
        if (savedPosition.side === 'left' || savedPosition.side === 'right') {
          const snapLeft = savedPosition.side === 'left'
            ? edgeMargin
            : Math.max(edgeMargin, window.innerWidth - rect.width - edgeMargin);
          applyFabPosition(fab, snapLeft, savedPosition.top, { width: rect.width, height: rect.height });
          fab.dataset.assistantSnapSide = savedPosition.side;
        } else {
          applyFabPosition(fab, savedPosition.left, savedPosition.top, { width: rect.width, height: rect.height });
        }
      }

      let dragging = false;
      let moved = false;
      let pointerId = null;
      let startX = 0;
      let startY = 0;
      let pointerOffsetX = 0;
      let pointerOffsetY = 0;
      let fabWidth = 0;
      let fabHeight = 0;
      let nextLeft = 0;
      let nextTop = 0;
      let frameId = null;

      fab.addEventListener('pointerdown', (event) => {
        if (fab.dataset.assistantDocked === '1') return;
        if (event.pointerType === 'mouse' && event.button !== 0) return;
        event.preventDefault();

        pointerId = event.pointerId;
        dragging = true;
        moved = false;
        startX = event.clientX;
        startY = event.clientY;
        const rect = fab.getBoundingClientRect();
        pointerOffsetX = event.clientX - rect.left;
        pointerOffsetY = event.clientY - rect.top;
        fabWidth = rect.width;
        fabHeight = rect.height;
        nextLeft = rect.left;
        nextTop = rect.top;
        fab.classList.add('is-dragging');
        fab.classList.remove('is-snapping');
        if (typeof fab.setPointerCapture === 'function') {
          fab.setPointerCapture(pointerId);
        }
      });

      fab.addEventListener('pointermove', (event) => {
        if (!dragging || event.pointerId !== pointerId) return;
        const deltaX = event.clientX - startX;
        const deltaY = event.clientY - startY;
        if (!moved && Math.hypot(deltaX, deltaY) < 2) return;
        moved = true;
        event.preventDefault();
        nextLeft = event.clientX - pointerOffsetX;
        nextTop = event.clientY - pointerOffsetY;

        if (frameId !== null) return;
        frameId = window.requestAnimationFrame(() => {
          frameId = null;
          applyFabPosition(fab, nextLeft, nextTop, { width: fabWidth, height: fabHeight });
        });
      });

      const stopDrag = (event) => {
        if (!dragging || event.pointerId !== pointerId) return;
        dragging = false;
        pointerId = null;
        fab.classList.remove('is-dragging');
        if (typeof fab.releasePointerCapture === 'function') {
          try {
            fab.releasePointerCapture(event.pointerId);
          } catch (_error) {
            // Ignore release failures.
          }
        }

        if (frameId !== null) {
          window.cancelAnimationFrame(frameId);
          frameId = null;
          applyFabPosition(fab, nextLeft, nextTop, { width: fabWidth, height: fabHeight });
        }

        if (!moved) return;
        fab.dataset.assistantSuppressClickUntil = String(Date.now() + 260);

        const rect = fab.getBoundingClientRect();
        const releaseX = Number.isFinite(event.clientX) ? event.clientX : (rect.left + (rect.width / 2));
        const snapSide = releaseX < (window.innerWidth / 2) ? 'left' : 'right';
        const snapLeft = snapSide === 'left'
          ? edgeMargin
          : Math.max(edgeMargin, window.innerWidth - rect.width - edgeMargin);
        const bounded = applyFabPosition(fab, snapLeft, rect.top, { width: rect.width, height: rect.height });
        fab.dataset.assistantSnapSide = snapSide;
        fab.classList.add('is-snapping');
        window.setTimeout(() => {
          fab.classList.remove('is-snapping');
        }, 220);
        try {
          window.localStorage.setItem(assistantFabPositionStorageKey, JSON.stringify({
            ...bounded,
            side: snapSide,
          }));
        } catch (_error) {
          // Ignore persistence failures.
        }
      };

      fab.addEventListener('pointerup', stopDrag);
      fab.addEventListener('pointercancel', stopDrag);
      fab.addEventListener('click', (event) => {
        const suppressUntil = Number(fab.dataset.assistantSuppressClickUntil || '0');
        if (Date.now() <= suppressUntil) {
          event.preventDefault();
          event.stopPropagation();
        }
      });
    });

    window.addEventListener('resize', () => {
      if (assistantPopup && !assistantPopup.hidden) {
        dockAssistantFabsToPopupTop();
        return;
      }
      draggableFabs.forEach((fab) => {
        if (!(fab instanceof HTMLElement)) return;
        if (fab.style.left === '' || fab.style.top === '') return;
        const currentLeft = Number.parseFloat(fab.style.left);
        const currentTop = Number.parseFloat(fab.style.top);
        if (!Number.isFinite(currentLeft) || !Number.isFinite(currentTop)) return;
        const rect = fab.getBoundingClientRect();
        const snapSide = fab.dataset.assistantSnapSide || '';
        if (snapSide === 'left' || snapSide === 'right') {
          const snapLeft = snapSide === 'left'
            ? edgeMargin
            : Math.max(edgeMargin, window.innerWidth - rect.width - edgeMargin);
          applyFabPosition(fab, snapLeft, currentTop, { width: rect.width, height: rect.height });
          return;
        }
        applyFabPosition(fab, currentLeft, currentTop, { width: rect.width, height: rect.height });
      });
    });
  }

  confirmButtons.forEach((button) => {
    button.addEventListener('click', (event) => {
      event.preventDefault();
      openConfirmModal(button);
    });
  });

  modalCancels.forEach((button) => {
    button.addEventListener('click', () => {
      closeConfirmModal();
    });
  });

  tipsOpenButtons.forEach((button) => {
    button.addEventListener('click', () => {
      openTipsModal(button);
    });
  });

  tipsCloseButtons.forEach((button) => {
    button.addEventListener('click', () => {
      closeTipsModal();
    });
  });

  if (topbarMenuToggle && topbarMenuPanel) {
    topbarMenuToggle.addEventListener('click', (event) => {
      event.preventDefault();
      const isOpen = !topbarMenuPanel.hidden;
      if (isOpen) {
        closeTopbarMenu();
      } else {
        openTopbarMenu();
      }
    });

    topbarMenuPanel.addEventListener('click', (event) => {
      if (!(event.target instanceof Element)) return;
      const actionable = event.target.closest('button, a');
      if (!actionable) return;
      window.setTimeout(() => {
        closeTopbarMenu();
      }, 0);
    });
  }

  assistantOpenButtons.forEach((button) => {
    button.addEventListener('click', (event) => {
      if (event.defaultPrevented) return;
      event.preventDefault();
      openAssistantPopup(button);
    });
  });

  assistantCloseButtons.forEach((button) => {
    button.addEventListener('click', (event) => {
      event.preventDefault();
      closeAssistantPopup();
    });
  });

  if (assistantChatForm instanceof HTMLFormElement && assistantChatInput instanceof HTMLTextAreaElement) {
    assistantChatInput.addEventListener('input', () => {
      syncAssistantSendState();
    });

    assistantChatInput.addEventListener('keydown', (event) => {
      if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        if (assistantChatInput.value.trim() !== '') {
          if (typeof assistantChatForm.requestSubmit === 'function') {
            assistantChatForm.requestSubmit();
          } else {
            assistantChatForm.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
          }
        }
      }
    });

    syncAssistantSendState();

    assistantChatForm.addEventListener('submit', async (event) => {
      event.preventDefault();
      const message = assistantChatInput.value.trim();
      if (!message) return;

      if (typeof window.fetch !== 'function') {
        assistantChatForm.submit();
        return;
      }

      const optimisticUserBubble = createAssistantBubble({ role: 'user', message });
      const pendingAssistantBubble = createAssistantBubble({ role: 'assistant', message: 'Thinking...', pending: true });
      if (assistantChatThread && optimisticUserBubble && pendingAssistantBubble) {
        assistantChatThread.appendChild(optimisticUserBubble);
        assistantChatThread.appendChild(pendingAssistantBubble);
        scrollAssistantThreadToBottom();
      }

      assistantChatInput.value = '';
      syncAssistantSendState();
      setAssistantChatBusy(true);

      try {
        const response = await window.fetch('?action=ai-chat-api', {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
          },
          body: new URLSearchParams({ assistant_message: message }).toString(),
        });

        let payload = null;
        try {
          payload = await response.json();
        } catch (_error) {
          payload = null;
        }

        if (payload && Array.isArray(payload.history)) {
          renderAssistantHistory(payload.history);
        } else {
          const fallbackBubble = createAssistantBubble({
            role: 'assistant',
            message: 'I could not process that request. Please try again.',
          });
          if (assistantChatThread && fallbackBubble) {
            assistantChatThread.appendChild(fallbackBubble);
            scrollAssistantThreadToBottom();
          }
        }

        if (payload && typeof payload.redirect_url === 'string' && payload.redirect_url !== '') {
          window.setTimeout(() => {
            window.location.href = payload.redirect_url;
          }, 650);
        }
      } catch (_error) {
        if (assistantChatThread) {
          const errorBubble = createAssistantBubble({
            role: 'assistant',
            message: 'Connection issue while sending your message. Please try again.',
          });
          if (errorBubble) assistantChatThread.appendChild(errorBubble);
          scrollAssistantThreadToBottom();
        }
      } finally {
        setAssistantChatBusy(false);
        syncAssistantSendState();
        assistantChatInput.focus();
      }
    });
  }

  if (assistantChatClearButton instanceof HTMLButtonElement) {
    assistantChatClearButton.addEventListener('click', async () => {
      if (typeof window.fetch !== 'function') {
        window.location.href = '?action=ai-chat-reset';
        return;
      }

      setAssistantChatBusy(true);
      try {
        const response = await window.fetch('?action=ai-chat-reset-api', {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
          },
        });
        let payload = null;
        try {
          payload = await response.json();
        } catch (_error) {
          payload = null;
        }
        renderAssistantHistory(payload && Array.isArray(payload.history) ? payload.history : []);
      } catch (_error) {
        const errorBubble = createAssistantBubble({
          role: 'assistant',
          message: 'Unable to clear chat right now. Please try again.',
        });
        if (assistantChatThread && errorBubble) {
          assistantChatThread.appendChild(errorBubble);
          scrollAssistantThreadToBottom();
        }
      } finally {
        setAssistantChatBusy(false);
        syncAssistantSendState();
        if (assistantChatInput instanceof HTMLElement) assistantChatInput.focus();
      }
    });
  }

  if (assistantChatThread) {
    scrollAssistantThreadToBottom();
  }

  // Fallback delegated tips handlers so the trigger still works even if direct bindings are missed.
  document.addEventListener('click', (event) => {
    if (!(event.target instanceof Element)) return;
    const openTrigger = event.target.closest('[data-tips-open]');
    if (openTrigger instanceof HTMLElement) {
      event.preventDefault();
      openTipsModal(openTrigger);
      return;
    }
    const closeTrigger = event.target.closest('[data-tips-close]');
    if (closeTrigger) {
      event.preventDefault();
      closeTipsModal();
      return;
    }
    const assistantOpenTrigger = event.target.closest('[data-assistant-open]');
    if (assistantOpenTrigger instanceof HTMLElement) {
      if (event.defaultPrevented) return;
      event.preventDefault();
      openAssistantPopup(assistantOpenTrigger);
      return;
    }
    const assistantCloseTrigger = event.target.closest('[data-assistant-close]');
    if (assistantCloseTrigger) {
      event.preventDefault();
      closeAssistantPopup();
      return;
    }
    const topbarMenuToggleTrigger = event.target.closest('[data-topbar-menu-toggle]');
    if (topbarMenuToggleTrigger) {
      return;
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && modal && !modal.hidden) {
      closeConfirmModal();
      return;
    }
    if (event.key === 'Escape' && tipsModal && !tipsModal.hidden) {
      closeTipsModal();
      return;
    }
    if (event.key === 'Escape' && assistantPopup && !assistantPopup.hidden) {
      closeAssistantPopup();
      return;
    }
    if (event.key === 'Escape' && topbarMenuPanel && !topbarMenuPanel.hidden) {
      closeTopbarMenu();
      if (topbarMenuToggle instanceof HTMLElement) {
        topbarMenuToggle.focus();
      }
      return;
    }
    if (event.key === 'Escape'
      && librarySearchForm
      && librarySearchForm.classList.contains('is-open')
      && librarySearchInput
      && librarySearchInput.value.trim() === '') {
      setLibrarySearchOpen(false);
    }
  });

  document.addEventListener('click', (event) => {
    if (!(event.target instanceof Element)) return;
    if (topbarMenuPanel && topbarMenu && !topbarMenuPanel.hidden && !event.target.closest('[data-topbar-menu]')) {
      closeTopbarMenu();
    }
    if (librarySearchForm
      && librarySearchToggle
      && !event.target.closest('[data-library-search-form], [data-library-search-toggle]')
      && librarySearchInput
      && librarySearchInput.value.trim() === '') {
      setLibrarySearchOpen(false);
    }
    if (libraryViewLayout && !event.target.closest('[data-song-list-item]')) {
      setExpandedListItem(null);
    }
  });

  document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
      stopMetronome();
    }
  });

  document.querySelectorAll('.assistant-fab-mascot').forEach((fab) => {
    fab.addEventListener('mouseenter', () => {
      fab.classList.add('is-hovered');
    });

    fab.addEventListener('mouseleave', () => {
      fab.classList.remove('is-hovered');
    });
  });

  document.querySelectorAll('.assistant-fab-orb').forEach((fab) => {
    fab.addEventListener('mouseenter', () => {
      fab.style.setProperty('--fab-border', 'rgba(125, 211, 252, 0.35)');
    });

    fab.addEventListener('mouseleave', () => {
      fab.style.setProperty('--fab-border', 'rgba(255, 255, 255, 0.12)');
    });
  });

  document.querySelectorAll('[data-photo-upload-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
      const card = button.closest('.developer-card');
      if (!card) return;
      const form = card.querySelector('[data-photo-upload-form]');
      if (!(form instanceof HTMLElement)) return;
      form.hidden = !form.hidden;
      if (!form.hidden) {
        const firstInput = form.querySelector('input, textarea, select');
        if (firstInput instanceof HTMLElement) firstInput.focus();
      }
    });
  });
});
