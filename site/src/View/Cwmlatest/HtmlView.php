<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\View\Cwmlatest;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Site\Model\CwmlatestModel;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;

/**
 * View class for Latest
 *
 * @package  Proclaim.Site
 * @since    7.1.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @throws \Exception
     * @since 7.0
     */
    #[\Override]
    public function display($tpl = null): void
    {
        /** @var CwmlatestModel $model */
        $model = $this->getModel();
        $id    = $model->getLatestStudyId();

        $app   = Factory::getApplication();
        $input = $app->getInput();
        $t     = $input->get('t', '1');

        // @todo move to slug asap, this will require a new query to load both alias and ID.
        $link = Route::_('index.php?option=com_proclaim&view=cwmsermon&id=' . $id . '&t=' . $t);

        $app->redirect($link);
    }
}
