<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 10/23/13
 * Time: 7:46 AM
 */
defined('_JEXEC') or die;

JLoader::register('JBSMDbHelper', JPATH_ADMINISTRATOR . '/components/com_biblestudy/helpers/dbhelper.php');

/**
 * Update for 8.1.0 class
 *
 * @package  BibleStudy.Admin
 * @since    8.1.0
 */
class JBS810Update
{
    public function update810()
    {
        self::updatetemplates();
        self::updateDocMan();
        return true;
    }

    public function updatetemplates()
    {
        $db = JFactory::getDBO();
        $query = 'SELECT id, title, params from #__bsms_templates';
        $db->setQuery($query);
        $data = $db->loadObjectList();
        foreach ($data as $d)
        {
            // Load Table Data.
            JTable::addIncludePath(JPATH_COMPONENT . '/tables');
            $table = JTable::getInstance('Template', 'Table', array('dbo' => $db));

            try
            {
                $table->load($d->id);
            }
            catch (Exception $e)
            {
                echo 'Caught exception: ', $e->getMessage(), "\n";
            }

            //store the table to invoke defaults of new params

            $table->store();
        }
    }
    public function updateDocMan()
    {
        $db = JFactory::getDBO();
        $query = 'UPDATE #__bsms_mediafiles SET `docMan_id` = varchar(250) NULL';
        $db->setQuery($query);
        $result = $db->query();
        if (!$result){return false;}
        else {return true;}
    }
}
