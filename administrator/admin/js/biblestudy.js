$j(document).ready( function() {
	$j('.btnPlay').click( function() {
		var mediaId = $j(this).attr('alt');
		var url = $j(this).attr('href');
		
		$j('.inlinePlayer:not(#media-'+mediaId+')').hide();
		$j('.inlinePlayer').html('');
		$j('#media-' + mediaId).toggle();
		$j('#media-' + mediaId).load('index.php?option=com_biblestudy&view=studieslist&controller=studieslist&task=inlinePlayer&tmpl=component');
		return false;
	});
	
	/**
	 * @title Add Study
	 */
	$j('#addReference').click(function() {
		var newReference = $j('#reference').clone();
		var deleteButton = '<a href="#" class="referenceDelete">Delete</a>';
		
		$j(newReference).children('#chapter_begin').attr('value', '');
		$j(newReference).children('#verse_begin').attr('value', '');
		$j(newReference).children('#chapter_end').attr('value', '');
		$j(newReference).children('#verse_end').attr('value', '');
		
		$j(newReference).append(deleteButton);
		$j(newReference).appendTo('#references');

		$j(".referenceDelete").bind('click', function() {
			$j(this).parent("#reference").remove();
			return false;
		})
		return false;
	});
	
	
	/**
	 * @title Add Mediafile
	 */
	$j('#docManCategory').change(function() {
		var catId = $j('#docManCategory option:selected').attr('value');
		var url = 'index.php?option=com_biblestudy&controller=mediafilesedit&task=docmanCategoryItems&format=raw&catId='
		
		$j('#loading').ajaxStart(function() {
			$j(this).show();
		});
		$j('#loading').ajaxStop(function() {
			$j(this).hide();
		});
		// request the items
		$j.ajax({
			dataType: "json",
			url: url+catId,
			success: function(data){
				$j.each(data, function(entryIndex, entry) {
					$j("#docmanItems").addOption(entry['id'], entry['name']);
				})
			}	
		});
	});
});
