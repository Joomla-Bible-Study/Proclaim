<?php

/**
 * Schema.org plugin for Proclaim — adds Sermon schema type with auto-population.
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
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Schemaorg\SchemaorgPluginTrait;
use Joomla\CMS\Schemaorg\SchemaorgPrepareDateTrait;
use Joomla\CMS\Schemaorg\SchemaorgPrepareImageTrait;
use Joomla\Event\Priority;
use Joomla\Event\SubscriberInterface;

/**
 * Proclaim Schema.org Plugin
 *
 * Adds "Sermon" as a schema type option for Proclaim messages, teachers,
 * and series. Auto-populates schema fields from item data so admins
 * don't need to enter duplicate information.
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
     * The name of the schema type shown in the dropdown.
     *
     * @var   string
     * @since 10.3.0
     */
    protected $pluginName = 'Sermon';

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
     * Clean up Sermon schema data before output.
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
            if (!isset($entry['@type']) || $entry['@type'] !== 'CreativeWork') {
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
                '@type' => 'person',
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
        $data->schema['schemaType'] = 'Sermon';

        $teacher          = [];
        $teacher['@type'] = 'Person';

        if (!empty($data->teachername)) {
            $teacher['headline'] = $data->teachername;
        }

        if (!empty($data->title)) {
            $teacher['description'] = $data->title;
        }

        if (!empty($data->short)) {
            $teacher['description'] = $this->cleanText($data->short);
        }

        if (!empty($data->teacher_image)) {
            $teacher['image'] = $data->teacher_image;
        }

        $data->schema['Sermon'] = $teacher;
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
        $data->schema['schemaType'] = 'Sermon';

        $series          = [];
        $series['@type'] = 'CreativeWorkSeries';

        if (!empty($data->series_text)) {
            $series['headline'] = $data->series_text;
        }

        if (!empty($data->description)) {
            $series['description'] = $this->cleanText($data->description);
        }

        if (!empty($data->series_thumbnail)) {
            $series['image'] = $data->series_thumbnail;
        }

        $data->schema['Sermon'] = $series;
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
