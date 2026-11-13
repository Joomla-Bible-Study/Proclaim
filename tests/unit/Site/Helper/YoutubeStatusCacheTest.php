<?php

/**
 * Part of Proclaim Package
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Site\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmyoutubeFileCache;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use CWM\Module\ProclaimYoutube\Site\Helper\ProclaimYoutubeHelper;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\Input\Input;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * The YouTube module's live-status checks answer from the file cache, so
 * repeated page loads and status polls share one YouTube API call.
 *
 * Each test caches a status the API path could not produce for this unknown
 * server, then checks that the status comes back: a cache that stopped
 * working would fall through to the API path and fail the assertion.
 *
 * @since  __DEPLOY_VERSION__
 */
class YoutubeStatusCacheTest extends ProclaimTestCase
{
    private const int SERVER_ID = 999998;

    private const string VIDEO_ID = 'zzStatusVid';

    private string $cacheDir;

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->cacheDir = JPATH_ROOT . '/media/com_proclaim/youtube_cache/' . substr(md5(JPATH_CACHE), 0, 8);
        $this->removeCacheDir();
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function tearDown(): void
    {
        $this->removeCacheDir();

        parent::tearDown();
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('A page load is told the cached live status rather than asking YouTube again')]
    public function testPageLoadUsesTheCachedStatus(): void
    {
        CwmyoutubeFileCache::storeVideoCache(
            'mod_proclaim_youtube_verify_' . self::SERVER_ID . '_' . self::VIDEO_ID,
            ['isLive' => true, 'isUpcoming' => false, 'scheduledStartTime' => '2026-11-15T15:00:00Z'],
            ProclaimYoutubeHelper::STATUS_CACHE_TTL
        );

        $video = (new ProclaimYoutubeHelper())->verifyLiveStatus(
            ['videoId' => self::VIDEO_ID, 'isLive' => false, 'isUpcoming' => true],
            self::SERVER_ID
        );

        $this->assertTrue($video['isLive']);
        $this->assertFalse($video['isUpcoming']);
        $this->assertSame('2026-11-15T15:00:00Z', $video['scheduledStartTime']);
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('A browser\'s status poll is answered from the cache rather than asking YouTube again')]
    public function testStatusPollUsesTheCachedStatus(): void
    {
        $cached = ['success' => true, 'isLive' => true, 'isUpcoming' => false, 'videoId' => self::VIDEO_ID];

        CwmyoutubeFileCache::storeVideoCache(
            'mod_proclaim_youtube_status_' . self::SERVER_ID . '_' . self::VIDEO_ID,
            $cached,
            ProclaimYoutubeHelper::STATUS_CACHE_TTL
        );

        $this->assertSame($cached, $this->poll(self::VIDEO_ID));
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('A poll\'s answer is cached for the next poll')]
    public function testPollAnswerIsCachedForTheNextPoll(): void
    {
        // With no video to check, the answer is worked out without YouTube.
        $status = $this->poll('');

        $this->assertSame($status, CwmyoutubeFileCache::getVideoCache('mod_proclaim_youtube_status_' . self::SERVER_ID . '_'));
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('A token for one video cannot ask about another, so a poll cannot fill the cache with videos of its own choosing')]
    public function testTokenIsBoundToItsVideo(): void
    {
        $status = $this->poll('zzOtherVideo', self::VIDEO_ID);

        $this->assertFalse($status['success']);
        $this->assertNull(CwmyoutubeFileCache::getVideoCache('mod_proclaim_youtube_status_' . self::SERVER_ID . '_zzOtherVideo'));
    }

    /**
     * A browser's status poll for a video, answered under a stand-in
     * application that carries the request and the site secret.
     *
     * @param   string       $videoId   The video to ask about
     * @param   string|null  $tokenFor  The video the token was minted for; the asked-about one when null
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    private function poll(string $videoId, ?string $tokenFor = null): array
    {
        $saved = Factory::$application;
        $app   = $this->createStub(CMSApplicationInterface::class);
        $app->method('get')->willReturnCallback(
            static fn (string $key, $default = null) => $key === 'secret' ? 'zz-test-secret' : $default
        );

        try {
            Factory::$application = $app;

            $app->method('getInput')->willReturn(new Input([
                'server_id' => self::SERVER_ID,
                'video_id'  => $videoId,
                'token'     => ProclaimYoutubeHelper::generateStatusToken(self::SERVER_ID, $tokenFor ?? $videoId),
            ]));

            return (new ProclaimYoutubeHelper())->getStatusAjax();
        } finally {
            Factory::$application = $saved;
        }
    }

    /**
     * Remove the site's cache folder and its parents once empty, as
     * CwmyoutubeFileCacheTest does: a leftover media/com_proclaim/ under the
     * repository makes cwm-verify misread every dev site's media link.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function removeCacheDir(): void
    {
        if (is_dir($this->cacheDir)) {
            foreach (scandir($this->cacheDir) as $entry) {
                if ($entry !== '.' && $entry !== '..') {
                    @unlink($this->cacheDir . '/' . $entry);
                }
            }

            @rmdir($this->cacheDir);
        }

        @rmdir(\dirname($this->cacheDir));
        @rmdir(\dirname($this->cacheDir, 2));
    }
}
