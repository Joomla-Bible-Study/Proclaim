<?php

/**
 * Integration tests for the live-broadcast capability contract (#1298 phase 1).
 *
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

declare(strict_types=1);

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Addons;

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use CWM\Component\Proclaim\Administrator\Addons\Servers\Youtube\CWMAddonYoutube;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use Joomla\Input\Input;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

/**
 * The three gates protect one promise: Proclaim never creates a broadcast
 * on a platform account unless the platform is capable, the server is in
 * Direct stream mode, and the administrator explicitly opted the record
 * in. Every test here exercises a refusal path — none can reach the
 * network, because each gate fails before an API call is possible
 * (an addon with no OAuth credentials cannot construct a client).
 *
 * @since __DEPLOY_VERSION__
 */
#[CoversClass(CWMAddonYoutube::class)]
#[CoversClass(CWMAddon::class)]
class CwmliveEventTest extends IntegrationTestCase
{
    /**
     * @var  DatabaseDriver
     */
    private DatabaseDriver $db;

    /**
     * Begin a transaction so seeded servers roll back.
     *
     * @return  void
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);
        $this->db->transactionStart();
    }

    /**
     * Roll the seeded rows back.
     *
     * @return  void
     */
    protected function tearDown(): void
    {
        // When setUp() skips (no DB), $this->db was never initialized and an
        // unconditional dereference turns every skip into an error on
        // DB-less machines.
        if (isset($this->db)) {
            $this->db->transactionRollback();
        }

        parent::tearDown();
    }

    #[Test]
    public function theBaseAddonDeclinesLiveEvents(): void
    {
        $addon = $this->minimalAddon();

        $this->assertFalse($addon->supportsLiveEvents(), 'Capability must be opt-in per addon');

        $result = $addon->createLiveEvent(new Input(['serverId' => 1, 'title' => 'X']));

        $this->assertFalse($result['success']);
        $this->assertNotSame('', (string) ($result['error'] ?? ''), 'A refusal must say why');
    }

    #[Test]
    public function youtubeDeclaresTheCapability(): void
    {
        $this->assertTrue($this->youtubeAddon()->supportsLiveEvents());
    }

    #[Test]
    public function missingTitleOrStartIsRefusedBeforeAnyGate(): void
    {
        $serverId = $this->insertServer(['stream_mode' => 'direct']);

        $result = $this->youtubeAddon()->createLiveEvent(new Input([
            'serverId'       => $serverId,
            'title'          => '',
            'scheduledStart' => '2026-08-02T15:00:00+00:00',
        ]));

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('JBS_ADDON_YOUTUBE_LIVE_MISSING_INPUT', $this->keyOf($result));
    }

    #[Test]
    public function anExternalRestreamerServerIsRefusedEvenWithFullInput(): void
    {
        // The restreamer owns the broadcast on an External server. This is
        // the gate that must hold even when a confused caller asks anyway.
        $serverId = $this->insertServer(['stream_mode' => 'external']);

        $result = $this->youtubeAddon()->createLiveEvent($this->fullInput($serverId));

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('JBS_ADDON_YOUTUBE_LIVE_NOT_DIRECT', $this->keyOf($result));
    }

    #[Test]
    public function aServerWithoutStreamModeDefaultsToExternal(): void
    {
        // Pre-#1298 servers carry no stream_mode at all. They must behave
        // as External — the safe reading — not silently become Direct.
        $serverId = $this->insertServer([]);

        $result = $this->youtubeAddon()->createLiveEvent($this->fullInput($serverId));

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('JBS_ADDON_YOUTUBE_LIVE_NOT_DIRECT', $this->keyOf($result));
    }

    #[Test]
    public function aDirectServerWithoutOauthIsRefusedWithTheConnectMessage(): void
    {
        $serverId = $this->insertServer(['stream_mode' => 'direct']);

        $result = $this->youtubeAddon()->createLiveEvent($this->fullInput($serverId));

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('JBS_ADDON_YOUTUBE_LIVE_OAUTH_REQUIRED', $this->keyOf($result));
    }

    #[Test]
    public function theBaseAddonDeclinesCancellationToo(): void
    {
        $result = $this->minimalAddon()->cancelLiveEvent(new Input(['serverId' => 1, 'broadcastId' => 'x']));

        $this->assertFalse($result['success']);
        $this->assertNotSame('', (string) ($result['error'] ?? ''));
    }

    #[Test]
    public function cancellingWithoutABroadcastIdIsRefused(): void
    {
        $serverId = $this->insertServer(['stream_mode' => 'direct']);

        $result = $this->youtubeAddon()->cancelLiveEvent(new Input([
            'serverId'    => $serverId,
            'broadcastId' => '',
        ]));

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('JBS_ADDON_YOUTUBE_LIVE_MISSING_INPUT', $this->keyOf($result));
    }

    #[Test]
    public function cancellingWithoutOauthIsRefusedBeforeAnyStatusCheck(): void
    {
        // The status check needs the API; without OAuth the refusal must
        // come first — this test cannot reach the network by construction.
        $serverId = $this->insertServer(['stream_mode' => 'direct']);

        $result = $this->youtubeAddon()->cancelLiveEvent(new Input([
            'serverId'    => $serverId,
            'broadcastId' => 'abc123def45',
        ]));

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('JBS_ADDON_YOUTUBE_LIVE_OAUTH_REQUIRED', $this->keyOf($result));
    }

    #[Test]
    public function anAlreadyProvisionedStreamIsReusedWithoutAnyApiCall(): void
    {
        // The "configure the encoder once" contract (#1298 phase 2): a
        // server that already carries its persistent stream must get those
        // exact details back with no network involvement. The YouTube
        // service passed here has no credentials — any API call would
        // throw, so a pass proves none happened.
        $serverId = $this->insertServer([
            'stream_mode'     => 'direct',
            'live_stream_id'  => 'stream-abc',
            'live_stream_key' => 'key-xyz',
            'live_rtmp_url'   => 'rtmp://a.rtmp.youtube.com/live2',
        ]);

        $addon  = $this->youtubeAddon();
        $method = new \ReflectionMethod($addon, 'ensurePersistentStream');

        $details = $method->invoke($addon, $serverId, new \Google\Service\YouTube(new \Google\Client()));

        $this->assertSame('stream-abc', $details['stream_id']);
        $this->assertSame('key-xyz', $details['stream_key']);
        $this->assertSame('rtmp://a.rtmp.youtube.com/live2', $details['rtmp_url']);
    }

    /**
     * Full, valid createLiveEvent input for a server.
     *
     * @param   int  $serverId  The seeded server ID.
     *
     * @return  Input
     */
    private function fullInput(int $serverId): Input
    {
        return new Input([
            'serverId'       => $serverId,
            'title'          => 'Sunday Service',
            'description'    => '',
            'scheduledStart' => '2026-08-02T15:00:00+00:00',
            'privacy'        => 'unlisted',
        ]);
    }

    /**
     * The language key (or translated string) a refusal carried. When the
     * addon language file is not loaded, Text::_() returns the key itself,
     * so asserting on the key matches both environments; when it IS
     * loaded, translate the expectation the same way the addon did.
     *
     * @param   array  $result  A createLiveEvent result.
     *
     * @return  string
     */
    private function keyOf(array $result): string
    {
        $error = (string) ($result['error'] ?? '');

        // Reverse-map a translated string back to its key for the contains
        // assertion. Only the keys this suite asserts on are needed.
        foreach ([
            'JBS_ADDON_YOUTUBE_LIVE_MISSING_INPUT',
            'JBS_ADDON_YOUTUBE_LIVE_NOT_DIRECT',
            'JBS_ADDON_YOUTUBE_LIVE_OAUTH_REQUIRED',
        ] as $key) {
            if ($error === \Joomla\CMS\Language\Text::_($key)) {
                return $key;
            }
        }

        return $error;
    }

    /**
     * A published YouTube server with the given params.
     *
     * @param   array  $params  Server params to store.
     *
     * @return  int  Server ID.
     */
    private function insertServer(array $params): int
    {
        $row = (object) [
            'server_name' => 'Live Test YouTube',
            'published'   => 1,
            'access'      => 1,
            'type'        => 'youtube',
            'params'      => json_encode($params),
            'media'       => '{}',
        ];

        $this->db->insertObject('#__bsms_servers', $row);

        return (int) $this->db->insertid();
    }

    /**
     * The real YouTube addon, constructed without the config-file side
     * effects the production constructor performs.
     *
     * @return  CWMAddonYoutube
     */
    private function youtubeAddon(): CWMAddonYoutube
    {
        $ref   = new \ReflectionClass(CWMAddonYoutube::class);
        $addon = $ref->newInstanceWithoutConstructor();

        return $addon;
    }

    /**
     * A minimal concrete CWMAddon exposing only the base implementations.
     *
     * @return  CWMAddon
     */
    private function minimalAddon(): CWMAddon
    {
        return new class () extends CWMAddon {
            public function __construct()
            {
            }

            public function renderGeneral(object $media_form, bool $new): string
            {
                return '';
            }

            public function render(object $media_form, bool $new): string
            {
                return '';
            }

            protected function upload(?array $data): mixed
            {
                return null;
            }
        };
    }
}
