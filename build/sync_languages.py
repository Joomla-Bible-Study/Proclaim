
import os
import re

def get_language_dirs(base_path):
    """
    Returns a list of language directories in the given base path.
    """
    if not os.path.exists(base_path):
        return []
    return [d for d in os.listdir(base_path) if os.path.isdir(os.path.join(base_path, d))]

def remove_duplicates(file_path):
    """
    Removes duplicate keys from a language file.
    """
    if not os.path.exists(file_path):
        return

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
    # print(f"Removed duplicates from {file_path}")

def prune_redundant_keys(source_file, target_file):
    """
    Removes keys from target_file that have the exact same value as in source_file.
    Does NOT add missing keys (Joomla falls back to en-GB automatically).
    """
    if not os.path.exists(source_file) or not os.path.exists(target_file):
        return

    with open(source_file, 'r', encoding='utf-8') as f:
        source_lines = f.readlines()

    source_map = {}
    for line in source_lines:
        stripped = line.strip()
        if '=' in line and not stripped.startswith(';'):
            parts = line.split('=', 1)
            key = parts[0].strip()
            value = parts[1].strip()
            source_map[key] = value

    with open(target_file, 'r', encoding='utf-8') as f:
        target_lines = f.readlines()

    new_target_lines = []
    removed_count = 0

    for line in target_lines:
        stripped = line.strip()
        if '=' in line and not stripped.startswith(';'):
            parts = line.split('=', 1)
            key = parts[0].strip()
            value = parts[1].strip()

            # Check if key exists in source and values match
            if key in source_map and source_map[key] == value:
                removed_count += 1
                continue # Skip this line (remove it)

        new_target_lines.append(line)

    if removed_count > 0:
        with open(target_file, 'w', encoding='utf-8') as f:
            f.writelines(new_target_lines)
        print(f"Removed {removed_count} redundant keys from {target_file}")

def process_directory(base_path, file_patterns):
    """
    Process all languages in the base path.
    """
    lang_dirs = get_language_dirs(base_path)
    if 'en-GB' not in lang_dirs:
        return

    source_lang = 'en-GB'
    target_langs = [lang for lang in lang_dirs if lang != source_lang]

    for file_pattern in file_patterns:
        source_file_name = f"{source_lang}.{file_pattern}"
        source_file_path = os.path.join(base_path, source_lang, source_file_name)

        # 1. Remove duplicates from source
        remove_duplicates(source_file_path)

        for target_lang in target_langs:
            target_file_name = f"{target_lang}.{file_pattern}"
            target_file_path = os.path.join(base_path, target_lang, target_file_name)

            # 2. Remove duplicates from target
            remove_duplicates(target_file_path)

            # 3. Prune redundant keys (remove if same as source)
            prune_redundant_keys(source_file_path, target_file_path)

def scan_and_process(root_dir):
    """
    Recursively scans for 'language' directories and processes them.
    Ignores 'vendor' and 'node_modules' directories.
    """
    for dirpath, dirnames, filenames in os.walk(root_dir):
        # Modify dirnames in-place to skip ignored directories
        if 'vendor' in dirnames:
            dirnames.remove('vendor')
        if 'node_modules' in dirnames:
            dirnames.remove('node_modules')

        if 'language' in dirnames:
            lang_path = os.path.join(dirpath, 'language')

            en_gb_path = os.path.join(lang_path, 'en-GB')
            if os.path.exists(en_gb_path):
                patterns = []
                for f in os.listdir(en_gb_path):
                    if f.startswith('en-GB.') and (f.endswith('.ini') or f.endswith('.sys.ini')):
                        pattern = f[6:]
                        patterns.append(pattern)

                if patterns:
                    print(f"Processing languages in {lang_path}")
                    process_directory(lang_path, patterns)

if __name__ == "__main__":
    script_dir = os.path.dirname(os.path.abspath(__file__))
    project_root = os.path.dirname(script_dir)

    print(f"Scanning project from {project_root}...")
    scan_and_process(project_root)
