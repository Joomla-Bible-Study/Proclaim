<?php

/**
 * Teacher view subset main
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Site\Helper\Cwmlisting;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$listing = new Cwmlisting();
$teacher = new Cwmlisting();
?>
<div class="container">

    <?php
    $teacherdisplay = $teacher->getFluidListing($this->item, $this->params, $this->template, $type = 'teacher');
    echo $teacherdisplay;

    ?>
    <?php
    if ($this->params->get('show_teacher_studies') > 0) {
        ?>
        <div class="row">
            <div class="col-lg-12">
                <?php
                $teacherstudies = $listing->getFluidListing(
                    $this->teacherstudies,
                    $this->params,
                    $this->state->template,
                    $type = 'sermons'
                );
                echo $teacherstudies; ?>
            </div>
        </div>
        <?php
    }
    ?>
    <hr/>

    <div class="row">
        <div class="col-lg-12">
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
