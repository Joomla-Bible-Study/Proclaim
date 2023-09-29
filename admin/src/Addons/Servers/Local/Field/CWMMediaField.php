<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
namespace CWM\Component\Proclaim\Administrator\Addons\Servers\Local\Field;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use SimpleXMLElement;

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla CMS.
 * Provides a modal media selector including upload mechanism
 *
 * @since  1.6
 */
class CWMMediaField extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $type = 'Media';

	/**
	 * The authorField.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $authorField;

	/**
	 * The asset.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $asset;

	/**
	 * The link.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $link;

	/**
	 * Modal width.
	 *
	 * @var    integer
	 * @since  3.4.5
	 */
	protected $width;

	/**
	 * Modal height.
	 *
	 * @var    integer
	 * @since  3.4.5
	 */
	protected $height;

	/**
	 * The authorField.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $preview;

	/**
	 * The preview.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $directory;

	/**
	 * The previewWidth.
	 *
	 * @var    integer
	 * @since  3.2
	 */
	protected $previewWidth;

	/**
	 * The previewHeight.
	 *
	 * @var    integer
	 * @since  3.2
	 */
	protected $previewHeight;

	/**
	 * Layout to render
	 *
	 * @var    string
	 * @since  3.5
	 */
	protected $layout = 'joomla.form.field.jbsmmedia';

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   3.2
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'authorField':
			case 'asset':
			case 'link':
			case 'width':
			case 'height':
			case 'preview':
			case 'directory':
			case 'previewWidth':
			case 'previewHeight':
				return $this->$name;
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'authorField':
			case 'asset':
			case 'link':
			case 'width':
			case 'height':
			case 'preview':
			case 'directory':
				$this->$name = (string) $value;
				break;

			case 'previewWidth':
			case 'previewHeight':
				$this->$name = (int) $value;
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Something everything should work now
	 *
	 * @param   string  $name   The property name for which to the value.
	 *
	 *
	 * @since version
	 * @return void
	 */
	public function __isset(string $name)
	{
		// TODO: Implement __isset() method.
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example, if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFormField::setup()
	 * @since   3.2
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null): bool
	{
		$result = parent::setup($element, $value, $group);

		if ($result)
		{
			$assetField = $this->element['asset_field'] ? (string) $this->element['asset_field'] : 'asset_id';

			$this->authorField   = $this->element['created_by_field'] ? (string) $this->element['created_by_field'] : 'created_by';
			$this->asset         = $this->form->getValue($assetField) ? $this->form->getValue($assetField) : (string) $this->element['asset_id'];
			$this->link          = (string) $this->element['link'];
			$this->width         = isset($this->element['width']) ? (int) $this->element['width'] : 800;
			$this->height        = isset($this->element['height']) ? (int) $this->element['height'] : 500;
			$this->preview       = (string) $this->element['preview'];
			$this->directory     = (string) $this->element['directory'];
			$this->previewWidth  = isset($this->element['preview_width']) ? (int) $this->element['preview_width'] : 200;
			$this->previewHeight = isset($this->element['preview_height']) ? (int) $this->element['preview_height'] : 200;
		}

		return $result;
	}

	/**
	 * Method to get the field input markup for a media selector.
	 * Use attributes to identify specific created_by and asset_id fields
	 *
	 * @return  string  The field input markup.
	 *
	 * @throws \Exception
	 * @since   1.6
	 */
	protected function getInput(): string
	{
		if (empty($this->layout))
		{
			throw new \UnexpectedValueException(sprintf('%s has no layout assigned.', $this->name));
		}

		return $this->getRenderer($this->layout)->render($this->getLayoutData());
	}

	/**
	 * Get the data that is going to be passed to the layout
	 *
	 * @return  array
	 * @throws \Exception
	 * @since 7.0.0
	 */
	public function getLayoutData(): array
	{
		// Get the basic field data
		$data = parent::getLayoutData();

		$asset = $this->asset;

		if ($asset === '')
		{
			$asset = Factory::getApplication()->input->get('option');
		}

		if ($this->value && file_exists(JPATH_ROOT . '/' . $this->value))
		{
			$folder = explode('/', $this->value);
			$folder = array_diff_assoc($folder, explode('/', ComponentHelper::getParams('com_media')->get('image_path', 'images')));
			array_pop($folder);
			$folder = implode('/', $folder);
		}
		elseif (file_exists(JPATH_ROOT . '/' . ComponentHelper::getParams('com_media')->get('image_path', 'images') . '/' . $this->directory))
		{
			$folder = $this->directory;
		}
		else
		{
			$folder = '';
		}

		$extraData = array(
			'asset'         => $asset,
			'authorField'   => $this->authorField,
			'authorId'      => $this->form->getValue($this->authorField),
			'folder'        => $folder,
			'link'          => $this->link,
			'preview'       => $this->preview,
			'previewHeight' => $this->previewHeight,
			'previewWidth'  => $this->previewWidth,
		);

		return array_merge($data, $extraData);
	}
}
