<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2016 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.christianwebministries.org
 * */
namespace CWM\Component\Proclaim\Site\View\CWMPodcastDisplay;
// No Direct Access
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Html\HTMLHelper;
use CWM\Component\Proclaim\Site\Helper\CWMImages;
use Joomla\CMS\Uri\Uri;
use CWM\Component\Proclaim\Administrator\Helper\CWMProclaimHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Router\Route;
use CWM\Component\Proclaim\Site\Helper\CWMMedia;

/**
 * View class for Messages
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class HtmlView extends BaseHtmlView
{
	protected $state;

	protected $item;

	protected $template;

	protected $media;

	/** @var  Registry */
	protected $params;

	private $studies;

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
		$mainframe = Factory::getApplication();
		$input     = Factory::getApplication();
		$document = Factory::getApplication()->getDocument();

		// Get the menu item object
		// Load the Admin settings and params from the template
		$item               = $this->get('Item');
		$this->state        = $this->get('State');

		/** @var Registry $params */
		$params             = $this->state->template->params;
		$this->template     = $this->state->get('template');

		if (!$item)
		{
			return;
		}

		// Get studies associated with this series
		$mainframe->setUserState('sid', $item->id);
		$this->studies = $this->get('Studies');

		// Get the series image
		$images              = new CWMImages;
		$image               = $images->getSeriesThumbnail($item->series_thumbnail);
		$item->image         = '<img src="' . $image->path . '" height="' . $image->height . '" width="' . $image->width . '" alt="" />';
		$teacherimage        = $images->getTeacherThumbnail($item->thumb, $image2 = null);
		$item->teacherimage = '<img src="' . $teacherimage->path . '" height="' . $teacherimage->height . '" width="'
			. $teacherimage->width . '" alt="" />';

		HtmlHelper::_('proclaim.framework');
		HTMLHelper::_('proclaim.loadCss', $params, null, 'font-awesome');

		$media = [];

		if ($this->studies)
		{
			foreach ($this->studies as $s => $stude)
			{
				$exmedias = explode(',', $stude->mids);
				$jbsmedia = new CWMMedia;

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
		$user   = Factory::getUser();
		$groups = $user->getAuthorisedViewLevels();

		if (!in_array($item->access, $groups) && $item->access)
		{
			$mainframe->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');

			return;
		}

		$input->set('returnid', $item->id);

		// End process prepare content plugins
		$this->params      = & $params;
		$this->item        = $item;
		$uri               = new Uri;
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
		$app     = Factory::getApplication('site');
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
	 * @return boolean
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
