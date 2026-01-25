import os
import re
import time
import json
import hashlib

# === CONFIGURATION ===

# When True: Keep all keys in target files (sync missing keys from source)
# When False: Prune keys that match source (Joomla falls back to en-GB)
SYNC_ALL_KEYS = True

# When True: Automatically translate missing/untranslated keys using Google Translate
# Requires: GOOGLE_TRANSLATE_API_KEY environment variable or set below
AUTO_TRANSLATE = True

# Google Translate API key - loaded in order of priority:
# 1. GOOGLE_TRANSLATE_API_KEY environment variable
# 2. 1Password CLI (if 'op' is available and OP_GOOGLE_TRANSLATE_REF is set)
# 3. Empty (translation disabled)
def get_api_key():
    """
    Securely retrieve API key from environment or 1Password.
    """
    # First check environment variable
    key = os.environ.get('GOOGLE_TRANSLATE_API_KEY', '')
    if key:
        return key

    # Check for 1Password secret reference
    op_ref = os.environ.get('OP_GOOGLE_TRANSLATE_REF', '')
    if op_ref:
        try:
            import subprocess
            result = subprocess.run(
                ['op', 'read', op_ref],
                capture_output=True,
                text=True,
                timeout=10
            )
            if result.returncode == 0:
                return result.stdout.strip()
            else:
                print(f"  Warning: Failed to read from 1Password: {result.stderr.strip()}")
        except FileNotFoundError:
            print("  Warning: 1Password CLI (op) not found. Install from https://1password.com/downloads/command-line/")
        except subprocess.TimeoutExpired:
            print("  Warning: 1Password CLI timed out (you may need to authenticate)")
        except Exception as e:
            print(f"  Warning: 1Password error: {e}")

    return ''

GOOGLE_API_KEY = get_api_key()

# Translation cache file (to avoid re-translating the same strings)
TRANSLATION_CACHE_FILE = os.path.join(os.path.dirname(os.path.abspath(__file__)), '.translation_cache.json')

# Map Joomla language codes to Google Translate language codes
LANG_CODE_MAP = {
    'en-GB': 'en',
    'de-DE': 'de',
    'es-ES': 'es',
    'nl-NL': 'nl',
    'hu-HU': 'hu',
    'no-NO': 'no',
    'cs-CZ': 'cs',
    'fr-FR': 'fr',
    'it-IT': 'it',
    'pt-BR': 'pt',
    'ru-RU': 'ru',
    'zh-CN': 'zh-CN',
    'ja-JP': 'ja',
    'ko-KR': 'ko',
}

# Replacements applied to the source language (en-GB)
SOURCE_REPLACEMENTS = {
    'Series': 'Theme'
}

# Global replacements applied to all non-source languages
GLOBAL_REPLACEMENTS = {
    # 'Series': 'Theme' # Removed to avoid forcing English 'Theme' into other languages
}

# Language-specific replacements (applied AFTER translation)
# Format: 'lang_code': {'Old': 'New'}
LANG_SPECIFIC_REPLACEMENTS = {
    'es-ES': {
        'Podcast': 'pódcast',
        'podcast': 'pódcast'
    }
}

# Specific key updates (force a specific value for a key)
# Format: 'lang_code': {'KEY_NAME': 'New Value'}
LANG_SPECIFIC_KEY_UPDATES = {
    # Example:
    # 'es-ES': {
    #     'JBS_CMN_PODCAST': 'pódcast'
    # }
}

# === TRANSLATION CACHE ===
_translation_cache = {}

def load_translation_cache():
    """Load the translation cache from disk."""
    global _translation_cache
    if os.path.exists(TRANSLATION_CACHE_FILE):
        try:
            with open(TRANSLATION_CACHE_FILE, 'r', encoding='utf-8') as f:
                _translation_cache = json.load(f)
        except (json.JSONDecodeError, IOError):
            _translation_cache = {}
    return _translation_cache

def save_translation_cache():
    """Save the translation cache to disk."""
    try:
        with open(TRANSLATION_CACHE_FILE, 'w', encoding='utf-8') as f:
            json.dump(_translation_cache, f, ensure_ascii=False, indent=2)
    except IOError as e:
        print(f"  Warning: Could not save translation cache: {e}")

def get_cache_key(text, target_lang):
    """Generate a cache key for a translation."""
    return hashlib.md5(f"{text}:{target_lang}".encode('utf-8')).hexdigest()

def translate_text(text, target_lang, source_lang='en'):
    """
    Translate text using Google Translate API.
    Returns the translated text or None if translation fails.
    """
    global _translation_cache

    if not GOOGLE_API_KEY:
        return None

    # Check cache first
    cache_key = get_cache_key(text, target_lang)
    if cache_key in _translation_cache:
        return _translation_cache[cache_key]

    try:
        import urllib.request
        import urllib.parse

        # Google Translate API v2 endpoint
        url = 'https://translation.googleapis.com/language/translate/v2'

        params = {
            'key': GOOGLE_API_KEY,
            'q': text,
            'source': source_lang,
            'target': target_lang,
            'format': 'text'
        }

        data = urllib.parse.urlencode(params).encode('utf-8')
        req = urllib.request.Request(url, data=data, method='POST')

        with urllib.request.urlopen(req, timeout=30) as response:
            result = json.loads(response.read().decode('utf-8'))

        if 'data' in result and 'translations' in result['data']:
            translated = result['data']['translations'][0]['translatedText']
            # Cache the result
            _translation_cache[cache_key] = translated
            return translated

    except Exception as e:
        print(f"    Translation error: {e}")
        return None

    return None

def translate_batch(texts, target_lang, source_lang='en'):
    """
    Translate multiple texts at once using Google Translate API.
    More efficient than translating one at a time.
    Returns a list of translated texts (or original if translation fails).
    """
    global _translation_cache

    if not GOOGLE_API_KEY or not texts:
        return texts

    # Separate cached and uncached texts
    results = [None] * len(texts)
    uncached_indices = []
    uncached_texts = []

    for i, text in enumerate(texts):
        cache_key = get_cache_key(text, target_lang)
        if cache_key in _translation_cache:
            results[i] = _translation_cache[cache_key]
        else:
            uncached_indices.append(i)
            uncached_texts.append(text)

    if not uncached_texts:
        return results

    try:
        import urllib.request
        import urllib.parse

        # Google Translate API v2 endpoint - batch translate
        url = 'https://translation.googleapis.com/language/translate/v2'

        # Build query with multiple 'q' parameters
        params = [
            ('key', GOOGLE_API_KEY),
            ('source', source_lang),
            ('target', target_lang),
            ('format', 'text')
        ]
        for text in uncached_texts:
            params.append(('q', text))

        data = urllib.parse.urlencode(params).encode('utf-8')
        req = urllib.request.Request(url, data=data, method='POST')

        with urllib.request.urlopen(req, timeout=60) as response:
            result = json.loads(response.read().decode('utf-8'))

        if 'data' in result and 'translations' in result['data']:
            translations = result['data']['translations']
            for i, trans in enumerate(translations):
                original_idx = uncached_indices[i]
                translated = trans['translatedText']
                results[original_idx] = translated
                # Cache the result
                cache_key = get_cache_key(uncached_texts[i], target_lang)
                _translation_cache[cache_key] = translated

        # Small delay to avoid rate limiting
        time.sleep(0.1)

    except Exception as e:
        print(f"    Batch translation error: {e}")
        # Return originals for failed translations
        for i in uncached_indices:
            if results[i] is None:
                results[i] = texts[i]

    # Fill any remaining None values with originals
    for i in range(len(results)):
        if results[i] is None:
            results[i] = texts[i]

    return results

def normalize_ini_value(value):
    """
    Normalizes an INI value by stripping surrounding quotes.
    Joomla INI values can be: KEY="value" or KEY=value
    """
    value = value.strip()
    if len(value) >= 2 and value.startswith('"') and value.endswith('"'):
        return value[1:-1]
    return value

def get_language_dirs(base_path):
    """
    Returns a list of language directories in the given base path.
    """
    if not os.path.exists(base_path):
        return []
    return [d for d in os.listdir(base_path) if os.path.isdir(os.path.join(base_path, d))]

def parse_ini_file(file_path):
    """
    Parses an INI file and returns:
    - lines: list of all lines (for preserving structure)
    - key_map: dict of key -> normalized value (without quotes)
    - key_line_map: dict of key -> line index
    """
    if not os.path.exists(file_path):
        return [], {}, {}

    with open(file_path, 'r', encoding='utf-8') as f:
        lines = f.readlines()

    key_map = {}
    key_line_map = {}

    for i, line in enumerate(lines):
        stripped = line.strip()
        if '=' in line and not stripped.startswith(';'):
            parts = line.split('=', 1)
            key = parts[0].strip()
            value = normalize_ini_value(parts[1])
            key_map[key] = value
            key_line_map[key] = i

    return lines, key_map, key_line_map

def remove_duplicates(file_path):
    """
    Removes duplicate keys from a language file, keeping the first occurrence.
    """
    if not os.path.exists(file_path):
        return

    with open(file_path, 'r', encoding='utf-8') as f:
        lines = f.readlines()

    seen_keys = set()
    new_lines = []
    removed_count = 0

    for line in lines:
        stripped_line = line.strip()
        if not stripped_line or stripped_line.startswith(';'):
            new_lines.append(line)
            continue

        if '=' in line:
            key = line.split('=', 1)[0].strip()
            if key in seen_keys:
                removed_count += 1
                continue
            seen_keys.add(key)
            new_lines.append(line)
        else:
            new_lines.append(line)

    if removed_count > 0:
        with open(file_path, 'w', encoding='utf-8') as f:
            f.writelines(new_lines)
        print(f"  Removed {removed_count} duplicate keys from {os.path.basename(file_path)}")

def update_translations(file_path, value_replacements=None, key_updates=None):
    """
    Updates translations in the file.
    value_replacements: dict of {old_str: new_str} to replace in values.
    key_updates: dict of {key: new_value} to set specific keys.
    """
    if not os.path.exists(file_path):
        return

    with open(file_path, 'r', encoding='utf-8') as f:
        lines = f.readlines()

    new_lines = []
    modified = False

    value_replacements = value_replacements or {}
    key_updates = key_updates or {}

    for line in lines:
        stripped = line.strip()
        if '=' in line and not stripped.startswith(';'):
            parts = line.split('=', 1)
            key = parts[0].strip()
            value_part = parts[1]  # Includes newline if present

            # Check for key updates (forced value override)
            if key in key_updates:
                new_val_str = key_updates[key]
                # Format as key="value"
                new_line = f'{key}="{new_val_str}"\n'
                if new_line != line:
                    new_lines.append(new_line)
                    modified = True
                else:
                    new_lines.append(line)
                continue

            # Check for value replacements (string substitution)
            new_value_part = value_part
            for old, new in value_replacements.items():
                if old in new_value_part:
                    new_value_part = new_value_part.replace(old, new)

            if new_value_part != value_part:
                new_lines.append(f"{parts[0]}={new_value_part}")
                modified = True
            else:
                new_lines.append(line)

        else:
            new_lines.append(line)

    if modified:
        with open(file_path, 'w', encoding='utf-8') as f:
            f.writelines(new_lines)
        print(f"  Updated translations in {os.path.basename(file_path)}")

def sync_keys(source_file, target_file, target_lang=None):
    """
    Synchronizes keys between source and target files:
    - Adds missing keys from source to target
    - If AUTO_TRANSLATE is enabled, translates missing keys
    - Removes keys from target that don't exist in source (obsolete keys)
    - Preserves existing translations (doesn't overwrite different values)
    """
    if not os.path.exists(source_file):
        return

    source_lines, source_map, source_key_lines = parse_ini_file(source_file)

    # Create target file if it doesn't exist
    if not os.path.exists(target_file):
        with open(source_file, 'r', encoding='utf-8') as f:
            content = f.read()
        with open(target_file, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"  Created {os.path.basename(target_file)} from source")
        return

    target_lines, target_map, target_key_lines = parse_ini_file(target_file)

    # Find missing and obsolete keys
    source_keys = set(source_map.keys())
    target_keys = set(target_map.keys())

    missing_keys = source_keys - target_keys  # In source but not in target
    obsolete_keys = target_keys - source_keys  # In target but not in source

    # Also find untranslated keys (same value as source)
    untranslated_keys = set()
    for key in target_keys:
        if key in source_map and target_map[key] == source_map[key]:
            untranslated_keys.add(key)

    if not missing_keys and not obsolete_keys and not untranslated_keys:
        return

    # Translate missing and untranslated keys if enabled
    translations = {}
    keys_to_translate = list(missing_keys | untranslated_keys)

    if AUTO_TRANSLATE and GOOGLE_API_KEY and keys_to_translate and target_lang:
        google_lang = LANG_CODE_MAP.get(target_lang)
        if google_lang and google_lang != 'en':
            # Get source texts for translation
            texts_to_translate = [source_map[k] for k in keys_to_translate]
            print(f"    Translating {len(texts_to_translate)} strings to {target_lang}...")

            # Batch translate (max 128 at a time for API limits)
            batch_size = 100
            translated_texts = []
            for i in range(0, len(texts_to_translate), batch_size):
                batch = texts_to_translate[i:i + batch_size]
                translated_batch = translate_batch(batch, google_lang, 'en')
                translated_texts.extend(translated_batch)

            # Map translations
            for key, translated in zip(keys_to_translate, translated_texts):
                if translated and translated != source_map[key]:
                    translations[key] = translated

    # Build new target content
    new_lines = []

    # Process existing lines, removing obsolete keys and updating untranslated
    removed_count = 0
    updated_count = 0
    for line in target_lines:
        stripped = line.strip()
        if '=' in line and not stripped.startswith(';'):
            key = line.split('=', 1)[0].strip()
            if key in obsolete_keys:
                removed_count += 1
                continue  # Skip obsolete key
            # Update untranslated keys with translations
            if key in untranslated_keys and key in translations:
                new_lines.append(f'{key}="{translations[key]}"\n')
                updated_count += 1
                continue
        new_lines.append(line)

    # Append missing keys (with translations if available)
    added_count = 0
    if missing_keys:
        # Ensure file ends with newline
        if new_lines and not new_lines[-1].endswith('\n'):
            new_lines.append('\n')

        # Add missing keys in the order they appear in source
        for key in source_map.keys():
            if key in missing_keys:
                if key in translations:
                    new_lines.append(f'{key}="{translations[key]}"\n')
                else:
                    # Use source value with quotes
                    new_lines.append(f'{key}="{source_map[key]}"\n')
                added_count += 1

    with open(target_file, 'w', encoding='utf-8') as f:
        f.writelines(new_lines)

    # Report what was done
    actions = []
    if added_count:
        translated_add = len([k for k in missing_keys if k in translations])
        if translated_add:
            actions.append(f"added {added_count} keys ({translated_add} translated)")
        else:
            actions.append(f"added {added_count} keys")
    if updated_count:
        actions.append(f"translated {updated_count} existing")
    if removed_count:
        actions.append(f"removed {removed_count} obsolete")
    if actions:
        print(f"  {os.path.basename(target_file)}: {', '.join(actions)}")

def prune_redundant_keys(source_file, target_file):
    """
    Removes keys from target_file that:
    1. Have the exact same value as in source_file (untranslated - Joomla falls back to en-GB)
    2. Don't exist in source_file (obsolete keys)
    Compares normalized values (without surrounding quotes) to handle format differences.
    """
    if not os.path.exists(source_file) or not os.path.exists(target_file):
        return

    _, source_map, _ = parse_ini_file(source_file)

    with open(target_file, 'r', encoding='utf-8') as f:
        target_lines = f.readlines()

    new_target_lines = []
    removed_untranslated = 0
    removed_obsolete = 0

    for line in target_lines:
        stripped = line.strip()
        if '=' in line and not stripped.startswith(';'):
            parts = line.split('=', 1)
            key = parts[0].strip()
            # Normalize the value for comparison (strip quotes)
            value = normalize_ini_value(parts[1])

            # Remove obsolete keys (not in source)
            if key not in source_map:
                removed_obsolete += 1
                continue

            # Remove untranslated keys (same value as source)
            if source_map[key] == value:
                removed_untranslated += 1
                continue  # Skip this line - Joomla will fall back to en-GB

        new_target_lines.append(line)

    total_removed = removed_untranslated + removed_obsolete
    if total_removed > 0:
        with open(target_file, 'w', encoding='utf-8') as f:
            f.writelines(new_target_lines)
        msg_parts = []
        if removed_untranslated:
            msg_parts.append(f"{removed_untranslated} untranslated")
        if removed_obsolete:
            msg_parts.append(f"{removed_obsolete} obsolete")
        print(f"  Removed {' + '.join(msg_parts)} keys from {os.path.basename(target_file)}")

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

        if not os.path.exists(source_file_path):
            continue

        print(f"\n  Processing {file_pattern}...")

        # 1. Remove duplicates from source
        remove_duplicates(source_file_path)

        # 2. Update source translations (apply SOURCE_REPLACEMENTS)
        update_translations(source_file_path, value_replacements=SOURCE_REPLACEMENTS)

        for target_lang in target_langs:
            target_file_name = f"{target_lang}.{file_pattern}"
            target_file_path = os.path.join(base_path, target_lang, target_file_name)

            # 3. Remove duplicates from target
            remove_duplicates(target_file_path)

            # 4. Apply language-specific translations
            replacements = GLOBAL_REPLACEMENTS.copy()
            if target_lang in LANG_SPECIFIC_REPLACEMENTS:
                replacements.update(LANG_SPECIFIC_REPLACEMENTS[target_lang])

            key_updates = {}
            if target_lang in LANG_SPECIFIC_KEY_UPDATES:
                key_updates = LANG_SPECIFIC_KEY_UPDATES[target_lang]

            update_translations(target_file_path, value_replacements=replacements, key_updates=key_updates)

            # 5. Sync or prune based on configuration
            if SYNC_ALL_KEYS:
                # Keep all keys in sync (add missing, remove obsolete, translate if enabled)
                sync_keys(source_file_path, target_file_path, target_lang)
            else:
                # Prune untranslated keys (rely on Joomla fallback)
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
                    print(f"\nProcessing languages in {lang_path}")
                    process_directory(lang_path, patterns)

if __name__ == "__main__":
    script_dir = os.path.dirname(os.path.abspath(__file__))
    project_root = os.path.dirname(script_dir)

    print(f"Scanning project from {project_root}...")
    print(f"Mode: {'Sync all keys' if SYNC_ALL_KEYS else 'Prune untranslated keys'}")

    if AUTO_TRANSLATE:
        if GOOGLE_API_KEY:
            # Determine source of API key for logging (without exposing the key)
            if os.environ.get('GOOGLE_TRANSLATE_API_KEY'):
                key_source = "environment variable"
            elif os.environ.get('OP_GOOGLE_TRANSLATE_REF'):
                key_source = "1Password"
            else:
                key_source = "unknown"
            print(f"Auto-translation: ENABLED (Google Translate via {key_source})")
            load_translation_cache()
            print(f"Translation cache: {len(_translation_cache)} entries loaded")
        else:
            print("Auto-translation: DISABLED (no API key)")
            print("  Options to provide API key:")
            print("    1. Set GOOGLE_TRANSLATE_API_KEY environment variable")
            print("    2. Set OP_GOOGLE_TRANSLATE_REF to 1Password reference (e.g., op://Vault/Item/field)")
    else:
        print("Auto-translation: DISABLED")

    scan_and_process(project_root)

    # Save translation cache
    if AUTO_TRANSLATE and GOOGLE_API_KEY:
        save_translation_cache()
        print(f"Translation cache: {len(_translation_cache)} entries saved")

    print("\nDone!")