<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
namespace CWM\Component\Proclaim\Site\View\CWMTerms;
// No Direct Access
defined('_JEXEC') or die;

use Joomla\CMS\Document\Document;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use CWM\Component\Proclaim\Administrator\Helper\CWMHelper;
use CWM\Component\Proclaim\Administrator\Helper\CWMParams;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\Factory;

// This is the popup window for the teachings.  We could put anything in this window.
/**
 * View class for Terms
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * Media
	 *
	 * @var Object
	 *
	 * @since 7.0
	 */
	public $media;

	/**
	 * Params
	 *
	 * @var Registry
	 *
	 * @since 7.0
	 */
	protected $params;

	/**
	 * Document
	 *
	 * @var Document
	 *
	 * @since 7.0
	 */
	public $document;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @throws \Exception
	 * @since 7.0.0
	 */
	public function display($tpl = null)
	{
		$input       = Factory::getApplication()->input;
		$mid         = $input->get('mid', '', 'int');
		$compat_mode = $input->get('compat_mode', '0', 'int');

		$template     = CWMParams::getTemplateparams();
		$this->params = $template->params;
		$termstext    = $this->params->get('terms');
		$db           = Factory::getDbo();
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
				echo '<a href="http://www.christianwebministries.org/router.php?file=' .
						CWMHelper::MediaBuildUrl($this->media->spath, $this->media->filename, $this->params)
					. '&size=' . $this->media->size . '">' . Text::_('JBS_CMN_CONTINUE_TO_DOWNLOAD') . '</a>';
			}
			else
			{
				echo '<a href="index.php?option=com_proclaim&task=CWMSermons.download&mid=' . $this->media->id . '">'
					. Text::_('JBS_CMN_CONTINUE_TO_DOWNLOAD') . '</a>';
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
	 *
	 * @throws \Exception
	 * @since 7.0.0
	 */
	protected function _prepareDocument()
	{
		$app   = Factory::getApplication();
		$menus = $app->getMenu();

		$itemparams = ComponentHelper::getParams('com_proclaim');
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
			$this->params->def('page_heading', Text::_('JGLOBAL_ARTICLES'));
		}

		$title = $this->params->get('page_title', '');
		$title .= ' : ' . $this->media->params->get('filename');

		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ((int) $app->get('sitename_pagetitles', 0) === 1)
		{
			$title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ((int) $app->get('sitename_pagetitles', 0) === 2)
		{
			$title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$this->document->setTitle($title);

		// Prepare meta information (under development)
		if ($itemparams->get('metakey'))
		{
			$this->document->setMetaData('keywords', $itemparams->get('metakey'));
		}
		elseif ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetaData('keywords', $this->params->get('menu-meta_keywords'));
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
			$this->document->setMetaData('robots', $this->params->get('robots'));
		}
	}
}
