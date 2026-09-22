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
 * @since  10.6.0
 */
final class TemplateCodeFileCheck implements HealthCheckInterface
{
    /**
     * @inheritDoc
     *
     * @since  10.6.0
     */
    public function getId(): string
    {
        return 'filesystem.templatecode-files';
    }

    /**
     * @inheritDoc
     *
     * @since  10.6.0
     */
    public function getGroup(): HealthGroup
    {
        return HealthGroup::Filesystem;
    }

    /**
     * @inheritDoc
     *
     * @since  10.6.0
     */
    public function getTitle(): string
    {
        return Text::_('JBS_HEALTH_TEMPLATECODE_FILES');
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
     * @inheritDoc
     *
     * @since  10.6.0
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
        $invalid = 0;

        foreach ($rows as $row) {
            // ⚠️ Checked before the path, because both fail the same way --
            // a null path -- and reporting a bad filename as an unknown type
            // sends whoever reads this at the wrong column.
            if (!CwmtemplatecodeTable::isValidLayoutFilename($row->filename)) {
                $invalid++;

                continue;
            }

            $path = CwmtemplatecodeTable::layoutPathForRecord((int) $row->type, $row->filename);

            if ($path === null) {
                $unknown++;

                continue;
            }

            if (!is_file($path)) {
                $missing++;
            }
        }

        if ($missing === 0 && $unknown === 0 && $invalid === 0) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Ok,
                Text::_('JBS_HEALTH_TEMPLATECODE_FILES_OK')
            );
        }

        if ($invalid > 0) {
            $message = Text::sprintf('JBS_HEALTH_TEMPLATECODE_FILES_INVALID_NAME', $invalid);
        } elseif ($unknown > 0 && $missing === 0) {
            $message = Text::sprintf('JBS_HEALTH_TEMPLATECODE_FILES_UNKNOWN_TYPE', $unknown);
        } else {
            $message = Text::sprintf('JBS_HEALTH_TEMPLATECODE_FILES_MISSING', $missing);
        }

        return new HealthResult(
            $this->getId(),
            HealthStatus::Warning,
            $message,
            // Every count, so a site that fixes one and not the others is still
            // told about the others.
            $missing . ':' . $unknown . ':' . $invalid,
            'index.php?option=com_proclaim&view=cwmtemplatecodes',
            Text::_('JBS_HEALTH_TEMPLATECODE_FILES_ACTION')
        );
    }
}
