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
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\Input\Input;
use Joomla\Registry\Registry;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * The YouTube module's status polls answer from the file cache, so polls from
 * every visitor share one YouTube API call, and the page renders the status
 * the last poll found.
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
    #[TestDox('A page reloaded because the poll saw the stream go live renders it live, so the poll does not reload it again')]
    public function testPageRendersThePolledStatus(): void
    {
        $this->cacheVideo(['isLive' => false, 'isUpcoming' => true]);
        CwmyoutubeFileCache::storeVideoCache(
            'mod_proclaim_youtube_status_' . self::SERVER_ID . '_' . self::VIDEO_ID,
            ['success' => true, 'isLive' => true, 'isUpcoming' => false, 'videoId' => self::VIDEO_ID, 'scheduledStartTime' => '2026-11-15T15:00:00Z'],
            ProclaimYoutubeHelper::STATUS_CACHE_TTL
        );

        $video = $this->render();

        $this->assertTrue($video['isLive']);
        $this->assertFalse($video['isUpcoming']);
        $this->assertSame('2026-11-15T15:00:00Z', $video['scheduledStartTime']);
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('A status the poll only guessed because the quota ran out does not change the page')]
    public function testQuotaGuessDoesNotChangeThePage(): void
    {
        $this->cacheVideo(['isLive' => true, 'isUpcoming' => false]);
        CwmyoutubeFileCache::storeVideoCache(
            'mod_proclaim_youtube_status_' . self::SERVER_ID . '_' . self::VIDEO_ID,
            ['success' => true, 'isLive' => false, 'isUpcoming' => true, 'videoId' => self::VIDEO_ID, 'quotaExceeded' => true],
            ProclaimYoutubeHelper::STATUS_CACHE_TTL
        );

        $video = $this->render();

        $this->assertTrue($video['isLive']);
        $this->assertFalse($video['isUpcoming']);
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
     * Cache the module's video, as getVideo() stores it for a module with the
     * default settings, so the page renders without asking YouTube.
     *
     * @param   array  $status  The video's isLive and isUpcoming flags
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function cacheVideo(array $status): void
    {
        CwmyoutubeFileCache::storeVideoCache(
            'mod_proclaim_youtube_' . self::SERVER_ID . '_live_first_1_' . md5(''),
            ['videoId' => self::VIDEO_ID, 'title' => 'zz Status Video'] + $status
        );
    }

    /**
     * The video the module renders, for a module with the default settings.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    private function render(): array
    {
        // getVideo() takes the application but does not use it, and the class
        // is final, so it cannot be stubbed
        $app = (new \ReflectionClass(SiteApplication::class))->newInstanceWithoutConstructor();

        return (new ProclaimYoutubeHelper())->getVideo(new Registry(['server_id' => self::SERVER_ID]), $app);
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
