jQuery(document).ready(function () {
	window.uploader = new plupload.Uploader({
		runtimes: 'html5,flash,silverlight,html4',
		multi_selection: false,
		browse_button: 'btn-add-file',
		drop_element: 'uploader-dropzone',
		flash_swf_url: '../includes/js/Moxie.swf',
		silverlight_xap_url: '../includes/js/Moxie.xap',

		init: {
			PostInit: function () {
				document.getElementById('btn-upload').onclick = function () {
					uploader.start();
					jQuery(this).attr('disabled', 'disabled');
					return false;
				};
			},

			FilesAdded: function (up, files) {
				jQuery("#uploader-file").val(files[0].name);
				jQuery('#btn-upload').removeAttr('disabled');
			},

			Browse: function () {
				uploader.splice();
			},

			BeforeUpload: function () {
				var progress = jQuery('#upload-progress');
				progress.show();
				progress.addClass('active').addClass('progress-striped').removeClass('progress-success');
			},

			UploadProgress: function () {
				var percent = uploader.total.percent;
				jQuery('#upload-progress>.bar').css('width', percent + "%");
			},

			FileUploaded: function (uploader, file, info) {
				var response = jQuery.parseJSON(info.response);
				if (response !== false) {
					jQuery('#jform_params_filename').val(response.data.filename);
					jQuery('#jform_params_size').val(response.data.size);
				}
				else {
					var uprogress = jQuery('#upload-progress');
					uprogress.addClass('progress-success').removeClass('active').removeClass('progress-striped');
					uprogress.text('Upload Error');
				}
			},

			UploadComplete: function () {
				jQuery('#btn-upload').removeAttr('disabled');
				var uprogress = jQuery('#upload-progress');
				uprogress.addClass('progress-success').removeClass('active').removeClass('progress-striped');
				uprogress.text('Uploaded');
			},

			Error: function (up, err) {
				document.getElementById('console').appendChild(document.createTextNode("\nError #" + err.code + ": " + err.message));
			}
		}
	});
});
