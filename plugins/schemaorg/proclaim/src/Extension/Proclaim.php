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
use Joomla\CMS\Form\Field\ListField;
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
