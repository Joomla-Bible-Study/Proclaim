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

use CWM\Component\Proclaim\Site\Helper\Cwmhelperroute;
use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Table\Table;
use Joomla\Component\Finder\Administrator\Indexer\Adapter;
use Joomla\Component\Finder\Administrator\Indexer\Helper;
use Joomla\Component\Finder\Administrator\Indexer\Indexer;
use Joomla\Component\Finder\Administrator\Indexer\Result;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseQuery;
use Joomla\Registry\Registry;


/**
 * Finder adapter for com_proclaim.
 *
 * @package     Proclaim
 * @subpackage  plg_finder_proclaim
 * @since       7.1.0
 */
final class Proclaim extends Adapter
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

    /**
     * Method to remove the link information for items that have been deleted.
     *
     * @param   string  $context  The context of the action being performed.
     * @param   Table   $table    A Table object containing the record to be deleted
     *
     * @return  void
     *
     * @throws  Exception on database error.
     * @since   2.5
     */
    public function onFinderAfterDelete($context, $table): void
    {
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
     * Method to determine if the access level of an item changed.
     *
     * @param   string  $context  The context of the content passed to the plugin.
     * @param   Table   $row      A JTable object
     * @param   bool    $isNew    If the content has just been created
     *
     * @return  void
     *
     * @throws  Exception on database error.
     * @since   7.1.0
     */
    public function onFinderAfterSave($context, $row, $isNew): void
    {
        if ($context === 'com_proclaim.message') {
            // Check if the access levels are different
            if (!$isNew && $this->old_access != $row->access) {
                // Process the change.
                $this->itemAccessChange($row);
            }

            // Reindex the item
            $this->reindex($row->id);
        }
    }

    /**
     * Method to reindex the link information for an item that has been saved.
     * This event is fired before the data is actually saved so we are going
     * to queue the item to be indexed later.
     *
     * @param   string  $context  The context of the content passed to the plugin.
     * @param   Table   $row      A JTable object
     * @param   bool    $isNew    If the content is just about to be created
     *
     * @return  bool  True on success.
     *
     * @throws  Exception on database error.
     * @since   7.1.0
     */
    public function onFinderBeforeSave($context, $row, $isNew): bool
    {
        // We only want to handle contacts here
        if ($context === 'com_proclaim.message') {
            // Query the database for the old access level if the item isn't new
            if (!$isNew) {
                $this->checkItemAccess($row);
            }
        }

        return true;
    }

    /**
     * Method to update the link information for items that have been changed
     * from outside the edit screen. This is fired when the item is published,
     * unpublished, archived, or unarchived from the list view.
     *
     * @param   string   $context  The context for the content passed to the plugin.
     * @param   array    $pks      A list of primary key ids of the content that has changed state.
     * @param   integer  $value    The value of the state that the content has been changed to.
     *
     * @return  void
     *
     * @since   7.1.0
     */
    public function onFinderChangeState($context, $pks, $value)
    {
        // We only want to handle sermons here
        if ($context === 'com_proclaim.message') {
            $this->itemStateChange($pks, $value);
        }

        // Handle when the plugin is disabled
        if ($context === 'com_plugins.plugin' && $value === 0) {
            $this->pluginDisable($pks);
        }
    }

    /**
     * Method to update the item link information when the item category is
     * changed. This is fired when the item category is published or unpublished
     * from the list view.
     *
     * @param   string   $extension  The extension whose category has been updated.
     * @param   array    $pks        A list of primary key ids of the content that has changed state.
     * @param   integer  $value      The value of the state that the content has been changed to.
     *
     * @return  void
     *
     * @throws Exception
     * @since   2.5
     */
    public function onFinderSeriesChangeState($extension, $pks, $value)
    {
        // Make sure we're handling com_content categories.
        if ($extension === 'com_proclaim') {
            $this->seriesStateChange($pks, $value);
        }
    }

    /**
     * @throws Exception
     * @since 10.0.0
     */
    private function seriesStateChange(array $pks, int $value): void
    {
        /*
         * The item's published state is tied to the category
         * published state, so we need to look up all published states
         * before we change anything.
         */
        foreach ($pks as $pk) {
            $query = clone $this->getStateQuery();
            $query->where('s.id = ' . (int)$pk);

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
        // Check if the extension is enabled
        if (ComponentHelper::isEnabled($this->extension) === false) {
            return;
        }

        $item->setLanguage();

        // Initialize the item parameters.

        // Initialize the item parameters.
        $item->params = new Registry($item->params);

        // Get the menu title if it exists.
        $title = $this->getItemMenuTitle($item->url);

        // Trigger the onContentPrepare event.
        $item->summary = Helper::prepareContent($item->summary, $item->params);
        $item->body    = Helper::prepareContent($item->body, $item->params);

        // Build the necessary route and path information.
        $item->url   = $this->getUrl($item->id, $this->extension, $this->layout);
        $item->route = Cwmhelperroute::getArticleRoute($item->slug, $item->language);

        // Get the menu title if it exists.
        $title = $this->getItemMenuTitle($item->url);

        // Adjust the title if necessary.
        if (!empty($title) && $this->params->get('use_menu_title', true)) {
            $item->title = $title;
        }

        $images = $item->images ? json_decode($item->images) : false;

        // Add the image.
        if ($images && !empty($images->image_intro)) {
            $item->imageUrl = $images->image_intro;
            $item->imageAlt = $images->image_intro_alt ?? '';
        }

        /*
         * Add the meta-data processing instructions based on the newsfeeds
         * configuration parameters.
         */

        // Handle the link to the meta-data.
        $item->addInstruction(Indexer::META_CONTEXT, 'summary');
        $item->addInstruction(Indexer::META_CONTEXT, 'body');
        $item->addInstruction(Indexer::META_CONTEXT, 'author');

        // Get taxonomies to display
        $taxonomies = $this->params->get('taxonomies', ['type', 'author', 'language']);

        // Translate the state. Articles should only be published if the category is published.
        $item->state = $this->translateState($item->state);

        // Add the type taxonomy data.
        $item->addTaxonomy('Type', 'Sermon');

        // Add the language taxonomy data.
        $item->addTaxonomy('Language', $item->language);

        // Get content extras.
        Helper::getContentExtras($item);

        // Index the item.
        $this->indexer->index($item);
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
     * Method to get the SQL query used to retrieve the list of content items.
     *
     * @param   mixed  $query  A JDatabaseQuery object or null.
     *
     * @return  DatabaseQuery  A database object.
     *
     * @since   7.1.0
     */
    protected function getListQuery($query = null): DatabaseQuery
    {
        $db = $this->getDatabase();

        // Check if we can use the supplied SQL query.
        $query = $query instanceof DatabaseQuery ? $query : $db->getQuery(true)
            ->select('a.id, a.studytitle AS title, a.alias, a.studyintro AS summary, a.studytext as body')
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

        $case_when_category_alias = ' CASE WHEN ';
        $case_when_category_alias .= $query->charLength('s.alias', '!=', '0');
        $case_when_category_alias .= ' THEN ';
        $s_id                     = $query->castAsChar('s.id');
        $case_when_category_alias .= $query->concatenate([$s_id, 's.alias'], ':');
        $case_when_category_alias .= ' ELSE ';
        $case_when_category_alias .= $s_id . ' END as seriesslug';
        $query->select($case_when_category_alias);

        $query->select('u.teachername AS author')
            ->from('#__bsms_studies AS a')
            ->join('LEFT', '#__bsms_teachers AS u ON u.id = a.teacher_id')
            ->join('LEFT', '#__bsms_series AS s ON s.id = a.series_id');

        return $query;
    }
}
