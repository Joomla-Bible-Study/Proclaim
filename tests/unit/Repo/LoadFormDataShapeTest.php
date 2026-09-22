<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Repo;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * `loadFormData()` must hand back the item, not a list containing it.
 *
 * ⚠️ `Form::bind()` treats a numeric key holding an object as a **group** and
 * binds the object's properties inside it. Wrapped, every parameter lands
 * somewhere no field reads, and several arrive as arrays.
 *
 * That failure hides well. A controller that binds the real item afterwards
 * repairs any parameter the item actually has, so the damage is confined to
 * parameters the stored record does not carry — which on a configured site is
 * none of them. It surfaced only on a fresh install, where `landing_layout` is
 * absent: the wreckage survived, a hidden field was rendered from an array, and
 * `htmlspecialchars()` took the whole Layout Editor tab down with a TypeError
 * that `catch (\Exception)` could not catch.
 *
 * Checked over the source rather than by exercising the models, because
 * reaching `loadFormData()` means a form, a database and a session — while the
 * defect is a shape error that reads perfectly well in the file.
 *
 * @since __DEPLOY_VERSION__
 */
class LoadFormDataShapeTest extends ProclaimTestCase
{
    /**
     * Every model in the component.
     *
     * @return  array<int, string>
     *
     * @since __DEPLOY_VERSION__
     */
    private static function modelFiles(): array
    {
        $base  = \dirname(__DIR__, 3);
        $files = [];

        foreach (['admin/src/Model', 'site/src/Model', 'api/src/Model'] as $dir) {
            foreach (glob($base . '/' . $dir . '/*.php') ?: [] as $file) {
                $files[] = $file;
            }
        }

        return $files;
    }

    /**
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('No model wraps its item in a list when loading form data')]
    public function testNoModelWrapsTheItem(): void
    {
        $base     = \dirname(__DIR__, 3);
        $offences = [];
        $checked  = 0;

        foreach (self::modelFiles() as $path) {
            $source = (string) file_get_contents($path);

            if (!str_contains($source, 'loadFormData')) {
                continue;
            }

            $checked++;

            foreach (explode("\n", $source) as $number => $line) {
                // `$data = [$this->getItem()];`, at any spacing.
                if (preg_match('#=\s*\[\s*\$this->getItem\(\)\s*,?\s*\]#', $line)) {
                    $offences[] = \sprintf(
                        '%s:%d  %s',
                        substr($path, \strlen($base) + 1),
                        $number + 1,
                        trim($line)
                    );
                }
            }
        }

        // ⚠️ Not a silent pass. If the glob or the filter breaks, this says so
        // rather than reporting success having read nothing.
        $this->assertGreaterThan(
            5,
            $checked,
            'Too few models with loadFormData() were scanned; the scan is broken.'
        );

        $this->assertSame(
            [],
            $offences,
            "Form::bind() binds a numeric key holding an object as a group, so a wrapped item\n"
            . "never reaches the fields that read it and some parameters arrive as arrays:\n"
            . implode("\n", $offences)
        );
    }
}
