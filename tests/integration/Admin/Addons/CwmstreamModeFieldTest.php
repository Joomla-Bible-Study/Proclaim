<?php

/**
 * Integration tests for the OAuth-gated stream-mode switcher.
 *
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

declare(strict_types=1);

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Addons;

use CWM\Component\Proclaim\Administrator\Addons\Servers\Youtube\Field\YoutubeStreamModeField;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

/**
 * Direct stream mode is useless without a connected YouTube account, so
 * the switcher must refuse to offer it: disabled, pinned to External,
 * with the OAuth requirement stated. These tests pin the gate the same
 * way the live-event suite pins the create refusals — no network is
 * reachable, only the stored server row decides.
 *
 * @since __DEPLOY_VERSION__
 */
#[CoversClass(YoutubeStreamModeField::class)]
class CwmstreamModeFieldTest extends IntegrationTestCase
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
     * Roll the seeded rows back and clear the request id.
     *
     * @return  void
     */
    protected function tearDown(): void
    {
        $this->db->transactionRollback();
        Factory::getApplication()->getInput()->set('id', null);

        parent::tearDown();
    }

    #[Test]
    public function aServerWithoutOauthIsNotConnected(): void
    {
        $id = $this->insertServer(['client_id' => 'abc', 'client_secret' => 'def']);
        Factory::getApplication()->getInput()->set('id', $id);

        $this->assertFalse($this->field()->isServerConnected(), 'Credentials alone are not a connection');
    }

    #[Test]
    public function aServerWithAStoredTokenIsConnected(): void
    {
        $id = $this->insertServer(['oauth_token' => ['access_token' => 'tok', 'refresh_token' => 'ref']]);
        Factory::getApplication()->getInput()->set('id', $id);

        $this->assertTrue($this->field()->isServerConnected());
    }

    #[Test]
    public function aNewUnsavedServerIsNotConnected(): void
    {
        Factory::getApplication()->getInput()->set('id', 0);

        $this->assertFalse($this->field()->isServerConnected(), 'OAuth cannot exist before the first save');
    }

    #[Test]
    public function anUnconnectedServerGetsTheRequirementNoticeInsteadOfTheSwitch(): void
    {
        $id = $this->insertServer([]);
        Factory::getApplication()->getInput()->set('id', $id);

        $html = $this->renderInput();

        $this->assertStringContainsString('alert-warning', $html, 'The OAuth requirement must be stated');
        $this->assertMatchesRegularExpression(
            '/<input type="hidden" name="[^"]*stream_mode[^"]*" id="[^"]*stream_mode[^"]*" value="external">/',
            $html,
            'The stored value must stay an explicit external, and the id must be present — '
            . "core showon.js reads the watched field's value by element id, so without it "
            . 'the showon-gated fields below stay visible'
        );
        $this->assertStringNotContainsString('value="direct"', $html, 'Direct must not be offered at all');
    }

    #[Test]
    public function aConnectedServerGetsTheNormalSwitch(): void
    {
        $id = $this->insertServer(['oauth_token' => ['access_token' => 'tok']]);
        Factory::getApplication()->getInput()->set('id', $id);

        $html = $this->renderInput();

        $this->assertStringNotContainsString('alert-warning', $html);
        $this->assertStringNotContainsString('type="hidden"', $html);
        $this->assertStringNotContainsString('disabled', $html);
    }

    /**
     * Render the field's input markup for a plain radio layout.
     *
     * The production XML uses the switcher layout; the plain radio layout
     * exercises the same gate without the switcher's web-asset needs.
     *
     * @return  string
     */
    private function renderInput(): string
    {
        $field = $this->field();

        $xml = new \SimpleXMLElement(
            '<field name="stream_mode" type="YoutubeStreamMode" default="external">'
            . '<option value="external">External</option>'
            . '<option value="direct">Direct</option>'
            . '</field>'
        );

        $this->assertTrue($field->setup($xml, 'external', 'params'));

        $method = new \ReflectionMethod($field, 'getInput');

        return (string) $method->invoke($field);
    }

    /**
     * The field under test.
     *
     * @return  YoutubeStreamModeField
     */
    private function field(): YoutubeStreamModeField
    {
        return new YoutubeStreamModeField();
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
            'server_name' => 'Stream Mode Test YouTube',
            'published'   => 1,
            'access'      => 1,
            'type'        => 'youtube',
            'params'      => json_encode($params),
            'media'       => '{}',
        ];

        $this->db->insertObject('#__bsms_servers', $row);

        return (int) $this->db->insertid();
    }
}
