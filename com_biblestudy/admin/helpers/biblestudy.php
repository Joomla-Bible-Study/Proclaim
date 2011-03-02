<?php

/**
 * @author Tom Fuller
 * @copyright 2011
 */
defined('_JEXEC') or die('Restriced Access');

class BibleStudyHelper
{
    public static $extension = 'com_biblestudy';
public static function getActions($Itemid = 0, $type = null)
        {
                $user  = JFactory::getUser();
                $result        = new JObject;
 
                if (empty($Itemid)) {
                        $assetName = 'com_biblestudy';
                }
                else {
                    switch ($type)
                    {
                        case 'foldersedit':
                        $assetName = 'com_biblestudy.foldersedit.'.(int)$Itemid;
                        break;
                        
                        case 'commentsedit':
                        $assetName = 'com_biblestudy.commentsedit.'.(int)$Itemid;
                        break;
                        
                        case 'cssedit':
                        $assetName = 'com_biblestudy.cssedit.'.(int)$Itemid;
                        break;
                        
                        case 'locationsedit':
                        $assetName = 'com_biblestudy.locationsedit.'.(int)$Itemid;
                        break;
                        
                        case 'mediaedit':
                        $assetName = 'com_biblestudy.mediaedit.'.(int)$Itemid;
                        break;
                        
                        case 'mediafilesedit':
                        $assetName = 'com_biblestudy.mediafilesedit.'.(int)$Itemid;
                        break;
                        
                        case 'messagetypeedit':
                        $assetName = 'com_biblestudy.messagetypeedit.'.(int)$Itemid;
                        break;
                        
                        case 'mimetypeedit':
                        $assetName = 'com_biblestudy.mimetypeedit.'.(int)$Itemid;
                        break;
                        
                        case 'podcastedit':
                        $assetName = 'com_biblestudy.podcastedit.'.(int)$Itemid;
                        break;
                        
                        case 'seriesedit':
                        $assetName = 'com_biblestudy.seriesedit.'.(int)$Itemid;
                        break;
                        
                        case 'serversedit':
                        $assetName = 'com_biblestudy.serversedit.'.(int)$Itemid;
                        break;
                        
                        case 'shareedit':
                        $assetName = 'com_biblestudy.shareedit.'.(int)$Itemid;
                        break;
                        
                        case 'studiesedit':
                        $assetName = 'com_biblestudy.studiesedit.'.(int)$Itemid;
                        break;
                        
                        case 'teacheredit':
                        $assetName = 'com_biblestudy.teacheredit.'.(int)$Itemid;
                        break;
                        
                        case 'templateedit':
                        $assetName = 'com_biblestudy.templateedit.'.(int)$Itemid;
                        break;
                        
                        case 'topicsedit':
                        $assetName = 'com_biblestudy.topicsedit.'.(int)$Itemid;
                        break;
                        
                        case 'message':
                        $assetName = 'com_biblestudy.message.'.(int)$Itemid;
                        break;
                        
                        case 'mediafile':
                        $assetName = 'com_biblestudy.mediafile.'.(int)$Itemid;
                        break;
                        
                        default:
                        $assetName = 'com_biblestudy.studiesedit.'.(int)$Itemid;
                        break;
                    }
                    
                }
 
                $actions = array(
                        'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
                );
 
                foreach ($actions as $action) {
                        $result->set($action,        $user->authorise($action, $assetName));
                }
 
                return $result;
        }
 
  /**
     * Configure the Linkbar.
     *
     * @param	string	The name of the active view.
     * @since	1.6
     */
    public static function addSubmenu($vName = 'biblestudy') {
        JSubMenuHelper::addEntry(
                        JText::_('JBS_CPL_CONTROL_PANEL'),
                        'index.php?option=com_biblestudy&view=cpanel',
                        $vName == 'Control Panel'
        );
        JSubMenuHelper::addEntry(
                        JText::_('JBS_CMN_STUDIES'),
                        'index.php?option=com_biblestudy&view=studieslist',
                        $vName == 'Studies'
        );
        JSubMenuHelper::addEntry(
                        JText::_('JBS_CMN_MEDIA_FILES'),
                        'index.php?option=com_biblestudy&view=mediafileslist',
                        $vName == 'Media Files'
        );
        JSubMenuHelper::addEntry(
                        JText::_('JBS_CMN_TEMPLATES'),
                        'index.php?option=com_biblestudy&view=templateslist',
                        $vName == 'Templates'
        );
        JSubMenuHelper::addEntry(
                        JText::_('JBS_CMN_TEACHERS'),
                        'index.php?option=com_biblestudy&view=teacherlist',
                        $vName == 'Teachers'
        );
        JSubMenuHelper::addEntry(
                        JText::_('JBS_CMN_MEDIAIMAGES'),
                        'index.php?option=com_biblestudy&view=medialist',
                        $vName == 'Media Images'
        );
        JSubMenuHelper::addEntry(
                        JText::_('JBS_CMN_SERIES'),
                        'index.php?option=com_biblestudy&view=serieslist',
                        $vName == 'Series List'
        );
        JSubMenuHelper::addEntry(
                        JText::_('JBS_CMN_SERVERS'),
                        'index.php?option=com_biblestudy&view=serverslist',
                        $vName == 'Servers'
        );
        JSubMenuHelper::addEntry(
                        JText::_('JBS_FLD_FOLDERS'),
                        'index.php?option=com_biblestudy&view=folderslist',
                        $vName == 'Folders'
        );
        JSubMenuHelper::addEntry(
                        JText::_('JBS_CMN_MESSAGE_TYPES'),
                        'index.php?option=com_biblestudy&view=messagetypelist',
                        $vName == 'Message Types'
        );
        JSubMenuHelper::addEntry(
                        JText::_('JBS_CMN_LOCATIONS'),
                        'index.php?option=com_biblestudy&view=locationslist',
                        $vName == 'Locations'
        );
        JSubMenuHelper::addEntry(
                        JText::_('JBS_CMN_TOPICS'),
                        'index.php?option=com_biblestudy&view=topicslist',
                        $vName == 'Topics'
        );
        JSubMenuHelper::addEntry(
                        JText::_('JBS_CMN_PODCASTS'),
                        'index.php?option=com_biblestudy&view=podcastlist',
                        $vName == 'Podcasts'
        );
        JSubMenuHelper::addEntry(
                        JText::_('JBS_CMN_MIME_TYPES'),
                        'index.php?option=com_biblestudy&view=mimetypelist',
                        $vName == 'Mime Types'
        );
        JSubMenuHelper::addEntry(
                        JText::_('JBS_CMN_COMMENTS'),
                        'index.php?option=com_biblestudy&view=commentslist',
                        $vName == 'Study Comments'
        );
        JSubMenuHelper::addEntry(
                        JText::_('JBS_CMN_SOCIAL_NETWORKING_LINKS'),
                        'index.php?option=com_biblestudy&view=sharelist',
                        $vName == 'Social Media'
        );
        JSubMenuHelper::addEntry(
                        JText::_('JBS_CMN_ADMINISTRATION'),
                        'index.php?option=com_biblestudy&task=admin.edit&id=1',
                        $vName == 'Administration'
        );
        JSubMenuHelper::addEntry(
                        JText::_('JBS_CSS_CSS_EDIT'),
                        'index.php?option=com_biblestudy&view=cssedit',
                        $vName == 'Edit CSS'
        );
    }
 }

?>