<?php
/**
 * Podcast Model
 *
 * @package     Proclaim
 * @subpackage  Model.Podcast
 * @copyright   2007-2023 (C) CWM Team All rights reserved
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link        https://www.christianwebministries.org
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Site\Helper\Cwmpodcastsubscribe;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\ModuleHelper;

// Always load Proclaim API if it exists.
$api = JPATH_ADMINISTRATOR . '/components/com_proclaim/api.php';

if (file_exists($api))
{
	require_once $api;
}

$templateparams = null;

if (!ComponentHelper::isEnabled('com_proclaim'))
{
	throw new Exception("Extension Proclaim not present or enabled");
}

$podcast   = new Cwmpodcastsubscribe;
$subscribe = $podcast->buildSubscribeTable($params->get('subscribeintro', 'Our Podcasts'));

// Display the module
require ModuleHelper::getLayoutPath('mod_proclaim_podcast', 'default');
