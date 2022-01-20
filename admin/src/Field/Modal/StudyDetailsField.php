<?php
namespace CWM\Component\Proclaim\Administrator\Field\Modal;
/**
 * Supports a modal study picker.
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\ParameterType;
// No Direct Access
defined('_JEXEC') or die;

/**
 * Supports a modal study picker.
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class StudyDetailsField extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $type = 'Modal_StudyDetails';

	/**
	 * Method to get the field input markup.
	 *
	 * @return    string    The field input markup.
	 *
	 * @since    1.6
	 */


	/**
	 * @package         Joomla.Administrator
	 * @subpackage      com_content
	 *
	 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
	 * @license         GNU General Public License version 2 or later; see LICENSE.txt
	 */









	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{

		$allowClear     = ((string) $this->element['clear'] != 'false');
		$allowSelect    = ((string) $this->element['select'] != 'false');
		$allowPropagate = ((string) $this->element['propagate'] == 'true');

		$languages = LanguageHelper::getContentLanguages(array(0, 1), false);

		// Load language
		Factory::getLanguage()->load('com_content', JPATH_ADMINISTRATOR);

		// The active article id field.
		$value = (int) $this->value ?: '';

		// Create the modal id.
		$modalId = 'Article_' . $this->id;

		/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
		$wa = Factory::getApplication()->getDocument()->getWebAssetManager();

		// Add the modal field script to the document head.
		$wa->useScript('field.modal-fields');

		// Script to proxy the select modal function to the modal-fields.js file.
		if ($allowSelect)
		{
			static $scriptSelect = null;

			if (is_null($scriptSelect))
			{
				$scriptSelect = array();
			}

			if (!isset($scriptSelect[$this->id]))
			{
				$wa->addInlineScript("
				window.jSelectArticle_" . $this->id . " = function (id, title, catid, object, url, language) {
					window.processModalSelect('Article', '" . $this->id . "', id, title, catid, object, url, language);
				}",
					[],
					['type' => 'module']
				);

				Text::script('JGLOBAL_ASSOCIATIONS_PROPAGATE_FAILED');

				$scriptSelect[$this->id] = true;
			}
		}

		// Setup variables for display.
		$linkArticles = 'index.php?option=com_content&amp;view=articles&amp;layout=modal&amp;tmpl=component&amp;' . Session::getFormToken() . '=1';
		$linkArticle  = 'index.php?option=com_content&amp;view=article&amp;layout=modal&amp;tmpl=component&amp;' . Session::getFormToken() . '=1';

		if (isset($this->element['language']))
		{
			$linkArticles .= '&amp;forcedLanguage=' . $this->element['language'];
			$linkArticle  .= '&amp;forcedLanguage=' . $this->element['language'];
			$modalTitle   = Text::_('COM_CONTENT_SELECT_AN_ARTICLE') . ' &#8212; ' . $this->element['label'];
		}
		else
		{
			$modalTitle = Text::_('COM_CONTENT_SELECT_AN_ARTICLE');
		}

		$urlSelect = $linkArticles . '&amp;function=jSelectArticle_' . $this->id;
		$urlEdit   = $linkArticle . '&amp;task=article.edit&amp;id=\' + document.getElementById(&quot;' . $this->id . '_id&quot;).value + \'';
		$urlNew    = $linkArticle . '&amp;task=article.add';

		if ($value)
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName('title'))
				->from($db->quoteName('#__content'))
				->where($db->quoteName('id') . ' = :value')
				->bind(':value', $value, ParameterType::INTEGER);
			$db->setQuery($query);

			try
			{
				$title = $db->loadResult();
			}
			catch (\RuntimeException $e)
			{
				Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			}
		}

		$title = empty($title) ? Text::_('COM_CONTENT_SELECT_AN_ARTICLE') : htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		// The current article display field.
		$html = '';

		if ($allowSelect || $allowClear)
		{
			$html .= '<span class="input-group">';
		}

		$html .= '<input class="form-control" id="' . $this->id . '_name" type="text" value="' . $title . '" readonly size="35">';

		// Select article button
		if ($allowSelect)
		{
			$html .= '<button'
				. ' class="btn btn-primary' . ($value ? ' hidden' : '') . '"'
				. ' id="' . $this->id . '_select"'
				. ' data-bs-toggle="modal"'
				. ' type="button"'
				. ' data-bs-target="#ModalSelect' . $modalId . '">'
				. '<span class="icon-file" aria-hidden="true"></span> ' . Text::_('JSELECT')
				. '</button>';
		}



		// Clear article button
		if ($allowClear)
		{
			$html .= '<button'
				. ' class="btn btn-secondary' . ($value ? '' : ' hidden') . '"'
				. ' id="' . $this->id . '_clear"'
				. ' type="button"'
				. ' onclick="window.processModalParent(\'' . $this->id . '\'); return false;">'
				. '<span class="icon-times" aria-hidden="true"></span> ' . Text::_('JCLEAR')
				. '</button>';
		}

		// Propagate article button
		if ($allowPropagate && count($languages) > 2)
		{
			// Strip off language tag at the end
			$tagLength            = (int) strlen($this->element['language']);
			$callbackFunctionStem = substr("jSelectArticle_" . $this->id, 0, -$tagLength);

			$html .= '<button'
				. ' class="btn btn-primary' . ($value ? '' : ' hidden') . '"'
				. ' type="button"'
				. ' id="' . $this->id . '_propagate"'
				. ' title="' . Text::_('JGLOBAL_ASSOCIATIONS_PROPAGATE_TIP') . '"'
				. ' onclick="Joomla.propagateAssociation(\'' . $this->id . '\', \'' . $callbackFunctionStem . '\');">'
				. '<span class="icon-sync" aria-hidden="true"></span> ' . Text::_('JGLOBAL_ASSOCIATIONS_PROPAGATE_BUTTON')
				. '</button>';
		}

		if ($allowSelect || $allowClear)
		{
			$html .= '</span>';
		}

		// Select article modal
		if ($allowSelect)
		{
			$html .= HTMLHelper::_(
				'bootstrap.renderModal',
				'ModalSelect' . $modalId,
				array(
					'title'      => $modalTitle,
					'url'        => $urlSelect,
					'height'     => '400px',
					'width'      => '800px',
					'bodyHeight' => 70,
					'modalWidth' => 80,
					'footer'     => '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'
						. Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>',
				)
			);
		}



		// Note: class='required' for client side validation.
		$class = $this->required ? ' class="required modal-value"' : '';

		$html .= '<input type="hidden" id="' . $this->id . '_id" ' . $class . ' data-required="' . (int) $this->required . '" name="' . $this->name
			. '" data-text="' . htmlspecialchars(Text::_('COM_CONTENT_SELECT_AN_ARTICLE'), ENT_COMPAT, 'UTF-8') . '" value="' . $value . '">';

		return $html;
	}

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   3.4
	 */
	protected function getLabel()
	{
		return str_replace($this->id, $this->id . '_name', parent::getLabel());
	}


protected function getInput_old()
	{
		// Load the modal behavior script.
		HtmlHelper::_('bootstrap.modal', 'a.modal');

		// Load the javascript and css
        HtmlHelper::_('behavior.framework');
        HtmlHelper::_('script', 'system/modal.js', false, true);
        HtmlHelper::_('stylesheet', 'system/modal.css', array(), true);

		// Build the script.
		$script   = array();
		$script[] = '	function jSelectChart_' . $this->id . '(id, name, object) {';
		$script[] = '		document.id("' . $this->id . '_id").value = id;';
		$script[] = '		document.id("' . $this->id . '_name").value = name;';
		$script[] = '		SqueezeBox.close();';
		$script[] = '	}';

		// Add the script to the document head.
		Factory::getDocument()->addScriptDeclaration(implode("\n", $script));

		// Build the script.
		$script   = array();
		$script[] = '	window.addEvent("domready", function() {';
		$script[] = '		var div = new Element("div").setStyle("display", "none").injectBefore(document.id("menu-types"));';
		$script[] = '		document.id("menu-types").injectInside(div);';
		$script[] = '		SqueezeBox.initialize();';
		$script[] = '		SqueezeBox.assign($$("input.modal"), {parse:"rel"});';
		$script[] = '	});';

		// Add the script to the document head.
		Factory::getDocument()->addScriptDeclaration(implode("\n", $script));

		// Get the title of the linked chart
		$db = Factory::getDbo();
		$db->setQuery(
			'SELECT studytitle AS name' .
			' FROM #__bsms_studies' .
			' WHERE id = ' . (int) $this->value
		);
		$title = $db->loadResult();

		if ($error = $db->getErrorMsg())
		{
			JError::raiseWarning(500, $error);
		}

		if (empty($title))
		{
			$title = Text::_('JBS_CMN_SELECT_STUDY');
		}

		$link = 'index.php?option=com_proclaim&amp;view=messages&amp;layout=modal&amp;tmpl=component&amp;function=jSelectChart_' . $this->id;

		HtmlHelper::_('bootstrap.modal', 'a.modal');
		$html = "\n" . '<div class="fltlft"><input type="text" id="' . $this->id . '_name" value="' .
			htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '" disabled="disabled" /></div>';
		$html .= '<div class="button2-left"><div class="blank"><a class="modal" title="' . Text::_('JBS_CMN_SELECT_STUDY') .
			'"  href="' . $link . '" rel="{handler: \'iframe\', size: {x: 900, y: 450}}">' . Text::_('JBS_CMN_SELECT_STUDY') . '</a></div></div>' . "\n";

		// The active study id field.
		if (0 == (int) $this->value)
		{
			$value = '';
		}
		else
		{
			$value = (int) $this->value;
		}

		// Class='required' for client side validation
		$class = '';

		if ($this->required)
		{
			$class = ' class="required modal-value"';
		}

		$html .= '<input type="hidden" id="' . $this->id . '_id"' . $class . ' name="' . $this->name . '" value="' . $value . '" />';

		return $html;
	}
}
