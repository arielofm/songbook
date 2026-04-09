<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';

function assistantChatHistory(): array {
    return getAssistantChatMessages(40);
}

function assistantPushChatEntry(string $role, string $message, array $meta = []): void {
    addAssistantChatMessage($role, $message, $meta);
}

function assistantExtractSearchQuery(string $message): string {
    $flat = trim(preg_replace('/\s+/', ' ', str_replace(["\r\n", "\r"], ' ', $message)) ?? '');
    if ($flat === '') {
        return '';
    }

    if (preg_match('/["“](.+?)["”]/u', $flat, $quoted)) {
        $candidate = trim((string)($quoted[1] ?? ''), " \t\n\r\0\x0B.,!?");
        if (mb_strlen($candidate) >= 2) {
            return $candidate;
        }
    }

    $patterns = [
        '/^\s*(?:find|search(?:\s+for)?|look\s+for|show|open|play)\s+(?:me\s+)?(?:the\s+)?(?:song\s+)?(.+)$/i',
        '/^\s*(?:do\s+you\s+have|is\s+there|check)\s+(?:any\s+)?(?:song\s+)?(?:called|named)?\s*(.+?)\s*(?:available)?\??$/i',
        '/^\s*song\s*:\s*(.+)$/i',
    ];

    foreach ($patterns as $pattern) {
        if (!preg_match($pattern, $flat, $matches)) {
            continue;
        }
        $candidate = trim((string)($matches[1] ?? ''), " \t\n\r\0\x0B.,!?");
        $candidate = preg_replace('/\b(?:please|thanks?)$/i', '', $candidate) ?? $candidate;
        $candidate = trim($candidate);
        if (mb_strlen($candidate) >= 2) {
            return $candidate;
        }
    }

    if (!str_contains($flat, "\n") && mb_strlen($flat) <= 70) {
        $looksLikeCommand = preg_match('/\b(clean|format|fix|normalize|arrange|chord|lyrics)\b/i', $flat) === 1;
        if (!$looksLikeCommand) {
            return trim($flat, " \t\n\r\0\x0B.,!?");
        }
    }

    return '';
}

function assistantLooksLikeCleanCommand(string $message): bool {
    $normalized = trim(str_replace(["\r\n", "\r"], "\n", $message));
    if ($normalized === '') {
        return false;
    }

    $lower = mb_strtolower($normalized);
    if (preg_match('/\b(clean|format|fix|normalize|arrange|polish)\b.*\b(song|chord|lyrics|chart)\b/u', $lower)) {
        return true;
    }
    if (preg_match('/\b(song|chord|lyrics|chart)\b.*\b(clean|format|fix|normalize|arrange|polish)\b/u', $lower)) {
        return true;
    }

    $lineCount = substr_count($normalized, "\n") + 1;
    $detectedChords = extractChordTokensFromText($normalized);
    if (count($detectedChords) >= 4) {
        return true;
    }
    return $lineCount >= 3 && count($detectedChords) >= 3;
}

function assistantExtractCleanPayload(string $message): string {
    $normalized = str_replace(["\r\n", "\r"], "\n", trim($message));
    if ($normalized === '') {
        return '';
    }

    if (preg_match('/^```(?:[a-z0-9_-]+)?\s*(.*?)\s*```$/is', $normalized, $fenced)) {
        $normalized = trim((string)($fenced[1] ?? ''));
    }

    $lines = preg_split('/\n/', $normalized) ?: [];
    if (count($lines) > 1 && preg_match('/^\s*(?:please\s+)?(?:clean|format|fix|normalize|arrange|polish)\b/i', (string)$lines[0])) {
        array_shift($lines);
    }

    return trim(implode("\n", $lines));
}

function assistantLooksLikeCorrectionFeedback(string $message): bool {
    $trimmed = trim($message);
    if ($trimmed === '') {
        return false;
    }

    if (preg_match('/^\s*(?:no|wrong|incorrect|not\s+right|instead|should\s+be|use\s+this|correction)\b/i', $trimmed)) {
        return true;
    }

    return preg_match('/\b(?:should\s+be|instead|correct(?:ion)?|better)\b/i', $trimmed) === 1;
}

function assistantExtractCorrectionPayload(string $message): string {
    $normalized = trim(str_replace(["\r\n", "\r"], "\n", $message));
    if ($normalized === '') {
        return '';
    }

    if (preg_match('/```(?:[a-z0-9_-]+)?\s*(.*?)\s*```/is', $normalized, $fenced)) {
        return trim((string)($fenced[1] ?? ''));
    }

    if (preg_match('/\b(?:should\s+be|use\s+this|instead)\s*[:\-]\s*(.+)$/is', $normalized, $matches)) {
        return trim((string)($matches[1] ?? ''));
    }

    $lines = preg_split('/\n/', $normalized) ?: [];
    if (count($lines) >= 3) {
        return trim($normalized);
    }

    return '';
}

function assistantLastAssistantMessageByIntent(array $history, string $intent): ?array {
    for ($index = count($history) - 1; $index >= 0; $index--) {
        $entry = $history[$index] ?? null;
        if (!is_array($entry)) {
            continue;
        }
        if ((string)($entry['role'] ?? '') !== 'assistant') {
            continue;
        }
        $meta = is_array($entry['meta'] ?? null) ? $entry['meta'] : [];
        if ((string)($meta['intent'] ?? '') === $intent) {
            return $entry;
        }
    }

    return null;
}

function assistantLearnFromFeedback(string $userMessage, array $history): ?string {
    if (!assistantLooksLikeCorrectionFeedback($userMessage)) {
        return null;
    }

    $lastCleanReply = assistantLastAssistantMessageByIntent($history, 'clean_song');
    if (!is_array($lastCleanReply)) {
        return null;
    }

    $meta = is_array($lastCleanReply['meta'] ?? null) ? $lastCleanReply['meta'] : [];
    $inputText = trim((string)($meta['input'] ?? ''));
    if ($inputText === '') {
        return null;
    }

    $correctedOutput = assistantExtractCorrectionPayload($userMessage);
    if ($correctedOutput === '') {
        return null;
    }

    $previousOutput = trim((string)($meta['output'] ?? ''));
    if ($previousOutput !== '' && $previousOutput === $correctedOutput) {
        return null;
    }

    addAssistantTrainingExample(
        $inputText,
        $correctedOutput,
        'Chat correction ' . date('Y-m-d H:i'),
        'pair'
    );

    return 'Thanks, I learned from your correction and will reuse it on similar song cleanup requests.';
}

function assistantLearnPreferenceFromMessage(string $userMessage, array $assistantMemory): ?string {
    if (!preg_match('/^\s*(?:remember|preference|note)\s*[:\-]\s*(.+)$/i', $userMessage, $matches)) {
        return null;
    }

    $newRule = trim((string)($matches[1] ?? ''));
    if ($newRule === '') {
        return null;
    }

    $currentInstructions = trim((string)($assistantMemory['custom_instructions'] ?? ''));
    if ($currentInstructions !== '' && mb_stripos($currentInstructions, $newRule) !== false) {
        return 'I already have that preference in memory.';
    }

    $mergedInstructions = $currentInstructions !== '' ? $currentInstructions . "\n- " . $newRule : '- ' . $newRule;
    saveAssistantMemory(
        $mergedInstructions,
        (string)($assistantMemory['preferred_terms'] ?? ''),
        (string)($assistantMemory['repertoire_notes'] ?? '')
    );

    return 'Preference saved. I will apply that in future responses.';
}

function assistantFindSongsByQuery(PDO $pdo, string $query, int $limit = 12): array {
    $term = trim($query);
    if ($term === '') {
        return [];
    }

    $safeLimit = max(1, min(20, $limit));
    $like = '%' . $term . '%';
    $stmt = $pdo->prepare(
        "SELECT id, title, artist, tags, source_format
         FROM songs
         WHERE title LIKE ? OR artist LIKE ? OR tags LIKE ?
         ORDER BY CASE WHEN title LIKE ? THEN 0 ELSE 1 END, title COLLATE NOCASE
         LIMIT ?"
    );
    $stmt->bindValue(1, $like, PDO::PARAM_STR);
    $stmt->bindValue(2, $like, PDO::PARAM_STR);
    $stmt->bindValue(3, $like, PDO::PARAM_STR);
    $stmt->bindValue(4, $like, PDO::PARAM_STR);
    $stmt->bindValue(5, $safeLimit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function assistantBuildSongsRedirectUrl(string $searchQuery, int $matchCount): string {
    return '?action=songs&q=' . urlencode($searchQuery)
        . '&type=all&sort=title&ai_highlight=1&ai_query=' . urlencode($searchQuery)
        . '&ai_found=' . $matchCount
        . '&ai_source=assistant';
}

function assistantHandleChatMessage(PDO $pdo, string $assistantMessage): array {
    $message = trim($assistantMessage);
    if ($message === '') {
        return [
            'ok' => false,
            'error' => 'Message cannot be empty.',
            'history' => assistantChatHistory(),
        ];
    }

    assistantPushChatEntry('user', $message);
    $assistantMemory = getAssistantMemory();
    $assistantChatHistory = assistantChatHistory();

    $preferenceLearnedReply = assistantLearnPreferenceFromMessage($message, $assistantMemory);
    if ($preferenceLearnedReply !== null) {
        assistantPushChatEntry('assistant', $preferenceLearnedReply, ['intent' => 'learn_preference']);
        return [
            'ok' => true,
            'intent' => 'learn_preference',
            'reply' => $preferenceLearnedReply,
            'redirect_url' => null,
            'history' => assistantChatHistory(),
        ];
    }

    $feedbackLearnedReply = assistantLearnFromFeedback($message, $assistantChatHistory);
    if ($feedbackLearnedReply !== null) {
        assistantPushChatEntry('assistant', $feedbackLearnedReply, ['intent' => 'learn_feedback']);
        return [
            'ok' => true,
            'intent' => 'learn_feedback',
            'reply' => $feedbackLearnedReply,
            'redirect_url' => null,
            'history' => assistantChatHistory(),
        ];
    }

    $searchQuery = assistantExtractSearchQuery($message);
    $isCleanCommand = assistantLooksLikeCleanCommand($message);

    if ($searchQuery !== '' && !$isCleanCommand) {
        $matches = assistantFindSongsByQuery($pdo, $searchQuery, 12);
        if ($matches !== []) {
            $matchCount = count($matches);
            $reply = $matchCount === 1
                ? 'I found 1 match in your library. I am opening it now and highlighting it.'
                : 'I found ' . $matchCount . ' matches in your library. I am opening them now and highlighting the results.';

            assistantPushChatEntry('assistant', $reply, [
                'intent' => 'search_song',
                'query' => $searchQuery,
                'match_count' => $matchCount,
            ]);

            return [
                'ok' => true,
                'intent' => 'search_song',
                'reply' => $reply,
                'redirect_url' => assistantBuildSongsRedirectUrl($searchQuery, $matchCount),
                'history' => assistantChatHistory(),
            ];
        }

        $reply = 'I checked your SongShelf library and "' . $searchQuery . '" is not there yet. Import or create it first, then I can find it instantly next time.';
        assistantPushChatEntry('assistant', $reply, [
            'intent' => 'search_song_miss',
            'query' => $searchQuery,
            'match_count' => 0,
        ]);
        return [
            'ok' => true,
            'intent' => 'search_song_miss',
            'reply' => $reply,
            'redirect_url' => null,
            'history' => assistantChatHistory(),
        ];
    }

    if ($isCleanCommand) {
        $payload = assistantExtractCleanPayload($message);
        if ($payload === '') {
            $reply = 'Paste the song text with chords and I will clean it into SongShelf-ready format.';
            assistantPushChatEntry('assistant', $reply, ['intent' => 'clean_song_missing_payload']);
            return [
                'ok' => true,
                'intent' => 'clean_song_missing_payload',
                'reply' => $reply,
                'redirect_url' => null,
                'history' => assistantChatHistory(),
            ];
        }

        $cleanResult = runLocalAssistant($payload, 'clean', $assistantMemory, true);
        $structuredResult = runLocalAssistant((string)($cleanResult['output'] ?? ''), 'structure', $assistantMemory, true);
        $systemReady = trim((string)($structuredResult['output'] ?? ''));
        if ($systemReady === '') {
            $systemReady = trim((string)($cleanResult['output'] ?? ''));
        }

        $reply = 'Done. Here is the cleaned SongShelf-ready format with corrected chord placement.';
        assistantPushChatEntry('assistant', $reply, [
            'intent' => 'clean_song',
            'input' => $payload,
            'summary' => (string)($structuredResult['summary'] ?? $cleanResult['summary'] ?? ''),
            'output' => $systemReady,
        ]);
        return [
            'ok' => true,
            'intent' => 'clean_song',
            'reply' => $reply,
            'output' => $systemReady,
            'redirect_url' => null,
            'history' => assistantChatHistory(),
        ];
    }

    $reply = 'I can chat naturally, but my scope is only SongShelf. I can find songs in your library, clean pasted chords and lyrics, and learn preferences with "Remember: ...".';
    assistantPushChatEntry('assistant', $reply, ['intent' => 'fallback']);
    return [
        'ok' => true,
        'intent' => 'fallback',
        'reply' => $reply,
        'redirect_url' => null,
        'history' => assistantChatHistory(),
    ];
}

$pdo = db();
$action = route();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'save-song') {
        $id = (int)($_POST['id'] ?? 0);
        $existingSong = null;

        if ($id > 0) {
            $stmt = $pdo->prepare('SELECT * FROM songs WHERE id=?');
            $stmt->execute([$id]);
            $existingSong = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }

        $isPdf = $existingSong ? songIsPdf($existingSong) : false;
        $body = $isPdf ? (string)($existingSong['body'] ?? '') : normalizeSongBody($_POST['body'] ?? '', true);
        $notationStyle = 'chords_over_lyrics';
        $meta = $isPdf ? [
            'title' => $existingSong['title'] ?? '',
            'artist' => $existingSong['artist'] ?? '',
            'key_name' => $existingSong['key_name'] ?? '',
            'capo' => $existingSong['capo'] ?? 0,
            'tags' => $existingSong['tags'] ?? '',
        ] : parseMetadataFromBody($body);
        $title = trim($_POST['title'] ?? $meta['title'] ?: 'Untitled Song');
        $artist = trim($_POST['artist'] ?? $meta['artist']);
        $detectedKey = $isPdf ? '' : detectSongKey($body);
        $key = $isPdf ? (string)($existingSong['key_name'] ?? '') : trim($_POST['key_name'] ?? $meta['key_name'] ?: $detectedKey);
        $capo = $isPdf ? (int)($existingSong['capo'] ?? 0) : (int)($_POST['capo'] ?? $meta['capo']);
        $tags = trim($_POST['tags'] ?? $meta['tags']);
        $bpm = sanitizeSongBpm($_POST['bpm'] ?? ($existingSong['bpm'] ?? 0));
        $timeSignature = normalizeSongTimeSignature((string)($_POST['time_signature'] ?? ($existingSong['time_signature'] ?? '4/4')));
        $scrollSpeed = sanitizeSongScrollSpeed($_POST['scroll_speed'] ?? ($existingSong['scroll_speed'] ?? 0), $isPdf);
        $audioFilePath = (string)($existingSong['audio_file_path'] ?? '');
        if ($audioFilePath !== '') {
            $existingAudioPath = absoluteDataPath($audioFilePath);
            if (is_file($existingAudioPath)) {
                @unlink($existingAudioPath);
            }
        }
        $audioSourceType = 'none';
        $audioUrl = '';
        $audioTitle = '';
        $audioFilePath = '';
        $audioMimeType = '';

        if ($id > 0) {
            $stmt = $pdo->prepare('UPDATE songs SET title=?, artist=?, key_name=?, capo=?, tags=?, notation_style=?, body=?, audio_source_type=?, audio_url=?, audio_title=?, audio_file_path=?, audio_mime_type=?, bpm=?, time_signature=?, scroll_speed=?, updated_at=? WHERE id=?');
            $stmt->execute([$title, $artist, $key, $capo, $tags, $notationStyle, $body, $audioSourceType, $audioUrl, $audioTitle, $audioFilePath, $audioMimeType, $bpm, $timeSignature, $scrollSpeed, now(), $id]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO songs (title, artist, key_name, capo, tags, notation_style, source_format, body, audio_source_type, audio_url, audio_title, audio_file_path, audio_mime_type, bpm, time_signature, scroll_speed, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$title, $artist, $key, $capo, $tags, $notationStyle, 'manual', $body, $audioSourceType, $audioUrl, $audioTitle, $audioFilePath, $audioMimeType, $bpm, $timeSignature, $scrollSpeed, now(), now()]);
            $id = (int)$pdo->lastInsertId();
        }
        redirect('?action=view-song&id=' . $id);
    }

    if ($action === 'save-song-bpm' || $action === 'save-song-playback') {
        $id = (int)($_POST['id'] ?? 0);
        header('Content-Type: application/json; charset=utf-8');

        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'message' => 'Missing song id']);
            return;
        }

        $lookup = $pdo->prepare('SELECT id, source_format, bpm, time_signature, scroll_speed FROM songs WHERE id=?');
        $lookup->execute([$id]);
        $song = $lookup->fetch(PDO::FETCH_ASSOC);
        if (!$song) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'message' => 'Song not found']);
            return;
        }

        $isPdf = songIsPdf($song);
        $bpm = sanitizeSongBpm($_POST['bpm'] ?? ($song['bpm'] ?? 0));
        $timeSignature = normalizeSongTimeSignature((string)($_POST['time_signature'] ?? ($song['time_signature'] ?? '4/4')));
        $scrollSpeed = sanitizeSongScrollSpeed($_POST['scroll_speed'] ?? ($song['scroll_speed'] ?? 0), $isPdf);

        $stmt = $pdo->prepare('UPDATE songs SET bpm=?, time_signature=?, scroll_speed=?, updated_at=? WHERE id=?');
        $stmt->execute([$bpm, $timeSignature, $scrollSpeed, now(), $id]);
        echo json_encode([
            'ok' => true,
            'bpm' => $bpm,
            'time_signature' => $timeSignature,
            'scroll_speed' => $scrollSpeed,
        ]);
        return;
    }

    if ($action === 'import') {
        $imported = 0;
        $errors = [];
        foreach ($_FILES['songs']['tmp_name'] ?? [] as $index => $tmpName) {
            if (!is_uploaded_file($tmpName)) {
                continue;
            }
            $name = $_FILES['songs']['name'][$index] ?? 'song.txt';
            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

            if ($extension === 'pdf') {
                $storedName = bin2hex(random_bytes(16)) . '.pdf';
                $targetPath = uploadDir() . '/' . $storedName;
                if (!move_uploaded_file($tmpName, $targetPath)) {
                    $errors[] = $name . ': failed to store PDF';
                    continue;
                }

                $title = pathinfo($name, PATHINFO_FILENAME);
                $stmt = $pdo->prepare('INSERT INTO songs (title, artist, key_name, capo, tags, source_format, body, file_path, mime_type, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([
                    $title,
                    '',
                    '',
                    0,
                    '',
                    'pdf',
                    '',
                    relativeDataPath($targetPath),
                    'application/pdf',
                    now(),
                    now(),
                ]);
                $imported++;
                continue;
            }

            $content = file_get_contents($tmpName);
            if ($content === false) {
                $errors[] = $name . ': failed to read';
                continue;
            }
            $body = normalizeSongBody($content, true);
            $meta = parseMetadataFromBody($body);
            $title = $meta['title'] ?: pathinfo($name, PATHINFO_FILENAME);
            $artist = $meta['artist'] ?? '';
            $key = ($meta['key_name'] ?? '') ?: detectSongKey($body);
            $capo = (int)($meta['capo'] ?? 0);
            $tags = $meta['tags'] ?? '';
            $notationStyle = 'chords_over_lyrics';
            $stmt = $pdo->prepare('INSERT INTO songs (title, artist, key_name, capo, tags, notation_style, source_format, body, file_path, mime_type, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$title, $artist, $key, $capo, $tags, $notationStyle, 'import', $body, '', 'text/plain', now(), now()]);
            $imported++;
        }
        redirect('?action=songs&imported=' . $imported . '&errors=' . urlencode(implode('; ', $errors)));
    }

    if ($action === 'paste-song') {
        $raw = trim($_POST['raw_song'] ?? '');
        if ($raw === '') {
            redirect('?action=import&message=' . urlencode('Paste a song first.'));
        }
        $body = normalizeSongBody($raw, true);
        $meta = parseMetadataFromBody($body);
        $title = trim($_POST['title'] ?? '') ?: ($meta['title'] ?: 'Untitled Song');
        $artist = trim($_POST['artist'] ?? '') ?: ($meta['artist'] ?? '');
        $key = trim($_POST['key_name'] ?? '') ?: ($meta['key_name'] ?? '') ?: detectSongKey($body);
        $capo = trim($_POST['capo'] ?? '') !== '' ? (int)$_POST['capo'] : (int)($meta['capo'] ?? 0);
        $tags = trim($_POST['tags'] ?? '') ?: ($meta['tags'] ?? '');
        $notationStyle = 'chords_over_lyrics';

        $stmt = $pdo->prepare('INSERT INTO songs (title, artist, key_name, capo, tags, notation_style, source_format, body, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$title, $artist, $key, $capo, $tags, $notationStyle, 'paste', $body, now(), now()]);
        $id = (int)$pdo->lastInsertId();
        redirect('?action=view-song&id=' . $id . '&message=' . urlencode('Song pasted and saved.'));
    }

    if ($action === 'save-ai-memory') {
        saveAssistantMemory(
            (string)($_POST['custom_instructions'] ?? ''),
            (string)($_POST['preferred_terms'] ?? ''),
            (string)($_POST['repertoire_notes'] ?? '')
        );
        redirect('?action=ai-assistant&saved=1');
    }

    if ($action === 'ai-run') {
        $assistantMemory = getAssistantMemory();
        $assistantTrainingExamples = getAssistantTrainingExamples(10);
        $assistantChatHistory = assistantChatHistory();
        $assistantInput = (string)($_POST['assistant_input'] ?? '');
        $assistantTask = (string)($_POST['assistant_task'] ?? 'clean');
        $assistantUseTraining = !empty($_POST['use_training']);
        $assistantResult = null;

        if (trim($assistantInput) !== '') {
            $assistantResult = runLocalAssistant($assistantInput, $assistantTask, $assistantMemory, $assistantUseTraining);
        }

        view('ai-assistant', compact(
            'assistantMemory',
            'assistantTrainingExamples',
            'assistantInput',
            'assistantTask',
            'assistantUseTraining',
            'assistantResult',
            'assistantChatHistory'
        ));
        return;
    }

    if ($action === 'ai-chat') {
        $result = assistantHandleChatMessage($pdo, (string)($_POST['assistant_message'] ?? ''));
        if (!empty($result['redirect_url'])) {
            redirect((string)$result['redirect_url']);
        }
        redirect('?action=ai-assistant');
    }

    if ($action === 'ai-chat-api') {
        $result = assistantHandleChatMessage($pdo, (string)($_POST['assistant_message'] ?? ''));
        header('Content-Type: application/json; charset=utf-8');
        if (($result['ok'] ?? false) !== true) {
            http_response_code(400);
        }
        echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return;
    }

    if ($action === 'ai-chat-reset-api') {
        clearAssistantChatMessages();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok' => true,
            'history' => assistantChatHistory(),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return;
    }

    if ($action === 'ai-train') {
        $trainingInput = trim((string)($_POST['training_input'] ?? ''));
        $trainingOutput = trim((string)($_POST['training_output'] ?? ''));
        $trainingLabel = trim((string)($_POST['training_label'] ?? ''));
        $trainingType = (string)($_POST['training_type'] ?? 'pair');

        if ($trainingInput !== '' && $trainingOutput !== '') {
            addAssistantTrainingExample($trainingInput, $trainingOutput, $trainingLabel, $trainingType);
            redirect('?action=ai-assistant&trained=1');
        }

        redirect('?action=ai-assistant&trained=0');
    }

    if ($action === 'upload-developer-photo') {
        $category = trim((string)($_POST['photo_category'] ?? 'general'));
        if (!in_array($category, ['general', 'art_exhibit', 'cover', 'avatar'], true)) {
            $category = 'general';
        }

        if (!isset($_FILES['developer_photo'])) {
            redirect('?action=about-developer&upload=0');
        }

        $photoTitle = trim((string)($_POST['photo_title'] ?? ''));
        $filesInput = $_FILES['developer_photo'];
        $tmpNames = $filesInput['tmp_name'] ?? null;
        $errors = $filesInput['error'] ?? null;
        $names = $filesInput['name'] ?? null;

        $normalizedFiles = [];
        if (is_array($tmpNames)) {
            foreach ($tmpNames as $index => $tmpName) {
                $normalizedFiles[] = [
                    'tmp_name' => (string)$tmpName,
                    'error' => (int)($errors[$index] ?? UPLOAD_ERR_NO_FILE),
                    'name' => (string)($names[$index] ?? ''),
                ];
            }
        } else {
            $normalizedFiles[] = [
                'tmp_name' => (string)($tmpNames ?? ''),
                'error' => (int)($errors ?? UPLOAD_ERR_NO_FILE),
                'name' => (string)($names ?? ''),
            ];
        }

        $uploadableFiles = array_values(array_filter(
            $normalizedFiles,
            static fn (array $file): bool => $file['error'] === UPLOAD_ERR_OK && is_uploaded_file($file['tmp_name'])
        ));
        if ($uploadableFiles === []) {
            redirect('?action=about-developer&upload=0');
        }

        // Cover and avatar should always take a single selected image.
        if (in_array($category, ['cover', 'avatar'], true)) {
            $uploadableFiles = [$uploadableFiles[0]];
        }

        $validatedFiles = [];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        foreach ($uploadableFiles as $file) {
            $tmpName = $file['tmp_name'];
            $photoBytes = file_get_contents($tmpName);
            if ($photoBytes === false || $photoBytes === '') {
                if ($finfo) {
                    finfo_close($finfo);
                }
                redirect('?action=about-developer&upload=0');
            }

            $mimeType = $finfo ? (string)finfo_file($finfo, $tmpName) : '';
            if (!isAllowedDeveloperPhotoMimeType($mimeType)) {
                if ($finfo) {
                    finfo_close($finfo);
                }
                redirect('?action=about-developer&upload=0');
            }

            if (strlen($photoBytes) > (8 * 1024 * 1024)) {
                if ($finfo) {
                    finfo_close($finfo);
                }
                redirect('?action=about-developer&upload=0');
            }

            $validatedFiles[] = [
                'mime_type' => $mimeType,
                'blob_data' => $photoBytes,
                'name' => $file['name'],
            ];
        }
        if ($finfo) {
            finfo_close($finfo);
        }

        $stmt = $pdo->prepare('INSERT INTO developer_photos (category, title, mime_type, blob_data, created_at) VALUES (?, ?, ?, ?, ?)');
        $totalFiles = count($validatedFiles);
        foreach ($validatedFiles as $index => $file) {
            $baseName = trim((string)pathinfo($file['name'], PATHINFO_FILENAME));
            $title = $photoTitle !== '' ? $photoTitle : $baseName;
            if ($totalFiles > 1 && $title !== '') {
                $title .= ' (' . ($index + 1) . ')';
            }

            $stmt->bindValue(1, $category, PDO::PARAM_STR);
            $stmt->bindValue(2, $title, PDO::PARAM_STR);
            $stmt->bindValue(3, $file['mime_type'], PDO::PARAM_STR);
            $stmt->bindValue(4, $file['blob_data'], PDO::PARAM_LOB);
            $stmt->bindValue(5, now(), PDO::PARAM_STR);
            $stmt->execute();
        }

        $successAnchor = in_array($category, ['general', 'art_exhibit'], true) ? '#photos' : '#about';
        redirect('?action=about-developer&upload=1' . $successAnchor);
    }

    if ($action === 'save-setlist') {
        $name = trim($_POST['name'] ?? 'Untitled Setlist');
        $notes = trim($_POST['notes'] ?? '');
        $songIds = array_values(array_filter(array_map('intval', $_POST['song_ids'] ?? [])));
        $id = (int)($_POST['id'] ?? 0);

        if ($id > 0) {
            $pdo->prepare('UPDATE setlists SET name=?, notes=?, updated_at=? WHERE id=?')->execute([$name, $notes, now(), $id]);
            $pdo->prepare('DELETE FROM setlist_songs WHERE setlist_id=?')->execute([$id]);
        } else {
            $pdo->prepare('INSERT INTO setlists (name, notes, created_at, updated_at) VALUES (?, ?, ?, ?)')->execute([$name, $notes, now(), now()]);
            $id = (int)$pdo->lastInsertId();
        }

        $position = 1;
        $stmt = $pdo->prepare('INSERT INTO setlist_songs (setlist_id, song_id, position) VALUES (?, ?, ?)');
        foreach ($songIds as $songId) {
            if ($songId > 0) {
                $stmt->execute([$id, $songId, $position++]);
            }
        }
        redirect('?action=view-setlist&id=' . $id);
    }

    if ($action === 'reorder-setlist') {
        $id = (int)($_POST['id'] ?? 0);
        $songIds = array_values(array_filter(array_map('intval', $_POST['song_ids'] ?? [])));

        if ($id <= 0) {
            redirect('?action=setlists');
        }

        $pdo->beginTransaction();
        try {
            $pdo->prepare('DELETE FROM setlist_songs WHERE setlist_id=?')->execute([$id]);
            $stmt = $pdo->prepare('INSERT INTO setlist_songs (setlist_id, song_id, position) VALUES (?, ?, ?)');
            $position = 1;
            foreach ($songIds as $songId) {
                $stmt->execute([$id, $songId, $position++]);
            }
            $pdo->prepare('UPDATE setlists SET updated_at=? WHERE id=?')->execute([now(), $id]);
            $pdo->commit();
        } catch (Throwable $error) {
            $pdo->rollBack();
        }

        redirect('?action=view-setlist&id=' . $id);
    }
}

if ($action === 'ai-chat-reset') {
    clearAssistantChatMessages();
    redirect('?action=ai-assistant');
}

if ($action === 'delete-song' && isset($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT file_path, source_format, mime_type, audio_file_path FROM songs WHERE id=?');
    $stmt->execute([(int)$_GET['id']]);
    $song = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($song && songHasAttachedPdf($song)) {
        $path = absoluteDataPath($song['file_path']);
        if (is_file($path)) {
            unlink($path);
        }
    }
    if ($song && trim((string)($song['audio_file_path'] ?? '')) !== '') {
        $audioPath = absoluteDataPath((string)$song['audio_file_path']);
        if (is_file($audioPath)) {
            unlink($audioPath);
        }
    }
    $pdo->prepare('DELETE FROM songs WHERE id=?')->execute([(int)$_GET['id']]);
    redirect('?action=songs');
}

if ($action === 'delete-setlist' && isset($_GET['id'])) {
    $pdo->prepare('DELETE FROM setlists WHERE id=?')->execute([(int)$_GET['id']]);
    redirect('?action=setlists');
}

if ($action === 'remove-setlist-song' && isset($_GET['id'], $_GET['song_id'])) {
    $setlistId = (int)$_GET['id'];
    $songId = (int)$_GET['song_id'];

    if ($setlistId <= 0 || $songId <= 0) {
        redirect('?action=setlists');
    }

    $pdo->beginTransaction();
    try {
        $pdo->prepare('DELETE FROM setlist_songs WHERE setlist_id=? AND song_id=?')->execute([$setlistId, $songId]);

        $stmt = $pdo->prepare('SELECT song_id FROM setlist_songs WHERE setlist_id=? ORDER BY position');
        $stmt->execute([$setlistId]);
        $remainingSongIds = array_map('intval', array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'song_id'));

        $pdo->prepare('DELETE FROM setlist_songs WHERE setlist_id=?')->execute([$setlistId]);
        $insert = $pdo->prepare('INSERT INTO setlist_songs (setlist_id, song_id, position) VALUES (?, ?, ?)');
        $position = 1;
        foreach ($remainingSongIds as $remainingSongId) {
            $insert->execute([$setlistId, $remainingSongId, $position++]);
        }

        $pdo->prepare('UPDATE setlists SET updated_at=? WHERE id=?')->execute([now(), $setlistId]);
        $pdo->commit();
    } catch (Throwable $error) {
        $pdo->rollBack();
    }

    redirect('?action=view-setlist&id=' . $setlistId);
}

if ($action === 'songs') {
    $search = trim($_GET['q'] ?? '');
    $documentType = $_GET['type'] ?? 'all';
    $sort = $_GET['sort'] ?? 'title';
    if (!in_array($documentType, ['all', 'song', 'pdf'], true)) {
        $documentType = 'all';
    }
    if (!in_array($sort, ['title', 'artist', 'key', 'recent'], true)) {
        $sort = 'title';
    }

    $where = [];
    $params = [];

    if ($search !== '') {
        $like = '%' . $search . '%';
        $where[] = '(title LIKE ? OR artist LIKE ? OR tags LIKE ?)';
        array_push($params, $like, $like, $like);
    }

    if ($documentType === 'pdf') {
        $where[] = 'source_format = ?';
        $params[] = 'pdf';
    } elseif ($documentType === 'song') {
        $where[] = 'source_format != ?';
        $params[] = 'pdf';
    }

    $sql = 'SELECT * FROM songs';
    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    if ($sort === 'artist') {
        $sql .= " ORDER BY CASE WHEN TRIM(artist) = '' THEN 1 ELSE 0 END, artist COLLATE NOCASE, title COLLATE NOCASE";
    } elseif ($sort === 'key') {
        $sql .= " ORDER BY CASE WHEN TRIM(key_name) = '' THEN 1 ELSE 0 END, key_name COLLATE NOCASE, title COLLATE NOCASE";
    } elseif ($sort === 'recent') {
        $sql .= ' ORDER BY updated_at DESC, title COLLATE NOCASE';
    } else {
        $sql .= ' ORDER BY title COLLATE NOCASE';
    }

    if ($params) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    } else {
        $stmt = $pdo->query($sql);
    }

    $songs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    view('songs', compact('songs', 'search', 'documentType', 'sort'));
    return;
}

if ($action === 'ai-assistant') {
    $assistantMemory = getAssistantMemory();
    $assistantTrainingExamples = getAssistantTrainingExamples(10);
    $assistantChatHistory = assistantChatHistory();
    $assistantInput = '';
    $assistantTask = 'clean';
    $assistantUseTraining = true;
    $assistantResult = null;
    view('ai-assistant', compact('assistantMemory', 'assistantTrainingExamples', 'assistantInput', 'assistantTask', 'assistantUseTraining', 'assistantResult', 'assistantChatHistory'));
    return;
}

if ($action === 'new-song' || $action === 'edit-song') {
    $song = ['id' => 0, 'title' => '', 'artist' => '', 'key_name' => '', 'capo' => 0, 'tags' => '', 'notation_style' => 'chords_over_lyrics', 'body' => "{title: }\n{artist: }\n{key: C}\n\nC            G\nType your song here", 'audio_source_type' => 'none', 'audio_url' => '', 'audio_title' => '', 'audio_file_path' => '', 'audio_mime_type' => '', 'bpm' => 0, 'time_signature' => '4/4', 'scroll_speed' => 4.0];
    if ($action === 'edit-song' && isset($_GET['id'])) {
        $stmt = $pdo->prepare('SELECT * FROM songs WHERE id=?');
        $stmt->execute([(int)$_GET['id']]);
        $song = $stmt->fetch(PDO::FETCH_ASSOC) ?: $song;
    }
    view('song-form', compact('song'));
    return;
}

if ($action === 'view-song' && isset($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT * FROM songs WHERE id=?');
    $stmt->execute([(int)$_GET['id']]);
    $song = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$song) {
        http_response_code(404);
        echo 'Song not found';
        return;
    }
    $transpose = (int)($_GET['transpose'] ?? 0);
    $displayMode = 'over';
    $rendered = songIsPdf($song) ? '' : renderSongHtml($song['body'], $transpose, $displayMode);
    $pdfPages = songIsPdf($song) ? renderPdfPages($song) : [];
    $songChordReferences = songIsPdf($song)
        ? extractPdfSongChordReferences($song, $transpose)
        : extractSongChordReferences((string)$song['body'], $transpose);
    view('song-view', compact('song', 'transpose', 'rendered', 'pdfPages', 'songChordReferences'));
    return;
}

if ($action === 'song-file' && isset($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT id, title, file_path, mime_type, source_format FROM songs WHERE id=?');
    $stmt->execute([(int)$_GET['id']]);
    $song = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$song || !songHasAttachedPdf($song)) {
        http_response_code(404);
        echo 'File not found';
        return;
    }

    $path = absoluteDataPath($song['file_path']);
    if (!is_file($path)) {
        http_response_code(404);
        echo 'File missing';
        return;
    }

    header('Content-Type: ' . ($song['mime_type'] ?: 'application/octet-stream'));
    header('Content-Length: ' . (string)filesize($path));
    header('Content-Disposition: inline; filename="' . rawurlencode($song['title'] ?: 'song') . '.pdf"');
    readfile($path);
    return;
}

if ($action === 'print-song' && isset($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT * FROM songs WHERE id=?');
    $stmt->execute([(int)$_GET['id']]);
    $song = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$song) {
        http_response_code(404);
        echo 'Song not found';
        return;
    }
    if (songIsPdf($song)) {
        redirect(songFileUrl($song));
    }

    $transpose = (int)($_GET['transpose'] ?? 0);
    $rendered = renderSongHtml((string)$song['body'], $transpose, 'over');
    require APP_ROOT . '/views/print-song.php';
    return;
}

if ($action === 'export-library') {
    $payload = appExportPackage($pdo);
    $filename = 'songshelf-export-' . date('Ymd-His') . '.json';
    $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        http_response_code(500);
        echo 'Export failed';
        return;
    }

    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . (string)strlen($json));
    echo $json;
    return;
}

if ($action === 'backup-app') {
    $filenameBase = 'songshelf-backup-' . date('Ymd-His');

    if (class_exists('ZipArchive')) {
        $tempPath = tempnam(sys_get_temp_dir(), 'songshelf_backup_');
        if ($tempPath !== false) {
            $zipPath = $tempPath . '.zip';
            @unlink($tempPath);
            $zip = new ZipArchive();

            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                $zip->addFile(DB_PATH, 'data/songbook.sqlite');
                addDirectoryToZip($zip, uploadDir(), 'data/uploads');
                $exportJson = json_encode(appExportPackage($pdo), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                if ($exportJson !== false) {
                    $zip->addFromString('data/export.json', $exportJson);
                }
                $zip->close();

                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . $filenameBase . '.zip"');
                header('Content-Length: ' . (string)filesize($zipPath));
                readfile($zipPath);
                @unlink($zipPath);
                return;
            }
        }
    }

    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filenameBase . '.sqlite"');
    header('Content-Length: ' . (string)filesize(DB_PATH));
    readfile(DB_PATH);
    return;
}

if ($action === 'song-page-image' && isset($_GET['id'], $_GET['page'])) {
    $stmt = $pdo->prepare('SELECT id, file_path, mime_type, source_format FROM songs WHERE id=?');
    $stmt->execute([(int)$_GET['id']]);
    $song = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$song || !songIsPdf($song)) {
        http_response_code(404);
        echo 'Image not found';
        return;
    }

    $page = max(1, (int)$_GET['page']);
    $pages = renderPdfPages($song);
    $expected = 'page-' . $page . '.png';
    if (!in_array($expected, $pages, true)) {
        http_response_code(404);
        echo 'Page not found';
        return;
    }

    $path = pdfRenderDir((int)$song['id']) . '/' . $expected;
    if (!is_file($path)) {
        http_response_code(404);
        echo 'Page missing';
        return;
    }

    header('Content-Type: image/png');
    header('Content-Length: ' . (string)filesize($path));
    readfile($path);
    return;
}

if ($action === 'developer-photo' && isset($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT mime_type, blob_data FROM developer_photos WHERE id=?');
    $stmt->execute([(int)$_GET['id']]);
    $photo = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$photo) {
        http_response_code(404);
        echo 'Photo not found';
        return;
    }
    $mimeType = (string)($photo['mime_type'] ?? 'application/octet-stream');
    $bytes = (string)($photo['blob_data'] ?? '');
    if ($bytes === '') {
        http_response_code(404);
        echo 'Photo missing';
        return;
    }
    header('Content-Type: ' . $mimeType);
    header('Content-Length: ' . (string)strlen($bytes));
    echo $bytes;
    return;
}

if ($action === 'import') {
    view('import');
    return;
}

if ($action === 'setlists') {
    $setlists = $pdo->query('SELECT * FROM setlists ORDER BY updated_at DESC')->fetchAll(PDO::FETCH_ASSOC);
    view('setlists', compact('setlists'));
    return;
}

if ($action === 'chord-diagrams') {
    $instrument = 'piano';
    $chords = chordLibrary($instrument);
    view('chord-diagrams', compact('instrument', 'chords'));
    return;
}

if ($action === 'settings') {
    view('settings');
    return;
}

if ($action === 'about-developer') {
    $uploadedFlag = $_GET['upload'] ?? '';
    $uploadSuccess = $uploadedFlag === '1';
    $uploadFailed = $uploadedFlag === '0';

    $photoStmt = $pdo->query('SELECT id, category, title, mime_type, created_at FROM developer_photos ORDER BY created_at DESC');
    $developerPhotos = $photoStmt->fetchAll(PDO::FETCH_ASSOC);
    $developerGeneralPhotos = array_values(array_filter($developerPhotos, static fn (array $photo): bool => ($photo['category'] ?? '') === 'general'));
    $developerArtPhotos = array_values(array_filter($developerPhotos, static fn (array $photo): bool => ($photo['category'] ?? '') === 'art_exhibit'));
    $developerCoverPhotos = array_values(array_filter($developerPhotos, static fn (array $photo): bool => ($photo['category'] ?? '') === 'cover'));
    $developerAvatarPhotos = array_values(array_filter($developerPhotos, static fn (array $photo): bool => ($photo['category'] ?? '') === 'avatar'));
    $developerCoverPhoto = $developerCoverPhotos[0] ?? null;
    $developerAvatarPhoto = $developerAvatarPhotos[0] ?? null;

    view('about-developer', compact(
        'uploadSuccess',
        'uploadFailed',
        'developerGeneralPhotos',
        'developerArtPhotos',
        'developerCoverPhoto',
        'developerAvatarPhoto'
    ));
    return;
}

if ($action === 'new-setlist' || $action === 'edit-setlist') {
    $setlist = ['id' => 0, 'name' => '', 'notes' => ''];
    $selected = [];
    if ($action === 'edit-setlist' && isset($_GET['id'])) {
        $stmt = $pdo->prepare('SELECT * FROM setlists WHERE id=?');
        $stmt->execute([(int)$_GET['id']]);
        $setlist = $stmt->fetch(PDO::FETCH_ASSOC) ?: $setlist;
        $stmt = $pdo->prepare('SELECT song_id FROM setlist_songs WHERE setlist_id=? ORDER BY position');
        $stmt->execute([$setlist['id']]);
        $selected = array_map('intval', array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'song_id'));
    }
    $songs = $pdo->query('SELECT id, title, artist, tags FROM songs ORDER BY title COLLATE NOCASE')->fetchAll(PDO::FETCH_ASSOC);
    view('setlist-form', compact('setlist', 'songs', 'selected'));
    return;
}

if ($action === 'view-setlist' && isset($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT * FROM setlists WHERE id=?');
    $stmt->execute([(int)$_GET['id']]);
    $setlist = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$setlist) {
        http_response_code(404);
        echo 'Setlist not found';
        return;
    }
    $stmt = $pdo->prepare('SELECT s.* FROM setlist_songs ss JOIN songs s ON s.id = ss.song_id WHERE ss.setlist_id=? ORDER BY ss.position');
    $stmt->execute([$setlist['id']]);
    $songs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    view('setlist-view', compact('setlist', 'songs'));
    return;
}

redirect('?action=songs');
