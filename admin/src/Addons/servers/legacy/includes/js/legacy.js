jQuery(document).ready(function ($) {
	window.uploader = new plupload.Uploader({
		runtimes: 'html5,flash,silverlight,html4',
		chunk_size : '1mb',
		unique_names : true,
		multi_selection: false,
		browse_button: 'btn-add-file',
		drop_element: 'uploader-dropzone',
		flash_swf_url: '../includes/js/Moxie.swf',
		silverlight_xap_url: '../includes/js/Moxie.xap',

		init: {
			PostInit: function () {
				// Called after initialization is finished and internal event handlers bound
				log('[PostInit]');

				document.getElementById('btn-upload').onclick = function () {
					uploader.start();
					$(this).attr('disabled', 'disabled');
					return false;
				};
			},

			FilesAdded: function (up, files) {
				$("#uploader-file").val(files[0].name);
				$('#btn-upload').removeAttr('disabled');
			},

			Browse: function () {
				uploader.splice();
			},

			BeforeUpload: function (up, file) {
				var progress = $('#upload-progress');
				progress.show();
				progress.addClass('active').addClass('progress-striped').removeClass('progress-success');
			},

			UploadProgress: function (up, file) {
				var percent = uploader.total.percent;
				$('#upload-progress>.bar').css('width', percent + "%");
			},

			FileUploaded: function (uploader, file, info) {
				var response = $.parseJSON(info.response);
				if (response.data !== '') {
					$('#jform_params_filename').val(response.data.filename);
					$('#jform_params_size').val(response.data.size);
				}
				else {
					var uprogress = $('#upload-progress');
					uprogress.addClass('progress-success').removeClass('active').removeClass('progress-striped');
					uprogress.text(response.error);
				}

				// Called when file has finished uploading
				log('[FileUploaded] File:', file, "Info:", info);
			},

			UploadComplete: function (up, files) {
				$('#btn-upload').removeAttr('disabled');
				var uprogress = $('#upload-progress');
				uprogress.addClass('progress-success').removeClass('active').removeClass('progress-striped');

				log('[UploadComplete]');
			},

			Error: function (up, err) {
				// Called when error occurs
				log('[Error] ', err);
			}
		}
	});

	function log() {
		var str = "";

		plupload.each(arguments, function (arg) {
			var row = "";

			if (typeof(arg) != "string") {
				plupload.each(arg, function (value, key) {
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
						row += (row ? ', ' : '') + key + '=' + value;
					}
				});

				str += row + " ";
			} else {
				str += arg + " ";
			}
		});

		var log = document.getElementById('console');
		log.innerHTML += str + "\n";
	}
});
