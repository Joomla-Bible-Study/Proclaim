<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Administrator\Addons\Servers\Youtube\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * YouTube Stream Key Field — read-only display of the server's persistent
 * ingestion details (#1298 phase 2): the RTMP URL and the stream key the
 * encoder points at.
 *
 * The key is a secret — anyone holding it can stream to the channel — so
 * it renders masked, with an explicit reveal toggle. Before the first
 * broadcast provisions the stream, the field just says so.
 *
 * @package  Proclaim.Admin
 * @since    10.4.0
 */
class YoutubeStreamKeyField extends FormField
{
    /**
     * The field type.
     *
     * @var  string
     *
     * @since 10.4.0
     */
    protected $type = 'YoutubeStreamKey';

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @throws \Exception
     * @since   10.4.0
     */
    protected function getInput(): string
    {
        $lang = Factory::getApplication()->getLanguage();
        $lang->load('jbs_addon_youtube', JPATH_ADMINISTRATOR . '/components/com_proclaim/src/Addons/Servers/Youtube');

        // Read the stored details straight from the server record. Form
        // binding can't be used here: Form::bindLevel() drops scalar params
        // keys that have no matching field definition, and these are
        // deliberately not editable fields.
        $serverId  = (int) Factory::getApplication()->getInput()->getInt('id', 0);
        $streamKey = '';
        $rtmpUrl   = '';

        if ($serverId > 0) {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select($db->quoteName('params'))
                ->from($db->quoteName('#__bsms_servers'))
                ->where($db->quoteName('id') . ' = ' . $serverId);
            $db->setQuery($query);
            $params = new Registry($db->loadResult() ?: '{}');

            $streamKey = (string) $params->get('live_stream_key', '');
            $rtmpUrl   = (string) $params->get('live_rtmp_url', '');
        }

        if ($streamKey === '') {
            return '<div class="alert alert-info mb-0">'
                . '<span class="icon-info-circle me-2" aria-hidden="true"></span>'
                . Text::_('JBS_ADDON_YOUTUBE_STREAM_KEY_PENDING')
                . '</div>';
        }

        $escapedKey  = htmlspecialchars($streamKey, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $escapedRtmp = htmlspecialchars($rtmpUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $revealText  = Text::_('JBS_ADDON_YOUTUBE_STREAM_KEY_REVEAL');

        $html   = [];
        $html[] = '<div class="mb-2">';
        $html[] = '<label class="form-label small fw-semibold mb-1">' . Text::_('JBS_ADDON_YOUTUBE_RTMP_URL') . '</label>';
        $html[] = '<input type="text" class="form-control" readonly value="' . $escapedRtmp . '">';
        $html[] = '</div>';
        $html[] = '<label class="form-label small fw-semibold mb-1">' . Text::_('JBS_ADDON_YOUTUBE_STREAM_KEY') . '</label>';
        $html[] = '<div class="input-group">';
        $html[] = '<input type="password" class="form-control" readonly id="youtube-stream-key-value" '
            . 'value="' . $escapedKey . '" autocomplete="off">';
        $html[] = '<button type="button" class="btn btn-secondary" '
            . 'aria-label="' . $revealText . '" title="' . $revealText . '" '
            . 'onclick="var f=document.getElementById(\'youtube-stream-key-value\');'
            . "f.type = f.type === 'password' ? 'text' : 'password';\">";
        $html[] = '<span class="icon-eye" aria-hidden="true"></span>';
        $html[] = '</button>';
        $html[] = '</div>';
        $html[] = '<div class="form-text">' . Text::_('JBS_ADDON_YOUTUBE_STREAM_KEY_DESC_NOTE') . '</div>';

        return implode('', $html);
    }
}
