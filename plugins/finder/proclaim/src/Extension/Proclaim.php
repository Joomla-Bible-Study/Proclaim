<?php

/**
 * Finder adapter for Proclaim.
 *
 * @package        Proclaim.Finder
 * @subpackage     plg_finder_proclaim
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 * */

namespace CWM\Plugin\Finder\Proclaim\Extension;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\Finder as FinderEvent;
use CWM\Component\Proclaim\Site\Helper\Cwmhelperroute;
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
     * Load the language file on instantiation.
     *
     * @var    bool
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    protected int $old_seriesAccess;

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
            'onFinderSeriesChangeState' => 'onFinderSeriesChangeState',
            'onFinderChangeState'       => 'onFinderChangeState',
            'onFinderAfterDelete'       => 'onFinderAfterDelete',
            'onFinderBeforeSave'        => 'onFinderBeforeSave',
            'onFinderAfterSave'         => 'onFinderAfterSave',
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
        return true;
    }

    /**
     * Method to update the item link information when the item category is
     * changed. This is fired when the item category is published or unpublished
     * from the list view.
     *
     * @param   FinderEvent\AfterSeriesChangeStateEvent  $event  The event instance.
     *
     * @return  void
     *
     * @throws Exception
     * @since   2.5
     */
    public function onFinderSeriesChangeState(FinderEvent\AfterSeriesChangeStateEvent $event): void
    {
        // Make sure we're handling com_proclaim series.
        if ($event->getExtension() === 'com_proclaim') {
            $this->seriesStateChange($event->getPks(), $event->getValue());
        }
    }

    /**
     * Method to remove the link information for items that have been deleted.
     *
     * @param   FinderEvent\AfterDeleteEvent   $event  The event instance.
     *
     * @return  void
     *
     * @throws  Exception on database error.
     *@since   2.5
     */
    public function onFinderAfterDelete(FinderEvent\AfterDeleteEvent $event): void
    {
        $context = $event->getContext();
        $table   = $event->getItem();

        if ($context === 'com_proclaim.message') {
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
     * @throws  Exception on database error.
     *@since   2.5
     */
    public function onFinderAfterSave(FinderEvent\AfterSaveEvent $event): void
    {
        $context = $event->getContext();
        $row     = $event->getItem();
        $isNew   = $event->getIsNew();

        // We only want to handle messages here.
        if ($context === 'com_proclaim.message' || $context === 'com_proclaim.form') {
            // Check if the access levels are different
            if (!$isNew && $this->old_access !== $row->access) {
                // Process the change.
                $this->itemAccessChange($row);
            }

            // Reindex the item
            $this->reindex($row->id);
        }

        // Check for access changes in the Series.
        if ($context === 'com_proclaim.series') {
            // Check if the access levels are different.
            if (!$isNew && $this->old_seriesAccess !== $row->access) {
                $this->SeriesAccessChange($row);
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
     * @throws  Exception on database error.
     * @since   2.5
     */
    public function onFinderBeforeSave(FinderEvent\BeforeSaveEvent $event): void
    {
        $context = $event->getContext();
        $row     = $event->getItem();
        $isNew   = $event->getIsNew();

        // We only want to handle contacts here
        if ($context === 'com_proclaim.message' || $context === 'com_proclaim.form') {
            // Query the database for the old access level if the item isn't new
            if (!$isNew) {
                $this->checkItemAccess($row);
            }
        }

        // Check for access levels from the Series.
        if ($context === 'com_proclaim.series') {
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
     * @param   FinderEvent\AfterChangeStateEvent   $event  The event instance.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function onFinderChangeState(FinderEvent\AfterChangeStateEvent $event): void
    {
        $context = $event->getContext();
        $pks     = $event->getPks();
        $value   = $event->getValue();

        // We only want to handle sermons here
        if ($context === 'com_proclaim.message' || $context === 'com_proclaim.form') {
            $this->itemStateChange($pks, $value);
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
        $this->old_cataccess = $this->db->loadResult();
    }

    /**
     * Method to update index data on series access level changes
     *
     * @param   Table  $row  A Table object
     *
     * @return  void
     *
     * @throws Exception
     * @since   2.5
     */
    protected function seriesAccessChange(Table $row): void
    {
        $query = clone $this->getStateQuery();
        $query->where('s.id = ' . (int) $row->id);

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
     * @throws Exception
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
            $query->where('s.id = ' . (int) $pk);

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
     * a message and series.
     *
     * @return  QueryInterface  A database object.
     *
     * @since   2.5
     */
    protected function getStateQuery(): QueryInterface
    {
        $query = $this->db->getQuery(true);

        // Item ID
        $query->select('a.id');

        // Item and category published state
        $query->select('a.' . $this->state_field . ' AS state, s.published AS series_state');

        // Item and category access levels
        $query->select('a.access, c.access AS cat_access')
            ->from($this->table . ' AS a')
            ->join('LEFT', '#__bsms_series AS s ON s.id = a.series_id');

        return $query;
    }

    /**
     * Method to index an item. The item must be a FinderIndexerResult object.
     *
     * @param   Result  $item  The item to index as an FinderIndexerResult object.
     *
     * @return  void
     *
     * @throws  Exception on database error.
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

        // Initialise the item parameters.
        $registry     = new Registry($item->params);
        $item->params = clone ComponentHelper::getParams('com_proclaim', true);
        $item->params->merge($registry);

        $item->metadata = new Registry($item->metadata);

        // Get the menu title if it exists.
        $title = $this->getItemMenuTitle($item->url);

        // Trigger the onContentPrepare event.
        $item->summary = Helper::prepareContent($item->summary, $item->params, $item);
        $item->body    = Helper::prepareContent($item->body, $item->params, $item);

        // Create a URL as identifier to recognize items again.
        $item->url   = $this->getUrl($item->id, $this->extension, $this->layout);

        // Build the necessary route and path information.
        $item->route = Cwmhelperroute::getArticleRoute($item->slug, $item->series_id, $item->language);

        // Get the menu title if it exists.
        $title = $this->getItemMenuTitle($item->url);

        // Adjust the title if necessary.
        if (!empty($title) && $this->params->get('use_menu_title', true)) {
            $item->title = $title;
        }

        $images = $item->thumbnailm ? json_decode($item->thumbnailm) : false;

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
        $taxonomies = $this->params->get('taxonomies', ['type', 'author', 'series', 'language']);

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

        // Add the language taxonomy data.
        if (\in_array('language', $taxonomies, true)) {
            $item->addTaxonomy('Language', $item->language);
        }

        // Get content extras.
        Helper::getContentExtras($item);
        Helper::addCustomFields($item, 'com_proclaim.message');

        // Index the item.
        $this->indexer->index($item);
    }

    /**
     * Method to get the SQL query used to retrieve the list of messages items.
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
            ->select('a.id, a.studytitle AS title, a.alias, a.studyintro AS summary, a.studytext as body')
            ->select('a.thumbnailm')
            ->select('a.published AS state, a.studydate AS start_date, a.user_id')
            ->select('a.language')
            ->select('a.access, a.ordering, a.params')
            ->select('a.publish_up AS publish_start_date, a.publish_down AS publish_end_date')
            ->select('s.series_text AS series, s.published AS s_state, s.access');

        // Handle the alias CASE WHEN portion of the query
        $case_when_item_alias = ' CASE WHEN ';
        $case_when_item_alias .= $query->charLength('a.alias', '!=', '0');
        $case_when_item_alias .= ' THEN ';
        $a_id                 = $query->castAsChar('a.id');
        $case_when_item_alias .= $query->concatenate(array($a_id, 'a.alias'), ':');
        $case_when_item_alias .= ' ELSE ';
        $case_when_item_alias .= $a_id . ' END as slug';
        $query->select($case_when_item_alias);

        $case_when_series_alias = ' CASE WHEN ';
        $case_when_series_alias .= $query->charLength('s.alias', '!=', '0');
        $case_when_series_alias .= ' THEN ';
        $s_id                     = $query->castAsChar('s.id');
        $case_when_series_alias .= $query->concatenate([$s_id, 's.alias'], ':');
        $case_when_series_alias .= ' ELSE ';
        $case_when_series_alias .= $s_id . ' END as seriesslug';
        $query->select($case_when_series_alias);

        $query->select('t.teachername AS author')
            ->from('#__bsms_studies AS a')
            ->join('LEFT', '#__bsms_teachers AS t ON t.id = a.teacher_id')
            ->join('LEFT', '#__bsms_series AS s ON s.id = a.series_id');

        return $query;
    }
}
