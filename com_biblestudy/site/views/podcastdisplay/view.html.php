<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * View class for Messages
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class BiblestudyViewPodcastdisplay extends JViewLegacy
{
	protected $state;

	protected $items;

	protected $template;

	/** @var  Registry */
	protected $params;

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
		$mainframe = JFactory::getApplication();
		$input     = new JInput;
		$document = JFactory::getDocument();

		// Get the menu item object
		// Load the Admin settings and params from the template
		$items              = $this->get('Item');
		$this->state        = $this->get('State');

		/** @var Registry $params */
		$params             = $this->state->template->params;
		$this->template     = $this->state->get('template');

		// Get studies associated with this series
		$mainframe->setUserState('sid', $items->id);
		$this->studies = $this->get('Studies');

		// Get the series image
		$images              = new JBSMImages;
		$image               = $images->getSeriesThumbnail($items->series_thumbnail);
		$items->image        = '<img src="' . $image->path . '" height="' . $image->height . '" width="' . $image->width . '" alt="" />';
		$teacherimage        = $images->getTeacherThumbnail($items->thumb, $image2 = null);
		$items->teacherimage = '<img src="' . $teacherimage->path . '" height="' . $teacherimage->height . '" width="'
			. $teacherimage->width . '" alt="" />';

		JHtml::_('biblestudy.framework');
		JHtml::_('biblestudy.loadCss', $params, null, 'font-awesome');

		$media = [];

		foreach ($this->studies as $s => $stude)
		{
			$exmedias = explode(',', $stude->mids);
			$jbsmedia = new JBSMMedia;

			foreach ($exmedias as $i => $exmedia)
			{
				$rmedia = $jbsmedia->getMediaRows2($exmedia);

				if ($rmedia)
				{
					$reg = new Registry;
					$reg->loadString($rmedia->params);
					$rparams = $reg;

					if ($this->endsWith($rparams->get('filename'), '.mp3') === true)
					{
						$media[] = $rmedia;
					}
				}
			}
		}

		$this->media = $media;

		// Set Player build info
		$params->set('pcplaylist', 1);
		$params->set('show_filesize', 0);
		$params->set('mp3', true);

		// Prepare meta information (under development)
		if ($params->get('metakey'))
		{
			$document->setMetaData('keywords', $params->get('metakey'));
		}

		if ($params->get('metadesc'))
		{
			$document->setDescription($params->get('metadesc'));
		}

		// Check permissions for this view by running through the records and removing those the user doesn't have permission to see
		$user   = JFactory::getUser();
		$groups = $user->getAuthorisedViewLevels();

		if (!in_array($items->access, $groups) && $items->access)
		{
			$mainframe->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');

			return;
		}

		$input->set('returnid', $items->id);

		// End process prepare content plugins
		$this->params      = & $params;
		$this->items       = $items;
		$uri               = new JUri;
		$stringuri         = $uri->toString();
		$this->request_url = $stringuri;

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return void
	 *
	 * @since 7.0
	 */
	protected function _prepareDocument()
	{
		$app     = JFactory::getApplication('site');
		$menus   = $app->getMenu()->getActive();
		$this->params->merge($menus->params);

		$title   = null;
	}

	/**
	 * Find Ends With
	 *
	 * @param   string  $haystack  Search string
	 * @param   string  $needle    What to search for.
	 *
	 * @return bool
	 *
	 * @since version
	 */
	private function endsWith($haystack, $needle)
	{
		$length = strlen($needle);

		if ($length == 0)
		{
			return true;
		}

		return (substr($haystack, -$length) === $needle);
	}
}
