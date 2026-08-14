<?php

/**
 * The update tells the administrator what it did.
 *
 * An update that skipped every migration looked exactly like one that ran them
 * all: a spinner, then silence. The step runner records what happened and this
 * reports it.
 *
 * ⚠️ The markup is constrained by machinery outside this repository. Joomla
 * renders queued messages client-side through `Joomla.sanitizeHtml`, against an
 * allowlist that contains no `table`, `tr` or `td` and no `style` attribute on
 * anything. A report built as a styled table arrives as stripped text, and
 * nothing here or in CI would have said so. These assertions are that allowlist.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Lib;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * @since  __DEPLOY_VERSION__
 */
class MigrationReportTest extends ProclaimTestCase
{
    /**
     * Tags Joomla's message sanitiser accepts, from
     * media/system/js/core.js `DefaultAllowlist` (Joomla 6.1).
     *
     * @since  __DEPLOY_VERSION__
     */
    private const ALLOWED_TAGS = [
        'a', 'area', 'b', 'br', 'col', 'code', 'div', 'em', 'hr', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'i', 'img', 'li', 'ol', 'p', 'pre', 's', 'small', 'span', 'sub', 'sup', 'strong', 'u', 'ul',
        'button', 'input', 'select', 'textarea', 'option', 'details', 'summary',
    ];

    /**
     * @return  string  The manifest script's source
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function script(): string
    {
        return (string) file_get_contents(\dirname(__DIR__, 4) . '/proclaim.script.php');
    }

    /**
     * Just the report builder, so tag assertions do not pick up the whole file.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function reportBody(): string
    {
        $source = self::script();
        $start  = strpos($source, 'private function reportMigrations(): void');

        self::assertNotFalse($start, 'reportMigrations() could not be found.');

        $end = strpos($source, "\n    }\n", $start);

        return substr($source, $start, $end - $start);
    }

    /**
     * @return  array<string, array{0: string}>
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function forbiddenMarkup(): array
    {
        return [
            'a table'         => ['<table'],
            'a table row'     => ['<tr'],
            'a table cell'    => ['<td'],
            'a table header'  => ['<th'],
            'an inline style' => ['style="'],
        ];
    }

    /**
     * @param   string  $needle  Markup the sanitiser would strip
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[DataProvider('forbiddenMarkup')]
    #[TestDox('the report does not use $_dataName, which the sanitiser strips')]
    public function testTheReportAvoidsStrippedMarkup(string $needle): void
    {
        self::assertStringNotContainsString(
            $needle,
            self::reportBody(),
            'Joomla renders queued messages through Joomla.sanitizeHtml, whose allowlist has no table elements '
            . 'and no style attribute. This would reach the administrator as stripped text, with no error '
            . 'anywhere to say why.'
        );
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('every tag the report emits is on the sanitiser allowlist')]
    public function testEveryTagIsAllowed(): void
    {
        preg_match_all('~<([a-z][a-z0-9]*)~i', self::reportBody(), $matches);

        $used = array_unique(array_map('strtolower', $matches[1]));
        $bad  = array_diff($used, self::ALLOWED_TAGS);

        self::assertSame(
            [],
            array_values($bad),
            'These tags are not on Joomla.sanitizeHtml\'s allowlist and would be stripped: '
            . implode(', ', $bad)
        );
    }

    /**
     * ⚠️ enqueueMessage survives the redirect postflight sets; printed output is
     * not guaranteed to.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('the report is queued rather than printed')]
    public function testTheReportIsQueued(): void
    {
        $body = self::reportBody();

        self::assertStringContainsString(
            'enqueueMessage(',
            $body,
            'postflight ends by setting a redirect to Proclaim\'s own view. A queued message survives that; '
            . 'printed output relies on com_installer rendering its extension message, which the redirect '
            . 'bypasses.'
        );

        self::assertStringNotContainsString(
            'echo ',
            $body,
            'The report must not print.'
        );
    }

    /**
     * A report on every update, saying nothing happened, is just more noise.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('a wholly uneventful update reports nothing')]
    public function testSilenceWhenNothingHappened(): void
    {
        $body = self::reportBody();

        self::assertStringContainsString(
            '$this->stepLog === []',
            $body,
            'A fresh install records no steps and must produce no report.'
        );

        self::assertMatchesRegularExpression(
            '~\$ran === \[\] && \$failed === \[\] && \$total < ~',
            $body,
            'An update where every step was skipped and nothing took measurable time should stay quiet.'
        );
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('the report is called from postflight, and every step is timed')]
    public function testWiring(): void
    {
        $source = self::script();

        self::assertStringContainsString(
            '$this->reportMigrations();',
            $source,
            'The report is built but never delivered.'
        );

        self::assertStringContainsString(
            'microtime(true)',
            $source,
            'Steps are not timed, so the report cannot say how long anything took — which is the question '
            . 'nobody could answer when this was investigated.'
        );

        self::assertSame(
            8,
            preg_match_all('~\$this->step\(~', $source),
            'Every legacy migration should go through step(), so it is both gated and recorded.'
        );
    }
}
