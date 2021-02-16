<?php
/**
 * Default
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');

/** @var $this BiblestudyViewLandingpage */

$JBSMLanding = new JBSMLanding;
/** @var Joomla\Registry\Registry $params */
$params = $this->params;
?>

<div id="biblestudy_landing" class="noRefTagger"> <!-- This div is the container for the whole page -->
	<div id="bsms_header">
		<h1 class="componentheading">
			<?php
			if ($this->params->get('landing_show_page_image') > 0)
			{
				if (isset($this->main->path))
				{
					?>
					<img src="<?php echo JUri::base() . $this->main->path; ?>"
					     alt="<?php echo $this->params->get('landing_page_title'); ?>"
                         width="<?php echo $this->main->width; ?>"
					     height="<?php echo $this->main->height; ?>"/>
					<?php
					// End of column for logo
				}
			}

			if ($this->params->get('landing_show_page_title') > 0)
			{
				echo $this->params->get('landing_page_title');
			}
			?>
		</h1>
		<?php
		if ($this->params->get('landing_intro_show') > 0)
		{
			echo $this->params->get('landing_intro');
		}
		?>
	</div>
	<!-- End div id="bsms_header" -->

	<?php
	$i = 1;

	for ($i = 1; $i <= 7; $i++)
	{
		$showIt = $params->get('headingorder_' . $i);

		if ((int) $params->get('show' . $showIt) === 1)
		{
			$heading_call  = null;
			$heading       = null;
			$showIt_phrase = null;

			switch ($showIt)
			{
				case 'teachers':
					$heading       = $JBSMLanding->getTeacherLandingPage($params, $id = 0);
					$showIt_phrase = JText::_('JBS_CMN_TEACHERS');
					$showhideall   = $this->getShowHide($showIt, $showIt_phrase, $i);
					break;

				case 'series':
					$heading       = $JBSMLanding->getSeriesLandingPage($params, $id = 0);
					$showIt_phrase = JText::_('JBS_CMN_SERIES');
					$showhideall   = $this->getShowHide($showIt, $showIt_phrase, $i);
					break;

				case 'locations':
					$heading       = $JBSMLanding->getLocationsLandingPage($params, $id = 0);
					$showIt_phrase = JText::_('JBS_CMN_LOCATIONS');
					$showhideall   = $this->getShowHide($showIt, $showIt_phrase, $i);
					break;

				case 'messagetypes':
					$heading       = $JBSMLanding->getMessageTypesLandingPage($params, $id = 0);
					$showIt_phrase = JText::_('JBS_CMN_MESSAGETYPES');
					$showhideall   = $this->getShowHide($showIt, $showIt_phrase, $i);
					break;

				case 'topics':
					$heading       = $JBSMLanding->getTopicsLandingPage($params, $id = 0);
					$showIt_phrase = JText::_('JBS_CMN_TOPICS');
					$showhideall   = $this->getShowHide($showIt, $showIt_phrase, $i);
					break;

				case 'books':
					$heading       = $JBSMLanding->getBooksLandingPage($params, $id = 0);
					$showIt_phrase = JText::_('JBS_CMN_BOOKS');
					$showhideall   = $this->getShowHide($showIt, $showIt_phrase, $i);
					break;

				case 'years':
					$heading       = $JBSMLanding->getYearsLandingPage($params, $id = 0);
					$showIt_phrase = JText::_('JBS_CMN_YEARS');
					$showhideall   = $this->getShowHide($showIt, $showIt_phrase, $i);
					break;
			}
			?>
			<!-- Wrap each in a DIV... -->
			<div class="landing_item">
				<div class="landing_title">
					<?php
					echo $params->get($showIt . 'label');
					echo "\n";
					?>
				</div>
				<!-- end div id="landing_title" -->
				<div class="landinglist">
					<?php
					if (isset($showhideall))
					{
						echo $showhideall;
					}

					if (isset($heading))
					{
						echo $heading;
					}
					?>
				</div>
				<!-- end div class="landinglist" -->
			</div><!-- end div class="landing_item" -->
		<?php
		}
	} // End Loop for the landing items
	?>
</div><!-- end div id="biblestudy_landing" -->
