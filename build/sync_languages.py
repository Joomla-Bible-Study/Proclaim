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

# When True: Force re-translation of ALL keys (ignores existing translations)
# Useful when source values have changed and you want to refresh all translations
# Set via --force command line argument
FORCE_RETRANSLATE = False

# 1Password configuration - standard item name for the API key
OP_ITEM_NAME = os.environ.get('OP_ITEM_NAME', 'Google Translate API - CWM')
OP_VAULT = os.environ.get('OP_VAULT', 'CWM')  # Default vault, can override with env var

# Google Translate API key - loaded in order of priority:
# 1. GOOGLE_TRANSLATE_API_KEY environment variable
# 2. OP_GOOGLE_TRANSLATE_REF environment variable (custom 1Password reference)
# 3. Standard 1Password item: "Google Translate API - Proclaim"
# 4. Empty (translation disabled)

def _get_op_account():
    """Get the first 1Password account URL for --account flag (triggers biometric)."""
    import subprocess
    try:
        result = subprocess.run(
            ['op', 'account', 'list', '--format', 'json'],
            capture_output=True, text=True, timeout=5
        )
        if result.returncode == 0:
            accounts = json.loads(result.stdout)
            if accounts:
                return accounts[0].get('url', '')
    except Exception:
        pass
    return ''

def check_op_cli():
    """Check if 1Password CLI is installed (auth is handled by get_api_key_from_op via --account)."""
    try:
        import subprocess
        result = subprocess.run(['op', '--version'], capture_output=True, text=True, timeout=5)
        return result.returncode == 0
    except Exception:
        return False

def get_api_key_from_op(item_ref=None):
    """
    Retrieve API key from 1Password.
    Uses --account flag to trigger biometric unlock in non-interactive shells.
    Searches all vaults if OP_VAULT is not explicitly set.
    """
    import subprocess

    account = _get_op_account()
    account_args = ['--account', account] if account else []

    if item_ref:
        # Use provided reference directly
        cmd = ['op', 'read', item_ref] + account_args
    else:
        # Try configured vault first, then fall back to all-vault search
        cmd = ['op', 'item', 'get', OP_ITEM_NAME, '--vault', OP_VAULT,
               '--fields', 'credential', '--format', 'json', '--reveal'] + account_args

    try:
        result = subprocess.run(cmd, capture_output=True, text=True, timeout=15)

        # If vault-specific lookup failed, retry across all vaults
        if result.returncode != 0 and not item_ref:
            cmd = ['op', 'item', 'get', OP_ITEM_NAME,
                   '--fields', 'credential', '--format', 'json', '--reveal'] + account_args
            result = subprocess.run(cmd, capture_output=True, text=True, timeout=15)

        # Backward compat: try legacy "Google Translate API - Proclaim" name
        if result.returncode != 0 and not item_ref and OP_ITEM_NAME != 'Google Translate API - Proclaim':
            cmd = ['op', 'item', 'get', 'Google Translate API - Proclaim', '--vault', OP_VAULT,
                   '--fields', 'credential', '--format', 'json', '--reveal'] + account_args
            result = subprocess.run(cmd, capture_output=True, text=True, timeout=15)

        if result.returncode == 0:
            if item_ref:
                return result.stdout.strip()
            else:
                # Parse JSON response
                data = json.loads(result.stdout)
                return data.get('value', '') if isinstance(data, dict) else result.stdout.strip()
        return None
    except Exception:
        return None

def create_op_item(api_key, vault=None):
    """
    Create a 1Password item to store the API key.
    """
    import subprocess

    target_vault = vault or OP_VAULT
    account = _get_op_account()
    print(f"Creating 1Password item '{OP_ITEM_NAME}' in vault '{target_vault}'...")

    cmd = [
        'op', 'item', 'create',
        '--category', 'API Credential',
        '--title', OP_ITEM_NAME,
        '--vault', target_vault,
        f'credential={api_key}',
    ]
    if account:
        cmd += ['--account', account]

    try:
        result = subprocess.run(cmd, capture_output=True, text=True, timeout=15)
        if result.returncode == 0:
            print(f"  ✓ Created 1Password item '{OP_ITEM_NAME}'")
            return True
        else:
            print(f"  ✗ Failed to create item: {result.stderr.strip()}")
            return False
    except Exception as e:
        print(f"  ✗ Error creating item: {e}")
        return False

def setup_api_key():
    """
    Interactive setup to store API key in 1Password.
    """
    print("\n=== Google Translate API Key Setup ===\n")

    if not check_op_cli():
        print("1Password CLI (op) is not installed or not authenticated.")
        print("Install from: https://1password.com/downloads/command-line/")
        print("Then run: op signin")
        return False

    # Check if item already exists
    existing_key = get_api_key_from_op()
    if existing_key:
        print(f"API key already exists in 1Password item '{OP_ITEM_NAME}'")
        response = input("Overwrite? [y/N]: ").strip().lower()
        if response != 'y':
            print("Setup cancelled.")
            return False
        # Delete existing item
        import subprocess
        del_cmd = ['op', 'item', 'delete', OP_ITEM_NAME, '--vault', OP_VAULT]
        account = _get_op_account()
        if account:
            del_cmd += ['--account', account]
        subprocess.run(del_cmd, capture_output=True)

    # Prompt for API key
    print("\nEnter your Google Translate API key")
    print("(Get one from: https://console.cloud.google.com/apis/credentials)")
    api_key = input("API Key: ").strip()

    if not api_key:
        print("No key provided. Setup cancelled.")
        return False

    # Test the key
    print("\nTesting API key...")
    try:
        import urllib.request
        import urllib.parse
        url = 'https://translation.googleapis.com/language/translate/v2'
        params = {'key': api_key, 'q': 'test', 'source': 'en', 'target': 'es'}
        data = urllib.parse.urlencode(params).encode('utf-8')
        req = urllib.request.Request(url, data=data, method='POST')
        with urllib.request.urlopen(req, timeout=10) as response:
            print("  ✓ API key is valid")
    except Exception as e:
        print(f"  ✗ API key test failed: {e}")
        response = input("Save anyway? [y/N]: ").strip().lower()
        if response != 'y':
            return False

    # Create the item
    if create_op_item(api_key):
        print(f"\nSetup complete! The script will now automatically use the key from 1Password.")
        return True
    return False

def get_api_key():
    """
    Securely retrieve API key from environment or 1Password.
    """
    # First check environment variable
    key = os.environ.get('GOOGLE_TRANSLATE_API_KEY', '')
    if key:
        return key, 'environment variable'

    # Check for custom 1Password secret reference
    op_ref = os.environ.get('OP_GOOGLE_TRANSLATE_REF', '')
    if op_ref:
        key = get_api_key_from_op(op_ref)
        if key:
            return key, '1Password (custom ref)'

    # Try standard 1Password item
    if check_op_cli():
        key = get_api_key_from_op()
        if key:
            return key, f'1Password ({OP_ITEM_NAME})'

    return '', None

# Defer loading until we know if we're in setup mode
GOOGLE_API_KEY = ''
API_KEY_SOURCE = None

# Translation cache file (to avoid re-translating the same strings)
TRANSLATION_CACHE_FILE = os.path.join(os.path.dirname(os.path.abspath(__file__)), '.translation_cache.json')

# Source values cache file (to detect when source values change)
SOURCE_CACHE_FILE = os.path.join(os.path.dirname(os.path.abspath(__file__)), '.source_cache.json')

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
    #'Series': 'Theme'
}

# Global replacements applied to all non-source languages
GLOBAL_REPLACEMENTS = {
    'Series': 'Theme' # Removed to avoid forcing English 'Theme' into other languages
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

# === SOURCE VALUES CACHE ===
# Tracks previous source (en-GB) values to detect changes
_source_cache = {}

def load_source_cache():
    """Load the source values cache from disk."""
    global _source_cache
    if os.path.exists(SOURCE_CACHE_FILE):
        try:
            with open(SOURCE_CACHE_FILE, 'r', encoding='utf-8') as f:
                _source_cache = json.load(f)
        except (json.JSONDecodeError, IOError):
            _source_cache = {}
    return _source_cache

def save_source_cache():
    """Save the source values cache to disk."""
    try:
        with open(SOURCE_CACHE_FILE, 'w', encoding='utf-8') as f:
            json.dump(_source_cache, f, ensure_ascii=False, indent=2)
    except IOError as e:
        print(f"  Warning: Could not save source cache: {e}")

def get_changed_source_keys(file_key, current_source_map):
    """
    Compare current source values against cached values.
    Returns a set of keys whose source values have changed.
    """
    global _source_cache
    changed_keys = set()

    cached_values = _source_cache.get(file_key, {})

    for key, current_value in current_source_map.items():
        if key in cached_values and cached_values[key] != current_value:
            changed_keys.add(key)

    return changed_keys

def update_source_cache(file_key, source_map):
    """Update the source cache with current source values."""
    global _source_cache
    _source_cache[file_key] = dict(source_map)

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

    except urllib.error.HTTPError as e:
        body = ''
        try:
            body = e.read().decode('utf-8', errors='replace')
        except Exception:
            pass
        print(f"    Batch translation error: {e} (chars={len(data)}, texts={len(uncached_texts)})")
        if body:
            try:
                err_json = json.loads(body)
                reason = err_json.get('error', {}).get('message', body[:200])
                print(f"      Reason: {reason}")
            except json.JSONDecodeError:
                print(f"      Response: {body[:200]}")
        if e.code == 403:
            # Rate limit or quota — back off before next request
            print("      Backing off 5 seconds...")
            time.sleep(5)
        # Return originals for failed translations
        for i in uncached_indices:
            if results[i] is None:
                results[i] = texts[i]
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

def sync_keys(source_file, target_file, target_lang=None, file_key=None):
    """
    Synchronizes keys between source and target files:
    - Adds missing keys from source to target
    - If AUTO_TRANSLATE is enabled, translates missing keys
    - Removes keys from target that don't exist in source (obsolete keys)
    - Re-translates keys when the source (en-GB) value has changed
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

    # Find untranslated keys (same value as source)
    untranslated_keys = set()
    for key in target_keys:
        if key in source_map and target_map[key] == source_map[key]:
            untranslated_keys.add(key)

    # Find keys where source value has changed (needs re-translation)
    changed_keys = set()
    if file_key:
        changed_keys = get_changed_source_keys(file_key, source_map)
        # Only consider changed keys that exist in target and aren't already untranslated
        changed_keys = changed_keys & target_keys - untranslated_keys

    # Force re-translation of all existing keys if requested
    force_retranslate_keys = set()
    if FORCE_RETRANSLATE:
        # All keys that exist in both source and target (excluding untranslated which are already included)
        force_retranslate_keys = (source_keys & target_keys) - untranslated_keys

    if not missing_keys and not obsolete_keys and not untranslated_keys and not changed_keys and not force_retranslate_keys:
        return

    # Translate missing, untranslated, changed, and force-retranslate keys if enabled
    translations = {}
    keys_to_translate = list(missing_keys | untranslated_keys | changed_keys | force_retranslate_keys)

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

    # Process existing lines, removing obsolete keys and updating untranslated/changed
    removed_count = 0
    updated_count = 0
    retranslated_count = 0
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
            # Re-translate keys where source value changed
            if key in changed_keys and key in translations:
                new_lines.append(f'{key}="{translations[key]}"\n')
                retranslated_count += 1
                continue
            # Force re-translate all keys
            if key in force_retranslate_keys and key in translations:
                new_lines.append(f'{key}="{translations[key]}"\n')
                retranslated_count += 1
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
    if retranslated_count:
        actions.append(f"re-translated {retranslated_count} changed")
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

def process_directory(base_path, file_patterns, known_locales=None):
    """
    Process all languages in the base path.
    If known_locales is provided, creates missing locale directories so that
    new addon language folders (with only en-GB) get all project locales.
    """
    lang_dirs = get_language_dirs(base_path)
    if 'en-GB' not in lang_dirs:
        return

    source_lang = 'en-GB'

    # Ensure all known project locales have directories in this language folder
    if known_locales:
        for locale in known_locales:
            locale_dir = os.path.join(base_path, locale)
            if not os.path.exists(locale_dir):
                os.makedirs(locale_dir, exist_ok=True)
        # Re-scan after creating directories
        lang_dirs = get_language_dirs(base_path)

    target_langs = [lang for lang in lang_dirs if lang != source_lang]

    for file_pattern in file_patterns:
        # Detect naming convention: prefixed (en-GB.foo.ini) or bare (foo.ini)
        prefixed_path = os.path.join(base_path, source_lang, f"{source_lang}.{file_pattern}")
        bare_path = os.path.join(base_path, source_lang, file_pattern)

        if os.path.exists(prefixed_path):
            source_file_path = prefixed_path
            use_prefix = True
        elif os.path.exists(bare_path):
            source_file_path = bare_path
            use_prefix = False
        else:
            continue

        print(f"\n  Processing {file_pattern}...")

        # Generate a unique key for this source file (for change tracking)
        file_key = f"{base_path}:{file_pattern}"

        # 1. Remove duplicates from source
        remove_duplicates(source_file_path)

        # 2. Update source translations (apply SOURCE_REPLACEMENTS)
        update_translations(source_file_path, value_replacements=SOURCE_REPLACEMENTS)

        # Parse source file to get current values (for change detection)
        _, source_map, _ = parse_ini_file(source_file_path)

        # Check for changed source keys
        changed_keys = get_changed_source_keys(file_key, source_map)
        if changed_keys:
            print(f"    Source values changed for {len(changed_keys)} keys: {', '.join(sorted(changed_keys)[:5])}{'...' if len(changed_keys) > 5 else ''}")

        for target_lang in target_langs:
            target_file_name = f"{target_lang}.{file_pattern}" if use_prefix else file_pattern
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
                sync_keys(source_file_path, target_file_path, target_lang, file_key)
            else:
                # Prune untranslated keys (rely on Joomla fallback)
                prune_redundant_keys(source_file_path, target_file_path)

        # After processing all target languages, update the source cache
        update_source_cache(file_key, source_map)

def discover_project_locales(root_dir):
    """
    Discover all project locales from the first language/ directory found.

    Checks (in order): admin/language/, site/language/, language/
    Falls back to LANG_CODE_MAP keys if none found.

    Returns a sorted list of locale codes (e.g., ['cs-CZ', 'de-DE', 'en-GB', ...]).
    """
    candidates = [
        os.path.join(root_dir, 'admin', 'language'),
        os.path.join(root_dir, 'site', 'language'),
        os.path.join(root_dir, 'language'),
    ]

    for lang_dir in candidates:
        if os.path.exists(lang_dir):
            locales = get_language_dirs(lang_dir)
            # If only en-GB exists, use full locale list to create all targets
            if locales and locales != ['en-GB']:
                return sorted(locales)

    # Fallback: use all locales from LANG_CODE_MAP
    return sorted(LANG_CODE_MAP.keys())


def scan_and_process(root_dir):
    """
    Recursively scans for 'language' directories and processes them.
    Discovers project locales from admin/language/ and ensures addon
    language directories include all locales (creating missing dirs).
    Ignores 'vendor' and 'node_modules' directories.
    """
    known_locales = discover_project_locales(root_dir)
    print(f"Project locales: {', '.join(known_locales)}")

    # Always skip these directories
    skip_dirs = {'vendor', 'node_modules', '.git'}

    # Also skip git submodule directories (they have their own language sync)
    gitmodules = os.path.join(root_dir, '.gitmodules')
    if os.path.exists(gitmodules):
        with open(gitmodules) as f:
            for line in f:
                m = re.match(r'\s*path\s*=\s*(.+)', line)
                if m:
                    # Add the leaf directory name (e.g., 'lib_cwmscripture' from 'libraries/lib_cwmscripture')
                    skip_dirs.add(os.path.basename(m.group(1).strip()))

    for dirpath, dirnames, filenames in os.walk(root_dir):
        # Modify dirnames in-place to skip ignored directories
        dirnames[:] = [d for d in dirnames if d not in skip_dirs]

        if 'language' in dirnames:
            lang_path = os.path.join(dirpath, 'language')

            en_gb_path = os.path.join(lang_path, 'en-GB')
            if os.path.exists(en_gb_path):
                patterns = []
                for f in os.listdir(en_gb_path):
                    # Old convention: en-GB.com_proclaim.ini (prefixed)
                    if f.startswith('en-GB.') and (f.endswith('.ini') or f.endswith('.sys.ini')):
                        patterns.append(f[6:])
                    # New convention: com_livingword.ini (no prefix, Joomla 4+)
                    elif not f.startswith('en-GB.') and (f.endswith('.ini') or f.endswith('.sys.ini')):
                        patterns.append(f)

                if patterns:
                    print(f"\nProcessing languages in {lang_path}")
                    process_directory(lang_path, patterns, known_locales)

if __name__ == "__main__":
    import sys

    script_dir = os.path.dirname(os.path.abspath(__file__))
    project_root = os.path.dirname(script_dir)

    # Check for setup command
    if len(sys.argv) > 1 and sys.argv[1] == 'setup':
        setup_api_key()
        sys.exit(0)

    # Check for help
    if len(sys.argv) > 1 and sys.argv[1] in ('--help', '-h', 'help'):
        print("""
sync_languages.py - Synchronize and translate Joomla language files

Usage:
    python3 sync_languages.py              Run translation sync
    python3 sync_languages.py --force      Force re-translate ALL keys
    python3 sync_languages.py setup        Store API key in 1Password
    python3 sync_languages.py help         Show this help

Options:
    --force     Force re-translation of all keys, ignoring existing translations.
                Useful when source (en-GB) values have changed significantly.

API Key Options (in priority order):
    1. GOOGLE_TRANSLATE_API_KEY env var
    2. OP_GOOGLE_TRANSLATE_REF env var (custom 1Password reference)
    3. 1Password item "Google Translate API - Proclaim" (run 'setup' to create)

Environment Variables:
    GOOGLE_TRANSLATE_API_KEY    API key directly
    OP_GOOGLE_TRANSLATE_REF     Custom 1Password reference (e.g., op://Vault/Item/field)
    OP_VAULT                    1Password vault name (default: Private)
""")
        sys.exit(0)

    # Check for --force flag
    if '--force' in sys.argv:
        FORCE_RETRANSLATE = True
        sys.argv.remove('--force')

    # Load API key
    GOOGLE_API_KEY, API_KEY_SOURCE = get_api_key()

    print(f"Scanning project from {project_root}...")
    print(f"Mode: {'Sync all keys' if SYNC_ALL_KEYS else 'Prune untranslated keys'}")
    if FORCE_RETRANSLATE:
        print("Force re-translate: ENABLED (all keys will be re-translated)")

    # Load source cache (for detecting changed source values)
    load_source_cache()
    print(f"Source cache: {len(_source_cache)} files tracked")

    if AUTO_TRANSLATE:
        if GOOGLE_API_KEY:
            print(f"Auto-translation: ENABLED (Google Translate via {API_KEY_SOURCE})")
            load_translation_cache()
            print(f"Translation cache: {len(_translation_cache)} entries loaded")
        else:
            print("Auto-translation: DISABLED (no API key)")
            print("  Options to provide API key:")
            print("    1. Run 'python3 sync_languages.py setup' to store in 1Password")
            print("    2. Set GOOGLE_TRANSLATE_API_KEY environment variable")
            print("    3. Set OP_GOOGLE_TRANSLATE_REF to 1Password reference")
    else:
        print("Auto-translation: DISABLED")

    scan_and_process(project_root)

    # Save caches
    save_source_cache()
    print(f"Source cache: {len(_source_cache)} files saved")

    if AUTO_TRANSLATE and GOOGLE_API_KEY:
        save_translation_cache()
        print(f"Translation cache: {len(_translation_cache)} entries saved")

    print("\nDone!")