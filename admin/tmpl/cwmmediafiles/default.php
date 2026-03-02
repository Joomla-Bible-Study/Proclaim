<?php

/**
 * Default
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CwmlangHelper;
use Joomla\CMS\Button\PublishedButton;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmmediafiles\HtmlView $this */

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('table.columns')
    ->useScript('multiselect')
    ->useScript('com_proclaim.media-analytics-modal');

CwmlangHelper::registerAllForJs();

$app       = Factory::getApplication();
$user      = $this->getCurrentUser();
$userId    = $user->id;
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder === 'mediafile.ordering';
$columns   = 9;

if (Multilanguage::isEnabled()) {
    $columns++;
}

if ($saveOrder && !empty($this->items)) {
    $saveOrderingUrl = 'index.php?option=com_proclaim&task=cwmmediafiles.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
    HTMLHelper::_('draggablelist.draggable');
}
?>
<form action="<?php echo Route::_('index.php?option=com_proclaim&view=cwmmediafiles'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
                <?php if (empty($this->items)) : ?>
                    <div class="alert alert-info">
                        <span class="icon-info-circle" aria-hidden="true"></span><span
                                class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                        <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                    </div>
                <?php else : ?>
                    <table class="table" id="mediafileList">
                        <caption class="visually-hidden">
                            <?php echo Text::_('JBS_STY_TABLE_CAPTION'); ?>,
                            <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
                            <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
                        </caption>
                        <thead>
                        <tr>
                            <th scope="col" class="w-1 text-center">
                                <?php echo HTMLHelper::_('grid.checkall'); ?>
                            </th>
                            <th scope="col" class="w-1 text-center d-none d-md-table-cell">
                                <?php echo HTMLHelper::_(
                                    'searchtools.sort',
                                    '',
                                    'mediafile.ordering',
                                    $listDirn,
                                    $listOrder,
                                    null,
                                    'asc',
                                    'JGRID_HEADING_ORDERING',
                                    'icon-menu-2'
                                ); ?>
                            </th>
                            <th scope="col" class="w-1 text-center">
                                <?php echo HTMLHelper::_(
                                    'searchtools.sort',
                                    'JPUBLISHED',
                                    'mediafile.published',
                                    $listDirn,
                                    $listOrder
                                ); ?>
                            </th>
                            <th scope="col" style="min-width:100px">
                                <?php echo Text::_('JBS_MED_RESOURCE_NAME'); ?>
                            </th>
                            <th scope="col">
                                <?php echo HTMLHelper::_(
                                    'searchtools.sort',
                                    'JBS_CMN_STUDY_TITLE',
                                    'study.studytitle',
                                    $listDirn,
                                    $listOrder
                                ); ?>
                            </th>
                            <th scope="col" class="w-10 d-none d-md-table-cell">
                                <?php echo Text::_('JBS_CMN_SERVER'); ?>
                            </th>
                            <th scope="col" class="w-10 d-none d-md-table-cell text-center">
                                <?php echo HTMLHelper::_(
                                    'searchtools.sort',
                                    'JBS_MED_CREATE_DATE',
                                    'mediafile.createdate',
                                    $listDirn,
                                    $listOrder
                                ); ?>
                            </th>
                            <th scope="col" class="w-10 d-none d-md-table-cell text-center">
                                <?php echo Text::_('JBS_MED_MEDIA_FILES_STATS'); ?>
                            </th>
                            <?php if (Multilanguage::isEnabled()) : ?>
                                <th scope="col" class="w-10 d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_(
                                        'searchtools.sort',
                                        'JGRID_HEADING_LANGUAGE',
                                        'mediafile.language',
                                        $listDirn,
                                        $listOrder
                                    ); ?>
                                </th>
                            <?php endif; ?>
                            <th scope="col" class="w-3 d-none d-lg-table-cell">
                                <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'mediafile.id', $listDirn, $listOrder); ?>
                            </th>
                        </tr>
                        </thead>
                        <tfoot>
                        <tr>
                            <td colspan="<?php echo $columns; ?>"></td>
                        </tr>
                        </tfoot>
                        <tbody<?php if ($saveOrder) :
                            ?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="true"<?php
                        endif; ?>>
                        <?php foreach ($this->items as $i => $item) :
                            $item->max_ordering = 0;
                            $canCheckin  = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $userId || \is_null($item->checked_out);
                            $canEdit     = $user->authorise('core.edit', 'com_proclaim.mediafile.' . $item->id);
                            $canEditOwn  = $user->authorise('core.edit.own', 'com_proclaim.mediafile.' . $item->id);
                            $canChange   = $user->authorise('core.edit.state', 'com_proclaim.mediafile.' . $item->id);

                            // Get the resource identifier (filename, URL, etc.)
                            $resourceKey = 'filename';
                            if ($item->serverConfig && isset($item->serverConfig->config->media_resource)) {
                                $resourceKey = $item->serverConfig->config->media_resource->__toString();
                            }
                            $resourceValue = $item->params->get($resourceKey, '');
                            ?>
                            <tr class="row<?php echo $i % 2; ?>" data-draggable-group="<?php echo $item->study_id; ?>">
                                <td class="text-center">
                                    <?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $this->escape($resourceValue ?: $item->id)); ?>
                                </td>
                                <td class="text-center d-none d-md-table-cell">
                                    <?php
                                    $iconClass = '';
                                    if (!$canChange) {
                                        $iconClass = ' inactive';
                                    } elseif (!$saveOrder) {
                                        $iconClass = ' inactive" title="' . HTMLHelper::tooltipText('JORDERINGDISABLED');
                                    }
                                    ?>
                                    <span class="sortable-handler<?php echo $iconClass ?>">
                                        <span class="icon-ellipsis-v" aria-hidden="true"></span>
                                    </span>
                                    <?php if ($canChange && $saveOrder) : ?>
                                        <input type="text" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order hidden"/>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $options = [
                                        'task_prefix' => 'cwmmediafiles.',
                                        'disabled'    => !$canChange,
                                        'id'          => 'state-' . $item->id,
                                    ];
                                    echo (new PublishedButton())->render((int) $item->published, $i, $options);
                                    ?>
                                </td>
                                <td class="nowrap has-context">
                                    <div>
                                        <?php if ($item->checked_out) : ?>
                                            <?php echo HTMLHelper::_(
                                                'jgrid.checkedout',
                                                $i,
                                                $item->editor,
                                                $item->checked_out_time,
                                                'cwmmediafiles.',
                                                $canCheckin
                                            ); ?>
                                        <?php endif; ?>
                                        <?php if ($canEdit || $canEditOwn) : ?>
                                            <a href="<?php echo Route::_(
                                                'index.php?option=com_proclaim&task=cwmmediafile.edit&id=' . (int) $item->id
                                            ); ?>">
                                                <?php echo $this->escape($resourceValue ?: Text::_('JBS_MED_RESOURCE_NAME')); ?>
                                            </a>
                                        <?php else : ?>
                                            <?php echo $this->escape($resourceValue ?: Text::_('JBS_MED_RESOURCE_NAME')); ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="small d-none d-md-table-cell">
                                    <?php echo $this->escape($item->studytitle); ?>
                                </td>
                                <td class="small d-none d-md-table-cell">
                                    <?php if (!empty($item->server_name)) : ?>
                                        <?php echo $this->escape($item->server_name); ?>
                                        <br/>
                                        <span class="badge bg-secondary"><?php echo $this->escape(ucfirst($item->serverType ?? 'legacy')); ?></span>
                                    <?php else : ?>
                                        <span class="text-muted fst-italic"><?php echo Text::_('JNONE'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="small d-none d-md-table-cell text-center">
                                    <?php echo HTMLHelper::_('date', $item->createdate, Text::_('DATE_FORMAT_LC4')); ?>
                                </td>
                                <td class="d-none d-md-table-cell text-center">
                                    <button type="button" class="btn btn-sm btn-info px-2"
                                            data-cwm-analytics-study="<?php echo (int) $item->study_id; ?>"
                                            title="<?php echo $this->escape(
                                                Text::sprintf('JBS_CMN_PLAYS', (int) $item->plays)
                                                . ' | ' . Text::sprintf('JBS_CMN_DOWNLOADS', (int) $item->downloads)
                                            ); ?>">
                                        <i class="icon-eye fs-5" aria-hidden="true"></i>
                                        <span class="visually-hidden"><?php echo Text::_('JBS_CPL_STATISTIC'); ?></span>
                                    </button>
                                </td>
                                <?php if (Multilanguage::isEnabled()) : ?>
                                    <td class="small d-none d-md-table-cell">
                                        <?php echo LayoutHelper::render('joomla.content.language', $item); ?>
                                    </td>
                                <?php endif; ?>
                                <td class="d-none d-lg-table-cell">
                                    <?php echo (int) $item->id; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php echo $this->pagination->getListFooter(); ?>

                    <?php if (
                        $user->authorise('core.create', 'com_proclaim')
                        && $user->authorise('core.edit', 'com_proclaim')
                        && $user->authorise('core.edit.state', 'com_proclaim')
                    ) : ?>
                        <?php echo HTMLHelper::_(
                            'bootstrap.renderModal',
                            'collapseModal',
                            [
                                'title'  => Text::_('JBS_CMN_BATCH_OPTIONS'),
                                'footer' => $this->loadTemplate('batch_footer'),
                            ],
                            $this->loadTemplate('batch_body')
                        ); ?>
                    <?php endif; ?>
                <?php endif; ?>
                <input type="hidden" name="task" value=""/>
                <input type="hidden" name="boxchecked" value="0"/>
                <input type="hidden" name="delete_physical_files" value="1"/>
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>
<?php echo LayoutHelper::render('analytics.modal', null, JPATH_ADMINISTRATOR . '/components/com_proclaim/layouts'); ?>
