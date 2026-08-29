<?php

/**
 * View html
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\Cwmcpanel;

use CWM\Component\Proclaim\Administrator\Health\Check\LegacyServersCheck;
use CWM\Component\Proclaim\Administrator\Health\HealthQuietStore;
use CWM\Component\Proclaim\Administrator\Helper\CwmcountHelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmserverMigrationHelper;
use CWM\Component\Proclaim\Administrator\Lib\Cwmstats;
use CWM\Component\Proclaim\Administrator\Model\CwmcpanelModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * HtmlView class for Cpanel
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Data from Model
     *
     * @var      \SimpleXMLElement|false|null
     * @since    7.0.0
     */
    public \SimpleXMLElement|false|null $xml = null;

    /**
     * Total Messages
     *
     * @var      string
     * @since    7.0.0
     */
    public string $total_messages;

    /**
     * Count of studies submitted through the API and awaiting review.
     *
     * Surfaced as a dashboard notice for users who can publish, so content
     * submitted by non-editors does not sit unnoticed. Counts only records the
     * API marked, not everything unpublished -- a draft or a retired sermon is
     * not awaiting anything. 0 when the current user lacks core.edit.state.
     *
     * @var    int
     * @since  10.3.3
     */
    public int $pendingReview = 0;

    /**
     * Legacy servers still awaiting migration, and the media rows on them.
     *
     * Both zero when there is nothing to do, which is what the template gates
     * on. A restored 9.x backup arrives with every server still `legacy`, and
     * until they are migrated most media will not resolve.
     *
     * @var    array{servers: int, media: int}
     * @since  10.6.0
     */
    public array $pendingServerMigration = ['servers' => 0, 'media' => 0];

    /**
     * Whether the server migration notice has been cleared on the dashboard.
     *
     * Cleared against the finding rather than for good: the stored fingerprint
     * is the server and media counts, so migrating some of them -- or a
     * restore adding more -- brings the notice straight back. The System
     * Health view lists it either way.
     *
     * @var    bool
     * @since  10.6.0
     */
    public bool $serverMigrationQuiet = false;

    /**
     * The model state
     *
     * @var      ?Registry
     * @since    10.0.0
     */
    protected ?Registry $state = null;

    /**
     * Post Installation Messages
     *
     * @var    bool
     * @since  7.0.0
     */
    protected bool $hasPostInstallationMessages;

    /**
     * Extension ID
     *
     * @var    int
     * @since  7.0.0
     */
    protected int $extension_id;

    /**
     * Display
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void  A string if successful, otherwise an Error object.
     *
     * @throws \Exception
     * @since    7.0.0
     */
    #[\Override]
    public function display($tpl = null): void
    {
        /** @var CwmcpanelModel $model */
        $model       = Factory::getApplication()->bootComponent('com_proclaim')
            ->getMVCFactory()->createModel('Cwmcpanel', 'Administrator');
        $component   = JPATH_ADMINISTRATOR . '/components/com_proclaim/proclaim.xml';
        $model->setUseExceptions(true);
        $this->state = $model->getState();

        if (file_exists($component)) {
            $this->xml = simplexml_load_string(file_get_contents($component));
        }

        $this->total_messages = Cwmstats::getTotalMessages();

        $this->hasPostInstallationMessages = $model->hasPostInstallMessages();
        $this->extension_id                = ComponentHelper::getComponent('com_proclaim')->id;

        // Editorial review notice: count unpublished studies, but only for users
        // who can actually publish them. Location-filtered so multi-campus
        // editors only see pending content they can act on.
        $user = Factory::getApplication()->getIdentity();

        if ($user && $user->authorise('core.edit.state', 'com_proclaim')) {
            $this->pendingReview = CwmcountHelper::getPendingReviewCount('location');
        }

        // Migrating servers is a component-wide change, so the notice is only
        // for those who could act on it. The count itself asks nothing about
        // the user -- that check belongs here, at the point of display.
        if ($user && $user->authorise('core.admin', 'com_proclaim')) {
            $this->pendingServerMigration = CwmserverMigrationHelper::countPendingMigration();
            $this->serverMigrationQuiet   = HealthQuietStore::isQuiet((new LegacyServersCheck())->run());
        }

        // Display the template
        parent::display($tpl);
    }
}
