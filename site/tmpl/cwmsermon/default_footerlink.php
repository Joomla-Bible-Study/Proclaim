<?php
/**
 * Default FooterLink
 *
 * @package    Proclaim.Site
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Input\Input;

?>
<div class="listingfooter">
	<?php
	$input     = new Input;
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

		$link = Route::_('index.php?option=com_biblestudy&view=cwmsermons&t=' . $t);
		?>
		<a href="<?php echo $link; ?>"> <?php echo $link_text; ?> </a> <?php } // End of if view_link not 0 ?>
</div><!--end of footer div-->

