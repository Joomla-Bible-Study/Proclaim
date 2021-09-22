var $j = jQuery.noConflict();

$j(document).ready(function () {
	setDefaults();
	var files;

	$j('#dirStatus').ajaxStart(function () {
		$j(this).html('Searching...')
	});
	$j('input[name=directoryname]').blur(function () {
		getFolder($j(this).val());
	});

	getFolder($j('input[name=directoryname]').val());

	//Helper Functions
	function getFolder(folder) {
		$j.getJSON(
			"index.php",
			{
				option: 'com_proclaimimport',
				controller: 'ajax',
				format: 'raw',
				task: 'getFolder',
				folder: folder
			},
			function (json) {
				if (json == null) {
					$j('#dirStatus').html("invalid folder");
					return;
				}
				$j('#dirStatus').html("Files: " + json.fileCount);
			}
		);
	}

	$j('.viewFile').click(function () {
		eval('var viewFile = ' + $j(this).attr('name'));
		//alert(viewFile.filename);
		return false;
	});

	//Global move files
	$j('#moveFiles').mouseup(function () {
		$j('.moveFile').attr('checked', !$j(this).attr('checked'));
	});
	//Global Custom Text
	$j('.globalCustom').change(function () {
		var field = $j(this).attr('name');
		var newValue = $j(this).val();
		$j('.availableTags[name=' + field + ']').val('- Use element from ID3 -');
		$j('.existing[name=' + field + ']').val('- Use existing data -');
		$j('.' + field).val(newValue);

	});

	//Global Id3 Information
	$j('.availableTags').change(function () {
		var field = $j(this).attr('name');
		var jsonField = $j(this).val();
		$j('.globalCustom[name=' + field + ']').val('');
		$j('.existing[name=' + field + ']').val('- Use existing data -');
		$j('.' + field).val(function () {
			try {
				eval('var newValue = file' + $j(this).attr('alt') + '.' + jsonField + ';');
			} catch (e) {
				var newValue = '';
			}
			return newValue;
		});
	});

	//Global Existing Information
	$j('.existing').change(function () {
		var field = $j(this).attr('name');
		$j('.globalCustom[name=' + field + ']').val('');
		$j('.availableTags[name=' + field + ']').val('- Use element from ID3 -');
		$j('.' + field).val($j(this).find(':selected').text());
	});

	//Start the import
	$j('#import').click(function () {
		var json = $j('#files tr:last').find('input').serializeArray();
		$j.ajax(
			{
				type: "POST",
				url: "index.php?option=com_proclaimimport&controller=ajax&format=raw&task=importFile",
				data: json,
				success: function (response) {
				}
			}
		);

		//});
		return false;
	});
	function setDefaults() {
		$j('.availableTags').each(function () {
			var field = $j(this).attr('name');
			var jsonField = $j(this).val();

			$j('.' + field).val(function () {
				try {
					eval('var newValue = file' + $j(this).attr('alt') + '.' + jsonField + ';');
				} catch (e) {
					var newValue = '';
				}
				return newValue;
			});
		});
	}
});
