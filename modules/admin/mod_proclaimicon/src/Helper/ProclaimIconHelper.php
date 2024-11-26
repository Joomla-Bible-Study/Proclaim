<?php

/**
 * @package     Proclaim.Admin
 * @subpackage  mod_proclaimicon
 *
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Module\Proclaimicon\Administrator\Helper;

use CWM\Component\Proclaim\Administrator\Helper\Cwmhelper;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for mod_proclaimicon
 *
 * @since  10.0.0
 */
class ProclaimIconHelper
{
    /**
     * Stack to hold buttons
     *
     * @var     array[]
     * @since   10.0.0
     */
    protected array $buttons = [];

    /**
     * Helper method to return a button list.
     *
     * This method returns the array by reference so it can be
     * used to add custom buttons or remove default ones.
     *
     * @param   Registry         $params       The module parameters
     * @param   ?CMSApplication  $application  The application
     *
     * @return  array  An array of buttons
     *
     * @since   10.0.0
     */
    public function getButtons(Registry $params, CMSApplicationInterface $application = null)
    {
        if ($application === null) {
            try {
                $application = Factory::getApplication();
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }

        $simple = Cwmhelper::getSimpleView();

        $key     = (string) $params;
        if (!isset($this->buttons[$key])) {
            // Load mod_proclaimicon language file in case this method is called before rendering the module
            $application->getLanguage()->load('mod_proclaimicon');

            $this->buttons[$key] = [];

            if ($params->get('show_messages')) {
                $tmp = [
                    'image'   => 'icon-big icon-book fa-3x',
                    'link'    => Route::_('index.php?option=com_proclaim&amp;view=cwmmessages'),
                    'linkadd' => Route::_('index.php?option=com_proclaim&amp;task=cwmmessage.add'),
                    'name'    => 'JBS_CMN_STUDIES',
                    'access'  => ['core.manage', 'com_proclaim', 'core.create', 'com_proclaim'],
                    'group'   => 'MOD_QUICKICON_SITE',
                ];

                if ((int) $params->get('show_messages') === 2) {
                    $tmp['ajaxurl'] = 'index.php?option=com_proclaim&amp;task=cwmmessages.getQuickIconMessage&amp;format=json';
                }

                $this->buttons[$key][] = $tmp;
            }

            if ($params->get('show_mediafiles')) {
                $tmp = [
                    'image'   => 'icon-big icon-video fa-3x',
                    'link'    => Route::_('index.php?option=com_proclaim&amp;view=cwmmediafiles'),
                    'linkadd' => Route::_('index.php?option=com_proclaim&amp;task=cwmmediafile.add'),
                    'name'    => 'JBS_CMN_MEDIA_FILES',
                    'access'  => ['core.manage', 'com_proclaim', 'core.create', 'com_proclaim'],
                    'group'   => 'MOD_QUICKICON_SITE',
                ];

                if ((int) $params->get('show_mediafiles') === 2) {
                    $tmp['ajaxurl'] = 'index.php?option=com_proclaim&amp;task=cwmmediafiles.getQuickIconMediaFiles&amp;format=json';
                }

                $this->buttons[$key][] = $tmp;
            }

            if ($params->get('show_teachers')) {
                $tmp = [
                    'image'   => 'icon-user icon-big fa-3x',
                    'link'    => Route::_('index.php?option=com_proclaim&amp;view=cwmteachers'),
                    'linkadd' => Route::_('index.php?option=com_proclaim&amp;task=cwmteacher.add'),
                    'name'    => 'JBS_CMN_TEACHERS',
                    'access'  => ['core.manage', 'com_proclaim', 'core.create', 'com_proclaim'],
                    'group'   => 'MOD_QUICKICON_SITE',
                ];

                if ((int) $params->get('show_teachers') === 2) {
                    $tmp['ajaxurl'] = 'index.php?option=com_proclaim&amp;task=cwmteachers.getQuickIconTeachers&amp;format=json';
                }

                $this->buttons[$key][] = $tmp;
            }

            if ($params->get('show_series')) {
                $tmp = [
                    'image'   => 'icon-big icon-tree-2 fa-3x',
                    'link'    => Route::_('index.php?option=com_proclaim&amp;view=cwmseries'),
                    'linkadd' => Route::_('index.php?option=com_proclaim&amp;task=cwmserie.add'),
                    'name'    => 'JBS_CMN_SERIES',
                    'access'  => ['core.manage', 'com_proclaim', 'core.create', 'com_proclaim'],
                    'group'   => 'MOD_QUICKICON_SITE',
                ];

                if ((int) $params->get('show_series') === 2) {
                    $tmp['ajaxurl'] = 'index.php?option=com_proclaim&amp;task=cwmseries.getQuickIconSeries&amp;format=json';
                }

                $this->buttons[$key][] = $tmp;
            }

            if ($params->get('show_messagetypes') && !$simple->mode) {
                $tmp = [
                    'image'   => 'icon-big icon-list-2 fa-3x',
                    'link'    => Route::_('index.php?option=com_proclaim&amp;view=cwmmessagetypes'),
                    'linkadd' => Route::_('index.php?option=com_proclaim&amp;task=cwmmessagetype.add'),
                    'name'    => 'JBS_CMN_MESSAGETYPES',
                    'access'  => ['core.manage', 'com_proclaim', 'core.create', 'com_proclaim'],
                    'group'   => 'MOD_QUICKICON_SITE',
                ];

                if ((int) $params->get('show_messagetypes') === 2) {
                    $tmp['ajaxurl'] = 'index.php?option=com_proclaim&amp;task=cwmmessagetypes.getQuickIconMessageTypes&amp;format=json';
                }

                $this->buttons[$key][] = $tmp;
            }

            if ($params->get('show_locations') && !$simple->mode) {
                $tmp = [
                    'image'   => 'icon-big icon-home fa-3x',
                    'link'    => Route::_('index.php?option=com_proclaim&amp;view=cwmlocations'),
                    'linkadd' => Route::_('index.php?option=com_proclaim&amp;task=cwmlocation.add'),
                    'name'    => 'JBS_CMN_LOCATIONS',
                    'access'  => ['core.manage', 'com_proclaim', 'core.create', 'com_proclaim'],
                    'group'   => 'MOD_QUICKICON_SITE',
                ];

                if ((int) $params->get('show_locations') === 2) {
                    $tmp['ajaxurl'] = 'index.php?option=com_proclaim&amp;task=cwmlocations.getQuickIconLocations&amp;format=json';
                }

                $this->buttons[$key][] = $tmp;
            }

            if ($params->get('show_topics') && !$simple->mode) {
                $tmp = [
                    'image'   => 'icon-big icon-tags fa-3x',
                    'link'    => Route::_('index.php?option=com_proclaim&amp;view=cwmtopics'),
                    'linkadd' => Route::_('index.php?option=com_proclaim&amp;task=cwmtopic.add'),
                    'name'    => 'JBS_CMN_TOPICS',
                    'access'  => ['core.manage', 'com_proclaim', 'core.create', 'com_proclaim'],
                    'group'   => 'MOD_QUICKICON_SITE',
                ];

                if ((int) $params->get('show_topics') === 2) {
                    $tmp['ajaxurl'] = 'index.php?option=com_proclaim&amp;task=cwmtopics.getQuickIconTopics&amp;format=json';
                }

                $this->buttons[$key][] = $tmp;
            }

            if ($params->get('show_comments') && !$simple->mode) {
                $tmp = [
                    'image'   => 'icon-big icon-comments-2 fa-3x',
                    'link'    => Route::_('index.php?option=com_proclaim&amp;view=cwmcomments'),
                    'linkadd' => Route::_('index.php?option=com_proclaim&amp;task=cwmcomment.add'),
                    'name'    => 'JBS_CMN_COMMENTS',
                    'access'  => ['core.manage', 'com_proclaim', 'core.create', 'com_proclaim'],
                    'group'   => 'MOD_QUICKICON_SITE',
                ];

                if ((int) $params->get('show_comments') === 2) {
                    $tmp['ajaxurl'] = 'index.php?option=com_proclaim&amp;task=cwmcomments.getQuickIconComments&amp;format=json';
                }

                $this->buttons[$key][] = $tmp;
            }

            if ($params->get('show_servers')) {
                $tmp = [
                    'image'   => 'icon-big icon-database fa-3x',
                    'link'    => Route::_('index.php?option=com_proclaim&amp;view=cwmservers'),
                    'linkadd' => Route::_('index.php?option=com_proclaim&amp;task=cwmserver.add'),
                    'name'    => 'JBS_CMN_SERVERS',
                    'access'  => ['core.manage', 'com_proclaim', 'core.create', 'com_proclaim'],
                    'group'   => 'MOD_QUICKICON_SITE',
                ];

                if ((int) $params->get('show_servers') === 2) {
                    $tmp['ajaxurl'] = 'index.php?option=com_proclaim&amp;task=cwmservers.getQuickIconServers&amp;format=json';
                }

                $this->buttons[$key][] = $tmp;
            }

            if ($params->get('show_podcasts')) {
                $tmp = [
                    'image'   => 'icon-big fa-solid fa-podcast fa-3x',
                    'link'    => Route::_('index.php?option=com_proclaim&amp;view=cwmpodcasts'),
                    'linkadd' => Route::_('index.php?option=com_proclaim&amp;task=cwmpodcast.add'),
                    'name'    => 'JBS_CMN_PODCASTS',
                    'access'  => ['core.manage', 'com_proclaim', 'core.create', 'com_proclaim'],
                    'group'   => 'MOD_QUICKICON_SITE',
                ];

                if ((int) $params->get('show_podcasts') === 2) {
                    $tmp['ajaxurl'] = 'index.php?option=com_proclaim&amp;task=cwmpodcasts.getQuickIconPodcasts&amp;format=json';
                }

                $this->buttons[$key][] = $tmp;
            }

            if ($params->get('show_templates') && !$simple->mode) {
                $tmp = [
                    'image'   => 'icon-big icon-grid fa-3x',
                    'link'    => Route::_('index.php?option=com_proclaim&amp;view=cwmtemplates'),
                    'linkadd' => Route::_('index.php?option=com_proclaim&amp;task=cwmtemplate.add'),
                    'name'    => 'JBS_CMN_TEMPLATES',
                    'access'  => ['core.manage', 'com_proclaim', 'core.create', 'com_proclaim'],
                    'group'   => 'MOD_QUICKICON_SITE',
                ];

                if ((int) $params->get('show_templates') === 2) {
                    $tmp['ajaxurl'] = 'index.php?option=com_proclaim&amp;task=cwmtemplates.getQuickIconTemplates&amp;format=json';
                }

                $this->buttons[$key][] = $tmp;
            }

            if ($params->get('show_templatecodes') && !$simple->mode) {
                $tmp = [
                    'image'   => 'icon-big fa-solid fa-file-code fa-3x',
                    'link'    => Route::_('index.php?option=com_proclaim&amp;view=cwmtemplatecodes'),
                    'linkadd' => Route::_('index.php?option=com_proclaim&amp;task=cwmtemplatecode.add'),
                    'name'    => 'JBS_CMN_TEMPLATECODES',
                    'access'  => ['core.manage', 'com_proclaim', 'core.create', 'com_proclaim'],
                    'group'   => 'MOD_QUICKICON_SITE',
                ];

                if ((int) $params->get('show_templatecodes') === 2) {
                    $tmp['ajaxurl'] = 'index.php?option=com_proclaim&amp;task=cwmtemplatecodes.getQuickIconTemplateCodes&amp;format=json';
                }

                $this->buttons[$key][] = $tmp;
            }

            if ($params->get('show_admin')) {
                $tmp = [
                    'image'   => 'icon-big icon-options fa-3x',
                    'link'    => Route::_('index.php?option=com_proclaim&amp;task=cwmadmin.edit&amp;id=1'),
                    'name'    => 'JBS_CMN_ADMINISTRATION',
                    'access'  => ['core.manage', 'com_proclaim', 'core.create', 'com_proclaim'],
                    'group'   => 'MOD_QUICKICON_SITE',
                ];

                $this->buttons[$key][] = $tmp;
            }
        }

        return $this->buttons[$key];
    }
}
