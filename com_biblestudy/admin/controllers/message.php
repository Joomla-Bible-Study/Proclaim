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

jimport('joomla.application.component.controllerform');

/**
 * Controller for Message
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyControllerMessage extends JControllerForm
{

	/**
	 * NOTE: This is needed to prevent Joomla 1.6's pluralization mechanisim from kicking in
	 *
	 * @since 7.0
	 */
	protected $view_list = 'messages';

	/**
	 * Class constructor.
	 *
	 * @param   array $config  A named array of configuration variables.
	 *
	 * @since    7.0.0
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	/**
	 * Reset Hits
	 *
	 * @return void
	 */
	public function resetHits()
	{
		$msg   = null;
		$input = new JInput;
		$id    = $input->get('id', 0, 'int');
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
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
	 * @param   object $model  The model.
	 *
	 * @return  boolean     True if successful, false otherwise and internal error is set.
	 *
	 * @since   1.6
	 */
	public function batch($model = null)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Set the model
		$model = $this->getModel('Message', '', array());

		// Preset the redirect
		$this->setRedirect(JRoute::_('index.php?option=com_biblestudy&view=messages' . $this->getRedirectToListAppend(), false));

		return parent::batch($model);
	}

	/**
	 * Method to save a record.
	 *
	 * @param   string $key     The name of the primary key of the URL variable.
	 * @param   string $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 */
	public function save($key = null, $urlVar = null)
	{
		$model     = $this->getModel('Topic');
		$jinput    = new JInput;
		$data      = $jinput->get('jform');
		$topic_ids = array();

		// Non-numeric topics are assumed to be new and are added to the database
		$topics = explode(',', $data['topics']);

		foreach ($topics as $topic)
		{
			if (!is_numeric($topic) && !empty($topic))
			{
				$model->save(array('topic_text' => $topic, 'language' => $data['language']));
				$topic_ids[] = $model->getState('topic.id');
			}
			else
			{
				$topic_ids[] = $topic;
			}
		}
		$data['topics'] = implode(',', $topic_ids);

		$jinput->set('jform', $data);

		return parent::save();
	}
}
