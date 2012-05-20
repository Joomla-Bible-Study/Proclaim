<?php

/**
 * @author Tom Fuller
 * @copyright 2011
 * Footer Studydetails view
 * Since 7.0
 */

//No Direct Access
defined('_JEXEC') or die;;
?> <div> <?php
switch ($this->params->get('show_passage_view', '0'))
{
	case 0:
		break;

	case 1:
		?>
<strong><a class="heading" href="javascript:ReverseDisplay('scripture')">>>
<?php echo JText::_('JBS_CMN_SHOW_HIDE_SCRIPTURE');?><<</a>
</strong>	<div id="scripture" style="display: none;">



<?php

$passage_call = JView::loadHelper('passage');
$response = getPassage($this->params, $this->studydetails);
echo $response;
?> </div> <?php
break;

case 2:
	?><div id="scripture"> <?php
	$passage_call = JView::loadHelper('passage');
	$response = getPassage($this->params, $this->studydetails);
	echo $response;
	 ?> </div> <?php
	break;
}
?> </div>