<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Health\Check;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Health\HealthCheckInterface;
use CWM\Component\Proclaim\Administrator\Health\HealthGroup;
use CWM\Component\Proclaim\Administrator\Health\HealthResult;
use CWM\Component\Proclaim\Administrator\Health\HealthStatus;
use CWM\Component\Proclaim\Administrator\Helper\CwmmediaProtectionHelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmprotectedStorage;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;

/**
 * What the web server was last seen doing with the protected media directory.
 *
 * ⚠️ Reads the stored verdict. It deliberately does not call
 * `CwmprotectedStorage::status()`, which re-probes when the verdict is stale —
 * that is an HTTP round trip to this same site, and a passive check runs on
 * every render of the Administration screen.
 *
 * ⚠️ Anything other than PROTECTED is a warning, including UNVERIFIED. That is
 * the rule the helper states for itself: an unanswered probe is what a broken
 * deny rule looks like from here, so it is never read as success.
 *
 * @since  10.6.0
 */
final class ProtectedStorageCheck implements HealthCheckInterface
{
    /**
     * @inheritDoc
     *
     * @since  10.6.0
     */
    public function getId(): string
    {
        return 'security.protected-storage';
    }

    /**
     * @inheritDoc
     *
     * @since  10.6.0
     */
    public function getGroup(): HealthGroup
    {
        return HealthGroup::Security;
    }

    /**
     * @inheritDoc
     *
     * @since  10.6.0
     */
    public function getTitle(): string
    {
        return Text::_('JBS_HEALTH_PROTECTED_STORAGE');
    }

    /**
     * @inheritDoc
     *
     * @since  10.6.0
     */
    public function isPassive(): bool
    {
        return true;
    }

    /**
     * The sentence for a folder that is not confirmed protected.
     *
     * @param   string  $stored  The recorded verdict.
     * @param   bool    $holds   Whether the folder holds any media of its own.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    private function describe(string $stored, bool $holds): string
    {
        if (!$holds) {
            return Text::_('JBS_HEALTH_PROTECTED_STORAGE_EMPTY');
        }

        if ($stored === CwmmediaProtectionHelper::EXPOSED) {
            return Text::_('JBS_HEALTH_PROTECTED_STORAGE_EXPOSED')
                . ' ' . Text::_('JBS_HEALTH_PROTECTED_STORAGE_FIX');
        }

        return Text::_('JBS_HEALTH_PROTECTED_STORAGE_UNVERIFIED')
            . ' ' . Text::_('JBS_HEALTH_PROTECTED_STORAGE_UNVERIFIED_FIX');
    }

    /**
     * @inheritDoc
     *
     * @since  10.6.0
     */
    public function run(): HealthResult
    {
        $params  = ComponentHelper::getParams('com_proclaim');
        $stored  = (string) $params->get(CwmprotectedStorage::STATUS_PARAM, '');
        $checked = (int) $params->get(CwmprotectedStorage::CHECKED_PARAM, 0);

        // Never probed. Not a fault yet, but nothing here has been established
        // either, so it cannot be reported as safe.
        if ($stored === '') {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Unknown,
                Text::_('JBS_HEALTH_PROTECTED_STORAGE_NEVER')
                . ' ' . Text::_('JBS_HEALTH_PROTECTED_STORAGE_NEVER_FIX'),
                'never',
                'index.php?option=com_proclaim&view=cwmmediafiles&filter[restricted]=1',
                Text::_('JBS_HEALTH_PROTECTED_STORAGE_ACTION')
            );
        }

        // ⚠️ Severity follows what is actually at stake. With nothing stored in
        // the folder there is no exposure to warn about — the deny rules being
        // unproven is a readiness question, worth reporting before anyone
        // relies on them, but not a fault. Reported as a Warning regardless,
        // this sat orange on every install for a folder holding only its own
        // guard files, and on a local or firewalled site the probe can never
        // succeed, so it never cleared.
        $holds_files  = CwmprotectedStorage::holdsAnything();

        if ($stored !== CwmmediaProtectionHelper::PROTECTED) {
            return new HealthResult(
                $this->getId(),
                $holds_files ? HealthStatus::Warning : HealthStatus::Notice,
                // ⚠️ One statement, not a warning with a disclaimer bolted on.
                // Stacking "not treated as safe" against "nothing is exposed"
                // made a paragraph that alarms and then withdraws it; the empty
                // case gets its own calm wording instead.
                $this->describe($stored, $holds_files),
                // The verdict, so quietening an EXPOSED site does not also
                // quieten it once the verdict changes to something else.
                $stored . ':' . ($holds_files ? 'holding' : 'empty'),
                'index.php?option=com_proclaim&view=cwmmediafiles&filter[restricted]=1',
                Text::_('JBS_HEALTH_PROTECTED_STORAGE_ACTION')
            );
        }

        if (CwmprotectedStorage::isRecheckDue($checked, time())) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Notice,
                Text::_('JBS_HEALTH_PROTECTED_STORAGE_STALE')
                . ' ' . Text::_('JBS_HEALTH_PROTECTED_STORAGE_STALE_FIX'),
                'stale',
                'index.php?option=com_proclaim&view=cwmmediafiles&filter[restricted]=1',
                Text::_('JBS_HEALTH_PROTECTED_STORAGE_ACTION')
            );
        }

        return new HealthResult(
            $this->getId(),
            HealthStatus::Ok,
            Text::_('JBS_HEALTH_PROTECTED_STORAGE_OK')
        );
    }
}
