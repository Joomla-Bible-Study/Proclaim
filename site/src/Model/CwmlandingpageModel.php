<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

/**
 * Model class for LandingPage
 *
 * @package  Proclaim.Site
 * @since    7.0.0
 */
class CwmlandingpageModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @throws \Exception
     * @since      1.6
     * @see        ListModel
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id',
                's.id',
                'language',
                's.language',
            ];
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * This method should only be called once per instantiation and is designed
     * to be called on the first call to the getState() method unless the model
     * configuration flag to ignore the request is set.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     *
     * @throws \Exception
     * @since   11.1
     */
    protected function populateState($ordering = null, $direction = null): void
    {
        $order = $this->getUserStateFromRequest($this->context . '.filter.order', 'filter_orders');
        $this->setState('filter.order', $order);

        // Load the parameters.
        $template = Cwmparams::getTemplateparams();
        $admin    = Cwmparams::getAdmin();

        $params = $template->params;

        $t = $params->get('sermonsid');

        if (!$t) {
            $input = Factory::getApplication();
            $t     = $input->get('t', 1, 'int');
        }

        $template->id = $t;

        $this->setState('template', $template);
        $this->setState('administrator', $admin);

        // Get show_archived parameter, fall back to template default
        $app          = Factory::getApplication();
        $menuParams   = $app->getParams();
        $showArchived = $menuParams->get('show_archived', '');
        if ($showArchived === '' || $showArchived === null) {
            $showArchived = $params->get('default_show_archived', '0');
        }
        $this->setState('filter.show_archived', $showArchived);

        parent::populateState('s.studydate', 'DESC');
    }

    /**
     * Method to get a JDatabaseQuery object for retrieving the data set from a database.
     *
     * @return  DatabaseQuery  A DatabaseQuery object to retrieve the data set.
     *
     * @throws \Exception
     * @since   11.1
     */
    protected function getListQuery(): DatabaseQuery
    {
        $db              = $this->getDatabase();
        $query           = $db->getQuery(true);
        $template_params = Cwmparams::getTemplateparams();
        $registry        = new Registry();
        $registry->loadString($template_params->params);
        $t_params = $registry;

        // Load the parameters. Merge Global and Menu Item params into new object
        $app = Factory::getApplication('site');
        /** @var Registry $params */
        $params = $app->getParams();
        $this->setState('params', $params);
        $menuparams = new Registry();
        $menu       = $app->getMenu()->getActive();

        if ($menu) {
            $menuparams->loadString($menu->params);
        }

        $query->select($db->quoteName('s.id'));
        $query->from($db->quoteName('#__bsms_studies', 's'));
        $query->select(
            $db->quoteName(['t.id', 't.teachername', 't.title', 't.language'], ['tid', 'teachertitle', null, null])
        );
        $query->join('LEFT', $db->quoteName('#__bsms_teachers', 't') . ' ON s.teacher_id = t.id');
        $query->select(
            $db->quoteName(
                ['se.id', 'se.series_text', 'se.description', 'se.series_thumbnail'],
                ['sid', null, 'sdescription', null]
            )
        );
        $query->join('LEFT', $db->quoteName('#__bsms_series', 'se') . ' ON s.series_id = se.id');
        $query->select($db->quoteName(['m.id', 'm.message_type'], ['mid', null]));
        $query->join('LEFT', $db->quoteName('#__bsms_message_type', 'm') . ' ON s.messagetype = m.id');
        $query->select('GROUP_CONCAT(DISTINCT ' . $db->quoteName('st.topic_id') . ')');
        $query->join('LEFT', $db->quoteName('#__bsms_studytopics', 'st') . ' ON s.id = st.study_id');
        $query->select(
            'GROUP_CONCAT(DISTINCT ' . $db->quoteName('tp.id') . '), ' .
            'GROUP_CONCAT(DISTINCT ' . $db->quoteName('tp.topic_text') . ') as topics_text, ' .
            'GROUP_CONCAT(DISTINCT ' . $db->quoteName('tp.params') . ')'
        );
        $query->join('LEFT', $db->quoteName('#__bsms_topics', 'tp') . ' ON tp.id = st.topic_id');
        $query->select($db->quoteName(['l.id', 'l.location_text'], ['lid', null]));
        $query->join('LEFT', $db->quoteName('#__bsms_locations', 'l') . ' ON s.location_id = l.id');

        $rightnow = date('Y-m-d H:i:s');

        // Filter by published state based on show_archived parameter
        $showArchived = $this->getState('filter.show_archived', '0');
        switch ($showArchived) {
            case '1': // Archived only
                $archived = 2;
                $query->where($db->quoteName('s.published') . ' = :published')
                    ->bind(':published', $archived, ParameterType::INTEGER);
                break;
            case '2': // Both published and archived
                $query->whereIn($db->quoteName('s.published'), [1, 2]);
                break;
            default: // Published only (backward compatible)
                $published = 1;
                $query->where($db->quoteName('s.published') . ' = :published')
                    ->bind(':published', $published, ParameterType::INTEGER);
                break;
        }

        $query->where($db->quoteName('s.studydate') . ' <= :rightnow')
            ->bind(':rightnow', $rightnow, ParameterType::STRING);

        // Order by order filter
        $orderparam = $params->get('default_order');

        if (empty($orderparam)) {
            $orderparam = $t_params->get('default_order', '1');
        }

        $order = ($orderparam == 2) ? 'ASC' : 'DESC';

        $orderstate = $this->getState('filter.order');

        if (!empty($orderstate)) {
            $order = $orderstate;
        }

        $query->order($db->quoteName('studydate') . ' ' . $db->escape($order));

        return $query;
    }
}
