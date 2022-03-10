<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\View\CWMCommentList;

// No Direct Access
defined('_JEXEC') or die;

use CWM\Component\Proclaim\Administrator\Helper\CWMProclaimHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Html\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/**
 * View class for CommentList extends Comments
 *
 * @property mixed canDo
 * @property array f_levels
 * @property mixed sidebar
 * @since  7.0.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * Items
	 *
	 * @var array
	 *
	 * @since 7.0
	 */
	protected $items;

	/**
	 * Pagination
	 *
	 * @var array
	 *
	 * @since 7.0
	 */
	protected $pagination;

	/**
	 * State
	 *
	 * @var array
	 *
	 * @since 7.0
	 */
	protected $state;

	/**
	 * State
	 *
	 * @var array
	 *
	 * @since 7.0
	 */
	protected $params;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since 7.0
	 */
	public function display($tpl = null)
	{
		$app              = Factory::getApplication();
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');
		$this->params     = $this->state->template->params;

		// Check for errors
		if (count($errors = $this->get('Errors')))
		{
			$app->enqueueMessage(implode("\n", $errors), 'error');

			return;
		}

		$language = Factory::getLanguage();
		$language->load('', JPATH_ADMINISTRATOR, null, true);

		$this->canDo = CWMProclaimHelper::getActions('', 'comments');

		// Check permissions to enter studies
		if (!$this->canDo->get('core.edit'))
		{
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');

			return;
		}

		// Levels filter.
		$options   = array();
		$options[] = HtmlHelper::_('select.option', '1', Text::_('J1'));
		$options[] = HtmlHelper::_('select.option', '2', Text::_('J2'));
		$options[] = HtmlHelper::_('select.option', '3', Text::_('J3'));
		$options[] = HtmlHelper::_('select.option', '4', Text::_('J4'));
		$options[] = HtmlHelper::_('select.option', '5', Text::_('J5'));
		$options[] = HtmlHelper::_('select.option', '6', Text::_('J6'));
		$options[] = HtmlHelper::_('select.option', '7', Text::_('J7'));
		$options[] = HtmlHelper::_('select.option', '8', Text::_('J8'));
		$options[] = HtmlHelper::_('select.option', '9', Text::_('J9'));
		$options[] = HtmlHelper::_('select.option', '10', Text::_('J10'));

		$this->f_levels = $options;


		// Display the template
		parent::display($tpl);

		// Set the document
		$this->setDocument();
	}

	/**
	 * Add the page title to browser.
	 *
	 * @return  void
	 *
	 * @since    7.1.0
	 */
	protected function setDocument()
	{
		$document = Factory::getApplication()->getDocument();
		$document->setTitle(Text::_('JBS_TITLE_COMMENTS'));
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array(
			'comment.full_name' => Text::_('JBS_CMT_FULL_NAME'),
			'comment.published' => Text::_('JSTATUS'),
			'study.studytitle'  => Text::_('JBS_CMN_TITLE'),
			'comment.language'  => Text::_('JGRID_HEADING_LANGUAGE'),
			'comment.id'        => Text::_('JGRID_HEADING_ID')
		);
	}
}
