<?php
/**
 * Controller for Comments
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2017 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

// Base this model on the backend version.
JLoader::register('BiblestudyControllerComments', JPATH_ADMINISTRATOR . '/components/com_biblestudy/controllers/comments.php');

/**
 * Controller for Comments
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class BiblestudyControllerCommentlist extends BiblestudyControllerComments
{
	/**
	 * View item
	 *
	 * @since    1.6
	 */
	protected $view_item = 'commentform';

	/**
	 * View list
	 *
	 * @since    1.6
	 */
	protected $view_list = 'commentlist';

	/**
	 * @var        string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'COM_BIBLESTUDY';

	/**
	 * Proxy for getModel
	 *
	 * @param   string  $name    The name of the model
	 * @param   string  $prefix  The prefix for the PHP class name
	 * @param   array   $config  Set ignore request
	 *
	 * @return \JModelLegacy
	 *
	 * @since 7.0
	 */
	public function &getModel($name = 'CommentList', $prefix = 'BiblestudyModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}
}
