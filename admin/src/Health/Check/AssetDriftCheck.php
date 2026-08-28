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
use CWM\Component\Proclaim\Administrator\Lib\Cwmassets;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

/**
 * Whether Proclaim's rows in `#__assets` still line up with its records.
 *
 * ⚠️ Drift presents as a 404 or a permission refusal on a record that looks
 * fine in the list, because the broken part is an `#__assets` row rather than
 * anything on the record itself. A record with `asset_id = 0` is not drift —
 * that is the normal state for one carrying no per-record rules.
 *
 * @since  __DEPLOY_VERSION__
 */
final class AssetDriftCheck implements HealthCheckInterface
{
    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getId(): string
    {
        return 'security.asset-drift';
    }

    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getGroup(): HealthGroup
    {
        return HealthGroup::Security;
    }

    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getTitle(): string
    {
        return Text::_('JBS_HEALTH_ASSET_DRIFT');
    }

    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function isPassive(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function run(): HealthResult
    {
        try {
            $db       = Factory::getContainer()->get(DatabaseInterface::class);
            $parentId = $this->readParentAssetId($db);

            // ⚠️ Deliberately not Cwmassets::parentId(): that falls through to
            // ensureParentAsset() and creates the row when it is missing. A
            // report has no business writing, and a missing parent is itself
            // the finding.
            if ($parentId < 1) {
                return new HealthResult(
                    $this->getId(),
                    HealthStatus::Warning,
                    Text::_('JBS_HEALTH_ASSET_DRIFT_NO_PARENT'),
                    'no-parent',
                    'index.php?option=com_proclaim&view=cwmassets',
                    Text::_('JBS_HEALTH_ASSET_DRIFT_ACTION')
                );
            }

            if (!Cwmassets::hasAnyDrift($db, $parentId)) {
                return new HealthResult(
                    $this->getId(),
                    HealthStatus::Ok,
                    Text::_('JBS_HEALTH_ASSET_DRIFT_OK')
                );
            }
        } catch (\Exception $e) {
            // The probe runs several counts across the content tables. If one
            // cannot answer, say so rather than reporting a clean site.
            return new HealthResult(
                $this->getId(),
                HealthStatus::Unknown,
                Text::_('JBS_HEALTH_ASSET_DRIFT_UNREADABLE')
            );
        }

        return new HealthResult(
            $this->getId(),
            HealthStatus::Warning,
            Text::_('JBS_HEALTH_ASSET_DRIFT_FOUND'),
            // hasAnyDrift() bails at the first hit, so there is no count to
            // fingerprint — only whether drift is present at all.
            'drift',
            'index.php?option=com_proclaim&view=cwmassets',
            Text::_('JBS_HEALTH_ASSET_DRIFT_ACTION')
        );
    }

    /**
     * The com_proclaim parent asset id, without creating it.
     *
     * @param   DatabaseInterface  $db  Database driver
     *
     * @return  int  The id, or 0 when the row is absent
     *
     * @since   __DEPLOY_VERSION__
     */
    private function readParentAssetId(DatabaseInterface $db): int
    {
        $query = $db->createQuery()
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__assets'))
            ->where($db->quoteName('name') . ' = ' . $db->quote('com_proclaim'));
        $db->setQuery($query);

        return (int) $db->loadResult();
    }
}
