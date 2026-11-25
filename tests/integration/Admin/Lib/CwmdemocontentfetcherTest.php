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
    /**
     * The database, inside a transaction each test rolls back.
     *
     * @var    DatabaseDriver|null
     *
     * @since  __DEPLOY_VERSION__
     */
    private ?DatabaseDriver $db = null;

    /**
     * Paths of the zips buildZip() wrote, for tearDown() to delete.
     *
     * @var    string[]
     *
     * @since  __DEPLOY_VERSION__
     */
    private array $zipFilesToClean = [];

    /**
     * Skip without a test database. Otherwise open a transaction.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);
        $this->db->transactionStart(true);
    }

    /**
     * Delete the zips buildZip() wrote, then roll back the transaction setUp()
     * opened.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
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

    /**
     * A random import tag, so a test starts with nothing recorded under it.
     *
     * @return  string  The tag.
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Random\RandomException
     */
    private function tag(): string
    {
        return 'cwm2177-test-' . bin2hex(random_bytes(4));
    }

    /**
     * A release the fetcher accepts: an https URL and a well-formed sha256. The
     * digest is of a placeholder string, not of any archive.
     *
     * @return  array{url: string, sha256: string}  The release.
     *
     * @since   __DEPLOY_VERSION__
     */
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
     *
     * @param   Response|\Throwable  $response  Returned, or thrown, for every request.
     * @param   ?object              $spy       When given, its calls property counts the requests made.
     *
     * @return  Http  A client that never opens a socket.
     *
     * @since   __DEPLOY_VERSION__
     */
    private function fakeHttp(Response|\Throwable $response, ?object $spy = null): Http
    {
        $transport = new class ($response, $spy) implements TransportInterface {
            /**
             * Keep the response every request returns, or the throwable it throws,
             * and the optional spy that counts requests.
             *
             * @param   Response|\Throwable  $response  Returned, or thrown, for every request.
             * @param   ?object              $spy       When given, its calls property counts the requests made.
             *
             * @since   __DEPLOY_VERSION__
             */
            public function __construct(
                private readonly Response|\Throwable $response,
                private readonly ?object $spy,
            ) {
            }

            /**
             * Count the request on the spy, if there is one, then return the
             * response, or throw the throwable, the transport was made with. The
             * arguments are ignored.
             *
             * @param   string        $method     The HTTP method.
             * @param   UriInterface  $uri        The URI to request.
             * @param   mixed         $data       The request body.
             * @param   array         $headers    The request headers.
             * @param   ?int          $timeout    The read timeout in seconds.
             * @param   ?string       $userAgent  The user agent string.
             *
             * @return  Response  The response the transport was made with.
             *
             * @since   __DEPLOY_VERSION__
             * @throws  \Throwable
             */
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

            /**
             * Report the transport as available.
             *
             * @return  bool  Always true.
             *
             * @since   __DEPLOY_VERSION__
             */
            public static function isSupported()
            {
                return true;
            }
        };

        return new Http([], $transport);
    }

    /**
     * A spy that the fake transport counts its requests on.
     *
     * @return  object  An object whose calls property starts at 0.
     *
     * @since   __DEPLOY_VERSION__
     */
    private function httpSpy(): object
    {
        return new class () {
            /**
             * How many requests the fake transport has received.
             *
             * @var    int
             *
             * @since  __DEPLOY_VERSION__
             */
            public int $calls = 0;
        };
    }

    /**
     * A response with the given body and status code.
     *
     * @param   string  $body    The response body.
     * @param   int     $status  The HTTP status code.
     *
     * @return  Response  The response.
     *
     * @since   __DEPLOY_VERSION__
     */
    private function responseWithBody(string $body, int $status = 200): Response
    {
        $response = new Response('php://temp', $status);
        $response->getBody()->write($body);

        return $response;
    }

    /**
     * Write a zip to the system temp directory and return its bytes, marking
     * the named entries as Unix symlinks. tearDown() deletes the file.
     *
     * @param   array<string, string>  $entries         Zip entry name => raw content.
     * @param   string[]               $symlinkEntries  Entry names, from $entries, to mark as symlinks.
     *
     * @return  string  The zip's bytes.
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Random\RandomException
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

    /**
     * With no release passed in and none pinned in the package, no release is
     * available.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testIsReleaseAvailableIsFalseWithNoPin(): void
    {
        $this->assertFalse((new Cwmdemocontentfetcher())->isReleaseAvailable());
    }

    /**
     * A release with a URL and a well-formed sha256 is available.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testIsReleaseAvailableIsTrueWithAValidRelease(): void
    {
        $this->assertTrue((new Cwmdemocontentfetcher($this->validRelease()))->isReleaseAvailable());
    }

    /**
     * A release whose sha256 is not a hex digest is not available.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testIsReleaseAvailableRejectsAMalformedSha256(): void
    {
        $release = ['url' => 'https://example.invalid/x.zip', 'sha256' => 'not-a-hash'];

        $this->assertFalse((new Cwmdemocontentfetcher($release))->isReleaseAvailable());
    }

    /**
     * With no release passed in and none pinned in the package, the import is
     * skipped as no_release_pinned.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Random\RandomException
     */
    public function testFetchAndImportSkipsWhenNoReleaseIsPinned(): void
    {
        $result = (new Cwmdemocontentfetcher())->fetchAndImport($this->tag());

        $this->assertSame(['status' => 'skipped', 'reason' => 'no_release_pinned'], $result);
    }

    /**
     * A tag that already has a manifest row is skipped as already_imported,
     * without a single request.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Random\RandomException
     */
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

    /**
     * An http URL fails as insecure_url, without a single request.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Random\RandomException
     */
    public function testFetchAndImportRefusesAnInsecureUrlWithoutTouchingTheNetwork(): void
    {
        $release = ['url' => 'http://example.invalid/x.zip', 'sha256' => hash('sha256', 'x')];
        $spy     = $this->httpSpy();
        $fetcher = new Cwmdemocontentfetcher($release, null, $this->fakeHttp($this->responseWithBody(''), $spy));

        $result = $fetcher->fetchAndImport($this->tag());

        $this->assertSame(['status' => 'failed', 'reason' => 'insecure_url'], $result);
        $this->assertSame(0, $spy->calls);
    }

    /**
     * A download whose sha256 doesn't match the release's fails as
     * checksum_mismatch.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Random\RandomException
     */
    public function testFetchAndImportFailsOnChecksumMismatch(): void
    {
        $release = ['url' => 'https://example.invalid/x.zip', 'sha256' => hash('sha256', 'the-real-thing')];
        $http    = $this->fakeHttp($this->responseWithBody('something-else'));
        $fetcher = new Cwmdemocontentfetcher($release, null, $http);

        $result = $fetcher->fetchAndImport($this->tag());

        $this->assertSame(['status' => 'failed', 'reason' => 'checksum_mismatch'], $result);
    }

    /**
     * A request that throws comes back as a failed result with the reason
     * download_failed, not as an exception.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Random\RandomException
     */
    public function testFetchAndImportReportsADownloadFailureWithoutThrowing(): void
    {
        $http    = $this->fakeHttp(new \RuntimeException('simulated connection failure'));
        $fetcher = new Cwmdemocontentfetcher($this->validRelease(), null, $http);

        $result = $fetcher->fetchAndImport($this->tag());

        $this->assertSame('failed', $result['status']);
        $this->assertSame('download_failed', $result['reason']);
    }

    /**
     * A zip without a manifest.json fails as extraction_failed, and the system
     * temp directory holds the same proclaim-demo-* entries afterward as before.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Random\RandomException
     */
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

    /**
     * A manifest.json that isn't valid JSON fails as invalid_manifest.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Random\RandomException
     */
    public function testFetchAndImportFailsWhenManifestJsonIsInvalidJson(): void
    {
        $zipBytes = $this->buildZip(['manifest.json' => '{not valid json']);
        $release  = ['url' => 'https://example.invalid/x.zip', 'sha256' => hash('sha256', $zipBytes)];
        $fetcher  = new Cwmdemocontentfetcher($release, null, $this->fakeHttp($this->responseWithBody($zipBytes)));

        $result = $fetcher->fetchAndImport($this->tag());

        $this->assertSame(['status' => 'failed', 'reason' => 'invalid_manifest'], $result);
    }

    /**
     * A manifest.json holding a bare JSON string fails as invalid_manifest.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Random\RandomException
     */
    public function testFetchAndImportFailsWhenManifestJsonIsNotAnObject(): void
    {
        $zipBytes = $this->buildZip(['manifest.json' => '"just a string"']);
        $release  = ['url' => 'https://example.invalid/x.zip', 'sha256' => hash('sha256', $zipBytes)];
        $fetcher  = new Cwmdemocontentfetcher($release, null, $this->fakeHttp($this->responseWithBody($zipBytes)));

        $result = $fetcher->fetchAndImport($this->tag());

        $this->assertSame(['status' => 'failed', 'reason' => 'invalid_manifest'], $result);
    }

    /**
     * A zip with a path-traversal entry and a symlink entry beside a manifest
     * and an image still imports. The manifest and the images folder reach the
     * importer, and the traversal entry's file is neither at the top of the
     * extraction directory nor in the system temp directory. The symlink sits
     * inside images/, below the level the importer double lists, so nothing
     * here checks it.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Random\RandomException
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
            /**
             * The names scandir() listed at the top of the directory import() was
             * handed.
             *
             * @var    string[]
             *
             * @since  __DEPLOY_VERSION__
             */
            public array $seenEntries = [];

            /**
             * Record the top level of the directory it is handed, and import
             * nothing.
             *
             * @param   string  $tag        The import tag.
             * @param   array   $payload    The decoded manifest.json.
             * @param   string  $sourceDir  The directory the archive was extracted to.
             *
             * @return  array<string, int>  A count of zero for each section.
             *
             * @since   __DEPLOY_VERSION__
             */
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

    /**
     * A successful import returns the status imported with the importer's
     * counts unchanged.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Random\RandomException
     */
    public function testFetchAndImportPassesThroughImporterCountsOnSuccess(): void
    {
        $zipBytes = $this->buildZip(['manifest.json' => '{}']);
        $release  = ['url' => 'https://example.invalid/x.zip', 'sha256' => hash('sha256', $zipBytes)];

        $importer = new class () extends Cwmcontentimporter {
            /**
             * Import nothing, and report fixed counts for the test to find in the
             * fetcher's result.
             *
             * @param   string  $tag        The import tag.
             * @param   array   $payload    The decoded manifest.json.
             * @param   string  $sourceDir  The directory the archive was extracted to.
             *
             * @return  array<string, int>  Two teachers, one series, three messages and one file.
             *
             * @since   __DEPLOY_VERSION__
             */
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

    /**
     * An importer that throws fails the import as unexpected_error, and the
     * system temp directory holds the same proclaim-demo-* entries afterward as
     * before.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Random\RandomException
     */
    public function testFetchAndImportCleansUpTempFilesEvenWhenTheImporterThrows(): void
    {
        $zipBytes = $this->buildZip(['manifest.json' => '{}']);
        $release  = ['url' => 'https://example.invalid/x.zip', 'sha256' => hash('sha256', $zipBytes)];

        $importer = new class () extends Cwmcontentimporter {
            /**
             * Fail the import by throwing.
             *
             * @param   string  $tag        The import tag.
             * @param   array   $payload    The decoded manifest.json.
             * @param   string  $sourceDir  The directory the archive was extracted to.
             *
             * @return  array<string, int>  Never returned; the method always throws.
             *
             * @since   __DEPLOY_VERSION__
             * @throws  \RuntimeException
             */
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
