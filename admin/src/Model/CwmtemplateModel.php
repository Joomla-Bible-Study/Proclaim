<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Table\CwmtemplateTable;
use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Table\Table;

/**
 * Template model class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmtemplateModel extends AdminModel
{
    /**
     * Method to save the form data.
     *
     * @param   array  $data  The form data.
     *
     * @return  boolean  True on success, False on error.
     *
     * @throws Exception
     * @since   12.2
     */
    public function save($data)
    {
        // Make sure we cannot unpublished default template.
        if ($data['id'] == '1' && $data['published'] != '1') {
            Factory::getApplication()->enqueueMessage(Text::_('JBS_TPL_DEFAULT_ERROR'), 'error');

            return false;
        }

        $image                          = HTMLHelper::cleanImageURL($data['text']);
        $data['text']                   = $image->url;
        $image                          = HTMLHelper::cleanImageURL($data['show_page_image_series']);
        $data['show_page_image_series'] = $image->url;
        $image                          = HTMLHelper::cleanImageURL($data['popupimage']);
        $data['popupimage']             = $image->url;

        return parent::save($data);
    }

    /**
     * Copy Template
     *
     * @param   array  $cid  ID of template
     *
     * @return boolean
     *
     * @throws Exception
     * @since 7.0
     */
    public function copy($cid)
    {
        foreach ($cid as $id) {
            $db       = Factory::getContainer()->get('DatabaseDriver');
            $tmplCurr = new CwmtemplateTable($db);

            $tmplCurr->load($id);
            $tmplCurr->id    = '';
            $tmplCurr->title .= " - copy";

            if (!$tmplCurr->store()) {
                Factory::getApplication()->enqueueMessage($tmplCurr->getError(), 'error');

                return false;
            }
        }

        return true;
    }

    /**
     * Method to change the published state of one or more records.
     *
     * @param   array  $pks    A list of the primary keys to change.
     * @param   int    $value  The value of the published state.
     *
     * @return  bool  True on success.
     *
     * @throws Exception
     * @since   12.2
     */
    public function publish(&$pks, $value = 1): bool
    {
        foreach ($pks as $i => $pk) {
            if ($pk == 1 && $value != 1) {
                Factory::getApplication()->enqueueMessage(Text::_('JBS_TPL_DEFAULT_ERROR'), 'error');
                unset($pks[$i]);
            }
        }

        return parent::publish($pks, $value);
    }

    /**
     * Get the form data
     *
     * @param   array  $data      Data for the form.
     * @param   bool   $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  mixed  A JForm object on success, false on failure
     *
     * @throws Exception
     * @since  7.0
     */
    public function getForm($data = array(), $loadData = true): mixed
    {
        // Get the form.
        $form = $this->loadForm(
            'com_proclaim.template',
            'template',
            array('control' => 'jform', 'load_data' => $loadData)
        );

        return $form ?? false;
    }

    /**
     * Method to check out a row for editing.
     *
     * @param   int  $pk  The numeric id of the primary key.
     *
     * @return  ?int  False on failure or error, true otherwise.
     *
     * @since   11.1
     */
    public function checkout($pk = null): ?int
    {
        return $pk;
    }

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string  $name     The table name. Optional.
     * @param   string  $prefix   The class prefix. Optional.
     * @param   array   $options  Configuration array for model. Optional.
     *
     * @return  Table  A Table object
     *
     * @throws  Exception
     * @since   3.0
     */
    public function getTable($name = 'Cwmtemplate', $prefix = '', $options = array()): Table
    {
        return parent::getTable($name, $prefix, $options);
    }

    /**
     * Load Form Date
     *
     * @return  array|CMSObject    The default data is an empty array.
     *
     * @throws Exception
     * @since   7.0
     */
    protected function loadFormData(): array|CMSObject
    {
        $data = Factory::getApplication()->getUserState('com_proclaim.edit.template.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    /**
     * Custom clean the cache of COM_Proclaim and Proclaim modules
     *
     * @param   string  $group      The cache group
     * @param   int     $client_id  The ID of the client
     *
     * @return  void
     *
     * @since    1.6
     */
    protected function cleanCache($group = null, $client_id = 0): void
    {
        parent::cleanCache('com_proclaim');
        parent::cleanCache('mod_proclaim');
    }
}
