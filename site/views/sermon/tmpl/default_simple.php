<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;
?>
<div style="width:100%;">
	<div class="span3"><div style="padding:12px 8px;line-height:22px;height:200px;">
			<?php if ($this->item->study_thumbnail) {echo '<span style="max-width:250px; height:auto;">'.$this->item->study_thumbnail .'</span>'; echo '<br />';} ?>
			<strong><?php echo $this->item->studytitle;?></strong><br />
			<span style="color:#9b9b9b;"><?php echo $this->item->scripture1;?> | <?php echo $this->item->studydate;?></span><br />
			<div style="font-size:85%;margin-bottom:-17px;max-height:122px;overflow:hidden;"><?php echo $this->item->teachername;?></div><br /><div style="background: rgba(0, 0, 0, 0) linear-gradient(to bottom, rgba(255, 255, 255, 0) 0%, white 100%) repeat scroll 0 0;bottom: 0;height: 32px;margin-top: -32px; position: relative;width: 100%;"></div>
			<?php echo $this->item->media; ?>
		</div></div>


</div>