<?php
/**
 * Controller for Comments
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
namespace CWM\Component\Proclaim\Site\CWMCommentlistController;
use JLoader;
use JModelLegacy;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Controller\BaseController;
use CWM\Component\Proclaim\Administrator\Controller\CWMCommentsController;

// No Direct Access
defined('_JEXEC') or die;

// Base this model on the backend version.
/**
 * Controller for Comments
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CWMCommentlistController extends CWMCommentsController
{
	/**
	 * View item
	 *
	 * @since    1.6
	 */
	protected $view_item = 'cwmcommentform';

	/**
	 * View list
	 *
	 * @since    1.6
	 */
	protected $view_list = 'cwmcommentlist';

	/**
	 * @var        string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'com_proclaim';

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
	public function getModel($name = 'CWMCommentsList', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}
}
