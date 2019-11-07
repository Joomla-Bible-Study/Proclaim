<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * Class JBSMAddonYouTube
 *
 * @package  Proclaim.Admin
 * @since    9.0.0
 */
class JBSMAddonYouTube extends JBSMAddon
{
	protected $config;

	/**
	 * Upload
	 *
	 * @param   JInput|array  $data  Data to upload
	 *
	 * @return array
	 *
	 * @since 9.0.0
	 */
	public function upload($data)
	{
		// Holde for nothing
		return $data;
	}

	/**
	 * Render Fields for general view.
	 *
	 * @param   object  $media_form  Midea files form
	 * @param   bool    $new         If media is new
	 *
	 * @return string
	 *
	 * @since 9.1.3
	 */
	public function renderGeneral($media_form, $new)
	{
		$html = '';
		$fields = $media_form->getFieldset('general');

		if ($fields)
		{
			foreach ($fields as $field):
				$html .= '<div class="control-group">';
				$html .= '<div class="control-label">';
				$html .= $field->label;
				$html .= '</div>';
				$html .= '<div class="controls">';

				// Way to set defaults on new media
				if ($new)
				{
					$s_name = $field->fieldname;

					if (isset($media_form->s_params[$s_name]))
					{
						$field->setValue($media_form->s_params[$s_name]);
					}
				}

				$html .= $field->input;
				$html .= '</div>';
				$html .= '</div>';
			endforeach;
		}

		return $html;
	}

	/**
	 * Render Layout and fields
	 *
	 * @param   object  $media_form  Midea files form
	 * @param   bool    $new         If media is new
	 *
	 * @return string
	 *
	 * @since 9.1.3
	 */
	public function render($media_form, $new)
	{
		$html = '';
		$html .= JHtml::_('bootstrap.addTab', 'myTab', 'options', JText::_('Options'));

		$html .= '<div class="row-fluid">';

		foreach ($media_form->getFieldsets('params') as $name => $fieldset)
		{
			if ($name !== 'general')
			{
				$html .= '<div class="span6">';

				foreach ($media_form->getFieldset($name) as $field):
					$html .= '<div class="control-group">';
					$html .= '<div class="control-label">';
					$html .= $field->label;
					$html .= '</div>';
					$html .= '<div class="controls">';

					// Way to set defaults on new media
					if ($new)
					{
						$s_name = $field->fieldname;

						if (isset($media_form->s_params[$s_name]))
						{
							$field->setValue($media_form->s_params[$s_name]);
						}
					}

					$html .= $field->input;
					$html .= '</div>';
					$html .= '</div>';
				endforeach;

				$html .= '</div>';
			}
		}

		$html .= '</div>';
		$html .= JHtml::_('bootstrap.endTab');

		return $html;
	}
}
