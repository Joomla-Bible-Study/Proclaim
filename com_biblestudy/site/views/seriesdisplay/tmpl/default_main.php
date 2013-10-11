<?php
/**
 * Default Main
 *
 * @package    BibleStudy.Site
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;
?>
<!-- Begin Fluid layout -->
<div class="container-fluid">
        <?php $listing = new JBSMListing;
        $list = $listing->getFluidListing($this->items, $this->params, $this->admin, $this->t, $type='seriesdisplay');
        echo $list;
        ?>

<!-- End Fluid Layout -->
<?php
		if ($this->params->get('series_list_return') > 0)
		{
			echo '<div class="row-fluid"><div class="span12"><a href="' . JRoute::_('index.php?option=com_biblestudy&view=seriesdisplays&t=' . $this->t) . '"><< '
				. JText::_('JBS_SER_RETURN_SERIES_LIST') . '</a> | <a href="'
				. JRoute::_('index.php?option=com_biblestudy&view=sermons&filter_series=' . $this->items->id . '&t=' . $this->t)
				. '">' . JText::_('JBS_CMN_SHOW_ALL') . ' ' . JText::_('JBS_SER_STUDIES_FROM_THIS_SERIES')
				. ' >></a></div></div>';
		}
?>


</div>