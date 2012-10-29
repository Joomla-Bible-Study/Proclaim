<?php
/**
 * Default Custom
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;
JHTML::_('behavior.tooltip');
?>
<script type="text/javascript" language="JavaScript">
    function HideContent(d) {
        document.getElementById(d).style.display = "none";
    }
    function ShowContent(d) {
        document.getElementById(d).style.display = "block";
    }
    function ReverseDisplay(d) {
        if(document.getElementById(d).style.display == "none") { document.getElementById(d).style.display = "block"; }
        else { document.getElementById(d).style.display = "none"; }
    }
</script>
<?php
$mainframe = JFactory::getApplication();
$option = JRequest::getCmd('option');
JHTML::_('behavior.tooltip');
$params = $this->params;
$admin_params = $this->admin_params;
$document = JFactory::getDocument();
$document->addScript(JURI::base() . 'media' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'tooltip.js');

$row = $this->studydetails;
$listingcall = JView::loadHelper('listing');
?>
<div id="biblestudy" class="noRefTagger"> <!-- This div is the container for the whole page -->
    <?php
    $details = getStudyExp($row, $params, $admin_params, $this->template);
    echo $details;

    switch ($this->params->get('show_passage_view', '0')) {
        case 0:
            break;

        case 1:
            ?>
            <strong><a class="heading" href="javascript:ReverseDisplay('scripture')">>>
                    <?php echo JText::_('JBS_CMN_SHOW_HIDE_SCRIPTURE'); ?><<</a>
                <div id="scripture" style="display:none;"></strong>
            <?php
            $passage_call = JView::loadHelper('passage');
            $response = getPassage($params, $row);
            echo $response;
            ?>
        </div>
        <?php
        break;

    case 2:
        ?>
        <div id="scripture">
            <?php
            $passage_call = JView::loadHelper('passage');
            $response = getPassage($params, $row);
            echo $response;
            ?>
        </div>
        <?php
        break;
}
?>
</div><!--End of page container div-->