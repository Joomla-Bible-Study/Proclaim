<?php
/**
 * Teacher view subset main
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
use Joomla\CMS\Router\Route;
use CWM\Component\Proclaim\Site\Helper\CWMListing;
use Joomla\CMS\Html\HtmlHelper;
use Joomla\CMS\Language\Text;
use CWM\Component\Proclaim\Site\Helper\CWMTeacher;
defined('_JEXEC') or die;


$listing = new CWMListing;
$teacher = new CWMListing;
?>
<div class="container">

	<?php
	$teacherdisplay = $teacher->getFluidListing($this->item, $this->params, $this->template, $type = 'teacher');
	echo $teacherdisplay;

	?>
	<?php
	if ($this->params->get('show_teacher_studies') > 0)
	{
		?>
		<div class="row">
			<div class="span12">
				<?php $teacherstudies = $listing->getFluidListing($this->teacherstudies, $this->params, $this->state->template, $type = 'sermons');
				echo $teacherstudies; ?>
			</div>
		</div>
	<?php
	}
	?>
	<hr/>

	<div class="row">
		<div class="span12">
			<a href="<?php echo Route::_('index.php?option=com_proclaim&view=CWMTeachers&t=' . $this->template->id) ?>">
				<button class="btn"><?php echo '&lt;-- ' . Text::_('JBS_TCH_RETURN_TEACHER_LIST'); ?></button>
			</a>
			<?php
			if ($this->params->get('teacherlink', '1') > 0)
			{
				echo '<a href="' .
					Route::_('index.php?option=com_proclaim&view=CWMSermons&filter_teacher=' . (int) $this->item->id . '&t=' . (int) $this->template->id
					) .
					'"><button class="btn">' . Text::_('JBS_TCH_MORE_FROM_THIS_TEACHER') . ' --></button></a>';
			}
			?>
		</div>
	</div>

</div>
