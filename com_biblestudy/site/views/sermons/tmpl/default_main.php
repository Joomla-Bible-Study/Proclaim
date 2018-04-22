<?php
/**
 * Default for sermons
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2018 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

JHtml::_('bootstrap.framework');
JHtml::_('bootstrap.tooltip');
JHtml::_('formbehavior.chosen', 'select');

$JBSMTeacher  = new JBSMTeacher;
$teachers     = $JBSMTeacher->getTeachersFluid($this->params);
$listing      = new JBSMListing;
$classelement = $listing->createelement($this->params->get('studies_element'));
?>

<div class="container-fluid">
    <div id="bsheader">
		<?php
		if ($this->params->get('showpodcastsubscribelist') == 1)
		{
			echo $this->subscribe;
		}
		?>
    </div>
	<?php if ($this->params->get('intro_show') > 0)
	{
		?>
        <div class="hero-unit" style="padding-top:30px; padding-bottom:20px;">
			<?php
			if ($this->params->get('listteachers') && $this->params->get('list_teacher_show') > 0)
			{
				?>
                <div class="row-fluid">
                    <ul class="thumbnails">
						<?php $spans = 12 / count($teachers);
						foreach ($teachers as $teacher)
						{
							echo '<li class="span' . $spans . '">';
							if ($this->params->get('teacherlink') > 0)
							{
								echo '<a href="index.php?option=com_biblestudy&view=teacher&id=' . $teacher['id'] . '&t=' . $teacher['t'] . '" >
							<img class="img-polaroid" src="' . JUri::base() . $teacher['image'] . '"></a>';
							}
							else
							{
								echo '<img class="img-polaroid" src="' . JUri::base() . $teacher['image'] . '">';
							}
							if ($this->params->get('teacherlink') > 0)
							{
								echo '<div class="caption"><p><a href="index.php?option=com_biblestudy&view=teacher&id=' .
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
			<?php } ?>
            <div class="row-fluid">
                <div class="span12">
					<?php if ($this->params->get('show_page_image') > 0)
					{
						?>
                        <img class="imgcenter" src="<?php echo JUri::base() . $this->main->path; ?>" alt="">
						<?php
					}
					if ($this->params->get('show_page_title') == 1)
					{ ?>
                    <<?php echo $classelement; ?> style="<?php echo $this->params->get('list_title_align'); ?>
                    ">
					<?php echo $this->params->get('list_page_title'); ?>
                </<?php echo $classelement; ?>>
				<?php
				}
				if ($this->params->get('list_intro'))
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

<div class="container-fluid">
	<?php
	// Search tools bar
	echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
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
		<?php if (($this->params->def('show_pagination', 2) == 1
				|| ($this->params->get('show_pagination') == 2))
			&& ($this->pagination->pagesTotal > 1)) : ?>
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
	if ($this->params->get('showpodcastsubscribelist') == 2)
	{
		echo $this->subscribe;
	}
	?>
</div>
