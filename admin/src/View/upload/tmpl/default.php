<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No direct access
defined('_JEXEC') or die();

?>
<div id="mediamu_wrapper">
	<div id="uploader_content">
		<?php echo $this->loadTemplate('uploader'); ?>
	</div>

	<div id="filebroswer_content">
		<?php echo $this->loadTemplate('navigator'); ?>
	</div>
</div>

