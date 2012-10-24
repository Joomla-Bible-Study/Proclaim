<?php
/**
 * Tearm JView
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;



// This is the popup window for the teachings.  We could put anything in this window.
//TODO Need to Clean this up and rework to be proper Joomla calls bcc
/**
 * View class for Terms
 * @package BibleStudy.Site
 * @since 7.0.0
 */
class biblestudyViewterms extends JViewLegacy {

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  mixed  A string if successful, otherwise a JError object.
     *
     * @see     fetch()
     * @since   11.1
     */
    public function display($tpl = null) {
        $t = JRequest::getInt('t', '1', 'get');
        $mid = JRequest::getInt('mid', '', 'get');
        $compat_mode = JRequest::getInt('compat_mode', '0', 'get');

        $db = JFactory::getDBO();
        $query = $db->getQuery('true');
        $query->select('*');
        $query->from('#__bsms_templates');
        $query->where('id = ' . $t);
        $db->setQuery($query);
        //$db->query();
        $template = $db->loadObject();
        $registry = new JRegistry();
        $registry->loadJSON($template->params);
        $params = $registry;
        $termstext = $params->get('terms');

        $query = $db->getQuery('true');
        $query->select('*');
        $query->from('#__bsms_mediafiles');
        $query->where('id= ' . $mid);
        $db->setQuery($query);
        // $db->query();
        $media = $db->loadObject();
        ?>
        <div class="termstext">
            <?php
            echo $termstext;
            ?>
        </div>
        <div class="termslink">
            <?php
            if ($compat_mode == 1) {
                echo '<a href="http://joomlabiblestudy.org/router.php?file=' . $media->spath . $media->fpath . $media->filename . '&size=' . $media->size . '">' . JText::_('JBS_CMN_CONTINUE_TO_DOWNLOAD') . '</a>';
            } else {
                echo '<a href="index.php?option=com_biblestudy&mid=' . $media->id . '&view=sermons&task=download">' . JText::_('JBS_CMN_CONTINUE_TO_DOWNLOAD') . '</a>';
            }
            ?>

        </div>
        <?php
    }

}

//end of class