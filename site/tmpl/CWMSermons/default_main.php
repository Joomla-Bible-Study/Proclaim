<?php
/**
 * Default for sermons
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

use CWM\Component\Proclaim\Site\Helper\CWMListing;
use Joomla\CMS\Layout\LayoutHelper;
use CWM\Component\Proclaim\Site\Helper\CWMTeacher;
use Joomla\CMS\Html\HTMLHelper;
use Joomla\CMS\Uri\Uri;

HtmlHelper::_('bootstrap.framework');
HtmlHelper::_('dropdown.init');
HtmlHelper::_('formbehavior.chosen', 'select');

$CWMTeacher  = new CWMTeacher;
$teachers     = $CWMTeacher->getTeachersFluid($this->params);
$listing      = new CWMListing;
$classelement = $listing->createelement($this->params->get('studies_element'));
?>

<div class="container">
	<div id="bsheader" class="row">
		<?php
		if ($this->params->get('showpodcastsubscribelist') === '1')
		{
			echo $this->subscribe;
		}
		?>
	</div>
	<?php if ($this->params->get('intro_show') > 0)
	{
		if ($this->params->get('listteachers') && $this->params->get('list_teacher_show') > 0)
		{
			?>
			<div class="hero-unit" style="padding-top:30px; padding-bottom:20px;">';
				<div class="row">
					<ul class="thumbnails">
						<?php $spans = 12 / count($teachers);
						foreach ($teachers as $teacher)
						{
							echo '<li class="span' . $spans . '">';
							if ($this->params->get('teacherlink') > 0)
							{
								echo '<a href="index.php?option=com_proclaim&view=CWMTeacher&id=' . $teacher['id'] . '&t=' . $teacher['t'] . '" >
							<img class="img-polaroid" src="' . Uri::base() . $teacher['image'] . '" alt="Teachers Image"></a>';
							}
							else
							{
								echo '<img class="img-polaroid" src="' . Uri::base() . $teacher['image'] . '">';
							}
							if ($this->params->get('teacherlink') > 0)
							{
								echo '<div class="caption"><p><a href="index.php?option=com_proclaim&view=CWMTeacher&id=' .
									$teacher['id'] . '&t=' . $teacher['t'] . '">' . $teacher['name'] . '</a></p></div>';
							}
							else
							{
								echo '<div class="caption"><p>' . $teacher['name'] . '</p></div>';
							}
							echo '</li>';
						}
						?>
					</ul>
				</div>
			</div>
		<?php } ?>
		<div class="row">
			<div class="col-12">
				<?php
				if (!empty($this->params->get('list_intro')))
				{
					?>
					<p>
						<?php echo $this->params->get('list_intro'); ?>
					</p>
				<?php } ?>

			</div>
		</div>
	<?php } ?>
</div>

<div class="container">
	<?php
	// Search tools bar
	//echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
	?>
	<hr/>
	<?php
	if ($this->items)
	{
		echo $listing->getFluidListing($this->items, $this->params, $this->template, $type = 'sermons');
	}
	?>
	<?php // Add pagination links ?>
	<?php if (!empty($this->items)) : ?>
		<?php if (($this->pagination->pagesTotal > 1) &&
			($this->params->def('show_pagination', 2) === '1' || ($this->params->get('show_pagination') === '2'))) : ?>
			<div class="pagination">
				<?php if ($this->params->def('show_pagination_results', 1)) : ?>
					<p class="counter pull-right">
						<?php echo $this->pagination->getPagesCounter(); ?>
					</p>
				<?php endif; ?>

				<?php echo $this->pagination->getPagesLinks(); ?>
			</div>
		<?php endif; ?>
	<?php endif; ?>
	<?php
	if ($this->params->get('showpodcastsubscribelist') === '2')
	{
		echo $this->subscribe;
	}
	?>
</div>
