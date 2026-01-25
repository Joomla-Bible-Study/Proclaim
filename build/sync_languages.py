
import os

def remove_duplicates(file_path):
    with open(file_path, 'r', encoding='utf-8') as f:
        lines = f.readlines()

    seen_keys = set()
    new_lines = []

    for line in lines:
        stripped_line = line.strip()
        if not stripped_line or stripped_line.startswith(';'):
            new_lines.append(line)
            continue

        if '=' in line:
            key = line.split('=', 1)[0].strip()
            if key in seen_keys:
                continue
            seen_keys.add(key)
            new_lines.append(line)
        else:
            new_lines.append(line)

    with open(file_path, 'w', encoding='utf-8') as f:
        f.writelines(new_lines)

if __name__ == "__main__":
    remove_duplicates('/Volumes/BCCExt_APFS_Extreme_Pro/GitHub/Proclaim/admin/language/en-GB/en-GB.com_proclaim.ini')
