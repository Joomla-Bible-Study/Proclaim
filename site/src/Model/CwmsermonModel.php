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
use CWM\Component\Proclaim\Administrator\Helper\Cwmtranslated;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\User\User;
use Joomla\Database\ParameterType;
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
     * @todo      this looks like it could be moved to a helper.
     * @since     1.5
     */
    public function hit(?int $pk = null): bool
    {
        $pk    = $pk ?? (int) $this->getState('study.id');
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);
        $query->update($db->quoteName('#__bsms_studies'))
            ->set($db->quoteName('hits') . ' = ' . $db->quoteName('hits') . ' + 1')
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':id', $pk, ParameterType::INTEGER);
        $db->setQuery($query);
        $db->execute();

        return true;
    }

    /**
     * Method to get study data.
     *
     * @param   ?int  $pk  The ID of the study.
     *
     * @return    mixed    Returns the Sermon Record, false on failure.
     *
     * @throws \Exception
     * @since 7.1.0
     */
    public function getItem(?int $pk = null): mixed
    {
        /** @var User $user */
        $user = Factory::getApplication()->getIdentity();

        // Initialise variables.
        $pk = $pk ?? (int) $this->getState('study.id');

        if (!isset($this->_item[$pk])) {
            try {
                $db    = $this->getDatabase();
                $query = $db->getQuery(true);
                $query->select(
                    $this->getState(
                        'item.select',
                        $db->quoteName('s') . '.*,'
                        . 'CASE WHEN CHAR_LENGTH(' . $db->quoteName('s.alias') . ') THEN CONCAT_WS('
                        . $db->quote(':') . ', ' . $db->quoteName('s.id') . ', ' . $db->quoteName('s.alias')
                        . ') ELSE ' . $db->quoteName('s.id') . ' END AS ' . $db->quoteName('slug')
                    )
                );
                $query->from($db->quoteName('#__bsms_studies', 's'));

                // Join over teachers
                $query->select(
                    $db->quoteName('t.id', 'tid') . ', '
                    . $db->quoteName('t.teachername', 'teachername') . ', '
                    . $db->quoteName('t.title', 'teachertitle') . ', '
                    . $db->quoteName('t.image') . ', ' . $db->quoteName('t.imagew') . ', ' . $db->quoteName('t.imageh') . ', '
                    . $db->quoteName('t.teacher_thumbnail', 'thumb') . ', '
                    . $db->quoteName('t.thumbw') . ', ' . $db->quoteName('t.thumbh')
                );

                $query->join(
                    'LEFT',
                    $db->quoteName('#__bsms_teachers', 't') . ' ON '
                    . $db->quoteName('s.teacher_id') . ' = ' . $db->quoteName('t.id')
                );

                // Join over series
                $query->select(
                    $db->quoteName('se.id', 'sid') . ', ' . $db->quoteName('se.series_text') . ', '
                    . $db->quoteName('se.series_thumbnail') . ', ' . $db->quoteName('se.description', 'sdescription')
                );
                $query->join(
                    'LEFT',
                    $db->quoteName('#__bsms_series', 'se') . ' ON '
                    . $db->quoteName('s.series_id') . ' = ' . $db->quoteName('se.id')
                );

                // Join over message type
                $query->select($db->quoteName('mt.id', 'mid') . ', ' . $db->quoteName('mt.message_type'));
                $query->join(
                    'LEFT',
                    $db->quoteName('#__bsms_message_type', 'mt') . ' ON '
                    . $db->quoteName('s.messagetype') . ' = ' . $db->quoteName('mt.id')
                );

                // Join over books
                $query->select($db->quoteName('b.bookname', 'bookname'));
                $query->join(
                    'LEFT',
                    $db->quoteName('#__bsms_books', 'b') . ' ON '
                    . $db->quoteName('s.booknumber') . ' = ' . $db->quoteName('b.booknumber')
                );

                $query->select($db->quoteName('book2.bookname', 'bookname2'));
                $query->join(
                    'LEFT',
                    $db->quoteName('#__bsms_books', 'book2') . ' ON '
                    . $db->quoteName('book2.booknumber') . ' = ' . $db->quoteName('s.booknumber2')
                );

                // Join over locations
                $query->select($db->quoteName('l.id', 'lid') . ', ' . $db->quoteName('l.location_text'));
                $query->join(
                    'LEFT',
                    $db->quoteName('#__bsms_locations', 'l') . ' ON '
                    . $db->quoteName('s.location_id') . ' = ' . $db->quoteName('l.id')
                );

                // Join over topics
                $query->select(
                    'GROUP_CONCAT(' . $db->quoteName('stp.id') . ' SEPARATOR ", ") AS ' . $db->quoteName('tp_id') . ', '
                    . 'GROUP_CONCAT(' . $db->quoteName('stp.topic_text') . ' SEPARATOR ", ") AS ' . $db->quoteName('topic_text') . ', '
                    . 'GROUP_CONCAT(' . $db->quoteName('stp.params') . ' SEPARATOR ", ") AS ' . $db->quoteName('topic_params')
                );
                $query->join(
                    'LEFT',
                    $db->quoteName('#__bsms_studytopics', 'tp') . ' ON '
                    . $db->quoteName('s.id') . ' = ' . $db->quoteName('tp.study_id')
                );
                $query->join(
                    'LEFT',
                    $db->quoteName('#__bsms_topics', 'stp') . ' ON '
                    . $db->quoteName('stp.id') . ' = ' . $db->quoteName('tp.topic_id')
                );

                // Join over media files
                $query->select(
                    'SUM(' . $db->quoteName('m.plays') . ') AS ' . $db->quoteName('totalplays') . ', '
                    . 'SUM(' . $db->quoteName('m.downloads') . ') AS ' . $db->quoteName('totaldownloads') . ', '
                    . $db->quoteName('m.id')
                );
                $query->select('GROUP_CONCAT(DISTINCT ' . $db->quoteName('m.id') . ') AS ' . $db->quoteName('mids'));
                $query->join(
                    'LEFT',
                    $db->quoteName('#__bsms_mediafiles', 'm') . ' ON '
                    . $db->quoteName('s.id') . ' = ' . $db->quoteName('m.study_id')
                );

                if (
                    (!$user->authorise('core.edit.state', 'com_proclaim')) && (!$user->authorise(
                        'core.edit',
                        'com_proclaim'
                    ))
                ) {
                    // Filter by start and end dates.
                    $nullDate = $db->quote($db->getNullDate());
                    $date     = new Date();

                    $nowDate = $db->quote($date->toSql());

                    $query->where('(' . $db->quoteName('s.publish_up') . ' = ' . $nullDate . ' OR ' . $db->quoteName('s.publish_up') . ' <= ' . $nowDate . ')')
                        ->where('(' . $db->quoteName('s.publish_down') . ' = ' . $nullDate . ' OR ' . $db->quoteName('s.publish_down') . ' >= ' . $nowDate . ')');
                }

                // Implement View Level Access
                if (!$user->authorise('core.cwmadmin')) {
                    $groups = implode(',', $user->getAuthorisedViewLevels());
                    $query->where($db->quoteName('s.access') . ' IN (' . $groups . ')');
                }

                // Filter by published state.
                $published = $this->getState('filter.published');
                $archived  = $this->getState('filter.archived');

                if (is_numeric($published)) {
                    $query->where('(' . $db->quoteName('s.published') . ' = ' . (int) $published . ' OR ' . $db->quoteName('s.published') . ' = ' . (int) $archived . ')');
                }

                $query->group($db->quoteName('s.id'));
                $query->where($db->quoteName('s.id') . ' = ' . (int) $pk);
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
                if (!$user->guest) {
                    $userId = $user->id;
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

                    $data->params->set('access-view', \in_array($data->access, $groups, true));
                }

                $this->_item[$pk] = $data;
            } catch (\Exception $e) {
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
     * @return  mixed  data object on success, false on failure.
     *
     * @throws \Exception
     * @since   7.0
     */
    public function getComments(): array
    {
        $app = Factory::getApplication();
        $id  = $app->input->get('id', 0, 'int');

        if (empty($id)) {
            return [];
        }

        $db        = $this->getDatabase();
        $query     = $db->getQuery(true);
        $published = 1;
        $query->select($db->quoteName('c') . '.*')
            ->from($db->quoteName('#__bsms_comments', 'c'))
            ->where($db->quoteName('c.published') . ' = :published')
            ->where($db->quoteName('c.study_id') . ' = :studyId')
            ->bind(':published', $published, ParameterType::INTEGER)
            ->bind(':studyId', $id, ParameterType::INTEGER)
            ->order($db->quoteName('c.comment_date') . ' ASC');
        $db->setQuery($query);

        return $db->loadObjectList();
    }

    /**
     * Method to store a record
     *
     * @access    public
     * @return    bool    True on success
     *
     * @throws \Exception
     * @since     7.0
     */
    public function storecomment(): bool
    {
        $row   = $this->getTable('Cwmcomment');
        $input = Factory::getApplication()->getInput();

        // Build data array from input (not raw $_POST)
        $data = [
            'study_id'     => $input->getInt('study_id', 0),
            'full_name'    => $input->getString('full_name', ''),
            'user_email'   => $input->getString('user_email', ''),
            'comment_text' => $input->get('comment_text', '', 'raw'),
            'comment_date' => $input->getString('comment_date', (new Date())->toSql()),
            'published'    => $input->getInt('published', 1),
            'language'     => $input->getString('language', '*'),
        ];

        // Bind the form fields to the table
        if (!$row->bind($data)) {
            return false;
        }

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
     * @throws \Exception
     * @since   4.0.0
     *
     */
    public function getForm($data = [], $loadData = true): bool|Form
    {
        // Get the form.
        $form = $this->loadForm('com_proclaim.comment', 'comment', ['control' => 'jform', 'load_data' => $loadData]);

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
     * @throws \Exception
     * @since    1.6
     */
    protected function populateState(): void
    {
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

        $user = $app->getIdentity();

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
