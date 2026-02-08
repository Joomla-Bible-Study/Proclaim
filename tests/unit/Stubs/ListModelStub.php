<?php

/**
 * Stub for Joomla\CMS\MVC\Model\ListModel for unit testing
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Stubs;

/**
 * Stub class for ListModel
 *
 * Provides minimal implementation to allow testing of list models
 * without requiring full Joomla framework.
 *
 * @since 10.0.0
 */
class ListModelStub extends BaseDatabaseModelStub
{
    /**
     * Get items
     *
     * @return mixed
     */
    public function getItems(): mixed
    {
        return [];
    }

    /**
     * Get pagination
     *
     * @return mixed
     */
    public function getPagination(): mixed
    {
        return null;
    }

    /**
     * Get list query
     *
     * @return mixed
     */
    protected function getListQuery(): mixed
    {
        return null;
    }

    /**
     * Get store ID
     *
     * @param   string  $id  Identifier
     *
     * @return string
     */
    protected function getStoreId(string $id = ''): string
    {
        return $id;
    }

    /**
     * Populate state
     *
     * @param   string  $ordering   Ordering column
     * @param   string  $direction  Direction
     *
     * @return void
     */
    protected function populateState(string $ordering = '', string $direction = ''): void
    {
        // Stub method - does nothing
    }
}
