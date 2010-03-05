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
	});
	
	$j('.imgChoose').change(function(){
		var targetImage = $j('#img'+$j(this).attr('id'));
		var activeDir = targetImage.attr('src').split('/');
		activeDir.pop(); //Remove the previous image
		
		if($j(this).val().substr(0,1) == 0) {
			targetImage.hide();
		} else {
			targetImage.show();
		}

		targetImage.attr('src', activeDir.join('/') + '/' + $j(this).val());	
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
function goTo() {
var sE = null, url;
if(document.getElementById) {
sE = document.getElementById('urlList');
} else if(document.all) {
sE = document.all['urlList'];
}
if(sE && (url = sE.options[sE.selectedIndex].value)) {
location.href = url;
}
}


// <[CDATA[
function tryStartPlayer(pid) {
   try {
       new allvideos.API(pid).play();
       // Old v1.1 version AvrPlay(pid);
   } catch (e) {
       // Starting the player may fail, if the page is not yet
       // fully loaded and it does not exist yet.
       // So we retry it a little bit later.
       window.setTimeout('tryStartPlayer("'+pid+'")', 500);
   }
}

function startPlayerOnce(id) {
   // Search for the cookie
   var nameEQ = 'pseen_'+id+'=';
   var ca = document.cookie.split(';');
   // Loop over all cookie vars
   for (var i = 0; i < ca.length; i++) {
      var c = ca[i];
      while (c.charAt(0) == ' ') {
          c = c.substring(1, c.length);
      }
      if (c.indexOf(nameEQ) == 0) {
         // If we arrive here, We found the
         // cookie and therefore simply return
         return;
      }
   }
   // If we arrive here, cookie wasn't found, so
   // let's create it and start the player.
   // The new cookie expires after one year.
   var now = new Date();
   now.setTime(now.getTime() + (365 * 24 * 60 * 60 * 1000));
   var expires = ' expires='+now.toGMTString()+';';
   // If you want the cookie last for only a session lifetime
   // (next visit, the video plays once again) then uncomment
   // the following line:
   //expires = '';
   document.cookie = nameEQ + '1;'+expires+' path=/';
   tryStartPlayer('p_'+id);
}
// ]]>

