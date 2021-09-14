<?php
/**
 * Controller for MediaFiles
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Controller\BaseController;
// No Direct Access
defined('_JEXEC') or die;

/**
 * Controller for MediaFiles
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class MediafilelistController extends BaseController
{
	/**
	 * View item
	 *
	 * @since    1.6
	 */
	protected $view_item = 'mediafileform';

	/**
	 * View list
	 *
	 * @since    1.6
	 */
	protected $view_list = 'mediafilelist';

	/**
	 * @var        string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'COM_PROCLAIM';

	/**
	 * Proxy for getModel
	 *
	 * @param   string  $name    The name of the model
	 * @param   string  $prefix  The prefix for the PHP class name
	 * @param   array   $config  Set ignore request
	 *
	 * @return JModelLegacy
	 *
	 * @since 7.0
	 */
	public function &getModel(
		$name = 'Mediafileform',
		$prefix = 'ProclaimModel',
		$config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}
}
