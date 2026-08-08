<?php

/**
 * Unit tests for AbstractWritableController::stripProtectedFields() (#1447)
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Admin\Api\Controller;

use CWM\Component\Proclaim\Api\Controller\AbstractWritableController;
use CWM\Component\Proclaim\Api\Controller\LocationsController;
use CWM\Component\Proclaim\Api\Controller\MediaController;
use CWM\Component\Proclaim\Api\Controller\MessagetypesController;
use CWM\Component\Proclaim\Api\Controller\PodcastsController;
use CWM\Component\Proclaim\Api\Controller\SeriesController;
use CWM\Component\Proclaim\Api\Controller\SermonsController;
use CWM\Component\Proclaim\Api\Controller\TeachersController;
use CWM\Component\Proclaim\Api\Controller\TopicsController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Regression test for #1447: preprocessSaveData()'s system-field stripping
 * (asset_id/checked_out/checked_out_time/modified_by) plus the
 * created_by/published ACL gate was duplicated verbatim across all 8
 * writable API controllers (Sermons, Locations, Podcasts, Media, Topics,
 * Teachers, Messagetypes, Series). Hoisted into
 * AbstractWritableController::stripProtectedFields(); each controller's
 * preprocessSaveData() now calls it and layers only its own extra fields
 * (Locations' email_to/user_id/contact_id, Media's podcast_id explode,
 * Teachers'/Sermons' image default) on top.
 *
 * @since  __DEPLOY_VERSION__
 */
class AbstractWritableControllerTest extends ProclaimTestCase
{
    /**
     * The 8 writable controllers preprocessSaveData() duplication spanned.
     *
     * @return  array<string, array{0: class-string}>
     */
    public static function writableControllerProvider(): array
    {
        return [
            'Sermons'      => [SermonsController::class],
            'Locations'    => [LocationsController::class],
            'Podcasts'     => [PodcastsController::class],
            'Media'        => [MediaController::class],
            'Topics'       => [TopicsController::class],
            'Teachers'     => [TeachersController::class],
            'Messagetypes' => [MessagetypesController::class],
            'Series'       => [SeriesController::class],
        ];
    }

    /**
     * Get the source body of a method for structural assertions.
     *
     * @param   class-string  $class
     * @param   string        $method
     *
     * @return  string
     */
    private static function methodBody(string $class, string $method): string
    {
        $reflection = new \ReflectionMethod($class, $method);
        $lines      = file($reflection->getFileName());

        return implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );
    }

    public function testStripProtectedFieldsExistsAndIsProtected(): void
    {
        $ref = new \ReflectionMethod(AbstractWritableController::class, 'stripProtectedFields');
        $this->assertTrue($ref->isProtected());
        $this->assertSame('array', $ref->getReturnType()?->getName());
    }

    public function testStripProtectedFieldsStripsSystemFieldsAndGatesAcl(): void
    {
        $body = self::methodBody(AbstractWritableController::class, 'stripProtectedFields');

        foreach (['asset_id', 'checked_out', 'checked_out_time', 'modified_by'] as $field) {
            $this->assertStringContainsString(
                "\$data['{$field}']",
                $body,
                "stripProtectedFields() must unset '{$field}' — see #1447"
            );
        }

        $this->assertStringContainsString('core.admin', $body, 'created_by must require core.admin');
        $this->assertStringContainsString('core.edit.state', $body, 'published must be gated on core.edit.state');
    }

    /**
     * @param   class-string  $class
     *
     * @return  void
     */
    #[DataProvider('writableControllerProvider')]
    public function testPreprocessSaveDataDelegatesToSharedHelper(string $class): void
    {
        $body = self::methodBody($class, 'preprocessSaveData');

        $this->assertStringContainsString(
            'stripProtectedFields(',
            $body,
            $class . '::preprocessSaveData() must delegate to stripProtectedFields() — see #1447'
        );
    }

    /**
     * The exact duplicated block #1447 removed. Its reappearance in any
     * controller means the dedup regressed — the shared helper exists
     * precisely so no controller needs to unset these fields itself again.
     *
     * @param   class-string  $class
     *
     * @return  void
     */
    #[DataProvider('writableControllerProvider')]
    public function testPreprocessSaveDataHasNoDuplicatedSystemFieldUnset(string $class): void
    {
        $body = self::methodBody($class, 'preprocessSaveData');

        $this->assertDoesNotMatchRegularExpression(
            "/unset\\(\\s*\\\$data\\['asset_id'\\]/",
            $body,
            $class . '::preprocessSaveData() must not re-duplicate the asset_id/checked_out/modified_by unset — see #1447'
        );
    }

    public function testLocationsControllerLayersItsOwnExtraFieldsOnTopOfSharedHelper(): void
    {
        $body = self::methodBody(LocationsController::class, 'preprocessSaveData');

        $this->assertStringContainsString('stripProtectedFields(', $body);

        foreach (['email_to', 'user_id', 'contact_id'] as $field) {
            $this->assertStringContainsString(
                "\$data['{$field}']",
                $body,
                "Locations must still strip its own extra field '{$field}' on top of the shared helper — see #1447"
            );
        }
    }

    public function testMediaControllerKeepsPodcastIdNormalizationBeforeSharedHelper(): void
    {
        $body = self::methodBody(MediaController::class, 'preprocessSaveData');

        $this->assertStringContainsString("explode(',', \$data['podcast_id'])", $body);
        $this->assertStringContainsString('stripProtectedFields(', $body);
        $this->assertLessThan(
            strpos($body, 'stripProtectedFields('),
            strpos($body, "explode(','"),
            'podcast_id normalization must run before the shared helper strips/gates fields'
        );
    }

    public function testTeachersControllerKeepsImageDefaultBeforeSharedHelper(): void
    {
        $body = self::methodBody(TeachersController::class, 'preprocessSaveData');

        $this->assertStringContainsString("\$data['image'] = ''", $body);
        $this->assertStringContainsString('stripProtectedFields(', $body);
    }
}
