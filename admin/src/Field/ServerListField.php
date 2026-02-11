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
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Server List Form Field class for the Proclaim component
 *
 * @package  Proclaim.Admin
 * @since    10.0.0
 */
class ServerListField extends ListField
{
    /**
     * The field type.
     *
     * @var  string
     *
     * @since 10.0.0
     */
    protected $type = 'ServerList';

    /**
     * Server ID-to-type mapping built during getOptions().
     *
     * @var  array<int, string>
     *
     * @since 10.1.0
     */
    protected array $serverTypeMap = [];

    /**
     * Method to get a list of options for a list input.
     *
     * @return  array  An array of JHtml options.
     *
     * @since 10.0.0
     */
    #[\Override]
    protected function getOptions(): array
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select($db->quoteName(['id', 'server_name', 'type']))
            ->from($db->quoteName('#__bsms_servers'))
            ->where($db->quoteName('published') . ' = 1')
            ->order($db->quoteName('server_name') . ' ASC');
        $db->setQuery($query);
        $servers = $db->loadObjectList();
        $options = [];

        $this->serverTypeMap = [];

        if ($servers) {
            foreach ($servers as $server) {
                $options[]                              = HTMLHelper::_('select.option', $server->id, $server->server_name);
                $this->serverTypeMap[(int) $server->id] = $server->type;
            }
        }

        return array_merge(parent::getOptions(), $options);
    }

    /**
     * Get the field input markup with a data-server-types attribute.
     *
     * @return  string  The field input markup.
     *
     * @since   10.1.0
     */
    #[\Override]
    protected function getInput(): string
    {
        // Ensure options (and serverTypeMap) are populated
        if (empty($this->serverTypeMap)) {
            $this->getOptions();
        }

        $html = parent::getInput();

        // Inject data-server-types JSON attribute onto the <select> element
        $attr = ' data-server-types="' . htmlspecialchars(json_encode($this->serverTypeMap), ENT_QUOTES, 'UTF-8') . '"';
        $html = preg_replace('/<select\b/', '<select' . $attr, $html, 1);

        return $html;
    }
}
