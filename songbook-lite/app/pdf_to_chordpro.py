#!/usr/bin/env python3
import argparse
import json
import re
import subprocess
import sys
import tempfile
from pathlib import Path


CHORD_RE = re.compile(
    r"^(?:N\.?C\.?|[A-G](?:[#b]|♯|♭)?(?:(?:maj|min|m|sus|dim|aug|add|no|omit|M)"
    r"[0-9A-Za-z#b+\-]*)*(?:[0-9#b+\-]+)?(?:\([^)]+\))?(?:/[A-G](?:[#b]|♯|♭)?)?)$"
)
SECTION_RE = re.compile(
    r"^(intro|verse(?:\s+\d+)?|chorus|refrain|bridge|pre-chorus|post-chorus|tag|outro|ending|instrumental)(?:[:\s].*)?$",
    re.IGNORECASE,
)
IGNORED_TOKENS = {"|", "||", "|||", "/", "//", "///"}


def extract_layout_text(pdf_path: Path) -> str:
    result = subprocess.run(
        ["pdftotext", "-layout", "-nopgbrk", str(pdf_path), "-"],
        stdout=subprocess.PIPE,
        stderr=subprocess.DEVNULL,
        check=False,
        text=True,
    )
    if result.returncode != 0:
        return ""
    return result.stdout


def detect_music_notation(pdf_path: Path) -> dict:
    symbol_hits = 0
    try:
        raw_text = extract_layout_text(pdf_path)
        symbol_hits = len(re.findall(r"[♩♪♫♬♭♮♯𝄞𝄢𝄡𝄐𝄑𝄒𝄓𝄔𝄕𝄗𝄘𝄙]", raw_text))
        if symbol_hits >= 3:
            return {"notation_detected": True, "notation_signal": symbol_hits}
    except Exception:
        symbol_hits = 0

    staff_groups = 0
    with tempfile.TemporaryDirectory(prefix="songshelf_notation_") as temp_dir:
        prefix = str(Path(temp_dir) / "page")
        result = subprocess.run(
            ["pdftoppm", "-gray", "-r", "72", "-f", "1", "-l", "3", str(pdf_path), prefix],
            stdout=subprocess.DEVNULL,
            stderr=subprocess.DEVNULL,
            check=False,
            text=False,
        )
        if result.returncode != 0:
            return {"notation_detected": False, "notation_signal": symbol_hits}

        for page_path in sorted(Path(temp_dir).glob("page-*.pgm")):
            try:
                staff_groups += count_staff_groups(page_path)
            except Exception:
                continue
            if staff_groups >= 2:
                return {"notation_detected": True, "notation_signal": staff_groups}

    return {"notation_detected": False, "notation_signal": max(symbol_hits, staff_groups)}


def count_staff_groups(image_path: Path) -> int:
    width, height, pixels = read_pgm(image_path)
    if width <= 0 or height <= 0:
        return 0

    dark_ratios = []
    for row_idx in range(height):
        row_start = row_idx * width
        row = pixels[row_start:row_start + width]
        dark = sum(1 for pixel in row if pixel < 150)
        dark_ratios.append(dark / width)

    candidate_rows = []
    idx = 0
    while idx < height:
        ratio = dark_ratios[idx]
        if 0.18 <= ratio <= 0.92:
            start = idx
            best_ratio = ratio
            while idx + 1 < height and 0.18 <= dark_ratios[idx + 1] <= 0.92:
                idx += 1
                best_ratio = max(best_ratio, dark_ratios[idx])
            end = idx
            if (end - start + 1) <= 4 and best_ratio >= 0.24:
                candidate_rows.append((start + end) / 2)
        idx += 1

    if len(candidate_rows) < 5:
        return 0

    groups = 0
    i = 0
    while i + 4 < len(candidate_rows):
        gaps = [candidate_rows[i + offset + 1] - candidate_rows[i + offset] for offset in range(4)]
        average_gap = sum(gaps) / 4
        if 2 <= average_gap <= 12 and all(abs(gap - average_gap) <= 2.2 for gap in gaps):
            groups += 1
            i += 5
            while i < len(candidate_rows) and candidate_rows[i] - candidate_rows[i - 1] < average_gap * 2.5:
                i += 1
            continue
        i += 1

    return groups


def read_pgm(image_path: Path):
    data = image_path.read_bytes()
    if not data.startswith(b"P5"):
        raise ValueError("Unsupported PGM format")

    offset = 2
    header_parts = []
    while len(header_parts) < 3:
        while offset < len(data) and chr(data[offset]).isspace():
            offset += 1
        if offset < len(data) and data[offset:offset + 1] == b"#":
            while offset < len(data) and data[offset:offset + 1] not in (b"\n", b"\r"):
                offset += 1
            continue
        start = offset
        while offset < len(data) and not chr(data[offset]).isspace():
            offset += 1
        header_parts.append(data[start:offset].decode("ascii"))

    width = int(header_parts[0])
    height = int(header_parts[1])
    max_value = int(header_parts[2])
    if max_value > 255:
        raise ValueError("Unsupported bit depth")

    while offset < len(data) and chr(data[offset]).isspace():
        offset += 1

    pixel_bytes = data[offset:offset + width * height]
    if len(pixel_bytes) != width * height:
        raise ValueError("Incomplete pixel buffer")

    return width, height, pixel_bytes


def tokenize_nonspace(line: str):
    return list(re.finditer(r"\S+", line))


def clean_token(token: str) -> str:
    token = token.strip()
    token = token.strip("[]{}<>")
    token = token.rstrip(".,;:!?" )
    return token


def is_section_line(line: str) -> bool:
    value = line.strip().strip("[]()")
    return bool(value and SECTION_RE.match(value))


def section_to_directive(line: str) -> str:
    value = line.strip().strip("[]()")
    return "{comment: " + value + "}"


def is_chord_token(token: str) -> bool:
    cleaned = clean_token(token)
    if not cleaned or cleaned in IGNORED_TOKENS:
        return False
    return bool(CHORD_RE.match(cleaned))


def is_chord_line(line: str) -> bool:
    stripped = line.strip()
    if not stripped or is_section_line(line):
        return False

    tokens = tokenize_nonspace(line)
    if not tokens:
        return False

    chordish = 0
    meaningful = 0
    for match in tokens:
        token = clean_token(match.group(0))
        if not token:
            continue
        if token in IGNORED_TOKENS:
            continue
        meaningful += 1
        if is_chord_token(token):
            chordish += 1

    if meaningful == 0:
        return False

    # Bias toward "mostly chord tokens" lines and reject lyric-like text.
    lowered_words = re.findall(r"[a-z]{3,}", stripped)
    if lowered_words and chordish < meaningful:
        return False

    return chordish > 0 and chordish / meaningful >= 0.75


def normalize_spacing(text: str) -> str:
    text = text.replace("\r\n", "\n").replace("\r", "\n").replace("\u00a0", " ")
    text = text.replace("\f", "\n\n")
    lines = [line.rstrip() for line in text.split("\n")]
    compact = []
    blank_run = 0
    for line in lines:
        if line.strip() == "":
            blank_run += 1
            if blank_run <= 2:
                compact.append("")
            continue
        blank_run = 0
        compact.append(line)
    return "\n".join(compact).strip()


def merge_chords_and_lyrics(chord_line: str, lyric_line: str) -> str:
    merged = lyric_line.rstrip()
    insertions = []
    for match in tokenize_nonspace(chord_line):
        token = clean_token(match.group(0))
        if not is_chord_token(token):
            continue
        insertions.append((match.start(), token))

    for pos, chord in sorted(insertions, key=lambda item: item[0], reverse=True):
        safe_pos = insertion_index_for_lyric(merged, pos)
        merged = merged[:safe_pos] + f"[{chord}]" + merged[safe_pos:]

    merged = re.sub(r"\[(.*?)\]\s+", r"[\1]", merged)
    return merged.rstrip()


def insertion_index_for_lyric(lyric_line: str, chord_pos: int) -> int:
    if chord_pos >= len(lyric_line):
        return len(lyric_line)

    idx = chord_pos
    while idx < len(lyric_line) and lyric_line[idx] == " ":
        idx += 1
    if idx >= len(lyric_line):
        return len(lyric_line)

    while idx > 0 and lyric_line[idx - 1] != " ":
        idx -= 1
    return idx


def looks_like_metadata(line: str) -> bool:
    lowered = line.strip().lower()
    prefixes = ("title:", "artist:", "key:", "capo:", "tags:", "by ")
    return lowered.startswith(prefixes)


def choose_title(lines, fallback_title: str) -> str:
    for line in lines[:5]:
        stripped = line.strip()
        if not stripped or is_chord_line(stripped) or looks_like_metadata(stripped):
            continue
        if len(stripped) > 80:
            continue
        return stripped
    return fallback_title


def convert_text_to_chordpro(text: str, fallback_title: str) -> dict:
    normalized = normalize_spacing(text)
    if not normalized:
        return {
            "body": "",
            "title": fallback_title,
            "chord_line_pairs": 0,
            "chords_detected": 0,
            "line_count": 0,
            "notation_detected": False,
            "notation_signal": 0,
        }

    lines = normalized.split("\n")
    output = []
    chord_line_pairs = 0
    chords_detected = 0
    i = 0

    while i < len(lines):
        line = lines[i]
        next_line = lines[i + 1] if i + 1 < len(lines) else None

        if is_section_line(line):
            output.append(section_to_directive(line))
            i += 1
            continue

        if (
            next_line is not None
            and is_chord_line(line)
            and next_line.strip() != ""
            and not is_chord_line(next_line)
            and not is_section_line(next_line)
        ):
            merged = merge_chords_and_lyrics(line, next_line)
            output.append(merged)
            chord_line_pairs += 1
            chords_detected += merged.count("[")
            i += 2
            continue

        output.append(line)
        i += 1

    body = "\n".join(output).strip()
    title = choose_title(lines, fallback_title)
    if title:
        pruned = body.split("\n")
        while pruned and pruned[0].strip() == "":
            pruned.pop(0)
        if pruned and pruned[0].strip() == title:
            pruned.pop(0)
        body = "\n".join(pruned).strip()
    if title:
        body = "{title: " + title + "}\n" + body

    return {
        "body": body.strip(),
        "title": title,
        "chord_line_pairs": chord_line_pairs,
        "chords_detected": chords_detected,
        "line_count": len(lines),
        "notation_detected": False,
        "notation_signal": 0,
    }


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--pdf")
    parser.add_argument("--text-file")
    parser.add_argument("--title", default="Untitled Song")
    args = parser.parse_args()

    source_text = ""
    if args.pdf:
        pdf_path = Path(args.pdf)
        source_text = extract_layout_text(pdf_path)
    elif args.text_file:
        source_text = Path(args.text_file).read_text(encoding="utf-8")
    else:
        parser.error("one of --pdf or --text-file is required")

    payload = convert_text_to_chordpro(source_text, args.title)
    if args.pdf:
        payload.update(detect_music_notation(pdf_path))
    payload["raw_text"] = normalize_spacing(source_text)
    json.dump(payload, sys.stdout, ensure_ascii=True)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
