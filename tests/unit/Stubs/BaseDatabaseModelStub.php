<?php

/**
 * Stub for Joomla\CMS\MVC\Model\BaseDatabaseModel for unit testing
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Stubs;

/**
 * Stub class for BaseDatabaseModel
 *
 * Provides minimal implementation to allow testing of models
 * without requiring full Joomla framework.
 *
 * @since 10.0.0
 */
class BaseDatabaseModelStub
{
    /**
     * Constructor
     *
     * @param   array  $config  Configuration array
     */
    public function __construct(array $config = [])
    {
        // Stub constructor - does nothing
    }

    /**
     * Get the database driver
     *
     * @return mixed
     */
    public function getDatabase(): mixed
    {
        return null;
    }

    /**
     * Get the model state
     *
     * @param   string  $property  Property name
     * @param   mixed   $default   Default value
     *
     * @return mixed
     */
    public function getState(string $property = '', mixed $default = null): mixed
    {
        return $default;
    }

    /**
     * Set the model state
     *
     * @param   string  $property  Property name
     * @param   mixed   $value     Value
     *
     * @return mixed
     */
    public function setState(string $property, mixed $value): mixed
    {
        return $value;
    }
}
