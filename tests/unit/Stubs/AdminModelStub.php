<?php

/**
 * Stub for Joomla\CMS\MVC\Model\AdminModel for unit testing
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Stubs;

/**
 * Stub class for AdminModel
 *
 * Provides minimal implementation to allow testing of admin models
 * without requiring full Joomla framework.
 *
 * @since 10.0.0
 */
class AdminModelStub extends BaseDatabaseModelStub
{
    /**
     * Get form
     *
     * @param   array    $data      Data
     * @param   boolean  $loadData  Load data flag
     *
     * @return mixed
     */
    public function getForm(array $data = [], bool $loadData = true): mixed
    {
        return null;
    }

    /**
     * Save data
     *
     * @param   array  $data  Data to save
     *
     * @return boolean
     */
    public function save(array $data): bool
    {
        return true;
    }

    /**
     * Get item
     *
     * @param   integer  $pk  Primary key
     *
     * @return mixed
     */
    public function getItem(int $pk = 0): mixed
    {
        return null;
    }

    /**
     * Prepare table
     *
     * @param   mixed  $table  Table object
     *
     * @return void
     */
    protected function prepareTable(mixed $table): void
    {
        // Stub method - does nothing
    }
}
