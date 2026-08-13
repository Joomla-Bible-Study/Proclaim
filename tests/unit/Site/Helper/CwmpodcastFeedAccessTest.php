<?php

/**
 * Contract test: the podcast feed must not list items the reader cannot see
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Helper;

use CWM\Component\Proclaim\Site\Helper\Cwmpodcast;
use PHPUnit\Framework\TestCase;

/**
 * A feed hands out the enclosure URL to whoever reads it, and podcast apps read
 * it with no session. Listing an item the reader was refused publishes the URL
 * of a file they cannot legitimately fetch — worse than the restriction simply
 * not applying (#1774).
 *
 * Asserted at source level rather than by running the query: getEpisodes()
 * needs a database and an application, and what matters is that the three
 * filters are present in the SQL the method builds.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmpodcastFeedAccessTest extends TestCase
{
    /**
     * The source of Cwmpodcast::getEpisodes().
     *
     * @return string
     */
    private static function episodeQuerySource(): string
    {
        $method = new \ReflectionMethod(Cwmpodcast::class, 'getEpisodes');
        $lines  = file($method->getFileName());

        return implode('', \array_slice(
            $lines,
            $method->getStartLine() - 1,
            $method->getEndLine() - $method->getStartLine() + 1
        ));
    }

    /**
     * Every link in the chain must be filtered, matching
     * Cwmdownload::isAccessible() and the sermon listings. Filtering only the
     * media file would still publish an episode whose message is restricted.
     */
    public function testTheFeedQueryFiltersOnTheWholeAccessChain(): void
    {
        $source = self::episodeQuerySource();

        foreach (['mf.access' => 'the media file', 's.access' => 'its message', 'se.access' => 'its series'] as $column => $what) {
            $this->assertStringContainsString(
                $column,
                $source,
                "getEpisodes() must filter on {$column} — otherwise the feed publishes the enclosure URL of an "
                . "episode whose access is restricted by {$what}."
            );
        }
    }

    /** The levels must come from the reader, not be hardcoded to Public. */
    public function testItFiltersAgainstTheReadersOwnViewLevels(): void
    {
        $this->assertStringContainsString(
            'getAuthorisedViewLevels',
            self::episodeQuerySource(),
            'The feed must filter against the fetching user\'s view levels rather than assuming a fixed level, '
            . 'so a site whose guests hold more than Public still gets a correct feed.'
        );
    }

    /**
     * Guards the two assertions above. A scan that finds nothing proves nothing
     * unless it is known to find something, and the same applies to a scan that
     * finds everything: this fails if episodeQuerySource() ever returns empty
     * or stops resolving the method.
     */
    public function testTheSourceScannerActuallyReadsTheMethod(): void
    {
        $source = self::episodeQuerySource();

        $this->assertNotSame('', trim($source), 'The scanner read nothing.');
        $this->assertStringContainsString(
            'FIND_IN_SET',
            $source,
            'The scanner is not reading getEpisodes() — its podcast_id membership test is missing.'
        );
        $this->assertStringNotContainsString(
            'function getEpisodes',
            substr($source, 20),
            'The scanner is reading past the end of the method.'
        );
    }
}
