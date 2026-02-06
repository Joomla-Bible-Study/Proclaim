<?php

/**
 * Form for exporting and importing template settings and files
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

/** @var CWM\Component\Proclaim\Administrator\View\Cwmtemplates\HtmlView $this */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('table.columns');

// Build the export template dropdown
$templates      = $this->get('templates');
$types          = [HTMLHelper::_('select.option', '0', Text::_('JBS_CMN_SELECT_TEMPLATE'))];
$types          = array_merge($types, $templates);
$templateSelect = HTMLHelper::_(
    'select.genericlist',
    $types,
    'template_export',
    'class="form-select w-auto d-inline-block" ',
    'value',
    'text',
    '0'
);
?>
<form enctype="multipart/form-data" action="<?php
echo Route::_('index.php?option=com_proclaim&view=cwmtemplates'); ?>"
      method="post" name="adminForm" id="adminForm">
    <div id="j-main-container">
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title"><?php echo Text::_('JBS_CMN_EXPORT'); ?></h2>
                        <div class="d-flex align-items-center gap-2">
                            <?php echo $templateSelect; ?>
                            <button type="submit" class="btn btn-primary"
                                    onclick="Joomla.submitbutton('cwmtemplates.templateExport')">
                                <?php echo Text::_('JBS_CMN_SUBMIT'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title"><?php echo Text::_('JBS_CMN_IMPORT'); ?></h2>
                        <div class="d-flex align-items-center gap-2">
                            <input class="form-control w-auto" id="template_import"
                                   name="template_import" type="file" />
                            <button type="submit" class="btn btn-primary"
                                    onclick="Joomla.submitbutton('cwmtemplates.templateImport')">
                                <?php echo Text::_('JBS_CMN_SUBMIT'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" name="task" value=""/>
        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
