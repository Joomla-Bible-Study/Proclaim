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
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/**
 * Schema.org rows stored against a context the edit form never reads.
 *
 * A teacher and a series each answer to two context names: the form name the
 * database is keyed on, and the model name Joomla's content events carry.
 * `#__schemaorg` is filtered by context on both read and write, so a row saved
 * under the model name is invisible to the form, which reads the form name.
 *
 * ⚠️ Nothing writes these today — the schemaorg plugin normalizes to the form
 * context and suppresses the core plugin's own write. Rows that exist came from
 * older code or an import, and they are not harmless: where one has been seen,
 * it held *more* than the row the form shows, so its extra fields are stored and
 * permanently unreachable.
 *
 * Reported rather than repaired. Which of the two rows is authoritative differs
 * per item, so merging them is a decision, not a rule.
 *
 * @since  10.7.1
 */
final class SchemaorgContextCheck implements HealthCheckInterface
{
    /**
     * Contexts the content events carry but the database is never keyed on.
     *
     * ⚠️ Mirrors `CONTEXT_CANONICAL` in the schemaorg plugin, which is private
     * to it. `SchemaorgContextCheckTest` fails if the two lists drift apart.
     *
     * @var    string[]
     * @since  10.7.1
     */
    private const NON_CANONICAL = [
        'com_proclaim.cwmteacher',
        'com_proclaim.cwmserie',
    ];

    /**
     * @inheritDoc
     *
     * @since  10.7.1
     */
    public function getId(): string
    {
        return 'database.schemaorg-context';
    }

    /**
     * @inheritDoc
     *
     * @since  10.7.1
     */
    public function getGroup(): HealthGroup
    {
        return HealthGroup::Database;
    }

    /**
     * @inheritDoc
     *
     * @since  10.7.1
     */
    public function getTitle(): string
    {
        return Text::_('JBS_HEALTH_SCHEMAORG_CONTEXT');
    }

    /**
     * @inheritDoc
     *
     * @since  10.7.1
     */
    public function isPassive(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     *
     * @since  10.7.1
     */
    public function run(): HealthResult
    {
        try {
            $db       = Factory::getContainer()->get(DatabaseInterface::class);
            $contexts = self::NON_CANONICAL;

            $query = $db->createQuery()
                ->select('COUNT(*)')
                ->from($db->quoteName('#__schemaorg'))
                ->whereIn($db->quoteName('context'), $contexts, ParameterType::STRING);
            $db->setQuery($query);

            $stranded = (int) $db->loadResult();
        } catch (\Exception) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Unknown,
                Text::_('JBS_HEALTH_SCHEMAORG_CONTEXT_UNREADABLE')
            );
        }

        if ($stranded === 0) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Ok,
                Text::_('JBS_HEALTH_SCHEMAORG_CONTEXT_NONE')
            );
        }

        return new HealthResult(
            $this->getId(),
            HealthStatus::Warning,
            ($stranded === 1
                ? Text::_('JBS_HEALTH_SCHEMAORG_CONTEXT_1')
                : Text::sprintf('JBS_HEALTH_SCHEMAORG_CONTEXT_N', $stranded))
                . ' ' . Text::_('JBS_HEALTH_SCHEMAORG_CONTEXT_FIX'),
            // The count, so clearing it at two raises again at three.
            (string) $stranded,
            'index.php?option=com_proclaim&view=cwmteachers',
            Text::_('JBS_HEALTH_SCHEMAORG_CONTEXT_ACTION')
        );
    }
}
