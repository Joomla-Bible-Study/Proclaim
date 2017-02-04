<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.joomlabiblestudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

JFormHelper::loadFieldClass('list');

/**
 * Location List Form Field class for the Joomla Bible Study component
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class JFormFieldMediafileImages extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var         string
	 *
	 * @since 7.0
	 */
	protected $type = 'Mediafileimages';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return   array  An array of JHtml options.
	 *
	 * @since 7.0
	 */
	protected function getOptions ()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__bsms_mediafiles');
		$db->setQuery((string) $query);
		$mediafiles = $db->loadObjectList();
		$options = array();

		if ($mediafiles)
		{
			foreach ($mediafiles as $media)
			{
				$reg = new Registry;
				$reg->loadString($media->params);
				$media->params = $reg;

				if ($media->params->get('media_use_button_icon') >= 1)
				{
					switch ($media->params->get('media_use_button_icon'))
					{
						case 1:
							$button             = $this->getButton($media);
							$media->media_image = JText::_('JBS_MED_BUTTON') . ': ' . $button . ' - ' . JText::_('JBS_MED_TEXT') .
									': ' . $media->params->get('media_button_text');
							$options[]          = JHtml::_('select.option', '{"media_use_button_icon":"' . $media->params->get('media_use_button_icon') .
									'","media_button_type":"' . $media->params->get('media_button_type') . '","media_button_text":"' .
									$media->params->get('media_button_text') . '","media_icon_type":"' . $media->params->get('media_icon_type') .
									'","media_icon_text_size":"' . $media->params->get('media_icon_text_size') . '","media_image":""}', $media->media_image
							);
							break;
						case 2:
							$button             = $this->getButton($media);
							$icon               = $this->getIcon($media);
							$media->media_image = JText::_('JBS_MED_BUTTON') . ': ' . $button . ' - ' . JText::_('JBS_MED_ICON') . ': ' . $icon;
							$options[]          = JHtml::_('select.option', '{"media_use_button_icon":"' . $media->params->get('media_use_button_icon') .
									'","media_button_type":"' . $media->params->get('media_button_type') . '","media_button_text":"' .
									$media->params->get('media_button_text') . '","media_icon_type":"' . $media->params->get('media_icon_type') .
									'","media_icon_text_size":"' . $media->params->get('media_icon_text_size') . '","media_image":""}', $media->media_image
							);
							break;
						case 3:
							$icon               = $this->getIcon($media);
							$media->media_image = JText::_('JBS_MED_ICON') . ': ' . $icon;
							$options[]          = JHtml::_('select.option', '{"media_use_button_icon":"' . $media->params->get('media_use_button_icon') .
									'","media_button_type":"' . $media->params->get('media_button_type') . '","media_button_text":"' .
									$media->params->get('media_button_text') . '","media_icon_type":"' . $media->params->get('media_icon_type') .
									'","media_icon_text_size":"' . $media->params->get('media_icon_text_size') . '","media_image":""}', $media->media_image
							);
							break;
					}
				}
				else
				{
					$image              = $media->params->get('media_image');
					$totalcount         = strlen($image);
					$slash              = strrpos($image, '/');
					$imagecount         = $totalcount - $slash;
					$media->media_image = JText::_('JBS_MED_IMAGE') . ': ' . substr($image, $slash + 1, $imagecount);
					$options[]          = JHtml::_('select.option', '{"media_use_button_icon":"' . $media->params->get('media_use_button_icon') .
							'","media_button_type":"' . $media->params->get('media_button_type') . '","media_button_text":"' .
							$media->params->get('media_button_text') . '","media_icon_type":"' . $media->params->get('media_icon_type') .
							'","media_icon_text_size":"' . $media->params->get('media_icon_text_size') . '","media_image":"' .
							$media->params->get('media_image') . '"}', $media->media_image
					);
				}
			}
		}

		$tmp = array();

		foreach ($options as $k => $v)
		{
			$tmp[$k] = $v->text;
		}

		// Determine the total records for each image/button/incon
		$count = array_count_values($tmp);

		$tmp = array_unique($tmp);

		// Remove the duplicates from original array
		foreach ($options as $k => $v)
		{
			if (!array_key_exists($k, $tmp))
			{
				unset($options[$k]);
			}
		}

		// Add the number of records from $count to the text of the drop down
		foreach ($options as $k => $v)
		{
			foreach ($count as $key => $value)
			{
				if ($key == $v->text)
				{
					$options[$k]->text = $v->text . ' (' . $value . ')';
				}
			}
		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}

	/**
	 * Get Button
	 *
	 * @param   Object  $media  Media table.
	 *
	 * @return string
	 *
	 * @since 7.0
	 */
	public function getButton ($media)
	{
		$button = null;

		switch ($media->params->get('media_button_type'))
		{
			case 'btn-link':
				$button = JText::_('JBS_MED_NO_COLOR');
				break;
			case 'btn-primary':
				$button = JText::_('JBS_MED_PRIMARY');
				break;
			case 'btn-success':
				$button = JText::_('JBS_MED_SUCCESS');
				break;
			case 'btn-info':
				$button = JText::_('JBS_MED_INFO');
				break;
			case 'btn-warning':
				$button = JText::_('JBS_MED_WARNING');
				break;
			case 'btn-danger':
				$button = JText::_('JBS_MED_DANGER');
				break;
		}

		if ($media->params->get('media_button_color'))
		{
			$button = $media->params->get('media_button_color');
		}

		return $button;
	}

	/**
	 * Get Icon
	 *
	 * @param   Object  $media  Media Table
	 *
	 * @return string
	 *
	 * @since 7.0
	 */
	public function getIcon ($media)
	{
		$icon = null;

		switch ($media->params->get('media_icon_type'))
		{
			case 'fa fa-play':
				$icon = JText::_('JBS_MED_PLAY');
				break;
			case 'fa fa-youtube':
				$icon = JText::_('JBS_MED_YOUTUBE');
				break;
			case 'fa fa-video-camera':
				$icon = JText::_('JBS_MED_VIDEO');
				break;
			case 'fa fa-television':
				$icon = JText::_('JBS_MED_BROADCAST');
				break;
			case 'fa fa-file':
				$icon = JText::_('JBS_MED_FILE');
				break;
			case 'fa fa-vimeo':
				$icon = JText::_('JBS_MED_VIMEO');
				break;
			case '1':
				$icon = $media->params->get('media_custom_icon');
				break;
		}

		return $icon;
	}
}
