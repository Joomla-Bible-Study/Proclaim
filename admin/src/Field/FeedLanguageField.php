<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Language codes for a podcast feed's <language> tag.
 *
 * Deliberately not Joomla's `contentlanguage` field. That offers the content
 * languages *installed in Joomla*, and the two are different concepts: RSS
 * <language> describes the audio, while a content language describes what
 * translations the CMS serves. Joomla ships en-GB and does not install en-US,
 * so a US church using the content languages could not select its own.
 *
 * The list is the codes podcast directories actually act on, not the full
 * RFC 5646 registry — Apple and Spotify use this for language and territory
 * targeting, and a long list of codes nobody selects helps no one. A stored
 * value outside the list is preserved rather than silently dropped, so a feed
 * hand-set to something unusual survives an edit.
 *
 * @package  Proclaim.Admin
 * @since    10.5.1
 */
class FeedLanguageField extends ListField
{
    /**
     * The field type.
     *
     * @var    string
     * @since  10.5.1
     */
    protected $type = 'FeedLanguage';

    /**
     * Codes worth offering, grouped by language, most-used variant first.
     *
     * @var    array<string, string>
     * @since  10.5.1
     */
    private const CODES = [
        'en-us' => 'English (United States) — en-us',
        'en-gb' => 'English (United Kingdom) — en-gb',
        'en-au' => 'English (Australia) — en-au',
        'en-ca' => 'English (Canada) — en-ca',
        'en-nz' => 'English (New Zealand) — en-nz',
        'en-za' => 'English (South Africa) — en-za',
        'es-es' => 'Spanish (Spain) — es-es',
        'es-mx' => 'Spanish (Mexico) — es-mx',
        'es-us' => 'Spanish (United States) — es-us',
        'pt-br' => 'Portuguese (Brazil) — pt-br',
        'pt-pt' => 'Portuguese (Portugal) — pt-pt',
        'fr-fr' => 'French (France) — fr-fr',
        'fr-ca' => 'French (Canada) — fr-ca',
        'de-de' => 'German — de-de',
        'nl-nl' => 'Dutch — nl-nl',
        'it-it' => 'Italian — it-it',
        'sv-se' => 'Swedish — sv-se',
        'no-no' => 'Norwegian — no-no',
        'da-dk' => 'Danish — da-dk',
        'fi-fi' => 'Finnish — fi-fi',
        'pl-pl' => 'Polish — pl-pl',
        'cs-cz' => 'Czech — cs-cz',
        'hu-hu' => 'Hungarian — hu-hu',
        'ro-ro' => 'Romanian — ro-ro',
        'ru-ru' => 'Russian — ru-ru',
        'uk-ua' => 'Ukrainian — uk-ua',
        'zh-cn' => 'Chinese (Simplified) — zh-cn',
        'zh-tw' => 'Chinese (Traditional) — zh-tw',
        'ja-jp' => 'Japanese — ja-jp',
        'ko-kr' => 'Korean — ko-kr',
        'hi-in' => 'Hindi — hi-in',
        'id-id' => 'Indonesian — id-id',
        'tl-ph' => 'Tagalog — tl-ph',
        'sw-ke' => 'Swahili — sw-ke',
        'af-za' => 'Afrikaans — af-za',
    ];

    /**
     * Method to get a list of options for a list input.
     *
     * @return  array  An array of HTMLHelper options.
     *
     * @since   10.5.1
     */
    #[\Override]
    protected function getOptions(): array
    {
        $options = [HTMLHelper::_('select.option', '*', Text::_('JBS_PDC_FEED_LANGUAGE_SITE_DEFAULT'))];

        foreach (self::CODES as $code => $label) {
            $options[] = HTMLHelper::_('select.option', $code, $label);
        }

        $options = array_merge(parent::getOptions(), $options);

        // Keep a stored value the list does not offer, or saving the form would
        // quietly replace a deliberate choice with the first option.
        $value = strtolower((string) $this->value);

        if ($value !== '' && !\in_array($value, array_column($options, 'value'), true)) {
            $options[] = HTMLHelper::_('select.option', $value, $value);
        }

        return $options;
    }
}
