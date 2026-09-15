<?php

/**
 * Unit tests for ProtectedStorageCheck
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Health;

use CWM\Component\Proclaim\Administrator\Health\Check\ProtectedStorageCheck;
use CWM\Component\Proclaim\Administrator\Health\HealthResult;
use CWM\Component\Proclaim\Administrator\Health\HealthStatus;
use CWM\Component\Proclaim\Administrator\Helper\CwmmediaProtectionHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * ⚠️ Nothing executed this check before. HealthContractTest reads the check
 * classes at source level — ids, groups, language keys, whether an active check
 * is run unprompted — and never calls run() on any of them. So a check whose
 * body no longer type-checks passes the whole suite and fails on the
 * Administration screen, which is the one place it is reached.
 *
 * That is not hypothetical here: this check's severity and wording are chosen
 * from whether the protected folder holds anything, and the shape of that
 * answer changed twice while no test ran the code that consumes it.
 *
 * These tests exercise the parts a source-level scan cannot see. The wider gap
 * — that no passive check is ever run by the suite — is its own problem.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(ProtectedStorageCheck::class)]
class ProtectedStorageCheckTest extends ProclaimTestCase
{
    #[TestDox('The check runs and returns a result')]
    public function testRunReturnsAResult(): void
    {
        $result = (new ProtectedStorageCheck())->run();

        $this->assertInstanceOf(HealthResult::class, $result);
        $this->assertInstanceOf(HealthStatus::class, $result->status);
    }

    #[TestDox('It is passive, so running it here is what the panel does')]
    public function testCheckIsPassive(): void
    {
        // ⚠️ Guards the test above. An active check must not be run without
        // being asked, so if this ever becomes active, calling run() stops
        // being a safe thing for a test to do.
        $this->assertTrue(
            (new ProtectedStorageCheck())->isPassive(),
            'The check became active; running it unprompted is no longer safe and testRunReturnsAResult must change.'
        );
    }

    /**
     * @return  array<string, array{0: string, 1: bool, 2: string}>
     */
    public static function verdicts(): array
    {
        return [
            'empty folder, unverified' => [CwmmediaProtectionHelper::UNVERIFIED, false, 'EMPTY'],
            'empty folder, exposed'    => [CwmmediaProtectionHelper::EXPOSED,    false, 'EMPTY'],
            'holding, exposed'         => [CwmmediaProtectionHelper::EXPOSED,    true,  'EXPOSED'],
            'holding, unverified'      => [CwmmediaProtectionHelper::UNVERIFIED, true,  'UNVERIFIED'],
        ];
    }

    #[TestDox('The wording follows what is actually at stake')]
    #[\PHPUnit\Framework\Attributes\DataProvider('verdicts')]
    public function testDescribeChoosesTheRightSentence(string $stored, bool $holds, string $expectedKey): void
    {
        $method = new \ReflectionMethod(ProtectedStorageCheck::class, 'describe');
        $text   = (string) $method->invoke(new ProtectedStorageCheck(), $stored, $holds);

        $this->assertNotSame('', $text);

        // ⚠️ Asserted on the language key, not the sentence. Untranslated,
        // Text::_() returns the key, which is exactly what identifies the
        // branch — and it does not re-break every time the copy is reworded.
        $this->assertStringContainsString(
            'JBS_HEALTH_PROTECTED_STORAGE_' . $expectedKey,
            $text,
            "An empty folder must be described calmly and an occupied one plainly; this took the wrong branch."
        );
    }

    #[TestDox('An empty folder is never reported as a fault')]
    public function testEmptyFolderIsNotAWarning(): void
    {
        // The regression the severity split exists to prevent: reported as a
        // Warning regardless, this sat orange on every install over a folder
        // holding nothing but its own deny files.
        $method = new \ReflectionMethod(ProtectedStorageCheck::class, 'describe');
        $text   = (string) $method->invoke(new ProtectedStorageCheck(), CwmmediaProtectionHelper::EXPOSED, false);

        $this->assertStringNotContainsString(
            'JBS_HEALTH_PROTECTED_STORAGE_EXPOSED',
            $text,
            'An empty folder was described as exposing files. There is nothing in it to expose.'
        );
    }
}
