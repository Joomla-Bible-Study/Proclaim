<?php

/**
 * Exporting a template must not let its title choose a path or a header.
 *
 * `templateExport()` built both the file it wrote and the name it offered the
 * browser from `#__bsms_templates.title`, a free-text field with no filter.
 * Joomla's `File::write()` creates the intermediate directory before writing,
 * so a title of `x/../../evil` resolved to a `.sql` written two levels above
 * `/tmp`; the same string then reached a `Content-Disposition` header, where a
 * bare `"` ends the quoted filename.
 *
 * The two are now separate values, and the path builder takes no arguments at
 * all — a title cannot be handed to something with nothing to pass it.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since __DEPLOY_VERSION__
 */

namespace CWM\Component\Proclaim\Tests\Admin\Controller;

use CWM\Component\Proclaim\Administrator\Controller\CwmtemplatesController;
use CWM\Component\Proclaim\Administrator\Lib\Cwmbackup;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\Factory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * @since __DEPLOY_VERSION__
 */
class TemplateExportNamingTest extends ProclaimTestCase
{
    /**
     * Anything that would give the title control of a path or a header.
     *
     * @return  array<string, array{0: string}>
     *
     * @since __DEPLOY_VERSION__
     */
    public static function hostileTitleProvider(): array
    {
        return [
            'the reachable escape' => ['x/../../evil'],
            'plain traversal'      => ['../../configuration'],
            'backslash traversal'  => ['..\\..\\win.ini'],
            'absolute path'        => ['/etc/passwd'],
            'breaks the header'    => ['a"; filename="hacked.sql'],
            'header continuation'  => ["a\r\nX-Injected: 1"],
            'percent encoded'      => ['%2e%2e%2fetc'],
            'null byte'            => ["ok\0.sql"],
            'bare dot dot'         => ['..'],
            'leading dot'          => ['.htaccess'],
        ];
    }

    /**
     * ⚠️ Asserted on the *output*, not on which sanitiser was called. What
     * matters is that nothing able to leave a directory or end a quoted header
     * parameter survives, whichever function does it.
     *
     * @param   string  $title  A title that must not reach a path or a header intact
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[DataProvider('hostileTitleProvider')]
    #[TestDox('A hostile title yields a download name with no path or header characters')]
    public function testDownloadNameIsInert(string $title): void
    {
        $name = CwmtemplatesController::exportDownloadName(7, $title);

        $this->assertDoesNotMatchRegularExpression(
            '#[/\\\\"\'\r\n%\x00]#',
            $name,
            var_export($title, true) . ' produced a download name that can still steer a path or a header: '
            . var_export($name, true)
        );

        $this->assertSame(
            basename($name),
            $name,
            'The download name has a directory component, so it is not just a name.'
        );

        $this->assertStringEndsWith('.sql', $name);
    }

    /**
     * A title that sanitises away entirely must still name the file something.
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('A title that sanitises to nothing falls back to the record id')]
    public function testEmptyTitleFallsBackToTheId(): void
    {
        foreach (['', '   ', '..', '///'] as $title) {
            $this->assertSame(
                'template-7.sql',
                CwmtemplatesController::exportDownloadName(7, $title),
                var_export($title, true) . ' did not fall back to the id.'
            );
        }

        $this->assertSame('template-7.sql', CwmtemplatesController::exportDownloadName(7, null));
    }

    /**
     * ⚠️ Not vacuous. A fix that reduced every title to the fallback would pass
     * every test above and make the export useless to the person downloading it.
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('An ordinary title is still recognisable in the download name')]
    public function testOrdinaryTitleSurvives(): void
    {
        $this->assertSame('My Template.sql', CwmtemplatesController::exportDownloadName(7, 'My Template'));
        $this->assertSame('advent-2026.sql', CwmtemplatesController::exportDownloadName(7, 'advent-2026'));

        // Transliterated rather than discarded, so a non-ASCII title stays useful.
        $this->assertStringContainsString(
            'Template',
            CwmtemplatesController::exportDownloadName(7, 'Ünïcödé Template')
        );
    }

    /**
     * The path the export is staged at.
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('The staging path is inside the configured tmp directory and ends in .sql')]
    public function testTempPathIsContained(): void
    {
        $path = CwmtemplatesController::exportTempPath();

        // ⚠️ Compared against the *configured* tmp_path, not a hard-coded
        // JPATH_ROOT/tmp. A site that has moved its temp directory outside the
        // web root must not fail this, and pinning the default here would stop
        // the staging location ever being moved.
        $expected = (string) Factory::getApplication()->get('tmp_path');
        $expected = rtrim($expected === '' ? JPATH_ROOT . '/tmp' : $expected, '/\\');

        $this->assertSame($expected, \dirname($path), 'The export is staged outside the tmp directory.');

        $this->assertStringEndsWith('.sql', $path);
        $this->assertDoesNotMatchRegularExpression('#[/\\\\]#', basename($path));
    }

    /**
     * ⚠️ The reason outputFile() can stop URL-decoding the name it puts in the
     * header: nothing legitimate was being decoded. This is the assumption that
     * change rests on, and the backup download it also affects has no test of
     * its own — so the assumption is asserted here rather than left as prose.
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('A generated backup filename carries nothing a URL decode would change')]
    public function testGeneratedBackupFilenameNeedsNoDecoding(): void
    {
        $config   = Factory::getApplication()->getConfig();
        $original = $config->get('sitename');

        // ⚠️ A hostile site name, forced. Reading whatever this install happens
        // to be called proves nothing -- the first version of this test passed
        // with the sanitiser deleted, because the local site is named safely.
        $config->set('sitename', 'My "Site" 100% /../.. ' . "\r\n" . 'X: 1');

        try {
            $name = Cwmbackup::generateBackupFilename();

            $this->assertDoesNotMatchRegularExpression(
                '#[/\\\\"\'\r\n%\x00]#',
                $name,
                'A generated backup filename can steer a header or a path: ' . var_export($name, true)
            );

            $this->assertSame(
                $name,
                rawurldecode($name),
                'Decoding changes this name, so removing the decode from outputFile() alters the download.'
            );

            $this->assertStringEndsWith('.sql', $name);
        } finally {
            $config->set('sitename', $original);
        }
    }

    /**
     * ⚠️ The reason the name is random rather than merely fixed: a file left
     * behind by a failed stream must not sit at a name its requester chose.
     * Joomla's `/tmp` is reachable under many server configurations and ships
     * an `index.html`, not a deny rule.
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('Two exports never stage at the same path')]
    public function testTempPathIsUnpredictable(): void
    {
        $seen = [];

        for ($i = 0; $i < 25; $i++) {
            $seen[] = CwmtemplatesController::exportTempPath();
        }

        $this->assertCount(25, array_unique($seen), 'The staging path repeats, so it is guessable.');
    }

    /**
     * ⚠️ The structural half of the fix, asserted as a contract: the builder
     * takes no arguments, so no caller can pass it a title however the code
     * around it is later rearranged.
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('The staging path builder accepts no caller input')]
    public function testTempPathBuilderTakesNoArguments(): void
    {
        $method = new \ReflectionMethod(CwmtemplatesController::class, 'exportTempPath');

        $this->assertSame(
            0,
            $method->getNumberOfParameters(),
            'exportTempPath() gained a parameter; a title can reach the path again.'
        );
    }
}
