<?php
/**
 * Mod_Biblestudy core file
 *
 * @package     Proclaim
 * @subpackage  Module.BibleStudy
 * @copyright   2007 - 2019 (C) CWM Team All rights reserved
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link        https://www.christianwebministries.org
 * */
defined('_JEXEC') or die;
$show_link = $params->get('show_link', 1);

JLoader::register('CWMHelper', BIBLESTUDY_PATH_ADMIN_HELPERS . 'helper.php');
JLoader::register('JBSMListing', BIBLESTUDY_PATH_LIB . '/CWMListing.php');
$JBSMListing = new JBSMListing;

// Load CSS framework for displaying properly.
JHtml::_('biblestudy.framework');
JHtml::_('biblestudy.loadCss', $params, null, 'font-awesome');
foreach ($list as $study)
{?>
	<div style="width:100%;">
		<div class="span3"><div style="padding:12px 8px;line-height:22px;height:200px;">
				<?php if ($study->study_thumbnail) {echo '<span style="max-width:250px; height:auto;">'.$study->study_thumbnail .'</span>'; echo '<br />';} ?>
				<strong><?php echo $study->studytitle;?></strong><br />
				<span style="color:#9b9b9b;"><?php echo $study->scripture1;?> | <?php echo $study->studydate;?></span><br />
				<div style="font-size:85%;margin-bottom:-17px;max-height:122px;overflow:hidden;"><?php echo $study->teachername;?></div><br /><div style="background: rgba(0, 0, 0, 0) linear-gradient(to bottom, rgba(255, 255, 255, 0) 0%, white 100%) repeat scroll 0 0;bottom: 0;height: 32px;margin-top: -32px; position: relative;width: 100%;"></div>
				<?php echo $study->media; ?>
			</div></div>


	</div>
<?php }?>
<div class="row-fluid">
	<div class="span12">
		<?php
		if ($params->get('show_link') > 0)
		{
			echo $link;
		}
		?>
	</div>
</div>
<!--end of footer div-->
<!--end container -->
<div style="clear: both;"></div>

