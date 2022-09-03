<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Table;

// No Direct Access
defined('_JEXEC') or die;

use Joomla\CMS\Access\Rules;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;

/**
 * Topic table class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CWMMessageTypeTable extends Table
{
    /**
     * Primary Key
     *
     * @var integer
     *
     * @since 9.0.0
     */
    public $id = null;

    /**
     * Topic text
     *
     * @var string
     *
     * @since 9.0.0
     */
    public $topic_text = null;

    /**
     * Published
     *
     * @var integer
     *
     * @since 9.0.0
     */
    public $published = 1;

    /**
     * Params
     *
     * @var string
     *
     * @since 9.0.0
     */
    public $params = null;

    public $asset_id;

    public $language;

    public $access;

    /**
     * Constructor
     *
     * @param   DatabaseDriver  $db  Database connector object
     *
     * @since 9.0.0
     */
    public function __construct(& $db)
    {
        parent::__construct('#__bsms_message_type', 'id', $db);
    }

    /**
     * Method to bind an associative array or object to the Table instance.This
     * method only binds properties that are publicly accessible and optionally
     * takes an array of properties to ignore when binding.
     *
     * @param   mixed  $array   An associative array or object to bind to the Table instance.
     * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
     *
     * @return  boolean  True on success.
     *
     * @todo    Consider deprecating this override
     * @link    http://docs.joomla.org/Table/bind
     * @since   11.1
     */
    public function bind($array, $ignore = '')
    {
        if (is_object($array))
        {
            return parent::bind($array, $ignore);
        }

        if (isset($array['params']) && is_array($array['params']))
        {
            $registry = new Registry;
            $registry->loadArray($array['params']);
            $array['params'] = (string) $registry;
        }

        // Bind the rules.
        if (isset($array['rules']) && is_array($array['rules']))
        {
            $rules = new Rules($array['rules']);
            $this->setRules($rules);
        }

        return parent::bind($array, $ignore);
    }

    /**
     * Method to store a row in the database from the Table instance properties.
     * If a primary key value is set the row with that primary key value will be
     * updated with the instance property values.  If no primary key value is set
     * a new row will be inserted into the database with the properties from the
     * Table instance.
     *
     * @param   boolean  $updateNulls  True to update fields even if they are null.
     *
     * @return  boolean  True on success.
     *
     * @link    https://docs.joomla.org/Table/store
     * @since   11.1
     */
    public function store($updateNulls = false)
    {
        if (!$this->_rules)
        {
            $this->setRules('{"core.delete":[],"core.edit":[],"core.create":[],"core.edit.state":[],"core.edit.own":[]}');
        }

        return parent::store($updateNulls);
    }

    /**
     * Overloaded load function
     *
     * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.  If not
     *                           set the instance property value is used.
     * @param   boolean  $reset  True to reset the default values before loading the new row.
     *
     * @return  boolean  True if successful. False if row not found.
     *
     * @see Table:load
     *
     * @since 9.0.0
     */
    public function load($keys = null, $reset = true)
    {
        if (parent::load($keys, $reset))
        {
            // Convert the languages field to a registry.
            $params = new Registry;
            $params->loadString($this->params);
            $this->params = $params;

            return true;
        }

        return false;
    }

    /**
     * check and (re-)construct the alias before storing the topic
     *
     * @param   array  $data      Data of record
     * @param   int    $recordId  id
     *
     * @return  boolean|array ?
     *
     * @since 9.0.0
     *
     * @todo this look like it is not used. (Neither Tom nor Brent wrote this one)
     */
    public function checkAlias($data = array(), $recordId = null)
    {
        $message_type = $data['message_type'];

        // Topic_text not given? -> use the first language item with some text
        if ($message_type == null || strlen($message_type) == 0)
        {
            if (isset($data['params']) && is_array($data['params']))
            {
                foreach ($data['params'] AS $language)
                {
                    if (strlen($language) > 0)
                    {
                        $topic = $language;
                        break;
                    }
                }
            }
        }

        // If still empty: use id
        // todo: For new items, this is always '0'. Next primary key would be nice...
        if ($message_type == null || strlen($message_type) == 0)
        {
            $message_type = $recordId;
        }

        // Add prefix if needed
        if (strncmp($message_type, 'JBS_TOP_', 8) != 0)
        {
            $message_type = 'JBS_CMN_MESSAGETYPES' . $message_type;
        }

        // And form well
        // replace all non a-Z 0-9 by '_'
        $message_type              = strtoupper(preg_replace('/[^a-z0-9]/i', '_', $topic));
        $data['message_type'] = $topic;

        return $data;
    }

    /**
     * Method to compute the default name of the asset.
     * The default name is in the form `table_name.id`
     * where id is the value of the primary key of the table.
     *
     * @return      string
     *
     * @since       1.6
     */
    protected function _getAssetName()
    {
        $k = $this->_tbl_key;

        return 'com_proclaim.message_type.' . (int) $this->$k;
    }

    /**
     * Method to return the title to use for the asset table.
     *
     * @return      string
     *
     * @since       1.6
     */
    protected function _getAssetTitle()
    {
        return 'CWM MessageType: ' . $this->message_type;
    }

    /**
     * Method to get the parent asset under which to register this one.
     * By default, all assets are registered to the ROOT node with ID 1.
     * The extended class can define a table and id to lookup.  If the
     * asset does not exist it will be created.
     *
     * @param   \Joomla\CMS\Table\Table|null  $table  A Table object for the asset parent.
     * @param   null                          $id     Id to look up
     *
     * @return  integer
     *
     * @since   11.1
     */
    protected function _getAssetParentId(Table $table = null, $id = null)
    {
        /** @var \Joomla\CMS\Table\Asset $asset */
        $asset = Table::getInstance('Asset');
        $asset->loadByName('com_proclaim');
        $asset->id = 1;
        return $asset->id;
    }
}