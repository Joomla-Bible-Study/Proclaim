<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Administrator\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/**
 * Schema.org JSON-LD structured data helper.
 *
 * Pure static methods that accept item data and return JSON-LD arrays
 * for injection into frontend page heads.
 *
 * @since  10.1.0
 */
class CwmschemaorgHelper
{
    /**
     * Build CreativeWork JSON-LD for a single sermon detail page.
     *
     * @param   object  $item      Sermon item from model
     * @param   string  $url       Canonical page URL
     * @param   string  $siteName  Site name for publisher
     *
     * @return array JSON-LD data array
     *
     * @since 10.1.0
     */
    public static function buildSermonDetail(object $item, string $url, string $siteName): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type'    => 'CreativeWork',
            'name'     => $item->studytitle ?? '',
            'url'      => $url,
        ];

        // Description from studyintro
        $desc = self::cleanDescription($item->studyintro ?? '');

        if ($desc !== '') {
            $data['description'] = $desc;
        }

        // Date published
        if (!empty($item->studydate)) {
            $data['datePublished'] = self::toIso8601($item->studydate);
        }

        // Author (primary teacher)
        if (!empty($item->teachername)) {
            $data['author'] = [
                '@type' => 'Person',
                'name'  => $item->teachername,
            ];
        }

        // Series
        if (!empty($item->series_text)) {
            $series = [
                '@type' => 'CreativeWorkSeries',
                'name'  => $item->series_text,
            ];

            if (!empty($item->series_id) && (int) $item->series_id > 0) {
                $series['url'] = self::buildAbsoluteUrl(
                    'index.php?option=com_proclaim&view=cwmseriesdisplay&id=' . (int) $item->series_id
                );
            }

            $data['isPartOf'] = $series;
        }

        // Topics
        $topics = self::parseTopics($item->topic_text ?? '');

        if ($topics !== []) {
            $data['about'] = $topics;
        }

        // Message type as genre
        if (!empty($item->messageType)) {
            $data['genre'] = $item->messageType;
        }

        // Location
        if (!empty($item->location_text)) {
            $data['locationCreated'] = [
                '@type' => 'Place',
                'name'  => $item->location_text,
            ];
        }

        // Publisher
        if ($siteName !== '') {
            $data['publisher'] = [
                '@type' => 'Organization',
                'name'  => $siteName,
            ];
        }

        return $data;
    }

    /**
     * Build ItemList JSON-LD for the sermons list page.
     *
     * @param   array   $items     Array of sermon items
     * @param   string  $pageUrl   Canonical page URL
     * @param   string  $siteName  Site name
     *
     * @return array JSON-LD data array
     *
     * @since 10.1.0
     */
    public static function buildSermonList(array $items, string $pageUrl, string $siteName): array
    {
        $data = [
            '@context'      => 'https://schema.org',
            '@type'         => 'ItemList',
            'url'           => $pageUrl,
            'numberOfItems' => \count($items),
        ];

        if ($siteName !== '') {
            $data['name'] = $siteName;
        }

        $listItems = [];

        foreach ($items as $position => $item) {
            $listItem = [
                '@type'    => 'ListItem',
                'position' => $position + 1,
            ];

            $sermonData = [
                '@type' => 'CreativeWork',
                'name'  => $item->studytitle ?? '',
            ];

            if (!empty($item->id)) {
                $sermonData['url'] = self::buildAbsoluteUrl(
                    'index.php?option=com_proclaim&view=cwmsermon&id=' . (int) $item->id
                );
            }

            if (!empty($item->studydate)) {
                $sermonData['datePublished'] = self::toIso8601($item->studydate);
            }

            if (!empty($item->teachername)) {
                $sermonData['author'] = [
                    '@type' => 'Person',
                    'name'  => $item->teachername,
                ];
            }

            $listItem['item'] = $sermonData;
            $listItems[]      = $listItem;
        }

        if ($listItems !== []) {
            $data['itemListElement'] = $listItems;
        }

        return $data;
    }

    /**
     * Build Person JSON-LD for a teacher detail page.
     *
     * @param   object  $item  Teacher item from model
     * @param   string  $url   Canonical page URL
     *
     * @return array JSON-LD data array
     *
     * @since 10.1.0
     */
    public static function buildTeacherDetail(object $item, string $url): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type'    => 'Person',
            'name'     => $item->teachername ?? '',
            'url'      => $url,
        ];

        // Job title
        if (!empty($item->title)) {
            $data['jobTitle'] = $item->title;
        }

        // Description from short bio or information
        $desc = self::cleanDescription($item->short ?? $item->information ?? '');

        if ($desc !== '') {
            $data['description'] = $desc;
        }

        // Image — use raw path, not rendered HTML
        $imagePath = $item->teacher_image ?? $item->image ?? '';

        if (\is_string($imagePath) && $imagePath !== '' && !str_contains($imagePath, '<')) {
            $data['image'] = Uri::root() . ltrim($imagePath, '/');
        }

        // Social links (sameAs)
        $sameAs = self::collectSocialLinks($item);

        if ($sameAs !== []) {
            $data['sameAs'] = $sameAs;
        }

        return $data;
    }

    /**
     * Build CreativeWorkSeries JSON-LD for a series detail page.
     *
     * @param   object  $item      Series item (single object despite $this->items name in view)
     * @param   array   $studies   Array of study items belonging to this series
     * @param   string  $url       Canonical page URL
     * @param   string  $siteName  Site name for publisher
     *
     * @return array JSON-LD data array
     *
     * @since 10.1.0
     */
    public static function buildSeriesDetail(object $item, array $studies, string $url, string $siteName): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type'    => 'CreativeWorkSeries',
            'name'     => $item->series_text ?? '',
            'url'      => $url,
        ];

        // Description
        $desc = self::cleanDescription($item->description ?? '');

        if ($desc !== '') {
            $data['description'] = $desc;
        }

        // Series image
        $imagePath = $item->series_thumbnail ?? '';

        if (\is_string($imagePath) && $imagePath !== '' && !str_contains($imagePath, '<')) {
            $data['image'] = Uri::root() . ltrim($imagePath, '/');
        }

        // Teacher as author
        if (!empty($item->teachername)) {
            $data['author'] = [
                '@type' => 'Person',
                'name'  => $item->teachername,
            ];
        }

        // Publisher
        if ($siteName !== '') {
            $data['publisher'] = [
                '@type' => 'Organization',
                'name'  => $siteName,
            ];
        }

        // Studies as hasPart
        $parts = [];

        foreach ($studies as $study) {
            $part = [
                '@type' => 'CreativeWork',
                'name'  => $study->studytitle ?? '',
            ];

            if (!empty($study->id)) {
                $part['url'] = self::buildAbsoluteUrl(
                    'index.php?option=com_proclaim&view=cwmsermon&id=' . (int) $study->id
                );
            }

            if (!empty($study->studydate)) {
                $part['datePublished'] = self::toIso8601($study->studydate);
            }

            $parts[] = $part;
        }

        if ($parts !== []) {
            $data['hasPart'] = $parts;
        }

        return $data;
    }

    /**
     * Inject a JSON-LD array into the document head.
     *
     * @param   array  $data  JSON-LD data array
     *
     * @return void
     *
     * @since 10.1.0
     */
    public static function inject(array $data): void
    {
        if ($data === []) {
            return;
        }

        try {
            $document = Factory::getApplication()->getDocument();
            $json     = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return;
        }

        $document->addCustomTag(
            '<script type="application/ld+json">' . $json . '</script>'
        );
    }

    /**
     * Strip HTML tags and truncate to ~200 characters for description fields.
     *
     * @param   string  $text  Raw text (may contain HTML)
     *
     * @return string Cleaned text
     *
     * @since 10.1.0
     */
    public static function cleanDescription(string $text): string
    {
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $text = trim(preg_replace('/\s+/', ' ', $text));

        if (mb_strlen($text) > 200) {
            $text = mb_substr($text, 0, 197) . '...';
        }

        return $text;
    }

    /**
     * Convert a date string to ISO 8601 format.
     *
     * @param   string  $date  Date string
     *
     * @return string ISO 8601 date
     *
     * @since 10.1.0
     */
    private static function toIso8601(string $date): string
    {
        try {
            return (new \DateTime($date))->format('c');
        } catch (\Exception $e) {
            return $date;
        }
    }

    /**
     * Parse comma-separated topic text into an array of strings.
     *
     * @param   string  $topicText  Comma-separated topics
     *
     * @return string[] Array of topic strings
     *
     * @since 10.1.0
     */
    private static function parseTopics(string $topicText): array
    {
        if ($topicText === '') {
            return [];
        }

        $topics = array_map('trim', explode(',', $topicText));

        return array_values(array_filter($topics, static fn (string $t): bool => $t !== ''));
    }

    /**
     * Build an absolute URL from a Joomla SEF route.
     *
     * @param   string  $route  Internal Joomla route
     *
     * @return string Absolute URL
     *
     * @since 10.1.0
     */
    private static function buildAbsoluteUrl(string $route): string
    {
        return rtrim(Uri::root(), '/') . '/' . ltrim(Route::_($route), '/');
    }

    /**
     * Collect valid social/web links from a teacher item into a sameAs array.
     *
     * @param   object  $item  Teacher item
     *
     * @return string[] Array of URLs
     *
     * @since 10.1.0
     */
    private static function collectSocialLinks(object $item): array
    {
        $links  = [];
        $fields = ['facebooklink', 'twitterlink', 'bloglink', 'website', 'link1', 'link2'];

        foreach ($fields as $field) {
            $value = $item->$field ?? '';

            if (\is_string($value) && $value !== '' && filter_var($value, FILTER_VALIDATE_URL)) {
                $links[] = $value;
            }
        }

        return $links;
    }
}
