$j(document).ready( function() {
	//Accordion
	$j('#templateTagsContainer').accordion({
		header: "h3"
	});
	
	
	//Tag events
	$j('.tmplTag').draggable();
});