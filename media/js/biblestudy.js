jQuery(document).ready(function () {
	jQuery('.btnPlay').click(function () {
		var mediaId = jQuery(this).attr('alt')
		var url = jQuery(this).attr('href')
		var mediaid = jQuery('#media-' + mediaId)

		jQuery('.inlinePlayer:not(#media-' + mediaId + ')').hide()
		jQuery('.inlinePlayer').html('')
		mediaid.toggle()
		mediaid.load(
			'index.php?option=com_proclaim&view=studieslist&controller=studieslist&task=inlinePlayer&tmpl=component')
		return false
	})

	if (Modernizr.touch)
	{
		// show the close overlay button
		jQuery('.jbsmclose-overlay').removeClass('hidden')
		// handle the adding of hover class when clicked
		jQuery('.jbsmimg').click(function (e) {
			if (!jQuery(this).hasClass('hover'))
			{
				jQuery(this).addClass('hover')
			}
		})
		// handle the closing of the overlay
		jQuery('.jbsmclose-overlay').click(function (e) {
			e.preventDefault()
			e.stopPropagation()
			if (jQuery(this).closest('.jbsmimg').hasClass('hover'))
			{
				jQuery(this).closest('.jbsmimg').removeClass('hover')
			}
		})
	}
	else
	{
		// handle the mouseenter functionality
		jQuery('.jbsmimg').mouseenter(function () {
			jQuery(this).addClass('hover')
		})
			// handle the mouseleave functionality
			.mouseleave(function () {
				jQuery(this).removeClass('hover')
			})
	}

	/**
	 * @title Add Study
	 */
	jQuery('#addReference').click(function () {
		var newReference = jQuery('#reference').clone()
		var deleteButton = '<a href="#" class="referenceDelete">Delete</a>'

		jQuery(newReference).children('#text').attr('value', '')
		jQuery(newReference).children('#scripture').selectOptions('0')

		jQuery(newReference).append(deleteButton)
		jQuery(newReference).appendTo('#references')

		jQuery('.referenceDelete').bind('click', function () {
			jQuery(this).parent('#reference').remove()
			return false
		})
		return false
	})
	jQuery('.referenceDelete').click(function () {
		jQuery(this).parent('#reference').remove()
		return false
	})

	jQuery('.imgChoose').change(function () {
		var targetImage = jQuery('#img' + jQuery(this).attr('id'))
		var activeDir = targetImage.attr('src').split('/')
		activeDir.pop() //Remove the previous image

		if (jQuery(this).val().substr(0, 1) == 0)
		{
			targetImage.hide()
		}
		else
		{
			targetImage.show()
		}

		targetImage.attr('src', activeDir.join('/') + '/' + jQuery(this).val())
	})

	/**
	 * @title Templating Procedures
	 */

	//Determine the type of template, and route to that function
	jQuery('#type').change(function () {
		eval(jQuery('#type').find('option:selected').attr('value') + '()')
	})

	function canvasItemFunctions ()
	{
		jQuery('#canvasDeleteItem').click(function () {
			//Delete Item, and update JSON string

			jQuery(this).parent('#canvasListItem').draggable(
				{
					handle: 'div#canvasDeleteItem',
				},
			)

		})
	}

	/**
	 * @desc Creates Controls for a item on the canvas.
	 */
	function canvasItemControls (itemLabel)
	{
		var itemOptions = '<div id="canvasItemOptions">&nbsp;</div>'
		var moveItem = '<div id="canvasMoveItem">&nbsp;</div>'
		var deleteItem = '<div id="canvasDeleteItem">&nbsp;</div>'
		var canvasItem = jQuery('.canvasItem')

		canvasItem.append(itemOptions)
		canvasItem.append(moveItem)
		canvasItem.append(deleteItem)
		canvasItem.append('<div class="canvasItemName">' + itemLabel + '</div>')

		canvasItemFunctions()
	}

	function tmplList ()
	{
		var canvasListItem = '<div id="canvasListItem" class="canvasItem"></div>'

		jQuery('#tmplCanvas').append(canvasListItem)
		canvasItemControls('List Items')
	}

	function tmplListItem ()
	{
		alert('this is the teacher list setup')
	}

	function tmplSingleItem ()
	{
		alert('this is the teacher list setup')
	}

	function tmplModuleList ()
	{
		alert('this is the teacher list setup')
	}

	function tmplModuleItem ()
	{
		alert('this is the teacher list setup')
	}

	function tmplPopup ()
	{
		alert('this is the teacher list setup')
	}
})

function goTo ()
{
	var sE = null, url
	if (document.getElementById)
	{
		sE = document.getElementById('urlList')
	}
	else
	{
		if (document.all)
		{
			sE = document.all['urlList']
		}
	}
	if (sE && (url = sE.options[sE.selectedIndex].value))
	{
		location.href = url
	}
}

function ReverseDisplay ()
{
	var ele = document.getElementById('scripture')
	var text = document.getElementById('heading')
	if (ele.style.display == 'block')
	{
		ele.style.display = 'none'
		text.innerHTML = 'show'
	}
	else
	{
		ele.style.display = 'block'
		text.innerHTML = 'hide'
	}
}

function HideContent (d)
{
	document.getElementById(d).style.display = 'none'
}

function ShowContent (d)
{
	document.getElementById(d).style.display = 'block'
}

function ReverseDisplay2 (d)
{
	if (document.getElementById(d).style.display == 'none')
	{
		document.getElementById(d).style.display = 'block'
	}
	else
	{
		document.getElementById(d).style.display = 'none'
	}
}

function decOnly (i)
{
	var t = i.value
	if (t.length > 0)
	{
		t = t.replace(/[^\d\.]+/g, '')
	}
	var s = t.split('.')
	if (s.length > 1)
	{
		s[1] = s[0] + '.' + s[1]
		s.shift(s)
	}
	i.value = s.join('')
}

function bandwidth (bytees, type)
{
	var value = bytees
	var res
	if (!isNaN(value) && (value != ''))
	{
		if (type == 'KB')
		{
			value *= 1024
		}
		else
		{
			if (type == 'MB')
			{
				value *= [Math.pow(1024, 2)]
			}
			else
			{
				if (type == 'GB')
				{
					value *= [Math.pow(1024, 3)]
				}
				else
				{
					return 'error'
				}
			}
		}

		res = parseInt(value)
		return res
	}
	else
	{
		return 'error'
	}
}

function transferFileSize ()
{
	var size = document.getElementById('Text1').value
	var ty = document.getElementById('Select1').value
	var ss = bandwidth(size, ty)
	if (ss == 'error')
	{
		alert('Numbers only please.')
		return false
	}
	else
	{
		document.getElementById('jform_params_size').value = ss;
		return true;
	}
}
