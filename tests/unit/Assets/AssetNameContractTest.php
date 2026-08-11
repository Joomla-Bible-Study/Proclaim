<?php

/**
 * ACL asset names are a contract with admin/access.xml, and nothing enforces it.
 *
 * `Access::getAssetRules()` walks up to the parent asset when a name does not
 * resolve, so a misspelled or undeclared section never errors -- it silently
 * inherits the component's rules and looks correct. Every section check today
 * resolves to `com_proclaim` anyway, because no section assets exist, which
 * hides the mistake completely.
 *
 * That is exactly how five wrong names reached production (#1653): a class
 * prefix leaking into an asset name (`com_proclaim.cwmserver` against declared
 * `server`), a dropped letter (`templatcode`), an underscore that access.xml
 * does not use (`message_type`), and a section written but never declared.
 *
 * The moment section assets exist, the correct names start honouring section
 * rules and the wrong ones keep falling back -- so the feature looks broken
 * rather than the names looking wrong. This test reads both sides and asserts
 * they agree, before that divergence can happen.
 *
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since __DEPLOY_VERSION__
 */

namespace CWM\Component\Proclaim\Tests\Assets;

use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Binds every ACL asset name in the codebase to a section in admin/access.xml.
 *
 * @since __DEPLOY_VERSION__
 */
class AssetNameContractTest extends TestCase
{
    /**
     * Sections written to #__assets that access.xml does not declare.
     *
     * These are real mismatches, deliberately not fixed here. Unlike the names
     * that live only in code, each of these has already been persisted as an
     * #__assets row on every existing site, so correcting them means renaming
     * live rows through Joomla's nested set rather than editing a string --
     * see #1653 items 3-5.
     *
     * This list must only ever shrink. Adding to it means shipping a section
     * whose permissions govern nothing.
     *
     * @var array<string, string>
     * @since __DEPLOY_VERSION__
     */
    private const KNOWN_UNDECLARED = [
        'message_type' => 'CwmmessagetypeTable writes message_type; access.xml declares messagetype (#1653)',
        'cwmadmin'     => 'CwmadminTable writes cwmadmin; access.xml declares admin (#1653)',
        'studytopics'  => 'CwmstudytopicsTable writes an asset for a section access.xml never declares (#1653)',
    ];

    /**
     * Section names declared in admin/access.xml.
     *
     * @return  list<string>
     * @since __DEPLOY_VERSION__
     */
    private static function declaredSections(): array
    {
        $doc = new \DOMDocument();
        $doc->load(self::root() . '/admin/access.xml');

        $names = [];

        foreach ((new \DOMXPath($doc))->query('/access/section') as $section) {
            $names[] = $section->getAttribute('name');
        }

        return $names;
    }

    /**
     * Repository root.
     *
     * @return  string
     * @since __DEPLOY_VERSION__
     */
    private static function root(): string
    {
        return \dirname(__DIR__, 3);
    }

    /**
     * Every PHP file that could carry an asset name.
     *
     * @return  list<string>
     * @since __DEPLOY_VERSION__
     */
    private static function sourceFiles(): array
    {
        $files = [];

        foreach (['admin/src', 'site/src', 'api/src', 'admin/tmpl', 'site/tmpl'] as $dir) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(self::root() . '/' . $dir, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->getExtension() === 'php') {
                    $files[] = $file->getPathname();
                }
            }
        }

        return $files;
    }

    /**
     * A file's PHP source with comments removed.
     *
     * These tests match call shapes with regular expressions, and the docblocks
     * on this feature quote the very calls being guarded against -- explaining
     * why `getActions('com_proclaim', 'teacher')` is inert is the whole point of
     * the comment on `Cwmassets::sectionActions()`. Scanning raw text flags the
     * explanation as the offence.
     *
     * @param   string  $file  Absolute path to a PHP file
     *
     * @return  string
     * @since __DEPLOY_VERSION__
     */
    private static function codeWithoutComments(string $file): string
    {
        $code = '';

        foreach (token_get_all((string) file_get_contents($file)) as $token) {
            if (\is_array($token)) {
                if ($token[0] === \T_COMMENT || $token[0] === \T_DOC_COMMENT) {
                    continue;
                }

                $code .= $token[1];

                continue;
            }

            $code .= $token;
        }

        return $code;
    }

    #[TestDox('every section passed to authorise() is declared in access.xml')]
    public function testAuthoriseTargetsAreDeclaredSections(): void
    {
        $declared = self::declaredSections();
        $offences = [];

        foreach (self::sourceFiles() as $file) {
            $code = self::codeWithoutComments($file);

            // Only authorise() calls. loadForm('com_proclaim.server.<type>'),
            // setUserState() and $typeAlias share the prefix but are unrelated
            // namespaces -- renaming those would break forms and UCM types.
            preg_match_all(
                '/authorise\(\s*[\'"][^\'"]+[\'"]\s*,\s*[\'"]com_proclaim\.([a-z_]+)/i',
                $code,
                $matches,
                PREG_SET_ORDER
            );

            foreach ($matches as $match) {
                if (!\in_array($match[1], $declared, true)) {
                    $offences[] = str_replace(self::root() . '/', '', $file) . ' -> com_proclaim.' . $match[1];
                }
            }
        }

        $this->assertSame(
            [],
            array_values(array_unique($offences)),
            "authorise() named a section admin/access.xml does not declare.\n"
            . "The check silently inherits the component's rules, so it looks correct until\n"
            . "section assets exist. Declare the section, or correct the name."
        );
    }

    #[TestDox('every section passed to Cwmassets::sectionActions() is declared in access.xml')]
    public function testSectionActionsTargetsAreDeclaredSections(): void
    {
        $declared = self::declaredSections();
        $offences = [];

        foreach (self::sourceFiles() as $file) {
            $code = self::codeWithoutComments($file);

            preg_match_all('/sectionActions\(\s*[\'"]([a-z_]+)[\'"]/i', $code, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                if (!\in_array($match[1], $declared, true)) {
                    $offences[] = str_replace(self::root() . '/', '', $file) . ' -> ' . $match[1];
                }
            }
        }

        $this->assertSame(
            [],
            array_values(array_unique($offences)),
            "Cwmassets::sectionActions() named a section admin/access.xml does not declare.\n"
            . "It falls back to the component's own permissions, so the toolbar looks correct\n"
            . 'while the section rules govern nothing. Declare the section, or correct the name.'
        );
    }

    #[TestDox('nothing passes a section to ContentHelper::getActions() without an item id')]
    public function testGetActionsIsNeverGivenASectionWithoutAnId(): void
    {
        // Core drops the section unless an id comes with it, so the two-argument
        // form reads as section-scoped and is not. Use sectionActions() instead.
        $offences = [];

        foreach (self::sourceFiles() as $file) {
            $code = self::codeWithoutComments($file);

            preg_match_all(
                '/getActions\(\s*[\'"]com_proclaim[\'"]\s*,\s*[\'"]([a-z_]+)[\'"]\s*([,)])/i',
                $code,
                $matches,
                PREG_SET_ORDER
            );

            foreach ($matches as $match) {
                // A third argument follows, so the section genuinely reaches the asset name.
                if ($match[2] === ',') {
                    continue;
                }

                $offences[] = str_replace(self::root() . '/', '', $file) . " -> getActions('com_proclaim', '{$match[1]}')";
            }
        }

        $this->assertSame(
            [],
            array_values(array_unique($offences)),
            "ContentHelper::getActions() was given a section but no item id, so core drops the\n"
            . "section and answers for com_proclaim. Use Cwmassets::sectionActions('<section>')\n"
            . 'for a section question, or pass the item id for an item question.'
        );
    }

    #[TestDox('every controller $aclSection is declared in access.xml')]
    public function testControllerSectionsAreDeclaredSections(): void
    {
        // Built from a property, so the authorise() scan above cannot see it.
        $declared = self::declaredSections();
        $offences = [];

        foreach (glob(self::root() . '/admin/src/Controller/*.php') as $file) {
            $code = self::codeWithoutComments($file);

            if (!preg_match('/\$aclSection\s*=\s*[\'"]([a-z_]*)[\'"]/i', $code, $match)) {
                continue;
            }

            if (!\in_array($match[1], $declared, true)) {
                $offences[] = basename($file) . ' -> ' . $match[1];
            }
        }

        $this->assertSame(
            [],
            $offences,
            "A controller gates create and edit on a section admin/access.xml does not declare.\n"
            . 'The asset never resolves, so the check falls back to the component and the '
            . 'section rule governs nothing.'
        );
    }

    #[TestDox("Cwmassets' entity section map matches the controllers' \$aclSection")]
    public function testEntitySectionMapMatchesTheControllers(): void
    {
        // The edit form is gated from the map and the save from $aclSection.
        // A mismatch gates the two on different sections.
        $lib = self::codeWithoutComments(self::root() . '/admin/src/Lib/Cwmassets.php');

        preg_match_all(
            '/[\'"](cwm[a-z]+)[\'"]\s*=>\s*[\'"]([a-z_]+)[\'"]/i',
            substr($lib, strpos($lib, 'SECTION_BY_VIEW')),
            $matches,
            PREG_SET_ORDER
        );

        $this->assertNotEmpty($matches, 'No entity section map found; the guard would be vacuous.');

        $declared    = self::declaredSections();
        $mismatches  = [];

        foreach ($matches as [, $view, $section]) {
            if (!\in_array($section, $declared, true)) {
                $mismatches[] = "{$view}: '{$section}' is not declared in access.xml";
            }

            $file = self::root() . '/admin/src/Controller/' . ucfirst($view) . 'Controller.php';

            if (!is_file($file)) {
                $mismatches[] = $view . ' -> no such controller';

                continue;
            }

            preg_match('/\$aclSection\s*=\s*[\'"]([a-z_]*)[\'"]/i', self::codeWithoutComments($file), $own);

            if (($own[1] ?? null) !== $section) {
                $mismatches[] = $view . ": map says '{$section}', controller says '" . ($own[1] ?? 'nothing') . "'";
            }
        }

        $this->assertSame([], $mismatches, 'The edit form and its save are gated on different sections.');
    }

    #[TestDox('every asset name written by a Table is declared in access.xml')]
    public function testTableAssetNamesAreDeclaredSections(): void
    {
        $declared = self::declaredSections();
        $offences = [];

        foreach (glob(self::root() . '/admin/src/Table/*.php') as $file) {
            $code = self::codeWithoutComments($file);

            if (!preg_match('/function\s+_getAssetName\b(.+?)\n    }/s', $code, $body)) {
                continue;
            }

            if (!preg_match('/[\'"]com_proclaim\.([a-z_]+)/i', $body[1], $match)) {
                continue;
            }

            $section = $match[1];

            if (\in_array($section, $declared, true) || isset(self::KNOWN_UNDECLARED[$section])) {
                continue;
            }

            $offences[] = basename($file) . ' -> com_proclaim.' . $section;
        }

        $this->assertSame(
            [],
            $offences,
            "A Table writes an #__assets row for a section admin/access.xml does not declare.\n"
            . 'Permissions set on that section would govern nothing.'
        );
    }

    #[TestDox('the known-undeclared list only shrinks')]
    public function testKnownUndeclaredEntriesStillApply(): void
    {
        $declared = self::declaredSections();

        foreach (self::KNOWN_UNDECLARED as $section => $why) {
            $this->assertNotContains(
                $section,
                $declared,
                "'{$section}' is now declared in access.xml, so it no longer belongs in "
                . 'KNOWN_UNDECLARED. Remove the entry: ' . $why
            );
        }
    }
}
