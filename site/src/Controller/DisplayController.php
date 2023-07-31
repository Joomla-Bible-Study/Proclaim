<?php
/**
 * @package     Proclaim/Site
 * @subpackage  com_proclaim
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Site\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/**
 * Component Controller
 *
 * @since  1.5
 */
class DisplayController extends \Joomla\CMS\MVC\Controller\BaseController
{
	/**
	 * Maps Proclaim Views 9.x to Proclaim Views 10x.
	 *
	 * @const array
	 * @since 5.0.0
	 */
	private const VIEW_CASE_MAP = [
		'cwmsermons'        => 'CWMSermons',
		'cwmsermon'         => 'CWMSermon',
		'cwmteachers'       => 'CWMTeachers',
		'cwmteacher'        => 'CWMTeacher',
		'cwmseriesdisplay'  => 'CWMSeriesDisplay',
		'cwmseriesdisplays' => 'CWMSeriesDisplays',
		'cwmcommentform'    => 'CWMCommentForm',
		'cwmcommentlist'    => 'CWMCommentList',
		'cwmlandingpage'    => 'CWMLandingPage',
		'cwmlatest'         => 'CWMLatest',
		'cwmmediafileform'  => 'CWMMediaFileForm',
		'cwmmediafilelist'  => 'CWMMediaFileList',
		'cwmmessageform'    => 'CWMMessageForm',
		'cwmmessagelist'    => 'CWMMessageList',
		'cwmpodcastdisplay' => 'CWMPodcastDisplay',
		'cwmpopup'          => 'CWMPopUp',
		'cwmsqueezebox'     => 'CWMSqueezebox',
		'cwmterms'          => 'CWMTerms',
	];

	/**
	 * @param   array                         $config   An optional associative array of configuration settings.
	 *                                                  Recognized key values include 'name', 'default_task', 'model_path', and
	 *                                                  'view_path' (this list is not meant to be comprehensive).
	 * @param   MVCFactoryInterface|null      $factory  The factory.
	 * @param   CMSApplication|null           $app      The Application for the dispatcher
	 * @param   \Joomla\CMS\Input\Input|null  $input    The Input object for the request
	 *
	 * @throws \Exception
	 * @since   3.0
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
	{
		// Contact frontpage Editor contacts proxying.
		$this->input = Factory::getApplication()->input;

		if ($this->input->get('view') === 'CWMLandingpage' && $this->input->get('layout') === 'modal')
		{
			$config['base_path'] = JPATH_ADMINISTRATOR . '/components';
		}
		// Sermon frontpage Editor article proxying:
		elseif ($this->input->get('view') === 'CWMSermons' && $this->input->get('layout') === 'modal')
		{
			$config['base_path'] = JPATH_ADMINISTRATOR . '/components';
		}

		parent::__construct($config, $factory, $app, $input);
	}

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link \JFilterInput::clean()}.
	 *
	 * @return  static  This object to support chaining.
	 *
	 * @throws \Exception
	 * @since   1.5
	 */
	public function display($cachable = true, $urlparams = array()): DisplayController
	{
		/*
		Set the default view name and format from the Request.
		Note we are using a_id to avoid collisions with the router and the return page.
		Frontend is a bit messier than the backend.
		*/
		$id    = $this->input->getInt('a_id');
		$vName = $this->translateOldViewName($this->input->getCmd('view', 'CWMLandingPage'));
		$this->input->set('view', $vName);

		$user = $this->app->getIdentity();

		if ($user->get('id')
			|| ($this->input->getMethod() === 'POST'
			&& strpos($vName, 'form') !== false)
			|| $vName === 'CWMPopup'
		)
		{
			$cachable = false;
		}

		// Attempt to change mysql for error in large select
		$t = $this->input->get('t', '1', 'int');

		$this->input->set('t', $t);

		$safeurlparams = array(
			'id'               => 'INT',
			'year'             => 'INT',
			'month'            => 'INT',
			'limit'            => 'INT',
			'limitstart'       => 'INT',
			'showall'          => 'INT',
			'return'           => 'BASE64',
			'filter'           => 'STRING',
			'filter_order'     => 'CMD',
			'filter_order_Dir' => 'CMD',
			'filter-search'    => 'STRING',
			'print'            => 'BOOLEAN',
			'lang'             => 'CMD',
			'Itemid'           => 'INT'
		);

		// Check for edit form.
		if ($vName === 'form' && !$this->checkEditId('com_proclaim.edit.message', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			throw new \RuntimeException(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 403);
		}

		parent::display($cachable, $safeurlparams);

		return $this;
	}

	/**
	 * Translates view names from older versions of the component to the ones currently in use.
	 *
	 * @param   string  $oldViewName  Old view name
	 *
	 * @return  string
	 * @since   5.0.0
	 */
	private function translateOldViewName(string $oldViewName): string
	{
		$oldViewName = strtolower($oldViewName);

		return self::VIEW_CASE_MAP[$oldViewName] ?? $oldViewName;
	}
}
