<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.modelitem');

/**
 * Model class for SeriesDisplay
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class BiblestudyModelSeriesdisplay extends JModelItem
{

	/**
	 * Model context string.
	 *
	 * @var        string
	 */
	protected $_context = 'com_biblestudy.seriesdisplay';

	/**
	 * Constructor
	 *
	 * @param   array $config  An array of configuration options (name, state, dbo, table_path, ignore_request).
	 *
	 * @since   11.1
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication('site');

		// Load state from the request.
		$pk = $app->input->get('id', '', 'int');
		$this->setState('series.id', $pk);

		$offset = $app->input->get('limitstart', '', 'int');
		$this->setState('list.offset', $offset);

		// Load the parameters.
		$params   = $app->getParams();
		$template = JBSMParams::getTemplateparams();
		$this->setState('template', $template);

		$params->merge($template->params);
		$this->setState('params', $params);

		$user = JFactory::getUser();

		if ((!$user->authorise('core.edit.state', 'com_biblestudy')) && (!$user->authorise('core.edit', 'com_biblestudy')))
		{
			$this->setState('filter.published', 1);
			$this->setState('filter.archived', 2);
		}
	}

	/**
	 * Method to get study data.
	 *
	 * @param   int $pk  The id of the study.
	 *
	 * @since 7.1.0
	 * @throws Exception
	 *
	 * @return    mixed    Menu item data object on success, false on failure.
	 *
	 * @todo  look are removing this may not used. bcc
	 */
	public function getItem($pk = null)
	{
		// Initialise variables.
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('series.id');

		if (!isset($this->_item[$pk]))
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select(
				$this->getState(
					'item.select',
					'se.*,CASE WHEN CHAR_LENGTH(se.alias) THEN CONCAT_WS(\':\', se.id, se.alias) ELSE se.id END as slug'
				)
			);
			$query->from('#__bsms_series AS se');

			// Join over teachers
			$query->select(
				't.id AS tid, t.teachername, t.title AS teachertitle, t.thumb, t.thumbh, t.thumbw, t.teacher_thumbnail'
			);
			$query->join('LEFT', '#__bsms_teachers as t on se.teacher = t.id');
			$query->where('se.id = ' . (int) $pk);
			$db->setQuery($query);
			$data = $db->loadObject();

			if (empty($data))
			{
				JFactory::getApplication()->enqueueMessage(JText::_('JBS_CMN_SERIES_NOT_FOUND'), 'message');

				return false;
			}

			$this->_item[$pk] = $data;
		}

		return $this->_item[$pk];
	}

    public function getStudies()
    {
        $app = JFactory::getApplication('site');
        $sid = $app->getUserState('sid');

        $params     = $app->getParams();
        $user            = JFactory::getUser();
        $groups          = implode(',', $user->getAuthorisedViewLevels());
        $db              = $this->getDbo();
        $query           = $db->getQuery(true);
        $template_params = JBSMParams::getTemplateparams();
        $t_params        = $template_params->params;
        $query->select(
            $this->getState(
                'list.select', 'study.id, study.published, study.studydate, study.studytitle, study.booknumber, study.chapter_begin,
		                study.verse_begin, study.chapter_end, study.verse_end, study.hits, study.alias, study.studyintro,
		                study.teacher_id, study.secondary_reference, study.booknumber2, study.location_id, study.media_hours, study.media_minutes,
		                study.media_seconds, study.series_id, study.download_id, study.thumbnailm, study.thumbhm, study.thumbwm,
		                study.access, study.user_name, study.user_id, study.studynumber, study.chapter_begin2, study.chapter_end2,
		                study.verse_end2, study.verse_begin2 ') . ','
            . ' CASE WHEN CHAR_LENGTH(study.alias) THEN CONCAT_WS(\':\', study.id, study.alias) ELSE study.id END as slug ');
        $query->from('#__bsms_studies AS study');

        // Join over Message Types
        $query->select('messageType.message_type AS message_type');
        $query->join('LEFT', '#__bsms_message_type AS messageType ON messageType.id = study.messagetype');

        // Join over Teachers
        $query->select('teacher.teachername AS teachername, teacher.title as teachertitle, teacher.teacher_thumbnail as thumb, teacher.thumbh, teacher.thumbw');
        $query->join('LEFT', '#__bsms_teachers AS teacher ON teacher.id = study.teacher_id');

        // Join over Series
        $query->select('series.series_text, series.series_thumbnail, series.description as sdescription, series.access as series_access');
        $query->join('LEFT', '#__bsms_series AS series ON series.id = study.series_id');

        // Join over Books
        $query->select('book.bookname');
        $query->join('LEFT', '#__bsms_books AS book ON book.booknumber = study.booknumber');

        // Join over Plays/Downloads
        $query->select('SUM(mediafile.plays) AS totalplays, SUM(mediafile.downloads) as totaldownloads, mediafile.study_id');
        $query->join('LEFT', '#__bsms_mediafiles AS mediafile ON mediafile.study_id = study.id');

        // Join over Locations
        $query->select('locations.location_text');
        $query->join('LEFT', '#__bsms_locations AS locations ON study.location_id = locations.id');

        // Join over topics
        $query->select('GROUP_CONCAT(DISTINCT st.topic_id)');
        $query->join('LEFT', '#__bsms_studytopics AS st ON study.id = st.study_id');
        $query->select('GROUP_CONCAT(DISTINCT t.id), GROUP_CONCAT(DISTINCT t.topic_text) as topics_text, GROUP_CONCAT(DISTINCT t.params)');
        $query->join('LEFT', '#__bsms_topics AS t ON t.id = st.topic_id');

        // Join over users
        $query->select('users.name as submitted');
        $query->join('LEFT', '#__users as users on study.user_id = users.id');

        $query->group('study.id');

        $query->select('GROUP_CONCAT(DISTINCT m.id) as mids');
        $query->join('LEFT', '#__bsms_mediafiles as m ON study.id = m.study_id');

        // Filter only for authorized view
        $query->where('(series.access IN (' . $groups . ') or study.series_id <= 0)');
        $query->where('study.access IN (' . $groups . ')');

        // Select only published studies
        $query->where('study.published = 1');
        $query->where('study.series_id = '.$sid);
        // Order by order filter
        $orderparam = $params->get('default_order');

        if (empty($orderparam))
        {
            $orderparam = $t_params->get('series_detail_order', '1');
        }
        if ($orderparam == 2)
        {
            $order = "ASC";
        }
        else
        {
            $order = "DESC";
        }


        $query->order('studydate ' . $order);
        $db->setQuery($query, 0, $t_params->get('series_detail_limit', 20));
        $studies = $db->loadObjectList();
        if (count($studies) < 1){return false;}
        return $studies;
    }
}
