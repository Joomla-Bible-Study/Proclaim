<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Html\HtmlHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

HtmlHelper::_('behavior.framework', true);
HtmlHelper::_('formbehavior.chosen', 'select');

$input     = Factory::getApplication()->input;
$function  = $input->getCmd('function', 'jSelectServer');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<form
        action="<?php
        echo Route::_(
            'index.php?option=com_proclaim&view=servers&layout=modal&tmpl=component&function=' . $function
        ); ?>"
        method="post" name="adminForm" id="adminForm" class="form-inline">
    <?php
    // Search tools bar
    echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
    ?>
    <table class="table table-striped table-condensed">
        <thead>
        <tr>
            <th>
                <?php
                echo HtmlHelper::_(
                    'searchtools.sort',
                    'JBS_SVR_SERVER_NAME',
                    'mediafile.name',
                    $listDirn,
                    $listOrder
                ); ?>
            </th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <td colspan="1">
                <?php
                echo $this->pagination->getListFooter(); ?>
            </td>
        </tr>
        </tfoot>
        <tbody>
        <?php
        foreach ($this->items as $i => $item) : ?>
            <tr class="row<?php
            echo $i % 2; ?>">
                <td class="center">
                    <a href="javascript:void(0)"
                       onclick="if (window.parent) window.parent.<?php
                        echo $this->escape($function); ?>('<?php
                       echo $item->id; ?>', '<?php
                       echo $item->server_name; ?>', '', null, '<?php
                       echo "index.php?option=com_proclaim&view=server&id=" . $item->id; ?>', '', null);">
                        <?php
                        echo $this->escape($item->server_name); ?>
                    </a>
                </td>
            </tr>
            <?php
        endforeach; ?>
        </tbody>
    </table>

    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="filter_order" value="<?php
    echo $listOrder; ?>"/>
    <input type="hidden" name="filter_order_Dir" value="<?php
    echo $listDirn; ?>"/>
    <?php
    echo HtmlHelper::_('form.token'); ?>
</form>
