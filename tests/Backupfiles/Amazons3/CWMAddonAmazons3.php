<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
namespace Backupfiles\CWMAddonAmazons3;

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use Joomla\Input\Input;

\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects


/**
 * Class JBSServerAmazonS3
 *
 * @package  Proclaim.Admin
 * @since    9.0.0
 */
class CWMAddonAmazons3 extends CWMAddon
{
    public $name = 'amazonS3';

    protected function renderGeneral($media_form, bool $new): string
    {
        // TODO: Implement renderGeneral() method.
        return '';
    }

    protected function render($media_form, bool $new): string
    {
        // TODO: Implement render() method.
        return '';
    }

    protected function upload(Input|array $data): mixed
    {
        // TODO: Implement upload() method.
        return '';
    }
}
