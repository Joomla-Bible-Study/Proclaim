<?php

/**
 * Unit tests for CwmprotectedMove eligibility
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmprotectedMove;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * The refusal rules are the feature. Moving a file is one rename; deciding
 * which files must never be offered the move is what the issue is about, and
 * the podcast rule above all: a feed's enclosure is a direct URL, so a
 * protected file cannot reach a subscriber at all. Refused, not warned — a
 * warning can be clicked past.
 *
 * Pure over the row on purpose, so every rule can be asserted without a
 * database or a filesystem.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmprotectedMove::class)]
class CwmprotectedMoveTest extends ProclaimTestCase
{
    /**
     * A row that passes every rule; each test breaks exactly one thing.
     *
     * @param   array<string, mixed>  $overrides  Fields to change.
     *
     * @return  object
     */
    private static function row(array $overrides = []): object
    {
        return (object) array_merge([
            'server_type'   => 'local',
            'server_params' => '{"protected_storage":"1","path":""}',
            'podcast_id'    => '',
            'params'        => '{"filename":"/images/biblestudy/media/sermon.mp3"}',
        ], $overrides);
    }

    #[TestDox('A local file on an opted-in server is eligible')]
    public function testEligibleRowIsEligible(): void
    {
        // ⚠️ Positive control. Every refusal case below builds on this row, so
        // if the baseline itself is refused, each of them passes by breaking
        // nothing — and the action would be offered to nobody.
        $this->assertNull(
            CwmprotectedMove::refusal(self::row()),
            'The baseline row was refused; every refusal test below is now proving nothing.'
        );
    }

    /**
     * @return  array<string, array{0: array<string, mixed>, 1: string}>
     */
    public static function refusals(): array
    {
        return [
            'a YouTube server'  => [['server_type' => 'youtube'], 'JBS_MED_PROTECT_REFUSED_SERVER_TYPE'],
            'no server at all'  => [['server_type' => null], 'JBS_MED_PROTECT_REFUSED_SERVER_TYPE'],
            'switch off'        => [['server_params' => '{"path":""}'], 'JBS_MED_PROTECT_REFUSED_SERVER_OFF'],
            'switch explicit 0' => [['server_params' => '{"protected_storage":"0"}'], 'JBS_MED_PROTECT_REFUSED_SERVER_OFF'],
            'podcast reference' => [['podcast_id' => '2'], 'JBS_MED_PROTECT_REFUSED_PODCAST'],
            'podcast list'      => [['podcast_id' => '0,3'], 'JBS_MED_PROTECT_REFUSED_PODCAST'],
            'no filename'       => [['params' => '{}'], 'JBS_MED_PROTECT_REFUSED_NO_FILE'],
            'a full URL'        => [['params' => '{"filename":"https://cdn.example.com/x.mp3"}'], 'JBS_MED_PROTECT_REFUSED_REMOTE'],
            'protocol-relative' => [['params' => '{"filename":"//cdn.example.com/x.mp3"}'], 'JBS_MED_PROTECT_REFUSED_REMOTE'],
        ];
    }

    /**
     * @param   array<string, mixed>  $overrides  What to break.
     * @param   string                $expected   The refusal that must name it.
     *
     * @return  void
     */
    #[DataProvider('refusals')]
    #[TestDox('Each rule refuses on its own, by name')]
    public function testRefusals(array $overrides, string $expected): void
    {
        $this->assertSame($expected, CwmprotectedMove::refusal(self::row($overrides)));
    }

    /**
     * @return  array<string, array{0: ?string, 1: bool}>
     */
    public static function podcastValues(): array
    {
        return [
            // The three shapes "no podcast" takes on real sites.
            'empty string' => ['', false],
            'null'         => [null, false],
            'zero'         => ['0', false],
            'minus one'    => ['-1', false],
            // Referenced, in the shapes FIND_IN_SET reads.
            'one id'      => ['2', true],
            'a list'      => ['2,4', true],
            'zero + real' => ['0,3', true],
            'spaced list' => [' 2 , 4 ', true],
        ];
    }

    /**
     * @param   ?string  $value     The raw column value.
     * @param   bool     $expected  Whether it references a podcast.
     *
     * @return  void
     */
    #[DataProvider('podcastValues')]
    #[TestDox('podcast_id is read the way the feed reads it')]
    public function testPodcastReferenced(?string $value, bool $expected): void
    {
        $this->assertSame($expected, CwmprotectedMove::podcastReferenced($value));
    }
}
