<?php

/**
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Console;

use CWM\Component\Proclaim\Administrator\Console\ImportCommand;
use CWM\Component\Proclaim\Administrator\Lib\Cwmcontentimporter;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Integration coverage for `proclaim:import` (#2188).
 *
 * The importer dependency is always a mock here — exercising the real
 * Cwmcontentimporter is CwmcontentimporterTest's job, and doing it again
 * from this class would need the same bootComponent()/content-plugin
 * environment that class already documents this harness cannot provide.
 * What this class actually needs to prove is the command's own logic:
 * argument/JSON handling, --user resolution against a real #__users table,
 * and turning an importer result (or exception) into the right exit code
 * and message — none of which needs a real Cwmcontentimporter.
 *
 * doExecute()'s identity/language loading is guarded on
 * `$app instanceof ConsoleApplication`, which this harness's bare
 * Joomla\Console\Application never satisfies (see CwmcontentimporterTest's
 * own docblock for why) — so that branch is simply never exercised here,
 * by design, rather than skipped.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(ImportCommand::class)]
class ImportCommandTest extends IntegrationTestCase
{
    private ?DatabaseDriver $db = null;

    /** @var int[] */
    private array $testUserIds = [];

    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);
        $this->db->transactionStart(true);
    }

    protected function tearDown(): void
    {
        if ($this->db !== null) {
            try {
                $this->db->transactionRollback(true);
            } catch (\Throwable) {
                // Connection lost; nothing to roll back.
            }
        }

        parent::tearDown();
    }

    /**
     * @param   bool  $blocked
     *
     * @return  int  The new user's id.
     */
    private function createUser(string $username, bool $blocked = false): int
    {
        $row = (object) [
            'name'         => $username,
            'username'     => $username,
            'email'        => $username . '@example.invalid',
            'password'     => '',
            'block'        => $blocked ? 1 : 0,
            'registerDate' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            'params'       => '{}',
        ];
        $this->db->insertObject('#__users', $row, 'id');

        return (int) $row->id;
    }

    private function fixturePayloadPath(): string
    {
        return \dirname(__DIR__, 3) . '/fixtures/demo-import-sample/payload.json';
    }

    /**
     * AbstractCommand::getApplication() throws if none was ever set — the
     * console-commands reference's own example command never calls it, but
     * this one does (to guard the CMS-only identity/language loading), so
     * the harness's own bare console application has to be wired in by hand.
     */
    private function newCommand(Cwmcontentimporter $importer): ImportCommand
    {
        $command = new ImportCommand($importer, $this->db);
        $command->setApplication(Factory::getApplication());

        return $command;
    }

    public function testFailsCleanlyWhenPayloadPathIsUnreadable(): void
    {
        $command = $this->newCommand($this->createStub(Cwmcontentimporter::class));
        $output  = new BufferedOutput();

        $exit = $command->execute(
            new ArrayInput(['payload' => '/does/not/exist.json', 'tag' => 'irrelevant']),
            $output
        );

        $this->assertSame(Command::INVALID, $exit);
        $this->assertStringContainsString('Cannot read payload', $output->fetch());
    }

    public function testFailsCleanlyOnInvalidJson(): void
    {
        $badFile = tempnam(sys_get_temp_dir(), 'proclaim-import-command-test-');
        file_put_contents($badFile, '{not valid json');

        try {
            $command = $this->newCommand($this->createStub(Cwmcontentimporter::class));
            $output  = new BufferedOutput();

            $exit = $command->execute(new ArrayInput(['payload' => $badFile, 'tag' => 't']), $output);

            $this->assertSame(Command::INVALID, $exit);
            $this->assertStringContainsString('not valid JSON', $output->fetch());
        } finally {
            @unlink($badFile);
        }
    }

    public function testFailsCleanlyWhenNamedUserDoesNotExist(): void
    {
        $command = $this->newCommand($this->createStub(Cwmcontentimporter::class));
        $output  = new BufferedOutput();

        $exit = $command->execute(
            new ArrayInput(['payload' => $this->fixturePayloadPath(), 'tag' => 't', '--user' => 'no-such-user-3948']),
            $output
        );

        $this->assertSame(Command::INVALID, $exit);
        $this->assertStringContainsString('No active user matches', $output->fetch());
    }

    public function testFailsCleanlyWhenNamedUserIsBlocked(): void
    {
        $username            = 'cwm2188blocked' . bin2hex(random_bytes(3));
        $this->testUserIds[] = $this->createUser($username, blocked: true);

        $command = $this->newCommand($this->createStub(Cwmcontentimporter::class));
        $output  = new BufferedOutput();

        $exit = $command->execute(
            new ArrayInput(['payload' => $this->fixturePayloadPath(), 'tag' => 't', '--user' => $username]),
            $output
        );

        $this->assertSame(Command::INVALID, $exit);
    }

    public function testResolvesAnExplicitUserByUsernameAndCallsTheImporter(): void
    {
        $username = 'cwm2188user' . bin2hex(random_bytes(3));
        $userId   = $this->createUser($username);

        $importer = $this->createMock(Cwmcontentimporter::class);
        $importer->expects($this->once())
            ->method('import')
            ->with('my-tag', $this->isArray(), \dirname($this->fixturePayloadPath()))
            ->willReturn(['teachers' => 1, 'series' => 0, 'messages' => 0, 'files' => 0]);

        $command = $this->newCommand($importer);
        $output  = new BufferedOutput();

        $exit = $command->execute(
            new ArrayInput(['payload' => $this->fixturePayloadPath(), 'tag' => 'my-tag', '--user' => $username]),
            $output
        );

        $this->assertSame(Command::SUCCESS, $exit);
        $this->assertStringContainsString('user #' . $userId, $output->fetch());
    }

    public function testResolvesAnExplicitUserById(): void
    {
        $userId = $this->createUser('cwm2188userid' . bin2hex(random_bytes(3)));

        $importer = $this->createStub(Cwmcontentimporter::class);
        $importer->method('import')->willReturn(['teachers' => 0, 'series' => 0, 'messages' => 0, 'files' => 0]);

        $command = $this->newCommand($importer);
        $output  = new BufferedOutput();

        $exit = $command->execute(
            new ArrayInput(['payload' => $this->fixturePayloadPath(), 'tag' => 't', '--user' => (string) $userId]),
            $output
        );

        $this->assertSame(Command::SUCCESS, $exit);
        $this->assertStringContainsString('user #' . $userId, $output->fetch());
    }

    public function testPrintsTheErrorAndReturnsFailureWhenTheImporterThrows(): void
    {
        $userId = $this->createUser('cwm2188fail' . bin2hex(random_bytes(3)));

        $importer = $this->createStub(Cwmcontentimporter::class);
        $importer->method('import')->willThrowException(new \RuntimeException('boom'));

        $command = $this->newCommand($importer);
        $output  = new BufferedOutput();

        $exit = $command->execute(
            new ArrayInput(['payload' => $this->fixturePayloadPath(), 'tag' => 't', '--user' => (string) $userId]),
            $output
        );

        $this->assertSame(Command::FAILURE, $exit);
        $this->assertStringContainsString('boom', $output->fetch());
    }

    public function testPrintsTheSummaryOnSuccess(): void
    {
        $userId = $this->createUser('cwm2188summary' . bin2hex(random_bytes(3)));

        $importer = $this->createStub(Cwmcontentimporter::class);
        $importer->method('import')->willReturn(['teachers' => 2, 'series' => 1, 'messages' => 3, 'files' => 4]);

        $command = $this->newCommand($importer);
        $output  = new BufferedOutput();

        $exit = $command->execute(
            new ArrayInput(['payload' => $this->fixturePayloadPath(), 'tag' => 't', '--user' => (string) $userId]),
            $output
        );

        $this->assertSame(Command::SUCCESS, $exit);
        $text = $output->fetch();
        $this->assertStringContainsString('2 teacher(s)', $text);
        $this->assertStringContainsString('1 serie(s)', $text);
        $this->assertStringContainsString('3 message(s)', $text);
        $this->assertStringContainsString('4 file(s)', $text);
    }

    /**
     * With no --user, resolution must find *some* active user carrying
     * core.admin — not asserted by id, since a shared dev database may
     * already have real Super Users with lower ids than any created here,
     * and resolveDefaultSuperUser() deliberately picks the lowest id. The
     * point of this test is that the query runs and finds a candidate at
     * all, not which one.
     */
    public function testResolvesADefaultUserWhenNoneIsGiven(): void
    {
        $importer = $this->createStub(Cwmcontentimporter::class);
        $importer->method('import')->willReturn(['teachers' => 0, 'series' => 0, 'messages' => 0, 'files' => 0]);

        $command = $this->newCommand($importer);
        $output  = new BufferedOutput();

        $exit = $command->execute(
            new ArrayInput(['payload' => $this->fixturePayloadPath(), 'tag' => 't']),
            $output
        );

        $this->assertSame(Command::SUCCESS, $exit);
        $this->assertMatchesRegularExpression('/user #\d+/', $output->fetch());
    }
}
