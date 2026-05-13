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
 * Validates and normalizes WEBVTT / SRT caption uploads.
 *
 * Pure-logic helper: extracted from CwmmediafileController::uploadVttXHR()
 * so the rejection rules and filename construction can be unit tested
 * without an HTTP / SAPI context. The controller stays responsible for
 * the I/O (session token, $_FILES handling, move_uploaded_file, JSON
 * response) and delegates every yes/no decision to this class.
 *
 * @since  10.3.0
 */
class CwmcaptionValidator
{
    /**
     * Caption file extensions accepted by the uploader.
     *
     * @var    string[]
     * @since  10.3.0
     */
    public const ALLOWED_EXTENSIONS = ['vtt', 'srt', 'sbv'];

    /**
     * Maximum caption file size in bytes (2 MB).
     *
     * @var    int
     * @since  10.3.0
     */
    public const MAX_SIZE_BYTES = 2 * 1024 * 1024;

    /**
     * Maximum length of the sanitized base name segment in the final
     * filename. Keeps stored filenames bounded regardless of input.
     *
     * @var    int
     * @since  10.3.0
     */
    public const MAX_BASENAME_LENGTH = 50;

    /**
     * Default base name used when sanitization removes every character
     * (e.g. a filename consisting only of Unicode or punctuation).
     *
     * @var    string
     * @since  10.3.0
     */
    public const FALLBACK_BASENAME = 'caption';

    /**
     * Check whether the given extension is in the caption whitelist.
     *
     * Comparison is case-insensitive — callers may pass an extension
     * exactly as `pathinfo()` returns it without pre-normalizing.
     *
     * @param   string  $ext  File extension without the leading dot.
     *
     * @return  bool  True when the extension is allowed.
     *
     * @since   10.3.0
     */
    public function isAllowedExtension(string $ext): bool
    {
        return \in_array(strtolower($ext), self::ALLOWED_EXTENSIONS, true);
    }

    /**
     * Check whether a file of the given byte count is within the caption
     * size limit.
     *
     * Zero or negative sizes are rejected — they indicate an empty or
     * corrupt upload.
     *
     * @param   int  $bytes  File size reported by the SAPI.
     *
     * @return  bool  True when 0 < $bytes <= MAX_SIZE_BYTES.
     *
     * @since   10.3.0
     */
    public function isAllowedSize(int $bytes): bool
    {
        return $bytes > 0 && $bytes <= self::MAX_SIZE_BYTES;
    }

    /**
     * Detect a caption file format by sniffing its header bytes.
     *
     * Recognizes:
     *  - WEBVTT files: leading `WEBVTT` token (per W3C VTT spec), with
     *    or without a UTF-8 BOM.
     *  - SBV files: a comma-joined `H:MM:SS.mmm,H:MM:SS.mmm` cue
     *    timestamp line (per YouTube SubViewer convention).
     *  - SRT files: a numeric cue index on its own line followed by
     *    a `HH:MM…` timestamp line (per SubRip convention).
     *
     * The SBV check runs before SRT so a 2-digit-hour SBV cue cannot
     * be misclassified — SBV's signature comma between timestamps is
     * unique to that format.
     *
     * @param   string  $head  First N bytes of the file. The caller is
     *                         responsible for choosing N — 64 bytes is
     *                         enough to cover all three signatures plus a BOM.
     *
     * @return  string|null  'vtt', 'srt', 'sbv', or null when none match.
     *
     * @since   10.3.0
     */
    public function detectFormat(string $head): ?string
    {
        $head = ltrim($head, "\xEF\xBB\xBF");
        $head = trim($head);

        if (str_starts_with($head, 'WEBVTT')) {
            return 'vtt';
        }

        if (preg_match('/^\d{1,2}:\d{2}:\d{2}\.\d{3},\d{1,2}:\d{2}:\d{2}\.\d{3}/', $head)) {
            return 'sbv';
        }

        if (preg_match('/^\d+\s*\r?\n\d{2}:\d{2}/', $head)) {
            return 'srt';
        }

        return null;
    }

    /**
     * Convert a YouTube SBV caption body to WebVTT.
     *
     * SBV cues are `H:MM:SS.mmm,H:MM:SS.mmm` on one line, followed by
     * one or more text lines, separated by blank lines. WebVTT expects
     * `HH:MM:SS.mmm --> HH:MM:SS.mmm` after a `WEBVTT` header — so the
     * conversion is mechanical: strip BOM, normalize line endings,
     * split each cue's timestamp line on `,`, zero-pad the hour to two
     * digits, and rejoin with ` --> `.
     *
     * @param   string  $body  Raw SBV file contents.
     *
     * @return  string  WebVTT body with header, suitable for writing to disk.
     *
     * @since   10.3.0
     */
    public function convertSbvToVtt(string $body): string
    {
        $body = ltrim($body, "\xEF\xBB\xBF");
        $body = str_replace(["\r\n", "\r"], "\n", $body);

        $blocks    = preg_split('/\n\s*\n/', trim($body));
        $converted = [];

        foreach ($blocks as $block) {
            $lines = explode("\n", $block);
            $first = array_shift($lines);

            if (!preg_match(
                '/^(\d{1,2}):(\d{2}:\d{2}\.\d{3}),(\d{1,2}):(\d{2}:\d{2}\.\d{3})$/',
                trim($first),
                $m
            )) {
                continue;
            }

            $start = \sprintf('%02d:%s', (int) $m[1], $m[2]);
            $end   = \sprintf('%02d:%s', (int) $m[3], $m[4]);

            $converted[] = $start . ' --> ' . $end . "\n" . implode("\n", $lines);
        }

        return "WEBVTT\n\n" . implode("\n\n", $converted) . "\n";
    }

    /**
     * Convert a SubRip (SRT) caption body to WebVTT.
     *
     * SRT cues are numbered with `HH:MM:SS,mmm --> HH:MM:SS,mmm` timestamps
     * separated by blank lines. WebVTT expects the same ` --> ` separator
     * but with `.` as the millisecond delimiter and an optional cue
     * identifier instead of a positional counter. The conversion is
     * mechanical: prepend the `WEBVTT` header, drop each cue's leading
     * numeric counter, and swap `,` for `.` inside the timestamp line only
     * — text bodies legitimately contain commas and must not be touched.
     *
     * @param   string  $body  Raw SRT file contents.
     *
     * @return  string  WebVTT body with header, suitable for writing to disk.
     *
     * @since   10.3.0
     */
    public function convertSrtToVtt(string $body): string
    {
        $body = ltrim($body, "\xEF\xBB\xBF");
        $body = str_replace(["\r\n", "\r"], "\n", $body);

        $blocks    = preg_split('/\n\s*\n/', trim($body));
        $converted = [];

        foreach ($blocks as $block) {
            $lines = explode("\n", $block);

            // Drop the SRT cue counter when the first line is a bare integer
            // — SRT counters are positional, not identifiers, and WebVTT does
            // not require them.
            if ($lines !== [] && preg_match('/^\d+$/', trim($lines[0]))) {
                array_shift($lines);
            }

            if ($lines === []) {
                continue;
            }

            // Swap the comma fraction separator for a period on the timestamp
            // line only. Match a full SRT timestamp pair to avoid touching
            // any commas inside cue text.
            $lines[0] = preg_replace(
                '/^(\d{2}:\d{2}:\d{2}),(\d{3})\s*-->\s*(\d{2}:\d{2}:\d{2}),(\d{3})(.*)$/',
                '$1.$2 --> $3.$4$5',
                $lines[0],
                1,
                $count
            );

            if (!$count) {
                continue;
            }

            $converted[] = implode("\n", $lines);
        }

        return "WEBVTT\n\n" . implode("\n\n", $converted) . "\n";
    }

    /**
     * Strip everything outside `[A-Za-z0-9_-]` from the given base name
     * and fall back to a constant when nothing usable remains.
     *
     * Defends against directory traversal, NUL bytes, Unicode lookalikes,
     * shell metacharacters, and zero-length names. The result is safe to
     * concatenate into a filesystem path.
     *
     * @param   string  $original  Caller-supplied base name (no extension).
     *
     * @return  string  Sanitized base name, never empty.
     *
     * @since   10.3.0
     */
    public function sanitizeFilename(string $original): string
    {
        $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '', $original);

        return $sanitized === '' || $sanitized === null ? self::FALLBACK_BASENAME : $sanitized;
    }

    /**
     * Build the final stored filename for a caption upload.
     *
     * Format: `caption_{timestamp}_{sanitized-base}.{ext}` — the timestamp
     * prefix prevents collisions between uploads of the same logical
     * filename.
     *
     * @param   string    $originalName  Original filename from the upload
     *                                   (may include or omit extension —
     *                                   only the base name is read).
     * @param   string    $ext           Lowercased extension to append.
     * @param   int|null  $timestamp     Override for the timestamp segment.
     *                                   Defaults to time(); pass an explicit
     *                                   value in tests for deterministic
     *                                   output.
     *
     * @return  string  Filename only — no directory component.
     *
     * @since   10.3.0
     */
    public function buildFilename(string $originalName, string $ext, ?int $timestamp = null): string
    {
        $base      = pathinfo($originalName, PATHINFO_FILENAME);
        $sanitized = $this->sanitizeFilename($base);
        $truncated = mb_substr($sanitized, 0, self::MAX_BASENAME_LENGTH);

        return 'caption_' . ($timestamp ?? time()) . '_' . $truncated . '.' . $ext;
    }
}
