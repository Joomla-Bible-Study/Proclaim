<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMTemplateCode;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Client\ClientHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View class for TemplateCode
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class HtmlView extends BaseHtmlView
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
     * @var mixed
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
    public function display($tpl = null): void
    {
        $this->form = $this->get("Form");
        $item       = $this->get("Item");

        if ((int)$item->id === 0) {
            ClientHelper::setCredentialsFromRequest('ftp');
            $ftp               = ClientHelper::getCredentials('ftp');
            $file              = JPATH_ADMINISTRATOR . '/components/com_proclaim/helpers/defaulttemplatecode.php';
            $this->defaultcode = file_get_contents($file);
        }

        $this->type = null;

        if ($item->id !== 0) {
            $this->type = $this->get('Type');
        }

        $this->item  = $item;
        $this->state = $this->get("State");
        $this->canDo = ContentHelper::getActions('com_proclaim', 'templatecode', (int)$this->item->id);

        $this->setLayout("edit");
        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Add Toolbar
     *
     * @return void
     *
     * @throws \Exception
     * @since 7.0.0
     */
    protected function addToolbar(): void
    {
        Factory::getApplication()->input->set('hidemainmenu', true);
        $isNew = ((int)$this->item->id === 0);
        $title = $isNew ? Text::_('JBS_CMN_NEW') : Text::_('JBS_CMN_EDIT');
        ToolbarHelper::title(
            Text::_('JBS_CMN_TEMPLATECODE') . ': <small><small>[' . $title . ']</small></small>',
            'file'
        );

        if ($isNew && $this->canDo->get('core.create', 'com_proclaim')) {
            ToolbarHelper::apply('cwmtemplatecode.apply');
            ToolbarHelper::save('cwmtemplatecode.save');
            ToolbarHelper::save2new('cwmtemplatecode.save2new');
            ToolbarHelper::cancel('cwmtemplatecode.cancel');
        } else {
            if ($this->canDo->get('core.edit', 'com_proclaim')) {
                ToolbarHelper::apply('cwmtemplatecode.apply');
                ToolbarHelper::save('cwmtemplatecode.save');
                ToolbarHelper::save2copy('cwmtemplatecode.save2copy');
            }

            ToolbarHelper::cancel('cwmtemplatecode.cancel', 'JTOOLBAR_CLOSE');
        }

        ToolbarHelper::divider();

        ToolbarHelper::help('templatecode', true);
    }
}
