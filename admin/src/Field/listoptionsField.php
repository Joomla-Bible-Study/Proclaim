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
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;



/**
 * Books List Form Field class for the Proclaim component
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class listoptionsField extends ListField
{
	/**
	 * The field type.
	 *
	 * @var         string
	 *
	 * @since 7.0
	 */
	protected $type = 'Listoptions';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return      array           An array of HTMLHelper options.
	 *
	 * @since 7.0
	 */
	protected function getOptions()
	{
		$options[] = HTMLHelper::_('select.option', '0', Text::_('JBS_CMN_NOTHING'));
		$options[] = HTMLHelper::_('select.option', '1', Text::_('JBS_TPL_SCRIPTURE1_BRACE'));
		$options[] = HTMLHelper::_('select.option', '2', Text::_('JBS_TPL_SCRIPTURE2_BRACE'));
		$options[] = HTMLHelper::_('select.option', '3', Text::_('JBS_TPL_SECONDARY_REFERENCES_BRACE'));
		$options[] = HTMLHelper::_('select.option', '4', Text::_('JBS_TPL_DURATION_BRACE'));
		$options[] = HTMLHelper::_('select.option', '5', Text::_('JBS_TPL_TITLE_BRACE'));
		$options[] = HTMLHelper::_('select.option', '6', Text::_('JBS_TPL_STUDY_INTRO_BRACE'));
		$options[] = HTMLHelper::_('select.option', '7', Text::_('JBS_TPL_TEACHER_BRACE'));
		$options[] = HTMLHelper::_('select.option', '8', Text::_('JBS_TPL_TITLE_TEACHER_BRACE'));
		$options[] = HTMLHelper::_('select.option', '30', Text::_('JBS_TPL_TITLE_TEACHER_THUMBNAIL'));
		$options[] = HTMLHelper::_('select.option', '9', Text::_('JBS_TPL_SERIES_BRACE'));
		$options[] = HTMLHelper::_('select.option', '26', Text::_('JBS_TPL_SERIES_THUMBNAIL_BRACE'));
		$options[] = HTMLHelper::_('select.option', '27', Text::_('JBS_TPL_SERIES_DESCRIPTION_BRACE'));
		$options[] = HTMLHelper::_('select.option', '10', Text::_('JBS_TPL_DATE_BRACE'));
		$options[] = HTMLHelper::_('select.option', '11', Text::_('JBS_TPL_SUBMITTED_BRACE'));
		$options[] = HTMLHelper::_('select.option', '12', Text::_('JBS_TPL_HITS_BRACE'));
		$options[] = HTMLHelper::_('select.option', '13', Text::_('JBS_TPL_STUDYNUMBER_BRACE'));
		$options[] = HTMLHelper::_('select.option', '14', Text::_('JBS_TPL_TOPIC_BRACE'));
		$options[] = HTMLHelper::_('select.option', '15', Text::_('JBS_TPL_LOCATION_BRACE'));
		$options[] = HTMLHelper::_('select.option', '16', Text::_('JBS_TPL_MESSAGETYPE_BRACE'));
		$options[] = HTMLHelper::_('select.option', '17', Text::_('JBS_TPL_DETAILS_TEXT_BRACE'));
		$options[] = HTMLHelper::_('select.option', '20', Text::_('JBS_TPL_MEDIA_BRACE'));
		$options[] = HTMLHelper::_('select.option', '23', Text::_('JBS_TPL_FILESIZE_BRACE'));
		$options[] = HTMLHelper::_('select.option', '25', Text::_('JBS_TPL_THUMBNAIL_BRACE'));
		$options[] = HTMLHelper::_('select.option', '28', Text::_('JBS_TPL_MEDIA_PLAYS'));
		$options[] = HTMLHelper::_('select.option', '29', Text::_('JBS_TPL_MEDIA_DOWNLOADS'));
		$options[] = HTMLHelper::_('select.option', '24', Text::_('JBS_CMN_CUSTOM'));
		$options   = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
