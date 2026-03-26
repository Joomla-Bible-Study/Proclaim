<?php

/**
 * View html
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\Cwmarchive;

// Check to ensure this file is included in Joomla!
use CWM\Component\Proclaim\Administrator\Model\CwmarchiveModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View class for Archive
 *
 * @package  Proclaim.Admin
 * @since    9.0.1
 */
class HtmlView extends BaseHtmlView
{
    public mixed $form;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void  A string if successful, otherwise a JError object.
     *
     * @throws \Exception
     * @since  11.1
     * @see    ViewLegacy::loadTemplate()
     */
    #[\Override]
    public function display($tpl = null): void
    {
        /** @var CwmarchiveModel $model */
        $model = $this->getModel();
        $model->setUseExceptions(true);

        $this->form = $model->getForm();

        $this->setLayout('edit');

        // Set the toolbar
        $this->addToolbar();

        // Display the template
        parent::display($tpl);
    }

    /**
     * Add Toolbar
     *
     * @return void
     *
     * @throws \Exception
     * @since  7.0.0
     */
    protected function addToolbar(): void
    {
        Factory::getApplication()->getInput()->set('hidemainmenu', true);

        ToolbarHelper::title(Text::_('JBS_CMN_ARCHIVE'), 'archive');

        $toolbar = Toolbar::getInstance();

        // Add back to admin tools button
        $toolbar->linkButton('back', 'JTOOLBAR_BACK')
            ->url('index.php?option=com_proclaim&view=cwmadmin')
            ->icon('fa-solid fa-arrow-left')
            ->listCheck(false);

        ToolbarHelper::help('cwmarchive', true);
    }
}
