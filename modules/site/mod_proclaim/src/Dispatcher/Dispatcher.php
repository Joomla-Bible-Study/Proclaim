<?php

/**
 * @package         Proclaim
 * @subpackage      mod.proclaim
 * @copyright   (C) 2025 CWM Team All rights reserved
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 * @link            https://www.christianwebministries.org
 */

namespace CWM\Module\Proclaim\Site\Dispatcher;

use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use CWM\Component\Proclaim\Site\Helper\Cwmpagebuilder;
use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_proclaim
 *
 * @since  10.0.0
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @throws \Exception
     * @since   10.0.0
     */
    protected function getLayoutData(): array
    {
        if (!\defined('BIBLESTUDY_COMPONENT_NAME')) {
            require_once JPATH_ADMINISTRATOR . '/components/com_proclaim/api.php';
        }

        /** @var array $data */
        $data = parent::getLayoutData();


        $templateID = $data['params']->get('t');

        try {
            $data['cwmtemplate'] = Cwmparams::getTemplateparams($templateID);
        } catch (\Exception $e) {
            $this->app->enqueueMessage($e, 'error');
        }

        $pageBuilder = new Cwmpagebuilder();

        $admin = Cwmparams::getAdmin();
        /** @var Registry $admin_params */
        $admin_params = $admin->params;
        $admin_params->merge($data['cwmtemplate']->params);
        $admin_params->merge($data['params']);
        $data['params'] = $admin_params;

        $data['list'] = $this->getHelperFactory()
            ->getHelper('ProclaimHelper')
            ->getLatest($data['params'], $this->getApplication());

        if (
            $data['params']->get('useexpert_module') > 0 || is_string(
                $data['params']->get('moduletemplate')
            ) === true
        ) {
            foreach ($data['list'] as $item) {
                try {
                    $renderedPage = $pageBuilder->buildPage($item, $data['params'], $data['cwmtemplate']);
                } catch (\Exception $e) {
                    $this->app->enqueueMessage($e, 'error');
                }

                $item->scripture1 = $renderedPage->scripture1;
                $item->scripture2 = $renderedPage->scripture2;
                $item->media      = $renderedPage->media;

                if (isset($renderedPage->duration)) {
                    $item->duration = $renderedPage->duration;
                } else {
                    $item->duration = null;
                }

                if (isset($renderedPage->studydate)) {
                    $item->studydate = $renderedPage->studydate;
                } else {
                    $item->studydate = null;
                }

                $item->topics = $renderedPage->topics;

                if (isset($renderedPage->study_thumbnail)) {
                    $item->study_thumbnail = $renderedPage->study_thumbnail;
                } else {
                    $item->study_thumbnail = null;
                }

                if (isset($renderedPage->series_thumbnail)) {
                    $item->series_thumbnail = $renderedPage->series_thumbnail;
                } else {
                    $item->series_thumbnail = null;
                }

                $item->detailslink = $renderedPage->detailslink;
            }
        }

        $link_text = $data['params']->get('pagetext');

        $input = $this->input;

        if (!$templateID) {
            $templateID = $input->getInt('templateID', 1);
        }

        $routeUrl      = Route::_('index.php?option=com_proclaim&view=cwmsermons&t=' . $templateID);
        $data['link']  = '<a href="' . $routeUrl . '"><button class="btn btn-primary">' . $link_text . '</button></a>';

        $wa = $this->app->getDocument()->getWebAssetManager();
        $wa->useStyle('com_proclaim.cwmcore');
        $wa->useStyle('com_proclaim.general');

        $url = $data['params']->get('stylesheet');

        if ($url) {
            $wa->load($url);
        }

        if ($data['params']->get('simple_mode') === '1') {
            $data['params']->set('layout', 'default_simple');
        } elseif ($data['params']->get('moduletemplate') && !$data['params']->get('simple_mode')) {
            $data['params']->set('layout', 'default_' . $data['params']->get('moduletemplate'));
        } else {
            $data['params']->set('layout', 'default_main');
        }

        return $data;
    }
}
