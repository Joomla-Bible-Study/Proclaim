<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * Controller for Message
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class BiblestudyControllerMessage extends JControllerForm
{
	/**
	 * NOTE: This is needed to prevent Joomla 1.6's pluralization mechanisim from kicking in
	 *
	 * @var   string
	 * @since 7.0
	 */
	protected $view_list = 'messages';

	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;
		$user     = JFactory::getApplication()->getIdentity();
		$userId   = $user->get('id');

		// Check general edit permission first.
		if ($user->authorise('core.edit', 'com_biblestudy.message.' . $recordId))
		{
			return true;
		}

		// Fallback on edit.own.
		// First test if the permission is available.
		if ($user->authorise('core.edit.own', 'com_biblestudy.message.' . $recordId))
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
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$msg    = null;
		$input  = new Joomla\Input\Input;
		$id     = $input->get('id', 0, 'int');
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);
		$query->update('#__bsms_studies')
			->set('hits = ' . $db->q('0'))
			->where(' id = ' . (int) $id);
		$db->setQuery($query);

		if (!$db->execute())
		{
			$msg = JText::_('JBS_CMN_ERROR_RESETTING_HITS');
			$this->setRedirect('index.php?option=com_biblestudy&view=message&controller=admin&layout=form&cid[]=' . $id, $msg);
		}
		else
		{
			$updated = $db->getAffectedRows();
			$msg     = JText::_('JBS_CMN_RESET_SUCCESSFUL') . ' ' . $updated . ' ' . JText::_('JBS_CMN_ROWS_RESET');
			$this->setRedirect('index.php?option=com_biblestudy&view=message&controller=message&layout=form&cid[]=' . $id, $msg);
		}
	}

	/**
	 * Method to run batch operations.
	 *
	 * @param   JModelLegacy  $model  The model.
	 *
	 * @return  boolean     True if successful, false otherwise and internal error is set.
	 *
	 * @since   1.6
	 */
	public function batch($model = null)
	{
		// Preset the redirect
		$this->setRedirect(JRoute::_('index.php?option=com_biblestudy&view=messages' . $this->getRedirectToListAppend(), false));

		return parent::batch($this->getModel('Message', '', array()));
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel
	 *
	 * @since   12.2
	 */
	public function getModel($name = 'Message', $prefix = 'BibleStudyModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
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
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		/** @var BibleStudyModelTopic $model */
		$model = $this->getModel('Topic');
		$app   = JFactory::getApplication();
		$data  = $this->input->post->get('jform', array(), 'array');

		// Get Tags
		$vTags = $data['topics'];
		$iTags = explode(",", $vTags);
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_biblestudy/tables');

		// Remove Exerting StudyTopics tags
		$db = JFactory::getDbo();
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
					/** @type TableStudyTopics $tagRow */
					$tagRow           = JTable::getInstance('studytopics', 'Table');
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
					/** @type TableStudyTopics $tagRow */
					$tagRow           = JTable::getInstance('studytopics', 'Table');
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
