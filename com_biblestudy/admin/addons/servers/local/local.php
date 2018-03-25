<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2018 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * Class JBSMAddonLocal
 *
 * @package  Proclaim.Admin
 * @since    9.0.0
 */
class JBSMAddonLocal extends JBSMAddon
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
		// Convert back slashes to forward slashes
		$file  = str_replace('\\', '/', $data->get('path', null, 'STRING'));
		$slash = strrpos($file, '/');

		$path = substr($file, 0, $slash + 1);

		// Remove domain from path
		preg_match('/\/+.+/', $path, $matches);

		// Make filename safe and move it to correct folder
		$destFile = JFile::makeSafe($_FILES["file"]["name"]);
		$destFile = str_replace(' ', '_', $destFile);

		if (!JFile::upload($_FILES['file']['tmp_name'], JPATH_ROOT . $matches[0] . $destFile))
		{
			die('false');
		}

		return array(
			'data' => array(
				'filename' => $matches[0] . $destFile,
				'size'     => $_FILES['file']['size']
			)
		);
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
			foreach ($media_form->getFieldset('general') as $field):
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

			foreach ($media_form->getFieldsets('params') as $name => $fieldset):
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

			endforeach;

		$html .= '</div>';
		$html .= JHtml::_('bootstrap.endTab');

		return $html;
	}
}
