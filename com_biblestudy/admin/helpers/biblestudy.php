<?php

/**
 * @author Tom Fuller
 * @copyright 2011
 */
defined('_JEXEC') or die('Restriced Access');
abstract class BibleStudyHelper
{
    
public static function getActions($Itemid = 0)
        {
                $user  = JFactory::getUser();
                $result        = new JObject;
 
                if (empty($Itemd)) {
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
 }

?>