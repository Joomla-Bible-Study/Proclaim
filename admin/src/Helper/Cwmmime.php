<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

/**
 * Shared MIME-type detection helper.
 *
 * The single home for the extension→MIME maps and the
 * mime_content_type → finfo → extension detection chain, shared by the backup
 * library, the addon base and local classes, and the podcast helper.
 *
 * @package  Proclaim.Admin
 * @since    10.3.3
 */
final class Cwmmime
{
    /**
     * Canonical single-extension → MIME-type map.
     *
     * Note: this is for detecting a single file's type. The broader pipe-keyed
     * "allowed types" catalog used by the media upload field lives in
     * Cwmmedia::getMimetypes() and is a separate concern.
     *
     * @var array<string, string>
     *
     * @since 10.3.3
     */
    private const MAP = [
        // Audio
        'mp3'  => 'audio/mpeg',
        'm4a'  => 'audio/mp4',
        'm4b'  => 'audio/mp4',
        'oga'  => 'audio/ogg',
        'ogg'  => 'audio/ogg',
        'wav'  => 'audio/wav',
        'flac' => 'audio/flac',
        'aac'  => 'audio/aac',
        'wma'  => 'audio/x-ms-wma',
        // Video
        'mp4'  => 'video/mp4',
        'm4v'  => 'video/mp4',
        'ogv'  => 'video/ogg',
        'webm' => 'video/webm',
        'mov'  => 'video/quicktime',
        'avi'  => 'video/x-msvideo',
        'mkv'  => 'video/x-matroska',
        'wmv'  => 'video/x-ms-wmv',
        // Documents
        'pdf'  => 'application/pdf',
        'txt'  => 'text/plain',
        'htm'  => 'text/html',
        'html' => 'text/html',
        'doc'  => 'application/msword',
        'xls'  => 'application/vnd.ms-excel',
        'ppt'  => 'application/vnd.ms-powerpoint',
        'sql'  => 'text/x-sql',
        'php'  => 'text/plain',
        // Images
        'gif'  => 'image/gif',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        // Archives / binaries
        'zip' => 'application/zip',
        'exe' => 'application/octet-stream',
    ];

    /**
     * Resolve a MIME type from a filename or URL by its extension.
     *
     * @param   string  $pathOrFilename  A file path, filename, or URL.
     *
     * @return  string|null  The mapped MIME type, or null when the extension is unknown.
     *
     * @since 10.3.3
     */
    public static function fromExtension(string $pathOrFilename): ?string
    {
        return self::MAP[self::extensionOf($pathOrFilename)] ?? null;
    }

    /**
     * Extract the lower-cased extension from a filename, path or URL.
     *
     * parse_url() splits on '?' and '#' whether or not the value is a URL, so
     * routing a bare filename through it loses everything after those
     * characters -- "Sermon #12.mp4" becomes "Sermon " and the extension is
     * gone. Filenames here are admin-entered and unsanitised, so a '#' in one
     * is ordinary rather than hostile. A query or fragment is therefore only
     * stripped when the value actually is a URL.
     *
     * Detected by '://' or a leading '//' rather than PHP_URL_SCHEME, because
     * a Windows path like "C:\media\file #1.mp4" reports its drive letter as
     * the scheme and would be misread as a URL.
     *
     * @param   string  $pathOrFilename  Filename, filesystem path or URL
     *
     * @return  string  Lower-cased extension, or an empty string
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function extensionOf(string $pathOrFilename): string
    {
        $path = $pathOrFilename;

        if (str_contains($pathOrFilename, '://') || str_starts_with($pathOrFilename, '//')) {
            $path = parse_url($pathOrFilename, PHP_URL_PATH) ?: $pathOrFilename;
        }

        return strtolower(pathinfo((string) $path, PATHINFO_EXTENSION));
    }

    /**
     * Detect a file's MIME type: real detection (mime_content_type, then finfo),
     * falling back to the extension map.
     *
     * @param   string  $filepath  Path to a local, readable file.
     *
     * @return  string|null  The detected MIME type, or null when undetermined.
     *
     * @since 10.3.3
     */
    public static function detect(string $filepath): ?string
    {
        if (\function_exists('mime_content_type')) {
            $mimeType = mime_content_type($filepath);

            if ($mimeType && $mimeType !== 'application/octet-stream') {
                return $mimeType;
            }
        }

        if (class_exists('finfo')) {
            $finfo    = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($filepath);

            if ($mimeType && $mimeType !== 'application/octet-stream') {
                return $mimeType;
            }
        }

        return self::fromExtension($filepath);
    }

    /**
     * Resolve a MIME type for a download response. Honours an explicit type,
     * otherwise derives one from the extension, falling back to a forced download.
     *
     * @param   string  $mimeType  An explicit MIME type, or '' to derive one.
     * @param   string  $file      The file path/name used to derive the type.
     *
     * @return  string  A non-empty MIME type suitable for a Content-Type header.
     *
     * @since 10.3.3
     */
    public static function forDownload(string $mimeType, string $file): string
    {
        if ($mimeType !== '') {
            return $mimeType;
        }

        return self::fromExtension($file) ?? 'application/force-download';
    }
}
