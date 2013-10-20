<?php
/**
 * Teacher view subset main
 *
 * @package    BibleStudy.Site
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;
JLoader::register('JBSMImages', BIBLESTUDY_PATH_LIB . '/biblestudy.images.class.php');
JLoader::register('JBSMTeacher', BIBLESTUDY_PATH_HELPERS . '/teacher.php');

$listing = new JBSMListing();


?>
<div class="container-fluid">

<?php
$list = $listing->getFluidListing($this->item, $this->params, $this->admin_params, $this->t, $type='teacher');
echo $list;
?>

    <div class="row-fluid">
        <div class="span12">
            <?php $teacherstudies = $listing->getFluidListing($this->studies, $this->params, $this->admin_params, $this->template, $type='sermons');
            echo $teacherstudies; ?>
        </div>
    </div>
    <hr />

    <div class="row-fluid">
        <div class="span12">
            <a href="index.php?option=com_biblestudy&amp;view=teachers&amp;t=<?php echo $this->t; ?>">
				<button class="btn"><?php echo '&lt;-- ' . JText::_('JBS_TCH_RETURN_TEACHER_LIST'); ?></button>
            </a>
			<?php
			if ($this->params->get('teacherlink', '1') > 0)
			{
				echo '<a href="index.php?option=com_biblestudy&amp;view=sermons&amp;filter_teacher=' . (int) $this->item->id
					. '&amp;t=' . $this->t . '"><button class="btn">' . JText::_('JBS_TCH_MORE_FROM_THIS_TEACHER') . ' --></button></a>';
			}
			?>
        </div>
    </div>

</div>
