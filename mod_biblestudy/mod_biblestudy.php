<?php

/**
 * @version $Id: mod_biblestudy.php 1 $
 * @package mod_biblestudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;
?>
<script type="text/javascript" src="media/com_biblestudy/js/tooltip.js"></script>

<?php

require(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'helper.php');
require_once ( JPATH_ROOT . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'joomla' . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . 'parameter.php' );
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.pagebuilder.class.php');
// Need for inline player
$document = JFactory::getDocument();
$document->addScript('http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js');
$params = new JParameter($params);
$templatemenuid = $params->get('t');
$template = modBiblestudyHelper::getTemplate($params);

$admin = modBiblestudyHelper::getAdmin();
$admin_params = new JParameter($admin[0]->params);
$items = modBiblestudyHelper::getLatest($params);

//attempt to change mysql for error in large select
$db = JFactory::getDBO();
$db->setQuery('SET SQL_BIG_SELECTS=1');
$db->query();

//check permissions for this view by running through the records and removing those the user doesn't have permission to see
$user = JFactory::getUser();
$groups = $user->getAuthorisedViewLevels();
$count = count($items);

for ($i = 0; $i < $count; $i++) {

    if ($items[$i]->access > 1) {
        if (!in_array($items[$i]->access, $groups)) {
            unset($items[$i]);
        }
    }
}
foreach ($items AS $item) {
    $item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id . ':' . str_replace(' ', '-', htmlspecialchars_decode($item->studytitle, ENT_QUOTES));
}

//Build the elements so they can be accessed through the $this->page array in the template
        $dispatcher = JDispatcher::getInstance();
        $linkit = $params->get('show_scripture_link','0'); 
            switch ($linkit) {
                case 0:
                    break;
                case 1:
                    JPluginHelper::importPlugin('content');
                    break;
                case 2:
                    JPluginHelper::importPlugin('content', 'scripturelinks');
                    break;
            }
            $limitstart = JRequest::getVar('limitstart', 'int');
            
$studies = $items;
   /*     $pagebuilder = new JBSPagebuilder();
        foreach ($studies as $i=>$study)
            {
                $pelements = $pagebuilder->buildPage($study, $params, $admin_params);
                $studies[$i]->scripture1 = $pelements->scripture1;
                $studies[$i]->scripture2 = $pelements->scripture2;
                $article->text = $studies[$i]->scripture1;
                $results = $dispatcher->trigger('onContentPrepare', array('com_biblestudy.sermons',& $article, & $params, $limitstart));
                $studies[$i]->scripture1 = $article->text; 
                $article->text = $studies[$i]->scripture2;
                $results = $dispatcher->trigger('onContentPrepare', array('com_biblestudy.sermons',& $article, & $params, $limitstart));
                $studies[$i]->scripture2 = $article->text;
                $studies[$i]->media = $pelements->media;
                $studies[$i]->duration = $pelements->duration;
                $studies[$i]->studydate = $pelements->studydate;
                $studies[$i]->topics = $pelements->topics;
                $studies[$i]->study_thumbnail = $pelements->study_thumbnail;
                $studies[$i]->series_thumbnail = $pelements->series_thumbnail;
                $studies[$i]->detailslink = $pelements->detailslink;
                $article->text = $studies[$i]->studyintro;
                $results = $dispatcher->trigger('onContentPrepare', array('com_biblestudy.sermons',& $article, & $params, $limitstart));
                $studies[$i]->studyintro = $article->text;
                $article->text = $studies[$i]->secondary_reference;
                $results = $dispatcher->trigger('onContentPrepare', array('com_biblestudy.sermons',& $article, & $params, $limitstart));
                $studies[$i]->secondary_reference = $article->text;
            }
        
*/

$language = JFactory::getLanguage();
$language->load('com_biblestudy');

$document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/biblestudy.css');
$url = $params->get('stylesheet');

if ($url) {
    $document->addStyleSheet($url);
}
$config = JComponentHelper::getParams('com_biblestudy');
//we need to load the path to the helper files
$path1 = JPATH_BASE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy/helpers/';
$url = $params->get('stylesheet');
if ($url) {
    $document->addStyleSheet($url);
}
$pageclass_sfx = $params->get('pageclass_sfx');
if ($params->get('useexpert_module') > 0) {
    $layout = 'default_custom';
} 
elseif ($params->get('moduletemplate'))
{
    $layout = $params->get('moduletemplate');
}
else {
    $layout = 'default_main';
}
require(JModuleHelper::getLayoutPath('mod_biblestudy', $layout));