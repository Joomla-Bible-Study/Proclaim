<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_mywalks
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Administrator\Extension;

\defined('JPATH_PLATFORM') or die;

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
use Psr\Container\ContainerInterface;

/**
 * Component class for com_mywalks
 *
 * @since  4.0.0
 */
class ProclaimComponent extends MVCComponent implements
BootableExtensionInterface, FieldsServiceInterface, RouterServiceInterface, WorkflowServiceInterface
{
	use RouterServiceTrait;
	use HTMLRegistryAwareTrait;
	use WorkflowServiceTrait;

	/** @var array Supported functionality */
	protected $supportedFunctionality = [
		'core.featured' => true,
		'core.state' => true,
	];

	/**
	 * The trashed condition
	 *
	 * @since   4.0.0
	 */
	const CONDITION_NAMES = [
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
	const CONDITION_ARCHIVED = 2;

	/**
	 * The published condition
	 *
	 * @since   4.0.0
	 */
	const CONDITION_PUBLISHED = 1;

	/**
	 * The unpublished condition
	 *
	 * @since   4.0.0
	 */
	const CONDITION_UNPUBLISHED = 0;

	/**
	 * The trashed condition
	 *
	 * @since   4.0.0
	 */
	const CONDITION_TRASHED = -2;

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
		//$this->getRegistry()->register('cwmproclaimadministrator', new AdministratorService);
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
		if (Factory::getApplication()->isClient('site'))
		{
			// On the front end we need to map some sections
			switch ($section)
			{
				// Editing an article
				case 'form':

					// Category list view
				case 'featured':
				case 'category':
					$section = 'article';
			}
		}

		if ($section !== 'cpanel')
		{
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
	 * @since   4.0.0
	 */
	public function getContexts(): array
	{
		Factory::getLanguage()->load('com_proclaim', JPATH_ADMINISTRATOR);

		$contexts = array(
			'com_proclaim.cpanel'    => Text::_('com_proclaim'),
			'com_proclaim.administrator' => Text::_('JCATEGORY')
		);

		return $contexts;
	}

	public function filterTransitions(array $transitions, int $pk): array
	{
		// TODO: Implement filterTransitions() method.
	}

	public function getWorkflowTableBySection(?string $section = null): string
	{
		// TODO: Implement getWorkflowTableBySection() method.
	}

	public function getWorkflowContexts(): array
	{
		// TODO: Implement getWorkflowContexts() method.
	}

	public function getCategoryWorkflowContext(?string $section = null): string
	{
		// TODO: Implement getCategoryWorkflowContext() method.
	}
}
