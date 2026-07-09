#!/usr/bin/env python3
import os
import re
import shutil
from pathlib import Path

ROOT = Path.cwd()
SKIP_DIRS = {".git", "build", "android", "ios", "linux", "macos", "windows", ".gradle", ".dart_tool", "generated"}
BACKUP_ROOT = ROOT / "comment_backup"

def is_text_file(path: Path) -> bool:
    try:
        with open(path, "rb") as f:
            chunk = f.read(1024)
            if b"\0" in chunk:
                return False
    except Exception:
        return False
    return True

def should_skip_path(path: Path) -> bool:
    parts = {p for p in path.parts}
    return not parts.isdisjoint(SKIP_DIRS)

def backup_file(path: Path, rel: Path):
    dest = BACKUP_ROOT / rel
    dest.parent.mkdir(parents=True, exist_ok=True)
    shutil.copy2(path, dest)

def process_file(path: Path, rel: Path):
    try:
        if not is_text_file(path):
            return 0
        text = path.read_text(encoding="utf-8", errors="replace")
    except Exception:
        return 0

    changed = False
    new_lines = []
    count_removed = 0
    for line in text.splitlines(keepends=True):
        # remove '
        new_line, n = re.subn(r'(?<!:)
        if new_line != line:
            changed = True
            count_removed += n
        new_lines.append(new_line)

    if changed:
        backup_file(path, rel)
        path.write_text(''.join(new_lines), encoding="utf-8")
    return count_removed

def main():
    BACKUP_ROOT.mkdir(parents=True, exist_ok=True)
    total_files = 0
    changed_files = 0
    total_removed = 0

    for root, dirs, files in os.walk(ROOT):
        root_path = Path(root)
        # prune skip dirs
        dirs[:] = [d for d in dirs if d not in SKIP_DIRS]
        for fname in files:
            fpath = root_path / fname
            rel = fpath.relative_to(ROOT)
            if should_skip_path(rel):
                continue
            total_files += 1
            removed = process_file(fpath, rel)
            if removed > 0:
                changed_files += 1
                total_removed += removed

    print(f"Processed files: {total_files}")
    print(f"Files changed: {changed_files}")
    print(f"'
    print(f"Backups saved under: {BACKUP_ROOT}")

if __name__ == '__main__':
    main()
