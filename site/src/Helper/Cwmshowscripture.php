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
        $choice    = (int) $params->get('show_passage_view');

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

            return '';
        }

        if (!$result->hasText()) {
            Log::add('No text returned for "' . $reference . '" (' . $version . ') via ' . ($provider->getName() ?? 'unknown'), Log::WARNING, 'com_proclaim.bible');

            return '';
        }

        // Build version switcher HTML (injected inside the passage container)
        $switcherHtml = '';

        if ((int) $params->get('allow_version_switch', 0) === 1) {
            $switcherHtml = $this->renderVersionSwitcher($row, $version, $adminParams);
        }

        $output = $this->renderTextPassage($result, $choice, $params, $switcherHtml);

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

                if ((int) $params->get('showpassage_icon') === 1) {
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
                // Popup
                $popupId  = 'scripture_popup_' . uniqid('', true);
                $passage  = '<div class="scripture-container">';
                $passage .= '<a href="#" class="scripture-popup-trigger" '
                    . 'onclick="var p = document.getElementById(\'' . $popupId . '\'); '
                    . 'p.style.display = (p.style.display === \'none\' ? \'block\' : \'none\'); '
                    . 'return false;" '
                    . 'title="' . Text::_('JBS_STY_CLICK_TO_OPEN_PASSAGE') . '">';

                if ((int) $params->get('showpassage_icon') === 1) {
                    $passage .= '<i class="fas fa-bible fa-3x" aria-hidden="true" '
                        . 'style="display: flex; margin-right: 10px;"></i>';
                } elseif ($params->get('showpassage_icon') > 0) {
                    $passage .= Text::_('JBS_STY_CLICK_TO_OPEN_PASSAGE');
                }

                $passage .= '</a>';
                $passage .= '<div id="' . $popupId . '" class="scripture-popup" style="display: none;">';
                $passage .= '<div class="scripture-popup-content">';
                $passage .= '<button type="button" class="scripture-popup-close" '
                    . 'onclick="this.closest(\'.scripture-popup\').style.display = \'none\'; return false;" '
                    . 'aria-label="' . Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '">&times;</button>';
                $passage .= '<div class="scripture-body">' . $result->text . '</div>';
                $passage .= $copyrightHtml;
                $passage .= $switcherHtml;
                $passage .= '</div></div></div>';
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

        // Collect translations from DB with language info
        $translations = [];

        try {
            $db    = Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select($db->quoteName(['abbreviation', 'name', 'language']))
                ->from($db->quoteName('#__bsms_bible_translations'))
                ->order($db->quoteName('language') . ' ASC, ' . $db->quoteName('name') . ' ASC');
            $db->setQuery($query);
            $translations = $db->loadObjectList() ?: [];
        } catch (\Exception $e) {
            // DB not available
        }

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
}
