<?php

/**
 * @@package        Proclaim.Admin
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Administrator\Extension;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Service\HTML\CWMAdministratorService;
use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Component\Router\RouterServiceTrait;
use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Factory;
use Joomla\CMS\Fields\FieldsServiceInterface;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Workflow\WorkflowServiceInterface;
use Joomla\CMS\Workflow\WorkflowServiceTrait;
use Joomla\Component\Content\Administrator\Helper\ContentHelper;
use Psr\Container\ContainerInterface;

/**
 * Component class for com_proclaim
 *
 * @since  4.0.0
 */
class ProclaimComponent extends MVCComponent implements
    BootableExtensionInterface,
    FieldsServiceInterface,
    RouterServiceInterface,
    WorkflowServiceInterface
{
    use RouterServiceTrait;
    use HTMLRegistryAwareTrait;
    use WorkflowServiceTrait;

    /**
     * The trashed condition
     *
     * @since   4.0.0
     */
    public const CONDITION_NAMES = [
        self::CONDITION_PUBLISHED   => 'JPUBLISHED',
        self::CONDITION_UNPUBLISHED => 'JUNPUBLISHED',
        self::CONDITION_ARCHIVED    => 'JARCHIVED',
        self::CONDITION_TRASHED     => 'JTRASHED',
    ];
    /**
     * The archived condition
     *
     * @since   4.0.0
     */
    public const CONDITION_ARCHIVED = 2;
    /**
     * The published condition
     *
     * @since   4.0.0
     */
    public const CONDITION_PUBLISHED = 1;
    /**
     * The unpublished condition
     *
     * @since   4.0.0
     */
    public const CONDITION_UNPUBLISHED = 0;
    /**
     * The trashed condition
     *
     * @since   4.0.0
     */
    public const CONDITION_TRASHED = -2;
    /** @var array Supported functionality */
    protected array $supportedFunctionality = [
        'core.featured' => true,
        'core.state'    => true,
    ];

    /**
     * Booting the extension. This is the function to set up the environment of the extension like
     * registering new class loaders, etc.
     *
     * If required, some initial set up can be done from services of the container, eg.
     * registering HTML services.
     *
     * @param   ContainerInterface  $container  The container
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function boot(ContainerInterface $container)
    {
        $this->getRegistry()->register('proclaimadministrator', new CWMAdministratorService());
    }

    /**
     * Returns a valid section for the given section. If it is not valid then null
     * is returned.
     *
     * @param   string  $section  The section to get the mapping for
     * @param   object  $item     The item
     *
     * @return  string|null  The new section
     *
     * @throws \Exception
     * @since   4.0.0
     */
    public function validateSection($section, $item = null)
    {
        if (Factory::getApplication()->isClient('site')) {
            // On the front end we need to map some sections
            switch ($section) {
                // Editing an article
                case 'form':
            }
        }

        if ($section !== 'cpanel') {
            // We don't know other sections
            return null;
        }

        return $section;
    }

    /**
     * Returns valid contexts
     *
     * @return  array
     *
     * @throws \Exception
     * @since   4.0.0
     */
    public function getContexts(): array
    {
        Factory::getApplication()->getLanguage()->load('com_proclaim', JPATH_ADMINISTRATOR);

        return array(
            'com_proclaim.cwmcpanel' => Text::_('com_proclaim'),
            'com_proclaim.cwmadmin'  => Text::_('JCATEGORY')
        );
    }

    /**
     * Method to filter transitions by given id of state.
     *
     * @param   array  $transitions  The Transitions to filter
     * @param   int    $pk           Id of the state
     *
     * @return  array
     *
     * @since  4.0.0
     */
    public function filterTransitions(array $transitions, int $pk): array
    {
        return ContentHelper::filterTransitions($transitions, $pk);
    }

    /**
     * Returns a table name for the state association
     *
     * @param   string|null  $section  An optional section to separate different areas in the component
     *
     * @return  string
     *
     * @since   4.0.0
     */
    public function getWorkflowTableBySection(?string $section = null): string
    {
        return '#__bsms_studies';
    }

    /**
     * Returns the workflow context based on the given category section
     *
     * @param   string|null  $section  The section
     *
     * @return  string|null
     *
     * @throws \Exception
     * @since   4.0.0
     */
    public function getCategoryWorkflowContext(?string $section = null): string
    {
        $context = $this->getWorkflowContexts();

        return array_key_first($context);
    }

    /**
     * Returns valid contexts
     *
     * @return  array
     *
     * @throws \Exception
     * @since   4.0.0
     */
    public function getWorkflowContexts(): array
    {
        Factory::getApplication()->getLanguage()->load('com_proclaim', JPATH_ADMINISTRATOR);

        return array(
            'com_proclaim.cwmadmin' => Text::_('COM_PROCLAIM')
        );
    }
}
