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
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('com_proclaim.cwmadmin-servers-modal');

$input     = Factory::getApplication()->input;
$function  = $input->getCmd('function', 'jSelectServer');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$onclick   = $this->escape($function);
?>

<div class="container-popup">
    <form action="<?php
    echo Route::_(
        'index.php?option=com_proclaim&view=cwmservers&layout=modal&tmpl=component&function=' . $function . '&' . Session::getFormToken(
        ) . '=1'
    ); ?>"
          method="post" name="adminForm" id="adminForm" class="form-inline">

        <?php
        echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
        <?php
        if (empty($this->items)) : ?>
            <div class="alert alert-info">
                <span class="icon-info-circle" aria-hidden="true"></span><span
                        class="visually-hidden"><?php
                    echo Text::_('INFO'); ?></span>
                <?php
                echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
            </div>
        <?php
        else : ?>
            <table class="table table-sm">
                <caption class="visually-hidden">
                    <?php
                    echo Text::_('COM_PROCLAIM_SERVERS_TABLE_CAPTION'); ?>,
                    <span id="orderedBy"><?php
                        echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
                    <span id="filteredBy"><?php
                        echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
                </caption>
                <thead>
                <tr>
                    <th>
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
                foreach ($this->items as $i => $item): ?>
                    <tr class="row<?php
                    echo $i % 2; ?>">
                        <th scope="row">
                            <?php
                            $attribs = 'data-function="' . $this->escape($onclick) . '"'
                                . ' data-id="' . $item->id . '"'
                                . ' data-title="' . $this->escape($item->server_name) . '"'
                                . ' data-uri="' . $this->escape(
                                    "index.php?option=com_proclaim&view=server&id=" . $item->id
                                ) . '"'
                                . ' data-language="*"';
                            ?>
                            <a class="select-link" href="javascript:void(0)" <?php
                            echo $attribs; ?>>
                                <?php
                                echo $this->escape($item->server_name); ?>
                            </a>
                        </th>
                    </tr>
                <?php
                endforeach; ?>
                </tbody>
            </table>

        <?php
        endif; ?>

        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="filter_order" value="<?php
        echo $listOrder; ?>"/>
        <input type="hidden" name="filter_order_Dir" value="<?php
        echo $listDirn; ?>"/>
        <?php
        echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
