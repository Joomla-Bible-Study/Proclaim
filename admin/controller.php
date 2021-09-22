<?php
/**
 * Admin Controller
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

// Always load JBSM API if it exists.
$api = JPATH_ADMINISTRATOR . '/components/com_proclaim/api.php';

if (file_exists($api))
{
	require_once $api;
}

/**
 * JController for BibleStudy Admin class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class controller extends JControllerLegacy
{
	/**
	 * Default view var.
	 *
	 * @var string
	 *
	 * @since 7.0.0
	 */
	protected $default_view = 'cwmcpanel';

	/**
	 * Core Display
	 *
	 * @param   boolean  $cachable   Cachable system
	 * @param   boolean  $urlparams  Url params
	 *
	 * @return  JControllerLegacy  his object to support chaining.
	 *
	 * @since 1.5
	 * @throws Exception
	 */
	public function display($cachable = false, $urlparams = array())
	{
		$app = Factory::getApplication();

		$view   = $app->input->getCmd('view', 'cwmcpanel');
		$layout = $app->input->getCmd('layout', 'default');

		if ($layout !== 'modal')
		{
			JBSMBibleStudyHelper::addSubmenu($view);
		}

		$jbsstate = JBSMDbHelper::getInstallState();

		if ($jbsstate)
		{
			$cache = new JCache(array('defaultgroup' => 'com_proclaim'));
			$cache->clean();
			$app->input->set('view', 'install');
			$app->input->set('scanstate', 'start');
		}

		if (!$view)
		{
			$app->input->set('view ', 'cwmcpanel');
		}

		return parent::display();
	}

	/**
	 * Write the XML file Called from administrator podcast list page.
	 *
	 * @return void
	 *
	 * @since 9.0.0
	 */
	public function writeXMLFile()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$podcasts = new JBSMPodcast;
		$result   = $podcasts->makePodcasts();
		$this->setRedirect('index.php?option=com_proclaim&view=podcasts&' . JSession::getFormToken() . '=1', $result);
	}
}
