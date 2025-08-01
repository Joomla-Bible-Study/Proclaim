<?php
/**
 * Default
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Lib\Cwmstats;
use Joomla\CMS\Button\PublishedButton;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns')
    ->useScript('multiselect');

$app       = Factory::getApplication();
$user      = $user = Factory::getApplication()->getSession()->get('user');
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$archived  = $this->state->get('filter.published') == 2 ? true : false;
$trashed   = $this->state->get('filter.published') == -2 ? true : false;
$columns   = 6;

$sortFields = $this->getSortFields();
?>
<form action="<?php
echo Route::_('index.php?option=com_proclaim&view=cwmpodcasts'); ?>" method="post" name="adminForm"
      id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php
                echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
                <?php echo Cwmstats::getPodcastTaskState(); ?>
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
                    <table class="table table-striped adminlist" id="podcasts">
                        <thead>
                        <tr>
                            <th class="w-1 text-center d-none d-md-table-cell">
                                <?php
                                echo HTMLHelper::_('grid.checkall'); ?>
                            </th>
                            <th scope="col" class="w-1 text-center d-none d-md-table-cell">
                                <?php
                                echo HTMLHelper::_(
                                    'searchtools.sort',
                                    'JPUBLISHED',
                                    'podcast.published',
                                    $listDirn,
                                    $listOrder
                                ); ?>
                            </th>
                            <th scope="col" style="min-width:100px">
                                <?php
                                echo HTMLHelper::_(
                                    'searchtools.sort',
                                    'JBS_CMN_PODCAST',
                                    'podcast.title',
                                    $listDirn,
                                    $listOrder
                                ); ?>
                            </th>
                            <th scope="col" class="w-1 text-center d-none d-md-table-cell">
                                <?php
                                echo HTMLHelper::_(
                                    'searchtools.sort',
                                    'JBS_PDC_XML_TTITLE',
                                    'podcast.filename',
                                    $listDirn,
                                    $listOrder
                                ); ?>
                            </th>
                            <th scope="col" class="w-1 text-center d-none d-md-table-cell">
                                <?php
                                echo HTMLHelper::_(
                                    'searchtools.sort',
                                    'JGRID_HEADING_LANGUAGE',
                                    'language',
                                    $listDirn,
                                    $listOrder
                                ); ?>
                            </th>
                            <th scope="col" class="w-1 text-center d-lg-table-cell">
                                <?php
                                echo HTMLHelper::_(
                                    'searchtools.sort',
                                    'JGRID_HEADING_ID',
                                    'podcast.id',
                                    $listDirn,
                                    $listOrder
                                ); ?>
                            </th>
                        </tr>
                        </thead>
                        <tfoot>
                        <tr>
                            <td colspan="<?php
                            echo $columns; ?>">
                            </td>
                        </tr>
                        </tfoot>
                        <tbody>
                        <?php
                        foreach ($this->items as $i => $item) :
                            $item->max_ordering = 0; //??
                            $canCreate = $user->authorise('core.create');
                            $canEdit = $user->authorise('core.edit', 'com_proclaim.podcast.' . $item->id);
                            $canEditOwn = $user->authorise('core.edit.own', 'com_proclaim.podcast.' . $item->id);
                            $canChange = $user->authorise('core.edit.state', 'com_proclaim.podcast.' . $item->id);
                            ?>
                            <tr class="row<?php
                            echo $i % 2; ?>" data-draggable-group="<?php
                            echo '1' ?>">

                                <td class="text-center d-none d-md-table-cell">
                                    <?php
                                    echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                                </td>
                                <td class="text-center d-none d-md-table-cell">
                                    <?php
                                    $options = [
                                        'task_prefix' => 'cwmpodcasts.',
                                        'disabled' => !$canChange,
                                        'id' => 'state-' . $item->id
                                    ];
                                    echo (new PublishedButton())->render((int) $item->published, $i, $options);
                                    ?>
                                </td>
                                <td class="nowrap has-context">
                                    <div class="float-left">

                                        <?php
                                        if ($canEdit || $canEditOwn) : ?>
                                            <a href="<?php
                                            echo Route::_(
                                                'index.php?option=com_proclaim&task=cwmpodcast.edit&id=' . (int)$item->id
                                            ); ?>">
                                                <?php
                                                echo $this->escape($item->title); ?> </a>

                                        <?php
                                        else : ?>
                                            <span
                                                    title="<?php
                                                    echo $this->escape($item->title); ?>"><?php
                                                echo $this->escape($item->title); ?></span>
                                        <?php
                                        endif; ?>
                                    </div>
                                </td>
                                <td class="center nowrap d-none d-md-table-cell">
                                    <a href="<?php
                                    echo Route::_(Uri::root() . $this->escape($item->filename)); ?>"
                                       target="_blank">
                                        <?php
                                        echo Text::_('JBS_PDC_XML'); ?>
                                    </a>
                                </td>
                                <td class="nowrap has-context d-none d-md-table-cell">
                                    <div class="float-left">
                                        <?php
                                        if ($item->language === '*'): ?>
                                            <?php
                                            echo Text::alt('JALL', 'language'); ?>
                                        <?php
                                        else: ?>
                                            <?php
                                            echo $item->language_title ? $this->escape($item->language_title) : Text::_(
                                                'JUNDEFINED'
                                            ); ?>
                                        <?php
                                        endif; ?>
                                    </div>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    <?php
                                    echo (int)$item->id; ?>
                                </td>
                            </tr>
                        <?php
                        endforeach; ?>
                        </tbody>
                    </table>
                <?php
                endif; ?>
                <?php
                echo $this->pagination->getListFooter(); ?>
                <input type="hidden" name="task" value=""/>
                <input type="hidden" name="boxchecked" value="0"/>
                <?php
                echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>
