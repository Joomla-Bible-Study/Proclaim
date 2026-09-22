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
 * A model's saved-form user state must be read under the key its controller writes.
 *
 * `FormController::save()` stashes the submitted values under
 * `<option>.edit.<context>.data` when a save fails, so the edit form can come
 * back with what the user typed. `$context` is not the entity name: no
 * controller here declares one, so Joomla derives it from the class name, and
 * `CwmtopicController` yields `cwmtopic`.
 *
 * ⚠️ A model that reads a different name gets `[]` every time and falls through
 * to the stored record. Nothing errors and the form still looks populated — with
 * the old values, the user's input silently gone. Eleven models read the
 * unprefixed entity name and so had never once honoured that state.
 *
 * Checked over the source rather than by exercising a save, because reaching
 * that state needs a session, a controller and a failing validation, while the
 * defect is a string mismatch that reads perfectly well in the file.
 *
 * @since __DEPLOY_VERSION__
 */
class FormUserStateKeyTest extends ProclaimTestCase
{
    /**
     * The context Joomla derives for a controller class.
     *
     * Mirrors `FormController::__construct()`: match everything after the last
     * `Controller\` namespace segment, lowercase it, and strip `controller`.
     *
     * @param   string  $class  Fully qualified controller class name.
     *
     * @return  string
     *
     * @since __DEPLOY_VERSION__
     */
    private static function derivedContext(string $class): string
    {
        $match = 'Controller';

        if (str_contains($class, '\\')) {
            $match .= '\\\\';
        }

        if (!preg_match('/(.*)' . $match . '(.*)/i', $class, $r)) {
            return '';
        }

        return str_replace(['\\', 'controller'], '', strtolower($r[2]));
    }

    /**
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('Every model reads saved form data under the key its controller writes')]
    public function testUserStateKeyMatchesTheControllerContext(): void
    {
        $base     = \dirname(__DIR__, 3);
        $offences = [];
        $checked  = 0;

        foreach (glob($base . '/admin/src/Model/*.php') ?: [] as $path) {
            $source = (string) file_get_contents($path);

            if (!preg_match("#getUserState\(\s*'com_proclaim\.edit\.([A-Za-z0-9_]+)\.data'#", $source, $m)) {
                continue;
            }

            $key        = $m[1];
            $entity     = basename($path, 'Model.php');
            $controller = $base . '/admin/src/Controller/' . $entity . 'Controller.php';

            if (!is_file($controller)) {
                $offences[] = \sprintf('%sModel reads "%s" but no %sController exists', $entity, $key, $entity);

                continue;
            }

            $checked++;

            // A controller that declares its own context overrides the guess.
            if (preg_match('/\$context\s*=\s*[\'"]([^\'"]+)[\'"]/', (string) file_get_contents($controller), $d)) {
                $expected = $d[1];
            } else {
                $expected = self::derivedContext(
                    'CWM\\Component\\Proclaim\\Administrator\\Controller\\' . $entity . 'Controller'
                );
            }

            if ($key !== $expected) {
                $offences[] = \sprintf(
                    '%sModel reads com_proclaim.edit.%s.data but %sController writes com_proclaim.edit.%s.data',
                    $entity,
                    $key,
                    $entity,
                    $expected
                );
            }
        }

        // ⚠️ Not a silent pass. If the glob or the pattern breaks, say so rather
        // than reporting success having read nothing.
        $this->assertGreaterThan(
            8,
            $checked,
            'Too few models with a saved-form user state were scanned; the scan is broken.'
        );

        $this->assertSame(
            [],
            $offences,
            "A key the controller never writes always reads empty, so the form silently\n"
            . "repopulates from the stored record and the user's input is lost:\n"
            . implode("\n", $offences)
        );
    }
}
