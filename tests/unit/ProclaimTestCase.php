<?php

/**
 * Base Test Case for Proclaim Component
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Base test case class for Proclaim unit tests
 *
 * Provides common setup, teardown, and helper methods for testing
 * Proclaim component classes without requiring a full Joomla installation.
 *
 * @since 10.1.0
 */
abstract class ProclaimTestCase extends TestCase
{
    /**
     * A copy of $_SERVER, restored after each test.
     *
     * @var    array
     * @since  10.1.0
     */
    protected array $serverBackup = [];

    /**
     * Sets up the fixture
     *
     * @return  void
     *
     * @since   10.1.0
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Backup superglobals
        $this->serverBackup = $_SERVER;
    }

    /**
     * Tears down the fixture
     *
     * @return  void
     *
     * @since   10.1.0
     */
    protected function tearDown(): void
    {
        // Restore superglobals
        $_SERVER = $this->serverBackup;

        parent::tearDown();
    }

    /**
     * Assert that a string contains valid JSON
     *
     * @param string $json The string to check
     * @param string $message Optional assertion message
     *
     * @return  void
     *
     * @since   10.1.0
     */
    protected function assertValidJson(string $json, string $message = ''): void
    {
        json_decode($json);
        $this->assertEquals(JSON_ERROR_NONE, json_last_error(), $message ?: 'Invalid JSON: ' . json_last_error_msg());
    }

    /**
     * Assert that an array has specific keys
     *
     * @param array $keys Expected keys
     * @param array $array Array to check
     * @param string $message Optional assertion message
     *
     * @return  void
     *
     * @since   10.1.0
     */
    protected function assertArrayHasKeys(array $keys, array $array, string $message = ''): void
    {
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $array, $message ?: "Array missing expected key: $key");
        }
    }

    /**
     * Get a protected/private property value using reflection
     *
     * @param object $object Object instance
     * @param string $property Property name
     *
     * @return  mixed Property value
     *
     * @since   10.1.0
     */
    protected function getProtectedProperty(object $object, string $property): mixed
    {
        $reflection = new \ReflectionClass($object);
        $prop       = $reflection->getProperty($property);

        return $prop->getValue($object);
    }

    /**
     * Set a protected/private property value using reflection
     *
     * @param object $object Object instance
     * @param string $property Property name
     * @param mixed $value Value to set
     *
     * @return  void
     *
     * @since   10.1.0
     */
    protected function setProtectedProperty(object $object, string $property, mixed $value): void
    {
        $reflection = new \ReflectionClass($object);
        $prop       = $reflection->getProperty($property);
        $prop->setValue($object, $value);
    }

    /**
     * Invoke a protected/private method using reflection
     *
     * @param object $object Object instance
     * @param string $method Method name
     * @param array $args Method arguments
     *
     * @return  mixed Method return value
     *
     * @since   10.1.0
     */
    protected function invokeProtectedMethod(object $object, string $method, array $args = []): mixed
    {
        $reflection = new \ReflectionClass($object);
        $method     = $reflection->getMethod($method);

        return $method->invokeArgs($object, $args);
    }

    /**
     * Assert method return type name, skipping if no return type is declared.
     *
     * Many Joomla base class methods do not declare return types. When our code
     * inherits without redeclaring, getReturnType() returns null. This helper
     * checks the type only when one is declared.
     *
     * @param   string             $expected    Expected type name
     * @param   \ReflectionMethod  $method      Reflected method
     *
     * @return  void
     *
     * @since   10.1.0
     */
    protected function assertReturnTypeName(string $expected, \ReflectionMethod $method): void
    {
        $type = $method->getReturnType();

        if ($type !== null) {
            $this->assertEquals($expected, $type->getName());
        }
    }

    /**
     * Assert parameter type name, skipping if no type is declared.
     *
     * Many Joomla base class method parameters are untyped. This helper checks
     * the type only when one is declared.
     *
     * @param   string                $expected  Expected type name
     * @param   \ReflectionParameter  $param     Reflected parameter
     *
     * @return  void
     *
     * @since   10.1.0
     */
    protected function assertParamTypeName(string $expected, \ReflectionParameter $param): void
    {
        $type = $param->getType();

        if ($type !== null) {
            $this->assertEquals($expected, $type->getName());
        }
    }

    /**
     * Create a temporary file for testing
     *
     * @param string $content File content
     * @param string $extension File extension
     *
     * @return  string Path to temporary file
     *
     * @since   10.1.0
     */
    protected function createTempFile(string $content = '', string $extension = 'tmp'): string
    {
        $path = sys_get_temp_dir() . '/proclaim_test_' . uniqid() . '.' . $extension;
        file_put_contents($path, $content);

        return $path;
    }

    /**
     * Remove a temporary file
     *
     * @param string $path Path to file
     *
     * @return  void
     *
     * @since   10.1.0
     */
    protected function removeTempFile(string $path): void
    {
        if (file_exists($path)) {
            unlink($path);
        }
    }
}
