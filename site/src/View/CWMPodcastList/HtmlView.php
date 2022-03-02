<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2016 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.christianwebministries.org
 * */
namespace CWM\Component\Proclaim\Site\View\CWMPodcastList;
// No Direct Access
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Html\HTMLHelper;
use CWM\Component\Proclaim\Site\Helper\CWMImages;
use Joomla\CMS\Uri\Uri;
use CWM\Component\Proclaim\Administrator\Helper\CWMProclaimHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Router\Route;

/**
 * View class for Messages
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class HtmlView extends BaseHtmlView
{
	protected $state;

	protected $items;

	protected $template;

	/** @var  Registry */
	protected $params;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	public function display($tpl = null)
	{
		$this->state      = $this->get('State');
		$this->items      = $this->get('items');
		$this->pagination = $this->get('Pagination');

		$this->template   = $this->state->template;
		$this->params     = $this->state->params;

		HtmlHelper::_('proclaim.framework', '', 'modernizr');
		$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
		$wa->useStyle('com_proclaim.biblestudy');
		$wa->useStyle('com_proclaim.podcast');

		$attribs = array(
			'class' => "jbsmimg"
		);

		$this->attribs = $attribs;

		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return void
	 *
	 * @since 7.0
	 */
	protected function _prepareDocument()
	{
		$app     = Factory::getApplication('site');
		$menus   = $app->getMenu()->getActive();
		$this->params->merge($menus->params);

		$title   = null;
	}
}
