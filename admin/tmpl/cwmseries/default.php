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

use Joomla\CMS\Button\PublishedButton;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmseries\HtmlView $this */

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('table.columns')
    ->useScript('multiselect');

$app       = Factory::getApplication();
$user      = $this->getCurrentUser();
$userId    = $user->id;
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$orderName = 'series.ordering';
$saveOrder = $listOrder == $orderName;


if (str_contains($listOrder, 'publish_up')) {
    $orderingColumn = 'publish_up';
} elseif (str_contains($listOrder, 'publish_down')) {
    $orderingColumn = 'publish_down';
} elseif (str_contains($listOrder, 'modified')) {
    $orderingColumn = 'modified';
} else {
    $orderingColumn = 'created';
}

?>
<form action="<?php echo Route::_('index.php?option=com_proclaim&view=cwmseries'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php
                echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
                <?php
                if (empty($this->items)) : ?>
                    <?php echo LayoutHelper::render('html.empty_state'); ?>
                <?php else : ?>
                    <table class="table itemList" id="seriesList">
                        <thead>
                        <tr>
                            <th class="w-1 text-center d-none d-md-table-cell">
                                <?php echo HTMLHelper::_('grid.checkall'); ?>
                            </th>
                            <th scope="col" class="w-1 text-center d-none d-md-table-cell">
                                <?php
                                echo HTMLHelper::_(
                                    'searchtools.sort',
                                    'JPUBLISHED',
                                    'series.published',
                                    $listDirn,
                                    $listOrder
                                ); ?>
                            </th>
                            <th scope="col" style="min-width:100px">
                                <?php
                                echo HTMLHelper::_(
                                    'searchtools.sort',
                                    'JBS_CMN_SERIES',
                                    'series.series_text',
                                    $listDirn,
                                    $listOrder
                                ); ?>
                            </th>
                            <th scope="col" class="w-5 text-center d-none d-md-table-cell">
                                <?php
                                echo HTMLHelper::_(
                                    'searchtools.sort',
                                    'JGRID_HEADING_ACCESS',
                                    'series.access',
                                    $listDirn,
                                    $listOrder
                                ); ?>
                            </th>
                            <?php
                            if (Multilanguage::isEnabled()) : ?>
                                <th scope="col" class="w-10 d-none d-md-table-cell">
                                    <?php
                                    echo HTMLHelper::_(
                                        'searchtools.sort',
                                        'JGRID_HEADING_LANGUAGE',
                                        'language',
                                        $listDirn,
                                        $listOrder
                                    ); ?>
                                </th>
                            <?php
                            endif; ?>
                            <th scope="col" class="w-3 d-none d-lg-table-cell">
                                <?php
                                echo HTMLHelper::_(
                                    'searchtools.sort',
                                    'JGRID_HEADING_ID',
                                    'series.id',
                                    $listDirn,
                                    $listOrder
                                ); ?>
                            </th>
                            <th scope="col" class="w-3 d-none d-lg-table-cell">
                                <?php
                                echo HTMLHelper::_(
                                    'searchtools.sort',
                                    'JBS_HEADING_DATE_' . strtoupper($orderingColumn),
                                    'series.created',
                                    $listDirn,
                                    $listOrder
                                ); ?>
                            </th>
                        </tr>
                        </thead>
                        <tbody
                        <?php
                        foreach ($this->items as $i => $item) :
                            $item->max_ordering  = 0;
                            $canCheckin          = $user->authorise('core.manage', 'com_checkin')
                                || $item->checked_out == $userId || \is_null($item->checked_out);
                            $canCreate          = $user->authorise('core.create');
                            $canEdit            = $user->authorise('core.edit', 'com_proclaim.serie.' . $item->id);
                            $canEditOwn         = $user->authorise('core.edit.own', 'com_proclaim.serie.' . $item->id);
                            $canChange          = $user->authorise('core.edit.state', 'com_proclaim.serie.' . $item->id);
                            ?>
                            <tr class="row<?php echo $i % 2; ?>">
                                <td class="center d-none d-md-table-cell">
                                    <?php
                                    echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                                </td>
                                <td class="text-center d-none d-md-table-cell">
                                    <?php
                                    $options = [
                                        'task_prefix' => 'cwmseries.',
                                        'disabled'    => !$canChange,
                                        'id'          => 'state-' . $item->id,
                                    ];
                            echo (new PublishedButton())->render((int) $item->published, $i, $options);
                            ?>
                                </td>
                                <td class="nowrap has-context">
                                    <div class="float-left">
                                        <?php
                                if ($item->language === '*'): ?>
                                            <?php
                                    $language = Text::alt('JALL', 'language'); ?>
                                        <?php else: ?>
                                            <?php
                                    $language = $item->language_title ? $this->escape(
                                        $item->language_title
                                    ) : Text::_('JUNDEFINED'); ?>
                                        <?php
                                        endif; ?>
                                        <?php if ($item->checked_out) : ?>
                                            <?php echo HTMLHelper::_(
                                                'jgrid.checkedout',
                                                $i,
                                                $item->editor,
                                                $item->checked_out_time,
                                                'cwmseries.',
                                                $canCheckin
                                            ); ?>
                                        <?php endif; ?>
                                        <?php
                                                                if ($canEdit || $canEditOwn) : ?>
                                            <a href="<?php
                                                                    echo Route::_(
                                                                        'index.php?option=com_proclaim&task=cwmserie.edit&id=' . (int)$item->id
                                                                    ); ?>"
                                               title="<?php
                                                                                                                               echo Text::_('JACTION_EDIT'); ?>">
                                                <?php
                                                                                                                                echo $this->escape($item->series_text); ?></a>
                                        <?php else : ?>
                                            <span
                                                    title="<?php
                                                                                                                                    echo Text::sprintf(
                                                                                                                                        'JFIELD_ALIAS_LABEL',
                                                                                                                                        $this->escape($item->alias)
                                                                                                                                    ); ?>"><?php
                                                echo $this->escape($item->series_text); ?></span>
                                        <?php
                                        endif; ?>
                                    </div>
                                </td>
                                <td class="small d-none d-md-table-cell">
                                    <?php
                                    echo $this->escape($item->access_level); ?>
                                </td>
                                <?php
                                if (Multilanguage::isEnabled()) : ?>
                                    <td class="small d-none d-md-table-cell">
                                        <?php
                                        echo LayoutHelper::render('joomla.content.language', $item); ?>
                                    </td>
                                <?php
                                endif; ?>
                                <td class="center d-none d-md-table-cell">
                                    <?php
                                    echo (int)$item->id; ?>
                                </td>
                                <td class="small d-none d-md-table-cell text-center">
                                    <?php if ($item->created && $item->created !== '0000-00-00 00:00:00') : ?>
                                        <?php echo HTMLHelper::_('date', $item->created, Text::_('DATE_FORMAT_LC1')); ?>
                                    <?php else : ?>
                                        <?php echo Text::_('JNONE'); // Or just leave blank?>
                                    <?php endif; ?>
                            </tr>
                        <?php
                        endforeach; ?>
                        </tbody>
                    </table>
                    <?php
                    echo $this->pagination->getListFooter(); ?>
                    <?php
                    // Load the batch processing form.?>
                    <?php
                    if ($user->authorise('core.create', 'com_proclaim')
                        && $user->authorise('core.edit', 'com_proclaim')
                        && $user->authorise('core.edit.state', 'com_proclaim')
                    ) : ?>
                        <?php
                        echo HTMLHelper::_(
                            'bootstrap.renderModal',
                            'collapseModal',
                            [
                                                    'title'  => Text::_('JBS_CMN_BATCH_OPTIONS'),
                                                    'footer' => $this->loadTemplate('batch_footer'),
                                                ],
                            $this->loadTemplate('batch_body')
                        ); ?>
                    <?php
                    endif; ?>
                <?php
                endif; ?>
                <?php echo $this->filterForm->renderControlFields(); ?>
            </div>
        </div>
    </div>
</form>
