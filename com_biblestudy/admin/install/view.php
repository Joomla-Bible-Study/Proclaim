<?php
/**
 * BibleStudy Component
 *
 * @package       BibleStudy.Installer
 *
 * @copyright (C) 2008 - 2014 BibleStudy Team. All rights reserved.
 * @license       http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link          http://www.joomlabiblestudy.org
 **/
defined('_JEXEC') or die ();

/**
 * The HTML Kunena configuration view.
 */
class BibleStudyViewInstall extends JViewLegacy
{
	protected $model = null;

	/**
	 * Method to display the view.
	 *
	 * @param    string $tpl A template file to load.
	 *
	 * @return    mixed    JError object on failure, void on success.
	 * @throws    object    JError
	 * @since    1.6
	 */
	public function display($tpl = null)
	{
		$layout = $this->getLayout();

		if ($layout == 'schema')
		{
			parent::display($tpl);

			return;
		}

		// Load the view data.
		$this->model = $this->get('Model');

		$versions = $this->model->getDetectVersions();
		$version  = reset($versions);
		$this->model->setAction(strtolower($version->action));
		$this->model->setStep(0);

		JRequest::setVar('hidemainmenu', 1);

		// Joomla 2.5 support
		if ($layout == 'default' && !$tpl && version_compare(JVERSION, '3.0', '<'))
		{
			$tpl = 'j25';
		}

		parent::display($tpl);
	}

	/**
	 * Private method to set the toolbar for this view
	 *
	 * @access private
	 *
	 * @return null
	 **/
	function setToolBar()
	{
		// Set the titlebar text
		JToolBarHelper::title('<span>Kunena ' . BibleStudyForum::version() . '</span> ' . JText::_('COM_BIBLESTUDY_INSTALLER'), 'jbsm.png');

	}

	function displaySchema()
	{
		require_once BIBLESTUDY_PATH_ADMIN . '/install/schema.php';
		$schema = new BibleStudyModelSchema ();
		$create = $schema->getCreateSQL();
		echo '<textarea cols="80" rows="50">';
		echo $this->escape($schema->getSchema()->saveXML());
		echo '</textarea>';

		if (BibleStudyForum::isDev())
		{
			echo '<textarea cols="80" rows="20">';

			foreach ($create as $item)
			{
				echo $this->escape($item ['sql']) . "\n\n";
			}

			echo '</textarea>';
		}
	}

	function displaySchemaDiff()
	{
		require_once BIBLESTUDY_PATH_ADMIN . '/install/schema.php';
		$schema = new BibleStudyModelSchema ();
		$diff   = $schema->getDiffSchema();
		$sql    = $schema->getSchemaSQL($diff);
		echo '<textarea cols="80" rows="20">';
		echo $this->escape($diff->saveXML());
		echo '</textarea>';

		if (BibleStudyForum::isDev())
		{
			echo '<textarea cols="80" rows="20">';

			foreach ($sql as $item)
			{
				echo $this->escape($item ['sql']) . "\n\n";
			}

			echo '</textarea>';
		}
	}
}
