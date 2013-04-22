<?php

/**
 * Update for 7.1.0
 *
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link    http://www.JoomlaBibleStudy.org
 */
//No Direct Access
defined('_JEXEC') or die;

JLoader::register('JBSMDbHelper', JPATH_ADMINISTRATOR . '/components/com_biblestudy/helpers/dbhelper.php');

/**
 * Update for 7.1.0 class
 *
 * @package BibleStudy.Admin
 * @since   7.1.0
 */
class JBS710Update
{

    /**
     * Method to Update to 7.1.0
     *
     * @return boolean
     */
    public function update710()
    {

    }

    public static function setemptytemplates()
    {
        $db    = JFactory::getDBO();
        $query = 'SELECT id FROM #__bsms_templates';
        $db->setQuery($query);
        $results = $db->loadObjectList();
        foreach ($results as $result)
        {
            // Store new Recorde so it can be seen.
            JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');
            $table = JTable::getInstance('Template', 'Table', array('dbo' => $db));
            try
            {
                $table->load($result->id);
                //@todo this is a Joomla bug for currentAssetId being missing in table.php. When fixed in Joomla should be removed
                @$table->store();
                $table->load($result->id);
                $registry = new JRegistry;
                $registry->loadString($table->params);
                $css = $registry->get('css');
                $registry->set('css', 'biblestudy.css');

                //Now write the params back into the $table array and store.
                $table->params = (string) $registry->toString();
                //@todo this is a Joomla bug for currentAssetId being missing in table.php. When fixed in Joomla should be removed
                @$table->store();
            }
            catch (Exception $e)
            {
                JError::raiseWarning(1, 'Caught exception: ' . $e->getMessage());
            }
        }
    }

}
