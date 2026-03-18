<?php

/**
 * Schema.org plugin for Proclaim — adds Sermon, Teacher, and Series schema types.
 *
 * @package     Proclaim
 * @subpackage  Plugin.Schemaorg.Proclaim
 *
 * @copyright   (C) 2026 CWM Team All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Plugin\Schemaorg\Proclaim\Extension;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Event\Plugin\System\Schemaorg\BeforeCompileHeadEvent;
use Joomla\CMS\Event\Plugin\System\Schemaorg\PrepareDataEvent;
use Joomla\CMS\Event\Plugin\System\Schemaorg\PrepareFormEvent;
use Joomla\CMS\Event\Plugin\System\Schemaorg\PrepareSaveEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Schemaorg\SchemaorgPluginTrait;
use Joomla\CMS\Schemaorg\SchemaorgPrepareDateTrait;
use Joomla\CMS\Schemaorg\SchemaorgPrepareImageTrait;
use Joomla\Event\Priority;
use Joomla\Event\SubscriberInterface;

/**
 * Proclaim Schema.org Plugin
 *
 * Registers Sermon, Teacher, and Series as schema type options for
 * Proclaim content. Auto-populates schema fields from item data so
 * admins don't need to enter duplicate information.
 *
 * @since  10.3.0
 */
final class Proclaim extends CMSPlugin implements SubscriberInterface
{
    use SchemaorgPluginTrait;
    use SchemaorgPrepareDateTrait;
    use SchemaorgPrepareImageTrait;

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  10.3.0
     */
    protected $autoloadLanguage = true;

    /**
     * The default plugin name (required by SchemaorgPluginTrait).
     *
     * @var   string
     * @since 10.3.0
     */
    protected $pluginName = 'Sermon';

    /**
     * Context-to-schema-type mapping.
     *
     * @var   array<string, string>
     * @since 10.3.0
     */
    private const CONTEXT_TYPE_MAP = [
        'com_proclaim.cwmmessage' => 'Sermon',
        'com_proclaim.teacher'    => 'Teacher',
        'com_proclaim.serie'      => 'Series',
    ];

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   10.3.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onSchemaPrepareForm'       => 'onSchemaPrepareForm',
            'onSchemaPrepareData'       => 'onSchemaPrepareData',
            'onSchemaPrepareSave'       => 'onSchemaPrepareSave',
            'onSchemaBeforeCompileHead' => ['onSchemaBeforeCompileHead', Priority::BELOW_NORMAL],
        ];
    }

    /**
     * Register all Proclaim schema types and load form fields.
     *
     * Overrides the trait method to add Sermon, Teacher, and Series
     * as separate options in the schemaType dropdown.
     *
     * @param   PrepareFormEvent  $event  The form event
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function onSchemaPrepareForm(PrepareFormEvent $event): void
    {
        $form    = $event->getForm();
        $context = $form->getName();

        if (!$this->isSupported($context)) {
            return;
        }

        $schemaType = $form->getField('schemaType', 'schema');

        if ($schemaType instanceof ListField) {
            // Only add the type relevant to this context
            $type = self::CONTEXT_TYPE_MAP[$context] ?? null;

            if ($type !== null) {
                $schemaType->addOption($type, ['value' => $type]);
            }
        }

        // Load the form fields
        $formFile = JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/forms/schemaorg.xml';

        if (is_file($formFile)) {
            $form->loadFile($formFile);
        }

        // Inject JS to track which schema fields the user edits.
        // Stores field names in a JSON array so save can selectively preserve edits.
        $this->getApplication()->getDocument()->addScriptDeclaration(
            <<<'JS'
            document.addEventListener('DOMContentLoaded', function() {
                var tracker = document.getElementById('jform_schema__editedFields');
                if (!tracker) return;
                var edited = new Set();
                var skip = ['jform[schema][_editedFields]', 'jform[schema][schemaType]'];
                document.querySelectorAll('[name^="jform[schema]"]').forEach(function(el) {
                    if (skip.indexOf(el.name) !== -1 || el.type === 'hidden') return;
                    function mark() {
                        // Extract the field name from the subform path
                        // e.g., jform[schema][Sermon][headline] → headline
                        var parts = el.name.replace(/\]/g, '').split('[');
                        var field = parts[parts.length - 1];
                        if (field && field !== '@type') {
                            edited.add(field);
                            tracker.value = JSON.stringify(Array.from(edited));
                        }
                    }
                    el.addEventListener('change', mark);
                    el.addEventListener('input', mark);
                });
            });
            JS
        );
    }

    /**
     * Auto-populate schema fields from Proclaim item data.
     *
     * When an admin opens a sermon/teacher/series edit form, this
     * pre-fills the schema fields from the item's existing data.
     * The admin can then override any field before saving.
     *
     * @param   PrepareDataEvent  $event  The data preparation event
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function onSchemaPrepareData(PrepareDataEvent $event): void
    {
        $context = $event->getContext();
        $data    = $event->getData();

        if (!$this->isSupported($context)) {
            return;
        }

        // Only auto-populate if no schema type has been selected yet
        if (!empty($data->schema['schemaType']) && $data->schema['schemaType'] !== 'None') {
            return;
        }

        // Auto-set schema type and populate fields based on context
        match ($context) {
            'com_proclaim.cwmmessage' => $this->populateSermon($data),
            'com_proclaim.teacher'    => $this->populateTeacher($data),
            'com_proclaim.serie'      => $this->populateSeries($data),
            default                   => null,
        };
    }

    /**
     * Warn about missing schema fields on save.
     *
     * Enqueues a notice (not an error) when key structured data fields
     * are empty so the admin knows the output may be incomplete.
     *
     * @param   PrepareSaveEvent  $event  The save event
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function onSchemaPrepareSave(PrepareSaveEvent $event): void
    {
        $context = $event->getContext();

        if (!$this->isSupported($context)) {
            return;
        }

        $entry  = $event->getData();
        $item   = $event->getItem();
        $itemId = (int) ($entry->itemId ?? 0);

        // Field-level Smart Sync on save:
        // 1. Regenerate fresh schema from current item data
        // 2. Merge _editedFields (JS, this session) into _customFields (persistent)
        // 3. Overlay custom fields from the form data — everything else auto-updates
        //
        // _customFields is stored in the schema JSON and persists across sessions.
        // The Reset button clears it. Bulk sync ignores it (force overwrites all).
        $formSchema      = $event->getSchema();
        $sessionEdited   = [];
        $debug           = true; // TODO: remove after debugging

        if (!empty($formSchema['_editedFields'])) {
            try {
                $sessionEdited = json_decode($formSchema['_editedFields'], true, 512, JSON_THROW_ON_ERROR) ?: [];
            } catch (\Throwable) {
                $sessionEdited = [];
            }
        }

        if ($debug) {
            try {
                $app = Factory::getApplication();
                $app->enqueueMessage('Schema Debug — _editedFields raw: ' . ($formSchema['_editedFields'] ?? '(not set)'), 'info');
                $app->enqueueMessage('Schema Debug — sessionEdited: ' . json_encode($sessionEdited), 'info');
                $app->enqueueMessage('Schema Debug — entry->schema keys: ' . implode(', ', array_keys(json_decode($entry->schema ?? '{}', true) ?: [])), 'info');
            } catch (\Throwable) {
                // skip
            }
        }

        if (!empty($entry->schema) && $itemId > 0) {
            try {
                $incoming = json_decode($entry->schema, true, 512, JSON_THROW_ON_ERROR);
                unset($incoming['_editedFields']);

                // Load persistent custom fields list from existing DB row
                $existingSchema = $this->loadExistingSchema($itemId, $context);
                $customFields   = $existingSchema['_customFields'] ?? [];

                // Merge in any fields edited this session
                $customFields = array_values(array_unique(array_merge($customFields, $sessionEdited)));

                if ($debug) {
                    try {
                        $app = Factory::getApplication();
                        $app->enqueueMessage('Schema Debug — existing _customFields: ' . json_encode($existingSchema['_customFields'] ?? []), 'info');
                        $app->enqueueMessage('Schema Debug — merged customFields: ' . json_encode($customFields), 'info');
                        $app->enqueueMessage('Schema Debug — incoming headline: ' . ($incoming['headline'] ?? '(none)'), 'info');
                    } catch (\Throwable) {
                        // skip
                    }
                }

                // Regenerate fresh schema from item data
                $fresh = $this->generateSchemaFromItem($item, $context);

                if ($fresh !== null) {
                    if ($debug) {
                        try {
                            Factory::getApplication()->enqueueMessage(
                                'Schema Debug — fresh headline: ' . ($fresh['headline'] ?? '(none)')
                                . ' | item studytitle: ' . ($item->studytitle ?? '(none)'),
                                'info'
                            );
                        } catch (\Throwable) {
                            // skip
                        }
                    }

                    // Overlay persistent custom fields from form submission
                    foreach ($customFields as $field) {
                        if (\array_key_exists($field, $incoming)) {
                            $fresh[$field] = $incoming[$field];
                        }
                    }

                    $fresh['_customFields'] = !empty($customFields) ? $customFields : null;
                    $fresh['_autoHash']     = self::hashSchema($fresh);
                    $entry->schema = json_encode(
                        array_filter($fresh, static fn ($v) => $v !== null),
                        JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE
                    );
                } else {
                    $incoming['_customFields'] = !empty($customFields) ? $customFields : null;
                    $incoming['_autoHash']     = self::hashSchema($incoming);
                    $entry->schema = json_encode(
                        array_filter($incoming, static fn ($v) => $v !== null),
                        JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE
                    );
                }
            } catch (\Throwable) {
                // JSON error — skip
            }
        }

        $schema     = $event->getSchema();
        $schemaType = $schema['schemaType'] ?? '';
        $typeData   = $schema[$schemaType] ?? [];

        if (empty($typeData) || $schemaType === 'None') {
            return;
        }

        $missing = [];

        // Define recommended fields per schema type
        $recommended = match ($schemaType) {
            'Sermon'  => [
                'headline'      => 'PLG_SCHEMAORG_PROCLAIM_FIELD_HEADLINE',
                'description'   => 'PLG_SCHEMAORG_PROCLAIM_FIELD_DESCRIPTION',
                'datePublished' => 'PLG_SCHEMAORG_PROCLAIM_FIELD_DATE_PUBLISHED',
            ],
            'Teacher' => [
                'name'        => 'PLG_SCHEMAORG_PROCLAIM_FIELD_NAME',
                'description' => 'PLG_SCHEMAORG_PROCLAIM_FIELD_DESCRIPTION',
            ],
            'Series'  => [
                'name'        => 'PLG_SCHEMAORG_PROCLAIM_FIELD_NAME',
                'description' => 'PLG_SCHEMAORG_PROCLAIM_FIELD_DESCRIPTION',
            ],
            default   => [],
        };

        foreach ($recommended as $field => $langKey) {
            if (empty($typeData[$field])) {
                $missing[] = Text::_($langKey);
            }
        }

        if (!empty($missing)) {
            try {
                Factory::getApplication()->enqueueMessage(
                    Text::sprintf(
                        'PLG_SCHEMAORG_PROCLAIM_MISSING_FIELDS',
                        implode(', ', $missing)
                    ),
                    'notice'
                );
            } catch (\Throwable) {
                // App not available
            }
        }
    }

    /**
     * Clean up schema data before output in the @graph.
     *
     * @param   BeforeCompileHeadEvent  $event  The compile head event
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function onSchemaBeforeCompileHead(BeforeCompileHeadEvent $event): void
    {
        $schema = $event->getSchema();
        $graph  = $schema->get('@graph');

        foreach ($graph as &$entry) {
            $type = $entry['@type'] ?? '';

            if (!\in_array($type, ['CreativeWork', 'Person', 'CreativeWorkSeries'], true)) {
                continue;
            }

            // Strip internal tracking fields from output
            unset($entry['_autoHash'], $entry['_customFields']);

            if (!empty($entry['datePublished'])) {
                $entry['datePublished'] = $this->prepareDate($entry['datePublished']);
            }

            if (!empty($entry['dateModified'])) {
                $entry['dateModified'] = $this->prepareDate($entry['dateModified']);
            }

            if (!empty($entry['image'])) {
                $entry['image'] = $this->prepareImage($entry['image']);
            }

            // Flatten sameAs subform [{value: url}, ...] → [url, ...]
            if (!empty($entry['sameAs']) && \is_array($entry['sameAs'])) {
                $flat = [];

                foreach ($entry['sameAs'] as $item) {
                    if (\is_array($item) && !empty($item['value'])) {
                        $flat[] = $item['value'];
                    } elseif (\is_string($item)) {
                        $flat[] = $item;
                    }
                }

                $entry['sameAs'] = !empty($flat) ? $flat : null;
            }
        }

        $schema->set('@graph', $graph);
    }

    /**
     * Auto-populate sermon schema fields from message data.
     *
     * @param   object  $data  The form data object
     *
     * @return  void
     *
     * @since   10.3.0
     */
    private function populateSermon(object $data): void
    {
        $data->schema['schemaType'] = 'Sermon';

        $sermon          = [];
        $sermon['@type'] = 'CreativeWork';

        if (!empty($data->studytitle)) {
            $sermon['headline'] = $data->studytitle;
        }

        if (!empty($data->studyintro)) {
            $sermon['description'] = $this->cleanText($data->studyintro);
        }

        if (!empty($data->studydate)) {
            $sermon['datePublished'] = $data->studydate;
        }

        if (!empty($data->modified)) {
            $sermon['dateModified'] = $data->modified;
        }

        // Primary teacher as author
        if (!empty($data->teachername)) {
            $sermon['author'] = [
                '@type' => 'Person',
                'name'  => $data->teachername,
            ];
        }

        // Study image
        if (!empty($data->image)) {
            $sermon['image'] = $data->image;
        } elseif (!empty($data->thumbnailm)) {
            $sermon['image'] = $data->thumbnailm;
        }

        $data->schema['Sermon'] = $sermon;
    }

    /**
     * Auto-populate teacher schema fields from teacher data.
     *
     * @param   object  $data  The form data object
     *
     * @return  void
     *
     * @since   10.3.0
     */
    private function populateTeacher(object $data): void
    {
        $data->schema['schemaType'] = 'Teacher';

        $teacher          = [];
        $teacher['@type'] = 'Person';

        if (!empty($data->teachername)) {
            $teacher['name'] = $data->teachername;
        }

        if (!empty($data->title)) {
            $teacher['jobTitle'] = $data->title;
        }

        if (!empty($data->short)) {
            $teacher['description'] = $this->cleanText($data->short);
        } elseif (!empty($data->information)) {
            $teacher['description'] = $this->cleanText($data->information);
        }

        if (!empty($data->teacher_image)) {
            $teacher['image'] = $data->teacher_image;
        } elseif (!empty($data->teacher_thumbnail)) {
            $teacher['image'] = $data->teacher_thumbnail;
        }

        if (!empty($data->website)) {
            $teacher['url'] = $data->website;
        }

        // Social links → sameAs array
        $sameAs = $this->collectSocialLinks($data);

        if (!empty($sameAs)) {
            // Structure as subform expects: array of {value: url}
            $teacher['sameAs'] = array_map(
                static fn ($url) => ['value' => $url],
                $sameAs
            );
        }

        $data->schema['Teacher'] = $teacher;
    }

    /**
     * Auto-populate series schema fields from series data.
     *
     * @param   object  $data  The form data object
     *
     * @return  void
     *
     * @since   10.3.0
     */
    private function populateSeries(object $data): void
    {
        $data->schema['schemaType'] = 'Series';

        $series          = [];
        $series['@type'] = 'CreativeWorkSeries';

        if (!empty($data->series_text)) {
            $series['name'] = $data->series_text;
        }

        if (!empty($data->description)) {
            $series['description'] = $this->cleanText($data->description);
        }

        if (!empty($data->series_thumbnail)) {
            $series['image'] = $data->series_thumbnail;
        }

        $data->schema['Series'] = $series;
    }

    /**
     * Collect validated social link URLs from teacher data.
     *
     * Prefers the new social_links JSON field, falls back to legacy columns.
     *
     * @param   object  $data  The form data object
     *
     * @return  array  List of validated URLs
     *
     * @since   10.3.0
     */
    private function collectSocialLinks(object $data): array
    {
        $sameAs = [];

        // New social_links JSON field
        if (!empty($data->social_links) && \is_string($data->social_links)) {
            try {
                $links = json_decode($data->social_links, true, 512, JSON_THROW_ON_ERROR);

                foreach ($links as $link) {
                    if (!empty($link['url']) && filter_var($link['url'], FILTER_VALIDATE_URL)) {
                        $sameAs[] = $link['url'];
                    }
                }
            } catch (\Throwable) {
                // Malformed JSON
            }
        }

        // Legacy link fields as fallback
        if (empty($sameAs)) {
            foreach (['facebooklink', 'twitterlink', 'bloglink', 'link1', 'link2', 'link3'] as $field) {
                if (!empty($data->$field) && filter_var($data->$field, FILTER_VALIDATE_URL)) {
                    $sameAs[] = $data->$field;
                }
            }
        }

        return $sameAs;
    }

    /**
     * Load the existing _autoHash from the #__schemaorg row, if any.
     *
     * @param   int     $itemId   Item ID
     * @param   string  $context  Context string
     *
     * @return  string|null  The stored hash, or null if no row/hash exists
     *
     * @since   10.3.0
     */
    private function loadExistingAutoHash(int $itemId, string $context): ?string
    {
        $schema = $this->loadExistingSchema($itemId, $context);

        return $schema['_autoHash'] ?? null;
    }

    /**
     * Load the full existing schema array from #__schemaorg.
     *
     * @param   int     $itemId   Item ID
     * @param   string  $context  Context string
     *
     * @return  array|null  Schema data or null if no row
     *
     * @since   10.3.0
     */
    private function loadExistingSchema(int $itemId, string $context): ?array
    {
        if ($itemId <= 0) {
            return null;
        }

        try {
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select($db->quoteName('schema'))
                ->from($db->quoteName('#__schemaorg'))
                ->where($db->quoteName('itemId') . ' = ' . $itemId)
                ->where($db->quoteName('context') . ' = ' . $db->quote($context));
            $stored = $db->setQuery($query)->loadResult();

            if ($stored === null) {
                return null;
            }

            return json_decode($stored, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Compute a short hash of schema data for Smart Sync fingerprinting.
     *
     * @param   array  $schema  Schema data (without _autoHash)
     *
     * @return  string  12-character hash
     *
     * @since   10.3.0
     */
    private static function hashSchema(array $schema): string
    {
        $schema = self::stripInternal($schema);
        ksort($schema);

        return substr(md5(json_encode($schema, JSON_UNESCAPED_UNICODE)), 0, 12);
    }

    /**
     * Strip internal tracking keys from schema data.
     *
     * @param   array  $schema  Schema data
     *
     * @return  array  Cleaned data
     *
     * @since   10.3.0
     */
    private static function stripInternal(array $schema): array
    {
        unset($schema['_autoHash'], $schema['_customFields'], $schema['_editedFields']);

        return $schema;
    }

    /**
     * Normalize schema data for comparison by removing empty values,
     * internal keys, and note fields that the form POST includes but
     * stored data omits.
     *
     * @param   array  $schema  Schema data
     *
     * @return  array  Normalized data for comparison
     *
     * @since   10.3.0
     */
    private static function normalizeForCompare(array $schema): array
    {
        unset($schema['_autoHash']);

        // Recursively remove empty values and note fields
        $filtered = [];

        foreach ($schema as $key => $value) {
            // Skip note fields (form-only, never stored)
            if (str_starts_with($key, 'note')) {
                continue;
            }

            if (\is_array($value)) {
                $nested = self::normalizeForCompare($value);

                if (!empty($nested)) {
                    $filtered[$key] = $nested;
                }
            } elseif ($value !== '' && $value !== null) {
                $filtered[$key] = $value;
            }
        }

        ksort($filtered);

        return $filtered;
    }

    /**
     * Generate fresh auto-schema from a Table item based on context.
     *
     * @param   \Joomla\CMS\Table\TableInterface  $item     The saved Table object
     * @param   string                             $context  Form context
     *
     * @return  array|null  Schema data array or null if context not handled
     *
     * @since   10.3.0
     */
    private function generateSchemaFromItem(\Joomla\CMS\Table\TableInterface $item, string $context): ?array
    {
        return match ($context) {
            'com_proclaim.cwmmessage' => $this->buildSermonSchema($item),
            'com_proclaim.teacher'    => $this->buildTeacherSchema($item),
            'com_proclaim.serie'      => $this->buildSeriesSchema($item),
            default                   => null,
        };
    }

    /**
     * Build sermon schema from a Table item.
     *
     * @param   \Joomla\CMS\Table\TableInterface  $item  Message table
     *
     * @return  array
     *
     * @since   10.3.0
     */
    private function buildSermonSchema(\Joomla\CMS\Table\TableInterface $item): array
    {
        $schema = ['@type' => 'CreativeWork'];

        if (!empty($item->studytitle)) {
            $schema['headline'] = $item->studytitle;
        }

        if (!empty($item->studyintro)) {
            $schema['description'] = $this->cleanText($item->studyintro);
        }

        if (!empty($item->studydate) && $item->studydate !== '0000-00-00 00:00:00') {
            $schema['datePublished'] = $item->studydate;
        }

        if (!empty($item->modified) && $item->modified !== '0000-00-00 00:00:00') {
            $schema['dateModified'] = $item->modified;
        }

        if (!empty($item->image)) {
            $schema['image'] = $item->image;
        }

        try {
            $orgName = \CWM\Component\Proclaim\Administrator\Helper\CwmschemaorgHelper::getOrgName();

            if ($orgName !== '') {
                $schema['publisher'] = ['@type' => 'Organization', 'name' => $orgName];
            }
        } catch (\Throwable) {
            // Helper not available
        }

        return $schema;
    }

    /**
     * Build teacher schema from a Table item.
     *
     * @param   \Joomla\CMS\Table\TableInterface  $item  Teacher table
     *
     * @return  array
     *
     * @since   10.3.0
     */
    private function buildTeacherSchema(\Joomla\CMS\Table\TableInterface $item): array
    {
        $schema = ['@type' => 'Person'];

        if (!empty($item->teachername)) {
            $schema['name'] = $item->teachername;
        }

        if (!empty($item->title)) {
            $schema['jobTitle'] = $item->title;
        }

        if (!empty($item->short)) {
            $schema['description'] = $this->cleanText($item->short);
        } elseif (!empty($item->information)) {
            $schema['description'] = $this->cleanText($item->information);
        }

        if (!empty($item->teacher_image)) {
            $schema['image'] = $item->teacher_image;
        } elseif (!empty($item->teacher_thumbnail)) {
            $schema['image'] = $item->teacher_thumbnail;
        }

        if (!empty($item->website)) {
            $schema['url'] = $item->website;
        }

        // worksFor: teacher org_name → admin setting → site name
        try {
            $orgName = !empty($item->org_name)
                ? $item->org_name
                : \CWM\Component\Proclaim\Administrator\Helper\CwmschemaorgHelper::getOrgName();

            if ($orgName !== '') {
                $schema['worksFor'] = ['@type' => 'Organization', 'name' => $orgName];
            }
        } catch (\Throwable) {
            // Helper not available
        }

        return $schema;
    }

    /**
     * Build series schema from a Table item.
     *
     * @param   \Joomla\CMS\Table\TableInterface  $item  Series table
     *
     * @return  array
     *
     * @since   10.3.0
     */
    private function buildSeriesSchema(\Joomla\CMS\Table\TableInterface $item): array
    {
        $schema = ['@type' => 'CreativeWorkSeries'];

        if (!empty($item->series_text)) {
            $schema['name'] = $item->series_text;
        }

        if (!empty($item->description)) {
            $schema['description'] = $this->cleanText($item->description);
        }

        if (!empty($item->series_thumbnail)) {
            $schema['image'] = $item->series_thumbnail;
        }

        return $schema;
    }

    /**
     * Strip HTML tags and normalize whitespace from text.
     *
     * @param   string  $text  Raw text (may contain HTML)
     *
     * @return  string  Cleaned text
     *
     * @since   10.3.0
     */
    private function cleanText(string $text): string
    {
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

        return trim(preg_replace('/\s+/', ' ', $text));
    }
}
