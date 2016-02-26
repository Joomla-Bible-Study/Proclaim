<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2015 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

// This is the popup window for the teachings.  We could put anything in this window.
/**
 * View class for Terms
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class BiblestudyViewTerms extends JViewLegacy
{
	/**
	 * Media
	 *
	 * @var Object
	 */
	public $media;

	/**
	 * Params
	 *
	 * @var Registry
	 */
	protected $params;

	/**
	 * Document
	 *
	 * @var JDocument
	 */
	public $document;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$input       = new JInput;
		$mid         = $input->get('mid', '', 'int');
		$compat_mode = $input->get('compat_mode', '0', 'int');

		$template     = JBSMParams::getTemplateparams();
		$this->params = $template->params;
		$termstext    = $this->params->get('terms');
		$db           = JFactory::getDbo();
		$query        = $db->getQuery('true');
		$query->select('*');
		$query->from('#__bsms_mediafiles');
		$query->where('id= ' . (int) $mid);
		$db->setQuery($query);
		$this->media = $db->loadObject();

		// Params are the individual params for the media file record
		$registory = new Registry;
		$registory->loadString($this->media->params);
		$this->media->params = $registory;
		?>
		<div class="termstext">
			<?php
			echo $termstext;
			?>
		</div>
		<div class="termslink">
			<?php
			if ($compat_mode == 1)
			{
				echo '<a href="http://joomlabiblestudy.org/router.php?file=' . $this->media->spath . $this->media->fpath . $this->media->filename
					. '&size=' . $this->media->size . '">' . JText::_('JBS_CMN_CONTINUE_TO_DOWNLOAD') . '</a>';
			}
			else
			{
				echo '<a href="index.php?option=com_biblestudy&mid=' . $this->media->id . '&view=sermons&task=download">'
					. JText::_('JBS_CMN_CONTINUE_TO_DOWNLOAD') . '</a>';
			}
			?>
		</div>
		<?php

		$this->_prepareDocument();
	}

	/**
	 * Prepares the document;
	 *
	 * @return void
	 */
	protected function _prepareDocument()
	{
		$app   = JFactory::getApplication();
		$menus = $app->getMenu();

		$itemparams = JComponentHelper::getParams('com_biblestudy');
		$title      = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('JGLOBAL_ARTICLES'));
		}
		$title = $this->params->get('page_title', '');
		$title .= ' : ' . $this->media->params->get('filename');

		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}
		$this->document->setTitle($title);

		// Prepare meta information (under development)
		if ($itemparams->get('metakey'))
		{
			$this->document->setMetadata('keywords', $itemparams->get('metakey'));
		}
		elseif ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($itemparams->get('metadesc'))
		{
			$this->document->setDescription($itemparams->get('metadesc'));
		}
		elseif ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}

}
