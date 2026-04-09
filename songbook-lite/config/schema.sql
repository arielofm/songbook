CREATE TABLE IF NOT EXISTS songs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    artist TEXT DEFAULT '',
    key_name TEXT DEFAULT '',
    capo INTEGER DEFAULT 0,
    tags TEXT DEFAULT '',
    notation_style TEXT DEFAULT 'chordpro',
    source_format TEXT DEFAULT 'chordpro',
    body TEXT NOT NULL,
    file_path TEXT DEFAULT '',
    mime_type TEXT DEFAULT '',
    audio_source_type TEXT DEFAULT '',
    audio_url TEXT DEFAULT '',
    audio_title TEXT DEFAULT '',
    audio_file_path TEXT DEFAULT '',
    audio_mime_type TEXT DEFAULT '',
    bpm INTEGER DEFAULT 0,
    time_signature TEXT DEFAULT '4/4',
    scroll_speed REAL DEFAULT 0,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS setlists (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    notes TEXT DEFAULT '',
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS setlist_songs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    setlist_id INTEGER NOT NULL,
    song_id INTEGER NOT NULL,
    position INTEGER NOT NULL DEFAULT 0,
    FOREIGN KEY(setlist_id) REFERENCES setlists(id) ON DELETE CASCADE,
    FOREIGN KEY(song_id) REFERENCES songs(id) ON DELETE CASCADE
);
