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
use Joomla\CMS\Form\Field\RadioField;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * YouTube Stream Mode Field — the External/Direct switcher, gated on the
 * server's YouTube account connection.
 *
 * Direct mode cannot do anything without OAuth: every live-broadcast call
 * needs an authorized client. Offering the switch on an unconnected server
 * only configures a mode with none of its prerequisites in place, so here
 * the switch renders disabled and pinned to External, with the requirement
 * stated where the administrator is deciding.
 *
 * @package  Proclaim.Admin
 * @since    10.4.1
 */
class YoutubeStreamModeField extends RadioField
{
    /**
     * The field type.
     *
     * @var  string
     *
     * @since 10.4.1
     */
    protected $type = 'YoutubeStreamMode';

    /**
     * Whether the server being edited has a connected YouTube account.
     *
     * Reads the stored server record directly: Form::bindLevel() drops
     * params keys that have no matching field definition, so the bound
     * form never carries oauth_token. This is the same stored key
     * isOAuthConnected() and the media-form gate test.
     *
     * @return  bool
     *
     * @throws \Exception
     * @since   10.4.1
     */
    public function isServerConnected(): bool
    {
        $serverId = (int) Factory::getApplication()->getInput()->getInt('id', 0);

        if ($serverId === 0) {
            return false;
        }

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->createQuery()
            ->select($db->quoteName('params'))
            ->from($db->quoteName('#__bsms_servers'))
            ->where($db->quoteName('id') . ' = ' . $serverId);
        $db->setQuery($query);
        $params = new Registry($db->loadResult() ?: '{}');

        return !empty($params->get('oauth_token'));
    }

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @throws \Exception
     * @since   10.4.1
     */
    protected function getInput(): string
    {
        if ($this->isServerConnected()) {
            return parent::getInput();
        }

        $lang = Factory::getApplication()->getLanguage();
        $lang->load('jbs_addon_youtube', JPATH_ADMINISTRATOR . '/components/com_proclaim/src/Addons/Servers/Youtube');

        // No switch at all: a control whose only enabled choice is its
        // default would just invite clicking. The hidden input keeps the
        // stored value an explicit "external" (and keeps the showon-gated
        // fields below it hidden) rather than a silently dropped key.
        return '<div class="alert alert-warning d-flex align-items-start mb-0">'
            . '<span class="icon-warning-2 me-2 mt-1" aria-hidden="true"></span>'
            . '<div>' . Text::_('JBS_ADDON_YOUTUBE_STREAM_MODE_OAUTH_REQUIRED') . '</div>'
            . '</div>'
            . '<input type="hidden" name="' . htmlspecialchars($this->name, ENT_QUOTES, 'UTF-8')
            . '" id="' . htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8')
            . '" value="external">';
    }
}
