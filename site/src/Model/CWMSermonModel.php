<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\Model;

// No Direct Access
defined('_JEXEC') or die;

use CWM\Component\Proclaim\Administrator\Helper\CWMParams;
use CWM\Component\Proclaim\Administrator\Helper\CWMTranslated;
use JApplicationSite;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\Registry\Registry;

/**
 * Model class for Sermon
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class CWMSermonModel extends ItemModel
{
	/**
	 * Model context string.
	 *
	 * @var        string
	 *
	 * @since 7.0
	 */
	protected $context = 'com_proclaim.sermon';

	/**
	 * Method to increment the hit counter for the study
	 *
	 * @param   int  $pk  ID
	 *
	 * @access    public
	 * @return    boolean    True on success
	 *
	 * @todo      this look like it could be moved to a helper.
	 * @since     1.5
	 */
	public function hit($pk = null)
	{
		$pk    = (!empty($pk)) ? $pk : (int) $this->getState('study.id');
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query->update('#__bsms_studies')->set('hits = hits  + 1')->where('id = ' . (int) $pk);
		$db->setQuery($query);
		$db->execute();

		return true;
	}

	/**
	 * Method to get study data.
	 *
	 * @param   int  $pk  The id of the study.
	 *
	 * @return    mixed    Menu item data object on success, false on failure.
	 *
	 * @since 7.1.0
	 * @todo  this look like it is not needed. bcc
	 */
	public function &getItem($pk = null)
	{
        $user     = Factory::getApplication()->getIdentity();

		// Initialise variables.
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('study.id');

		if ($this->_item === null)
		{
			$this->_item = array();
		}
        //$input = Factory::getApplication()->input;
       // $mid   = $input->getInt('mid');
       // if ($mid){return $mid;}
		if (!isset($this->_item[$pk]))
		{
			try
			{
				$db    = $this->getDbo();
				$query = $db->getQuery(true);
				$query->select($this->getState('item.select', 's.*,CASE WHEN CHAR_LENGTH(s.alias) THEN CONCAT_WS(\':\', s.id, s.alias) ELSE s.id END as slug'));
				$query->from('#__bsms_studies AS s');

				// Join over teachers
				$query->select('t.id AS tid, t.teachername AS teachername, t.title AS teachertitle, t.image, t.imagew, t.imageh,' .
					't.teacher_thumbnail as thumb, t.thumbw, t.thumbh');

				$query->join('LEFT', '#__bsms_teachers as t on s.teacher_id = t.id');

				// Join over series
				$query->select('se.id AS sid, se.series_text, se.series_thumbnail, se.description as sdescription');
				$query->join('LEFT', '#__bsms_series as se on s.series_id = se.id');

				// Join over message type
				$query->select('mt.id as mid, mt.message_type');
				$query->join('LEFT', '#__bsms_message_type as mt on s.messagetype = mt.id');

				// Join over books
				$query->select('b.bookname as bookname');
				$query->join('LEFT', '#__bsms_books as b on s.booknumber = b.booknumber');

				$query->select('book2.bookname as bookname2');
				$query->join('LEFT', '#__bsms_books AS book2 ON book2.booknumber = s.booknumber2');

				// Join over locations
				$query->select('l.id as lid, l.location_text');
				$query->join('LEFT', '#__bsms_locations as l on s.location_id = l.id');

				// Join over topics
				$query->select('group_concat(stp.id separator ", ") AS tp_id, group_concat(stp.topic_text separator ", ")
					 as topic_text, group_concat(stp.params separator ", ") as topic_params');
				$query->join('LEFT', '#__bsms_studytopics as tp on s.id = tp.study_id');
				$query->join('LEFT', '#__bsms_topics as stp on stp.id = tp.topic_id');

				// Join over media files
				$query->select('sum(m.plays) AS totalplays, sum(m.downloads) AS totaldownloads, m.id');
				$query->select('GROUP_CONCAT(DISTINCT m.id) as mids');
				$query->join('LEFT', '#__bsms_mediafiles AS m on s.id = m.study_id');

				if ((!$user->authorise('core.edit.state', 'com_proclaim')) && (!$user->authorise('core.edit', 'com_proclaim')))
				{
					// Filter by start and end dates.
					$nullDate = $db->quote($db->getNullDate());
					$date     = Factory::getDate();

					$nowDate = $db->quote($date->toSql());

					$query->where('(s.publish_up = ' . $nullDate . ' OR s.publish_up <= ' . $nowDate . ')')
						->where('(s.publish_down = ' . $nullDate . ' OR s.publish_down >= ' . $nowDate . ')');
				}

				// Implement View Level Access
				if (!$user->authorise('core.cwmadmin'))
				{
					$groups = implode(',', $user->getAuthorisedViewLevels());
					$query->where('s.access IN (' . $groups . ')');
				}

				// Filter by published state.
				$published = $this->getState('filter.published');
				$archived  = $this->getState('filter.archived');

				if (is_numeric($published))
				{
					$query->where('(s.published = ' . (int) $published . ' OR s.published =' . (int) $archived . ')');
				}

				$query->group('s.id');
				$query->where('s.id = ' . (int) $pk);
				$db->setQuery($query);
				$data = $db->loadObject();

				if (empty($data))
				{

                    Factory::getApplication()->enqueueMessage(Text::_('JBS_CMN_STUDY_NOT_FOUND', 'error'));

					return $data;
				}

				// Check for published state if filter set.
				if (((is_numeric($published)) || (is_numeric($archived))) && (($data->published != $published) && ($data->published != $archived)))
				{
					Factory::getApplication()->enqueueMessage(Text::_('JBS_CMN_ITEM_NOT_PUBLISHED'), 'error');
					$data = null;

					return $data;
				}

				// Concat topic_text and concat topic_params do not fit, so translate individually
				$topic_text       = CWMTranslated::getTopicItemTranslated($data);
				$data->id         = $pk;
				$data->topic_text = $topic_text;
				$data->bookname   = Text::_($data->bookname);

				$registry = new Registry;
				$registry->loadString($data->params);
				$data->params = $registry;
				$template     = CWMParams::getTemplateparams();

				$data->params->merge($template->params);
				$mparams = clone $this->getState('params');
				$mj      = new Registry;
				$mj->loadString($mparams);
				$data->params->merge($mj);

				$a_params           = CWMParams::getAdmin();
				$data->admin_params = $a_params->params;

				// Technically guest could edit an article, but lets not check that to improve performance a little.
				if (!$user->get('guest'))
				{
					$userId = $user->get('id');
					$asset  = 'com_proclaim.message.' . $data->id;

					// Check general edit permission first.
					if ($user->authorise('core.edit', $asset))
					{
						$data->params->set('access-edit', true);
					}
					// Now check if edit.own is available.
					elseif (!empty($userId) && $user->authorise('core.edit.own', $asset))
					{
						// Check for a valid user and that they are the owner.
						if ($userId == $data->created_by)
						{
							$data->params->set('access-edit', true);
						}
					}
				}

				// Compute view access permissions.
				$access = $this->getState('filter.access');

				if ($access)
				{
					// If the access filter has been set, we already know this user can view.
					$data->params->set('access-view', true);
				}
				else
				{
					// If no access filter is set, the layout takes some responsibility for display of limited information.
					$user   = Factory::getUser();
					$groups = $user->getAuthorisedViewLevels();

					$data->params->set('access-view', in_array($data->access, $groups));
				}

				$this->_item[$pk] = $data;
			}
			catch (Exception $e)
			{
				if ($e->getCode() == 404)
				{
					// Need to go through the error handler to allow Redirect to work.
					Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
				}
				else
				{
					Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
					$this->_item[$pk] = false;
				}
			}
		}

		return $this->_item[$pk];
	}

	/**
	 * Method to retrieve comments for a study
	 *
	 * @access  public
     * @return    mixed    data object on success, false on failure.
	 *
	 * @since   7.0
	 */
	public function getComments()
	{
		$app = Factory::getApplication('site');
		$id  = $app->input->get('id', '', 'int');
        if (empty($id)){return false;}
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query->select('c.*')->from('#__bsms_comments AS c')->where('c.published = 1')->where('c.study_id = ' . $id)->order('c.comment_date asc');
		$db->setQuery($query);
		$comments = $db->loadObjectList();

		return $comments;
	}

	/**
	 * Method to store a record
	 *
	 * @access    public
	 * @return    boolean    True on success
	 *
	 * @since     7.0
	 */
	public function storecomment()
	{
		$row                  = $this->getTable('comment');
		$input                = Factory::getApplication();
		$data                 = $_POST;
		$data['comment_text'] = $input->get('comment_text', '', 'string');

		// Bind the form fields to the table
		$row->bind($data);

		// Make sure the record is valid
		if (!$row->check())
		{
			return false;
		}

		// Store the table to the database
		if (!$row->store())
		{
			return false;
		}

		return true;
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
		/** @type JApplicationSite $app */
		$app = Factory::getApplication('site');

		// Load state from the request.
		$pk = $app->input->get('id', '', 'int');
		$this->setState('study.id', $pk);

		$offset = $app->input->get('limitstart', '', 'int');
		$this->setState('list.offset', $offset);

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);
		$template = CWMParams::getTemplateparams();
		$admin    = CWMParams::getAdmin();

		$template->params->merge($params);
		$template->params->merge($admin->params);
		$params = $template->params;

		$t = $params->get('sermonid');

		if (!$t)
		{
			$input = Factory::getApplication();
			$t     = $input->get('t', 1, 'int');
		}

		$template->id = $t;

		$this->setState('template', $template);
		$this->setState('administrator', $admin);

		$user = Factory::getUser();

		if ((!$user->authorise('core.edit.state', 'com_proclaim')) && (!$user->authorise('core.edit', 'com_proclaim')))
		{
			$this->setState('filter.published', 1);
			$this->setState('filter.archived', 2);
		}
	}
}
