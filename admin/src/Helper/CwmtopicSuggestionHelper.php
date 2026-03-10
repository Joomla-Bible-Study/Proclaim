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

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

/**
 * Helper for auto-suggesting topics from sermon text content
 *
 * @package  Proclaim.Admin
 * @since    10.1.0
 */
class CwmtopicSuggestionHelper
{
    /**
     * English stop words to exclude from keyword suggestions
     *
     * @var array
     * @since 10.1.0
     */
    private static array $stopWords = [
        'a', 'about', 'above', 'after', 'again', 'against', 'all', 'also', 'am', 'an', 'and',
        'any', 'are', 'as', 'at', 'be', 'because', 'been', 'before', 'being', 'below',
        'between', 'both', 'but', 'by', 'can', 'could', 'did', 'do', 'does', 'doing',
        'down', 'during', 'each', 'even', 'few', 'for', 'from', 'further', 'get', 'got',
        'had', 'has', 'have', 'having', 'he', 'her', 'here', 'hers', 'herself', 'him',
        'himself', 'his', 'how', 'however', 'i', 'if', 'in', 'into', 'is', 'it', 'its',
        'itself', 'just', 'know', 'let', 'like', 'look', 'make', 'may', 'me', 'might',
        'more', 'most', 'much', 'must', 'my', 'myself', 'new', 'no', 'nor', 'not', 'now',
        'of', 'off', 'on', 'once', 'one', 'only', 'or', 'other', 'our', 'ours', 'ourselves',
        'out', 'over', 'own', 'part', 'per', 'put', 'really', 'right', 'said', 'same', 'say',
        'see', 'she', 'should', 'show', 'so', 'some', 'still', 'such', 'take', 'than', 'that',
        'the', 'their', 'theirs', 'them', 'themselves', 'then', 'there', 'these', 'they',
        'thing', 'this', 'those', 'through', 'time', 'to', 'too', 'two', 'under', 'until',
        'up', 'upon', 'us', 'use', 'very', 'want', 'was', 'way', 'we', 'well', 'were',
        'what', 'when', 'where', 'which', 'while', 'who', 'whom', 'why', 'will', 'with',
        'within', 'without', 'would', 'yet', 'you', 'your', 'yours', 'yourself', 'yourselves',
        // Common sermon filler words
        'verse', 'chapter', 'book', 'passage', 'text', 'read', 'reading', 'today',
        'week', 'last', 'next', 'first', 'second', 'third', 'also', 'many', 'going',
        'come', 'came', 'went', 'back', 'good', 'great', 'every', 'need', 'tell',
        'told', 'think', 'thought', 'nbsp', 'amp', 'quot',
    ];

    /**
     * Match existing published topics against the provided text
     *
     * @param   string  $text  Plain text to search for topic matches
     *
     * @return  array  Array of matched topics: [['id' => int, 'text' => string, 'source' => 'existing']]
     *
     * @since   10.1.0
     */
    public static function matchExistingTopics(string $text): array
    {
        $text = strip_tags($text);

        if (empty(trim($text))) {
            return [];
        }

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select(
            $db->quoteName('id') . ', '
            . $db->quoteName('topic_text') . ', '
            . $db->quoteName('params', 'topic_params')
        )
            ->from($db->quoteName('#__bsms_topics'))
            ->where($db->quoteName('published') . ' = 1');

        $db->setQuery($query);
        $topics  = $db->loadObjectList();
        $matched = [];

        if (!$topics) {
            return [];
        }

        $textLower = mb_strtolower($text);

        foreach ($topics as $topic) {
            $topicName = Cwmtranslated::getTopicItemTranslated($topic);

            if (empty($topicName)) {
                continue;
            }

            $topicNameLower = mb_strtolower($topicName);

            // Word-boundary match: check if the topic name appears as a whole word/phrase
            $pattern = '/\b' . preg_quote($topicNameLower, '/') . '\b/iu';

            if (preg_match($pattern, $textLower)) {
                $matched[] = [
                    'id'     => (int) $topic->id,
                    'text'   => $topicName,
                    'source' => 'existing',
                ];
            }
        }

        return $matched;
    }

    /**
     * Extract keyword suggestions from text that could become new topics
     *
     * @param   string  $text           Plain text to analyze
     * @param   array   $excludeTopics  Topic names to exclude from suggestions (already matched)
     *
     * @return  array  Array of suggestions: [['word' => string, 'count' => int]]
     *
     * @since   10.1.0
     */
    public static function extractKeywordSuggestions(string $text, array $excludeTopics = []): array
    {
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if (empty(trim($text))) {
            return [];
        }

        // Normalize exclude list to lowercase
        $excludeLower = array_map('mb_strtolower', $excludeTopics);

        // Tokenize: split on non-word characters, keep only alphabetic tokens
        $words = preg_split('/[^a-zA-Z\'-]+/', mb_strtolower($text), -1, PREG_SPLIT_NO_EMPTY);

        if (!$words) {
            return [];
        }

        // Build stop words set for fast lookup
        $stopSet = array_flip(self::$stopWords);

        // Count single-word frequencies
        $wordFreq = [];

        foreach ($words as $word) {
            // Strip leading/trailing punctuation
            $word = trim($word, "'-");

            // Skip short words, stop words, and excluded topics
            if (mb_strlen($word) < 3) {
                continue;
            }

            if (isset($stopSet[$word])) {
                continue;
            }

            if (\in_array($word, $excludeLower, true)) {
                continue;
            }

            $wordFreq[$word] = ($wordFreq[$word] ?? 0) + 1;
        }

        // Extract significant bigrams (two-word phrases appearing 2+ times)
        $bigramFreq = [];

        for ($i = 0, $count = \count($words) - 1; $i < $count; $i++) {
            $w1 = trim($words[$i], "'-");
            $w2 = trim($words[$i + 1], "'-");

            if (mb_strlen($w1) < 3 || mb_strlen($w2) < 3) {
                continue;
            }

            if (isset($stopSet[$w1]) || isset($stopSet[$w2])) {
                continue;
            }

            $bigram = $w1 . ' ' . $w2;

            if (\in_array($bigram, $excludeLower, true)) {
                continue;
            }

            $bigramFreq[$bigram] = ($bigramFreq[$bigram] ?? 0) + 1;
        }

        // Filter to only words appearing more than once and sort by frequency
        arsort($wordFreq);
        $suggestions = [];

        foreach ($wordFreq as $word => $freq) {
            if ($freq < 2) {
                continue;
            }

            $suggestions[] = [
                'word'  => ucfirst($word),
                'count' => $freq,
            ];

            if (\count($suggestions) >= 10) {
                break;
            }
        }

        // Add significant bigrams
        arsort($bigramFreq);

        foreach ($bigramFreq as $bigram => $freq) {
            if ($freq < 2) {
                continue;
            }

            $suggestions[] = [
                'word'  => ucwords($bigram),
                'count' => $freq,
            ];

            if (\count($suggestions) >= 15) {
                break;
            }
        }

        // Re-sort all suggestions by count
        usort($suggestions, static function ($a, $b) {
            return $b['count'] <=> $a['count'];
        });

        return \array_slice($suggestions, 0, 10);
    }
}
