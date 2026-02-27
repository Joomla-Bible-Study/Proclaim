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

use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Model class for Teachers
 *
 * @package  Proclaim.Site
 * @since    7.0.0
 */
class CwmteachersModel extends ListModel
{
    /**
     * Build an SQL query to load the list data
     *
     * @return  DatabaseQuery A DatabaseQuery object to retrieve the data set.
     *
     * @throws \Exception
     * @since   7.0.0
     */
    protected function getListQuery(): DatabaseQuery
    {
        $db = $this->getDatabase();

        // See if this view is being filtered by language in the menu
        $app  = Factory::getApplication();
        $menu = $app->getMenu();
        $item = $menu->getActive();

        $query = $db->getQuery(true);
        $query->select(
            'teachers.*,CASE WHEN CHAR_LENGTH(teachers.alias) THEN CONCAT_WS(\':\', teachers.id, teachers.alias)'
            . 'ELSE teachers.id END as slug'
        );
        $query->from($db->quoteName('#__bsms_teachers', 'teachers'));
        $query->select($db->quoteName('s.id', 'sid'));
        $query->join('LEFT', $db->quoteName('#__bsms_study_teachers', 'stj') . ' ON '
            . $db->quoteName('teachers.id') . ' = ' . $db->quoteName('stj.teacher_id'));
        $query->join('LEFT', $db->quoteName('#__bsms_studies', 's') . ' ON '
            . $db->quoteName('s.id') . ' = ' . $db->quoteName('stj.study_id'));

        // Filter by language
        if (isset($item->language) && $item->language !== '*') {
            $query->whereIn($db->quoteName('teachers.language'), [$item->language, '*'], ParameterType::STRING);
        } else {
            $allLang = '*';
            $query->where($db->quoteName('teachers.language') . ' = :lang')
                ->bind(':lang', $allLang, ParameterType::STRING);
        }

        $published = 1;
        $listShow  = 1;
        $query->where($db->quoteName('teachers.published') . ' = :published')
            ->where($db->quoteName('teachers.list_show') . ' = :listShow')
            ->bind(':published', $published, ParameterType::INTEGER)
            ->bind(':listShow', $listShow, ParameterType::INTEGER);

        // Filter by view access level
        $user   = $app->getIdentity();
        $groups = $user->getAuthorisedViewLevels();
        $query->whereIn($db->quoteName('teachers.access'), $groups);

        $query->order($db->quoteName('teachers.ordering') . ', ' . $db->quoteName('teachers.teachername') . ' ASC');
        $query->group($db->quoteName('teachers.id'));

        return $query;
    }

    /**
     * Populate the State
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return void
     *
     * @throws \Exception
     * @since 7.0
     */
    protected function populateState($ordering = 'teachers.ordering', $direction = 'asc'): void
    {
        $app = Factory::getApplication();

        // Load state from the request.
        $pk = $app->getInput()->getInt('id', '');
        $this->setState('sermon.id', $pk);

        $offset = $app->getInput()->getInt('limitstart', '');
        $this->setState('list.offset', $offset);

        // Load the parameters.
        $params = $app->getParams();
        $this->setState('params', $params);
        $template = Cwmparams::getTemplateparams();
        $admin    = Cwmparams::getAdmin();

        $template->params->merge($params);
        $template->params->merge($admin->params);
        $params = $template->params;

        $t = (int)$params->get('teachersid');

        if (!$t) {
            $t = $app->getInput()->get('t', 1, 'int');
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

        $this->setState('filter.language', $app->getLanguageFilter());
    }
}
