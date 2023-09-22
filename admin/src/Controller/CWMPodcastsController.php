<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Site\Helper\CWMPodcast;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Session\Session;

/**
 * Controller for Podcasts
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CWMPodcastsController extends AdminController
{
	/**
	 * Proxy for getModel
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return boolean|BaseDatabaseModel
	 *
	 * @since 7.0.0
	 */
	public function getModel($name = 'CWMPodcast', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Write the XML file Called from admin podcast list page.
	 * Used for the Podcasts Page to create xml files.
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 10.0.0
	 */
	public function writeXMLFile(): void
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$podcasts = new CWMPodcast;
		$result   = $podcasts->makePodcasts();
		$this->setRedirect('index.php?option=com_proclaim&view=CWMPodcasts&' . Session::getFormToken() . '=1', $result);
	}
}
