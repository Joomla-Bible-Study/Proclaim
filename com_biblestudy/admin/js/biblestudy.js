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


/**
 * Install Assets Joomla 1.6 
 *
 * @return	bool	
 * @since	0.4.
 */
function assetinstall(event){

  var mySlideInstall = new Fx.Slide('assetinstall');
  mySlideInstall.hide();
  $('assetinstall').setStyle('display', 'block');
  mySlideInstall.toggle();

	var pb3 = new dwProgressBar({
		container: $('pb3'),
		startPercentage: 2,
		speed: 1000,
		boxID: 'pb3-box',
		percentageID: 'pb3-perc',
		displayID: 'text',
		displayText: false
	});

  var d = new Ajax( 'components/com_biblestudy/install/biblestudy.assets.php', {
    method: 'get',
		noCache: true,
    onComplete: function( response ) {
			pb3.set(100);
			pb3.finish();
			done();
    }
  }).request();

};

/**
 * Show done message
 *
 * @return	bool
 * @since	0.4.
 */
function done(event){

  var d = new Ajax( 'components/com_biblestudy/install/done.php', {
    method: 'get',
    onComplete: function( response ) {
			var mySlideDone = new Fx.Slide('done');
			mySlideDone.hide();
			$('done').setStyle('display', 'block');
			mySlideDone.toggle();
    }
  }).request();

};




