// Used for inline player hit count
var currentState = "NONE";
var previousState = "NONE";

var player = null;
// Setup inline player to put in Listen mode
function playerReady(obj) {
	player = document.getElementById(obj.id);
	player.addModelListener("STATE", "stateListener");
}

// Listener for inline player
function stateListener(obj) { //IDLE, BUFFERING, PLAYING, PAUSED, COMPLETED
	currentState = obj.newstate;
	previousState = obj.oldstate;

	if ((currentState == "PLAYING") && (previousState != "PAUSED")) {
		//Register a play
		$j.ajax({
			type: "POST",
			url: "index.php?option=com_proclaim&controller=studieslist&task=playHit&tmpl=component&id=" + obj.id
		});
		// Need "mywin ="  IE and google popup blocker will prevent
		// mywin = window.open('index.php?option=com_proclaim&view=popup&Itemid=7&mediaid='+obj.id+'&close=true', 'newwindow','width=100,height=100');

		//alert('Playing ' + obj.id);
	}
}
