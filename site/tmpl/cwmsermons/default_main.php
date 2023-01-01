<?php
/**
 * Default for sermons
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Site\Helper\CWMListing;
use CWM\Component\Proclaim\Site\Helper\CWMTeacher;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$CWMTeacher   = new CWMTeacher;
$teachers     = $CWMTeacher->getTeachersFluid($this->params);
$listing      = new CWMListing;
$classelement = $listing->createelement($this->params->get('studies_element'));
$app          = Factory::getApplication();
$itemid       = $app->input->get('Itemid');

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
			<div class="hero-unit" style="padding-top:30px; padding-bottom:20px;">
				<div class="row">

					<?php
					foreach ($teachers as $teacher)
					{
						echo '<div class="col">';
						if ($this->params->get('teacherlink') > 0)
						{
							echo '<a href="' . Route::_('index.php?option=com_proclaim&view=cwmteacher&id=' . $teacher['id'] . '&t=' . $teacher['t'] . '&Itemid=' . $itemid) . '" >
							<img class="img-polaroid" src="' . Uri::base() . $teacher['image'] . '" alt="Teachers Image"></a>';
						}
						else
						{
							echo '<img class="img-polaroid" src="' . Uri::base() . $teacher['image'] . '">';
						}
						if ($this->params->get('teacherlink') > 0)
						{
							echo '<div class="caption"><p><a href="' . Route::_('index.php?option=com_proclaim&view=cwmteacher&id=' .
									$teacher['id'] . '&t=' . $teacher['t']) . '">' . $teacher['name'] . '</a></p></div>';
						}
						else
						{
							echo '<div class="caption"><p>' . $teacher['name'] . '</p></div>';
						}
						echo '</div>';
					}
					?>

				</div>
			</div>
		<?php } ?>
		<?php if ($this->params->get('show_page_image == 1') || $this->params->get('show_page_title') > 0){ ?>
            <div class="col" style="display: inline-flex; align-items: center;">

                <?php if ($this->params->get('show_page_image') > 0)
                {
                    if ($this->params->get('main_image_icon_or_image') == 1)
                    {echo '<div class="col" style="display: flex;>' . $this->mainimage . '</div>';}
                    else {echo '<i class="fas fa-bible fa-3x" style="display: flex; margin-right: 10px;"></i>';}
                }
                if ($this->params->get('show_page_title') > 0)
                {
                    echo '<h2 style="display: flex; list-style: none;"> ' . $this->params->get('list_page_title') . '</h2>';
                }
                ?>
            </div>
        <?php } ?>
		<div class="row">
			<div class="col-12">
				<?php

				if (!empty($this->params->get('list_intro')))
				{
					?>
					<div style="display: block;">
						<?php echo $this->params->get('list_intro'); ?>
					</div>
				<?php } ?>

			</div>
		</div>
	<?php } ?>
</div>

<div class="container">
    <div class="row">
        <div class="col1-12">
	<?php
	// Search tools bar
	echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
	?>

            <?php // Add pagination links ?>
            <?php if (!empty($this->items)) : ?>
                <?php if (($this->pagination->pagesTotal > 1) &&
                    ($this->params->def('show_pagination', 1) === '1' || ($this->params->get('show_pagination') === '1'))) : ?>
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
	if ($this->items)
	{
		echo $listing->getFluidListing($this->items, $this->params, $this->template, $type = 'sermons');
	}
	?>
	<?php // Add pagination links ?>
	<?php if (!empty($this->items)) : ?>
		<?php if (($this->pagination->pagesTotal > 1) &&
			($this->params->def('show_pagination', 2) === '2' || ($this->params->get('show_pagination') === '2'))) : ?>
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
    </div>
</div>
