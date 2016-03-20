<?php
/**
 * Default Custom
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

$mainframe = JFactory::getApplication();

$pathway = $mainframe->getPathway();
$uri = new JUri;
$database = JFactory::getDbo();
$teacher = $this->teacher;
$params = $this->params;
$image = new stdClass;
$JBSMTeacher = new JBSMTeacher;
$JBSMImage = new JBSMImage;
$input = new JInput;
$t = $input->get('t', 1, 'int');

if (!$t)
{
	$t = 1;
}
$t = $this->params->get('teachertemplateid');

if (!$t)
{
	$t = $input->get('t', 1, 'int');
}
$studieslisttemplateid = $this->params->get('studieslisttemplateid');

if (!$studieslisttemplateid)
{
	$studieslisttemplateid = $input->get('t', 1, 'int');
}
if (!$teacher->teacher_image)
{
	$image->path   = $teacher->image;
	$image->height = $teacher->imageh;
	$image->width  = $teacher->imagew;
}
else
{
	if ($teacher->teacher_image)
	{
		$i_path = 'images' . $teacher->teacher_image;
	}
	$image = $JBSMImage->getImage($i_path);
}
?>
<div id="biblestudy" class="noRefTagger">
	<?php
	if (!$teacher->teacher_image)
	{
		$image->path   = $teacher->image;
		$image->height = $teacher->imageh;
		$image->width  = $teacher->imagew;
	}
	else
	{
		if ($teacher->teacher_image)
		{
			$i_path = 'images' . $teacher->teacher_image;
		}
		$image = $JBSMImage->getImage($i_path);
	}
	?>
	<table class="table table-striped bslisttable">
		<?php
		$listing = $JBSMTeacher->getTeacherDetailsExp($teacher, $params);
		echo $listing;

		if ($this->params->get('show_teacher_studies') > 0)
		{
			$studies = $JBSMTeacher->getTeacherStudiesExp($teacher->id, $params);
			echo $studies;
		}

		echo '<table class="table table-striped"><tr><td id="bsmsteacherstudyfooter">
		<a href="' . JRoute::_('index.php?option=com_biblestudy&view=sermons&filter_teacher=' . $teacher->id) . '">'
			. JText::_('JBS_TCH_MORE_FROM_THIS_TEACHER') . ' --></a></td><tr></table>';
		?>
		<tr>
			<td style="text-align: center;" colspan="0" class="bsm_teacherfooter">
				<a href="index.php?option=com_biblestudy&view=teacher<?php echo '&t=' . $t; ?>">
					<?php echo '<--' . JText::_('JBS_TCH_RETURN_TEACHER_LIST'); ?>
				</a>
	</table>
</div>
