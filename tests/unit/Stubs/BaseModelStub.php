<?php

/**
 * Stub for Joomla\CMS\MVC\Model\BaseModel for unit testing
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Stubs;

/**
 * Stub class for BaseModel
 *
 * Provides minimal implementation to allow testing of base models
 * without requiring full Joomla framework.
 *
 * @since 10.0.0
 */
class BaseModelStub
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
