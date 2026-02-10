<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Site\Bible;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Interface for Bible text providers.
 *
 * Each provider retrieves scripture passages from a different source
 * (local database, remote API, or iframe fallback).
 *
 * @since  10.1.0
 */
interface BibleProviderInterface
{
    /**
     * Retrieve a scripture passage.
     *
     * @param   string  $reference    Scripture reference (e.g. "John+3:16-18" or "Genesis+1:1-5")
     * @param   string  $translation  Translation abbreviation (e.g. "kjv", "web")
     *
     * @return  BiblePassageResult
     *
     * @since  10.1.0
     */
    public function getPassage(string $reference, string $translation): BiblePassageResult;

    /**
     * Get available translations for this provider.
     *
     * @return  array  Array of ['abbreviation' => string, 'name' => string, 'language' => string]
     *
     * @since  10.1.0
     */
    public function getAvailableTranslations(): array;

    /**
     * Whether this provider returns actual text (vs iframe URL).
     *
     * @return  bool
     *
     * @since  10.1.0
     */
    public function returnsText(): bool;

    /**
     * Whether this provider works without internet access.
     *
     * @return  bool
     *
     * @since  10.1.0
     */
    public function isOfflineCapable(): bool;

    /**
     * Get the provider identifier.
     *
     * @return  string  e.g. "local", "getbible"
     *
     * @since  10.1.0
     */
    public function getName(): string;
}
