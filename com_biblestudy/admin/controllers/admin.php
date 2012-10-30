<?php

/**
 * Controller for Admin
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

JLoader::register('fixJBSAssets', BIBLESTUDY_PATH_ADMIN_LIB . '/biblestudy.assets.php');
JLoader::register('JBSconvert', BIBLESTUDY_PATH_ADMIN_LIB . '/biblestudy.sermonspeakerconvert.class.php');
JLoader::register('JBSPIconvert', BIBLESTUDY_PATH_ADMIN_LIB . '/biblestudy.preachitconvert.class.php');
JLoader::register('fixJBSalias', BIBLESTUDY_PATH_ADMIN_HELPERS . '/alias.php');

jimport('joomla.application.component.controllerform');

/**
 * Controller for Admin
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class BiblestudyControllerAdmin extends JControllerForm
{

    /**
     * NOTE: This is needed to prevent Joomla 1.6's pluralization mechanisim from kicking in
     *
     * @since 7.0
     */
    protected $view_list = 'cpanel';

    /**
     * constructor (registers additional tasks to methods)
     * @return void
     * @param array $config
     */
    function __construct($config = array())
    {
        parent::__construct($config);

        // Register Extra tasks
        $this->registerTask('add', 'edit');
        $this->registerTask('apply', 'save');
    }

    /**
     * Tools to change player or pupup
     */
    function tools()
    {
        $tool = JRequest::getVar('tooltype', '', 'post');
        switch ($tool) {
            case 'players':
                $player = $this->changePlayers();
                if (!$player) {
                    $msg = JText::_('JBS_CMN_OPERATION_FAILED');
                    $this->setRedirect('index.php?option=com_biblestudy&view=cpanel', $msg);
                }
                break;

            case 'popups':
                $popups = $this->changePopup();
                if (!$popups) {
                    $msg = JText::_('JBS_CMN_OPERATION_FAILED');
                    $this->setRedirect('index.php?option=com_biblestudy&view=cpanel', $msg);
                }
                break;
        }
    }

    /**
     * Reset Hits
     */
    function resetHits()
    {
        $msg = null;
        $db = JFactory::getDBO();
        $db->setQuery("UPDATE #__bsms_studies SET hits='0'");
        if (!$db->execute()) {
            $error = $db->getErrorMsg();
            $msg = JText::_('JBS_CMN_ERROR_RESETTING_HITS') . ' ' . $error;
        } else {
            $updated = $db->getAffectedRows();
            $msg = JText::_('JBS_CMN_RESET_SUCCESSFUL') . ' ' . $updated . ' ' . JText::_('JBS_CMN_ROWS_RESET');
        }
        $this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $msg);
    }

    /**
     * Reset Downloads
     */
    function resetDownloads()
    {
        $msg = null;
        $db = JFactory::getDBO();
        $db->setQuery("UPDATE #__bsms_mediafiles SET downloads='0'");
        if (!$db->execute()) {
            $error = $db->getErrorMsg();
            $msg = JText::_('JBS_CMN_ERROR_RESETTING_DOWNLOADS') . ' ' . $error;
        } else {
            $updated = $db->getAffectedRows();
            $msg = JText::_('JBS_CMN_RESET_SUCCESSFUL') . ' ' . $updated . ' ' . JText::_('JBS_CMN_ROWS_RESET');
        }
        $this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $msg);
    }

    /**
     * Reset Players
     */
    function resetPlays()
    {
        $msg = null;
        $db = JFactory::getDBO();
        $db->setQuery("UPDATE #__bsms_mediafiles SET plays='0'");
        if (!$db->execute()) {
            $error = $db->getErrorMsg();
            $msg = JText::_('JBS_CMN_ERROR_RESETTING_PLAYS') . ' ' . $error;
        } else {
            $updated = $db->getAffectedRows();
            $msg = JText::_('JBS_CMN_RESET_SUCCESSFUL') . ' ' . $updated . ' ' . JText::_('JBS_CMN_ROWS_RESET');
        }
        $this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $msg);
    }

    /**
     * Change Player Modes
     */
    function changePlayers()
    {
        $db = JFactory::getDBO();
        $msg = null;
        $from = JRequest::getInt('from', '', 'post');
        $to = JRequest::getInt('to', '', 'post');
        switch ($from) {
            case '100':
                $query = "UPDATE #__bsms_mediafiles SET `player` = " . $db->quote($to) . " WHERE `player` IS NULL";
                break;

            default:
                $query = "UPDATE #__bsms_mediafiles SET `player` = " . $db->quote($to) . " WHERE `player` = " . $db->quote($from);
        }
        $db->setQuery($query);
        if (!$db->execute()) {
            $msg = JText::_('JBS_ADM_ERROR_OCCURED') . ' ' . $db->getErrorMsg();
        } else {
            $msg = JText::_('JBS_CMN_OPERATION_SUCCESSFUL');
        }
        $this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $msg);
    }

    /**
     * Change Media Popup
     */
    function changePopup()
    {
        $db = JFactory::getDBO();
        $msg = null;
        $from = JRequest::getInt('pfrom', '', 'post');
        $to = JRequest::getInt('pto', '', 'post');
        $query = "UPDATE #__bsms_mediafiles SET `popup` = " . $db->quote($to) . " WHERE `popup` = " . $db->quote($from);
        $db->setQuery($query);
        if (!$db->execute()) {
            $msg = JText::_('JBS_ADM_ERROR_OCCURED') . ' ' . $db->getErrorMsg();
        } else {
            $msg = JText::_('JBS_CMN_OPERATION_SUCCESSFUL');
        }
        $this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $msg);
    }

    /**
     * Check Assets
     */
    function checkassets()
    {
        $asset = new fixJBSAssets();
        $checkassets = $asset->checkAssets();
        JRequest::setVar('checkassets', $checkassets, 'get', JREQUEST_ALLOWRAW);
        parent::display();
    }

    /**
     * Fix Assets
     */
    function fixAssets()
    {
        $asset = new fixJBSAssets();
        $asset->fixAssets();
        $this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1&task=admin.checkassets');
    }

    /**
     * Convert SermonSpeaker to BibleStudy
     */
    function convertSermonSpeaker()
    {
        $convert = new JBSconvert();
        $ssconversion = $convert->convertSS();
        $this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $ssconversion);
    }

    /**
     * Convert PreachIt to BibleStudy
     */
    function convertPreachIt()
    {
        $convert = new JBSPIconvert();
        $piconversion = $convert->convertPI();
        $this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $piconversion);
    }

    /**
     * Tries to fix missing database updates
     *
     * @since    7.1.0
     */
    function fix()
    {
        $model = $this->getModel('admin');
        $model->fix();
        $this->setRedirect(JRoute::_('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', false));
    }

    /**
     * Alias Updates
     * @since 7.1.0
     */
    function aliasUpdate()
    {
        $alias = new fixJBSalias();
        $update = $alias->updateAlias();
        $this->setMessage(JText::_('JBS_ADM_ALIAS_ROWS') . $update);
        $this->setRedirect(JRoute::_('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', false));
    }

}