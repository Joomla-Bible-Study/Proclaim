<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\Component\Contact\Site\Model\ContactModel;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Class for Teachers Helper
 *
 * @package  Proclaim.Site
 * @since    8.0.0
 */
class Cwmteacher extends Cwmlisting
{
    /**
     * Get Teacher for Fluid layout
     *
     * @param   Registry  $params  Parameters
     *
     * @return array
     *
     * @throws \Exception
     * @since    8.0.0
     */
    public function getTeachersFluid($params): array
    {
        $input      = Factory::getApplication()->getInput();
        $id         = $input->get('id', '', 'int');
        $teachers   = [];
        $teacherIDs = [];
        $t          = $params->get('teachertemplateid');

        if (!$t) {
            $t = $input->get('t', 1, 'int');
        }

        if ($params->get('listteachers', '0')) {
            $teacherIDs = $params->get('listteachers');
        }

        if (!empty($teacherIDs)) {
            $database = Factory::getContainer()->get(DatabaseInterface::class);
            $query    = $database->getQuery(true);
            $query->select('*')
                ->from($database->quoteName('#__bsms_teachers'))
                ->where($database->quoteName('id') . ' IN (' . implode(',', array_map('intval', $teacherIDs)) . ')');
            $database->setQuery($query);
            $results = $database->loadObjectList();

            foreach ($results as $result) {
                // Check to see if com_contact used instead
                if ($result->contact) {
                    $contactmodel = new ContactModel();
                    $contact      = $contactmodel->getItem($pk = $result->contact);

                    // Substitute contact info from com_contacts for duplicate fields
                    $result->title       = $contact->con_position;
                    $result->teachername = $contact->name;
                }

                if ($result->teacher_thumbnail) {
                    $image = $result->teacher_thumbnail;
                } else {
                    $image = $result->teacher_image ?? '';
                }

                if ($result->title) {
                    $teachername = $result->title . ' ' . $result->teachername;
                } else {
                    $teachername = $result->teachername;
                }

                $teachers[] = ['name' => $teachername, 'image' => $image, 't' => $t, 'id' => $result->id];
            }
        }

        return $teachers;
    }
}
