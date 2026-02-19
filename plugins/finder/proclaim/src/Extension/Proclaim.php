<?php

/**
 * Finder adapter for Proclaim.
 *
 * @package        Proclaim.Finder
 * @subpackage     plg_finder_proclaim
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 * */

namespace CWM\Plugin\Finder\Proclaim\Extension;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CwmscriptureHelper;
use CWM\Component\Proclaim\Site\Helper\Cwmhelperroute;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\Finder as FinderEvent;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\Component\Finder\Administrator\Indexer\Adapter;
use Joomla\Component\Finder\Administrator\Indexer\Helper;
use Joomla\Component\Finder\Administrator\Indexer\Indexer;
use Joomla\Component\Finder\Administrator\Indexer\Result;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\QueryInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;

/**
 * Finder adapter for com_proclaim.
 *
 * @package     Proclaim
 * @subpackage  plg_finder_proclaim
 * @since       7.1.0
 */
final class Proclaim extends Adapter implements SubscriberInterface
{
    use DatabaseAwareTrait;

    /**
     * The plugin identifier.
     *
     * @var    string
     * @since  7.1.0
     */
    protected $context = 'Proclaim';

    /**
     * The extension name.
     *
     * @var    string
     * @since  7.1.0
     */
    protected $extension = 'com_proclaim';

    /**
     * The sublayout to use when rendering the results.
     *
     * @var    string
     * @since  7.1.0
     */
    protected $layout = 'cwmsermon';

    /**
     * The type of content that the adapter indexes.
     *
     * @var    string
     * @since  7.1.0
     */
    protected $type_title = 'Studies';

    /**
     * The table name.
     *
     * @var    string
     * @since  7.1.0
     */
    protected $table = '#__bsms_studies';

    /**
     * The field name for the published state.
     *
     * @var    string
     * @since  7.1.0
     */
    protected $state_field = 'published';

    /**
     * Load the language file on instantiation.
     *
     * @var    bool
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    /**
     * The old series access level before saving.
     *
     * @var    int
     * @since  7.1.0
     */
    protected int $old_seriesAccess = 0;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   5.0.0
     */
    public static function getSubscribedEvents(): array
    {
        return array_merge(parent::getSubscribedEvents(), [
            'onFinderChangeState' => 'onFinderChangeState',
            'onFinderAfterDelete' => 'onFinderAfterDelete',
            'onFinderBeforeSave'  => 'onFinderBeforeSave',
            'onFinderAfterSave'   => 'onFinderAfterSave',
        ]);
    }

    /**
     * Method to set up the indexer to be run.
     *
     * @return  bool  True on success.
     *
     * @since   2.5
     */
    protected function setup(): bool
    {
        // Load the API if not already loaded
        if (!\defined('CWM_LOADED')) {
            $apiFile = JPATH_ADMINISTRATOR . '/components/com_proclaim/api.php';
            if (file_exists($apiFile)) {
                require_once $apiFile;
            }
        }

        return true;
    }

    /**
     * Method to remove the link information for items that have been deleted.
     *
     * @param   FinderEvent\AfterDeleteEvent   $event  The event instance.
     *
     * @return  void
     *
     * @throws  \Exception on database error.
     *@since   2.5
     */
    public function onFinderAfterDelete(FinderEvent\AfterDeleteEvent $event): void
    {
        $context = $event->getContext();
        $table   = $event->getItem();

        if ($context === 'com_proclaim.cwmmessage' || $context === 'com_proclaim.message') {
            $id = $table->id;
        } elseif ($context === 'com_finder.index') {
            $id = $table->link_id;
        } else {
            return;
        }

        // Remove the items.
        $this->remove($id);
    }

    /**
     * Smart Search after save message method.
     * Reindex the link information for a message that has been saved.
     * It also makes adjustments if the access level of an item or the
     * series to which it belongs has changed.
     *
     * @param   FinderEvent\AfterSaveEvent   $event  The event instance.
     *
     * @return  void
     *
     * @throws  \Exception on database error.
     *@since   2.5
     */
    public function onFinderAfterSave(FinderEvent\AfterSaveEvent $event): void
    {
        $context = $event->getContext();
        $row     = $event->getItem();
        $isNew   = $event->getIsNew();

        // We only want to handle messages here.
        if ($context === 'com_proclaim.cwmmessage' || $context === 'com_proclaim.message' || $context === 'com_proclaim.form') {
            // Check if the access levels are different
            if (!$isNew && $this->old_access !== $row->access) {
                // Process the change.
                $this->itemAccessChange($row);
            }

            // Reindex the item
            $this->reindex($row->id);
        }

        // Check for access changes in the Series.
        if ($context === 'com_proclaim.cwmserie' || $context === 'com_proclaim.series') {
            // Check if the access levels are different.
            if (!$isNew && $this->old_seriesAccess !== (int) $row->access) {
                $this->seriesAccessChange($row);
            }
        }
    }

    /**
     * Smart Search before content save method.
     * This event is fired before the data is actually saved.
     *
     * @param   FinderEvent\BeforeSaveEvent   $event  The event instance.
     *
     * @return  void
     *
     * @throws  \Exception on database error.
     * @since   2.5
     */
    public function onFinderBeforeSave(FinderEvent\BeforeSaveEvent $event): void
    {
        $context = $event->getContext();
        $row     = $event->getItem();
        $isNew   = $event->getIsNew();

        // We only want to handle messages here
        if ($context === 'com_proclaim.cwmmessage' || $context === 'com_proclaim.message' || $context === 'com_proclaim.form') {
            // Query the database for the old access level if the item isn't new
            if (!$isNew) {
                $this->checkItemAccess($row);
            }
        }

        // Check for access levels from the Series.
        if ($context === 'com_proclaim.cwmserie' || $context === 'com_proclaim.series') {
            // Query the database for the old access level if the item isn't new.
            if (!$isNew) {
                $this->checkSeriesAccess($row);
            }
        }
    }

    /**
     * Method to update the link information for items that have been changed
     * from outside the edit screen. This is fired when the item is published,
     * unpublished, archived, or unarchived from the list view.
     *
     * @param   FinderEvent\AfterChangeStateEvent  $event  The event instance.
     *
     * @return  void
     *
     * @throws \Exception
     * @since   2.5
     */
    public function onFinderChangeState(FinderEvent\AfterChangeStateEvent $event): void
    {
        $context = $event->getContext();
        $pks     = $event->getPks();
        $value   = $event->getValue();

        // We only want to handle sermons here
        if ($context === 'com_proclaim.cwmmessage' || $context === 'com_proclaim.message' || $context === 'com_proclaim.form') {
            $this->itemStateChange($pks, $value);
        } elseif ($context === 'com_proclaim.cwmserie' || $context === 'com_proclaim.series') {
            $this->seriesStateChange($pks, $value);
        }

        // Handle when the plugin is disabled
        if ($context === 'com_plugins.plugin' && $value === 0) {
            $this->pluginDisable($pks);
        }
    }

    /**
     * Method to check the existing access level for categories
     *
     * @param   Table  $row  A Table object
     *
     * @return  void
     *
     * @since   2.5
     */
    protected function checkSeriesAccess(Table $row): void
    {
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('access'))
            ->from($this->db->quoteName('#__bsms_series'))
            ->where($this->db->quoteName('id') . ' = ' . (int) $row->id);
        $this->db->setQuery($query);

        // Store the access level to determine if it changes
        $this->old_seriesAccess = (int) $this->db->loadResult();
    }

    /**
     * Method to update index data on series access level changes
     *
     * @param   Table  $row  A Table object
     *
     * @return  void
     *
     * @throws \Exception
     * @since   2.5
     */
    protected function seriesAccessChange(Table $row): void
    {
        $query = clone $this->getStateQuery();
        $query->where($this->db->quoteName('s.id') . ' = ' . (int) $row->id);

        // Get the access level.
        $this->db->setQuery($query);
        $items = $this->db->loadObjectList();

        // Adjust the access level for each item within the Series.
        foreach ($items as $item) {
            // Set the access level.
            $temp = max($item->access, $row->access);

            // Update the item.
            $this->change((int) $item->id, 'access', $temp);
        }
    }

    /**
     * Method to update index data on series access level changes
     *
     * @param   array  $pks    A list of primary key ids of the content that has changed state.
     * @param   int    $value  The value of the state that the content has been changed to.
     *
     * @return  void
     *
     * @throws \Exception
     * @since   2.5
     */
    protected function seriesStateChange(array $pks, int $value): void
    {
        /*
         * The item's published state is tied to the category
         * published state so we need to look up all published states
         * before we change anything.
         */
        foreach ($pks as $pk) {
            $query = clone $this->getStateQuery();
            $query->where($this->db->quoteName('s.id') . ' = ' . (int) $pk);

            // Get the published states.
            $this->db->setQuery($query);
            $items = $this->db->loadObjectList();

            // Adjust the state for each item within the category.
            foreach ($items as $item) {
                // Translate the state.
                $temp = $this->translateState($item->state, $value);

                // Update the item.
                $this->change($item->id, 'state', $temp);
            }
        }
    }

    /**
     * Method to get an SQL query to load the published and access states for
     * a message and a series.
     *
     * @return  QueryInterface  A database object.
     *
     * @since   2.5
     */
    protected function getStateQuery(): QueryInterface
    {
        $query = $this->db->getQuery(true);

        $db = $this->db;

        // Item ID
        $query->select($db->quoteName('a.id'));

        // Item and category published state
        $query->select($db->quoteName('a.' . $this->state_field, 'state') . ', ' . $db->quoteName('s.published', 'series_state'));

        // Item and series access levels
        $query->select($db->quoteName('a.access') . ', ' . $db->quoteName('s.access', 'series_access'))
            ->from($db->quoteName($this->table, 'a'))
            ->join('LEFT', $db->quoteName('#__bsms_series', 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('a.series_id'));

        return $query;
    }

    /**
     * Method to index an item. The item must be a FinderIndexerResult object.
     *
     * @param   Result  $item  The item to index as a FinderIndexerResult object.
     *
     * @return  void
     *
     * @throws  \Exception on database error.
     * @since   7.1.0
     */
    protected function index(Result $item): void
    {
        $item->setLanguage();

        // Check if the extension is enabled
        if (ComponentHelper::isEnabled($this->extension) === false) {
            return;
        }

        $item->context = 'com_proclaim.message';

        // Initialize the item parameters.
        $registry     = new Registry($item->params);
        $item->params = clone ComponentHelper::getParams('com_proclaim', true);
        $item->params->merge($registry);

        // Initialise metadata (empty as table doesn't have it)
        $item->metadata = new Registry();

        // Trigger the onContentPrepare event.
        $item->summary = Helper::prepareContent($item->summary, $item->params, $item);
        $item->body    = Helper::prepareContent($item->body, $item->params, $item);

        // Create a URL as an identifier to recognize items again.
        $item->url   = $this->getUrl($item->id, $this->extension, $this->layout);

        // Build the necessary route and path information.
        $item->route = Cwmhelperroute::getArticleRoute($item->slug, $item->series_id, $item->language);

        // Get the menu title if it exists.
        $title = $this->getItemMenuTitle($item->url);

        // Adjust the title if necessary.
        if (!empty($title) && $this->params->get('use_menu_title', true)) {
            $item->title = $title;
        }

        $images = null;
        if (!empty($item->thumbnailm)) {
            // Try to decode as JSON
            $decoded = json_decode($item->thumbnailm);

            if (json_last_error() === JSON_ERROR_NONE && \is_object($decoded)) {
                $images = $decoded;
            } else {
                // Treat as direct path
                $images = (object) ['image_intro' => $item->thumbnailm];
            }
        }

        // Add the image.
        if ($images && !empty($images->image_intro)) {
            $item->imageUrl = $images->image_intro;
            $item->imageAlt = $images->image_intro_alt ?? '';
        }

        // Add the meta-author.
        $item->metaauthor = $item->metadata->get('author');

        // Add the metadata processing instructions.
        $item->addInstruction(Indexer::META_CONTEXT, 'summary');
        $item->addInstruction(Indexer::META_CONTEXT, 'body');
        $item->addInstruction(Indexer::META_CONTEXT, 'author');

        // Translate the state. Messages should only be published if the series is published.
        $item->state = $this->translateState($item->state, $item->series_state);

        // Get taxonomies to display
        $taxonomies = $this->params->get('taxonomies', ['type', 'author', 'series', 'language', 'topic', 'scripter', 'location']);

        // Add the type taxonomy data.
        if (\in_array('type', $taxonomies, true)) {
            $item->addTaxonomy('Type', 'Sermon');
        }

        // Add the language taxonomy data.
        if (\in_array('language', $taxonomies, true)) {
            $item->addTaxonomy('Language', $item->language);
        }

        // Add the author taxonomy data.
        if ((!empty($item->author) || !empty($item->created_by_alias)) && \in_array('author', $taxonomies, true)) {
            $item->addTaxonomy('Author', !empty($item->created_by_alias) ? $item->created_by_alias : $item->author, $item->state);
        }

        // Add the series taxonomy data.
        if (!empty($item->series) && \in_array('series', $taxonomies, true)) {
            $item->addTaxonomy('Series', $item->series, $item->series_state, $item->series_access);
        }

        // Add the location taxonomy data (campus/location for multi-campus installations).
        if (!empty($item->location_id) && $item->location_id > 0 && \in_array('location', $taxonomies, true)) {
            $locationText = $item->location_text ?? '';

            if ($locationText !== '') {
                $item->addTaxonomy('Location', $locationText);
                $item->body .= ' ' . $locationText;
            }
        }

        // Add Topics
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('t.topic_text'))
            ->from($db->quoteName('#__bsms_topics', 't'))
            ->join('INNER', $db->quoteName('#__bsms_studytopics', 'st') . ' ON ' . $db->quoteName('st.topic_id') . ' = ' . $db->quoteName('t.id'))
            ->where($db->quoteName('st.study_id') . ' = ' . (int) $item->id)
            ->where($db->quoteName('t.published') . ' = 1');
        $db->setQuery($query);
        $topics = $db->loadColumn();

        if (!empty($topics)) {
            foreach ($topics as $topic) {
                // Translate topic if it's a language key
                $topic = Text::_($topic);

                if (\in_array('topic', $taxonomies, true)) {
                    $item->addTaxonomy('Topic', $topic);
                }
                // Add to the body for searchability
                $item->body .= ' ' . $topic;
            }
        }

        // Add Scripture/Book
        if (!empty($item->booknumber)) {
            $query = $db->getQuery(true)
                ->select($db->quoteName('bookname'))
                ->from($db->quoteName('#__bsms_books'))
                ->where($db->quoteName('booknumber') . ' = ' . (int) $item->booknumber);
            $db->setQuery($query);
            $bookname = $db->loadResult();

            if ($bookname) {
                // Translate bookname if it's a language key
                $bookname = Text::_($bookname);

                // Construct reference string
                $reference = $bookname . ' ' . $item->chapter_begin;
                if ($item->verse_begin !== '0') {
                    $reference .= ':' . $item->verse_begin;
                }

                if ($item->verse_end !== '0') {
                    $reference .= '-' . $item->verse_end;
                }

                if (\in_array('scripter', $taxonomies, true)) {
                    $item->addTaxonomy('Scripter', $reference);
                }

                // Add to body
                $item->body .= ' ' . $reference;
            }
        }

        // Also index all references from the junction table
        try {
            $scriptures = CwmscriptureHelper::getScripturesForStudy((int) $item->id);

            foreach ($scriptures as $ref) {
                if ($ref->referenceText !== '') {
                    $item->body .= ' ' . $ref->referenceText;
                }
            }
        } catch (\Exception $e) {
            // Junction table may not exist yet during migration
        }

        // Get content extras.
        Helper::getContentExtras($item);
        Helper::addCustomFields($item, 'com_proclaim.message');

        // Index the item.
        $this->indexer->index($item);
    }

    /**
     * Method to get the SQL query used to retrieve the list of message items.
     *
     * @param   mixed  $query  A JDatabaseQuery object or null.
     *
     * @return  QueryInterface  A database object.
     *
     * @since   7.1.0
     */
    protected function getListQuery($query = null): QueryInterface
    {
        $db = $this->getDatabase();

        // Check if we can use the supplied SQL query.
        $query = $query instanceof QueryInterface ? $query : $db->getQuery(true)
            ->select($db->quoteName('a.id') . ', ' . $db->quoteName('a.studytitle', 'title') . ', ' . $db->quoteName('a.alias') . ', ' . $db->quoteName('a.studyintro', 'summary') . ', ' . $db->quoteName('a.studytext', 'body'))
            ->select($db->quoteName('a.thumbnailm') . ', ' . $db->quoteName('a.series_id'))
            ->select($db->quoteName('a.published', 'state') . ', ' . $db->quoteName('a.studydate', 'start_date') . ', ' . $db->quoteName('a.user_id'))
            ->select($db->quoteName('a.language'))
            ->select($db->quoteName('a.access') . ', ' . $db->quoteName('a.ordering') . ', ' . $db->quoteName('a.params'))
            ->select($db->quoteName('a.publish_up', 'publish_start_date') . ', ' . $db->quoteName('a.publish_down', 'publish_end_date'))
            ->select($db->quoteName(['a.booknumber', 'a.chapter_begin', 'a.verse_begin', 'a.chapter_end', 'a.verse_end']))
            ->select($db->quoteName('s.series_text', 'series') . ', ' . $db->quoteName('s.published', 'series_state') . ', ' . $db->quoteName('s.access', 'series_access'));

        // Handle the alias CASE WHEN portion of the query
        $case_when_item_alias = ' CASE WHEN ';
        $case_when_item_alias .= $query->charLength('a.alias', '!=', '0');
        $case_when_item_alias .= ' THEN ';
        $a_id                 = $query->castAs('CHAR', 'a.id');
        $case_when_item_alias .= $query->concatenate([$a_id, 'a.alias'], ':');
        $case_when_item_alias .= ' ELSE ';
        $case_when_item_alias .= $a_id . ' END as slug';
        $query->select($case_when_item_alias);

        $case_when_series_alias = ' CASE WHEN ';
        $case_when_series_alias .= $query->charLength('s.alias', '!=', '0');
        $case_when_series_alias .= ' THEN ';
        $s_id                     = $query->castAs('CHAR', 's.id');
        $case_when_series_alias .= $query->concatenate([$s_id, 's.alias'], ':');
        $case_when_series_alias .= ' ELSE ';
        $case_when_series_alias .= $s_id . ' END as seriesslug';
        $query->select($case_when_series_alias);

        $query->select($db->quoteName('a.location_id') . ', ' . $db->quoteName('loc.location_text'))
            ->select($db->quoteName('t.teachername', 'author'))
            ->from($db->quoteName('#__bsms_studies', 'a'))
            ->join('LEFT', $db->quoteName('#__bsms_study_teachers', 'stj') . ' ON '
                . $db->quoteName('stj.study_id') . ' = ' . $db->quoteName('a.id')
                . ' AND ' . $db->quoteName('stj.ordering') . ' = 0')
            ->join('LEFT', $db->quoteName('#__bsms_teachers', 't') . ' ON '
                . $db->quoteName('t.id') . ' = COALESCE(' . $db->quoteName('stj.teacher_id') . ', ' . $db->quoteName('a.teacher_id') . ')')
            ->join('LEFT', $db->quoteName('#__bsms_series', 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('a.series_id'))
            ->join('LEFT', $db->quoteName('#__bsms_locations', 'loc') . ' ON ' . $db->quoteName('loc.id') . ' = ' . $db->quoteName('a.location_id'));

        return $query;
    }
}
