<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */

defined('_JEXEC') or die;
jimport('joomla.application.component.controller');
jimport('joomla.html.parameter');

JLoader::register('Com_BiblestudyInstallerScript', JPATH_ADMINISTRATOR . '/components/com_biblestudy/biblestudy.script.php');

/**
 * Class Migration Controller
 *
 * @package  BibleStudy.Admin
 * @since    7.1.0
 */
class BiblestudyControllerMigration extends JControllerLegacy
{
	/**
	 * Constructor.
	 *
	 * @param   array $config  An optional associative array of configuration settings.
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->modelName = 'migration';
	}

	/**
	 * Constructor.
	 *
	 * @param   string $task  An optional associative array of configuration settings.
	 *
	 * @return void
	 */
	public function execute($task)
	{
		if ($task != 'run')
		{
			$task = 'browse';
		}
		parent::execute($task);
	}

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function browse()
	{
		$app = JFactory::getApplication();

		if ($app->input->getInt('jbsimport') == 0)
		{
			JBSMDbHelper::resetdb();
			$app->enqueueMessage(JText::_('JBS_CMN_DATABASE_NOT_MIGRATED'), 'warning');
			$this->setRedirect('index.php?option=com_biblestudy&view=admin&id=1');
		}
		else
		{
			$model = $this->getModel('migration');
			$state = $model->startScanning();
			$app->input->set('scanstate', $state);

			$this->display(false);
		}
	}

	/**
	 * Run Function
	 *
	 * @return void
	 *
	 * @since 8.0.0
	 */
	public function run()
	{
		$app   = JFactory::getApplication();
		$model = $this->getModel('migration');
		$state = $model->run();
		$app->input->set('scanstate', $state);

		$this->display(false);
	}

}
