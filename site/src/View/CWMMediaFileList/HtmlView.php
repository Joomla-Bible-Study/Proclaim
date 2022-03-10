<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
namespace CWM\Component\Proclaim\Site\View\CWMMediaFileList;
// No Direct Access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Html\HTMLHelper;
use CWM\Component\Proclaim\Site\Helper\CWMImages;
use Joomla\CMS\Uri\Uri;
use CWM\Component\Proclaim\Administrator\Helper\CWMProclaimHelper;

/**
 * View class for MediaFilelist
 *
 * @property mixed document
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class HtmlView extends BaseHtmlView
{
	/** @var  string Can Do
	 *
	 * @since 7.0 */
	public $canDo;

	/** @var  string Media Types
	 *
	 * @since 7.0 */
	public $mediatypes;

	/** @var  string Page Class SFX
	 *
	 * @since 7.0 */
	public $pageclass_sfx;

	/** @var  string New Link
	 *
	 * @since 7.0 */
	public $newlink;

	/** Items @var JObject
	 *
	 * @since 7.0 */
	protected $items;

	/** Pagination @var array
	 *
	 * @since 7.0 */
	protected $pagination;

	/** State @var object
	 *
	 * @since 7.0 */
	protected $state;

	/** @var  Registry
	 *
	 * @since 7.0 */
	protected $params;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	public function display($tpl = null)
	{
		$app              = Factory::getApplication();
		$this->canDo      = CWMProclaimHelper::getActions('', 'mediafilesedit');
		$this->state      = $this->get('State');
		$this->items      = $this->get('Items');
		$this->mediatypes = $this->get('Mediatypes');
		$this->pagination = $this->get('Pagination');

		// Check for errors
		if (count($errors = $this->get('Errors')))
		{
			$app->enqueueMessage(implode("\n", $errors), 'error');

			return;
		}

		$language = Factory::getLanguage();
		$language->load('', JPATH_ADMINISTRATOR, null, true);

		if (!$this->canDo === 'core.edit')
		{
			Factory::getApplication()->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->setHeader('status', 403, true);

			return false;
		}

		// Create a shortcut to the parameters.
		$this->params = & $this->state->template->params;

		// Render the toolbar on the page. rendering it here means that it is displayed on every view of your component.
		// Puts a new record link at the top of the form @todo There is not "get" in getActions function. I can't test the return but hopefully this is how to do it.
		if ($this->canDo === 'core.create')
		{
			$this->newlink = '<a href="index.php?option=com_proclaim&view=CWMMediaFileForm&task=mediafileform.edit"  class="btn btn-primary">'
				. Text::_('JBS_CMN_NEW') . ' <i class="icon-plus"></i></a>';
		}

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	protected function _prepareDocument()
	{
		$app     = Factory::getApplication();
		$menus   = $app->getMenu();
		$title   = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', Text::_('JBS_FORM_EDIT_ARTICLE'));
		}

		$title = $this->params->def('page_title', '');
		$title .= ' : ' . Text::_('JBS_TITLE_MEDIA_FILES');

		if ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$this->document->setTitle($title);

		$pathway = $app->getPathway();
		$pathway->addItem($title, '');

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
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
			'study.studytitle'     => Text::_('JBS_CMN_STUDY_TITLE'),
			'mediafile.ordering'   => Text::_('JGRID_HEADING_ORDERING'),
			'mediafile.id'         => Text::_('JGRID_HEADING_ID')
		);
	}
}
