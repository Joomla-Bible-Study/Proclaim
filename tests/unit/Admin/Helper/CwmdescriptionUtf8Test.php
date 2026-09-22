<?php

/**
 * The description template's separator cleanup ate the last byte of multibyte
 * characters at end of line.
 *
 * Its class holds an em and an en dash, but the pattern had no /u, so PCRE
 * matched their individual bytes — E2 80 94, E2 80 93 and 2D. Any character
 * whose UTF-8 encoding ends in 0x80, 0x93 or 0x94 therefore matched at end of
 * line and lost its trailing byte, leaving a string that is no longer UTF-8:
 * À (C3 80), Ô (C3 94), œ (C5 93), Cyrillic Г/Д (D0 93/94), and others.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 *
 * @since __DEPLOY_VERSION__
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmdescriptionHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * @since __DEPLOY_VERSION__
 */
class CwmdescriptionUtf8Test extends ProclaimTestCase
{
    /**
     * Characters whose encoding ends in one of the bytes the dash class held.
     *
     * @return  array<string, array{0: string}>
     * @since __DEPLOY_VERSION__
     */
    public static function trailingByteProvider(): array
    {
        return [
            'oe ligature (C5 93)'  => ['Bœ'],
            'A grave (C3 80)'      => ['À'],
            'O circumflex (C3 94)' => ['Ô'],
            'Cyrillic Ge (D0 93)'  => ['Г'],
            'Cyrillic De (D0 94)'  => ['Д'],
        ];
    }

    /**
     * @param   string  $title  A title ending in a vulnerable character.
     *
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    #[DataProvider('trailingByteProvider')]
    #[TestDox('A title ending in a multibyte character survives the separator cleanup')]
    public function testMultibyteTitleSurvives(string $title): void
    {
        $result = $this->applyTemplate('{title}', ['title' => $title]);

        $this->assertTrue(mb_check_encoding($result, 'UTF-8'), 'The result must still be valid UTF-8');
        $this->assertSame($title, $result, 'No byte of the title may be stripped');
    }

    /**
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('A genuine trailing separator is still removed')]
    public function testTrailingSeparatorIsStillStripped(): void
    {
        $this->assertSame('Sermon', $this->applyTemplate('{title} — ', ['title' => 'Sermon']));
        $this->assertSame('Sermon', $this->applyTemplate('{title} - ', ['title' => 'Sermon']));
        $this->assertSame('Sermon', $this->applyTemplate('{title} – ', ['title' => 'Sermon']));
    }

    /**
     * With /u, preg_replace returns null on a subject that is not valid UTF-8.
     * The helper is typed to return a string, so it must keep the text rather
     * than blank the description.
     *
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('Input that is already invalid UTF-8 is preserved, not blanked')]
    public function testInvalidUtf8InputIsNotBlanked(): void
    {
        // A lone continuation byte: never valid UTF-8.
        $broken = "Title \xC3";

        $result = $this->applyTemplate('{title}', ['title' => $broken]);

        $this->assertNotSame('', $result, 'A broken title must not blank the whole description');
        $this->assertStringContainsString('Title', $result);
    }

    /**
     * @param   string                $format  Template format string.
     * @param   array<string, string> $data    Placeholder values.
     *
     * @return  string
     * @since __DEPLOY_VERSION__
     */
    private function applyTemplate(string $format, array $data): string
    {
        return (new \ReflectionMethod(CwmdescriptionHelper::class, 'applyTemplate'))
            ->invoke(null, $format, $data);
    }
}
