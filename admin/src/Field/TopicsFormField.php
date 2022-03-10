<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
namespace CWM\Component\Proclaim\Administrator\Field;
// No Direct Access
use Joomla\CMS\Form\FormField;

defined('_JEXEC') or die;

jimport('joomla.html.html');
jimport('joomla.access.access');
jimport('joomla.form.formfield');

/**
 * Form Field class for the Topics
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class TopicsFormField extends FormField
{
	/**
	 * Set type to topics
	 *
	 * @var string
	 *
	 * @since 9.0.0
	 */
	public $type = 'TopicsForm';

	/**
	 * Get input form
	 *
	 * @return string
	 *
	 * @since 9.0.0
	 */
	protected function getInput()
	{
		return '<input type="hidden" id="topics" name="jform[topics]"/>';
	}
}
