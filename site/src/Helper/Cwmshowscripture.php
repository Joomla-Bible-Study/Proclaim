<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\Helper;

use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use CWM\Component\Proclaim\Administrator\Helper\CwmscriptureHelper;
use CWM\Component\Proclaim\Administrator\Helper\ScriptureReference;
use CWM\Component\Proclaim\Site\Bible\AbstractBibleProvider;
use CWM\Component\Proclaim\Site\Bible\BiblePassageResult;
use CWM\Component\Proclaim\Site\Bible\BibleProviderFactory;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Scripture Show class.
 *
 * Renders scripture passages using the configured Bible provider.
 * Bible version is read from the message record; provider resolution uses admin params.
 *
 * @package  Proclaim.Site
 * @since    7.1.0
 */
class Cwmshowscripture
{
    /**
     * Passage Build system
     *
     * @param   object    $row     Item Info (message row with bible_version property)
     * @param   Registry  $params  Template Params (display settings)
     *
     * @return string|bool
     *
     * @since    7.1
     */
    public function buildPassage($row, Registry $params): string|bool
    {
        AbstractBibleProvider::registerLogger();

        if (empty($row->bookname)) {
            return false;
        }

        $reference = $this->formReference($row);
        $choice    = (int) $params->get('show_passage_view', 3);

        // Read Bible version from message row (new), fallback to template params (legacy compat)
        $version = $row->bible_version ?? $params->get('bible_translation', '');

        if (empty($version)) {
            $version = 'kjv';
        }

        // Get admin params for provider configuration
        try {
            $admin       = Cwmparams::getAdmin();
            $adminParams = $admin->params ?? new Registry();
        } catch (\Exception $e) {
            Log::add('Failed to load admin params: ' . $e->getMessage(), Log::WARNING, 'com_proclaim.bible');
            $adminParams = new Registry();
        }

        // Resolve which provider can serve this version
        $result           = null;
        $provider         = null;
        $requestedVersion = $version;

        try {
            $provider = BibleProviderFactory::getProviderForTranslation($version, $adminParams);

            // Configure cache TTL from admin params
            $cacheDays = (int) $adminParams->get('scripture_cache_days', 30);

            if ($cacheDays > 0 && method_exists($provider, 'setCacheTtl')) {
                $provider->setCacheTtl($cacheDays * 86400);
            }

            $result = $provider->getPassage($reference, $version);
        } catch (\Exception $e) {
            Log::add('Provider error for "' . $reference . '" (' . $version . '): ' . $e->getMessage(), Log::ERROR, 'com_proclaim.bible');
        }

        // Fallback 1: try the same version via Local provider
        if (($result === null || !$result->hasText()) && ($provider === null || $provider->getName() !== 'local')) {
            try {
                $localProvider = BibleProviderFactory::getProvider('local');
                $localResult   = $localProvider->getPassage($reference, $version);

                if ($localResult->hasText()) {
                    Log::add('Fallback to local provider for "' . $reference . '" (' . $version . ')', Log::INFO, 'com_proclaim.bible');
                    $result = $localResult;
                }
            } catch (\Exception $e) {
                // Local fallback failed too — try default version next
            }
        }

        // Fallback 2: try the admin default bible version locally
        if ($result === null || !$result->hasText()) {
            $defaultVersion = (string) $adminParams->get('default_bible_version', 'kjv');

            if ($defaultVersion === '') {
                $defaultVersion = 'kjv';
            }

            if ($defaultVersion !== $version) {
                try {
                    $localProvider = BibleProviderFactory::getProvider('local');
                    $defaultResult = $localProvider->getPassage($reference, $defaultVersion);

                    if ($defaultResult->hasText()) {
                        Log::add(
                            'Fallback to default version "' . $defaultVersion . '" for "' . $reference . '" (requested: ' . $version . ')',
                            Log::INFO,
                            'com_proclaim.bible'
                        );
                        $result  = $defaultResult;
                        $version = $defaultVersion;
                    }
                } catch (\Exception $e) {
                    // Default version fallback failed too
                }
            }
        }

        // Fallback 3: hard fallback to KJV (bundled, always auto-downloaded)
        if ($result === null || !$result->hasText()) {
            $coreDefault = 'kjv';

            if ($coreDefault !== $version) {
                try {
                    $localProvider = BibleProviderFactory::getProvider('local');
                    $kjvResult     = $localProvider->getPassage($reference, $coreDefault);

                    if ($kjvResult->hasText()) {
                        Log::add(
                            'Hard fallback to KJV for "' . $reference . '" (requested: ' . $requestedVersion . ')',
                            Log::WARNING,
                            'com_proclaim.bible'
                        );
                        $result  = $kjvResult;
                        $version = $coreDefault;
                    }
                } catch (\Exception $e) {
                    // Even KJV failed — nothing we can do
                }
            }
        }

        if ($result === null || !$result->hasText()) {
            Log::add('No text returned for "' . $reference . '" (' . $requestedVersion . ') — all fallbacks exhausted', Log::WARNING, 'com_proclaim.bible');

            // Return "temporarily unavailable" notice with retry button
            if ($choice > 0) {
                return $this->renderUnavailableNotice($row, $reference, $requestedVersion);
            }

            return '';
        }

        // Build version switcher HTML (injected inside the passage container)
        $switcherHtml = '';

        if ((int) $params->get('allow_version_switch', 0) === 1) {
            $switcherHtml = $this->renderVersionSwitcher($row, $version, $adminParams);
        }

        $output = '';

        // Show fallback notice if serving a different version than requested
        if ($version !== $requestedVersion) {
            $output .= '<div class="scripture-fallback-notice text-muted small mb-1">'
                . '<em>' . Text::sprintf('JBS_CMN_SCRIPTURE_FALLBACK', strtoupper($version)) . '</em>'
                . '</div>';
        }

        $output .= $this->renderTextPassage($result, $choice, $params, $switcherHtml);

        return $output;
    }

    /**
     * Render a text-based scripture passage (from local or API provider).
     *
     * @param   BiblePassageResult  $result       The passage result
     * @param   int                 $choice       Display mode (0=hide, 1=toggle, 2=always, 3=popup)
     * @param   Registry            $params       Template parameters
     * @param   string              $switcherHtml Version switcher HTML to embed inside the passage
     *
     * @return  string  HTML output
     *
     * @since  10.1.0
     */
    public function renderTextPassage(BiblePassageResult $result, int $choice, Registry $params, string $switcherHtml = ''): string
    {
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->useStyle('com_proclaim.scripture-text');

        $copyrightHtml = '';

        if (!empty($result->copyright)) {
            $copyrightHtml = '<div class="scripture-copyright">'
                . htmlspecialchars($result->copyright) . '</div>';
        }

        $passage = '';

        switch ($choice) {
            case 1:
                // Toggle show/hide
                $id       = 'scripture_' . uniqid('', true);
                $passage  = '<div class="scripture-container">';
                $passage .= '<a class="scripture-toggle heading" href="#" role="button" '
                    . 'aria-expanded="false" aria-controls="' . $id . '" '
                    . 'onclick="var e = document.getElementById(\'' . $id . '\'); '
                    . 'var isHidden = e.style.display == \'none\'; '
                    . 'e.style.display = (isHidden ? \'block\' : \'none\'); '
                    . 'this.setAttribute(\'aria-expanded\', isHidden); return false;">';

                if ((int) $params->get('showpassage_icon', 1) === 1) {
                    $passage .= '<i class="fas fa-bible fa-3x" aria-hidden="true" '
                        . 'style="display: flex; margin-right: 10px;"></i>';
                }

                $passage .= Text::_('JBS_CMN_SHOW_HIDE_SCRIPTURE') . '</a>';
                $passage .= '<div id="' . $id . '" class="scripture-text" style="display: none;">';
                $passage .= '<div class="scripture-body">' . $result->text . '</div>';
                $passage .= $copyrightHtml;
                $passage .= $switcherHtml;
                $passage .= '</div></div>';
                break;

            case 2:
                // Always visible
                $passage  = '<div class="scripture-container scripture-visible">';
                $passage .= '<div class="scripture-text">';
                $passage .= '<div class="scripture-body">' . $result->text . '</div>';
                $passage .= $copyrightHtml;
                $passage .= $switcherHtml;
                $passage .= '</div></div>';
                break;

            case 3:
                // New window – opens scripture in a separate browser window/tab.
                // Opens a blank window, then writes the server-rendered HTML into it
                // via the DOM API. This is cross-browser safe: Chrome blocks data: URIs
                // in top-level navigations, and Safari lacks Blob URL support in some
                // contexts. The content comes from a server-rendered <template> element
                // (not user input) so there is no XSS vector.
                $popupId  = 'scripture_popup_' . uniqid('', true);
                $passage  = '<div class="scripture-container">';
                $passage .= '<a href="#" class="scripture-popup-trigger" '
                    . 'onclick="var t=document.getElementById(\'' . $popupId . '\');'
                    . 'var w=window.open(\'\',\'scripture_popup\','
                    . '\'width=700,height=500,scrollbars=yes,resizable=yes\');'
                    . 'if(w){w.document.open();w.document.write(t.innerHTML);w.document.close();}'
                    . 'return false;" '
                    . 'title="' . Text::_('JBS_STY_CLICK_TO_OPEN_PASSAGE') . '">';

                if ((int) $params->get('showpassage_icon', 1) === 1) {
                    $passage .= '<i class="fas fa-bible fa-3x" aria-hidden="true" '
                        . 'style="display: flex; margin-right: 10px;"></i>';
                } else {
                    $passage .= Text::_('JBS_STY_CLICK_TO_OPEN_PASSAGE');
                }

                $passage .= '</a>';

                // Hidden template — JS reads innerHTML to write into the popup window
                $lang     = Factory::getApplication()->getLanguage()->getTag();
                $passage .= '<template id="' . $popupId . '">';
                $passage .= '<!DOCTYPE html><html lang="' . $lang . '">';
                $passage .= '<head><meta charset="utf-8">';
                $passage .= '<title>' . Text::_('JBS_STY_CLICK_TO_OPEN_PASSAGE') . '</title>';
                $passage .= '<style>'
                    . 'body{font-family:Georgia,"Times New Roman",serif;line-height:1.8;'
                    . 'padding:2em;margin:0;max-width:700px;margin:0 auto;color:#333;background:#fafaf8;}'
                    . 'sup{font-size:0.65em;font-weight:700;color:#8b4513;margin-right:2px;}'
                    . '.scripture-copyright{margin-top:1em;padding-top:0.75em;'
                    . 'border-top:1px solid #e0ddd5;font-size:0.8em;color:#888;font-style:italic;}'
                    . '</style></head><body>';
                $passage .= $result->text;
                $passage .= $copyrightHtml;
                $passage .= '</body></html>';
                $passage .= '</template></div>';
                break;

            default:
                // Hidden (choice = 0), return nothing
                break;
        }

        return $passage;
    }

    /**
     * Render a Bible version switcher dropdown.
     *
     * @param   object    $row          Message row
     * @param   string    $version      Current Bible version abbreviation
     * @param   Registry  $adminParams  Admin params with provider settings
     *
     * @return  string  HTML select element
     *
     * @since  10.1.0
     */
    /**
     * ISO 639-1 language code to language name mapping.
     *
     * @var array<string, string>
     * @since 10.1.0
     */
    private static array $languageNames = [
        'af' => 'Afrikaans', 'ar' => 'Arabic', 'cs' => 'Czech', 'da' => 'Danish',
        'de' => 'German', 'el' => 'Greek', 'en' => 'English', 'es' => 'Spanish',
        'fi' => 'Finnish', 'fr' => 'French', 'he' => 'Hebrew', 'hi' => 'Hindi',
        'hu' => 'Hungarian', 'it' => 'Italian', 'ja' => 'Japanese', 'ko' => 'Korean',
        'la' => 'Latin', 'nl' => 'Dutch', 'no' => 'Norwegian', 'pl' => 'Polish',
        'pt' => 'Portuguese', 'ro' => 'Romanian', 'ru' => 'Russian', 'sv' => 'Swedish',
        'sw' => 'Swahili', 'th' => 'Thai', 'tl' => 'Tagalog', 'tr' => 'Turkish',
        'uk' => 'Ukrainian', 'vi' => 'Vietnamese', 'zh' => 'Chinese',
    ];

    /**
     * Render the Bible version switcher dropdown.
     *
     * Displays a searchable dropdown grouped by language. The site language
     * group appears first, followed by other languages alphabetically.
     * Always includes bundled/default translations.
     *
     * @param   object    $row          Message row
     * @param   string    $version      Current Bible version abbreviation
     * @param   Registry  $adminParams  Admin parameters
     *
     * @return  string  HTML for the version switcher
     *
     * @since   10.1.0
     */
    public function renderVersionSwitcher(object $row, string $version, Registry $adminParams): string
    {
        $app = Factory::getApplication();
        $wa  = $app->getDocument()->getWebAssetManager();
        $wa->useScript('com_proclaim.scripture-switcher');
        $wa->useStyle('com_proclaim.scripture-switcher-css');

        // Provide the AJAX endpoint URL to JS (SEF-safe, avoids redirect)
        $ajaxUrl = Route::_('index.php?option=com_proclaim&task=cwmscripture.getPassageXHR&format=raw', false);
        $app->getDocument()->addScriptOptions('com_proclaim.scripture', [
            'ajaxUrl' => $ajaxUrl,
        ]);

        // Build reference for AJAX calls
        $reference = $this->formReference($row);

        // Detect the site's active language (ISO 2-letter code)
        $siteLang = substr(Factory::getApplication()->getLanguage()->getTag(), 0, 2);

        // Collect translations from DB with language info (cached per-request)
        static $translationsCache = null;

        if ($translationsCache === null) {
            $translationsCache = [];

            try {
                $db    = Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);
                $query = $db->getQuery(true)
                    ->select($db->quoteName(['abbreviation', 'name', 'language']))
                    ->from($db->quoteName('#__bsms_bible_translations'))
                    ->order($db->quoteName('language') . ' ASC, ' . $db->quoteName('name') . ' ASC');
                $db->setQuery($query);
                $translationsCache = $db->loadObjectList() ?: [];
            } catch (\Exception $e) {
                // DB not available
            }
        }

        $translations = $translationsCache;

        // Fallback if nothing found
        if (empty($translations)) {
            $obj          = (object) ['abbreviation' => $version, 'name' => strtoupper($version), 'language' => $siteLang];
            $translations = [$obj];
        }

        // Group translations by language
        $siteGroup  = []; // Site language translations (shown first)
        $otherGroup = []; // Other languages, keyed by language name

        foreach ($translations as $trans) {
            $langCode = substr($trans->language ?? 'en', 0, 2);
            $langName = self::$languageNames[$langCode] ?? ucfirst($langCode);

            $item = [
                'abbr' => $trans->abbreviation,
                'name' => $trans->name,
                'lang' => $langCode,
            ];

            if ($langCode === $siteLang) {
                $siteGroup[] = $item;
            } else {
                $otherGroup[$langName][] = $item;
            }
        }

        // Sort other groups alphabetically by language name
        ksort($otherGroup);

        // Build the searchable dropdown HTML
        $messageId    = (int) ($row->id ?? 0);
        $siteLangName = self::$languageNames[$siteLang] ?? ucfirst($siteLang);

        // Find current version name for display
        $currentName = strtoupper($version);

        foreach ($translations as $trans) {
            if ($trans->abbreviation === $version) {
                $currentName = $trans->name;

                break;
            }
        }

        $html = '<div class="scripture-version-switcher scripture-searchable-switcher" '
            . 'data-reference="' . htmlspecialchars($reference) . '" '
            . 'data-message-id="' . $messageId . '">';

        // Hidden select for form data / fallback
        $html .= '<select class="scripture-version-select" '
            . 'data-reference="' . htmlspecialchars($reference) . '" '
            . 'data-message-id="' . $messageId . '" '
            . 'aria-label="' . Text::_('JBS_STY_BIBLE_VERSION') . '" '
            . 'style="display:none;">';

        // Site language group first
        if (!empty($siteGroup)) {
            $html .= '<optgroup label="' . htmlspecialchars($siteLangName) . '">';

            foreach ($siteGroup as $item) {
                $selected = ($item['abbr'] === $version) ? ' selected' : '';
                $html .= '<option value="' . htmlspecialchars($item['abbr']) . '"'
                    . ' data-lang="' . htmlspecialchars($item['lang']) . '"' . $selected . '>'
                    . htmlspecialchars($item['name']) . '</option>';
            }

            $html .= '</optgroup>';
        }

        // Other language groups
        foreach ($otherGroup as $langName => $items) {
            $html .= '<optgroup label="' . htmlspecialchars((string) $langName) . '">';

            foreach ($items as $item) {
                $selected = ($item['abbr'] === $version) ? ' selected' : '';
                $html .= '<option value="' . htmlspecialchars($item['abbr']) . '"'
                    . ' data-lang="' . htmlspecialchars($item['lang']) . '"' . $selected . '>'
                    . htmlspecialchars($item['name']) . '</option>';
            }

            $html .= '</optgroup>';
        }

        $html .= '</select>';

        // Custom searchable dropdown UI (enhanced by JS)
        $html .= '<div class="scripture-dropdown">';
        $html .= '<button type="button" class="scripture-dropdown-toggle" '
            . 'aria-haspopup="listbox" aria-expanded="false">'
            . '<span class="scripture-dropdown-text">' . htmlspecialchars($currentName) . '</span>'
            . '</button>';
        $html .= '<div class="scripture-dropdown-menu" role="listbox" style="display:none;">';

        // Search input (fixed at top via flex-shrink:0)
        $html .= '<div class="scripture-dropdown-search">'
            . '<input type="text" class="form-control form-control-sm" '
            . 'placeholder="' . htmlspecialchars(Text::_('JBS_STY_SEARCH_VERSIONS')) . '" '
            . 'aria-label="' . htmlspecialchars(Text::_('JBS_STY_SEARCH_VERSIONS')) . '">'
            . '</div>';

        // Scrollable items area
        $html .= '<div class="scripture-dropdown-items">';

        // Site language section
        if (!empty($siteGroup)) {
            $html .= '<div class="scripture-dropdown-group" data-lang="' . htmlspecialchars($siteLang) . '">';
            $html .= '<div class="scripture-dropdown-header">' . htmlspecialchars($siteLangName) . '</div>';

            foreach ($siteGroup as $item) {
                $active = ($item['abbr'] === $version) ? ' active' : '';
                $html .= '<div class="scripture-dropdown-item' . $active . '" '
                    . 'role="option" data-value="' . htmlspecialchars($item['abbr']) . '" '
                    . 'data-lang="' . htmlspecialchars($item['lang']) . '">'
                    . htmlspecialchars($item['name']) . '</div>';
            }

            $html .= '</div>';
        }

        // Other language groups (initially hidden, inside the scrollable area)
        if (!empty($otherGroup)) {
            foreach ($otherGroup as $langName => $items) {
                $langCode = $items[0]['lang'];
                $html .= '<div class="scripture-dropdown-group scripture-other-lang" '
                    . 'data-lang="' . htmlspecialchars($langCode) . '" style="display:none;">';
                $html .= '<div class="scripture-dropdown-header">'
                    . htmlspecialchars((string) $langName) . '</div>';

                foreach ($items as $item) {
                    $active = ($item['abbr'] === $version) ? ' active' : '';
                    $html .= '<div class="scripture-dropdown-item' . $active . '" '
                        . 'role="option" data-value="' . htmlspecialchars($item['abbr']) . '" '
                        . 'data-lang="' . htmlspecialchars($item['lang']) . '">'
                        . htmlspecialchars($item['name']) . '</div>';
                }

                $html .= '</div>';
            }
        }

        $html .= '</div>'; // end .scripture-dropdown-items

        // Footer with "Show All Languages" (fixed at bottom via flex-shrink:0)
        if (!empty($otherGroup)) {
            $html .= '<div class="scripture-dropdown-footer">';
            $html .= '<button type="button" class="scripture-dropdown-show-all" '
                . 'data-hide-text="' . htmlspecialchars(Text::_('JBS_STY_HIDE_OTHER_LANGUAGES')) . '">'
                . Text::_('JBS_STY_SHOW_ALL_LANGUAGES') . '</button>';
            $html .= '</div>';
        }

        $html .= '</div></div></div>'; // end menu, dropdown, switcher

        return $html;
    }

    /**
     * Create Form of Reference
     *
     * @param   object  $row  ?
     *
     * @return string
     *
     * @since    7.1
     */
    public function formReference($row): string
    {
        $book      = str_replace(' ', '+', Text::_($row->bookname));
        $reference = $book . '+' . $row->chapter_begin;

        if (!empty($row->verse_begin)) {
            $reference .= ':' . $row->verse_begin;
        }

        if (!empty($row->chapter_end) && !empty($row->verse_end)) {
            $reference .= '-' . $row->chapter_end . ':' . $row->verse_end;
        } elseif (!empty($row->verse_end)) {
            $reference .= '-' . $row->verse_end;
        }

        return $reference;
    }

    /**
     * Render a "temporarily unavailable" notice with a retry button.
     *
     * @param   object  $row        Message row
     * @param   string  $reference  Scripture reference
     * @param   string  $version    Bible version abbreviation
     *
     * @return  string  HTML notice
     *
     * @since  10.1.0
     */
    private function renderUnavailableNotice(object $row, string $reference, string $version): string
    {
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->useScript('com_proclaim.scripture-switcher');
        $wa->useStyle('com_proclaim.scripture-text');

        $ajaxUrl = Route::_('index.php?option=com_proclaim&task=cwmscripture.getPassageXHR&format=raw', false);
        Factory::getApplication()->getDocument()->addScriptOptions('com_proclaim.scripture', [
            'ajaxUrl' => $ajaxUrl,
        ]);

        $messageId = (int) ($row->id ?? 0);
        $uid       = uniqid('retry_', true);

        $html  = '<div class="scripture-container scripture-unavailable" '
            . 'data-reference="' . htmlspecialchars($reference) . '" '
            . 'data-version="' . htmlspecialchars($version) . '" '
            . 'data-message-id="' . $messageId . '">';
        $html .= '<div class="scripture-text">';
        $html .= '<div class="scripture-body">';
        $html .= '<p class="text-muted"><em>' . Text::_('JBS_CMN_SCRIPTURE_UNAVAILABLE') . '</em></p>';
        $html .= '<button type="button" class="btn btn-sm btn-outline-secondary scripture-retry-btn" '
            . 'id="' . $uid . '">'
            . '<i class="fas fa-redo" aria-hidden="true"></i> '
            . Text::_('JBS_CMN_SCRIPTURE_RETRY') . '</button>';
        $html .= '</div></div></div>';

        return $html;
    }

    /**
     * Build passage text for all scripture references on a message.
     *
     * Uses the junction table scriptures if available, falls back to single buildPassage().
     * Each reference gets its own passage display and version switcher.
     *
     * @param   object    $row     Message row (must have scriptures property)
     * @param   Registry  $params  Template params
     *
     * @return  string  Combined HTML output for all passages
     *
     * @since  10.1.0
     */
    public function buildAllPassages(object $row, Registry $params): string
    {
        if (empty($row->scriptures) || !\is_array($row->scriptures)) {
            // Fallback to legacy single passage
            return (string) $this->buildPassage($row, $params);
        }

        $output = '';

        foreach ($row->scriptures as $ref) {
            if (!($ref instanceof ScriptureReference) || $ref->booknumber <= 0) {
                continue;
            }

            // Build a virtual row for each reference
            $bookKey = '';

            foreach (CwmscriptureHelper::getAllBooks() as $book) {
                if ($book['booknumber'] === $ref->booknumber) {
                    $bookKey = $book['key'];

                    break;
                }
            }

            if ($bookKey === '') {
                continue;
            }

            $virtualRow = (object) [
                'id'            => $row->id ?? 0,
                'booknumber'    => $ref->booknumber,
                'bookname'      => $bookKey,
                'chapter_begin' => $ref->chapterBegin,
                'verse_begin'   => $ref->verseBegin,
                'chapter_end'   => $ref->chapterEnd,
                'verse_end'     => $ref->verseEnd,
                'bible_version' => $ref->bibleVersion,
            ];

            $passage = $this->buildPassage($virtualRow, $params);

            if ($passage !== false && $passage !== '') {
                // Add scripture reference heading above each passage block
                $refLabel = CwmscriptureHelper::formatReference(
                    $ref->booknumber,
                    $ref->chapterBegin,
                    $ref->verseBegin,
                    $ref->chapterEnd,
                    $ref->verseEnd
                );

                if ($refLabel !== '') {
                    $output .= '<h4 class="scripture-passage-heading">' . htmlspecialchars($refLabel) . '</h4>';
                }

                $output .= $passage;
            }
        }

        if ($output === '') {
            // Fallback to legacy single passage
            return (string) $this->buildPassage($row, $params);
        }

        return $output;
    }
}
