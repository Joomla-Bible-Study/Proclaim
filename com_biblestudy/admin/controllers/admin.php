<?php

/**
 * Controller for Admin
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

JLoader::register('fixJBSAssets', BIBLESTUDY_PATH_ADMIN_LIB . '/biblestudy.assets.php');
JLoader::register('JBSconvert', BIBLESTUDY_PATH_ADMIN_LIB . '/biblestudy.sermonspeakerconvert.class.php');
JLoader::register('JBSPIconvert', BIBLESTUDY_PATH_ADMIN_LIB . '/biblestudy.preachitconvert.class.php');
JLoader::register('JBSMFixAlias', BIBLESTUDY_PATH_ADMIN_HELPERS . '/alias.php');

jimport('joomla.application.component.controllerform');

/**
 * Controller for Admin
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyControllerAdmin extends JControllerForm
{

	/**
	 * NOTE: This is needed to prevent Joomla 1.6's pluralization mechanisim from kicking in
	 *
	 * @param  string
	 *
	 * @since 7.0
	 */
	protected $view_list = 'cpanel';

	/**
	 * Class constructor.
	 *
	 * @param   array  $config  A named array of configuration variables.
	 *
	 * @since    1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		// Register Extra tasks
		$this->registerTask('add', 'edit');
		$this->registerTask('apply', 'save');
	}

	/**
	 * Tools to change player or popup
	 *
	 * @return void
	 */
	public function tools()
	{
		$tool = JFactory::getApplication()->input->get('tooltype', '', 'post');

		switch ($tool)
		{
			case 'players':
				$this->changePlayers();
				$msg = JText::_('JBS_CMN_OPERATION_FAILED');
				$this->setRedirect('index.php?option=com_biblestudy&view=cpanel', $msg);
				break;

			case 'popups':
				$this->changePopup();
				$msg = JText::_('JBS_CMN_OPERATION_FAILED');
				$this->setRedirect('index.php?option=com_biblestudy&view=cpanel', $msg);
				break;
		}
	}

	/**
	 * Reset Hits
	 *
	 * @return void
	 */
	public function resetHits()
	{
		$msg   = null;
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->update('#__bsms_studies')
			->set('hits = ' . $db->q('0'));
		$db->setQuery($query);

		if (!$db->execute())
		{
			$msg = JText::_('JBS_CMN_ERROR_RESETTING_HITS');
		}
		else
		{
			$updated = $db->getAffectedRows();
			$msg     = JText::_('JBS_CMN_RESET_SUCCESSFUL') . ' ' . $updated . ' ' . JText::_('JBS_CMN_ROWS_RESET');
		}
		$this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $msg);
	}

	/**
	 * Reset Downloads
	 *
	 * @return void
	 */
	public function resetDownloads()
	{
		$msg   = null;
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->update('#__bsms_mediafiles')
			->set('downloads = ' . $db->q('0'));
		$db->setQuery($query);

		if (!$db->execute())
		{
			$msg = JText::_('JBS_CMN_ERROR_RESETTING_DOWNLOADS');
		}
		else
		{
			$updated = $db->getAffectedRows();
			$msg     = JText::_('JBS_CMN_RESET_SUCCESSFUL') . ' ' . $updated . ' ' . JText::_('JBS_CMN_ROWS_RESET');
		}
		$this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $msg);
	}

	/**
	 * Reset Players
	 *
	 * @return null
	 */
	public function resetPlays()
	{
		$msg   = null;
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->update('#__bsms_mediafiles')
			->set('plays = ' . $db->q('0'));
		$db->setQuery($query);

		if (!$db->execute())
		{
			$msg = JText::_('JBS_CMN_ERROR_RESETTING_PLAYS');
		}
		else
		{
			$updated = $db->getAffectedRows();
			$msg     = JText::_('JBS_CMN_RESET_SUCCESSFUL') . ' ' . $updated . ' ' . JText::_('JBS_CMN_ROWS_RESET');
		}
		$this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $msg);
	}

	/**
	 * Change Player Modes
	 *
	 * @return void
	 */
	public function changePlayers()
	{
		$jinput = JFactory::getApplication()->input;
		$db     = JFactory::getDBO();
		$msg    = null;
		$from   = $jinput->getInt('from', '', 'post');
		$to     = $jinput->getInt('to', '', 'post');

		switch ($from)
		{
			case '100':
				$query = "UPDATE #__bsms_mediafiles SET `player` = " . $db->quote($to) . " WHERE `player` IS NULL";
				break;

			default:
				$query = "UPDATE #__bsms_mediafiles SET `player` = " . $db->quote($to) . " WHERE `player` = " . $db->quote($from);
		}
		$db->setQuery($query);

		if (!$db->execute())
		{
			$msg = JText::_('JBS_ADM_ERROR_OCCURED');
		}
		else
		{
			$msg = JText::_('JBS_CMN_OPERATION_SUCCESSFUL');
		}
		$this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $msg);
	}

	/**
	 * Change Media Popup
	 *
	 * @return void
	 */
	public function changePopup()
	{
		$jinput = JFactory::getApplication()->input;
		$db     = JFactory::getDBO();
		$msg    = null;
		$from   = $jinput->getInt('pfrom', '', 'post');
		$to     = $jinput->getInt('pto', '', 'post');
		$query  = $db->getQuery(true);
		$query->update('#__bsms_mediafiles')
			->set('popup = ' . $db->q($to))
			->where('popup = ' . $db->q($from));
		$db->setQuery($query);

		if (!$db->execute())
		{
			$msg = JText::_('JBS_ADM_ERROR_OCCURED');
		}
		else
		{
			$msg = JText::_('JBS_CMN_OPERATION_SUCCESSFUL');
		}
		$this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $msg);
	}

	/**
	 * Check Assets
	 *
	 * @return void
	 */
	public function checkassets()
	{
		$asset       = new fixJBSAssets;
		$checkassets = $asset->checkAssets();
		JFactory::getApplication()->input->set('checkassets', $checkassets, 'get', JREQUEST_ALLOWRAW);
		parent::display();
	}

	/**
	 * Fix Assets
	 *
	 * @return void
	 */
	public function fixAssets()
	{
		$asset = new fixJBSAssets;
		$asset->fixAssets();
		$this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1&task=admin.checkassets');
	}

	/**
	 * Convert SermonSpeaker to BibleStudy
	 *
	 * @return void
	 */
	public function convertSermonSpeaker()
	{
		$convert      = new JBSconvert;
		$ssconversion = $convert->convertSS();
		$this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $ssconversion);
	}

	/**
	 * Convert PreachIt to BibleStudy
	 *
	 * @return void
	 */
	public function convertPreachIt()
	{
		$convert      = new JBSPIconvert;
		$piconversion = $convert->convertPI();
		$this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $piconversion);
	}

	/**
	 * Tries to fix missing database updates
	 *
	 * @return void
	 *
	 * @since    7.1.0
	 */
	public function fix()
	{
		$model = $this->getModel('admin');
		$model->fix();
		$this->setRedirect(JRoute::_('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', false));
	}

	/**
	 * Alias Updates
	 *
	 * @return void
	 *
	 * @since 7.1.0
	 */
	public function aliasUpdate()
	{
		$alias  = new JBSMFixAlias;
		$update = $alias->updateAlias();
		$this->setMessage(JText::_('JBS_ADM_ALIAS_ROWS') . $update);
		$this->setRedirect(JRoute::_('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', false));
	}

}
