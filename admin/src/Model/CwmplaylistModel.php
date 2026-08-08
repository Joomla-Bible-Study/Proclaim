<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CwmlogHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;

/**
 * Playlist model class
 *
 * @package  Proclaim.Admin
 * @since    10.3.3
 */
class CwmplaylistModel extends AdminModel
{
    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string  $name     The table name. Optional.
     * @param   string  $prefix   The class prefix. Optional.
     * @param   array   $options  Configuration array for model. Optional.
     *
     * @return  Table  A Table object
     *
     * @throws  \Exception
     * @since   10.3.3
     */
    public function getTable($name = 'Cwmplaylist', $prefix = '', $options = []): Table
    {
        return parent::getTable($name, $prefix, $options);
    }

    /**
     * Method to get a single record.
     *
     * Decodes the default_settings JSON column into an array so the form's
     * default_settings field group can populate its sub-fields.
     *
     * @param   int|null  $pk  The id of the primary key.
     *
     * @return  \Joomla\CMS\Object\CMSObject|bool  Object on success, false on failure.
     *
     * @throws  \Exception
     * @since   10.3.3
     */
    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);

        if ($item && !empty($item->default_settings) && \is_string($item->default_settings)) {
            try {
                $decoded                = json_decode($item->default_settings, true, 512, JSON_THROW_ON_ERROR);
                $item->default_settings = \is_array($decoded) ? $decoded : [];
            } catch (\JsonException $e) {
                // The fallback stays [], because the form has to render something.
                // What changes is that the administrator is told: the fields below
                // will show defaults, and saving replaces the stored value with
                // them. Silently substituting [] turned an unreadable column into
                // a wiped one on the next save.
                $item->default_settings = [];

                CwmlogHelper::warning(
                    'Playlist ' . (int) ($item->id ?? 0) . ' has an unreadable default_settings column; '
                    . 'the edit form is showing defaults: ' . $e->getMessage()
                );

                Factory::getApplication()->enqueueMessage(
                    Text::_('JBS_CMN_PLAYLIST_SETTINGS_UNREADABLE'),
                    'warning'
                );
            }
        }

        return $item;
    }

    /**
     * Get the form data
     *
     * @param   array  $data      Data for the form.
     * @param   bool   $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  false|Form  A Form object on success, false on failure
     *
     * @throws  \Exception
     * @since   10.3.3
     */
    public function getForm($data = [], $loadData = true): mixed
    {
        $form = $this->loadForm(
            'com_proclaim.playlist',
            'playlist',
            ['control' => 'jform', 'load_data' => $loadData]
        );

        return $form ?? false;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed  The data for the form.
     *
     * @throws  \Exception
     * @since   10.3.3
     */
    protected function loadFormData(): mixed
    {
        $data = Factory::getApplication()->getUserState('com_proclaim.edit.playlist.data', []);

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    /**
     * Prepare and sanitise the table prior to saving.
     *
     * @param   \CWM\Component\Proclaim\Administrator\Table\CwmplaylistTable  $table  A reference to a Table object.
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   10.3.3
     */
    protected function prepareTable($table): void
    {
        $date = new Date();
        $user = Factory::getApplication()->getIdentity();

        if (empty($table->created) || $table->created === '') {
            $table->created = $date->toSql();
        }

        if (empty($table->id)) {
            if (empty($table->created_by)) {
                $table->created_by = $user->id;
            }

            // Place new playlists at the end of the ordering list.
            if (empty($table->ordering)) {
                $table->ordering = $table->getNextOrder();
            }
        } else {
            $table->modified    = $date->toSql();
            $table->modified_by = $user->id;
        }
    }

    /**
     * Custom clean the cache of com_proclaim and proclaim modules
     *
     * @param   string  $group      The cache group
     * @param   int     $client_id  The ID of the client
     *
     * @return  void
     *
     * @since   10.3.3
     */
    protected function cleanCache($group = null, int $client_id = 0): void
    {
        parent::cleanCache('com_proclaim');
        parent::cleanCache('mod_proclaim');
    }
}
