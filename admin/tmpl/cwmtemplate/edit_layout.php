<?php

/**
 * Layout Editor Tab
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Language\Text;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmtemplate\HtmlView $this */

/**
 * This file provides the layout editor tab content (HTML only).
 *
 * All script options (addScriptOptions) and language strings (Text::script)
 * are registered in edit.php because this file is loaded via AJAX (format=raw)
 * and those calls don't reach the main page context.
 */
?>

<div id="layout-editor-container" data-context="messages">
    <noscript>
        <div class="alert alert-warning">
            <?php echo Text::_('JBS_TPL_LAYOUT_REQUIRES_JS'); ?>
        </div>
    </noscript>
    <!-- Loading placeholder - replaced when Layout Editor initializes -->
    <div id="layout-editor-loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden"><?php echo Text::_('JLIB_HTML_BEHAVIOR_LOADING'); ?></span>
        </div>
        <p class="mt-3 text-muted"><?php echo Text::_('JBS_TPL_LAYOUT_LOADING'); ?></p>
    </div>
</div>

<div class="alert alert-info mt-3">
    <span class="icon-info-circle" aria-hidden="true"></span>
    <?php echo Text::_('JBS_TPL_LAYOUT_CLASSIC_NOTE'); ?>
</div>

<!-- Hidden fields for Landing Page backward compatibility (managed by Layout Editor) -->
<div style="display: none;">
    <?php echo $this->form->renderFieldset('LANDINGPAGE'); ?>
</div>
