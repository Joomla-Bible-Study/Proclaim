<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\Controller;

use CWM\Component\Proclaim\Site\Helper\CWMdownload;
use CWM\Component\Proclaim\Site\Helper\CWMMedia;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Input\Input;

// No Direct Access
defined('_JEXEC') or die;

/**
 * Controller class for Sermons
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class CWMSermonsController extends BaseController
{
	/**
	 * Media Code
	 *
	 * @var string
	 * @since 7.0
	 */
	public $mediaCode;

	/**
	 * Download?
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	public function download()
	{
		$input = Factory::getApplication()->input;
		$task  = $input->get('task');
		$mid   = $input->getInt('id');

		if ($task === 'download')
		{
			$downloader = new CWMDownload;
			$downloader->download($mid);
		}
	}

	/**
	 * Avplayer
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	public function avplayer()
	{
		$input = Factory::getApplication()->input;
		$task  = $input->get('task');

		if ($task === 'avplayer')
		{
			$mediacode       = $input->get('code', '', 'string');
			$this->mediaCode = $mediacode;
		}
	}

	/**
	 * Add hits to the play count.
	 *
	 * @return  void
	 *
	 * @since 7.0
	 */
	public function playHit()
	{
		$getMedia = new CWMMedia;
		$input    = new Input;
		$getMedia->hitPlay($input->get('id', '', 'int'));
	}
}
