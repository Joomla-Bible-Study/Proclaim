<?php
/**
 *
 */
// no direct access
defined("_JEXEC") or die();

class UploadScript
{

    public  $mediaRoot;
    public  $runtime;
    public  $runtimeScript;

    private $_maxFileSize;
    private $_chunkSize;
    private $_chunkUnit;
    private $_rename; //bool
    private $_uniqueNames; //bool
    private $_imageFilter;
    private $_otherFilesFilter;
    private $_resize; //bool
    private $_resizeWidth;
    private $_resizeHeight;
    private $_resizeQuality;

    private $_SCRIPT;

    /**
     *
     * @param object $params Component parameters
     * @param string $mediaRoot Media folder
     *
     */
    public function __construct( $params, $mediaRoot )
    {
        $this->mediaRoot = $mediaRoot;
        $this->_setParams( $params );
        $this->_buildScript();
    }

    /**
     *
     * Properly set component parameters for JavaScript usage
     *
     * @param object Component parameters
     */
    private function _setParams( $params )
    {
        //runtimes
        $allRuntimes                    = 'html5,flash,gears,silverlight,browserplus,html4';
        $this->runtimeScript            = 'full';
        $this->runtime                  = $this->runtimeScript == 'full' ? $allRuntimes : $this->runtimeScript;

        //default 1MB
        $this->_maxFileSize             = '1000';

        //chunk upload
        $this->_chunkSize 		= '1';
        $this->_chunkUnit               = 'mb';
        $this->_chunkUnit               = strtolower($this->_chunkUnit);

        //file rename
        $this->_rename 			= 'false';

        //file filters
        $imageFilter                    = 'jpg,png,gif';
        $this->_imageFilter             = $this->_cleanOption($imageFilter);
        $otherFilesFilter               = '*,doc,txt';
        $this->_otherFilesFilter        = $this->_cleanOption($otherFilesFilter);

        //generate unique names for files
        $this->_uniqueNames		= 'true';

        //image resizing
        $this->_resize 			= false;
        $this->_resizeWidth 		= '640';
        $this->_resizeHeight		= '480';
        $this->_resizeQuality		= '90';
    }

    /**
     * Sets up the JavaScript code with component parameters
     *
     * @return void
     */
    private function _buildScript()
    {
        $l_resize               = ""; //script resize line
        $l_chunk		= ""; //script chuk_size line
        $l_flash_swf_url 	= "flash_swf_url : '" . $this->mediaRoot . "js/plupload.flash.swf',"; //script flash swf url line
        $l_silverlight_xap	= "silverlight_xap_url: '" . $this->mediaRoot . "js/plupload.silverlight.xap',"; //script silverlight xap line

        if($this->_resize)
        {
            $l_resize = "resize : {width : " . $this->_resizeWidth . ", height : " . $this->_resizeHeight . ", quality : " . $this->_resizeQuality . "},";
        }

        if($this->_chunkSize !== 0 || $this->_chunkSize !== "")
        {
            $l_chunk = "chunk_size : '" . $this->_chunkSize . $this->_chunkUnit . "',";
        }

        ob_start();

        ?>
        $.noConflict();

        jQuery(document).ready(function($) {

        $('a#dismiss').click(function () {
        $('#system-message-container').html('');
        });

        function handleUpStatus(up, file, info, chunk) {
        // Called when a file or chunk has finished uploading
        var rspObj = $.parseJSON(info.response);
        var statusMsg = '';
        var fileString = '';
        var spanClass = '';

        if(rspObj.error == 1) {
        $('#' + file.id).attr('class', 'plupload_failed');
        file.hint = rspObj.msg;
        file.status = plupload.FAILED;
        file.percent = 0;

        up.total.size-= file.size;
        up.total.percent-=100;
        up.total.uploaded-=1;
        spanClass = 'failed_uploading';
        } else {
        $('#' + file.id).attr('class', 'plupload_done');
        file.status = plupload.DONE;
        spanClass = 'success_uploading';
        }

        statusMsg+= '<span class="' + spanClass + '">';
            statusMsg+= ' Status: ';
            statusMsg+= (file.status == plupload.DONE) ? 'DONE' : 'FAILED ';
            statusMsg+= ' Code: ' + rspObj.code + ' : '+ rspObj.msg;
            statusMsg+= '</span>';

        fileString+= ' Id: ' + file.id + ' Name: ' + file.name + ' Size: ' + file.size + ' Loaded: ' + file.percent + '% ';
        fileString+= statusMsg;
        if(!chunk){
        log('<b>[FileUploaded]</b> ' + fileString);
        } else {
        log('<b>[ChunkUploaded]</b> File:' + fileString);
        }

        }

        function ajaxReq(dataString, action) {

        var msgCont = $('#system-message-container');
        msgCont.html('<span class="loading"></span>');

        $.ajax({
        type: 'POST',
        url: action,
        data: dataString,
        dataType : 'json',
        success: function(response) {

        msgCont.html(' ');
        var msgHTML = '';

        if(response.error == 1) {
        msgHTML+= '<dl id="system-message">';
        msgHTML+= '<dt class="error">Error</dt>';
        msgHTML+= '<dd class="error message">';
            msgHTML+= '<ul><li>' + response.msg + '</li></ul>';
            msgHTML+= '</dd>';
        msgHTML+= '</dl>';
        } else {
        msgHTML+= '<dl id="system-message">';
        msgHTML+= '<dt class="message">Error</dt>';
        msgHTML+= '<dd class="message message">';
            msgHTML+= '<ul><li>' + response.msg + '</li></ul>';
            msgHTML+= '</dd>';
        msgHTML+= '</dl>';
        }
        window.frames[0].location.reload();
        msgCont.html(msgHTML);
        }
        });
        }

        function addLanguageSupport() {
        plupload.addI18n({
        'Select files' : '<?php echo JText::_('JBS_UPLOADER_SELECT_FILES');  ?>',
        'Add files to the upload queue and click the start button.' : '<?php echo JText::_('JBS_UPLOADER_ADD_FILES_TO_QUEUE')  ?>',
        'Filename' : '<?php echo JText::_('JBS_UPLOADER_FILENAME')  ?>',
        'Status' : '<?php echo JText::_('JBS_UPLOADER_STATUS')  ?>',
        'Size' : '<?php echo JText::_('JBS_UPLOADER_SIZE')  ?>',
        'Add files' : '<?php echo JText::_('JBS_UPLOADER_ADD_FILES')  ?>',
        'Start upload':'<?php echo JText::_('JBS_UPLOADER_START_UPLOAD')  ?>',
        'Stop current upload' : '<?php echo JText::_('JBS_UPLOADER_STOP_CURRENT_UPLOAD')  ?>',
        'Start uploading queue' : '<?php echo JText::_('JBS_UPLOADER_START_UPLOADING_QUEUE') ?>',
        'Drag files here.' : '<?php echo JText::_('JBS_UPLOADER_DRAG_FILES')  ?>'
        });
        }

        //init uploader
        function initUploader() {
        $("#uploader").pluploadQueue({
        // General settings
        runtimes : '<?php echo $this->runtime ?>',
        url : 'index.php?option=com_mediamu&no_html=1&task=file.upload&<?php echo JSession::getFormToken()  ?>=1',
        max_file_size : '<?php echo $this->_maxFileSize  ?>mb',
        <?php echo $l_chunk ?>
        rename : <?php echo $this->_rename ?>,
        unique_names : <?php echo $this->_uniqueNames ?>,
        <?php echo $l_resize ?>
        // Flash settings
        <?php echo $l_flash_swf_url ?>
        // Silverlight settings
        <?php echo $l_silverlight_xap  ?>
        filters : [
        {title : "Image files", extensions : "<?php echo $this->_imageFilter ?>"},
        {title : "Other files", extensions : "<?php echo $this->_otherFilesFilter ?>"}
        ],

        preinit : {
        Init: function(up, info) {
        log('<b>[Init]</b>', 'Info:', info, 'Features:', up.features);

        },

        UploadFile: function(up, file) {
        log('<b>[UploadFile]</b>', file);
        }

        },

        // Post init events, bound after the internal events
        init : {
        Refresh: function(up) {
        // Called when upload shim is moved
        log('<b>[Refresh]</b>');
        },

        StateChanged: function(up) {
        // Called when the state of the queue is changed
        if(up.state == plupload.STARTED) {
        //disable navigation
        $('#dirbroswer').hide();

        $('div#upload_in_progress').addClass('upload_in_progress');
        $('div#upload_in_progress').html('<h5>Upload in progress...</h5>');
        // Add stop button
        var stopBtn = document.createElement('a');
        stopBtn.className = 'plupload_button plupload_stop';
        stopBtn.id = 'plupload_stop';
        stopBtn.innerHTML = '<?php echo JText::_('JBS_UPLOADER_STOP_UPLOAD')  ?>';
        stopBtn.href = '#',
        stopBtn.onclick = function (up) {
        up.stop();
        }

        $('.plupload_filelist_footer').prepend(stopBtn);

        }

        if(up.state == plupload.STOPPED) {
        //enable navigation and reload iframe
        $('div#upload_in_progress').removeClass('upload_in_progress');
        $('div#upload_in_progress').html('');
        $('#dirbroswer').show();

        window.frames[0].location.reload();

        //add refresh uploader button
        var refreshBtn = document.createElement('a');
        refreshBtn.className = 'plupload_button plupload_refresh';
        refreshBtn.id = 'plupload_refresh';
        refreshBtn.innerHTML = '<?php echo JText::_('JBS_UPLOADER_REFRESH_UPLOADER')  ?>';
        refreshBtn.href = '#',
        refreshBtn.onclick = function (up) {
        initUploader();
        }

        $('.plupload_filelist_footer').prepend(refreshBtn);

        }
        log('<b>[StateChanged]</b>', up.state == plupload.STARTED ? "STARTED" : "STOPPED");
        },

        QueueChanged: function(up) {
        // Called when the files in queue are changed by adding/removing files
        log('<b>[QueueChanged]</b>');
        },

        FilesAdded: function(up, files) {
        // Callced when files are added to queue
        log('<b>[FilesAdded]</b>');

        plupload.each(files, function(file) {
        log('  File:', file);
        });
        },

        FilesRemoved: function(up, files) {
        // Called when files where removed from queue
        log('[FilesRemoved]');

        plupload.each(files, function(file) {
        log('  File:', file);
        });
        },

        FileUploaded: function(up, file, info) {
        // Called when a file has finished uploading
        handleUpStatus(up, file, info, false);
        },

        ChunkUploaded: function(up, file, info) {
        // Called when a file chunk has finished uploading
        handleUpStatus(up, file, info, true);
        },

        Error: function(up, args) {
        // Called when a error has occured
        log('[error] ', args);
        }
        }

        });
        }
        //log events
        function log() {
        var str = "";

        plupload.each(arguments, function(arg) {
        var row = "";

        if (typeof(arg) != "string") {
        plupload.each(arg, function(value, key) {
        // Convert items in File objects to human readable form
        if (arg instanceof plupload.File) {
        // Convert status to human readable
        switch (value) {
        case plupload.QUEUED:
        value = 'QUEUED';
        break;

        case plupload.UPLOADING:
        value = 'UPLOADING';
        break;

        case plupload.FAILED:
        value = 'FAILED';
        break;

        case plupload.DONE:
        value = 'DONE';
        break;
        }
        }

        if (typeof(value) != "function") {
        row += (row ? ', ' : '') + key + ' = ' + value;
        }
        });

        str += row + " ";
        } else {
        str += arg + " ";
        }
        });

        $('#log').prepend(str + '<span class="log_sep"></span>');
        }



        // show/hide uploader log
        $("#log_btn").click(function () {
        $("#log").slideToggle('slow');
        });

        $('#delete_selected').click(function () {
        var cnfTxt = '<?php echo JText::_('JBS_DIR_BROSWER_CONFIRM_DELETE_MULTIPLE'); ?>';
        if(confirm(cnfTxt)) {
        var data = $('#dirbroswer').contents().find('form#delete_form').serialize();
        var action = 'index.php?option=com_mediamu&no_html=1&task=path.delete';
        ajaxReq(data, action);
        } else {
        return false;
        }

        });

        //add language support and initialize uploader
        addLanguageSupport();
        initUploader();
        });
        <?php
        $script = ob_get_contents();
        ob_clean();
        $this->_SCRIPT = $script;
    }

    /**
     *
     * Get the dependency Script
     *
     * @return string JavaScript code
     */
    public function getScript()
    {
        return $this->_SCRIPT;
    }

    /**
     * Clean Comma Separated Option
     * @param string $string Option to be cleaned
     * @return string
     */
    private function _cleanOption( $string )
    {
        return trim($string);
    }

}

?>