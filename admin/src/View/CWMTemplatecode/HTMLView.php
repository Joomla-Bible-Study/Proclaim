<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMTemplatecode;

// No Direct Access
use CWM\Component\Proclaim\Administrator\Helper\CWMProclaimHelper;
use Joomla\CMS\Client\ClientHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Input\Input;

defined('_JEXEC') or die;

/**
 * View class for TemplateCode
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class HTMLView extends BaseHtmlView
{
	/**
	 * Default Code for the Edit if content is null
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public $defaultcode;

	/**
	 * Type
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public $type;

	/**
	 * Can Do
	 *
	 * @var object
	 * @since    7.0.0
	 */
	public $canDo;

	/**
	 * Form
	 *
	 * @var object
	 * @since    7.0.0
	 */
	protected $form;

	/**
	 * Item
	 *
	 * @var object
	 * @since    7.0.0
	 */
	protected $item;

	/**
	 * State
	 *
	 * @var object
	 * @since    7.0.0
	 */
	protected $state;

	/**
	 * Defaults
	 *
	 * @var object
	 * @since    7.0.0
	 */
	protected $defaults;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void  A string if successful, otherwise a JError object.
	 *
	 * @throws \Exception
	 * @since   11.1
	 * @see     fetch()
	 */
	public function display($tpl = null)
	{
		$this->form = $this->get("Form");
		$item       = $this->get("Item");

		if ((int) $item->id === 0)
		{
			jimport('joomla.client.helper');
			jimport('joomla.filesystem.file');
			ClientHelper::setCredentialsFromRequest('ftp');
			$ftp               = ClientHelper::getCredentials('ftp');
			$file              = JPATH_ADMINISTRATOR . '/components/com_proclaim/helpers/defaulttemplatecode.php';
			$this->defaultcode = file_get_contents($file);
		}

		$this->type = null;

		if ($item->id !== 0)
		{
			$this->type = $this->get('Type');
		}

		$this->item  = $item;
		$this->state = $this->get("State");
		$this->canDo = CWMProclaimHelper::getActions($this->item->id, 'templatecode');

		$this->setLayout("edit");
		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add Toolbar
	 *
	 * @return void
	 *
	 * @since 7.0.0
	 */
	protected function addToolbar()
	{
		$input = new Input;
		$input->set('hidemainmenu', true);
		$isNew = ((int) $this->item->id === 0);
		$title = $isNew ? Text::_('JBS_CMN_NEW') : Text::_('JBS_CMN_EDIT');
		ToolbarHelper::title(Text::_('JBS_CMN_TEMPLATECODE') . ': <small><small>[' . $title . ']</small></small>', 'file file');

		if ($isNew && $this->canDo->get('core.create', 'com_proclaim'))
		{
			ToolbarHelper::apply('cwmtemplatecode.apply');
			ToolbarHelper::save('cwmtemplatecode.save');
			ToolbarHelper::save2new('cwmtemplatecode.save2new');
			ToolbarHelper::cancel('cwmtemplatecode.cancel');
		}
		else
		{
			if ($this->canDo->get('core.edit', 'com_proclaim'))
			{
				ToolbarHelper::apply('cwmtemplatecode.apply');
				ToolbarHelper::save('cwmtemplatecode.save');
				ToolbarHelper::save2copy('cwmtemplatecode.save2copy');
			}

			ToolbarHelper::cancel('cwmtemplatecode.cancel', 'JTOOLBAR_CLOSE');
		}

		ToolbarHelper::divider();
		ToolbarHelper::help('templatecodehelp', true);
	}

	/**
	 * Add the page title to browser.
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since    7.1.0
	 */
	protected function setDocument()
	{
		$isNew    = ($this->item->id < 1);
		$document = Factory::getApplication()->getDocument();
		$document->setTitle(
			$isNew ? Text::_('JBS_TITLE_TEMPLATECODES_CREATING')
				: Text::sprintf('JBS_TITLE_TEMPLATECODES_EDITING', $this->item->topic_text)
		);
	}
}
