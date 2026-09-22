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
 * Every class the install script names must resolve to an import.
 *
 * ⚠️ The script has no namespace, so an unimported `Foo::bar()` resolves to a
 * global `\Foo` and PHP raises an **Error** — which `catch (\Exception $e)` does
 * not catch. The failure is a fatal, not a caught one.
 *
 * This is not hypothetical. `DatabaseDriver::splitSql()` was written into
 * dropTablesIfRequested() a month after a refactor removed the import, and sat
 * there from 2026-03-23. That path runs on uninstall when the administrator has
 * opted into dropping tables, and it deletes the `#__assets` rows *before*
 * reaching the fatal — so an uninstall destroyed the ACL rows, then died without
 * dropping a single table.
 *
 * Nothing caught it for five months: it only runs on uninstall, only with
 * drop_tables set, and the install script is not exercised by the unit suite.
 *
 * @since  __DEPLOY_VERSION__
 */
class InstallScriptImportsTest extends ProclaimTestCase
{
    /**
     * Language keywords that appear where a class name would.
     *
     * ⚠️ Everything else is decided by asking whether the name resolves, not by
     * listing it here. A hand-kept list of PHP's globals is a list that goes
     * stale: this one omitted ReflectionObject, and `new ReflectionObject()` —
     * correct code, since the file has no namespace and the global class
     * exists — was reported as an offender.
     *
     * @var    string[]
     * @since  __DEPLOY_VERSION__
     */
    private const KEYWORDS = ['self', 'parent', 'static'];

    /**
     * The script source with comments and string literals removed.
     *
     * Tokenised rather than pattern-matched: the docblocks in this file name
     * classes constantly (`CwmtemplatecodeTable::store()`, `FileStorage::…`),
     * and one branch tests for the literal string `'JHtml::addIncludePath'`.
     * A regex over raw text reports all of those and is worse than no test.
     *
     * @return  array<int, array{name: string, line: int}>  Static-call targets
     *
     * @since   __DEPLOY_VERSION__
     */
    private function staticCallTargets(): array
    {
        $source = (string) file_get_contents(\dirname(__DIR__, 3) . '/proclaim.script.php');
        $tokens = token_get_all($source);
        $found  = [];

        foreach ($tokens as $i => $token) {
            if (!\is_array($token) || $token[0] !== \T_STRING) {
                continue;
            }

            // Four shapes name a class inside a method body, and all four fail
            // the same way when the name is not imported: a global class that
            // does not exist, and an Error at the moment the line runs.
            $next = $tokens[$i + 1] ?? null;
            $back = $this->previousMeaningful($tokens, $i);

            $isStaticCall = \is_array($next) && $next[0] === \T_DOUBLE_COLON;
            $isNew        = \is_array($back) && $back[0] === \T_NEW;
            $isInstanceof = \is_array($back) && $back[0] === \T_INSTANCEOF;
            $isCatch      = $this->isInsideCatch($tokens, $i);

            if (!$isStaticCall && !$isNew && !$isInstanceof && !$isCatch) {
                continue;
            }

            // A leading backslash makes it explicitly global and always valid.
            $prev = $tokens[$i - 1] ?? null;

            if (\is_array($prev) && $prev[0] === \T_NS_SEPARATOR) {
                continue;
            }

            if ($prev === '\\') {
                continue;
            }

            $found[] = ['name' => $token[1], 'line' => $token[2]];
        }

        return $found;
    }

    #[TestDox('Every class named in a static call in the install script is imported')]
    public function testEveryStaticCallTargetIsImported(): void
    {
        $source = (string) file_get_contents(\dirname(__DIR__, 3) . '/proclaim.script.php');

        preg_match_all('/^use\s+([\w\\\\]+?)(?:\s+as\s+(\w+))?;/m', $source, $matches, \PREG_SET_ORDER);

        $imported = [];

        foreach ($matches as $match) {
            $parts      = explode('\\', $match[1]);
            $imported[] = $match[2] ?? end($parts);
        }

        $usages = $this->staticCallTargets();

        $this->assertNotEmpty($usages, 'No class references found — the tokeniser is not seeing the file.');

        $offenders = [];

        foreach ($usages as $usage) {
            $name = $usage['name'];

            if (\in_array($name, self::KEYWORDS, true) || \in_array($name, $imported, true)) {
                continue;
            }

            // Unimported is fine when the bare name is a real global — the file
            // has no namespace, so `new ReflectionObject()` resolves. What is
            // not fine is a name that resolves to nothing, which is the
            // DatabaseDriver case: an Error at the moment the line runs.
            if (class_exists($name) || interface_exists($name) || enum_exists($name)) {
                continue;
            }

            $offenders[] = $name . '  at line ' . $usage['line'];
        }

        $this->assertSame(
            [],
            $offenders,
            "proclaim.script.php has no namespace, so an unimported class resolves to the global one and PHP\n"
            . "raises an Error — which catch (\\Exception) does not catch, making it a fatal. Add the use\n"
            . "statement, or prefix the call with a backslash if the global class is genuinely meant."
        );
    }

    /**
     * The token before $i that is not whitespace or a comment.
     *
     * @param   array  $tokens  token_get_all() output
     * @param   int    $i       Index to look back from
     *
     * @return  mixed
     *
     * @since   __DEPLOY_VERSION__
     */
    private function previousMeaningful(array $tokens, int $i): mixed
    {
        for ($j = $i - 1; $j >= 0; $j--) {
            if (\is_array($tokens[$j]) && \in_array($tokens[$j][0], [\T_WHITESPACE, \T_COMMENT, \T_DOC_COMMENT], true)) {
                continue;
            }

            return $tokens[$j];
        }

        return null;
    }

    /**
     * Whether $i sits inside the parentheses of a `catch`.
     *
     * @param   array  $tokens  token_get_all() output
     * @param   int    $i       Index to test
     *
     * @return  bool
     *
     * @since   __DEPLOY_VERSION__
     */
    private function isInsideCatch(array $tokens, int $i): bool
    {
        for ($j = $i - 1; $j >= 0 && $j > $i - 12; $j--) {
            $t = $tokens[$j];

            if ($t === ')' || $t === '{' || $t === ';') {
                return false;
            }

            if (\is_array($t) && $t[0] === \T_CATCH) {
                return true;
            }
        }

        return false;
    }

    /**
     * ⚠️ Type hints fail exactly as static calls do and were invisible here.
     *
     * The file has no namespace, so `private function f(Foo $x)` with no `use`
     * resolves to a global `\Foo`, and PHP raises an Error when the method is
     * called — not when the file is parsed, so `php -l` passes and the class
     * loads. #1981 added such a hint and its import had to be checked by hand
     * precisely because this test could not see it.
     *
     * Reflection rather than tokens: it reports the name PHP actually resolved
     * to, so an unimported hint arrives as the bare name and simply fails to
     * exist. Nothing has to be parsed or guessed.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('Every class named in a signature or property type resolves')]
    public function testEverySignatureTypeResolves(): void
    {
        if (!class_exists('com_proclaimInstallerScript', false)) {
            require_once \dirname(__DIR__, 3) . '/proclaim.script.php';
        }

        $class = new \ReflectionClass('com_proclaimInstallerScript');
        $names = [];

        foreach ($class->getMethods() as $method) {
            if ($method->getDeclaringClass()->getName() !== $class->getName()) {
                continue;
            }

            foreach ($method->getParameters() as $parameter) {
                $names += $this->typeNames($parameter->getType(), $method->getName() . '()');
            }

            $names += $this->typeNames($method->getReturnType(), $method->getName() . '() return');
        }

        foreach ($class->getProperties() as $property) {
            if ($property->getDeclaringClass()->getName() !== $class->getName()) {
                continue;
            }

            $names += $this->typeNames($property->getType(), '$' . $property->getName());
        }

        $this->assertNotEmpty($names, 'No declared types were found — this test would pass on nothing.');

        $offenders = [];

        foreach ($names as $name => $where) {
            if (class_exists($name) || interface_exists($name) || enum_exists($name)) {
                continue;
            }

            $offenders[] = $name . '  (' . $where . ')';
        }

        $this->assertSame(
            [],
            $offenders,
            "proclaim.script.php has no namespace, so a type hint whose class is not imported resolves\n"
            . "to a global one that does not exist. PHP raises an Error when the method is called, which\n"
            . 'php -l cannot see. Add the use statement.'
        );
    }

    /**
     * Class names in a declared type, keyed by name.
     *
     * @param   ?\ReflectionType  $type   The declared type, if any
     * @param   string            $where  Where it was declared, for the message
     *
     * @return  array<string, string>
     *
     * @since   __DEPLOY_VERSION__
     */
    private function typeNames(?\ReflectionType $type, string $where): array
    {
        if ($type === null) {
            return [];
        }

        // Union and intersection types hold several named types; a single one
        // arrives as a ReflectionNamedType on its own.
        $parts = $type instanceof \ReflectionNamedType ? [$type] : $type->getTypes();
        $found = [];

        foreach ($parts as $part) {
            if ($part instanceof \ReflectionNamedType && !$part->isBuiltin()) {
                $found[$part->getName()] = $where;
            }
        }

        return $found;
    }
}
