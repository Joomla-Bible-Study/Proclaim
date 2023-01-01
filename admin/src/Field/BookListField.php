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

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CWMProclaimHelper;
use Joomla\CMS\Form\Field\ListField;

/**
 * Location List Form Field class for the Proclaim component
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class BookListField extends ListField
{
	/**
	 * The field type.
	 *
	 * @var  string
	 *
	 * @since 7.0
	 */
	protected $type = 'BookList';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array   An array of JHtml options.
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	protected function getOptions()
	{
		return array_merge(parent::getOptions(), CWMProclaimHelper::getStudyBooks());
	}
}
