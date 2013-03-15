<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

$published = $this->state->get('filter.published');
JHtml::addIncludePath(BIBLESTUDY_PATH_ADMIN_HELPERS . '/html');
?>
<div class="modal hide fade" id="collapseModal">
    <div class="modal-header">
		<?php if (BIBLESTUDY_CHECKREL)
	{
		?>
        <button type="button" role="presentation" class="close" data-dismiss="modal">x</button><?php } ?>
        <h3><?php echo JText::_('JBS_CMN_BATCH_OPTIONS'); ?></h3>
    </div>
    <div class="modal-body">
        <p><?php echo JText::_('JBS_CMN_BATCH_TIP_MEDIAFILES'); ?></p>

        <div class="control-group">
            <div class="controls">
				<?php echo JHtml::_('biblestudy.players');?>
            </div>
        </div>
        <div class="control-group">
            <div class="controls">
				<?php echo JHtml::_('biblestudy.popup');?>
            </div>
        </div>
        <div class="control-group">
            <div class="controls">
				<?php echo JHtml::_('biblestudy.Mediatype');?>
            </div>
        </div>
        <div class="control-group">
            <div class="controls">
				<?php echo JHtml::_('biblestudy.link_type');?>
            </div>
        </div>
        <div class="control-group">
            <div class="controls">
				<?php echo JHtml::_('batch.access'); ?>
            </div>
        </div>
        	<div class="control-group">
			<div class="controls">
				<?php echo JHtml::_('batch.language'); ?>
			</div>
		</div>
    </div>
    <div class="modal-footer">
        <button class="btn" type="button"
                onclick="document.id('batch-mediafiles-id');document.id('batch-access').value=''" data-dismiss="modal">
			<?php echo JText::_('JCANCEL'); ?>
        </button>
        <button class="btn btn-primary" type="submit" onclick="Joomla.submitbutton('mediafile.batch');">
			<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
        </button>
    </div>
</div>
