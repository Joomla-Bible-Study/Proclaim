<?php

/**
 * @package         Proclaim
 * @subpackage      mod.proclaim
 * @copyright   (C) 2026 CWM Team All rights reserved
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 * @link            https://www.christianwebministries.org
 */

namespace CWM\Module\Proclaim\Site\Dispatcher;

use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use CWM\Component\Proclaim\Site\Helper\Cwmpagebuilder;
use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\CMS\Language\Text;
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
    #[\Override]
    protected function getLayoutData(): array
    {
        if (!\defined('CWM_LOADED')) {
            require_once JPATH_ADMINISTRATOR . '/components/com_proclaim/api.php';
        }

        /** @var array $data */
        $data = parent::getLayoutData();


        $templateID = $data['params']->get('t');

        try {
            $data['cwmtemplate'] = Cwmparams::getTemplateparams($templateID);
        } catch (\Exception $e) {
            $this->app->enqueueMessage($e->getMessage(), 'error');
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

        if ($data['params']->get('useexpert_module') > 0 || \is_string($data['params']->get('moduletemplate'))) {
            try {
                $pageBuilder->enrichStudies($data['list'], $data['params'], $data['cwmtemplate']);
            } catch (\Exception $e) {
                $this->app->enqueueMessage($e->getMessage(), 'error');
            }
        }

        $link_text = $data['params']->get('pagetext');

        $input = $this->input;

        if (!$templateID) {
            $templateID = $input->getInt('templateID', 1);
        }

        $routeUrl      = Route::_('index.php?option=com_proclaim&view=cwmsermons&t=' . $templateID);
        $data['link']  = '<a href="' . $routeUrl . '"><button class="btn btn-primary"><span class="fas fa-bible" aria-hidden="true"></span> ' . $link_text . '</button></a>';

        $wa = $this->app->getDocument()->getWebAssetManager();
        $wa->useStyle('com_proclaim.cwmcore');
        $wa->useStyle('com_proclaim.general');

        // Load scripture tooltip assets (per-element controlled; JS is a no-op
        // if no elements have show_tooltip enabled)
        $wa->useScript('lib_cwmscripture.scripture-tooltip');
        $wa->useStyle('lib_cwmscripture.scripture-tooltip');

        $this->app->getDocument()->addScriptOptions('com_proclaim.scripture', [
            'ajaxUrl' => Route::_(
                'index.php?option=com_proclaim&task=cwmscripture.getPassageXHR&format=raw',
                false
            ),
        ]);

        // Register language strings used by scripture-switcher JS
        Text::script('JBS_CMN_SCRIPTURE_UNAVAILABLE');
        Text::script('JBS_CMN_SCRIPTURE_RETRY');
        Text::script('JBS_CMN_SCRIPTURE_FALLBACK');
        Text::script('JBS_CMN_SCRIPTURE_SERVICE_BUSY');

        $url = $data['params']->get('stylesheet');

        if ($url) {
            $wa->registerAndUseStyle('mod_proclaim.custom', $url);
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
