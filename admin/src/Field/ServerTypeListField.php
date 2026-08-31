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
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;

/**
 * The server types actually present, for filtering the servers list.
 *
 * Distinct values from the table rather than the set of installed addons: a
 * filter offering a type no row uses produces an empty list and reads as a
 * broken screen, and `legacy` is not an addon at all — it is the 9.x type a
 * migration retires, which is precisely the one worth filtering to.
 *
 * @since  __DEPLOY_VERSION__
 */
class ServerTypeListField extends ListField
{
    /**
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $type = 'ServerTypeList';

    /**
     * Build the option list.
     *
     * @return  array  The field option objects.
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getOptions(): array
    {
        $db      = Factory::getContainer()->get(DatabaseInterface::class);
        $options = [];

        try {
            $query = $db->createQuery()
                ->select(
                    'DISTINCT ' . $db->quoteName('type', 'value') . ', '
                    . $db->quoteName('type', 'text')
                )
                ->from($db->quoteName('#__bsms_servers'))
                ->where($db->quoteName('type') . ' <> ' . $db->quote(''))
                ->order($db->quoteName('type') . ' ASC');
            $db->setQuery($query);

            $options = $db->loadObjectList() ?: [];
        } catch (\Exception $e) {
            // A filter that cannot read its options is worth reporting, but not
            // worth taking the list screen down for: the parent options still
            // render and the filter simply offers nothing.
            Log::add('ServerTypeList options query failed: ' . $e->getMessage(), Log::WARNING, 'com_proclaim');
        }

        return array_merge(parent::getOptions(), $options);
    }
}
