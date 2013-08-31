<?php
/**
 * Edit
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.calendar');
JHtml::_('behavior.formvalidation');

// Load the tooltip behavior.
if (BIBLESTUDY_CHECKREL)
{
	JHtml::_('formbehavior.chosen', 'select');
}
else
{
	JHtml::_('behavior.tooltip');
	JHtml::stylesheet('media/com_biblestudy/jui/css/bootstrap.css');
	JHtml::script('media/com_biblestudy/jui/js/jquery.js');
	JHtml::script('media/com_biblestudy/jui/js/jquery-noconflict.js');
	JHtml::script('media/com_biblestudy/jui/js/jquery.ui.core.min.js');
	JHtml::script('media/com_biblestudy/jui/js/bootstrap.js');
	JHTML::stylesheet('media/com_biblestudy/css/biblestudy-j2.5.css');
	JHTML::stylesheet('media/com_biblestudy/jui/css/chosen.css');
}

JHtml::script('media/com_biblestudy/js/noconflict.js');
JHtml::script('media/com_biblestudy/js/biblestudy.js');

// Create shortcut to parameters.
$params = $this->item->params;
$app = JFactory::getApplication();
$input = $app->input;

// Get the studyid if this is coming to us in a modal form
$folder = '';
$server = '';
$option = $input->get('option', '', 'cmd');
$input = new JInput;
$study = $app->getUserState($option . 'sid');
$sdate = $app->getUserState($option . 'sdate');

$size = $app->getUserState($option . 'size');
$fname = $app->getUserState($option . 'fname');
$serverid = $app->getUserState($option . 'serverid');

if ($this->item->server)
{
	$server = $this->item->server;
}
elseif ($serverid)
{
	$server = $serverid;
}
elseif (empty($this->item->study_id))
{
	$server = $this->admin_params->get('server');
}
$folderid = $app->getUserState('folderid');

if ($this->item->path)
{
	$folder = $this->item->path;
}
elseif ($folderid)
{
	$folder = $folderid;
}
elseif (empty($this->item->study_id))
{
	$folder = $this->admin_params->get('path');
}
?>
<script>
	function openConverter1() {
		var Wheight = 125;
		var Wwidth = 300;
		var winl = (screen.width - Wwidth) / 2;
		var wint = (screen.height - Wheight) / 2;

		var msg1 = window.open("components/com_biblestudy/convert1.htm", "Window", "scrollbars=1,width=" + Wwidth + ",height=" + Wheight + "" +
			",top=" + wint + ",left=" + winl);
		if (!msg1.closed) {
			msg1.focus();
		}
	}
</script>
<script type="text/javascript">
	Joomla.submitbutton = function (task) {
		if (task == 'mediafileform.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
			Joomla.submitform(task, document.getElementById('item-form'));
		} else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>
<script language="javascript" type="text/javascript">
	function submitbutton(task) {
		if (task == '') {
			return false;
		}
		else if (task == 'upload') {
			if (document.adminForm.upload_folder.value == '') {
				alert("<?php echo JText::_('JBS_MED_SELECT_FOLDER'); ?>");
			}
			else if (document.adminForm.upload_server.value == '') {
				alert("<?php echo JText::_('JBS_MED_ENTER_SERVER'); ?>");
			}
			else {
				submitform(task);
				window.location.setTimeout('window.location.reload(true)', 1000);
				return true;
			}
		}

		else if (task == 'thirdparty') {
			if (document.adminForm.video_third.value == '') {
				alert("<?php echo JText::_('JBS_MED_ADD_THIRD_PARTY_URL'); ?>");
			}
			else {
				if (confirm("<?php echo JText::_('JBS_MED_SURE_OVERWRITE_DETAILS'); ?>")) {
					submitform(task);
					window.top.setTimeout('window.location.reload(true)', 1000);
					return true;
				}
			}
		}
		else if (task == 'cancel') {

			window.parent.SqueezeBox.close();
		}
		else {
			var isValid = true;
			if (task != 'cancel' && task != 'close' && task != 'uploadflash') {
				var forms = $$('form.form-validate');
				for (var i = 0; i < forms.length; i++) {
					if (!document.formvalidator.isValid(forms[i])) {
						isValid = false;
						break;
					}
				}
			}

			if (isValid) {
				submitform(task);
				if (self != top) {
					window.top.setTimeout('window.parent.SqueezeBox.close()', 2000);
				}
				window.top.setTimeout('window.location.reload(true)', 1000);
				return true;
			}
			else {
				alert('<?php echo JText::_('JBS_MED_FIELDS_INVALID'); ?>');
				return false;
			}
		}
	}

	function sizebutton(remotefilesize) {
		var objTB = document.getElementById("size");
		objTB.value = remotefilesize;
	}

	function showupload() {
		var id = 'SWFUpload_0';
		if (document.adminForm.upload_server.value != '' && document.adminForm.upload_folder.value != '') {
			document.getElementById(id).style.display = 'inline';
		}
		else {
			document.getElementById(id).style.display = 'none';
		}
	}

	if (window.addEventListener) {
		window.addEventListener('load', showupload, false);
	} else if (window.attachEvent) {
		window.attachEvent('load', showupload);
	}

</script>
<div class="edit item-page biblestudy">
<form
	action="<?php
	$input = new JInput;

	if ($input->get('layout', '', 'string') == 'modal')
	{
		$url = 'index.php?option=com_biblestudy&layout=mediafileform&tmpl=component&layout=modal&a_id=' . (int) $this->item->id;
	}
	else
	{
		$url = 'index.php?option=com_biblestudy&view=mediafileform&layout=edit&a_id=' . (int) $this->item->id;
	}
	echo $url;
	?>" method="post" name="adminForm" id="item-form" class="form-validate" enctype="multipart/form-data">

<div class="btn-toolbar">
	<?php
	echo JText::_('JBS_MED_MEDIA_FILES_DETAILS');

	if ($input->get('layout', '', 'string') == 'modal')
	{
		?>

		<div class="btn-group">
			<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('mediafileform.save')">
				<span class="icon-ok"></span>&#160;<?php echo JText::_('JSAVE') ?>
			</button>
		</div>
		<div class="btn-group">
			<button type="button" onclick="window.parent.SqueezeBox.close();  ">
				<span class="icon-cancel"></span>&#160;<?php echo JText::_('JCANCEL') ?>
			</button>
		</div>
	<?php
	}
	else
	{
		?>
		<div class="btn-group">
			<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('mediafileform.save')">
				<span class="icon-ok"></span>&#160;<?php echo JText::_('JSAVE') ?>
			</button>
		</div>
		<div class="btn-group">
			<button type="button" class="btn" onclick="Joomla.submitbutton('mediafileform.cancel')">
				<span class="icon-cancel"></span>&#160;<?php echo JText::_('JCANCEL') ?>
			</button>
		</div>
	<?php } ?>
</div>
<!-- Begin Content -->
<fieldset>
<ul class="nav nav-tabs">
	<li class="active">
		<a href="#general" data-toggle="tab"><?php echo JText::_('JBS_CMN_DETAILS'); ?></a>
	</li>
	<li>
		<a href="#state" data-toggle="tab"><?php echo JText::_('JBS_CMN_ITEM_PUBLISHED'); ?></a>
	</li>
	<li>
		<a href="#linktype" data-toggle="tab"><?php echo JText::_('JBS_MED_MEDIA_FILES_LINKER'); ?></a>
	</li>
	<li>
		<a href="#player" data-toggle="tab"><?php echo JText::_('JBS_MED_MEDIA_FILES_SETTINGS'); ?></a>
	</li>
	<li>
		<a href="#file" data-toggle="tab"><?php echo JText::_('JBS_MED_MEDIA_FILES'); ?></a>
	</li>
	<li>
		<a href="#upload" data-toggle="tab"><?php echo JText::_('JBS_MED_UPLOAD'); ?></a>
	</li>
	<li>
		<a href="#mediatype" data-toggle="tab"><?php echo JText::_('JBS_MED_MEDIA_TYPE'); ?></a>
	</li>
	<li>
		<a href="#parameters" data-toggle="tab"><?php echo JText::_('JBS_CMN_PARAMETERS'); ?></a>
	</li>

	<?php if ($this->canDo->get('core.admin')): ?>
		<li><a href="#permissions" data-toggle="tab"><?php echo JText::_('JBS_CMN_FIELDSET_RULES'); ?></a></li>
	<?php endif ?>
</ul>
<div class="tab-content">
<div class="tab-pane active" id="general">

	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('id'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('id'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('plays'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('plays'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('downloads'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('downloads'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('createdate'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('createdate', null, empty($this->item->createdate) ? $sdate : null); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('study_id'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('study_id', null, empty($this->item->study_id) ? $study : null); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('podcast_id'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('podcast_id', null, empty($this->item->study_id) ? $this->admin_params->get('podcast') : null); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('link_type'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('link_type', null, empty($this->item->study_id) ? $this->admin_params->get('download') : $this->item->link_type); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('comment'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('comment'); ?>
		</div>
	</div>


</div>
<div class="tab-pane" id="linktype">
	<div class="row-fluid">


		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('docMan_id'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('docMan_id'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('article_id'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('article_id'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('virtueMart_id'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('virtueMart_id'); ?>
			</div>
		</div>

	</div>
</div>
<div class="tab-pane" id="player">


	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('player'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('player'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('popup'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('popup'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('mediacode'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('mediacode'); ?>
		</div>
	</div>


</div>
<div class="tab-pane" id="file">


	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('plays'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('plays'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('downloads'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('downloads'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('server'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('server', null, empty($this->item->server) ? $server : null); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('path'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('path', null, empty($this->item->study_id) ? $folder : null); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('filename'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('filename', null, empty($this->item->filename) ? $fname : null); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('size'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('size', null, empty($this->item->size) ? $size : null); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('special'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('special', null, empty($this->item->study_id) ? $this->admin_params->get('target') : $this->item->special); ?>
		</div>
	</div>


</div>
<div class="tab-pane" id="upload">

	<div class="control-group">
		<div class="control-label">
			<?php echo JText::_('JBS_STY_UPLOAD'); ?>
		</div>
		<div class="controls">
			<table class="table table-striped adminlist">

				<tbody>
				<tr>
					<td>
						<?php echo $this->upload_server; ?></td>
					</td></tr>
				<tr>
					<td>
						<?php echo $this->upload_folder; ?></td>
					</td></tr>
				<tr>
					<td>

						<input type="file" name="uploadfile" value=""/>
						<button type="button" onclick="submitbutton('upload')">
							<?php echo JText::_('JBS_STY_UPLOAD_BUTTON'); ?> </button>
					</td>
					<td></td>
				</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
<div class="tab-pane" id="mediatype">


	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('media_image'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('media_image', null, empty($this->item->study_id) ? $this->admin_params->get('media_image') : $this->item->media_image); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('mime_type'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('mime_type', null, empty($this->item->study_id) ? $this->admin_params->get('mime') : $this->item->mime_type); ?>
		</div>
	</div>


</div>
<div class="tab-pane" id="parameters">
	<?php foreach ($params as $name => $fieldset) :
		foreach ($this->form->getFieldset('params') as $field) :  ?>
			<div class="control-group">
				<div class="control-label">
					<?php echo $field->label; ?>
				</div>
				<div class="controls">
					<?php echo $field->input; ?>
				</div>
			</div>
		<?php endforeach; ?>
	<?php endforeach; ?>
</div>
<div class="tab-pane" id="state">
	<fieldset>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('published'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('published'); ?>
			</div>
		</div>

		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('access'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('access'); ?>
			</div>
		</div>

		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('language'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('language'); ?>
			</div>
		</div>
</div>
<?php if ($this->canDo->get('core.admin')): ?>
	<div class="tab-pane" id="permissions">

		<div class="control-group">
			<div class="controls">
				<?php echo $this->form->getInput('rules'); ?>
			</div>
		</div>
	</div>
<?php endif; ?>
<input type="hidden" name="flupfile" value=""/>
<input type="hidden" name="task" value=""/>
<input type="hidden" name="return" value="<?php echo $this->return_page; ?>"/>
<?php echo JHtml::_('form.token'); ?>
</div>
</fieldset>
</form>
</div>
<div class="clearfix"></div>

