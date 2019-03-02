<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2018 (C) CWM Team All rights reserved
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
	 * @since 7.0 */
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
		$document  = JFactory::getDocument();

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

		$images   = new JBSMImages;
		$images->getShowHide();

		// Get the main study list image
		$this->main  = $images->mainStudyImage();

		$uri               = new JUri;
		$Uri_toString      = $uri->toString();
		$this->request_url = $Uri_toString;

		parent::display($tpl);
	}
}
