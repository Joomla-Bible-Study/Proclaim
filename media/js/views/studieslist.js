// Used for inline player hit count
var currentState = 'NONE'
var previousState = 'NONE'

var player = null

// Setup inline player to put in Listen mode
function playerReady (obj)
{
	player = document.getElementById(obj.id)
	player.addModelListener('STATE', 'stateListener')
}

// Listener for inline player
function stateListener (obj)
{ //IDLE, BUFFERING, PLAYING, PAUSED, COMPLETED
	currentState = obj.newstate
	previousState = obj.oldstate

	if ((currentState == 'PLAYING') && (previousState != 'PAUSED'))
	{
		//Register a play
		$j.ajax({
			type: 'POST',
			url: 'index.php?option=com_proclaim&controller=studieslist&task=playHit&tmpl=component&id=' +
				obj.id,
		})
	}
}
