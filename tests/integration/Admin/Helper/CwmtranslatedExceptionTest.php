<?php

/**
 * Integration tests for Cwmtranslated's no-application failure path.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\Cwmtranslated;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression coverage for #1588.
 *
 * The file imported http\Exception\RuntimeException -- the PECL ext-http
 * class, which is not a dependency of this project and is not installed on a
 * standard stack. The throw in getTopicItemTranslated() therefore named a class
 * that does not exist, so instead of the RuntimeException it advertises it
 * raised "class not found": an Error, which does not extend Exception, so a
 * caller catching either would not have seen it.
 *
 * Removing the import alone is not enough. This file is namespaced, so an
 * unqualified RuntimeException resolves against
 * CWM\Component\Proclaim\Administrator\Helper, not the global namespace -- one
 * "class not found" would simply have replaced another. The throw is qualified.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(Cwmtranslated::class)]
class CwmtranslatedExceptionTest extends IntegrationTestCase
{
    private mixed $savedApplication = null;

    private bool $applicationRemoved = false;

    protected function tearDown(): void
    {
        // Restore before anything else; every later test in the run needs it.
        if ($this->applicationRemoved) {
            Factory::$application     = $this->savedApplication;
            $this->applicationRemoved = false;
        }

        parent::tearDown();
    }

    /**
     * Remove the CMS application so Factory::getApplication() throws, which is
     * the only way into the failure path under test.
     *
     * @return  void
     */
    private function removeApplication(): void
    {
        $this->savedApplication   = Factory::$application;
        Factory::$application     = null;
        $this->applicationRemoved = true;
    }

    /**
     * The failure path, exercised rather than asserted about.
     */
    #[TestDox('A missing application raises a real RuntimeException')]
    public function testMissingApplicationRaisesRuntimeException(): void
    {
        $this->removeApplication();

        try {
            Cwmtranslated::getTopicItemTranslated((object) ['id' => 1]);
            $this->fail('Expected the missing application to raise');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString(
                'Unable to load Application',
                $e->getMessage(),
                'The exception it names must be the exception it throws — see #1588'
            );
        } catch (\Error $e) {
            $this->fail(
                'Still a fatal rather than a catchable exception — see #1588: ' . $e->getMessage()
            );
        }
    }

    /**
     * The cause is what a reader actually needs; without it the message says
     * only that something failed.
     */
    #[TestDox('The original failure is chained as the previous exception')]
    public function testOriginalFailureIsChained(): void
    {
        $this->removeApplication();

        try {
            Cwmtranslated::getTopicItemTranslated((object) ['id' => 1]);
            $this->fail('Expected the missing application to raise');
        } catch (\RuntimeException $e) {
            $this->assertInstanceOf(
                \Throwable::class,
                $e->getPrevious(),
                'The underlying failure must not be discarded'
            );
        }
    }

    /**
     * A caller catching the broad case must see it too -- which an Error would
     * not have satisfied.
     */
    #[TestDox('The failure is catchable as an Exception')]
    public function testFailureIsCatchableAsException(): void
    {
        $this->removeApplication();

        $caught = false;

        try {
            Cwmtranslated::getTopicItemTranslated((object) ['id' => 1]);
        } catch (\Exception $e) {
            $caught = true;
        }

        $this->assertTrue(
            $caught,
            'A "class not found" Error does not extend Exception, so no caller could catch it — see #1588'
        );
    }

    /**
     * The class named must be one that exists. ext-http is not a dependency of
     * this project and is absent from a standard PHP install.
     */
    #[TestDox('The helper does not reference the PECL ext-http exception')]
    public function testDoesNotImportTheExtHttpClass(): void
    {
        $source = (string) file_get_contents(
            (new \ReflectionClass(Cwmtranslated::class))->getFileName()
        );

        $this->assertStringNotContainsString(
            'use http\Exception\RuntimeException;',
            $source,
            'ext-http is not in composer.json and is not installed — see #1588'
        );
        $this->assertStringContainsString(
            'new \RuntimeException(',
            $source,
            'The throw must be qualified: unqualified resolves to this namespace, not the global one'
        );
    }
}
