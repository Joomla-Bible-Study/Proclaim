<?php
/**
 * Form
 *
 * @package        Proclaim.Admin
 * @copyright  (C) 2007 - 2012 CWM Team All rights reserved
 * @license        http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link           https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

// Create shortcut to parameters.
/** @type Joomla\Registry\Registry $params */
$params = $this->state->get('params');
$params = $params->toArray();
$app    = Factory::getApplication();
$input  = $app->input;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate')
	->addInlineScript('
	Joomla.submitbutton = function (task) {
		if (task == "cwmpodcast.cancel" || document.formvalidator.isValid(document.getElementById("item-form")))
		{
			Joomla.submitform(task, document.getElementById("item-form"))
		}
		else
		{
			alert("' . $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED')) . '")
		}
	}
');
?>

<form action="<?php echo Route::_('index.php?option=com_proclaim&layout=edit&id=' . (int) $this->item->id); ?>"
      method="post" name="adminForm" id="item-form" class="form-validate">

    <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>
    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('JBS_STY_GENERAL')); ?>
		<!-- Begin Content -->
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-12">
                    <?php echo $this->form->renderField('title'); ?>
                    <?php echo $this->form->renderField('description'); ?>
                    <?php echo $this->form->renderField('website'); ?>
                    <?php echo $this->form->renderField('podcastlink'); ?>
                    <?php echo $this->form->renderField('author'); ?>
                    <?php echo $this->form->renderField('editor_name'); ?>
                    <?php echo $this->form->renderField('editor_email'); ?>
                    <?php echo $this->form->renderField('podcastsearch'); ?>
                    <?php echo $this->form->renderField('podcastlanguage'); ?>
                    <?php echo $this->form->renderField('detailstemplateid'); ?>
                    <?php echo $this->form->renderField('podcast_subscribe_show'); ?>
                </div>
            </div>
        </div>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'images', Text::_('JBS_STY_IMAGES')); ?>
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-12">
                    <?php echo $this->form->renderField('image'); ?>
                    <?php echo $this->form->renderField('podcastimage'); ?>
                    <?php echo $this->form->renderField('podcast_image_subscribe'); ?>
                    <?php echo $this->form->renderField('podcast_subscribe_desc'); ?>
                    <?php echo $this->form->renderField('linktype'); ?>
                    <?php echo $this->form->renderField('alternatelink'); ?>
                    <?php echo $this->form->renderField('alternateimage'); ?>
                    <?php echo $this->form->renderField('alternatewords'); ?>
                </div>
            </div>
        </div>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php if ($this->canDo->get('core.admin')): ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'Permissions', Text::_('JBS_CMN_PERMISSIONS')); ?>
        <?php echo $this->form->renderField('rules'); ?>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php endif; ?>
    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('JBS_CMN_DETAILS')); ?>
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-12">
                    <?php echo $this->form->renderField('id'); ?>
                    <?php echo $this->form->renderField('published'); ?>
                    <?php echo $this->form->renderField('filename'); ?>
                    <?php echo $this->form->renderField('podcastlimit'); ?>
                    <?php echo $this->form->renderField('episodetitle'); ?>
                    <?php echo $this->form->renderField('custom'); ?>
                    <?php echo $this->form->renderField('episodesubtitle'); ?>
                    <?php echo $this->form->renderField('customsubtitle'); ?>
                    <?php echo $this->form->renderField('language'); ?>
                    <?php echo $this->form->renderField('linktype'); ?>
                </div>
            </div>
        </div>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>"/>
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
