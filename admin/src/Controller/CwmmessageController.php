<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Model\CwmtopicModel;
use CWM\Component\Proclaim\Administrator\Table\CwmstudytopicsTable;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Table\Table;
use Joomla\Input\Input;

/**
 * Controller for Message
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmmessageController extends FormController
{
	/**
	 * NOTE: This is needed to prevent Joomla 1.6's pluralization mechanisim from kicking in
	 *
	 * @var   string
	 * @since 7.0
	 */
	protected $view_list = 'cwmmessages';

	/**
	 * Method overrides to check if you can edit an existing record.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  boolean
	 *
	 * @throws \Exception
	 * @since   1.6
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;
		$user     = Factory::getApplication()->getIdentity();
		$userId   = $user->get('id');

		// Check general edit permission first.
		if ($user->authorise('core.edit', 'com_proclaim.message.' . $recordId))
		{
			return true;
		}

		// Fallback on edit.own.
		// First test if the permission is available.
		if ($user->authorise('core.edit.own', 'com_proclaim.message.' . $recordId))
		{
			// Now test the owner is the user.
			$ownerId = (int) isset($data['created_by']) ? $data['created_by'] : 0;

			if (empty($ownerId) && $recordId)
			{
				// Need to do a lookup from the model.
				$record = $this->getModel()->getItem($recordId);

				if (empty($record))
				{
					return false;
				}

				$ownerId = $record->created_by;
			}

			// If the owner matches 'me' then do the test.
			if ($ownerId === $userId)
			{
				return true;
			}
		}

		// Since there is no asset tracking, revert to the component permissions.
		return parent::allowEdit($data, $key);
	}

	/**
	 * Reset Hits
	 *
	 * @return void
	 *
	 * @since 1.5
	 */
	public function resetHits()
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$msg   = null;
		$input = new Input;
		$id    = $input->get('id', 0, 'int');
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query->update('#__bsms_studies')
			->set('hits = ' . $db->q('0'))
			->where(' id = ' . (int) $id);
		$db->setQuery($query);

		if (!$db->execute())
		{
			$msg = Text::_('JBS_CMN_ERROR_RESETTING_HITS');
			$this->setRedirect('index.php?option=com_proclaim&view=cwmmessage&controller=administrator&layout=form&cid[]=' . $id, $msg);
		}
		else
		{
			$updated = $db->getAffectedRows();
			$msg     = Text::_('JBS_CMN_RESET_SUCCESSFUL') . ' ' . $updated . ' ' . Text::_('JBS_CMN_ROWS_RESET');
			$this->setRedirect('index.php?option=com_proclaim&view=cwmmessage&controller=message&layout=form&cid[]=' . $id, $msg);
		}
	}

	/**
	 * Method to run batch operations.
	 *
	 * @param   BaseDatabaseModel  $model  The model.
	 *
	 * @return  boolean     True if successful, false otherwise and internal error is set.
	 *
	 * @since   1.6
	 */
	public function batch($model = null)
	{
		// Preset the redirect
		$this->setRedirect(Route::_('index.php?option=com_proclaim&view=cwmmessages' . $this->getRedirectToListAppend(), false));

		return parent::batch($this->getModel());
	}

	/**
	 * Method to save a record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @throws \Exception
	 * @since 1.5
	 */
	public function save($key = null, $urlVar = null)
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$model = new CwmtopicModel;
		$app   = Factory::getApplication();
		$data  = $this->input->post->get('jform', array(), 'array');

		// Get Tags
		$vTags = $data['topics'];
		$iTags = explode(",", $vTags);

		// Remove Exerting StudyTopics tags
		$db = Factory::getContainer()->get('DatabaseDriver');
		$qurey = $db->getQuery(true);
		$qurey->delete('#__bsms_studytopics')
			->where('study_id =' . $data['id']);
		$db->setQuery($qurey);

		if (!$db->execute())
		{
			$app->enqueueMessage('error deleting topics', 'error');
		}

		foreach ($iTags as $aTag)
		{
			if (is_numeric($aTag))
			{
				// It's an existing tag.  Add it
				if ($aTag != "")
				{
					$tagRow           = new CwmstudytopicsTable($db);
					$tagRow->study_id = $data['id'];
					$tagRow->topic_id = $aTag;

					if (!$tagRow->store())
					{
						$app->enqueueMessage('Error Storing Tags with Message', 'error');

						return false;
					}
				}
			}
			else
			{
				// It's a new tag.  Gotta insert it into the Topics table.
				if ($aTag != "")
				{
					$model->save(array('topic_text' => $aTag, 'language' => $data['language']));

					// Gotta somehow make sure this isn't a duplicate...
					$tagRow           = new CwmstudytopicsTable($db);
					$tagRow->study_id = $data['id'];
					$tagRow->topic_id = $model->getState('topic.id');

					if (!$tagRow->store())
					{
						$app->enqueueMessage('Error Storing New Tags', 'error');

						return false;
					}
				}
			}
		}

		return parent::save($key, $urlVar);
	}
}
