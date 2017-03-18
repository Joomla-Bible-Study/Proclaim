<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2017 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.joomlabiblestudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Model class for LandingPage
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class BiblestudyModelLandingpage extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see        JController
	 * @since      1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id',
				's.id',
				'language',
				's.language',
			);
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
	 * @since   11.1
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$order = $this->getUserStateFromRequest($this->context . '.filter.order', 'filter_orders');
		$this->setState('filter.order', $order);

		// Load the parameters.
		$template = JBSMParams::getTemplateparams();
		$admin    = JBSMParams::getAdmin();

		$params = $template->params;

		$t = $params->get('sermonsid');

		if (!$t)
		{
			$input = new JInput;
			$t     = $input->get('t', 1, 'int');
		}

		$template->id = $t;

		$this->setState('template', $template);
		$this->setState('admin', $admin);

		parent::populateState('s.studydate', 'DESC');
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * @return  JDatabaseQuery   A JDatabaseQuery object to retrieve the data set.
	 *
	 * @since   11.1
	 */
	protected function getListQuery()
	{
		$db              = $this->getDbo();
		$query           = $db->getQuery(true);
		$template_params = JBSMParams::getTemplateparams();
		$registry        = new Registry;
		$registry->loadString($template_params->params);
		$t_params = $registry;

		// Load the parameters. Merge Global and Menu Item params into new object
		/** @type JApplicationSite $app */
		$app        = JFactory::getApplication('site');
		/** @var Registry $params */
		$params     = $app->getParams();
		$this->setState('params', $params);
		$menuparams = new Registry;
		$menu       = $app->getMenu()->getActive();

		if ($menu)
		{
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
		$query->where("date_format(s.studydate, %Y-%m-%d %T') <= " . (int) $rightnow);

		// Order by order filter
		$orderparam = $params->get('default_order');

		if (empty($orderparam))
		{
			$orderparam = $t_params->get('default_order', '1');
		}

		if ($orderparam == 2)
		{
			$order = "ASC";
		}
		else
		{
			$order = "DESC";
		}

		$orderstate = $this->getState('filter.order');

		if (!empty($orderstate))
		{
			$order = $orderstate;
		}

		$query->order('studydate ' . $order);

		return $query;
	}
}
