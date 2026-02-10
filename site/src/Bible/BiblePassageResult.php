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
 * Value object representing a scripture passage result from any provider.
 *
 * @since  10.1.0
 */
class BiblePassageResult
{
    /**
     * The passage text (plain text or HTML).
     *
     * @var  string
     * @since  10.1.0
     */
    public string $text;

    /**
     * The normalized scripture reference.
     *
     * @var  string
     * @since  10.1.0
     */
    public string $reference;

    /**
     * The translation abbreviation used.
     *
     * @var  string
     * @since  10.1.0
     */
    public string $translation;

    /**
     * Copyright notice for this translation.
     *
     * @var  string
     * @since  10.1.0
     */
    public string $copyright;

    /**
     * Whether the text contains HTML markup.
     *
     * @var  bool
     * @since  10.1.0
     */
    public bool $isHtml;

    /**
     * Constructor.
     *
     * @param   string  $text         The passage text
     * @param   string  $reference    The scripture reference
     * @param   string  $translation  The translation abbreviation
     * @param   string  $copyright    Copyright notice
     * @param   bool    $isHtml       Whether text contains HTML
     *
     * @since  10.1.0
     */
    public function __construct(
        string $text = '',
        string $reference = '',
        string $translation = '',
        string $copyright = '',
        bool $isHtml = false
    ) {
        $this->text        = $text;
        $this->reference   = $reference;
        $this->translation = $translation;
        $this->copyright   = $copyright;
        $this->isHtml      = $isHtml;
    }

    /**
     * Whether the result has actual text content.
     *
     * @return  bool
     *
     * @since  10.1.0
     */
    public function hasText(): bool
    {
        return !empty($this->text);
    }
}
