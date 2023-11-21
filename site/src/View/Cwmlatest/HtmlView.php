<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\View\Cwmlatest;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

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
    public function display($tpl = null): void
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery('true');
        $query->select('id')
            ->from('#__bsms_studies')
            ->where('published = 1')
            ->order('studydate DESC LIMIT 1');
        $db->setQuery($query);
        $id    = $db->loadResult();
        $input = Factory::getApplication()->input;
        $t     = $input->get('t', '1');

        // @todo move to slug asap
        $link = Route::_('index.php?option=com_proclaim&view=cwmsermon&id=' . $id . '&t=' . $t);
        $app  = Factory::getApplication();

        $app->redirect($link);
    }
}
