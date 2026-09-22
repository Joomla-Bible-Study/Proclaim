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

/**
 * Template code rows carrying their own CSS.
 *
 * ⚠️ The risk is loss, not correctness. The row's stored PHP is written out to
 * a layout file under a component folder that the install script prunes on
 * update, so styling kept inside it is thrown away by an update that looks
 * successful. The help text used to recommend putting it there, so the sites
 * most likely to be carrying this are the ones that followed the advice.
 *
 * Counts `<style` only. CSS written as bare rules inside PHP output cannot be
 * told apart from any other string without parsing, and a check that guesses
 * would report the wrong number rather than none.
 *
 * @since  10.6.0
 */
final class TemplateCodeCssCheck implements HealthCheckInterface
{
    /**
     * @inheritDoc
     *
     * @since  10.6.0
     */
    public function getId(): string
    {
        return 'security.templatecode-css';
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
        return Text::_('JBS_HEALTH_TEMPLATECODE_CSS');
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
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->createQuery()
                ->select('COUNT(*)')
                ->from($db->quoteName('#__bsms_templatecode'))
                ->where($db->quoteName('templatecode') . ' LIKE ' . $db->quote('%<style%'));
            $db->setQuery($query);

            $count = (int) $db->loadResult();
        } catch (\Exception) {
            // The table is dropped on uninstall and absent mid-migration. An
            // unanswerable question is not a clean site.
            return new HealthResult(
                $this->getId(),
                HealthStatus::Unknown,
                Text::_('JBS_HEALTH_TEMPLATECODE_CSS_UNREADABLE')
            );
        }

        if ($count === 0) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Ok,
                Text::_('JBS_HEALTH_TEMPLATECODE_CSS_NONE')
            );
        }

        return new HealthResult(
            $this->getId(),
            HealthStatus::Notice,
            $count === 1
                ? Text::_('JBS_HEALTH_TEMPLATECODE_CSS_1')
                : Text::sprintf('JBS_HEALTH_TEMPLATECODE_CSS_N', $count),
            // The count, so quietening at two raises again at three rather than
            // staying silent while more rows collect the same risk.
            (string) $count,
            'index.php?option=com_proclaim&view=cwmtemplatecodes',
            Text::_('JBS_HEALTH_TEMPLATECODE_CSS_ACTION')
        );
    }
}
