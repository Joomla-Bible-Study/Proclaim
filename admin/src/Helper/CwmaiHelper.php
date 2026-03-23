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
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Http\HttpFactory;
use Joomla\Registry\Registry;

/**
 * AI Content Assistant helper for sermon content generation
 *
 * Supports Claude (Anthropic), Gemini (Google), and ChatGPT (OpenAI) providers.
 *
 * @package  Proclaim.Admin
 * @since    10.1.0
 */
class CwmaiHelper
{
    /**
     * Provider constants
     *
     * @since 10.1.0
     */
    private const PROVIDER_CLAUDE  = 'claude';
    private const PROVIDER_GEMINI  = 'gemini';
    private const PROVIDER_OPENAI  = 'openai';

    /**
     * Cache TTL in seconds (5 minutes)
     *
     * @since 10.1.0
     */
    private const CACHE_TTL = 300;

    /**
     * Generate sermon content (topics, description, study text) using AI
     *
     * Results are cached for 5 minutes per unique context to avoid redundant
     * API calls when the user clicks AI Assist multiple times.
     *
     * Context keys for field toggles (all default to true):
     *   generate_topics — include topic generation
     *   generate_intro  — include description generation
     *   generate_text   — include study text generation
     *
     * @param   array  $context  Sermon context: title, scripture, video_title, video_description,
     *                           video_tags, existing_intro, existing_text, existing_topics,
     *                           generate_topics, generate_intro, generate_text, video_chapters
     *
     * @return  array  Generated content: ['topics' => string[], 'studyintro' => string, 'studytext' => string,
     *                 'chapters' => array]
     *
     * @throws  \RuntimeException  If API call fails or is not configured
     * @since   10.1.0
     */
    public static function generateSermonContent(array $context): array
    {
        $params   = Cwmparams::getAdmin()->params;
        $provider = $params->get('ai_provider', self::PROVIDER_CLAUDE);
        $apiKey   = $params->get('ai_api_key', '');
        $model    = $params->get('ai_model', '');

        if (empty($apiKey)) {
            throw new \RuntimeException(Text::_('JBS_CMN_AI_NO_API_KEY'));
        }

        // Check session cache for recent result with the same context
        $cacheKey = 'cwm_ai_' . md5(json_encode($context) . $provider . $model);
        $session  = Factory::getApplication()->getSession();
        $cached   = $session->get($cacheKey);

        if ($cached !== null) {
            try {
                $cached = json_decode($cached, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                $cached = null;
            }

            if (\is_array($cached) && !empty($cached['_ts']) && (time() - $cached['_ts']) < self::CACHE_TTL) {
                unset($cached['_ts']);

                return $cached;
            }
        }

        // Determine which fields to generate
        $fields = [
            'topics' => !empty($context['generate_topics'] ?? true),
            'intro'  => !empty($context['generate_intro'] ?? true),
            'text'   => !empty($context['generate_text'] ?? true),
        ];

        $wantChapters    = !empty($context['generate_chapters'] ?? true);
        $hasChapters     = $wantChapters && !empty($context['video_chapters']);
        // Suggest chapters whenever the user wants them and none exist yet —
        // the AI can infer logical structure from sermon content alone.
        $suggestChapters = $wantChapters && !$hasChapters;

        $voice        = $params->get('ai_voice', 'third_person');
        $teacherName  = $context['teacher_name'] ?? '';
        $systemPrompt = self::buildSystemPrompt($fields, $hasChapters, $suggestChapters, $voice, $teacherName);
        $userMessage  = self::buildUserMessage($context);

        $result = match ($provider) {
            self::PROVIDER_GEMINI => self::callGemini($apiKey, $model ?: 'gemini-2.0-flash', $systemPrompt, $userMessage),
            self::PROVIDER_OPENAI => self::callOpenAI($apiKey, $model ?: 'gpt-4o-mini', $systemPrompt, $userMessage),
            default               => self::callClaude($apiKey, $model ?: 'claude-haiku-4-5-20251001', $systemPrompt, $userMessage),
        };

        // Cache the result in the session
        $result['_ts'] = time();
        $session->set($cacheKey, json_encode($result));
        unset($result['_ts']);

        return $result;
    }

    /**
     * Retrieve video context/metadata from a media file's platform
     *
     * Loads the media file record, determines the platform, and fetches
     * video title, description, and tags where available.
     *
     * @param   int  $mediaFileId  The media file record ID
     *
     * @return  array  Normalized: ['video_title' => string, 'video_description' => string, 'video_tags' => string[]]
     *
     * @since   10.1.0
     */
    public static function getVideoContext(int $mediaFileId): array
    {
        $empty = ['video_title' => '', 'video_description' => '', 'video_tags' => [], 'video_chapters' => []];

        if ($mediaFileId <= 0) {
            return $empty;
        }

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select($db->quoteName(['m.params', 'm.server_id', 's.type']))
            ->from($db->quoteName('#__bsms_mediafiles', 'm'))
            ->join('LEFT', $db->quoteName('#__bsms_servers', 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('m.server_id'))
            ->where($db->quoteName('m.id') . ' = ' . (int) $mediaFileId);

        $db->setQuery($query);
        $row = $db->loadObject();

        if (!$row) {
            return $empty;
        }

        $params     = new Registry($row->params);
        $serverType = strtolower(trim($row->type ?? ''));
        $filename   = $params->get('filename', '');

        if (empty($filename)) {
            return $empty;
        }

        try {
            return match ($serverType) {
                'youtube' => self::fetchYouTubeMetadata($filename, (int) $row->server_id),
                'vimeo'   => self::fetchVimeoMetadata($filename),
                default   => $empty,
            };
        } catch (\Exception $e) {
            return $empty;
        }
    }

    /**
     * Check if AI assistant is configured and available
     *
     * @return  bool
     *
     * @since   10.1.0
     */
    public static function isConfigured(): bool
    {
        $params = Cwmparams::getAdmin()->params;

        if ($params->get('ai_api_key', '')) {
            return true;
        }

        return false;
    }

    /**
     * Fetch available models from the selected AI provider
     *
     * Queries the provider's models API and returns a filtered list suitable
     * for sermon content generation (text models only, no embedding/image models).
     *
     * @param   string  $provider  Provider identifier: 'claude', 'openai', or 'gemini'
     * @param   string  $apiKey    API key for the provider
     *
     * @return  array  Array of ['id' => string, 'name' => string] sorted by name
     *
     * @throws  \RuntimeException  If API call fails
     * @since   10.1.0
     */
    public static function fetchAvailableModels(string $provider, string $apiKey): array
    {
        if (empty($apiKey)) {
            throw new \RuntimeException(Text::_('JBS_CMN_AI_NO_API_KEY'));
        }

        return match ($provider) {
            self::PROVIDER_GEMINI => self::fetchGeminiModels($apiKey),
            self::PROVIDER_OPENAI => self::fetchOpenAIModels($apiKey),
            default               => self::fetchClaudeModels($apiKey),
        };
    }

    /**
     * Fetch available models from the Anthropic API
     *
     * @param   string  $apiKey  API key
     *
     * @return  array  Models list
     *
     * @throws  \RuntimeException
     * @since   10.1.0
     */
    private static function fetchClaudeModels(string $apiKey): array
    {
        $factory  = new HttpFactory();
        $http     = $factory->getHttp();
        $headers  = [
            'x-api-key'         => $apiKey,
            'anthropic-version' => '2023-06-01',
        ];

        $response = $http->get('https://api.anthropic.com/v1/models?limit=100', $headers);

        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException(
                Text::sprintf('JBS_ADM_AI_FETCH_ERROR', 'Claude', $response->getStatusCode())
            );
        }

        $data   = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $models = [];

        foreach ($data['data'] ?? [] as $model) {
            $id = $model['id'] ?? '';

            // Skip embedding and legacy models
            if (empty($id) || str_contains($id, 'embed')) {
                continue;
            }

            $models[] = [
                'id'   => $id,
                'name' => $model['display_name'] ?? $id,
            ];
        }

        usort($models, fn ($a, $b) => strcmp($a['name'], $b['name']));

        return $models;
    }

    /**
     * Fetch available models from the OpenAI API
     *
     * @param   string  $apiKey  API key
     *
     * @return  array  Models list
     *
     * @throws  \RuntimeException
     * @since   10.1.0
     */
    private static function fetchOpenAIModels(string $apiKey): array
    {
        $factory  = new HttpFactory();
        $http     = $factory->getHttp();
        $headers  = [
            'Authorization' => 'Bearer ' . $apiKey,
        ];

        $response = $http->get('https://api.openai.com/v1/models', $headers);

        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException(
                Text::sprintf('JBS_ADM_AI_FETCH_ERROR', 'OpenAI', $response->getStatusCode())
            );
        }

        $data   = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $models = [];

        foreach ($data['data'] ?? [] as $model) {
            $id = $model['id'] ?? '';

            // Only include GPT chat models (skip embeddings, whisper, dall-e, tts, etc.)
            if (empty($id)
                || str_contains($id, 'embed')
                || str_contains($id, 'whisper')
                || str_contains($id, 'dall-e')
                || str_contains($id, 'tts')
                || str_contains($id, 'davinci')
                || str_contains($id, 'babbage')
                || str_contains($id, 'moderation')
                || str_contains($id, 'realtime')
            ) {
                continue;
            }

            // Only include gpt and o-series models
            if (!str_starts_with($id, 'gpt-') && !str_starts_with($id, 'o1') && !str_starts_with($id, 'o3') && !str_starts_with($id, 'o4')) {
                continue;
            }

            $models[] = [
                'id'   => $id,
                'name' => $id,
            ];
        }

        usort($models, fn ($a, $b) => strcmp($a['name'], $b['name']));

        return $models;
    }

    /**
     * Fetch available models from the Google Gemini API
     *
     * @param   string  $apiKey  API key
     *
     * @return  array  Models list
     *
     * @throws  \RuntimeException
     * @since   10.1.0
     */
    private static function fetchGeminiModels(string $apiKey): array
    {
        $factory  = new HttpFactory();
        $http     = $factory->getHttp();

        $response = $http->get(
            'https://generativelanguage.googleapis.com/v1beta/models?key=' . urlencode($apiKey)
        );

        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException(
                Text::sprintf('JBS_ADM_AI_FETCH_ERROR', 'Gemini', $response->getStatusCode())
            );
        }

        $data   = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $models = [];

        foreach ($data['models'] ?? [] as $model) {
            $name = $model['name'] ?? '';

            // Only include generateContent-capable models
            $methods = $model['supportedGenerationMethods'] ?? [];

            if (empty($name) || !\in_array('generateContent', $methods, true)) {
                continue;
            }

            // Strip "models/" prefix for the ID
            $id          = str_replace('models/', '', $name);
            $displayName = $model['displayName'] ?? $id;

            $models[] = [
                'id'   => $id,
                'name' => $displayName,
            ];
        }

        usort($models, fn ($a, $b) => strcmp($a['name'], $b['name']));

        return $models;
    }

    /**
     * Build the system prompt for sermon content generation
     *
     * @param   array  $fields           Which fields to generate: ['topics' => bool, 'intro' => bool, 'text' => bool]
     * @param   bool   $hasChapters      Whether the video already has chapter timestamps
     * @param   bool   $suggestChapters  Whether the AI should suggest chapter timestamps
     *
     * @return  string
     *
     * @since   10.1.0
     */
    private static function buildSystemPrompt(
        array $fields = ['topics' => true, 'intro' => true, 'text' => true],
        bool $hasChapters = false,
        bool $suggestChapters = false,
        string $voice = 'third_person',
        string $teacherName = ''
    ): string {
        $instructions = [];
        $jsonKeys     = [];

        $voiceGuide = match ($voice) {
            'first_person'   => 'Write from the teacher\'s perspective in first person'
                . ($teacherName ? ' (as ' . $teacherName . ')' : '')
                . '. Use "I", "we", and "us". Sound warm, personal, and pastoral — '
                . 'as if the teacher is describing their own message to a friend.',
            'conversational' => 'Write in a warm, conversational tone that speaks directly to the listener. '
                . 'Use "you" and "we". Ask engaging questions. Sound like a trusted friend '
                . 'inviting someone to listen, not a catalog description.',
            'summary'        => 'Write in a concise, factual style with no narrative voice. '
                . 'Focus on the scripture references, key themes, and practical takeaways. '
                . 'Keep sentences short and informative.',
            default          => 'Write in third person.'
                . ($teacherName ? ' Refer to the teacher as ' . $teacherName . '.' : ''),
        };

        $instructions[] = 'You are a ministry content assistant helping organize sermon and Bible study records. '
            . 'Your task is to analyze sermon information and generate the requested content. '
            . $voiceGuide;

        $index = 1;

        if (!empty($fields['topics'])) {
            $instructions[] = $index . '. **Topics** (5-10 relevant topic tags) — short single words or two-word phrases '
                . 'that categorize the sermon. Examples: "Faith", "Prayer", "Holy Spirit", "Spiritual Growth", '
                . '"Forgiveness". Use title case.';
            $jsonKeys[] = '"topics":["Topic1","Topic2"]';
            $index++;
        }

        if (!empty($fields['intro'])) {
            $instructions[] = $index . '. **Study Introduction** (studyintro) — a compelling 2-3 sentence summary suitable '
                . 'for display in sermon listings and search results. Do not use markdown formatting.';
            $jsonKeys[] = '"studyintro":"Brief summary..."';
            $index++;
        }

        if (!empty($fields['text'])) {
            $textInstruction = $index . '. **Study Text** (studytext) — a rich, substantive 3-5 paragraph writeup of '
                . 'what the teacher actually preached. Cover the main scripture passage, the key points and arguments made, '
                . 'practical application for the listener, and any memorable illustrations or stories used. '
                . 'This should read like a detailed sermon recap that someone who missed the message could learn from. '
                . 'Use simple HTML for paragraphs (<p> tags only). Do not use markdown.';

            if ($hasChapters) {
                $textInstruction .= ' When referencing video chapters, embed clickable timestamp links using this format: '
                    . '<a href="#" class="cwm-timestamp" data-seconds="SECONDS">TIME</a> where SECONDS is the total '
                    . 'seconds and TIME is the display format (e.g. 2:30). Weave these naturally into the study text.';
            }

            $instructions[] = $textInstruction;
            $jsonKeys[]     = '"studytext":"<p>Detailed description...</p>"';
            $index++;
        }

        if ($suggestChapters) {
            $instructions[] = $index . '. **Suggested Chapters** (chapters) — Based on the sermon content, suggest 3-8 '
                . 'logical chapter timestamps that divide the sermon into meaningful sections. The first chapter must '
                . 'start at 0:00. Return as an array of objects with "time" (display format like "0:00" or "1:23:45") '
                . 'and "label" (short chapter title).';
            $jsonKeys[] = '"chapters":[{"time":"0:00","label":"Introduction"},{"time":"2:30","label":"Main Point"}]';
        }

        // Instruct AI to return empty values for fields not requested
        if (empty($fields['topics'])) {
            $jsonKeys[] = '"topics":[]';
        }

        if (empty($fields['intro'])) {
            $jsonKeys[] = '"studyintro":""';
        }

        if (empty($fields['text'])) {
            $jsonKeys[] = '"studytext":""';
        }

        if (!$suggestChapters) {
            $jsonKeys[] = '"chapters":[]';
        }

        $instructions[] = '';
        $instructions[] = 'Respond ONLY with valid JSON in this exact format:';
        $instructions[] = '{' . implode(',', $jsonKeys) . '}';

        return implode("\n\n", $instructions);
    }

    /**
     * Build the user message from sermon context
     *
     * @param   array  $context  Sermon context data
     *
     * @return  string
     *
     * @since   10.1.0
     */
    private static function buildUserMessage(array $context): string
    {
        $parts = [];

        if (!empty($context['title'])) {
            $parts[] = 'Sermon Title: ' . $context['title'];
        }

        if (!empty($context['scripture'])) {
            $parts[] = 'Scripture: ' . $context['scripture'];
        }

        if (!empty($context['video_title'])) {
            $parts[] = 'Video Title: ' . $context['video_title'];
        }

        if (!empty($context['video_description'])) {
            $parts[] = 'Video Description: ' . $context['video_description'];
        }

        if (!empty($context['video_tags'])) {
            $tags    = \is_array($context['video_tags']) ? implode(', ', $context['video_tags']) : $context['video_tags'];
            $parts[] = 'Video Tags: ' . $tags;
        }

        if (!empty($context['existing_intro'])) {
            $parts[] = 'Current Description: ' . strip_tags($context['existing_intro']);
        }

        if (!empty($context['existing_text'])) {
            $parts[] = 'Current Study Text: ' . strip_tags($context['existing_text']);
        }

        if (!empty($context['existing_topics'])) {
            $topics  = \is_array($context['existing_topics']) ? implode(', ', $context['existing_topics']) : $context['existing_topics'];
            $parts[] = 'Current Topics: ' . $topics;
        }

        if (!empty($context['video_chapters'])) {
            $chapterLines = [];

            foreach ($context['video_chapters'] as $ch) {
                $chapterLines[] = $ch['time'] . ' ' . $ch['label'];
            }

            $parts[] = "Video Chapters:\n" . implode("\n", $chapterLines);
        }

        if (empty($parts)) {
            $parts[] = 'No sermon details provided. Generate generic church sermon content.';
        }

        return implode("\n", $parts);
    }

    /**
     * Call the Anthropic Claude API
     *
     * @param   string  $apiKey        API key
     * @param   string  $model         Model identifier
     * @param   string  $systemPrompt  System prompt
     * @param   string  $userMessage   User message
     *
     * @return  array  Parsed content
     *
     * @throws  \RuntimeException
     * @since   10.1.0
     */
    private static function callClaude(string $apiKey, string $model, string $systemPrompt, string $userMessage): array
    {
        $factory = new HttpFactory();
        $http    = $factory->getHttp();

        $payload = json_encode([
            'model'      => $model,
            'max_tokens' => 2048,
            'system'     => $systemPrompt,
            'messages'   => [
                ['role' => 'user', 'content' => $userMessage],
            ],
        ], JSON_THROW_ON_ERROR);

        $headers = [
            'Content-Type'      => 'application/json',
            'x-api-key'         => $apiKey,
            'anthropic-version' => '2023-06-01',
        ];

        $response = $http->post('https://api.anthropic.com/v1/messages', $payload, $headers);

        if ($response->getStatusCode() !== 200) {
            try {
                $body = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                $body = [];
            }

            $detail = $body['error']['message'] ?? (string) $response->getBody();

            throw new \RuntimeException(
                'Claude API ' . $response->getStatusCode() . ': ' . $detail
            );
        }

        $data       = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $content    = $data['content'][0]['text'] ?? '';
        $stopReason = $data['stop_reason'] ?? '';

        if ($stopReason === 'max_tokens') {
            throw new \RuntimeException(
                Text::_('JBS_CMN_AI_ERROR') . ': ' . Text::_('JBS_CMN_AI_RESPONSE_TRUNCATED')
            );
        }

        return self::parseJsonResponse($content);
    }

    /**
     * Call the Google Gemini API
     *
     * @param   string  $apiKey        API key
     * @param   string  $model         Model identifier
     * @param   string  $systemPrompt  System prompt
     * @param   string  $userMessage   User message
     *
     * @return  array  Parsed content
     *
     * @throws  \RuntimeException
     * @since   10.1.0
     */
    private static function callGemini(string $apiKey, string $model, string $systemPrompt, string $userMessage): array
    {
        $factory = new HttpFactory();
        $http    = $factory->getHttp();

        // Ensure no double models/ prefix
        $model   = str_replace('models/', '', $model);
        $url     = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . $apiKey;

        $payload = json_encode([
            'system_instruction' => [
                'parts' => [['text' => $systemPrompt]],
            ],
            'contents' => [
                [
                    'parts' => [['text' => $userMessage]],
                ],
            ],
            'generationConfig' => [
                'temperature'      => 0.7,
                'maxOutputTokens'  => 4096,
                'responseMimeType' => 'application/json',
            ],
        ], JSON_THROW_ON_ERROR);

        $headers = [
            'Content-Type' => 'application/json',
        ];

        $response = $http->post($url, $payload, $headers);

        if ($response->getStatusCode() !== 200) {
            try {
                $body = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                $body = [];
            }

            $detail = $body['error']['message'] ?? (string) $response->getBody();

            throw new \RuntimeException(
                'Gemini API ' . $response->getStatusCode() . ': ' . $detail
            );
        }

        $data         = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $content      = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        $finishReason = $data['candidates'][0]['finishReason'] ?? '';

        if ($finishReason === 'MAX_TOKENS') {
            throw new \RuntimeException(
                Text::_('JBS_CMN_AI_ERROR') . ': ' . Text::_('JBS_CMN_AI_RESPONSE_TRUNCATED')
            );
        }

        return self::parseJsonResponse($content);
    }

    /**
     * Call the OpenAI ChatGPT API
     *
     * @param   string  $apiKey        API key
     * @param   string  $model         Model identifier
     * @param   string  $systemPrompt  System prompt
     * @param   string  $userMessage   User message
     *
     * @return  array  Parsed content
     *
     * @throws  \RuntimeException
     * @since   10.1.0
     */
    private static function callOpenAI(string $apiKey, string $model, string $systemPrompt, string $userMessage): array
    {
        $factory = new HttpFactory();
        $http    = $factory->getHttp();

        $payload = json_encode([
            'model'       => $model,
            'max_tokens'  => 2048,
            'temperature' => 0.7,
            'messages'    => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userMessage],
            ],
            'response_format' => ['type' => 'json_object'],
        ], JSON_THROW_ON_ERROR);

        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $apiKey,
        ];

        $response = $http->post('https://api.openai.com/v1/chat/completions', $payload, $headers);

        if ($response->getStatusCode() !== 200) {
            try {
                $body = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                $body = [];
            }

            $detail = $body['error']['message'] ?? (string) $response->getBody();

            throw new \RuntimeException(
                'OpenAI API ' . $response->getStatusCode() . ': ' . $detail
            );
        }

        $data         = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $content      = $data['choices'][0]['message']['content'] ?? '';
        $finishReason = $data['choices'][0]['finish_reason'] ?? '';

        if ($finishReason === 'length') {
            throw new \RuntimeException(
                Text::_('JBS_CMN_AI_ERROR') . ': ' . Text::_('JBS_CMN_AI_RESPONSE_TRUNCATED')
            );
        }

        return self::parseJsonResponse($content);
    }

    /**
     * Parse JSON response from any AI provider into standardized format
     *
     * @param   string  $content  Raw text response containing JSON
     *
     * @return  array  Parsed: ['topics' => string[], 'studyintro' => string, 'studytext' => string]
     *
     * @throws  \RuntimeException
     * @since   10.1.0
     */
    private static function parseJsonResponse(string $content): array
    {
        // Extract JSON from response (may be wrapped in markdown code blocks)
        if (preg_match('/\{[\s\S]*\}/', $content, $matches)) {
            $content = $matches[0];
        }

        try {
            $parsed = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $snippet = mb_substr(trim($content), 0, 120);

            throw new \RuntimeException(
                Text::_('JBS_CMN_AI_ERROR') . ': Invalid JSON — ' . $snippet
            );
        }

        return [
            'topics'     => (array) ($parsed['topics'] ?? []),
            'studyintro' => (string) ($parsed['studyintro'] ?? ''),
            'studytext'  => (string) ($parsed['studytext'] ?? ''),
            'chapters'   => (array) ($parsed['chapters'] ?? []),
        ];
    }

    /**
     * Fetch video metadata from YouTube Data API
     *
     * @param   string  $filename  YouTube video URL or ID
     * @param   int     $serverId  Server record ID (for API key lookup)
     *
     * @return  array  Normalized metadata
     *
     * @since   10.1.0
     */
    private static function fetchYouTubeMetadata(string $filename, int $serverId): array
    {
        $empty = ['video_title' => '', 'video_description' => '', 'video_tags' => []];

        // Extract video ID from URL
        $videoId = self::extractYouTubeId($filename);

        if (empty($videoId)) {
            return $empty;
        }

        // Get YouTube API key from server config
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        $query->select($db->quoteName('params'))
            ->from($db->quoteName('#__bsms_servers'))
            ->where($db->quoteName('id') . ' = ' . (int) $serverId);
        $db->setQuery($query);
        $serverParams = new Registry($db->loadResult());
        $apiKey       = $serverParams->get('api_key', '');

        if (empty($apiKey)) {
            return $empty;
        }

        // Check YouTube API daily quota before making the call
        if (!CwmyoutubeQuota::hasQuota($serverId, CwmyoutubeQuota::COST_VIDEOS)) {
            CwmyoutubeLogHelper::log(
                CwmyoutubeLogHelper::LEVEL_WARNING,
                'Admin: Quota exhausted — skipped YouTube metadata fetch',
                ['server_id' => $serverId, 'video_id' => $videoId, 'remaining' => CwmyoutubeQuota::getRemaining($serverId)]
            );

            return $empty;
        }

        $factory  = new HttpFactory();
        $http     = $factory->getHttp();
        $url      = 'https://www.googleapis.com/youtube/v3/videos?part=snippet&id='
            . urlencode($videoId) . '&key=' . urlencode($apiKey);

        $response = $http->get($url);

        // Record quota usage regardless of response (YouTube counts the call)
        CwmyoutubeQuota::recordUsage($serverId, CwmyoutubeQuota::COST_VIDEOS);

        if ($response->getStatusCode() === 403) {
            $body = (string) $response->getBody();

            if (CwmyoutubeQuota::isQuotaExceededError($body)) {
                CwmyoutubeQuota::markExhausted($serverId);
                CwmyoutubeLogHelper::log(
                    CwmyoutubeLogHelper::LEVEL_ERROR,
                    'YouTube API returned 403 quotaExceeded — local counter synced to exhausted',
                    ['server_id' => $serverId, 'method' => 'fetchYouTubeMetadata']
                );
            }

            return $empty;
        }

        if ($response->getStatusCode() !== 200) {
            return $empty;
        }

        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $item = $data['items'][0]['snippet'] ?? null;

        if (!$item) {
            return $empty;
        }

        $description = $item['description'] ?? '';

        return [
            'video_title'       => $item['title'] ?? '',
            'video_description' => $description,
            'video_tags'        => $item['tags'] ?? [],
            'video_chapters'    => self::parseYouTubeChapters($description),
        ];
    }

    /**
     * Fetch video metadata from Vimeo oEmbed API
     *
     * @param   string  $filename  Vimeo video URL or ID
     *
     * @return  array  Normalized metadata
     *
     * @since   10.1.0
     */
    private static function fetchVimeoMetadata(string $filename): array
    {
        $empty = ['video_title' => '', 'video_description' => '', 'video_tags' => []];

        // Build a URL for oEmbed
        $videoUrl = $filename;

        if (is_numeric($filename)) {
            $videoUrl = 'https://vimeo.com/' . $filename;
        } elseif (!str_starts_with($filename, 'http')) {
            $videoUrl = 'https://vimeo.com/' . ltrim($filename, '/');
        }

        $factory  = new HttpFactory();
        $http     = $factory->getHttp();
        $url      = 'https://vimeo.com/api/oembed.json?url=' . urlencode($videoUrl);
        $response = $http->get($url);

        if ($response->getStatusCode() !== 200) {
            return $empty;
        }

        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        return [
            'video_title'       => $data['title'] ?? '',
            'video_description' => $data['description'] ?? '',
            'video_tags'        => [],
        ];
    }

    /**
     * Parse YouTube chapter timestamps from a video description
     *
     * YouTube requires: 3+ chapters, first at 0:00, each at least 10 seconds apart.
     *
     * @param   string  $description  Video description text
     *
     * @return  array  Array of ['time' => '2:30', 'seconds' => 150, 'label' => 'Main Point']
     *
     * @since   10.1.0
     */
    public static function parseYouTubeChapters(string $description): array
    {
        if (empty($description)) {
            return [];
        }

        $chapters = [];

        // Match lines starting with a timestamp like "0:00", "1:23", "1:23:45"
        if (!preg_match_all('/^(\d{1,2}:\d{2}(?::\d{2})?)\s+(.+)$/m', $description, $matches, PREG_SET_ORDER)) {
            return [];
        }

        foreach ($matches as $match) {
            $time    = trim($match[1]);
            $label   = trim($match[2]);
            $parts   = array_map('intval', explode(':', $time));
            $seconds = 0;

            if (\count($parts) === 3) {
                $seconds = $parts[0] * 3600 + $parts[1] * 60 + $parts[2];
            } elseif (\count($parts) === 2) {
                $seconds = $parts[0] * 60 + $parts[1];
            }

            $chapters[] = [
                'time'    => $time,
                'seconds' => $seconds,
                'label'   => $label,
            ];
        }

        // YouTube rules: minimum 3 chapters, first must be at 0:00
        if (\count($chapters) < 3) {
            return [];
        }

        if ($chapters[0]['seconds'] !== 0) {
            return [];
        }

        // Each chapter must be at least 10 seconds apart
        for ($i = 1, $count = \count($chapters); $i < $count; $i++) {
            if ($chapters[$i]['seconds'] - $chapters[$i - 1]['seconds'] < 10) {
                return [];
            }
        }

        return $chapters;
    }

    /**
     * Sync metadata from YouTube for a media file without AI involvement
     *
     * Fetches raw YouTube metadata and matches tags against existing topics.
     *
     * @param   int  $mediaFileId  The media file record ID
     *
     * @return  array  Sync result with video_title, video_description, video_tags, video_chapters,
     *                 matched_topics, and optionally error
     *
     * @since   10.1.0
     */
    public static function syncFromYouTube(int $mediaFileId): array
    {
        if ($mediaFileId <= 0) {
            return ['error' => Text::_('JBS_CMN_YT_SYNC_NO_MEDIA')];
        }

        // Look up the server ID to check quota before making the API call
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        $query->select($db->quoteName('m.server_id'))
            ->from($db->quoteName('#__bsms_mediafiles', 'm'))
            ->join(
                'LEFT',
                $db->quoteName('#__bsms_servers', 's')
                . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('m.server_id')
            )
            ->where([
                $db->quoteName('m.id') . ' = ' . (int) $mediaFileId,
                'LOWER(' . $db->quoteName('s.type') . ') = ' . $db->quote('youtube'),
            ]);
        $db->setQuery($query);
        $serverId = (int) $db->loadResult();

        if ($serverId > 0 && !CwmyoutubeQuota::hasQuota($serverId, CwmyoutubeQuota::COST_VIDEOS)) {
            $remaining = CwmyoutubeQuota::getRemaining($serverId);
            $budget    = CwmyoutubeQuota::getDailyBudget($serverId);

            return [
                'error'       => Text::sprintf('JBS_CMN_YT_QUOTA_EXHAUSTED', $remaining, $budget),
                'quota_error' => true,
            ];
        }

        $videoContext = self::getVideoContext($mediaFileId);

        if (
            empty($videoContext['video_title'])
            && empty($videoContext['video_description'])
            && empty($videoContext['video_tags'])
        ) {
            return ['error' => Text::_('JBS_CMN_YT_SYNC_NO_METADATA')];
        }

        $matchedTopics = [];

        if (!empty($videoContext['video_tags'])) {
            $tagText       = implode(', ', $videoContext['video_tags']);
            $matchedTopics = CwmtopicSuggestionHelper::matchExistingTopics($tagText);
        }

        return [
            'video_title'       => $videoContext['video_title'] ?? '',
            'video_description' => $videoContext['video_description'] ?? '',
            'video_tags'        => $videoContext['video_tags'] ?? [],
            'video_chapters'    => $videoContext['video_chapters'] ?? [],
            'matched_topics'    => $matchedTopics,
        ];
    }

    /**
     * Extract YouTube video ID from various URL formats
     *
     * @param   string  $input  URL or video ID
     *
     * @return  string  Video ID or empty string
     *
     * @since   10.1.0
     */
    private static function extractYouTubeId(string $input): string
    {
        // Already a bare ID
        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $input)) {
            return $input;
        }

        // Standard and short URL patterns
        $patterns = [
            '/[?&]v=([a-zA-Z0-9_-]{11})/',
            '/youtu\.be\/([a-zA-Z0-9_-]{11})/',
            '/embed\/([a-zA-Z0-9_-]{11})/',
            '/\/v\/([a-zA-Z0-9_-]{11})/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input, $matches)) {
                return $matches[1];
            }
        }

        return '';
    }
}
