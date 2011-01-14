<?php
/**
 * @version     $Id$
 * @package     com_biblestudy
 * @license     GNU/GPL
 */

//No Direct Access
defined('_JEXEC') or die();

?>

<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&layout=form&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm">

<?php echo JHtml::_('tabs.start'); ?>
<?php echo JHtml::_('tabs.panel', JText::_('JBS_TPL_GENERAL'), 'general'); ?>
 <div class="width-100">
    <div class="width-60 fltlft">
        <fieldset class="panelform">
                <legend><?php echo JText::_('JBS_TPL_GENERAL'); ?></legend>
                 <ul class="adminformlist">
                <li>
                    <?php echo $this->form->getLabel('published'); ?>
                    <?php echo $this->form->getInput('published'); ?>
                </li>
                <?php foreach($this->form->getFieldset('TEMPLATES') as $field): ?>
                        <li><?php echo $field->label;echo $field->input;?></li>
                    <?php endforeach; ?>
                </ul>
        </fieldset>
    </div>
        
 </div>
 <div class="clr"></div>
 <?php echo JHtml::_('tabs.panel', JText::_('JBS_TPL_LIST_ITEMS'), 'admin-system-defaults'); ?>
    <div class="width-100">
        <div class="width-60 fltlft">
            <fieldset class="panelform">
                <legend><?php echo JText::_('JBS_TPL_LIST_ITEMS'); ?></legend>      
                    <?php echo JHtml::_('sliders.start','content-sliders-'.$this->item->id, array('useCookie'=>1)); ?>

            <?php echo JHtml::_('sliders.panel',JText::_('JBS_TPL_VERSES_DATES_CSS'), 'publishing-details'); ?>
			<fieldset class="panelform">
                <legend><?php echo JText::_('JBS_TPL_VERSES_DATES_CSS'); ?></legend>
				<ul class="adminformlist">
                <?php foreach($this->form->getFieldset('VERSES') as $field): ?>
                        <li><?php echo $field->label;echo $field->input;?></li>
                    <?php endforeach; ?>
                </ul>   

			<?php echo JHtml::_('sliders.panel',JText::_('JBS_TPL_LIST_ITEMS'), 'publishing-details'); ?>
			<fieldset class="panelform">
                <legend><?php echo JText::_('JBS_TPL_LIST_ITEMS'); ?></legend>
				<ul class="adminformlist">
                <?php foreach($this->form->getFieldset('LISTITEMS') as $field): ?>
                        <li><?php echo $field->label;echo $field->input;?></li>
                    <?php endforeach; ?>
                </ul>
                
             <?php echo JHtml::_('sliders.panel',JText::_('JBS_TPL_FILTERS'), 'publishing-details'); ?>
			<fieldset class="panelform">
                <legend><?php echo JText::_('JBS_TPL_FILTERS'); ?></legend>
				<ul class="adminformlist">
                <?php foreach($this->form->getFieldset('FILTERS') as $field): ?>
                        <li><?php echo $field->label;echo $field->input;?></li>
                    <?php endforeach; ?>
                </ul>   
              
               <?php echo JHtml::_('sliders.panel',JText::_('JBS_TPL_TOOLTIP_ITEMS'), 'publishing-details'); ?>
			<fieldset class="panelform">
                <legend><?php echo JText::_('JBS_TPL_TOOLTIP_ITEMS'); ?></legend>
				<ul class="adminformlist">
                <?php foreach($this->form->getFieldset('TOOLTIP') as $field): ?>
                        <li><?php echo $field->label;echo $field->input;?></li>
                    <?php endforeach; ?>
                </ul> 
                
                <?php echo JHtml::_('sliders.panel',JText::_('JBS_TPL_STUDY_LIST_ROW1'), 'publishing-details'); ?>
			<fieldset class="panelform">
                <legend><?php echo JText::_('JBS_TPL_STUDY_LIST_ROW1'); ?></legend>
				<ul class="adminformlist">
                <?php foreach($this->form->getFieldset('ROW1') as $field): ?>
                        <li><?php echo $field->label;echo $field->input;?></li>
                    <?php endforeach; ?>
                </ul> 
                
                 
                <?php echo JHtml::_('sliders.panel',JText::_('JBS_TPL_STUDY_LIST_ROW2'), 'publishing-details'); ?>
			<fieldset class="panelform">
                <legend><?php echo JText::_('JBS_TPL_STUDY_LIST_ROW1'); ?></legend>
				<ul class="adminformlist">
                <?php foreach($this->form->getFieldset('ROW2') as $field): ?>
                        <li><?php echo $field->label;echo $field->input;?></li>
                    <?php endforeach; ?>
                </ul> 
                
                 
                <?php echo JHtml::_('sliders.panel',JText::_('JBS_TPL_STUDY_LIST_ROW3'), 'publishing-details'); ?>
			<fieldset class="panelform">
                <legend><?php echo JText::_('JBS_TPL_STUDY_LIST_ROW3'); ?></legend>
				<ul class="adminformlist">
                <?php foreach($this->form->getFieldset('ROW3') as $field): ?>
                        <li><?php echo $field->label;echo $field->input;?></li>
                    <?php endforeach; ?>
                </ul> 
                
                 
                <?php echo JHtml::_('sliders.panel',JText::_('JBS_TPL_STUDY_LIST_ROW4'), 'publishing-details'); ?>
			<fieldset class="panelform">
                <legend><?php echo JText::_('JBS_TPL_STUDY_LIST_ROW4'); ?></legend>
				<ul class="adminformlist">
                <?php foreach($this->form->getFieldset('ROW4') as $field): ?>
                        <li><?php echo $field->label;echo $field->input;?></li>
                    <?php endforeach; ?>
                </ul> 
                     
                      
                <?php echo JHtml::_('sliders.panel',JText::_('JBS_TPL_STUDY_LIST_CUSTOM'), 'publishing-details'); ?>
			<fieldset class="panelform">
                <legend><?php echo JText::_('JBS_TPL_STUDY_LIST_CUSTOM'); ?></legend>
				<ul class="adminformlist">
                <?php foreach($this->form->getFieldset('STUDIESVIEW') as $field): ?>
                        <li><?php echo $field->label;echo $field->input;?></li>
                    <?php endforeach; ?>
                </ul> 
        <?php echo JHtml::_('sliders.end'); ?>
        </div>
    </div>
    
 <div class="clr"></div>
    <?php echo JHtml::_('tabs.panel', JText::_('JBS_CMN_MEDIA'), 'admin-system-defaults'); ?>
    <div class="width-100">
        <div class="width-60 fltlft">
            <fieldset class="panelform">
                <legend><?php echo JText::_('JBS_CMN_MEDIA'); ?></legend> 
                <ul class="adminformlist">
                    <?php foreach($this->form->getFieldset('MEDIA') as $field): ?>
                        <li><?php echo $field->label;echo $field->input;?></li>
                    <?php endforeach; ?>
                </ul>
        </div>
    </div>
    
    
 <div class="clr"></div>
    <?php echo JHtml::_('tabs.panel', JText::_('JBS_TPL_DETAILS_VIEW'), 'admin-system-defaults'); ?>
    <div class="width-100">
        <div class="width-60 fltlft">
            <fieldset class="panelform">
                <legend><?php echo JText::_('JBS_TPL_DETAILS_VIEW'); ?></legend>      
<?php echo JHtml::_('sliders.start','content-sliders-'.$this->item->id, array('useCookie'=>1)); ?>

<?php echo JHtml::_('sliders.panel',JText::_('JBS_TPL_DETAILS_VIEW'), 'publishing-details'); ?>
			<fieldset class="panelform">
                <legend><?php echo JText::_('JBS_TPL_DETAILS_VIEW'); ?></legend>
				<ul class="adminformlist">
                <?php foreach($this->form->getFieldset('DETAILS') as $field): ?>
                        <li><?php echo $field->label;echo $field->input;?></li>
                    <?php endforeach; ?>
                 </ul> 
                 
<?php echo JHtml::_('sliders.panel',JText::_('JBS_TPL_DETAILS_LIST_ROW1'), 'publishing-details'); ?>
			<fieldset class="panelform">
                <legend><?php echo JText::_('JBS_TPL_DETAILS_LIST_ROW1'); ?></legend>
				<ul class="adminformlist">
                <?php foreach($this->form->getFieldset('DETAILSROW1') as $field): ?>
                        <li><?php echo $field->label;echo $field->input;?></li>
                    <?php endforeach; ?>
                 </ul>                  
 
 <?php echo JHtml::_('sliders.panel',JText::_('JBS_TPL_DETAILS_LIST_ROW2'), 'publishing-details'); ?>
			<fieldset class="panelform">
                <legend><?php echo JText::_('JBS_TPL_DETAILS_LIST_ROW2'); ?></legend>
				<ul class="adminformlist">
                <?php foreach($this->form->getFieldset('DETAILSROW2') as $field): ?>
                        <li><?php echo $field->label;echo $field->input;?></li>
                    <?php endforeach; ?>
                 </ul>    

<?php echo JHtml::_('sliders.panel',JText::_('JBS_TPL_DETAILS_LIST_ROW3'), 'publishing-details'); ?>
			<fieldset class="panelform">
                <legend><?php echo JText::_('JBS_TPL_DETAILS_LIST_ROW3'); ?></legend>
				<ul class="adminformlist">
                <?php foreach($this->form->getFieldset('DETAILSROW3') as $field): ?>
                        <li><?php echo $field->label;echo $field->input;?></li>
                    <?php endforeach; ?>
                </ul>
<?php echo JHtml::_('sliders.panel',JText::_('JBS_TPL_DETAILS_LIST_ROW4'), 'publishing-details'); ?>
			<fieldset class="panelform">
                <legend><?php echo JText::_('JBS_TPL_DETAILS_LIST_ROW4'); ?></legend>
				<ul class="adminformlist">
                <?php foreach($this->form->getFieldset('DETAILSROW4') as $field): ?>
                        <li><?php echo $field->label;echo $field->input;?></li>
                    <?php endforeach; ?>
                 </ul>    
                     
                 
   <?php echo JHtml::_('sliders.end'); ?>
        </div>
    </div>
 <div class="clr"></div>
    <?php echo JHtml::_('tabs.panel', JText::_('JBS_TPL_TEACHER_VIEW'), 'admin-system-defaults'); ?>
    
 
    <div class="width-100">
        <div class="width-60 fltlft">
            <fieldset class="panelform">
                <legend><?php echo JText::_('JBS_TPL_TEACHER_VIEW'); ?></legend>      
                    
				<ul class="adminformlist">
                <?php foreach($this->form->getFieldset('TEACHER') as $field): ?>
                        <li><?php echo $field->label;echo $field->input;?></li>
                    <?php endforeach; ?>
                 </ul>  
        </div>
    </div>
     <div class="clr"></div>
    <?php echo JHtml::_('tabs.panel', JText::_('JBS_TPL_SERIES'), 'admin-system-defaults'); ?>
    <div class="width-100">
        <div class="width-60 fltlft">
            <fieldset class="panelform">
                <legend><?php echo JText::_('JBS_TPL_SERIES_LIST'); ?></legend>      
                    <?php echo JHtml::_('sliders.start','content-sliders-'.$this->item->id, array('useCookie'=>1)); ?>
                    
                        <?php echo JHtml::_('sliders.panel',JText::_('JBS_TPL_SERIES_LIST'), 'publishing-details'); ?>
							<ul class="adminformlist">
                <?php foreach($this->form->getFieldset('SERIES') as $field): ?>
                
                        <li><?php echo $field->label;echo $field->input;?></li>
                    <?php endforeach; ?>
                </ul>
                
                 <?php echo JHtml::_('sliders.panel',JText::_('JBS_CMN_SERIES_DETAIL_VIEW'), 'publishing-details'); ?>
							<ul class="adminformlist">
                <?php foreach($this->form->getFieldset('SERIESDETAIL') as $field): ?>
                
                        <li><?php echo $field->label;echo $field->input;?></li>
                    <?php endforeach; ?>
                </ul>
                
    <?php echo JHtml::_('sliders.end'); ?>            
        </div>
    </div>
     <div class="clr"></div>
    <?php echo JHtml::_('tabs.panel', JText::_('JBS_TPL_LANDING_PAGE'), 'admin-system-defaults'); ?>
    <div class="width-100">
        <div class="width-60 fltlft">
            <fieldset class="panelform">
                <legend><?php echo JText::_('JBS_TPL_LANDING_PAGE'); ?></legend>      
                    	<ul class="adminformlist">
                <?php foreach($this->form->getFieldset('LANDINGPAGE') as $field): ?>
                        <li><?php echo $field->label;echo $field->input;?></li>
                    <?php endforeach; ?>
                 </ul>  
        </div>
    </div>
</form>
