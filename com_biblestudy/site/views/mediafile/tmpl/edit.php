<?php
/**
 * Edit
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * @fixme look at coresponding fixme
 * */
//No Direct Access
defined('_JEXEC') or die;
$params = $this->form->getFieldsets('params');
$folder = '';
$server = '';
$app = JFactory::getApplication();
$option = JRequest::getCmd('option');
$study = $app->getUserState($option . 'sid');
$sdate = $app->getUserState($option . 'sdate');
$size = $app->getUserState($option . 'size');
$fname = $app->getUserState($option . 'fname');
$serverid = $app->getUserState($option . 'serverid');
if ($this->item->server) {
    $server = $this->item->server;
} elseif ($serverid) {
    $server = $serverid;
} elseif (empty($this->item->study_id)) {
    $server = $this->admin->params['server'];
}
$folderid = $app->getUserState($option . 'folderid');
if ($this->item->path) {
    $folder = $this->item->path;
} elseif ($folderid) {
    $folder = $folderid;
} elseif (empty($this->item->study_id)) {
    $folder = $this->admin->params['path'];
}
?>
<script language="javascript" type="text/javascript">
    function submitbutton(task)
    {
        if (task == '')
        {
            return false;
        }
       	else if (task == 'upload')
        {
            if (document.adminForm.upload_folder.value == '')
            {
                alert("<?php echo JText::_('JBS_MED_SELECT_FOLDER'); ?>");
            }
            else if (document.adminForm.upload_server.value == '' )
            {
                alert("<?php echo JText::_('JBS_MED_ENTER_SERVER'); ?>");
            }
            else {
                submitform(task);
                window.location.setTimeout('window.location.reload(true)', 1000);
                return true;
            }
        }
        else if  (task == 'thirdparty')
        {
            if (document.adminForm.video_third.value == '')
            {
                alert("<?php echo JText::_('JBS_MED_ADD_THIRD_PARTY_URL'); ?>");
            }
            else
            {
                if(confirm("<?php echo JText::_('JBS_MED_SURE_OVERWRITE_DETAILS'); ?>"))
                {submitform(task);
                    window.location.setTimeout('window.location.reload(true)', 1000);
                    return true;}
            }
        }
        else if (task == 'cancelclose')
        {

            window.parent.SqueezeBox.close();
        }
        else
        {
            var isValid=true;
            if (task != 'cancel' && task != 'close' && task != 'uploadflash')
            {
                var forms = $$('form.form-validate');
                for (var i=0;i<forms.length;i++)
                {
                    if (!document.formvalidator.isValid(forms[i]))
                    {
                        isValid = false;
                        break;
                    }
                }
            }

            if (isValid)
            {
                submitform(task);
                if (self != top)
                {
                    window.top.setTimeout('window.parent.SqueezeBox.close()', 2000);
                }
                window.top.setTimeout('window.location.reload(true)', 1000);
                return true;
            }
            else
            {
                alert('<?php echo JText::_('JBS_MED_FIELDS_INVALID'); ?>');
                return false;
            }
        }
    }
</script>
<script language="javascript" type="text/javascript">
    function sizebutton(remotefilesize)
    {
        var objTB = document.getElementById("size");
        objTB.value = remotefilesize;
    }

    function showupload() {
        var id = 'SWFUpload_0';
        if (document.adminForm.upload_server.value != '' && document.adminForm.upload_folder.value != '')
        {document.getElementById(id).style.display = 'inline';}
        else {document.getElementById(id).style.display = 'none';}
    }

    if (window.addEventListener){
        window.addEventListener('load', showupload, false);
    } else if (window.attachEvent){
        window.attachEvent('load', showupload);
    }

</script>

<div class="edit">
    <form action="<?php
if (JRequest::getWord('layout', '') == 'modal') {
    $url = 'index.php?option=com_biblestudy&layout=mediafile&tmpl=component&layout=modal&id=' . (int) $this->item->id;
} else {
    $url = 'index.php?option=com_biblestudy&view=mediafile&layout=edit&id=' . (int) $this->item->id;
} echo JRoute::_($url);
?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
        <fieldset class="panelform">
            <div class="formelm-buttons">
                <legend>
                    <?php
                    echo JText::_('JBS_MED_MEDIA_FILES_DETAILS');
                    if (JRequest::getWord('layout', '') == 'modal') {
                        ?> <div class="fltlft">
                            <button type="button" onclick="submitbutton('mediafile.save');  ">
                                <?php echo JText::_('JSAVE'); ?></button>
                            <button type="button" onclick="window.parent.SqueezeBox.close();  ">
                                <?php echo JText::_('JCANCEL'); ?></button>
                        </div> <?php } ?>
                </legend>
            </div>
            <div class="formelm">
                <?php echo $this->form->getLabel('published'); ?>
                <?php echo $this->form->getInput('published'); ?>
            </div>
            <div class="formelm">
                <?php echo $this->form->getLabel('createdate'); ?>
                <?php echo $this->form->getInput('createdate', null, empty($this->item->createdate) ? $sdate : null); ?>
            </div>
            <div class="formelm-area">
                <?php echo $this->form->getLabel('study_id'); ?>
                <?php echo $this->form->getInput('study_id', null, empty($this->item->study_id) ? $study : null); ?><br /><br />
            </div>
            <div class="formelm">
                <?php echo $this->form->getLabel('podcast_id'); ?>
                <?php echo $this->form->getInput('podcast_id', null, $this->admin->params['podcast']); ?>
            </div>
            <div class="formelm">
                <?php echo $this->form->getLabel('link_type'); ?>
                <?php echo $this->form->getInput('link_type', null, $this->admin->params['download']); ?>
            </div>
            <div class="formelm">
                <?php echo $this->form->getLabel('ordering'); ?>
                <?php echo $this->form->getInput('ordering'); ?>
            </div>
            <div class="formelm">
                <?php echo $this->form->getLabel('comment'); ?>
                <?php echo $this->form->getInput('comment'); ?>
            </div>
        </fieldset>

        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_MED_MEDIA_FILES_SETTINGS'); ?></legend>
            <div class="formelm">
                <?php echo $this->form->getLabel('player'); ?>
                <?php echo $this->form->getInput('player'); ?>
            </div>
            <div class="formelm">
                <?php echo $this->form->getLabel('popup'); ?>
                <?php echo $this->form->getInput('popup'); ?>
            </div>
            <div class="formelm">
                <?php echo $this->form->getLabel('mediacode'); ?>
                <?php echo $this->form->getInput('mediacode'); ?>
            </div>
        </fieldset>
        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_MED_MEDIA_TYPE'); ?></legend>
            <div class="formelm">
                <?php echo $this->form->getLabel('media_image'); ?>
                <?php echo $this->form->getInput('media_image'); ?>
            </div>
            <div class="formelm">
                <?php echo $this->form->getLabel('mime_type'); ?>
                <?php echo $this->form->getInput('mime_type', null, $this->admin->params['mime']); ?>
            </div>
        </fieldset>
        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_MED_MEDIA_FILES'); ?></legend>
            <div class="formelm">
                <?php echo $this->form->getLabel('plays'); ?>
                <?php echo $this->form->getInput('plays'); ?>
            </div>
            <div class="formelm">
                <?php echo $this->form->getLabel('downloads'); ?>
                <?php echo $this->form->getInput('downloads'); ?>
            </div>
            <div class="formelm">
                <?php echo $this->form->getLabel('spacer'); ?>
            </div>
            <div class="formelm">
                <?php echo $this->form->getLabel('server'); ?>
                <?php echo $this->form->getInput('server', null, empty($this->item->server) ? $server : null); ?>
            </div>
            <div class="formelm">
                <?php echo $this->form->getLabel('path'); ?>
                <?php echo $this->form->getInput('path', null, empty($this->item->study_id) ? $folder : null); ?>
            </div>
            <div class="formelm">
                <?php echo $this->form->getLabel('filename'); ?>
                <?php echo $this->form->getInput('filename', null, empty($this->item->filename) ? $fname : null); ?>
            </div>
            <div class="formelm">
                <?php echo $this->form->getLabel('size'); ?>
                <?php echo $this->form->getInput('size', null, empty($this->item->size) ? $size : null); ?><br /><br />
            </div>
            <div class="formelm">
                <?php echo $this->form->getLabel('special'); ?>
                <?php echo $this->form->getInput('special', null, $this->admin->params['target']); ?>
            </div>
            <table class="adminlist">
                <thead>
                <th align="center" colspan="2"><?php echo JText::_('JBS_STY_UPLOAD'); ?></th>
                </thead>
                <tbody>
                    <tr><td>
                            <?php echo $this->upload_server; ?></td>
                    </tr>
                    <tr><td>
                            <?php echo $this->upload_folder; ?></td>
                    </tr>
                    <tr><td>
                            <input type="file" name ="uploadfile" value="" />
                            <button type="button" onclick="submitbutton('upload')">
                                <?php echo JText::_('JBS_STY_UPLOAD_BUTTON'); ?></button></td>
                    </tr>
                </tbody>
            </table>
        </fieldset>
        <fieldset class="panelform">
            <div class="formelm">
                <?php echo $this->form->getLabel('docMan_id'); ?>
                <?php echo $this->form->getInput('docMan_id'); ?>
            </div>
            <div class="formelm">
                <?php echo $this->form->getLabel('virtueMart_id'); ?>
                <?php echo $this->form->getInput('virtueMart_id'); ?>
            </div>
            <div class="formelm">
                <?php echo $this->form->getLabel('article_id'); ?>
                <?php echo $this->form->getInput('article_id'); ?>
            </div>
        </fieldset>
        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_CMN_PARAMETERS'); ?></legend>
            <?php
            foreach ($params as $name => $fieldset): // @fixme what is going on hear not sure whey we need this?
                ?>
                <fieldset class="panelform" >
                    <legend><?php echo JText::_('JBS_MED_OPTIONS'); ?></legend>
                    <div class="panelform">
                        <?php foreach ($this->form->getFieldset($name) as $field) : ?>
                            <div class="formelm-area"><?php echo $field->label; ?><?php echo $field->input; ?></div>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
            <?php endforeach; ?>
        </fieldset>

        <div class="clr"></div>
        <input type="hidden" name="flupfile" value ="" />
        <input type="hidden" name="task" value="" />
        <?php echo JHtml::_('form.token'); ?>
        <input type="hidden" name="controller" value="mediafile" />
    </form>
</div>