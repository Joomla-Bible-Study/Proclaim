<?php

/**
 * Controller for Seriesdisplay
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\Controller;

use Exception;
use Joomla\CMS\MVC\Controller\BaseController;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Controller for SeriesDisplay
 *
 * @package  Proclaim.Site
 * @since    7.0.0
 */
class CwmseriesdisplayController extends BaseController
{
    /**
     * The default view for the display method.
     *
     * @var    string
     * @since  3.0
     */
    protected $default_view = 'cwmseriesdisplay';

    /**
     * Typical view method for MVC based architecture
     *
     * This function is provided as a default implementation, in most cases
     * you will need to override it in your own controllers.
     *
     * @param   bool   $cachable   If true, the view output will be cached
     * @param   array  $urlparams  An array of safe url parameters and their variable types, for valid values see {@link InputFilter::clean()}.
     *
     * @return  static  A \CwmseriesdisplayController object to support chaining.
     *
     * @throws  Exception
     * @since   3.0
     */
    public function display($cachable = false, $urlparams = array()): CwmseriesdisplayController
    {
        return parent::display();
    }
}
