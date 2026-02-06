<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\User\CurrentUserInterface;
use Joomla\CMS\Versioning\VersionableModelTrait;

/**
 * Controller for Archive
 *
 * @since  9.0.1
 */
class CwmarchiveModel extends AdminModel
{
    use VersionableModelTrait;

    /**
     * @var        string    The prefix to use with controller messages.
     * @since    1.6
     */
    protected $text_prefix = 'com_proclaim';

    /**
     * Gets the form from the XML file.
     *
     * @param array $data Data for the form.
     * @param bool $loadData True if the form is to load its own data (default case), false if not.
     *
     * @return  false|Form|CurrentUserInterface  A JForm object on success, false on failure
     *
     * @throws \Exception
     * @since 7.0
     */
    public function getForm($data = [], $loadData = true): bool|CurrentUserInterface|Form
    {
        // Get the form.
        $form = $this->loadForm('com_proclaim.archive', 'archive', ['control' => 'jform', 'load_data' => $loadData]);

        if ($form === null) {
            return false;
        }

        return $form;
    }

    /**
     * Do Archive of Sermons and Media
     *
     * @return string
     *
     * @throws \Exception
     * @since  9.0.1
     */
    public function doArchive(): string
    {
        $db         = Factory::getContainer()->get('DatabaseDriver');
        $query      = $db->getQuery(true);
        $studies    = 0;
        $mediafiles = 0;

        $input = Factory::getApplication()->getInput();

        // Support both form POST (jform array) and AJAX GET (direct params)
        $data = $input->get('jform', [], 'array');

        if (!empty($data['timeframe'])) {
            // Form POST mode
            $timeframe = (int) $data['timeframe'];
            $switch = $data['switch'];
        } else {
            // AJAX GET mode
            $timeframe = $input->getInt('timeframe', 0);
            $switch = $input->getCmd('switch', 'year');
        }

        // Fields to update.
        $fields = [
            $db->qn('published') . ' = ' . $db->q('2'),
        ];

        // Conditions for which records should be updated.
        $conditions = [
            $db->qn('studydate') . ' <= NOW() - INTERVAL ' . $timeframe . ' ' . strtoupper($switch),
        ];

        $query->update($db->quoteName('#__bsms_studies'))->set($fields)->where($conditions);

        $db->setQuery($query);

        if ($db->execute()) {
            $studies = $db->getAffectedRows();
        }

        $query = $db->getQuery(true);

        // Conditions for which records should be updated.
        $conditions = [
            $db->qn('createdate') . ' <= NOW() - INTERVAL ' . $timeframe . ' ' . strtoupper($switch),
        ];

        $query->update($db->quoteName('#__bsms_mediafiles'))->set($fields)->where($conditions);

        $db->setQuery($query);

        if ($db->execute()) {
            $mediafiles = $db->getAffectedRows();
        }

        $frame = $timeframe . ' ' . $switch . 's';

        return Text::sprintf('JBS_ARCHIVE_DB_CHANGE', $studies, $mediafiles, $frame);
    }
}
