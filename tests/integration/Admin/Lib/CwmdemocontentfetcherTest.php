<?php

/**
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Lib;

use CWM\Component\Proclaim\Administrator\Lib\Cwmcontentimporter;
use CWM\Component\Proclaim\Administrator\Lib\Cwmdemocontentfetcher;
use CWM\Component\Proclaim\Administrator\Lib\Cwmimportmanifest;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use Joomla\Http\Http;
use Joomla\Http\Response;
use Joomla\Http\TransportInterface;
use Joomla\Uri\UriInterface;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Integration coverage for #2177's fetch/verify/extract/import path.
 *
 * The network leg is stubbed via a fake {@see TransportInterface} passed
 * into a real {@see Http} client — never a real socket — so these tests are
 * a security check on the checksum, extraction and cleanup logic, not a
 * check that the network stack works. The one thing this suite cannot
 * exercise is the pinned release itself (empty until #2178 ships a real
 * archive) or a real GitHub redirect — those are live-verified by hand,
 * per this project's own "live-test before done" rule.
 *
 * Runs inside a savepoint-based transaction for the same reason
 * {@see CwmcontentimporterTest} does — {@see Cwmimportmanifest::exists()}
 * writes a real row for the "already imported" case.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(Cwmdemocontentfetcher::class)]
class CwmdemocontentfetcherTest extends IntegrationTestCase
{
    private ?DatabaseDriver $db = null;

    /** @var string[] */
    private array $zipFilesToClean = [];

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
        foreach ($this->zipFilesToClean as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }

        if ($this->db !== null) {
            try {
                $this->db->transactionRollback(true);
            } catch (\Throwable) {
                // Connection lost; nothing to roll back.
            }
        }

        parent::tearDown();
    }

    private function tag(): string
    {
        return 'cwm2177-test-' . bin2hex(random_bytes(4));
    }

    private function validRelease(): array
    {
        return ['url' => 'https://example.invalid/proclaim-demo-content.zip', 'sha256' => hash('sha256', 'placeholder')];
    }

    /**
     * A real Http client backed by a fake transport, so no socket is ever
     * opened. $response is returned for every request; $spy, if given,
     * counts how many requests were actually made — several tests assert
     * this stays 0, proving a short-circuit really happened before any
     * network access.
     */
    private function fakeHttp(Response|\Throwable $response, ?object $spy = null): Http
    {
        $transport = new class ($response, $spy) implements TransportInterface {
            public function __construct(
                private readonly Response|\Throwable $response,
                private readonly ?object $spy,
            ) {
            }

            public function request($method, UriInterface $uri, $data = null, array $headers = [], $timeout = null, $userAgent = null)
            {
                if ($this->spy !== null) {
                    $this->spy->calls++;
                }

                if ($this->response instanceof \Throwable) {
                    throw $this->response;
                }

                return $this->response;
            }

            public static function isSupported()
            {
                return true;
            }
        };

        return new Http([], $transport);
    }

    private function httpSpy(): object
    {
        return new class () {
            public int $calls = 0;
        };
    }

    private function responseWithBody(string $body, int $status = 200): Response
    {
        $response = new Response('php://temp', $status);
        $response->getBody()->write($body);

        return $response;
    }

    /**
     * @param   array<string, string>  $entries  Zip entry name => raw content.
     * @param   string[]               $symlinkEntries  Entry names, from $entries, to mark as symlinks.
     */
    private function buildZip(array $entries, array $symlinkEntries = []): string
    {
        $path                        = sys_get_temp_dir() . '/cwm2177-fixture-' . bin2hex(random_bytes(6)) . '.zip';
        $this->zipFilesToClean[]     = $path;

        $zip = new \ZipArchive();
        $zip->open($path, \ZipArchive::CREATE);

        foreach ($entries as $name => $content) {
            $zip->addFromString($name, $content);

            if (\in_array($name, $symlinkEntries, true)) {
                // 0120777 << 16: S_IFLNK | 0777, the Unix mode a real
                // symlink zip entry carries in its external attributes.
                $zip->setExternalAttributesName($name, \ZipArchive::OPSYS_UNIX, 0120777 << 16);
            }
        }

        $zip->close();

        return (string) file_get_contents($path);
    }

    public function testIsReleaseAvailableIsFalseWithNoPin(): void
    {
        $this->assertFalse((new Cwmdemocontentfetcher())->isReleaseAvailable());
    }

    public function testIsReleaseAvailableIsTrueWithAValidRelease(): void
    {
        $this->assertTrue((new Cwmdemocontentfetcher($this->validRelease()))->isReleaseAvailable());
    }

    public function testIsReleaseAvailableRejectsAMalformedSha256(): void
    {
        $release = ['url' => 'https://example.invalid/x.zip', 'sha256' => 'not-a-hash'];

        $this->assertFalse((new Cwmdemocontentfetcher($release))->isReleaseAvailable());
    }

    public function testFetchAndImportSkipsWhenNoReleaseIsPinned(): void
    {
        $result = (new Cwmdemocontentfetcher())->fetchAndImport($this->tag());

        $this->assertSame(['status' => 'skipped', 'reason' => 'no_release_pinned'], $result);
    }

    public function testFetchAndImportSkipsWithoutTouchingTheNetworkWhenAlreadyImported(): void
    {
        $tag = $this->tag();
        Cwmimportmanifest::recordRow($tag, '#__bsms_teachers', 1);

        $spy      = $this->httpSpy();
        $fetcher  = new Cwmdemocontentfetcher($this->validRelease(), null, $this->fakeHttp($this->responseWithBody(''), $spy));
        $result   = $fetcher->fetchAndImport($tag);

        $this->assertSame(['status' => 'skipped', 'reason' => 'already_imported'], $result);
        $this->assertSame(0, $spy->calls, 'A re-run must not spend a network round trip finding this out.');
    }

    public function testFetchAndImportRefusesAnInsecureUrlWithoutTouchingTheNetwork(): void
    {
        $release = ['url' => 'http://example.invalid/x.zip', 'sha256' => hash('sha256', 'x')];
        $spy     = $this->httpSpy();
        $fetcher = new Cwmdemocontentfetcher($release, null, $this->fakeHttp($this->responseWithBody(''), $spy));

        $result = $fetcher->fetchAndImport($this->tag());

        $this->assertSame(['status' => 'failed', 'reason' => 'insecure_url'], $result);
        $this->assertSame(0, $spy->calls);
    }

    public function testFetchAndImportFailsOnChecksumMismatch(): void
    {
        $release = ['url' => 'https://example.invalid/x.zip', 'sha256' => hash('sha256', 'the-real-thing')];
        $http    = $this->fakeHttp($this->responseWithBody('something-else'));
        $fetcher = new Cwmdemocontentfetcher($release, null, $http);

        $result = $fetcher->fetchAndImport($this->tag());

        $this->assertSame(['status' => 'failed', 'reason' => 'checksum_mismatch'], $result);
    }

    public function testFetchAndImportReportsADownloadFailureWithoutThrowing(): void
    {
        $http    = $this->fakeHttp(new \RuntimeException('simulated connection failure'));
        $fetcher = new Cwmdemocontentfetcher($this->validRelease(), null, $http);

        $result = $fetcher->fetchAndImport($this->tag());

        $this->assertSame('failed', $result['status']);
        $this->assertSame('download_failed', $result['reason']);
    }

    public function testFetchAndImportFailsWhenTheZipHasNoManifestJson(): void
    {
        $zipBytes = $this->buildZip(['../../escape.txt' => 'pwned', 'images/logo.png' => 'not-really-a-png']);
        $release  = ['url' => 'https://example.invalid/x.zip', 'sha256' => hash('sha256', $zipBytes)];
        $fetcher  = new Cwmdemocontentfetcher($release, null, $this->fakeHttp($this->responseWithBody($zipBytes)));

        $before = glob(sys_get_temp_dir() . '/proclaim-demo-*');

        $result = $fetcher->fetchAndImport($this->tag());

        $this->assertSame(['status' => 'failed', 'reason' => 'extraction_failed'], $result);

        $after = glob(sys_get_temp_dir() . '/proclaim-demo-*');
        $this->assertSame($before, $after, 'Nothing should be left behind under tmp after a failed extraction.');
    }

    public function testFetchAndImportFailsWhenManifestJsonIsInvalidJson(): void
    {
        $zipBytes = $this->buildZip(['manifest.json' => '{not valid json']);
        $release  = ['url' => 'https://example.invalid/x.zip', 'sha256' => hash('sha256', $zipBytes)];
        $fetcher  = new Cwmdemocontentfetcher($release, null, $this->fakeHttp($this->responseWithBody($zipBytes)));

        $result = $fetcher->fetchAndImport($this->tag());

        $this->assertSame(['status' => 'failed', 'reason' => 'invalid_manifest'], $result);
    }

    public function testFetchAndImportFailsWhenManifestJsonIsNotAnObject(): void
    {
        $zipBytes = $this->buildZip(['manifest.json' => '"just a string"']);
        $release  = ['url' => 'https://example.invalid/x.zip', 'sha256' => hash('sha256', $zipBytes)];
        $fetcher  = new Cwmdemocontentfetcher($release, null, $this->fakeHttp($this->responseWithBody($zipBytes)));

        $result = $fetcher->fetchAndImport($this->tag());

        $this->assertSame(['status' => 'failed', 'reason' => 'invalid_manifest'], $result);
    }

    /**
     * The security-critical case: a zip carrying a path-traversal entry and
     * a symlink entry alongside a legitimate manifest and image. Both
     * hostile entries must be silently dropped, the legitimate content must
     * still import, and nothing must land outside the extraction directory.
     */
    public function testFetchAndImportDropsTraversalAndSymlinkEntriesButImportsTheRest(): void
    {
        $escapeTarget = sys_get_temp_dir() . '/cwm2177-should-never-exist.txt';
        @unlink($escapeTarget);

        $entries = [
            'manifest.json'                     => '{}',
            'images/logo.png'                   => 'fake-image-bytes',
            '../cwm2177-should-never-exist.txt' => 'pwned',
            'images/link.png'                   => '/etc/passwd',
        ];
        $zipBytes = $this->buildZip($entries, symlinkEntries: ['images/link.png']);
        $release  = ['url' => 'https://example.invalid/x.zip', 'sha256' => hash('sha256', $zipBytes)];

        $importer = new class () extends Cwmcontentimporter {
            public array $seenEntries = [];

            #[\Override]
            public function import(string $tag, array $payload, string $sourceDir): array
            {
                $this->seenEntries = scandir($sourceDir) ?: [];

                return ['teachers' => 0, 'series' => 0, 'locations' => 0, 'messages' => 0, 'mediafiles' => 0, 'files' => 0];
            }
        };

        $fetcher = new Cwmdemocontentfetcher($release, $importer, $this->fakeHttp($this->responseWithBody($zipBytes)));

        $result = $fetcher->fetchAndImport($this->tag());

        $this->assertSame('imported', $result['status']);
        $this->assertContains('manifest.json', $importer->seenEntries);
        $this->assertContains('images', $importer->seenEntries);
        $this->assertNotContains('cwm2177-should-never-exist.txt', $importer->seenEntries);
        $this->assertFileDoesNotExist($escapeTarget, 'A traversal entry must never escape the extraction directory.');
    }

    public function testFetchAndImportPassesThroughImporterCountsOnSuccess(): void
    {
        $zipBytes = $this->buildZip(['manifest.json' => '{}']);
        $release  = ['url' => 'https://example.invalid/x.zip', 'sha256' => hash('sha256', $zipBytes)];

        $importer = new class () extends Cwmcontentimporter {
            #[\Override]
            public function import(string $tag, array $payload, string $sourceDir): array
            {
                return ['teachers' => 2, 'series' => 1, 'locations' => 0, 'messages' => 3, 'mediafiles' => 0, 'files' => 1];
            }
        };

        $fetcher = new Cwmdemocontentfetcher($release, $importer, $this->fakeHttp($this->responseWithBody($zipBytes)));

        $result = $fetcher->fetchAndImport($this->tag());

        $this->assertSame('imported', $result['status']);
        $this->assertSame(
            ['teachers' => 2, 'series' => 1, 'locations' => 0, 'messages' => 3, 'mediafiles' => 0, 'files' => 1],
            $result['counts']
        );
    }

    public function testFetchAndImportCleansUpTempFilesEvenWhenTheImporterThrows(): void
    {
        $zipBytes = $this->buildZip(['manifest.json' => '{}']);
        $release  = ['url' => 'https://example.invalid/x.zip', 'sha256' => hash('sha256', $zipBytes)];

        $importer = new class () extends Cwmcontentimporter {
            #[\Override]
            public function import(string $tag, array $payload, string $sourceDir): array
            {
                throw new \RuntimeException('simulated importer failure');
            }
        };

        $before = glob(sys_get_temp_dir() . '/proclaim-demo-*');

        $fetcher = new Cwmdemocontentfetcher($release, $importer, $this->fakeHttp($this->responseWithBody($zipBytes)));
        $result  = $fetcher->fetchAndImport($this->tag());

        $this->assertSame('failed', $result['status']);
        $this->assertSame('unexpected_error', $result['reason']);

        $after = glob(sys_get_temp_dir() . '/proclaim-demo-*');
        $this->assertSame($before, $after, 'The finally block must clean up even when the importer itself throws.');
    }
}
