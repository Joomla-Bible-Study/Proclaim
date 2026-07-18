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

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\TextField;
use Joomla\CMS\Language\Text;

/**
 * Read-only YouTube playlist picker.
 *
 * Renders the remote playlist ID as a read-only input paired with a "Browse
 * Playlists" button. The button opens a live picker (Proclaim.PlaylistPicker)
 * that lists the selected server's channel playlists and, on pick, writes the
 * ID back here and auto-fills the Title when it is still empty. The value is
 * never typed by hand — bulk import is the primary path; this is the manual
 * edge case.
 *
 * @package  Proclaim.Admin
 * @since    10.3.3
 */
class PlaylistPickerField extends TextField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  10.3.3
     */
    protected $type = 'PlaylistPicker';

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @throws  \Exception
     * @since   10.3.3
     */
    protected function getInput(): string
    {
        // The ID is chosen via the picker, never typed.
        $this->readonly = true;

        Factory::getApplication()->getDocument()->getWebAssetManager()
            ->useScript('com_proclaim.playlist-picker');

        Text::script('JBS_PLAYLIST_PICK_TITLE');
        Text::script('JBS_PLAYLIST_PICK_NO_SERVER');
        Text::script('JBS_PLAYLIST_PICK_EMPTY');
        Text::script('JGLOBAL_LOADING');
        Text::script('JCANCEL');
        Text::script('JERROR_AN_ERROR_HAS_OCCURRED');

        $button = '<button type="button" class="btn btn-secondary"'
            . ' onclick="Proclaim.PlaylistPicker.open(this)">'
            . '<span class="icon-list" aria-hidden="true"></span> '
            . Text::_('JBS_PLAYLIST_BROWSE')
            . '</button>';

        return '<div class="input-group">' . parent::getInput() . $button . '</div>';
    }
}
