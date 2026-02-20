<?php

/**
 * Teacher detail — simple list sub-template for messages
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * @since      10.1.0
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Language\Text;

/** @var CWM\Component\Proclaim\Site\View\Cwmteacher\HtmlView $this */

$studies = $this->teacherstudies;

if (empty($studies)) {
    return;
}
?>
<div class="proclaim-teacher-list">
    <div class="proclaim-teacher-list-header d-none d-md-flex row fw-bold text-body-secondary border-bottom pb-2 mb-2">
        <div class="col-md-2"><?php echo Text::_('JDATE'); ?></div>
        <div class="col-md-5"><?php echo Text::_('JGLOBAL_TITLE'); ?></div>
        <div class="col-md-5"><?php echo Text::_('JBS_CMN_SCRIPTURE'); ?></div>
    </div>
    <?php foreach ($studies as $study) :
        $title     = $study->studytitle ?? '';
        $date      = $study->studydate ?? '';
        $scripture = $study->scripture1 ?? '';
        $link      = $study->detailslink ?? '';
        ?>
        <div class="proclaim-teacher-list-row row py-2 border-bottom align-items-center">
            <div class="col-12 col-md-2 text-body-secondary small">
                <?php echo $date; ?>
            </div>
            <div class="col-12 col-md-5">
                <a href="<?php echo $link; ?>"><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></a>
            </div>
            <div class="col-12 col-md-5 text-body-secondary">
                <?php echo $scripture; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
