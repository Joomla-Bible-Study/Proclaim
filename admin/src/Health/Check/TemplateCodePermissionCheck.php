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
use Joomla\CMS\Access\Access;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

/**
 * User groups that can write template code without being Super Users.
 *
 * Template code is PHP that Proclaim writes into the site's layout directories
 * and the front end executes, so whoever may create or edit it may run anything
 * on the server. The permission guarding it is an ordinary per-section
 * `core.edit` — a permission sites hand to editors and campus managers, whose
 * name gives no hint of what it carries here.
 *
 * ⚠️ Reported, not enforced. Delegating this can be a deliberate choice on a
 * site whose template authors are trusted, and silently removing their access
 * would be the worse failure. What is not acceptable is it being true without
 * anyone having decided it.
 *
 * @since  __DEPLOY_VERSION__
 */
final class TemplateCodePermissionCheck implements HealthCheckInterface
{
    /**
     * The actions that let a group put a PHP file on disk.
     *
     * `core.delete` is not one: removing a layout is destructive but runs
     * nothing. `core.edit.state` only publishes what already exists.
     *
     * @var    string[]
     * @since  __DEPLOY_VERSION__
     */
    private const WRITING_ACTIONS = ['core.create', 'core.edit', 'core.edit.own'];

    /**
     * The asset the template-code section's rules hang on.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    private const SECTION_ASSET = 'com_proclaim.templatecode';

    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getId(): string
    {
        return 'security.templatecode-permission';
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
        return Text::_('JBS_HEALTH_TEMPLATECODE_PERMISSION');
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
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->createQuery()
                ->select($db->quoteName(['id', 'title']))
                ->from($db->quoteName('#__usergroups'))
                ->order($db->quoteName('lft') . ' ASC');
            $db->setQuery($query);

            $groups = $db->loadObjectList();

            $holders = [];

            foreach ($groups as $group) {
                $id = (int) $group->id;

                // A Super User already has this power everywhere, so naming
                // them here would report the expected state as a finding.
                if (Access::checkGroup($id, 'core.admin')) {
                    continue;
                }

                foreach (self::WRITING_ACTIONS as $action) {
                    if (Access::checkGroup($id, $action, self::SECTION_ASSET)) {
                        $holders[] = (string) $group->title;

                        break;
                    }
                }
            }
        } catch (\Exception) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Unknown,
                Text::_('JBS_HEALTH_TEMPLATECODE_PERMISSION_UNREADABLE')
            );
        }

        if ($holders === []) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Ok,
                Text::_('JBS_HEALTH_TEMPLATECODE_PERMISSION_NONE')
            );
        }

        $count = \count($holders);

        return new HealthResult(
            $this->getId(),
            HealthStatus::Warning,
            ($count === 1
                ? Text::_('JBS_HEALTH_TEMPLATECODE_PERMISSION_1')
                : Text::sprintf('JBS_HEALTH_TEMPLATECODE_PERMISSION_N', $count))
                . ' ' . Text::sprintf('JBS_HEALTH_TEMPLATECODE_PERMISSION_GROUPS', implode(', ', $holders))
                . ' ' . Text::_('JBS_HEALTH_TEMPLATECODE_PERMISSION_FIX'),
            // The group names, so granting it to one more raises again while
            // clearing it for a set that has not changed stays quiet.
            implode('|', $holders),
            'index.php?option=com_proclaim&view=cwmpermissions&section=templatecode',
            Text::_('JBS_HEALTH_TEMPLATECODE_PERMISSION_ACTION')
        );
    }
}
