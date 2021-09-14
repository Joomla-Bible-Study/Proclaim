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
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
// No Direct Access
defined('_JEXEC') or die;

/**
 * Controller class for Sermons
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class SermonsController extends BaseController
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
	 * @since 7.0
	 */
	public function download()
	{
        $input = Factory::getApplication();
		$task  = $input->get('task');
		$mid   = $input->getInt('id');

		if ($task === 'download')
		{
			$downloader = new JBSMDownload;
			$downloader->download($mid);
		}
	}

	/**
	 * Avplayer
	 *
	 * @return void
	 *
	 * @since 7.0
	 */
	public function avplayer()
	{
        $input = Factory::getApplication();
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
		$getMedia = new JBSMMedia;
		$input    = new JInput;
		$getMedia->hitPlay($input->get('id', '', 'int'));
	}
}
