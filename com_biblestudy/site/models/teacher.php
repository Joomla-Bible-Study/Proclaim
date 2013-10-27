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
 * Model class for Teacher
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class BiblestudyModelTeacher extends JModelItem
{

	/**
	 *  Model context string.
	 *
	 * @var  string
	 */
	protected $_context = 'com_biblestudy.teacher';

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since    1.6
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication('site');

		// Load state from the request.
		$input = new JInput;
		$pk    = $input->get('id', '', 'int');
		$this->setState('teacher.id', $pk);

		$offset = $input->get('limitstart', '', 'int');
		$this->setState('list.offset', $offset);

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

		// TODO: Tune these values based on other permissions.
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
	 * @return    mixed    Menu item data object on success, false on failure.
	 *
	 * @since 7.1.0
	 */
	public function &getItem($pk = null)
	{
		$app = JFactory::getApplication();

		// Initialise variables.
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('teacher.id');

		if (!isset($this->_item[$pk]))
		{

			try
			{
				$db    = $this->getDbo();
				$query = $db->getQuery(true);
				$query->select($this->getState('item.select', 't.*,CASE WHEN CHAR_LENGTH(t.alias) THEN CONCAT_WS(\':\', t.id, t.alias) ELSE t.id END as slug'));
				$query->from('#__bsms_teachers AS t');
				$query->where('t.id = ' . (int) $pk);
				$db->setQuery($query);
				$data = $db->loadObject();

				if (empty($data))
				{
					$app->enqueueMessage(JText::_('JBS_CMN_TEACHER_NOT_FOUND'), 'error');

					return false;
				}

				$this->_item[$pk] = $data;
			}
			catch (Exception $e)
			{
				if ($e->getCode() == 404)
				{
					// Need to go through the error handler to allow Redirect to work.
					$app->enqueueMessage($e->getMessage(), 'error');
				}
				else
				{
					$this->setError($e);
					$this->_item[$pk] = false;
				}
			}
		}

		return $this->_item[$pk];
	}

    public function getStudies()
    {
        $app = JFactory::getApplication('site');
        $tid = (int) $this->getState('teacher.id');

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
        $query->where('study.teacher_id = '.$tid);
        // Order by order filter
        $orderparam = $params->get('default_order');


        if ($orderparam == 2)
        {
            $order = "ASC";
        }
        else
        {
            $order = "DESC";
        }


        $query->order('studydate ' . $order);
        $db->setQuery($query, 0, $t_params->get('studies', 20));
        $studies = $db->loadObjectList();
        if (count($studies) < 1){return false;}
        return $studies;
    }
// End class
}
