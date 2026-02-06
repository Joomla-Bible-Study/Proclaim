<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

/** @var CWM\Component\Proclaim\Site\View\Cwmserverslist\HtmlView $this */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;

HTMLHelper::_('behavior.framework', true);
HTMLHelper::_('formbehavior.chosen', 'select');

$input     = Factory::getApplication()->getInput();
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
    echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]);
?>
    <table class="table table-striped table-condensed">
        <caption class="visually-hidden"><?php echo Text::_('JBS_SVR_SERVER_LIST'); ?></caption>
        <thead>
        <tr>
            <th scope="col">
                <?php
            echo HTMLHelper::_(
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
        foreach ($this->items as $i => $item) :
            $selectFunction = "if (window.parent) window.parent." . $this->escape($function)
                . "('" . $item->id . "', '" . $this->escape($item->server_name) . "', '', null, '"
                . "index.php?option=com_proclaim&view=server&id=" . $item->id . "', '', null);";
            ?>
            <tr class="row<?php echo $i % 2; ?>">
                <td>
                    <a href="#"
                       role="button"
                       class="select-server-link"
                       data-server-id="<?php echo $item->id; ?>"
                       onclick="<?php echo $selectFunction; ?> return false;"
                       onkeydown="if(event.key === 'Enter' || event.key === ' ') { event.preventDefault(); <?php echo $selectFunction; ?> }">
                        <?php echo $this->escape($item->server_name); ?>
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
    echo HTMLHelper::_('form.token'); ?>
</form>
