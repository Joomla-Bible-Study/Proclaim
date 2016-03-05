<?php
/**
 * Default
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

JHTML::_('behavior.tooltip');

$JBSMLanding = new JBSMLanding;
/** @var Joomla\Registry\Registry $params */
$params = $this->params;
?>

<div id="biblestudy_landing" class="noRefTagger"> <!-- This div is the container for the whole page -->
	<div id="bsms_header">
		<h1 class="componentheading">
			<?php
			if ($this->params->get('show_page_image') > 0)
			{
				if (isset($this->main->path))
				{
					?>
					<img src="<?php echo JURI::base() . $this->main->path; ?>"
					     alt="<?php echo $this->params->get('page_title'); ?>" width="<?php echo $this->main->width; ?>"
					     height="<?php echo $this->main->height; ?>"/>
					<?php
					// End of column for logo
				}
			}
			if ($this->params->get('show_page_title') > 0)
			{
				echo $this->params->get('page_title');
			}
			?>
		</h1>
	</div>
	<!-- End div id="bsms_header" -->

	<?php
	$i = 1;

	for ($i = 1; $i <= 7; $i++)
	{
		$showIt = $params->get('headingorder_' . $i);

		if ($params->get('show' . $showIt) == 1)
		{
			$heading_call  = null;
			$heading       = null;
			$showIt_phrase = null;

			switch ($showIt)
			{

				case 'teachers':
					$heading       = $JBSMLanding->getTeacherLandingPage($params, $id = 0);
					$showIt_phrase = JText::_('JBS_CMN_TEACHERS');
					break;

				case 'series':
					$heading       = $JBSMLanding->getSeriesLandingPage($params, $id = 0);
					$showIt_phrase = JText::_('JBS_CMN_SERIES');
					break;

				case 'locations':
					$heading       = $JBSMLanding->getLocationsLandingPage($params, $id = 0);
					$showIt_phrase = JText::_('JBS_CMN_LOCATIONS');
					break;

				case 'messagetypes':
					$heading       = $JBSMLanding->getMessageTypesLandingPage($params, $id = 0);
					$showIt_phrase = JText::_('JBS_CMN_MESSAGETYPES');
					break;

				case 'topics':
					$heading       = $JBSMLanding->getTopicsLandingPage($params, $id = 0);
					$showIt_phrase = JText::_('JBS_CMN_TOPICS');
					break;

				case 'books':
					$heading       = $JBSMLanding->getBooksLandingPage($params, $id = 0);
					$showIt_phrase = JText::_('JBS_CMN_BOOKS');
					break;

				case 'years':
					$heading       = $JBSMLanding->getYearsLandingPage($params, $id = 0);
					$showIt_phrase = JText::_('JBS_CMN_YEARS');
					break;
			}
			// End Switch

			if ($params->get('landing' . $showIt . 'limit'))
			{
				$images       = new JBSMImages;
				$showhide_tmp = $images->getShowHide();

				$showhideall = "<div id='showhide" . $i . "'>";

				$buttonlink = "\n\t" . '<a class="showhideheadingbutton" href="javascript:ReverseDisplay2(' . "'showhide" . $showIt . "'" . ')">';
				$labellink  = "\n\t" . '<a class="showhideheadinglabel" href="javascript:ReverseDisplay2(' . "'showhide" . $showIt . "'" . ')">';

				switch ($params->get('landing_hide', 0))
				{
					case 0: // Image only
						$showhideall .= $buttonlink;
						$showhideall .= "\n\t\t" . '<img src="' . JURI::base() . $showhide_tmp->path . '" alt="' . JText::_('JBS_CMN_SHOW_HIDE_ALL');
						$showhideall .= ' ' . $showIt_phrase . '" title="' . JText::_('JBS_CMN_SHOW_HIDE_ALL') . ' ' . $showIt_phrase . '" border="0" width="';
						$showhideall .= $showhide_tmp->width . '" height="' . $showhide_tmp->height . '" />';

						// Spacer
						$showhideall .= ' ';
						$showhideall .= "\n\t" . '</a>';
						break;

					case 1: // Image and label
						$showhideall .= $buttonlink;
						$showhideall .= "\n\t\t" . '<img src="' . JURI::base() . $showhide_tmp->path . '" alt="' . JText::_('JBS_CMN_SHOW_HIDE_ALL');
						$showhideall .= ' ' . $showIt_phrase . '" title="' . JText::_('JBS_CMN_SHOW_HIDE_ALL') . ' ' . $showIt_phrase . '" border="0" width="';
						$showhideall .= $showhide_tmp->width . '" height="' . $showhide_tmp->height . '" />';

						// Spacer
						$showhideall .= ' ';
						$showhideall .= "\n\t" . '</a>';
						$showhideall .= $labellink;
						$showhideall .= "\n\t\t" . '<span id="landing_label">' . $params->get('landing_hidelabel') . '</span>';
						$showhideall .= "\n\t" . '</a>';
						break;

					case 2: // Label only
						$showhideall .= $labellink;
						$showhideall .= "\n\t\t" . '<span id="landing_label">' . $params->get('landing_hidelabel') . '</span>';
						$showhideall .= "\n\t" . '</a>';
						break;
				}

				$showhideall .= "\n" . '      </div> <!-- end div id="showhide" for ' . $i . ' -->' . "\n";
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
