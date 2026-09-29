<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Lib;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CwmDebug;
use Joomla\CMS\Factory;
use Joomla\Filesystem\Folder;
use Joomla\Http\Http;
use Joomla\Http\HttpFactory;

/**
 * Fetches the hosted `proclaim-demo-content` archive, verifies it, and hands
 * it to {@see Cwmcontentimporter}.
 *
 * A site fetching this over the network is untrusted input the moment a byte
 * of it comes from a URL, so every step here follows a strict order —
 *
 * 1. the release (URL + sha256) is {@see PINNED_RELEASE}, a constant shipped
 *    inside this package, never fetched from the same host serving the
 *    archive at request time — a hash fetched next to the file only catches
 *    transit corruption, not a compromised or substituted host;
 * 2. the fetch is TLS-verified (enforced by refusing anything but an
 *    `https://` URL, and by {@see \CWM\Component\Proclaim\Tests\Repo\TlsVerificationContractTest}
 *    banning `verify => false` anywhere in this codebase);
 * 3. the sha256 is checked against the pin, in memory, before a single byte
 *    reaches disk;
 * 4. the archive is opened as a zip and only two entry shapes are ever
 *    extracted — `manifest.json` and `images/<safe-name>.<image-ext>` — with
 *    every other entry (traversal, absolute path, symlink) silently
 *    dropped rather than trusted to {@see \Joomla\CMS\Installer\InstallerHelper::unpack()},
 *    which is built for a Joomla extension package, not an arbitrary zip,
 *    and would otherwise queue a spurious "no XML found" installer warning;
 * 5. the verified, extracted payload is handed to {@see Cwmcontentimporter},
 *    which does its own independent validation pass regardless.
 *
 * {@see fetchAndImport()} never throws — every failure, expected or not, is
 * caught and returned as a status the caller can act on, because a site
 * that cannot reach the internet, or whose pinned release has gone stale,
 * must still be able to finish setup.
 *
 * @package  Proclaim.Admin
 * @since    __DEPLOY_VERSION__
 */
final class Cwmdemocontentfetcher
{
    /**
     * The one known-good `proclaim-demo-content` release this package
     * understands: `{url, sha256}`, or `null` when none has been cut yet.
     *
     * Deliberately `null` on this release — `proclaim-demo-content`
     * (https://github.com/Joomla-Bible-Study/proclaim-demo-content) has no
     * published archive yet. A future release process updates this constant
     * exactly the way a submodule pin is bumped: to
     *
     *     private const ?array PINNED_RELEASE = [
     *         'url'    => 'https://github.com/Joomla-Bible-Study/proclaim-demo-content/releases/download/vX.Y.Z/proclaim-demo-content-X.Y.Z.zip',
     *         'sha256' => '<64 lowercase hex characters, published alongside the release>',
     *     ];
     *
     * No version or schema key belongs here or in the archive's own
     * `manifest.json` — a pin shipped inside this package already matches
     * the Proclaim release running it, and `Cwmcontentimporter::validate()`
     * refuses any top-level `manifest.json` key outside its own
     * `ALLOWED_SECTIONS` in any case.
     *
     * @var  array{url: string, sha256: string}|null
     * @since  __DEPLOY_VERSION__
     */
    private const ?array PINNED_RELEASE = null;

    /**
     * @since  __DEPLOY_VERSION__
     */
    private const int HTTP_TIMEOUT = 30;

    /**
     * The only file this archive's zip is ever expected to extract at its
     * root.
     *
     * @since  __DEPLOY_VERSION__
     */
    private const string MANIFEST_FILENAME = 'manifest.json';

    /**
     * Every other extractable entry must match this — `images/`, a safe
     * filename (no path separators, no leading dot), one of the extensions
     * {@see Cwmcontentimporter::IMAGE_EXTENSIONS} already allows for an
     * imported `files[]` destination.
     *
     * @since  __DEPLOY_VERSION__
     */
    private const string IMAGE_ENTRY_PATTERN = '#^images/[A-Za-z0-9._-]+\.(?:jpe?g|png|webp|gif)$#i';

    /**
     * @param   ?array               $release   Overrides {@see PINNED_RELEASE}. Null uses the pin.
     * @param   ?Cwmcontentimporter  $importer  Overrides the real importer. Exists for tests.
     * @param   ?Http                $http      Overrides the real HTTP client. Exists for tests —
     *                                           construct with a fake `TransportInterface` to avoid
     *                                           any real network access.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function __construct(
        private readonly ?array $release = null,
        private readonly ?Cwmcontentimporter $importer = null,
        private readonly ?Http $http = null,
    ) {
    }

    /**
     * Whether a release is pinned at all — used by the setup wizard to
     * decide whether to offer the option, rather than show a checkbox that
     * can only ever no-op.
     *
     * @return  bool
     *
     * @since  __DEPLOY_VERSION__
     */
    public function isReleaseAvailable(): bool
    {
        return $this->resolveRelease() !== null;
    }

    /**
     * Fetch, verify and import the pinned demo content release.
     *
     * Never throws. Every failure mode — nothing pinned, already imported,
     * unreachable host, checksum mismatch, a malformed or hostile archive,
     * an importer refusal — comes back as a status string instead, so a
     * caller (the setup wizard) can let the rest of setup finish regardless.
     *
     * @param   string  $tag  The import tag to record rows/files under.
     *
     * @return  array{status: string, reason?: string, counts?: array}
     *
     * @since  __DEPLOY_VERSION__
     */
    public function fetchAndImport(string $tag): array
    {
        try {
            return $this->attempt($tag);
        } catch (\Throwable $e) {
            CwmDebug::error('Demo content fetch/import failed', $e, 'democontent');

            return ['status' => 'failed', 'reason' => 'unexpected_error'];
        }
    }

    /**
     * @param   string  $tag  See {@see fetchAndImport()}.
     *
     * @return  array{status: string, reason?: string, counts?: array}
     *
     * @since  __DEPLOY_VERSION__
     */
    private function attempt(string $tag): array
    {
        $release = $this->resolveRelease();

        if ($release === null) {
            return ['status' => 'skipped', 'reason' => 'no_release_pinned'];
        }

        // Checked before any network access — a re-run must say "already
        // imported", not "download failed", and must not spend a round trip
        // finding that out. Cwmcontentimporter::import() checks this again
        // itself; that is its own contract to keep, not redundant with this.
        if (Cwmimportmanifest::exists($tag)) {
            return ['status' => 'skipped', 'reason' => 'already_imported'];
        }

        if (!str_starts_with($release['url'], 'https://')) {
            return ['status' => 'failed', 'reason' => 'insecure_url'];
        }

        $zipPath    = null;
        $extractDir = null;

        try {
            try {
                $body = $this->download($release['url']);
            } catch (\Throwable $e) {
                CwmDebug::error('Demo content download failed', $e, 'democontent');

                return ['status' => 'failed', 'reason' => 'download_failed'];
            }

            if (!hash_equals($release['sha256'], hash('sha256', $body))) {
                return ['status' => 'failed', 'reason' => 'checksum_mismatch'];
            }

            $tmpBase = $this->tmpBase();
            $zipPath = $tmpBase . '/proclaim-demo-' . bin2hex(random_bytes(8)) . '.zip';

            if (file_put_contents($zipPath, $body) === false) {
                return ['status' => 'failed', 'reason' => 'temp_write_failed'];
            }

            $extractDir = $this->extract($zipPath, $tmpBase);

            if ($extractDir === null) {
                return ['status' => 'failed', 'reason' => 'extraction_failed'];
            }

            $manifestPath = $extractDir . '/' . self::MANIFEST_FILENAME;

            if (!is_readable($manifestPath)) {
                return ['status' => 'failed', 'reason' => 'missing_manifest'];
            }

            try {
                $payload = json_decode((string) file_get_contents($manifestPath), true, 512, \JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                return ['status' => 'failed', 'reason' => 'invalid_manifest'];
            }

            if (!\is_array($payload)) {
                return ['status' => 'failed', 'reason' => 'invalid_manifest'];
            }

            $importer = $this->importer ?? new Cwmcontentimporter();
            $counts   = $importer->import($tag, $payload, $extractDir);

            return ['status' => 'imported', 'counts' => $counts];
        } finally {
            if ($zipPath !== null && is_file($zipPath)) {
                @unlink($zipPath);
            }

            if ($extractDir !== null && is_dir($extractDir)) {
                Folder::delete($extractDir);
            }
        }
    }

    /**
     * A real, writable base directory to extract into.
     *
     * `Factory::getApplication()->get('tmp_path')` is populated from
     * `configuration.php` on a real site, but this component's own bare
     * PHPUnit harness boots a plain framework `Joomla\Console\Application`
     * with an empty config registry — `get('tmp_path')` there returns
     * nothing, and treating that as a path prefix would silently target the
     * filesystem root. Falling back to `sys_get_temp_dir()` avoids that
     * everywhere it might occur, not just under test.
     *
     * @return  string  No trailing slash.
     *
     * @since  __DEPLOY_VERSION__
     */
    private function tmpBase(): string
    {
        $configured = (string) Factory::getApplication()->get('tmp_path', '');

        return rtrim($configured !== '' ? $configured : sys_get_temp_dir(), '/');
    }

    /**
     * @return  array{url: string, sha256: string}|null
     *
     * @since  __DEPLOY_VERSION__
     */
    private function resolveRelease(): ?array
    {
        $release = $this->release ?? self::PINNED_RELEASE;

        if ($release === null) {
            return null;
        }

        if (
            !\is_string($release['url'] ?? null) || $release['url'] === ''
            || !\is_string($release['sha256'] ?? null)
            || preg_match('/^[0-9a-f]{64}$/', $release['sha256']) !== 1
        ) {
            return null;
        }

        return $release;
    }

    /**
     * @param   string  $url  The archive URL. Already confirmed `https://` by the caller.
     *
     * @return  string  The raw response body.
     *
     * @throws  \Exception  If the request itself fails (connection, TLS, timeout, …).
     *
     * @since  __DEPLOY_VERSION__
     */
    private function download(string $url): string
    {
        $http = $this->http ?? (new HttpFactory())->getHttp();

        $response = $http->get($url, [], self::HTTP_TIMEOUT);

        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException('Unexpected HTTP status ' . $response->getStatusCode() . ' fetching ' . $url);
        }

        return (string) $response->getBody();
    }

    /**
     * Safely extract only the entries this archive is ever expected to
     * carry into a fresh temp directory, ignoring everything else.
     *
     * Deliberately does not use {@see \Joomla\CMS\Installer\InstallerHelper::unpack()}
     * — that helper is built for a Joomla extension package (it looks for
     * an XML manifest and queues a warning when it does not find one) and,
     * more importantly, this project should not assume its path handling is
     * safe against a hostile entry name without checking; writing a narrow
     * allowlist here is simpler than auditing that assumption.
     *
     * @param   string  $zipPath  The already checksum-verified zip file.
     * @param   string  $tmpBase  Joomla's `tmp_path`, to extract under.
     *
     * @return  string|null  The extraction directory, or null on any problem.
     *
     * @since  __DEPLOY_VERSION__
     */
    private function extract(string $zipPath, string $tmpBase): ?string
    {
        $zip = new \ZipArchive();

        if ($zip->open($zipPath) !== true) {
            return null;
        }

        try {
            $safeNames = [];

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);

                if ($name === false || !$this->isSafeEntryName($name) || $this->isSymlinkEntry($zip, $i)) {
                    continue;
                }

                $safeNames[] = $name;
            }

            if (!\in_array(self::MANIFEST_FILENAME, $safeNames, true)) {
                return null;
            }

            $extractDir = $tmpBase . '/proclaim-demo-' . bin2hex(random_bytes(8));

            if (!mkdir($extractDir, 0755, true) && !is_dir($extractDir)) {
                return null;
            }

            if (!$zip->extractTo($extractDir, $safeNames)) {
                Folder::delete($extractDir);

                return null;
            }

            $realExtractDir = realpath($extractDir);

            if ($realExtractDir === false) {
                return null;
            }

            // Belt-and-suspenders: every entry we asked extractTo() for must
            // still resolve inside the directory we asked for.
            foreach ($safeNames as $name) {
                $real = realpath($extractDir . '/' . $name);

                if ($real === false || !str_starts_with($real, $realExtractDir . \DIRECTORY_SEPARATOR)) {
                    Folder::delete($extractDir);

                    return null;
                }
            }

            return $extractDir;
        } finally {
            $zip->close();
        }
    }

    /**
     * @param   string  $name  A zip entry name.
     *
     * @return  bool
     *
     * @since  __DEPLOY_VERSION__
     */
    private function isSafeEntryName(string $name): bool
    {
        if (
            $name === ''
            || str_contains($name, "\0")
            || str_contains($name, '..')
            || str_starts_with($name, '/')
            || preg_match('#^[A-Za-z]:#', $name) === 1
        ) {
            return false;
        }

        return $name === self::MANIFEST_FILENAME || preg_match(self::IMAGE_ENTRY_PATTERN, $name) === 1;
    }

    /**
     * Reject a symlink entry — `extractTo()` would otherwise create one
     * under the temp dir that could point anywhere on the filesystem.
     *
     * The Unix file mode a zip entry carries lives in the high 16 bits of
     * its external attributes; `0120000` (`S_IFLNK`) marks a symlink.
     *
     * @param   \ZipArchive  $zip    The open archive.
     * @param   int          $index  The entry's index.
     *
     * @return  bool
     *
     * @since  __DEPLOY_VERSION__
     */
    private function isSymlinkEntry(\ZipArchive $zip, int $index): bool
    {
        $opsys = 0;
        $attr  = 0;

        if (!$zip->getExternalAttributesIndex($index, $opsys, $attr)) {
            return false;
        }

        return $opsys === \ZipArchive::OPSYS_UNIX && (($attr >> 16) & 0170000) === 0120000;
    }
}
