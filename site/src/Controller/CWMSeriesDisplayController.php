<?php
/**
 * Controller for Seriesdisplay
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
namespace CWM\Component\Proclaim\Site\Controller;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
// No Direct Access
defined('_JEXEC') or die;

/**
 * Controller for Seriesdisplay
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class CWMSeriesDisplayController extends BaseController
{

    protected $default_view = 'CWMSeriesDisplay';

    public function display($cachable = false, $urlparams = array())
    {
        return parent::display();
    }

}
