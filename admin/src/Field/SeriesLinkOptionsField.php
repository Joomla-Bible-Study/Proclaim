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

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Books List Form Field class for the Proclaim component
 *
 * @package  Proclaim.Admin
 * @since    7.0.4
 */
class SeriesLinkOptionsField extends ListField
{
	/**
	 * The field type.
	 *
	 * @var         string
	 *
	 * @since    7.0.4
	 */
	protected $type = 'SeriesLinkOptions';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return      array           An array of JHtml options.
	 *
	 * @since    7.0.4
	 */
	protected function getOptions(): array
	{
		$options[] = HTMLHelper::_('select.option', '0', Text::_('JBS_TPL_NO_LINK'));
		$options[] = HTMLHelper::_('select.option', '1', Text::_('JBS_TPL_LINK_TO_DETAILS'));

		return array_merge(parent::getOptions(), $options);
	}
}
