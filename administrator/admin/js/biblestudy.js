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
		
		$j(newReference).children('#text').attr('value', '');
		$j(newReference).children('#scripture').selectOptions('0');
		
		$j(newReference).append(deleteButton);
		$j(newReference).appendTo('#references');

		$j(".referenceDelete").bind('click', function() {
			$j(this).parent("#reference").remove();
			return false;
		})
		return false;
	});
	$j(".referenceDelete").click(function() {
		$j(this).parent("#reference").remove();
		return false;
	})
	
	
	/**
	 * @title Add Mediafile
	 */
	
	//Docman integration
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
	
	/**
	 * @title Templating Procedures
	 */

	//Determine the type of template, and route to that function
	$j('#type').change(function() {
		eval($j('#type option:selected').attr('value') + '()');
	});
	
	
	function canvasItemFunctions() {
		$j('#canvasDeleteItem').click(function() {
			//Delete Item, and update JSON string
			
			$j(this).parent('#canvasListItem').draggable(
					{
						handle: 'div#canvasDeleteItem'
					}
			);
				
		

		});
	}
	
	/**
	 * @desc Creates Controls for a item on the canvas.
	 */
	function canvasItemControls(itemLabel) {
		var itemOptions = '<div id="canvasItemOptions">&nbsp;</div>';
		var moveItem = '<div id="canvasMoveItem">&nbsp;</div>';
		var deleteItem = '<div id="canvasDeleteItem">&nbsp;</div>';
		
		$j('.canvasItem').append(itemOptions);
		$j('.canvasItem').append(moveItem);
		$j('.canvasItem').append(deleteItem);	
		$j('.canvasItem').append('<div class="canvasItemName">' + itemLabel + '</div>');
	
		canvasItemFunctions();
	}
	

	
	
	
	function tmplList () {
		var canvasListItem = '<div id="canvasListItem" class="canvasItem"></div>';
		
		$j('#tmplCanvas').append(canvasListItem);
		canvasItemControls('List Items');
	}
	
	function tmplListItem () {
		alert ('this is the teacher list setup');
	}
	
	function tmplSingleItem () {
		alert ('this is the teacher list setup');
	}
	
	function tmplModuleList () {
		alert ('this is the teacher list setup');
	}
	
	function tmplModuleItem () {
		alert ('this is the teacher list setup');
	}
	
	function tmplPopup () {
		alert ('this is the teacher list setup');
	}
});
