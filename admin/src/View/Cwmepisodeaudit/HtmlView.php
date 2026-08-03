<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Administrator\View\Cwmepisodeaudit;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Model\CwmepisodeauditModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Duplicate episode number audit report view.
 *
 * @package  Proclaim.Admin
 * @since    __DEPLOY_VERSION__
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Every series with more than one message sharing the same episode number.
     *
     * @var array
     * @since __DEPLOY_VERSION__
     */
    public array $duplicates = [];

    /**
     * Execute and display the audit report.
     *
     * @param   string  $tpl  Template override name.
     *
     * @return  void
     *
     * @throws \Exception
     * @since   __DEPLOY_VERSION__
     */
    #[\Override]
    public function display($tpl = null): void
    {
        /** @var CwmepisodeauditModel $model */
        $model = $this->getModel();

        $this->duplicates = $model->getDuplicates();

        ToolbarHelper::title(Text::_('JBS_EPA_EPISODE_AUDIT'), 'list');
        ToolbarHelper::back('JTOOLBAR_BACK', Route::_('index.php?option=com_proclaim'));

        parent::display($tpl);
    }
}
