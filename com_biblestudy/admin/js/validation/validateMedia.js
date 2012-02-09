$j(document).ready(function() {
	$j('#adminForm').validate();
	
	$j('#size').rules('add', {
		required: true,
		messages: {
			required: "This field is required"
		}
	})
	
});