<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Site\Bible\Provider;

use CWM\Component\Proclaim\Site\Bible\AbstractBibleProvider;
use CWM\Component\Proclaim\Site\Bible\BiblePassageResult;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * BibleGateway.com iframe provider.
 *
 * This is a backward-compatibility fallback that returns an iframe URL
 * pointing to BibleGateway's print interface. No actual text is retrieved.
 *
 * The $translation parameter maps to BibleGateway's numeric version codes
 * which are stored in template params as `bible_version`.
 *
 * @since  10.1.0
 */
class BibleGatewayProvider extends AbstractBibleProvider
{
    /**
     * Mapping from BibleGateway numeric version IDs to abbreviations.
     *
     * Used for migration purposes when converting existing configs to text-based providers.
     *
     * @var  array<int|string, string>
     * @since  10.1.0
     */
    public const VERSION_MAP = [
        9   => 'kjv',
        51  => 'nlt',
        47  => 'esv',
        31  => 'niv',
        49  => 'nasb',
        50  => 'nkjv',
        8   => 'asvd',
        15  => 'ylt',
        77  => 'hcsb',
        45  => 'amp',
        46  => 'cev',
        65  => 'msg',
        'GNT' => 'gnt',
    ];

    /**
     * @inheritDoc
     */
    public function getPassage(string $reference, string $translation): BiblePassageResult
    {
        $url = 'https://www.biblegateway.com/passage/?search='
            . $reference . '&version=' . $translation . '&interface=print';

        return new BiblePassageResult(
            reference: $reference,
            translation: $translation,
            isIframe: true,
            iframeUrl: $url
        );
    }

    /**
     * @inheritDoc
     */
    public function getAvailableTranslations(): array
    {
        // BibleGateway translations are defined in template.xml as static options
        return [];
    }

    /**
     * @inheritDoc
     */
    public function returnsText(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function isOfflineCapable(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'biblegateway';
    }
}
