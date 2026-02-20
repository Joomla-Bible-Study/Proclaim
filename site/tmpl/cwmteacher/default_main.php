<?php

/**
 * Teacher view subset main
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var CWM\Component\Proclaim\Site\View\Cwmteacher\HtmlView $this */

// Use pre-created listing from HtmlView
$listing = $this->listing;
$teacher = $this->listing;
?>
<div class="container">

    <?php
    $teacherdisplay = $teacher->getFluidListing($this->item, $this->params, $this->template, $type = 'teacher');
    echo $teacherdisplay;

    $studyMode = (int) $this->params->get('show_teacher_studies', 0);

    if ($studyMode > 0 && !empty($this->teacherstudies)) {
        // Section heading
        $label = $this->params->get('label_teacher', Text::_('JBS_TCH_LATEST_MESSAGES'));
        if ($label) {
            echo '<h3 class="mt-4 mb-3">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</h3>';
        }

        switch ($studyMode) {
            case 2:
                // Full configurable grid layout (existing behaviour)
                ?>
                <div class="row">
                    <div class="col-12">
                        <?php
                        echo $listing->getFluidListing(
                            $this->teacherstudies,
                            $this->params,
                            $this->state->template,
                            $type = 'sermons'
                        );
                        ?>
                    </div>
                </div>
                <?php
                break;

            case 3:
                // Card grid mode
                echo $this->loadTemplate('cards');
                break;

            case 4:
                // Simple list mode
                echo $this->loadTemplate('list');
                break;
        }
    }
    ?>
    <hr/>

    <div class="row">
        <div class="col-12">
            <a href="<?php
            echo Route::_('index.php?option=com_proclaim&view=cwmteachers&t=' . $this->template->id) ?>">
                <button class="btn btn-primary"><?php
                echo '&lt;-- ' . Text::_('JBS_TCH_RETURN_TEACHER_LIST'); ?></button>
            </a>
            <?php
            if ($this->params->get('teacherlink', '1') > 0) {
                echo '<a href="' .
                    Route::_(
                        'index.php?option=com_proclaim&view=cwmsermons&filter_teacher=' . (int)$this->item->id . '&t=' . (int)$this->template->id
                    ) .
                    '"><button class="btn btn-primary">' . Text::_(
                        'JBS_TCH_MORE_FROM_THIS_TEACHER'
                    ) . ' --></button></a>';
            }
            ?>
        </div>
    </div>

</div>
