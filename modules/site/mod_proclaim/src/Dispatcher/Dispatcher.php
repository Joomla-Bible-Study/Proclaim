<?php

/**
 * @package         Proclaim
 * @subpackage      mod.proclaim
 * @copyright   (C) 2007 CWM Team All rights reserved
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

// Always load Proclaim API if it exists.
$api = JPATH_ADMINISTRATOR . '/components/com_proclaim/api.php';

if (!\defined('BIBLESTUDY_COMPONENT_NAME')) {
    require_once $api;
}

/**
 * Dispatcher class for mod_articles_latest
 *
 * @since  4.2.0
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
     * @since   4.2.0
     */
    protected function getLayoutData(): array
    {
        $data = parent::getLayoutData();


        $templatemenuid = $data['params']->get('t');

        try {
            $data['cwmtemplate'] = Cwmparams::getTemplateparams($templatemenuid);
        } catch (\Exception $e) {
            $this->app->enqueueMessage($e, 'error');
        }

        $pagebuilder = new Cwmpagebuilder();

        $admin = Cwmparams::getAdmin();
        /** @var Registry $admin_params */
        $admin_params = $admin->params;
        $admin_params->merge($data['cwmtemplate']->params);
        $admin_params->merge($data['params']);
        $data['params'] = $admin_params;


        $data['list'] = $this->getHelperFactory()->getHelper('ProclaimHelper')->getLatest($data['params'], $this->getApplication());

        if (
            $data['params']->get('useexpert_module') > 0 || is_string(
                $data['params']->get('moduletemplate')
            ) === true
        ) {
            foreach ($data['list'] as $item) {
                try {
                    $pelements = $pagebuilder->buildPage($item, $data['params'], $data['cwmtemplate']);
                } catch (\Exception $e) {
                    $this->app->enqueueMessage($e, 'error');
                }

                $item->scripture1 = $pelements->scripture1;
                $item->scripture2 = $pelements->scripture2;
                $item->media      = $pelements->media;

                if (isset($pelements->duration)) {
                    $item->duration = $pelements->duration;
                } else {
                    $item->duration = null;
                }

                if (isset($pelements->studydate)) {
                    $item->studydate = $pelements->studydate;
                } else {
                    $item->studydate = null;
                }

                $item->topics = $pelements->topics;

                if (isset($pelements->study_thumbnail)) {
                    $item->study_thumbnail = $pelements->study_thumbnail;
                } else {
                    $item->study_thumbnail = null;
                }

                if (isset($pelements->series_thumbnail)) {
                    $item->series_thumbnail = $pelements->series_thumbnail;
                } else {
                    $item->series_thumbnail = null;
                }

                $item->detailslink = $pelements->detailslink;
            }
        }

        $link_text = $data['params']->get('pagetext');

        $input = $this->input;

        if (!$templatemenuid) {
            $templatemenuid = $input->getInt('templatemenuid', 1);
        }

        $linkurl      = Route::_('index.php?option=com_proclaim&view=cwmsermons&t=' . $templatemenuid);
        $data['link'] = '<a href="' . $linkurl . '"><button class="btn btn-primary">' . $link_text . '</button></a>';

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
