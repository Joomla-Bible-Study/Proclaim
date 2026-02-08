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
use CWM\Component\Proclaim\Site\Bible\BiblePassageResult;
use CWM\Component\Proclaim\Site\Bible\BibleProviderFactory;
use CWM\Component\Proclaim\Site\Bible\Provider\BibleGatewayProvider;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
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
    /** @var  string Link
     * @since 7.1
     */
    public string $link;

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
            $adminParams = new Registry();
        }

        // Legacy: if template still has scripture_provider set (migration period),
        // honor it for backward compatibility
        $legacyProvider = $params->get('scripture_provider', '');

        if (!empty($legacyProvider) && $legacyProvider === 'biblegateway') {
            return $this->renderIframePassage($reference, $choice, $params);
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
            // Fall back to BibleGateway iframe
            return $this->renderIframePassage($reference, $choice, $params);
        }

        if ($result->isIframe) {
            return $this->renderIframePassage($reference, $choice, $params);
        }

        if (!$result->hasText()) {
            return $this->renderIframePassage($reference, $choice, $params);
        }

        $output = $this->renderTextPassage($result, $choice, $params);

        // Add version switcher dropdown if enabled in template
        if ((int) $params->get('allow_version_switch', 0) === 1) {
            $output = $this->renderVersionSwitcher($row, $version, $adminParams) . $output;
        }

        return $output;
    }

    /**
     * Render a text-based scripture passage (from local or API provider).
     *
     * @param   BiblePassageResult  $result  The passage result
     * @param   int                 $choice  Display mode (0=hide, 1=toggle, 2=always, 3=popup)
     * @param   Registry            $params  Template parameters
     *
     * @return  string  HTML output
     *
     * @since  10.1.0
     */
    public function renderTextPassage(BiblePassageResult $result, int $choice, Registry $params): string
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
                $passage .= '</div></div>';
                break;

            case 2:
                // Always visible
                $passage  = '<div class="scripture-container scripture-visible">';
                $passage .= '<div class="scripture-text">';
                $passage .= '<div class="scripture-body">' . $result->text . '</div>';
                $passage .= $copyrightHtml;
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
                $passage .= '</div></div></div>';
                break;

            default:
                // Hidden (choice = 0), return nothing
                break;
        }

        return $passage;
    }

    /**
     * Render an iframe-based scripture passage (BibleGateway backward compat).
     *
     * @param   string    $reference  The reference string
     * @param   int       $choice     Display mode
     * @param   Registry  $params     Template parameters
     *
     * @return  string  HTML output
     *
     * @since  10.1.0
     */
    public function renderIframePassage(string $reference, int $choice, Registry $params): string
    {
        $version    = $params->get('bible_version', '77');
        $this->link = $this->getBiblegateway($reference, $version, $params);
        $passage    = '';
        $css        = false;

        switch ($choice) {
            case 1:
                $passage = $this->getHideShow($params);
                $css     = true;
                break;

            case 2:
                $passage = $this->getShow();
                $css     = true;
                break;

            case 3:
                $passage = $this->getLink($params);
                break;
        }

        if ($css) {
            Factory::getApplication()->getDocument()->getWebAssetManager()
                ->useStyle('com_proclaim.biblegateway-print');
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
    public function renderVersionSwitcher(object $row, string $version, Registry $adminParams): string
    {
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->useScript('com_proclaim.scripture-switcher');

        // Build reference for AJAX calls
        $reference = $this->formReference($row);

        $html  = '<div class="scripture-version-switcher">';
        $html .= '<select class="scripture-version-select form-select form-select-sm" '
            . 'data-reference="' . htmlspecialchars($reference) . '" '
            . 'data-message-id="' . (int) ($row->id ?? 0) . '" '
            . 'aria-label="' . Text::_('JBS_STY_BIBLE_VERSION') . '">';

        // Well-known BibleGateway version names
        $bgNames = [
            'kjv'  => 'King James Version', 'nlt' => 'New Living Translation',
            'esv'  => 'English Standard Version', 'niv' => 'New International Version',
            'nasb' => 'New American Standard Bible', 'nkjv' => 'New King James Version',
            'asvd' => 'American Standard Version', 'ylt' => "Young's Literal Translation",
            'hcsb' => 'Holman Christian Standard Bible', 'amp' => 'Amplified Bible',
            'cev'  => 'Contemporary English Version', 'msg' => 'The Message',
            'gnt'  => 'Good News Translation', 'web' => 'World English Bible',
        ];

        // Collect versions keyed by abbreviation to deduplicate
        $versions = [];

        // Get available translations from DB
        try {
            $db    = Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select($db->quoteName(['abbreviation', 'name']))
                ->from($db->quoteName('#__bsms_bible_translations'))
                ->order($db->quoteName('name') . ' ASC');
            $db->setQuery($query);
            $translations = $db->loadObjectList();

            foreach ($translations as $trans) {
                $versions[$trans->abbreviation] = $trans->name;
            }
        } catch (\Exception $e) {
            // DB not available
        }

        // Add BibleGateway versions if that provider is enabled
        $bgEnabled = (int) $adminParams->get('provider_biblegateway', 1) === 1;

        if ($bgEnabled) {
            foreach (BibleGatewayProvider::VERSION_MAP as $abbr) {
                if (!isset($versions[$abbr])) {
                    $versions[$abbr] = $bgNames[$abbr] ?? strtoupper($abbr);
                }
            }
        }

        // Fallback if nothing found
        if (empty($versions)) {
            $versions[$version] = strtoupper($version);
        }

        // Sort alphabetically and render
        asort($versions);

        foreach ($versions as $abbr => $name) {
            $selected = ($abbr === $version) ? ' selected' : '';
            $html .= '<option value="' . htmlspecialchars((string) $abbr) . '"' . $selected . '>'
                . htmlspecialchars($name) . '</option>';
        }

        $html .= '</select></div>';

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
     * Get Bible Gateway References
     *
     * @param   string    $reference  Search string
     * @param   string    $version    Bible Version
     * @param   Registry  $params     Parameters
     *
     * @return string
     *
     * @since    7.1
     */
    public function getBiblegateway($reference, $version, $params): string
    {
        return "https://www.biblegateway.com/passage/?search=" . $reference . "&version=" . $version . "&interface=print";
    }

    /**
     * Get HideShow
     *
     * @param   Registry  $params  Parameters
     *
     * @return string
     *
     * @since    7.1
     */
    public function getHideShow(Registry $params): string
    {
        $id      = 'scripture_' . uniqid('', true);
        $passage = '<div class="fluid-row"><div class="col-12"></div>';

        $passage .= '<a class="heading" href="#" role="button" aria-expanded="false" aria-controls="' . $id . '" onclick="var e = document.getElementById(\'' . $id . '\'); var isHidden = e.style.display == \'none\'; e.style.display = (isHidden ? \'block\' : \'none\'); this.setAttribute(\'aria-expanded\', isHidden); return false;">';

        if ((int) $params->get('showpassage_icon') === 1) {
            $passage .= '<i class="fas fa-bible fa-3x" aria-hidden="true" style="display: flex; margin-right: 10px;"></i>';
        }

        $passage .= Text::_('JBS_CMN_SHOW_HIDE_SCRIPTURE') . '</a>';
        $passage .= '<div id="' . $id . '" style="display: none;">';
        $passage .= '<iframe src="' . $this->link . '" width="100%" height="400" style="border:0;" title="' . Text::_('JBS_CMN_BIBLE_PASSAGE') . '"></iframe>';
        $passage .= '</div>';
        $passage .= '</div>';

        return $passage;
    }

    /**
     * Get Show
     *
     * @return string
     *
     * @since    7.1
     */
    public function getShow(): string
    {
        return '<div class="passage"><iframe src="' . $this->link . '" width="100%" height="400" style="border:0;" title="' . Text::_('JBS_CMN_BIBLE_PASSAGE') . '"></iframe></div>';
    }

    /**
     * Get Link
     *
     * @param   Registry  $params  Parameters
     *
     * @return string
     *
     * @since    7.1
     */
    public function getLink(Registry $params): string
    {
        $passage = '<div class="passage">';
        $passage .= '<a href="' . $this->link . '" ';
        $passage .= "onclick=\"window.open(this.href,'mywindow','toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,";
        $passage .= "resizable=yes,width=800,height=500'); return false;\"";
        $passage .= ' title="' . Text::_('JBS_STY_CLICK_TO_OPEN_PASSAGE') . '">';

        if ((int) $params->get('showpassage_icon') === 1) {
            $passage .= '<i class="fas fa-bible fa-3x" aria-hidden="true" style="display: flex; margin-right: 10px;"></i>';
        } elseif ($params->get('showpassage_icon') > 0) {
            $passage .= Text::_('JBS_STY_CLICK_TO_OPEN_PASSAGE');
        }

        $passage .= '</a></div>';

        return $passage;
    }
}
