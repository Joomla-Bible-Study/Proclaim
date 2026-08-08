<?php

/**
 * Integration tests for Cwmhelper URL building and remote size lookups.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\Cwmhelper;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\Registry\Registry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression coverage for #1576.
 *
 * 1. mediaBuildUrl() declared a non-nullable Registry, but Cwmmedia::
 *    getAVmediacode() passes null for it whenever a record uses the legacy
 *    inline player. Object type declarations are never weak-coerced, so that
 *    was a TypeError -- a fatal instead of a rendered video.
 *
 * 2. getRemoteFileSize() computed the host for its streaming-platform skip
 *    before normalising the URL's scheme. parse_url() reports no host for a
 *    schemeless value, so the skip never fired for exactly the stored shape the
 *    normalisation below it exists to repair, and a real HEAD request went out
 *    to the streaming platform.
 *
 * 3. mediaBuildUrl() read $_SERVER['HTTP_HOST'] unguarded. The podcast feed
 *    rebuild runs from the CLI scheduler, where that key does not exist, so
 *    every media file emitted a warning. The value fed a block whose result was
 *    discarded.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(Cwmhelper::class)]
class CwmhelperUrlTest extends IntegrationTestCase
{
    /** @var mixed */
    private mixed $savedHost = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->savedHost = $_SERVER['HTTP_HOST'] ?? null;
    }

    protected function tearDown(): void
    {
        if ($this->savedHost === null) {
            unset($_SERVER['HTTP_HOST']);
        } else {
            $_SERVER['HTTP_HOST'] = $this->savedHost;
        }

        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // Finding 1 -- the null a live caller passes
    // -------------------------------------------------------------------------

    #[TestDox('mediaBuildUrl accepts the null params its site caller passes')]
    public function testNullParamsIsAccepted(): void
    {
        $_SERVER['HTTP_HOST'] = 'example.org';

        // Exactly the call Cwmmedia::getAVmediacode() makes for the legacy
        // inline player.
        $result = Cwmhelper::mediaBuildUrl('media/sermons', 'talk.mp4', null);

        $this->assertSame(
            'media/sermons/talk.mp4',
            $result,
            'A live caller passes null here; a non-nullable Registry made that a fatal — see #1576'
        );
    }

    #[TestDox('mediaBuildUrl still works when params are supplied')]
    public function testRegistryParamsStillHonoured(): void
    {
        $_SERVER['HTTP_HOST'] = 'example.org';

        $params = new Registry();
        $params->set('protocol', 'https://');

        $this->assertSame(
            'https://cdn.example.org/talk.mp4',
            Cwmhelper::mediaBuildUrl('cdn.example.org', 'talk.mp4', $params, true)
        );
    }

    /**
     * With no Registry the protocol has to fall back rather than dereference
     * null, since $setProtocol is what reaches that branch.
     */
    #[TestDox('A null Registry falls back to a default protocol')]
    public function testNullParamsFallsBackToDefaultProtocol(): void
    {
        $_SERVER['HTTP_HOST'] = 'example.org';

        $this->assertSame(
            'https://cdn.example.org/talk.mp4',
            Cwmhelper::mediaBuildUrl('cdn.example.org', 'talk.mp4', null, true)
        );
    }

    #[TestDox('An empty path yields an empty string, not false')]
    public function testEmptyPathReturnsAString(): void
    {
        $_SERVER['HTTP_HOST'] = 'example.org';

        $this->assertSame('', Cwmhelper::mediaBuildUrl('media', '', null));
    }

    // -------------------------------------------------------------------------
    // Finding 3 -- no HTTP request means no HTTP_HOST
    // -------------------------------------------------------------------------

    /**
     * The podcast feed rebuild runs under the CLI scheduler, where there is no
     * request at all. A warning per media file is noise in the task log, and
     * phpunit.xml's failOnWarning would turn it into a failure here.
     */
    #[TestDox('mediaBuildUrl works with no HTTP_HOST, as under the CLI scheduler')]
    public function testWorksWithoutHttpHost(): void
    {
        unset($_SERVER['HTTP_HOST']);

        $this->assertSame(
            'media/sermons/talk.mp4',
            Cwmhelper::mediaBuildUrl('media/sermons', 'talk.mp4', null),
            'The scheduled podcast rebuild has no request context — see #1576'
        );
    }

    // -------------------------------------------------------------------------
    // Finding 2 -- the streaming skip has to see a normalised URL
    // -------------------------------------------------------------------------

    /**
     * Clear the per-request file-size cache.
     *
     * @return  void
     */
    private function clearFileSizeCache(): void
    {
        $prop = new \ReflectionProperty(Cwmhelper::class, 'fileSizeCache');
        $prop->setValue(null, []);
    }

    /**
     * @return  array<string, int>  Current contents of the cache.
     */
    private function fileSizeCache(): array
    {
        $prop = new \ReflectionProperty(Cwmhelper::class, 'fileSizeCache');

        return (array) $prop->getValue();
    }

    /**
     * A schemeless stored URL is the shape the normalisation in this method
     * exists to repair, so it is precisely the case the skip must handle.
     *
     * Asserting only that 0 comes back proves nothing: a HEAD request that
     * fails also returns 0. The cache is the discriminator -- it is written
     * only once a lookup has been attempted, so an empty cache means the URL
     * was recognised and no request went out.
     *
     * @param   string  $url  Stored URL
     */
    #[DataProvider('streamingUrlProvider')]
    #[TestDox('Streaming platforms are skipped without a request, scheme or not')]
    public function testStreamingHostsAreSkipped(string $url): void
    {
        $this->clearFileSizeCache();

        $this->assertSame(0, Cwmhelper::getRemoteFileSize($url));
        $this->assertSame(
            [],
            $this->fileSizeCache(),
            'The host was read before the scheme was normalised, so the skip never fired and a real '
            . 'HEAD request went out to the streaming platform — see #1576'
        );
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function streamingUrlProvider(): array
    {
        return [
            'youtube with scheme'       => ['https://www.youtube.com/watch?v=abc123'],
            'youtube schemeless'        => ['youtube.com/watch?v=abc123'],
            'youtube protocol-relative' => ['//www.youtube.com/watch?v=abc123'],
            'youtu.be schemeless'       => ['youtu.be/abc123'],
            'vimeo schemeless'          => ['vimeo.com/123456'],
        ];
    }

    #[TestDox('An empty URL is not looked up')]
    public function testEmptyUrlReturnsZero(): void
    {
        $this->assertSame(0, Cwmhelper::getRemoteFileSize(''));
    }
}
