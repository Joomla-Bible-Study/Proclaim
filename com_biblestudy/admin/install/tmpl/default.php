<?php
/**
 * BibleStudy Component
 * @package BibleStudy.Installer
 * @subpackage Template
 *
 * @copyright (C) 2008 - 2015 BibleStudy Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.joomlabiblestudy.org
 **/
defined ( '_JEXEC' ) or die ();
?>
<div id="jbsm" style="max-width:530px">
	<div id="jbsm-install">
		<h2><?php echo JText::_('COM_BIBLESTUDY_INSTALL_PLEASE_WAIT'); ?></h2>
		<div>
			<div id="jbsm-description"><?php echo JText::_('COM_BIBLESTUDY_INSTALL_PREPARING'); ?></div>
			<div class="progress progress-striped active">
				<div id="jbsm-progress" class="bar" style="width: 0%;"></div>
			</div>
		</div>
	</div>
	<div>
		<button id="jbsm-toggle" class="btn" style="float: left;"><?php echo JText::_('COM_BIBLESTUDY_INSTALL_DETAILS'); ?></button>
		<div class="pull-right">
			<button id="jbsm-component" class="btn jbsm-close" disabled="disabled"><?php echo JText::_('COM_BIBLESTUDY_INSTALL_TO_JBSM'); ?></button>
			<button id="jbsm-installer" class="btn jbsm-close" disabled="disabled" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_BIBLESTUDY_INSTALL_CLOSE'); ?></button>
		</div>
		<div id="jbsm-container" class="hidden">
			<p class="clr clearfix"></p>
			<div id="jbsm-details" class="well well-small"><h4><?php echo JText::_('COM_BIBLESTUDY_INSTALL_DETAILS'); ?></h4><div><?php echo JText::_('COM_BIBLESTUDY_INSTALL_PREPARING'); ?></div></div>
		</div>
	</div>
</div>
<script>
window.jbsmAddItems = function(log) {
	jQuery('#jbsm-details').html(log);
};
window.jbsminstall = function() {
	var jbsmInstall = jQuery('#jbsm-install');
	var jbsmProgress = jQuery('#jbsm-progress');
	var jbsmDescription = jQuery('#jbsm-description');

	jQuery.ajax({
		type: 'POST',
		dataType: 'json',
		timeout: '180000', // 3 minutes
		url: '<?php echo JRoute::_('index.php?option=com_biblestudy&view=install&task=run', false)?>',
		data: '<?php echo JSession::getFormToken(); ?>=1',
		cache: false,
		error: function (xhr, ajaxOptions, thrownError) {
			jbsmInstall.html('<h2><?php echo JText::_('COM_BIBLESTUDY_INSTALL_ERROR_MESSAGE', true); ?></h2><div><?php echo JText::_('COM_BIBLESTUDY_INSTALL_ERROR_DETAILS', true); ?></div><div>' + xhr.responseText + '</div>');
			jbsmProgress.addClass('bar-danger');
			jQuery('#jbsm-installer').show();
		},
		beforeSend: function () {
			jbsmProgress.css('width', '1%');
		},
		complete: function () {

		},
		success: function (json) {
			if (json.status) {
				jbsmProgress.css('width', json.status);
			}
			if (json.log) {
				window.jbsmAddItems(json.log);
			}
			if (json.success) {
				jbsmDescription.html(json.current);
				if (json.status != '100%') {
					window.jbsminstall();
					return;
				} else {
					jbsmInstall.find('h2').text('<?php echo JText::_('COM_BIBLESTUDY_INSTALL_SUCCESS_MESSAGE', true); ?>');
					jbsmProgress.parent().removeClass('active');
					jbsmProgress.addClass('bar-success');
				}
				jQuery('.jbsm-close').removeAttr('disabled');
			} else {
				jbsmProgress.parent().removeClass('active');
				jbsmInstall.find('h2').text('<?php echo JText::_('COM_BIBLESTUDY_INSTALL_ERROR_MESSAGE', true); ?>');
				jbsmDescription.html(json.error);
				jbsmProgress.addClass('bar-danger');
				jQuery('#jbsm-installer').removeAttr('disabled');
				jQuery('#jbsm-container').removeClass('hidden');
			}
		}
	});


}
jQuery( document ).ready(function() {
	jQuery('#jbsm-toggle').click(function(e) {
		jQuery('#jbsm-container').toggleClass('hidden');
		e.preventDefault();
	});
	jQuery('#jbsm-component').click(function(e) {
		window.location.href='<?php echo JRoute::_('index.php?option=com_biblestudy', false)?>';
		e.preventDefault();
	});
	jQuery('#jbsm-installer').click(function(e) {
		window.location.href='#Close';
		e.preventDefault();
	});
	window.jbsminstall();
});
</script>
