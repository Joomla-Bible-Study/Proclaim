<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Landing page list view class
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class BiblestudyViewLandingpage extends JViewLegacy
{
	/** @var  string Request URL
	 *
	 * @since 7.0
	 */
	public $request_url;

	/**
	 * Params
	 *
	 * @var Registry
	 *
	 * @since 7.0
	 */
	public $params;

	/**
	 * Params
	 *
	 * @var Registry
	 *
	 * @since 7.0
	 */
	public $state;

	public $main;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since 7.0
	 */
	public function display($tpl = null)
	{
		$document = JFactory::getDocument();

		$this->state  = $this->get('state');
		$this->params = $this->state->template->params;

		$itemparams = JComponentHelper::getParams('com_biblestudy');

		// Prepare meta information (under development)
		if ($itemparams->get('metakey'))
		{
			$document->setMetaData('keywords', $itemparams->get('metakey'));
		}
		elseif (!$itemparams->get('metakey'))
		{
			$document->setMetaData('keywords', $this->params->get('metakey'));
		}

		if ($itemparams->get('metadesc'))
		{
			$document->setDescription($itemparams->get('metadesc'));
		}
		elseif (!$itemparams->get('metadesc'))
		{
			$document->setDescription($this->params->get('metadesc'));
		}

		JHtml::_('biblestudy.framework');

		JBSMImages::getShowHide();

		// Get the main study list image
		$this->main = JBSMImages::mainStudyImage($this->state->admin->params);

		$uri               = new JUri;
		$Uri_toString      = $uri->toString();
		$this->request_url = $Uri_toString;

		parent::display($tpl);
	}

	/**
	 * Parce through the Show hid buttons/links
	 *
	 * @param   string   $showIt         Name of Show
	 * @param   string   $showIt_phrase  Name of the
	 * @param   integer  $i              Number of Show
	 *
	 * @return string
	 *
	 * @since 9.2.4
	 */
	public function getShowHide($showIt, $showIt_phrase, $i)
	{

		// End Switch
		if ($this->params->get('landing' . $showIt . 'limit'))
		{
			$showhide_tmp = JBSMImages::getShowHide();

			$showhideall = "<div id='showhide" . $i . "'>";

			$buttonlink = "\n\t" . '<a class="showhideheadingbutton" href="javascript:ReverseDisplay2(' . "'showhide" . $showIt . "'" . ')">';
			$labellink  = "\n\t" . '<a class="showhideheadinglabel" href="javascript:ReverseDisplay2(' . "'showhide" . $showIt . "'" . ')">';

			switch ($this->params->get('landing_hide', 0))
			{
				case 0: // Image only
					$showhideall .= $buttonlink;
					$showhideall .= "\n\t\t" . '<img src="' . JUri::base() . $showhide_tmp->path . '" alt="' . JText::_('JBS_CMN_SHOW_HIDE_ALL');
					$showhideall .= ' ' . $showIt_phrase . '" title="' . JText::_('JBS_CMN_SHOW_HIDE_ALL') . ' ' .
						$showIt_phrase . '" border="0" width="';
					$showhideall .= $showhide_tmp->width . '" height="' . $showhide_tmp->height . '" />';

					// Spacer
					$showhideall .= ' ';
					$showhideall .= "\n\t" . '</a>';
					break;

				case 1: // Image and label
					$showhideall .= $buttonlink;
					$showhideall .= "\n\t\t" . '<img src="' . JUri::base() . $showhide_tmp->path . '" alt="' .
						JText::_('JBS_CMN_SHOW_HIDE_ALL');
					$showhideall .= ' ' . $showIt_phrase . '" title="' . JText::_('JBS_CMN_SHOW_HIDE_ALL') . ' ' .
						$showIt_phrase . '" border="0" width="';
					$showhideall .= $showhide_tmp->width . '" height="' . $showhide_tmp->height . '" />';

					// Spacer
					$showhideall .= ' ';
					$showhideall .= "\n\t" . '</a>';
					$showhideall .= $labellink;
					$showhideall .= "\n\t\t" . '<span id="landing_label">' . $this->params->get('landing_hidelabel') . '</span>';
					$showhideall .= "\n\t" . '</a>';
					break;

				case 2: // Label only
					$showhideall .= $labellink;
					$showhideall .= "\n\t\t" . '<span id="landing_label">' . $this->params->get('landing_hidelabel') . '</span>';
					$showhideall .= "\n\t" . '</a>';
					break;
			}

			$showhideall .= "\n" . '</div> <!-- end div id="showhide" for ' . $i . ' -->' . "\n";

			return $showhideall;
		}
	}
}
