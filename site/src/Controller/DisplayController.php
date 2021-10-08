<?php
/**
 * @package     Proclaim/Site
 * @subpackage  com_proclaim
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

/**
 * Component Controller
 *
 * @since  1.5
 */
class DisplayController extends BaseController
{
    /**
     * The default view.
     *
     * @var    string
     * @since  1.6
     */
    protected $default_view = 'CWMLandingPage';

    /**
     * Method to display a view.
     *
     * @param   boolean  $cachable   If true, the view output will be cached
     * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link \JFilterInput::clean()}.
     *
     * @return  static  This object to support chaining.
     *
     * @since   1.5
     */
    public function display($cachable = false, $urlparams = array())
    {
	    /*
	   Set the default view name and format from the Request.
	   Note we are using a_id to avoid collisions with the router and the return page.
	   Frontend is a bit messier than the backend.
	   */
	    $id = $this->input->getInt('a_id');
	    $vName = $this->input->getCmd('view', 'CWMLandingPage');
	    $this->input->set('view', $vName);
	    $user = Factory::getUser();

	    /*
		 * This is to check and see if we need to not cache popup pages
		 * With this we Look to see if things are coming from the Landing page using the vairble sendingview"
		 */
	    if ($user->get('id')
		    || ($this->input->getMethod() === 'POST' && strpos($vName, 'form') !== false)
		    || $vName === 'popup' || $vName === 'CWMSermons'
	    ) {
		    $cachable = false;
	    }

	    // Attempt to change mysql for error in large select
	    $db = Factory::getDbo();
	    $db->setQuery('SET SQL_BIG_SELECTS=1');
	    $db->execute();
	    $t = $this->input->get('t', '', 'int');

	    if (!$t)
	    {
		    $t = 1;
	    }

	    $this->input->set('t', $t);

	    $safeurlparams = array(
		    'id'               => 'INT',
		    'cid'              => 'ARRAY',
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
		    'lang'             => 'CMD'
	    );

	    // Check for edit form.
	    if ($vName === 'form' && !$this->checkEditId('com_proclaim.edit.message', $id))
	    {
		    // Somehow the person just went to the form - we don't allow that.
		    throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 403);
	    }

	    parent::display($cachable, $safeurlparams);

	    //return $this;
    	//return parent::display();


    }
}
