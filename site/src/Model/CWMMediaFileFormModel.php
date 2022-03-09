<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
namespace CWM\Component\Proclaim\Site\Model;
// No Direct Access
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\ItemModel;
Use Joomla\CMS\Factory;
use CWM\Component\Proclaim\Administrator\Helper\CWMParams;
use CWM\Component\Proclaim\Administrator\Model\CWMMediafileModel;
// Base this model on the backend version.
//JLoader::register('BiblestudyModelMediafile', JPATH_ADMINISTRATOR . '/components/com_proclaim/models/MediaFileController.php');

/**
 * Model class for MediaFile
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class CWMMediaFileFormModel extends CWMMediafileModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since   11.1
	 */
	public function __construct($config = array())
	{
		$app = Factory::getApplication();
		$app->input->set('id', $app->input->getInt('a_id'));
		parent::__construct($config);
	}

	/**
	 * Get the return URL.
	 *
	 * @return    string    The return URL.
	 *
	 * @since    1.6
	 */
	public function getReturnPage()
	{
		return base64_encode($this->getState('return_page'));
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	protected function populateState()
	{
		/** @type JApplicationSite $app */
		$app   = Factory::getApplication('site');
		$input = $app->input;

		// Load state from the request.
		$pk = $input->get('a_id', null, 'INTEGER');
		$this->setState('mediafile.id', $pk);

		$return = $app->input->get('return', null, 'base64');
		$this->setState('return_page', base64_decode($return));

		// Load the parameters
		/** @var Joomla\Registry\Registry $params */
		$params = $app->getParams();
		$this->setState('params', $params);
		$admin    = CWMParams::getAdmin();
		$params->merge($admin->params);
		$this->setState('administrator', $params);

		$this->setState('layout', $app->input->get('layout'));

		$cdate = $app->getUserState('com_proclaim.edit.mediafile.createdate');
		$this->setState('mediafile.createdate', $cdate);

		$study_id = $app->getUserState('com_proclaim.edit.mediafile.study_id');
		$this->setState('mediafile.study_id', $study_id);

		$server_id = $app->getUserState('com_proclaim.edit.mediafile.server_id');
		$this->setState('mediafile.server_id', $server_id);
	}
}
