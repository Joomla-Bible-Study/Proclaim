<?php
/**
 * Analytics breadcrumb navigation.
 *
 * @package    Proclaim.Admin
 * @since      10.1.0
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmanalytics\HtmlView $this */

$baseUrl  = 'index.php?option=com_proclaim&view=cwmanalytics';
$presets  = 'preset=' . htmlspecialchars(Factory::getApplication()->getInput()->getString('preset', '30d')) . '&location_id=' . (int) $this->locationId;
?>
<div class="card mb-3">
<div class="card-body py-2">
<nav aria-label="<?php echo Text::_('JBS_ANA_BREADCRUMB'); ?>">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?php echo Route::_($baseUrl . '&' . $presets); ?>">
                <?php echo Text::_('JBS_ANA_ANALYTICS_DASHBOARD'); ?>
            </a>
        </li>

        <?php if ($this->drilldown === 'series' && $this->drilldownId === 0) : ?>
            <li class="breadcrumb-item active" aria-current="page"><?php echo Text::_('JBS_ANA_ALL_SERIES'); ?></li>

        <?php elseif ($this->drilldown === 'series' && $this->drilldownId > 0) : ?>
            <li class="breadcrumb-item">
                <a href="<?php echo Route::_($baseUrl . '&drilldown=series&' . $presets); ?>">
                    <?php echo Text::_('JBS_ANA_ALL_SERIES'); ?>
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <?php echo htmlspecialchars((string) ($this->seriesInfo->title ?? ''), ENT_QUOTES); ?>
            </li>

        <?php elseif ($this->drilldown === 'message') : ?>
            <?php if ($this->studyInfo && $this->studyInfo->series_id) : ?>
                <li class="breadcrumb-item">
                    <a href="<?php echo Route::_($baseUrl . '&drilldown=series&' . $presets); ?>">
                        <?php echo Text::_('JBS_ANA_ALL_SERIES'); ?>
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="<?php echo Route::_($baseUrl . '&drilldown=series&id=' . (int) $this->studyInfo->series_id . '&' . $presets); ?>">
                        <?php echo htmlspecialchars((string) ($this->studyInfo->series_title ?? ''), ENT_QUOTES); ?>
                    </a>
                </li>
            <?php endif; ?>
            <li class="breadcrumb-item active" aria-current="page">
                <?php echo htmlspecialchars((string) ($this->studyInfo->title ?? ''), ENT_QUOTES); ?>
            </li>

        <?php elseif ($this->drilldown === 'media') : ?>
            <li class="breadcrumb-item active" aria-current="page"><?php echo Text::_('JBS_ANA_MEDIA_TYPES'); ?></li>
        <?php endif; ?>
    </ol>
</nav>
</div>
</div>
