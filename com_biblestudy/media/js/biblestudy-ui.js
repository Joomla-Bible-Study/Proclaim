$j(document).ready(function () {
	//Accordion
	$j('#templateTagsContainer').accordion({
		header: "h3"
	});


	//UI INIT

	$j('#tabs').tabs({
		selected: 0,
		disabled: [1]
	});

	$j('.tmplTag').hover(
		function () {
			$j(this).css('cursor', 'move');
			$j(this).addClass('tmplTagSelected');
		},
		function () {
			$j(this).removeClass('tmplTagSelected');
		}
	);

	$j('.tmplTag').draggable({
		revert: 'invalid',
		cursor: 'move'
	});

	$j('#tmplCanvas').droppable({
		over: function () {
			$j(this).animate({
				opacity: "0.7"
			});
		},
		out: function () {
			$j(this).animate({
				opacity: "1"
			});
		},
		drop: function () {
			$j(this).animate({
				opacity: "1"
			});
		}
	});


	//New Approach
	//Initialize
	var canvasControls = '<div id="cursor"><div id="cursorLabel">Add</div><div id="cursorInsertRight"></div><div id="cursorInsertBottom"></div></div>';

	$j('#tmplCanvas:first-child ul li').append(canvasControls);

	/*	$j('.canvasRow').hover(
	 function() {
	 $j(this).append(canvasControls);
	 },
	 function() {
	 $j(this).children('#cursor').remove();
	 }
	 );*/

	$j('#cursorInsertRight').bind('click', function () {
		var newElement = '<div class="canvasElement"></div>';
		$j(this).parent().before(newElement);
	});

	$j('#cursorInsertBottom').click(function () {
		var newRow = '<li class="canvasRow"></li>';
		$j(this).parent().parent().parent().append(newRow);
	});

});
