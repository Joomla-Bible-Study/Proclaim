<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

use \Joomla\Registry\Registry;

/**
 * Controller for Templates
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyControllerTemplates extends JControllerAdmin
{
	/**
	 * Proxy for getModel
	 *
	 * @param   string  $name    The name of the model
	 * @param   string  $prefix  The prefix for the PHP class name
	 *
	 * @return JModel
	 *
	 * @since 7.0.0
	 */
	public function getModel($name = 'Template', $prefix = 'BiblestudyModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}

	/**
	 * Import Template
	 *
	 * @return boolean
	 *
	 * @since 8.0
	 */
	public function template_import()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Set Variables.
		$sermonstemplate = null;
		$sermontemplate = null;
		$teacherstemplate = null;
		$teachertemplate = null;
		$seriesdisplaystemplate = null;
		$seriesdisplaytemplate = null;
		$moduletemplate = null;

		/**
		 * Attempt to increase the maximum execution time for php scripts with check for safe_mode.
		 */
		if (!ini_get('safe_mode'))
		{
			set_time_limit(300);
		}

		$input    = new JInputFiles;
		$userfile = $input->get('template_import');
		$app      = JFactory::getApplication();
		$tc       = 0;

		// Make sure that file uploads are enabled in php
		if (!(bool) ini_get('file_uploads'))
		{
			$app->enqueueMessage(JText::_('JBS_CMN_UPLOADS_NOT_ENABLED'), 'warning');
			$this->setRedirect('index.php?option=com_biblestudy&view=templates');
		}

		// If there is no uploaded file, we have a problem...
		if (!is_array($userfile))
		{
			$app->enqueueMessage(JText::_('JBS_CMN_NO_FILE_SELECTED'), 'warning');
			$this->setRedirect('index.php?option=com_biblestudy&view=templates');
		}

		// Check if there was a problem uploading the file.
		if ($userfile['error'] || $userfile['size'] < 1)
		{
			$app->enqueueMessage(JText::_('JBS_CMN_WARN_INSTALL_UPLOAD_ERROR'), 'warning');
			$this->setRedirect('index.php?option=com_biblestudy&view=templates');
		}

		// Build the appropriate paths
		$tmp_dest = JPATH_SITE . '/tmp/' . $userfile['name'];

		$tmp_src = $userfile['tmp_name'];

		// Move uploaded file
		jimport('joomla.filesystem.file');
		move_uploaded_file($tmp_src, $tmp_dest);

		$db = JFactory::getDbo();

		$query   = file_get_contents(JPATH_SITE . '/tmp/' . $userfile['name']);
		$queries = $db->splitSql($query);

		if (count($queries) == 0)
		{
			// No queries to process
			return 0;
		}

		foreach ($queries as $querie)
		{
			$querie = trim($querie);

			if (substr_count($querie, 'INSERT'))
			{
				if ($querie != '' && $querie{0} != '#')
				{
					// Check for duplicate names and change
					if (substr_count($querie, '#__bsms_styles'))
					{
						// Defecating this as we nologer support style in DB
					}
					elseif (substr_count($querie, '#__bsms_templatecode'))
					{
						// Start to insert new Record
						$this->performDB($querie);

						// Get new  record insert to change name
						$query = $db->getQuery(true);
						$query->select('filename, id, type')
							->from('#__bsms_templatecode')
							->order($db->q('id') . ' DESC');
						$db->setQuery($query, 0, 1);
						$data  = $db->loadObject();
						$query = $db->getQuery(true);
						$query->update('#__bsms_styles')
							->set($db->qn('filename') . ' = ' . $db->q($data->filename . '_copy' . $data->id))
							->where($db->qn('id') . ' = ' . (int) $data->id);
						$this->performDB($query);

						$tc++;

						// Store new Recorded so it can be seen.
						JTable::addIncludePath(JPATH_COMPONENT . '/tables');
						$table = JTable::getInstance('Templatecode', 'Table', array('dbo' => $db));

						try
						{
							$table->load($data->id);
							$table->store();
						}
						catch (Exception $e)
						{
							echo 'Caught exception: ', $e->getMessage(), "\n";
						}
					}
					elseif (substr_count($querie, '#__bsms_templates'))
					{
						// Start to insert new Record
						$this->performDB($querie);

						// Get new  record insert to change name
						$query = $db->getQuery(true);
						$query->select('id, title, params')
							->from('#__bsms_templates')
							->order($db->q('id') . ' DESC');
						$db->setQuery($query, 0, 1);
						$data  = $db->loadObject();
						$query = $db->getQuery(true);
						$query->update('#__bsms_templates')
							->set($db->qn('title') . ' = ' . $db->q($data->filename . '_copy' . $data->id))
							->where($db->qn('id') . ' = ' . (int) $data->id);
						$this->performDB($query);
					}
				}
			}
		}

		// Get new  record insert to change name
		$query = $db->getQuery(true);
		$query->select('id, type, filename')
			->from('#__bsms_templatecode')
			->order($db->q('id') . ' DESC');
		$db->setQuery($query, 0, $tc);
		$data = $db->loadObjectList();

		foreach ($data AS $tpcode):
			// Preload variables for templates
			$type = $tpcode->type;

			switch ($type)
			{
				case 1:
					// Sermonlist
					$sermonstemplate = $tpcode->filename;
					break;
				case 2:
					// Sermon
					$sermontemplate = $tpcode->filename;
					break;
				case 3:
					// Teachers
					$teacherstemplate = $tpcode->filename;
					break;
				case 4:
					// Teacher
					$teachertemplate = $tpcode->filename;
					break;
				case 5:
					// Serieslist
					$seriesdisplaystemplate = $tpcode->filename;
					break;
				case 6:
					// Series
					$seriesdisplaytemplate = $tpcode->filename;
					break;
				case 7:
					// Module
					$moduletemplate = $tpcode->filename;
					break;

			}

		endforeach;

		// Get new record insert to change name
		$query = $db->getQuery(true);
		$query->select('id, title, params')
			->from('#__bsms_templates')
			->order('id');
		$db->setQuery($query, 1);
		$data = $db->loadObject();

		// Load Table Data.
		JTable::addIncludePath(JPATH_COMPONENT . '/tables');
		/** @type TableTemplate $table */
		$table = JTable::getInstance('Template', 'Table', array('dbo' => $db));

		try
		{
			$table->load($data->id);
		}
		catch (Exception $e)
		{
			echo 'Caught exception: ', $e->getMessage(), "\n";
		}

		// Need to adjust the params and write back
		$registry = new Registry;
		$registry->loadString($table->params);
		$params = $registry;
		$params->set('sermonstemplate', $sermonstemplate);
		$params->set('sermontemplate', $sermontemplate);
		$params->set('teacherstemplate', $teacherstemplate);
		$params->set('teachertemplate', $teachertemplate);
		$params->set('seriesdisplaystemplate', $seriesdisplaystemplate);
		$params->set('seriesdisplaytemplate', $seriesdisplaytemplate);
		$params->set('moduletemplate', $moduletemplate);

		// Now write the params back into the $table array and store.
		$table->params = (string) $params->toString();

		$table->store();

		$message = JText::_('JBS_TPL_IMPORT_SUCCESS');

		return $this->setRedirect('index.php?option=com_biblestudy&view=templates', $message);
	}

	/**
	 * Perform DB Query
	 *
	 * @param   string  $query  Query
	 *
	 * @return boolean
	 *
	 * @since 8.0
	 */
	private function performDB($query)
	{
		$db = JFactory::getDbo();
		$db->setQuery($query);

		if (!$db->execute())
		{
			return false;
		}

		return true;
	}

	/**
	 * Export the Template
	 *
	 * @return boolean
	 *
	 * @since 8.0
	 */
	public function template_export()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$input          = new JInput;
		$data           = $input->get('template_export');
		$exporttemplate = $data;

		if (!$exporttemplate)
		{
			$message = JText::_('JBS_TPL_NO_FILE_SELECTED');
			$this->setRedirect('index.php?option=com_biblestudy&view=templates', $message);
		}

		jimport('joomla.filesystem.file');
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('t.id, t.type, t.params, t.title, t.text');
		$query->from('#__bsms_templates as t');
		$query->where('t.id = ' . $exporttemplate);
		$db->setQuery($query);
		$result       = $db->loadObject();
		$objects[]    = $this->getExportSetting($result);
		$filecontents = implode(' ', $objects);
		$filename     = $result->title . '.sql';
		$filepath     = JPATH_ROOT . '/tmp/' . $filename;

		if (!JFile::write($filepath, $filecontents))
		{
			return false;
		}

		$xport = new JBSMBackup;
		$xport->output_file($filepath, $filename, 'text/x-sql');
		JFile::delete($filepath);
		$message = JText::_('JBS_TPL_EXPORT_SUCCESS');

		return $this->setRedirect('index.php?option=com_biblestudy&view=templates', $message);
	}

	/**
	 * Get Exported Template Settings
	 *
	 * @param   object  $result  ?
	 *
	 * @return string
	 *
	 * @since 8.0
	 */
	private function getExportSetting($result)
	{
		// Export must be in this order: css, template files, template.
		$registry = new Registry;
		$registry->loadString($result->params);
		$params  = $registry;
		$db      = JFactory::getDbo();
		$objects = '';
		$css     = $params->get('css');
		$css     = substr($css, 0, -4);

		if ($css)
		{
			$objects = "--\n-- CSS Style Code\n--\n";
			$query2  = $db->getQuery(true);
			$query2->select('style.*');
			$query2->from('#__bsms_styles AS style');
			$query2->where('style.filename = "' . $css . '"');
			$db->setQuery($query2);
			$db->execute();
			$cssresult = $db->loadObject();
			$objects .= "\nINSERT INTO #__bsms_styles SET `published` = '1',\n`filename` = " . $db->q($cssresult->filename)
				. ",\n`stylecode` = " . $db->q($cssresult->stylecode) . ";\n";
		}

		// Get the individual template files
		$sermons = $params->get('sermonstemplate');

		if ($sermons)
		{
			$objects .= "\n--\n-- Sermons\n--";
			$objects .= $this->getTemplate($sermons);
		}

		$sermon = $params->get('sermontemplate');

		if ($sermon)
		{
			$objects .= "\n--\n-- Sermon\n--";
			$objects .= $this->getTemplate($sermon);
		}

		$teachers = $params->get('teacherstemplate');

		if ($teachers)
		{
			$objects .= "\n--\n-- Teachers\n--";
			$objects .= $this->getTemplate($teachers);
		}

		$teacher = $params->get('teachertemplate');

		if ($teacher)
		{
			$objects .= "\n--\n-- Teacher\n--";
			$objects .= $this->getTemplate($teacher);
		}

		$seriesdisplays = $params->get('seriesdisplaystemplate');

		if ($seriesdisplays)
		{
			$objects .= "\n--\n-- Seriesdisplays\n--";
			$objects .= $this->getTemplate($seriesdisplays);
		}

		$seriesdisplay = $params->get('seriesdisplaytemplate');

		if ($seriesdisplay)
		{
			$objects .= "\n--\n-- SeriesDisplay\n--";
			$objects .= $this->getTemplate($seriesdisplay);
		}

		$objects .= "\n\n--\n-- Template Table\n--\n";

		// Create the main template insert
		$objects .= "\nINSERT INTO #__bsms_templates SET `type` = " . $db->q($result->type) . ",";
		$objects .= "\n`params` = " . $db->q($result->params) . ",";
		$objects .= "\n`title` = " . $db->q($result->title) . ",";
		$objects .= "\n`text` = " . $db->q($result->text) . ";";

		$objects .= "\n-- --------------------------------------------------------\n\n";

		return $objects;
	}

	/**
	 * Get Template Settings
	 *
	 * @param   array  $template  ?
	 *
	 * @return boolean|string
	 *
	 * @since 8.0
	 */
	public function getTemplate($template)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('tc.id, tc.templatecode,tc.type,tc.filename');
		$query->from('#__bsms_templatecode as tc');
		$query->where('tc.filename ="' . $template . '"');
		$db->setQuery($query);

		if (!$object = $db->loadObject())
		{
			return false;
		}

		$templatereturn = '
                        INSERT INTO `#__bsms_templatecode` SET `type` = "' . $db->escape($object->type) . '",
                        `templatecode` = "' . $db->escape($object->templatecode) . '",
                        `filename`="' . $db->escape($template) . '",
                        `published` = "1";
                        ';

		return $templatereturn;
	}
}
