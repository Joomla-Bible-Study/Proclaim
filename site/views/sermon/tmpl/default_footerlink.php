<?php
/**
 * Default FooterLink
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
// No Direct Access
defined('_JEXEC') or die;
?>
<div class="listingfooter">
	<?php
	$input = Factory::getApplication();
	$link_text = $this->item->params->get('link_text');

	if (!$link_text)
	{
		$link_text = Text::_('JBS_STY_RETURN_STUDIES_LIST');
	}

	if ($this->item->params->get('view_link') > 0)
	{
		$t = $this->item->params->get('studieslisttemplateid');

		if (!$t)
		{
			$t = $input->get('t', 1, 'int');
		}
		if (!isset($returnmenu))
		{
			$returnmenu = 1;
		}
		$Itemid = $input->get('Itemid', '', 'int');

		if (!$Itemid)
		{
			$link = Route::_('index.php?option=com_proclaim&view=sermons&t=' . $t);
		}
		else
		{
			$link = Route::_('index.php?option=com_proclaim&view=sermons&t=' . $t);
		}
		?>
		<a href="<?php echo $link; ?>"> <?php echo $link_text; ?> </a> <?php } // End of if view_link not 0 ?>
</div><!--end of footer div-->

