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
use CWM\Component\Proclaim\Administrator\Table\CwmtemplatecodeTable;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

/**
 * Template code records whose layout file is not on disk.
 *
 * A record is only half the layout. `CwmtemplatecodeTable::store()` writes its
 * PHP out to a file under the component's tmpl folders, and the front end
 * renders that file — not the record. So a record can look present and edited
 * while the thing actually rendered is missing or has fallen back.
 *
 * ⚠️ The folders those files live in are pruned by the install script on
 * update, which is the same mechanism that loses CSS kept inside a record.
 * A site can therefore arrive here through an update that reported success.
 *
 * A record naming a type with no directory behind it is counted separately —
 * that one was never writable anywhere, rather than written and then lost.
 *
 * @since  __DEPLOY_VERSION__
 */
final class TemplateCodeFileCheck implements HealthCheckInterface
{
    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getId(): string
    {
        return 'filesystem.templatecode-files';
    }

    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getGroup(): HealthGroup
    {
        return HealthGroup::Filesystem;
    }

    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getTitle(): string
    {
        return Text::_('JBS_HEALTH_TEMPLATECODE_FILES');
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
            $db   = Factory::getContainer()->get(DatabaseInterface::class);
            $rows = $db->setQuery(
                $db->createQuery()
                    ->select($db->quoteName(['id', 'filename', 'type']))
                    ->from($db->quoteName('#__bsms_templatecode'))
            )->loadObjectList();
        } catch (\Exception) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Unknown,
                Text::_('JBS_HEALTH_TEMPLATECODE_FILES_UNREADABLE')
            );
        }

        $missing = 0;
        $unknown = 0;

        foreach ($rows as $row) {
            $path = CwmtemplatecodeTable::layoutPath(
                (int) $row->type,
                'default_' . $row->filename . '.php'
            );

            if ($path === null) {
                $unknown++;

                continue;
            }

            if (!is_file($path)) {
                $missing++;
            }
        }

        if ($missing === 0 && $unknown === 0) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Ok,
                Text::_('JBS_HEALTH_TEMPLATECODE_FILES_OK')
            );
        }

        return new HealthResult(
            $this->getId(),
            HealthStatus::Warning,
            $unknown > 0 && $missing === 0
                ? Text::sprintf('JBS_HEALTH_TEMPLATECODE_FILES_UNKNOWN_TYPE', $unknown)
                : Text::sprintf('JBS_HEALTH_TEMPLATECODE_FILES_MISSING', $missing),
            // Both counts, so a site that fixes one and not the other is still
            // told about the other.
            $missing . ':' . $unknown,
            'index.php?option=com_proclaim&view=cwmtemplatecodes',
            Text::_('JBS_HEALTH_TEMPLATECODE_FILES_ACTION')
        );
    }
}
