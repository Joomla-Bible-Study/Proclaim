<?php

declare(strict_types=1);

/**
 * Not Found layout for the single series podcast view
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

/** @var CWM\Component\Proclaim\Site\View\Cwmseriespodcastdisplay\HtmlView $this */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$t = Factory::getApplication()->getInput()->getInt('t', 1);
?>
<div class="com-proclaim">
    <div class="container-fluid proclaim-main-content" role="main">
        <div class="proclaim-notfound text-center py-5">
            <div class="mb-4">
                <span class="fa fa-search fa-3x text-muted" aria-hidden="true"></span>
            </div>
            <h1 class="mb-4"><?php echo Text::_('JBS_CMN_SERIES_NOT_FOUND'); ?></h1>

            <?php
            // Only strings that already ship in every language are used here.
            // There is no JBS_CMN_SERIES_NOT_FOUND_DESC, and adding one would
            // render as the raw key on the twenty locales that would not have it.
            ?>
            <div class="mt-4">
                <a href="<?php echo Route::_('index.php?option=com_proclaim&view=cwmseriespodcastlist&t=' . $t); ?>"
                   class="btn btn-primary btn-lg">
                    <span class="fa fa-list me-2" aria-hidden="true"></span><?php echo Text::_('JBS_CMN_SERIES_PODCASTS'); ?>
                </a>
            </div>
        </div>
    </div>
</div>
