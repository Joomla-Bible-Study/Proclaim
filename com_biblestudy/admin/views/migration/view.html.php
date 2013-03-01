<?php
/**
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;


/**
 * View class for Migration
 *
 * @package  BibleStudy.Admin
 * @since    8.0.0
 */
class BiblestudyViewMigration extends JViewLegacy
{
	protected $more;

	protected $percentage;

	protected $callstack;

	protected $i = 0;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @see     fetch()
	 * @since   11.1
	 */
	public function display($tpl = null)
	{
		$model = $this->getModel('migration');

		if (!JFactory::getApplication()->input->get('task'))
		{
			//$model->setRedirect;
			//$model->setState('scanstate', $state);
			//$this->i++;
			//var_dump($this->i);
		}
		$state = $model->getState('scanstate', false);

		$total           = max(1, $model->totalVersions);
		$done            = $model->doneVersions;

		if ($state)
		{
			var_dump($state); var_dump($total); var_dump($done);
			die();
			if ($total > 0)
			{
				$percent = min(max(round(100 * $done / $total), 1), 100);
			}

			$more = true;
		}
		else
		{
			var_dump($state);
			die();
			$percent = 100;
			$more    = false;
		}

		$this->more = & $more;
		$this->setLayout('default');

		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			JHTML::_('behavior.framework');
		}
		else
		{
			JHTML::_('behavior.mootools');
		}

		$this->percentage = & $percent;

		if ($more)
		{
			$script = "window.addEvent( 'domready' ,  function() {\n";
			$script .= "document.forms.adminForm.submit();\n";
			$script .= "});\n";
			JFactory::getDocument()->addScriptDeclaration($script);
		}
		JToolBarHelper::title(JText::_('JBS_CMN_ADMINISTRATION'), 'administration');
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('JBS_TITLE_ADMINISTRATION'));
		parent::display($tpl);
	}
}
