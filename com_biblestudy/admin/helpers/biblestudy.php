<?php

/**
 * @author Tom Fuller
 * @copyright 2011
 */
defined('_JEXEC') or die('Restriced Access');

class BibleStudyHelper
{
    public static $extension = 'com_biblestudy';
public static function getActions($folderId = 0, $studyId = 0, $mediafileeditId = 0, $templateId = 0, $teacherId = 0, $mediaId = 0, $seriesId = 0, $serverId = 0, $locationId = 0, $topicId = 0, $podcastId = 0, $mimetypeId = 0, $commentId = 0, $shareId = 0, $messagetypeId = 0, $messageId = 0, $mediafileId = 0, $cssId = 0, $adminId = 0)
        {
                $user  = JFactory::getUser();
                $result        = new JObject;
 
                if (empty($folderId)) {
                        $assetName = 'com_biblestudy';
                }
                else {
                    $assetName = 'com_biblestudy.foldersedit.'.(int)$Itemid;
                }
 
                $actions = array(
                        'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.delete'
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