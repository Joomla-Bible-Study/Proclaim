$j(document).ready( function() {
	$j('#loading').ajaxStart( function() {
		$j(this).show();
	});
	$j('#loading').ajaxStop( function() {
		$j(this).hide();
	});
	
	// Docman integration
	$j('#docManCategories').change(function() {
			docManItems = $j('#docManItems');
			docManItems.removeOption(/./);
			var catId = $j('#docManCategories option:selected').attr('value');
			var url = 'index.php?option=com_biblestudy&controller=mediafilesedit&task=docmanCategoryItems&format=raw&catId=';
			// request the items
			$j.ajax( {
				dataType : "json",
				url : url + catId,
				success : function(data) {
					$j.each(data, function(entryIndex, entry) {
						docManItems.addOption(entry['id'], entry['name']);
						$j('#docManItemsContainer').show();
						
						$j('#articlesCategoriesContainer').hide();
						$j('#articlesItemsContainer').hide();
						
						$j('#articleSectionCategories').removeOption(/./);
						$j('#categoryItems').removeOption(/./);
						
						$j('#articleSections').selectOptions(1);
						
					})
				}
			});
		});
	
	// Articles Integration
	$j('#articlesSections').change(function() {
		$j('#articleSectionCategories').removeOption(/./);
		var secId = $j('#articlesSections option:selected').attr('value');
		var url = 'index.php?option=com_biblestudy&controller=mediafilesedit&task=articlesSectionCategories&format=raw&secId=' + secId;
		if(secId !== "") {
			$j.ajax( {
				dataType : "json",
				url : url,
				success : function(data) {
					$j.each(data, function(entryIndex, entry) {
						$j('#articleSectionCategories').addOption(entry['id'], entry['title']);
						$j('#articlesCategoriesContainer').show();	
						$j('#docManItemsContainer').hide();
						$j('#docManItems').removeOption(/./);
					})	
					refreshArticleItems();
				}
			});
			$j('#categoryItems').removeOption(/./);
		}else{
			//Hide all others
			$j('#articlesCategoriesContainer').hide();
			$j('#articlesItemsContainer').hide();
		}
	});
	
	$j('#articleSectionCategories')
			.change(
					function() {
						$j('#categoryItems').removeOption(/./);
						var catId = $j('#articleSectionCategories option:selected').attr('value');
						var url = 'index.php?option=com_biblestudy&controller=mediafilesedit&task=articlesCategoryItems&format=raw&catId=' + catId;
						$j.ajax({
							dataType : "json",
							url : url,
							success : function(data) {
								$j.each(data, function(entryIndex, entry) {
									$j('#categoryItems').addOption(entry['id'],	entry['title']);
									$j('#articlesItemsContainer').show();
								})
							}
						});
					});
	$j('#categoryItems').change( function() {
		$j('#docManItemsContainer').hide();
		$j('#docManItems').removeOption(/./);
	});
	
	function refreshArticleItems() {
		var catId = $j('#articleSectionCategories option:selected').attr('value');
		var url = 'index.php?option=com_biblestudy&controller=mediafilesedit&task=articlesCategoryItems&format=raw&catId=' + catId;
		$j.ajax({
			dataType : "json",
			url : url,
			success : function(data) {
				$j.each(data, function(entryIndex, entry) {
					$j('#categoryItems').addOption(entry['id'],	entry['title']);
					$j('#articlesItemsContainer').show();
				})
			}
		});
	}
});