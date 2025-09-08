<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2025 CWM Team All rights reserved
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
     * @see        JController
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
        $db              = Factory::getContainer()->get('DatabaseDriver');
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

        $query->select("'list.select', 's.id'");
        $query->from('#__bsms_studies as s');
        $query->select('t.id as tid, t.teachername, t.title as teachertitle, t.language');
        $query->join('LEFT', '#__bsms_teachers as t on s.teacher_id = t.id');
        $query->select('se.id as sid, se.series_text, se.description as sdescription, se.series_thumbnail');
        $query->join('LEFT', '#__bsms_series as se on s.series_id = se.id');
        $query->select('m.id as mid, m.message_type');
        $query->join('LEFT', '#__bsms_message_type as m on s.messagetype = m.id');
        $query->select('GROUP_CONCAT(DISTINCT st.topic_id)');
        $query->join('LEFT', '#__bsms_studytopics AS st ON s.id = st.study_id');
        $query->select(
            'GROUP_CONCAT(DISTINCT tp.id), GROUP_CONCAT(DISTINCT tp.topic_text) as topics_text, GROUP_CONCAT(DISTINCT tp.params)'
        );
        $query->join('LEFT', '#__bsms_topics AS tp ON tp.id = st.topic_id');
        $query->select('l.id as lid, l.location_text');
        $query->join('LEFT', '#__bsms_locations as l on s.location_id = l.id');
        $rightnow = date('Y-m-d H:i:s');
        $query->where('s.published = 1');
        $query->where("date_format(s.studydate, %Y-%m-%d %T') <= " . (int)$rightnow);

        // Order by order filter
        $orderparam = $params->get('default_order');

        if (empty($orderparam)) {
            $orderparam = $t_params->get('default_order', '1');
        }

        if ($orderparam == 2) {
            $order = "ASC";
        } else {
            $order = "DESC";
        }

        $orderstate = $this->getState('filter.order');

        if (!empty($orderstate)) {
            $order = $orderstate;
        }

        $query->order('studydate ' . $order);

        return $query;
    }
}
