<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use CWM\Component\Proclaim\Administrator\Helper\Cwmtranslated;
use Exception;
use JApplicationSite;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\User\User;
use Joomla\Registry\Registry;

/**
 * Model class for Sermon
 *
 * @package  Proclaim.Site
 * @since    7.0.0
 */
class CwmsermonModel extends FormModel
{
    /**
     * Model context string.
     *
     * @var        string
     *
     * @since 7.0
     */
    protected string $context = 'com_proclaim.sermon';

    /**
     * Method to increment the hit counter for the study
     *
     * @param   ?int  $pk  ID
     *
     * @access    public
     * @return    bool    True on success
     *
     * @todo      this look like it could be moved to a helper.
     * @since     1.5
     */
    public function hit(?int $pk = null): bool
    {
        $pk    = $pk ?? (int)$this->getState('study.id');
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->update('#__bsms_studies')->set('hits = hits  + 1')->where('id = ' . (int)$pk);
        $db->setQuery($query);
        $db->execute();

        return true;
    }

    /**
     * Method to get study data.
     *
     * @param   ?int  $pk  The id of the study.
     *
     * @return    mixed    Returns the Sermon Record, false on failure.
     *
     * @throws Exception
     * @since 7.1.0
     */
    public function getItem(?int $pk = null): mixed
    {
        /** @var User $user */
        $user = Factory::getApplication()->getIdentity();

        // Initialise variables.
        $pk = $pk ?? (int)$this->getState('study.id');

        if (!isset($this->_item[$pk])) {
            try {
                $db    = Factory::getContainer()->get('DatabaseDriver');
                $query = $db->getQuery(true);
                $query->select(
                    $this->getState(
                        'item.select',
                        's.*,CASE WHEN CHAR_LENGTH(s.alias) THEN CONCAT_WS(\':\', s.id, s.alias) ELSE s.id END as slug'
                    )
                );
                $query->from('#__bsms_studies AS s');

                // Join over teachers
                $query->select(
                    't.id AS tid, t.teachername AS teachername, t.title AS teachertitle, t.image, t.imagew, t.imageh,' .
                    't.teacher_thumbnail as thumb, t.thumbw, t.thumbh'
                );

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
                $query->select(
                    'group_concat(stp.id separator ", ") AS tp_id, group_concat(stp.topic_text separator ", ")
					 as topic_text, group_concat(stp.params separator ", ") as topic_params'
                );
                $query->join('LEFT', '#__bsms_studytopics as tp on s.id = tp.study_id');
                $query->join('LEFT', '#__bsms_topics as stp on stp.id = tp.topic_id');

                // Join over media files
                $query->select('sum(m.plays) AS totalplays, sum(m.downloads) AS totaldownloads, m.id');
                $query->select('GROUP_CONCAT(DISTINCT m.id) as mids');
                $query->join('LEFT', '#__bsms_mediafiles AS m on s.id = m.study_id');

                if (
                    (!$user->authorise('core.edit.state', 'com_proclaim')) && (!$user->authorise(
                        'core.edit',
                        'com_proclaim'
                    ))
                ) {
                    // Filter by start and end dates.
                    $nullDate = $db->quote($db->getNullDate());
                    $date     = Factory::getDate();

                    $nowDate = $db->quote($date->toSql());

                    $query->where('(s.publish_up = ' . $nullDate . ' OR s.publish_up <= ' . $nowDate . ')')
                        ->where('(s.publish_down = ' . $nullDate . ' OR s.publish_down >= ' . $nowDate . ')');
                }

                // Implement View Level Access
                if (!$user->authorise('core.cwmadmin')) {
                    $groups = implode(',', $user->getAuthorisedViewLevels());
                    $query->where('s.access IN (' . $groups . ')');
                }

                // Filter by published state.
                $published = $this->getState('filter.published');
                $archived  = $this->getState('filter.archived');

                if (is_numeric($published)) {
                    $query->where('(s.published = ' . (int)$published . ' OR s.published =' . (int)$archived . ')');
                }

                $query->group('s.id');
                $query->where('s.id = ' . (int)$pk);
                $db->setQuery($query);
                $data = $db->loadObject();

                if (empty($data)) {
                    Factory::getApplication()->enqueueMessage(Text::_('JBS_CMN_STUDY_NOT_FOUND', 'error'));

                    return $data;
                }

                // Check for published state if filter set.
                if (
                    ((is_numeric($published)) || (is_numeric(
                        $archived
                    ))) && (($data->published != $published) && ($data->published != $archived))
                ) {
                    Factory::getApplication()->enqueueMessage(Text::_('JBS_CMN_ITEM_NOT_PUBLISHED'), 'error');
                    return null;
                }

                // Concat topic_text and concat topic_params do not fit, so translate individually
                $topic_text       = Cwmtranslated::getTopicItemTranslated($data);
                $data->id         = $pk;
                $data->topic_text = $topic_text;

                $registry = new Registry();
                $registry->loadString($data->params);
                $data->params = $registry;
                $template     = Cwmparams::getTemplateparams();

                $data->params->merge($template->params);
                $mparams = clone $this->getState('params');
                $mj      = new Registry();
                $mj->loadString($mparams);
                $data->params->merge($mj);

                $data->admin_params = Cwmparams::getAdmin()->params;

                // Technically guest could edit an article, but lets not check that to improve performance a little.
                if (!$user->get('guest')) {
                    $userId = $user->get('id');
                    $asset  = 'com_proclaim.message.' . $data->id;

                    // Check general edit permission first.
                    if ($user->authorise('core.edit', $asset)) {
                        $data->params->set('access-edit', true);
                    } elseif (!empty($userId) && $user->authorise('core.edit.own', $asset)) {
                        // Check for a valid user and that they are the owner.
                        if ($userId == $data->created_by) {
                            $data->params->set('access-edit', true);
                        }
                    }
                }

                // Compute view access permissions.
                $access = $this->getState('filter.access');

                if ($access) {
                    // If the access filter has been set, we already know this user can view.
                    $data->params->set('access-view', true);
                } else {
                    // If no access filter is set, the layout takes some responsibility for display of limited information.
                    $user   = Factory::getApplication()->getIdentity();
                    $groups = $user->getAuthorisedViewLevels();

                    $data->params->set('access-view', in_array($data->access, $groups, true));
                }

                $this->_item[$pk] = $data;
            } catch (Exception $e) {
                if ((int) $e->getCode() === 404) {
                    // Need to go through the error handler to allow Redirect to work.
                    Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                } else {
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
     * @return  mixed    data object on success, false on failure.
     *
     * @throws Exception
     * @since   7.0
     */
    public function getComments(): array
    {
        $app = Factory::getApplication();
        $id  = $app->input->get('id', '', 'int');

        if (empty($id)) {
            return false;
        }

        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('c.*')->from('#__bsms_comments AS c')->where('c.published = 1')->where(
            'c.study_id = ' . $id
        )->order('c.comment_date asc');
        $db->setQuery($query);

        return $db->loadObjectList();
    }

    /**
     * Method to store a record
     *
     * @access    public
     * @return    bool    True on success
     *
     * @throws Exception
     * @since     7.0
     */
    public function storecomment(): bool
    {
        $row                  = $this->getTable('comment');
        $data                 = $_POST;
        $data['comment_text'] = Factory::getApplication()->input->get('comment_text', '', 'string');

        // Bind the form fields to the table
        $row->bind($data);

        // Make sure the record is valid
        if (!$row->check()) {
            return false;
        }

        // Store the table to the database
        if (!$row->store()) {
            return false;
        }

        return true;
    }

    /**
     * Method for getting a form.
     *
     * @param   array  $data      Data for the form.
     * @param   bool   $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  bool|Form  Will load form if found or return false
     *
     * @throws Exception
     * @since   4.0.0
     *
     */
    public function getForm($data = array(), $loadData = true): bool|Form
    {
        // Get the form.
        $form = $this->loadForm('com_proclaim.comment', 'comment', array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return void
     *
     * @throws Exception
     * @since    1.6
     */
    protected function populateState(): void
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
        $template = Cwmparams::getTemplateparams();
        $admin    = Cwmparams::getAdmin();

        $template->params->merge($params);
        $template->params->merge($admin->params);
        $params = $template->params;

        $t = (int)$params->get('sermonid');

        if (!$t) {
            $t = $app->input->get('t', 1, 'int');
        }

        $template->id = $t;

        $this->setState('template', $template);
        $this->setState('administrator', $admin);

        $user = $app->getSession()->get('user');

        if (
            (!$user->authorise('core.edit.state', 'com_proclaim')) && (!$user->authorise(
                'core.edit',
                'com_proclaim'
            ))
        ) {
            $this->setState('filter.published', 1);
            $this->setState('filter.archived', 2);
        }
    }
}
