<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Tests\Admin\Model;

use CWM\Component\Proclaim\Administrator\Model\CwmpodcastModel;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Regression test for #1509: podcast RSS feeds are static XML files written
 * by Cwmpodcast::makePodcasts(), which only ran from the admin "Write XML"
 * button or the scheduled task — never from saving the podcast settings
 * form. Confirmed live on nfsda.org: changing the "Podcast Image" field and
 * saving did not update <itunes:image>/<podcast:image> in the feed until
 * "Write XML" was run manually afterward.
 *
 * Fixed by having CwmpodcastModel::save() regenerate the feeds after a
 * successful save, matching the existing manual-button behavior.
 * Regeneration failures are logged, never allowed to fail the settings save
 * itself — a broken feed write shouldn't block an admin from saving
 * unrelated changes.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmpodcastModelTest extends ProclaimTestCase
{
    private static function methodBody(): string
    {
        $reflection = new \ReflectionMethod(CwmpodcastModel::class, 'save');
        $lines      = file($reflection->getFileName());

        return implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );
    }

    public function testSaveReturnsFalseImmediatelyWhenParentSaveFails(): void
    {
        $body = self::methodBody();

        $this->assertMatchesRegularExpression(
            '/if\s*\(\s*!\s*parent::save\(\$data\)\s*\)\s*\{\s*return\s+false;/s',
            $body,
            'save() must bail out before attempting feed regeneration when the underlying save fails — see #1509'
        );
    }

    public function testSaveTriggersFeedRegenerationOnSuccess(): void
    {
        $body = self::methodBody();

        $this->assertStringContainsString(
            'makePodcasts()',
            $body,
            'save() must call Cwmpodcast::makePodcasts() so settings changes (image, category, etc.) take ' .
                'effect immediately instead of waiting for a manual "Write XML" click or the scheduled task — see #1509'
        );
    }

    public function testFeedRegenerationFailureIsCaughtAndLoggedNotThrown(): void
    {
        $body = self::methodBody();

        $this->assertMatchesRegularExpression(
            '/catch\s*\(\s*\\\\Exception\s+\$e\s*\)\s*\{\s*Log::add/s',
            $body,
            'A feed regeneration failure must be caught and logged, not allowed to propagate and fail the ' .
                'settings save — see #1509'
        );
    }

    public function testSaveAlwaysReturnsTrueAfterASuccessfulParentSaveRegardlessOfRegenerationOutcome(): void
    {
        $body = self::methodBody();

        // The final statement must be `return true;`, not `return $filewritten;`
        // or similar — regeneration failing must not make the settings save
        // itself look like it failed.
        $this->assertMatchesRegularExpression(
            '/return\s+true;\s*\}\s*$/',
            $body,
            'save() must unconditionally return true once parent::save() succeeded, regardless of whether ' .
                'feed regeneration succeeded — see #1509'
        );
    }

    public function testCwmpodcastImportedFromSiteHelperNamespace(): void
    {
        $reflection = new \ReflectionClass(CwmpodcastModel::class);
        $contents   = file_get_contents($reflection->getFileName());

        $this->assertStringContainsString(
            'use CWM\Component\Proclaim\Site\Helper\Cwmpodcast;',
            $contents,
            'CwmpodcastModel must import the site-side Cwmpodcast helper (the same class the admin ' .
                '"Write XML" controller and the scheduled task both call) to regenerate feeds — see #1509'
        );
    }
}
