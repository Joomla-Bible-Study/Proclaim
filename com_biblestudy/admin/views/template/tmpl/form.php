<?php
/**
 * Form
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');

?>
<form enctype="multipart/form-data"
      action="<?php echo JRoute::_('index.php?option=com_biblestudy&layout=form&id=' . (int) $this->item->id); ?>"
      method="post" name="adminForm" id="adminForm">
<?php echo JHtml::_('tabs.start', 'com_biblestudy_template_' . $this->item->id, array('useCookie' => 1)); ?>
<?php echo JHtml::_('tabs.panel', JText::_('JBS_TPL_GENERAL'), 'general'); ?>
<div class="width-100">
    <div class="width-80 fltlft">
        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_TPL_GENERAL'); ?></legend>
            <ul class="adminformlist">
                <li>
					<?php echo $this->form->getLabel('id'); ?>
					<?php echo $this->form->getInput('id'); ?>
                </li>
                <li>
					<?php echo $this->form->getLabel('published'); ?>
					<?php echo $this->form->getInput('published'); ?>
                </li>
                <li>
					<?php echo $this->form->getLabel('title'); ?>
					<?php echo $this->form->getInput('title'); ?>
                </li>
                <li>
					<?php echo $this->form->getLabel('text'); ?>
					<?php echo $this->form->getInput('text'); ?>
                </li>

            </ul>
			<?php foreach ($this->form->getFieldset('TEMPLATES') as $field): ?>
            <li>
				<?php
				echo $field->label;
				echo $field->input;
				?>
            </li>
			<?php endforeach; ?>
			<?php foreach ($this->form->getFieldset('TERMS') as $field): ?>
            <li>
				<?php echo $field->label; ?>
                <div class="clr"></div>
				<?php echo $field->input; ?>
            </li>
			<?php endforeach; ?>
            </ul>
        </fieldset>
    </div>
</div>

<div class="clr"></div>

<?php echo JHtml::_('tabs.panel', JText::_('JBS_CMN_MEDIA'), 'admin-system-defaults'); ?>
<div class="width-100">
    <div class="width-80 fltlft">
        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_CMN_MEDIA'); ?></legend>
            <ul class="adminformlist">
				<?php foreach ($this->form->getFieldset('MEDIA') as $field): ?>
                <li><?php
					echo $field->label;
					echo $field->input;
					?></li>
				<?php endforeach; ?>
            </ul>
        </fieldset>
    </div>
</div>

<div class="clr"></div>
<?php echo JHtml::_('tabs.panel', JText::_('JBS_TPL_LANDING_PAGE'), 'admin-system-defaults'); ?>
<div class="width-100">
    <div class="width-80 fltlft">
        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_TPL_LANDING_PAGE'); ?></legend>
            <ul class="adminformlist">
				<?php foreach ($this->form->getFieldset('LANDINGPAGE') as $field): ?>
                <li><?php
					echo $field->label;
					echo $field->input;
					?></li>
				<?php endforeach; ?>
            </ul>
        </fieldset>
    </div>
</div>

<div class="clr"></div>
<?php echo JHtml::_('tabs.panel', JText::_('JBS_TPL_STUDY_LIST_VIEW'), 'admin-system-defaults'); ?>
<div class="width-100">
    <div class="width-80 fltlft">
        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_TPL_STUDY_LIST_VIEW'); ?></legend>
			<?php echo JHtml::_('sliders.start', 'content-sliders-' . $this->item->id, array('useCookie' => 1)); ?>
			<?php echo JHtml::_('sliders.panel', JText::_('JBS_TPL_VERSES_DATES_CSS'), 'publishing-details'); ?>
            <fieldset class="panelform">
                <legend><?php echo JText::_('JBS_TPL_VERSES_DATES_CSS'); ?></legend>
                <ul class="adminformlist">
					<?php foreach ($this->form->getFieldset('VERSES') as $field): ?>
                    <li><?php
						echo $field->label;
						echo $field->input;
						?></li>
					<?php endforeach; ?>
                </ul>
            </fieldset>
			<?php echo JHtml::_('sliders.panel', JText::_('JBS_TPL_LIST_ITEMS'), 'publishing-details'); ?>
            <fieldset class="panelform">
                <legend><?php echo JText::_('JBS_TPL_LIST_ITEMS'); ?></legend>
                <ul class="adminformlist">
					<?php foreach ($this->form->getFieldset('LISTITEMS') as $field): ?>
					<?php
					$thename = $field->label;
					if (substr_count($thename, 'jform_params_list_intro-lbl'))
					{
						echo '<div class="clr"></div>';
					}
					?>
                    <li><?php
						echo $field->label;
						echo $field->input;
						?></li>
					<?php endforeach; ?>
                </ul>
            </fieldset>
			<?php echo JHtml::_('sliders.panel', JText::_('JBS_TPL_FILTERS'), 'publishing-details'); ?>
            <fieldset class="panelform">
                <legend><?php echo JText::_('JBS_TPL_FILTERS'); ?></legend>
                <ul class="adminformlist">
					<?php foreach ($this->form->getFieldset('FILTERS') as $field): ?>
                    <li><?php
						echo $field->label;
						echo $field->input;
						?></li>
					<?php endforeach; ?>
                </ul>
            </fieldset>
			<?php echo JHtml::_('sliders.panel', JText::_('JBS_TPL_TOOLTIP_ITEMS'), 'publishing-details'); ?>
            <fieldset class="panelform">
                <legend><?php echo JText::_('JBS_TPL_TOOLTIP_ITEMS'); ?></legend>
                <ul class="adminformlist">
					<?php foreach ($this->form->getFieldset('TOOLTIP') as $field): ?>
                    <li><?php
						echo $field->label;
						echo $field->input;
						?></li>
					<?php endforeach; ?>
                </ul>
            </fieldset>
			<?php echo JHtml::_('sliders.panel', JText::_('JBS_TPL_STUDY_LIST_ROW1'), 'publishing-details'); ?>
            <fieldset class="panelform">
                <legend><?php echo JText::_('JBS_TPL_STUDY_LIST_ROW1'); ?></legend>
                <ul class="adminformlist">
					<?php foreach ($this->form->getFieldset('ROW1') as $field): ?>
                    <li><?php
						echo $field->label;
						echo $field->input;
						?></li>
					<?php endforeach; ?>
                </ul>
            </fieldset>
			<?php echo JHtml::_('sliders.panel', JText::_('JBS_TPL_STUDY_LIST_ROW2'), 'publishing-details'); ?>
            <fieldset class="panelform">
                <legend><?php echo JText::_('JBS_TPL_STUDY_LIST_ROW1'); ?></legend>
                <ul class="adminformlist">
					<?php foreach ($this->form->getFieldset('ROW2') as $field): ?>
                    <li><?php
						echo $field->label;
						echo $field->input;
						?></li>
					<?php endforeach; ?>
                </ul>
            </fieldset>
			<?php echo JHtml::_('sliders.panel', JText::_('JBS_TPL_STUDY_LIST_ROW3'), 'publishing-details'); ?>
            <fieldset class="panelform">
                <legend><?php echo JText::_('JBS_TPL_STUDY_LIST_ROW3'); ?></legend>
                <ul class="adminformlist">
					<?php foreach ($this->form->getFieldset('ROW3') as $field): ?>
                    <li><?php
						echo $field->label;
						echo $field->input;
						?></li>
					<?php endforeach; ?>
                </ul>
            </fieldset>
			<?php echo JHtml::_('sliders.panel', JText::_('JBS_TPL_STUDY_LIST_ROW4'), 'publishing-details'); ?>
            <fieldset class="panelform">
                <legend><?php echo JText::_('JBS_TPL_STUDY_LIST_ROW4'); ?></legend>
                <ul class="adminformlist">
					<?php foreach ($this->form->getFieldset('ROW4') as $field): ?>
                    <li><?php
						echo $field->label;
						echo $field->input;
						?></li>
					<?php endforeach; ?>
                </ul>
            </fieldset>
			<?php echo JHtml::_('sliders.panel', JText::_('JBS_TPL_STUDY_LIST_CUSTOM'), 'publishing-details'); ?>
            <fieldset class="panelform">
                <legend><?php echo JText::_('JBS_TPL_STUDY_LIST_CUSTOM'); ?></legend>
                <ul class="adminformlist">
					<?php foreach ($this->form->getFieldset('STUDIESVIEW') as $field): ?>
                    <li><?php
						echo $field->label;
						echo $field->input;
						?></li>
					<?php endforeach; ?>
                </ul>
            </fieldset>
			<?php echo JHtml::_('sliders.end'); ?>
        </fieldset>
    </div>
</div>

<div class="clr"></div>
<?php echo JHtml::_('tabs.panel', JText::_('JBS_TPL_STUDY_DETAILS_VIEW'), 'admin-system-defaults'); ?>
<div class="width-100">
    <div class="width-80 fltlft">
        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_TPL_STUDY_DETAILS_VIEW'); ?></legend>
			<?php echo JHtml::_('sliders.start', 'content-sliders-' . $this->item->id, array('useCookie' => 1)); ?>
			<?php echo JHtml::_('sliders.panel', JText::_('JBS_TPL_DETAILS_VIEW'), 'publishing-details'); ?>
            <fieldset class="panelform">
                <legend><?php echo JText::_('JBS_TPL_DETAILS_VIEW'); ?></legend>
                <ul class="adminformlist">
					<?php foreach ($this->form->getFieldset('DETAILS') as $field): ?>
                    <li><?php
						echo $field->label;
						echo $field->input;
						?></li>
					<?php endforeach; ?>
                </ul>
            </fieldset>
			<?php echo JHtml::_('sliders.panel', JText::_('JBS_TPL_DETAILS_LIST_ROW1'), 'publishing-details'); ?>
            <fieldset class="panelform">
                <legend><?php echo JText::_('JBS_TPL_DETAILS_LIST_ROW1'); ?></legend>
                <ul class="adminformlist">
					<?php foreach ($this->form->getFieldset('DETAILSROW1') as $field): ?>
                    <li><?php
						echo $field->label;
						echo $field->input;
						?></li>
					<?php endforeach; ?>
                </ul>
            </fieldset>
			<?php echo JHtml::_('sliders.panel', JText::_('JBS_TPL_DETAILS_LIST_ROW2'), 'publishing-details'); ?>
            <fieldset class="panelform">
                <legend><?php echo JText::_('JBS_TPL_DETAILS_LIST_ROW2'); ?></legend>
                <ul class="adminformlist">
					<?php foreach ($this->form->getFieldset('DETAILSROW2') as $field): ?>
                    <li><?php
						echo $field->label;
						echo $field->input;
						?></li>
					<?php endforeach; ?>
                </ul>
            </fieldset>
			<?php echo JHtml::_('sliders.panel', JText::_('JBS_TPL_DETAILS_LIST_ROW3'), 'publishing-details'); ?>
            <fieldset class="panelform">
                <legend><?php echo JText::_('JBS_TPL_DETAILS_LIST_ROW3'); ?></legend>
                <ul class="adminformlist">
					<?php foreach ($this->form->getFieldset('DETAILSROW3') as $field): ?>
                    <li><?php
						echo $field->label;
						echo $field->input;
						?></li>
					<?php endforeach; ?>
                </ul>
            </fieldset>
			<?php echo JHtml::_('sliders.panel', JText::_('JBS_TPL_DETAILS_LIST_ROW4'), 'publishing-details'); ?>
            <fieldset class="panelform">
                <legend><?php echo JText::_('JBS_TPL_DETAILS_LIST_ROW4'); ?></legend>
                <ul class="adminformlist">
					<?php foreach ($this->form->getFieldset('DETAILSROW4') as $field): ?>
                    <li><?php
						echo $field->label;
						echo $field->input;
						?></li>
					<?php endforeach; ?>
                </ul>
            </fieldset>
			<?php echo JHtml::_('sliders.end'); ?>
        </fieldset>
    </div>
</div>
<div class="clr"></div>
<?php echo JHtml::_('tabs.panel', JText::_('JBS_TPL_TEACHER_VIEW'), 'admin-system-defaults'); ?>
<div class="width-100">
    <div class="width-80 fltlft">
        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_TPL_TEACHER_VIEW'); ?></legend>

            <ul class="adminformlist">
				<?php foreach ($this->form->getFieldset('TEACHER') as $field): ?>
                <li><?php
					echo $field->label;
					echo $field->input;
					?></li>
				<?php endforeach; ?>
            </ul>
        </fieldset>
    </div>
</div>
<div class="clr"></div>
<?php echo JHtml::_('tabs.panel', JText::_('JBS_CMN_SERIES'), 'admin-system-defaults'); ?>
<div class="width-100">
    <div class="width-80 fltlft">
        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_CMN_SERIES'); ?></legend>
			<?php echo JHtml::_('sliders.start', 'content-sliders-' . $this->item->id, array('useCookie' => 1)); ?>
			<?php echo JHtml::_('sliders.panel', JText::_('JBS_TPL_SERIES_LIST'), 'publishing-details'); ?>
            <ul class="adminformlist">
				<?php foreach ($this->form->getFieldset('SERIES') as $field): ?>
                <li><?php
					echo $field->label;
					echo $field->input;
					?></li>
				<?php endforeach; ?>
            </ul>
			<?php echo JHtml::_('sliders.panel', JText::_('JBS_CMN_SERIES_DETAIL_VIEW'), 'publishing-details'); ?>
            <ul class="adminformlist">
				<?php foreach ($this->form->getFieldset('SERIESDETAIL') as $field): ?>
                <li><?php
					echo $field->label;
					echo $field->input;
					?></li>
				<?php endforeach; ?>
            </ul>
			<?php echo JHtml::_('sliders.end'); ?>
        </fieldset>
    </div>
</div>

<div class="clr"></div>
<?php echo JHtml::_('tabs.panel', JText::_('JBS_CMN_FIELDSET_RULES'), 'admin-system-defaults'); ?>
<div class="clr"></div>
<?php if ($this->canDo->get('core.admin')): ?>
<div class="width-100 fltlft">
	<?php echo JHtml::_('sliders.start', 'permissions-sliders-' . $this->item->id, array('useCookie' => 1)); ?>
	<?php echo JHtml::_('sliders.panel', JText::_('JBS_CMN_FIELDSET_RULES'), 'access-rules'); ?>
    <fieldset class="panelform">
		<?php echo $this->form->getLabel('rules'); ?>
		<?php echo $this->form->getInput('rules'); ?>
    </fieldset>
	<?php echo JHtml::_('sliders.end'); ?>
</div>
	<?php endif; ?>
<div class="clr"></div>
<?php echo JHtml::_('tabs.end'); ?>
<input type="hidden" name="task" value=""/>
<input type="hidden" name="controller" value="template"/>
<?php echo JHtml::_('form.token'); ?>
</form>