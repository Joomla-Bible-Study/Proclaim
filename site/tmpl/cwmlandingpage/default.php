<?php
/**
 * Default
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

use CWM\Component\Proclaim\Site\Helper\CWMLanding;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

// Load CSS for view.
HTMLHelper::_('proclaim.loadcss', $this->params);

$CWMLanding = new CWMLanding;
$params = $this->params;
?>

<div id="biblestudy_landing" class="container"> <!-- This div is the container for the whole page -->
	<div id="bsms_header">
		<h1 class="componentheading">
			<?php
			if ($this->params->get('landing_show_page_image') > 0)
			{
				if (isset($this->main->path))
				{
					?>
					<img src="<?php echo Uri::base() . $this->main->path; ?>"
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
					$heading       = $CWMLanding->getTeacherLandingPage($params, $id = 0);
					$showIt_phrase = Text::_('JBS_CMN_TEACHERS');
					$showhideall   = $this->getShowHide($showIt, $showIt_phrase, $i);
					break;

				case 'series':
					$heading       = $CWMLanding->getSeriesLandingPage($params, $id = 0);
					$showIt_phrase = Text::_('JBS_CMN_SERIES');
					$showhideall   = $this->getShowHide($showIt, $showIt_phrase, $i);
					break;

				case 'locations':
					$heading       = $CWMLanding->getLocationsLandingPage($params, $id = 0);
					$showIt_phrase = Text::_('JBS_CMN_LOCATIONS');
					$showhideall   = $this->getShowHide($showIt, $showIt_phrase, $i);
					break;

				case 'messagetypes':
					$heading       = $CWMLanding->getMessageTypesLandingPage($params, $id = 0);
					$showIt_phrase = Text::_('JBS_CMN_MESSAGETYPES');
					$showhideall   = $this->getShowHide($showIt, $showIt_phrase, $i);
					break;

				case 'topics':
					$heading       = $CWMLanding->getTopicsLandingPage($params, $id = 0);
					$showIt_phrase = Text::_('JBS_CMN_TOPICS');
					$showhideall   = $this->getShowHide($showIt, $showIt_phrase, $i);
					break;

				case 'books':
					$heading       = $CWMLanding->getBooksLandingPage($params, $id = 0);
					$showIt_phrase = Text::_('JBS_CMN_BOOKS');
					$showhideall   = $this->getShowHide($showIt, $showIt_phrase, $i);
					break;

				case 'years':
					$heading       = $CWMLanding->getYearsLandingPage($params, $id = 0);
					$showIt_phrase = Text::_('JBS_CMN_YEARS');
					$showhideall   = $this->getShowHide($showIt, $showIt_phrase, $i);
					break;
			}
			?>
			<!-- Wrap each in a DIV... -->
			<div class="landing_item ">
				<div class="landing_title">
					<?php
					echo $params->get($showIt . 'label');
					echo "\n";
					?>
				</div>
				<!-- end div id="landing_title" -->
				<div class="landinglist" style="display: inline-block;">
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
