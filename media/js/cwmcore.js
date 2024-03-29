/**
 * @package     Proclaim.Core
 * @subpackage  com_proclaim
 *
 * @copyright   Copyright (C) 2005 - 2023 Open Source Matters, Inc. All rights
 *   reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
$(function () {
		$('.btnPlay').on('click', function () {
				const mediaId = $(this).attr('alt')
				const url = $(this).attr('href')
				const mediaid = $('#media-' + mediaId)

				$('.inlinePlayer:not(#media-' + mediaId + ')').hide()
				$('.inlinePlayer').html('')
				mediaid.toggle()
				mediaid.load(
					'index.php?option=com_proclaim&view=cwmstudieslist&controller=cwmstudieslist&task=inlinePlayer&tmpl=component',
				)
				return false
			},
		)

	if (Modernizr.touch)
	{
		const jbsmcloseoverlay = $('.jbsmclose-overlay')

		// show the close overlay button
		jbsmcloseoverlay.removeClass('hidden')
		// handle the adding of hover class when clicked
		$('.jbsmimg').on('click', function (e) {
				if (!$(this).hasClass('hover'))
				{
					$(this).addClass('hover')
				}
			},
		)
		// handle the closing of the overlay
		jbsmcloseoverlay.on('click', function (e) {
				e.preventDefault()
				e.stopPropagation()
				if ($(this).closest('.jbsmimg').hasClass('hover'))
				{
					$(this).closest('.jbsmimg').removeClass('hover')
				}
			},
		)
	}
	else
	{
		// handle the mouseenter functionality
		$('.jbsmimg').on('mouseenter', function () {
				$(this).addClass('hover')
			},
		)
			// handle the mouseleave functionality
			.mouseleave(function () {
					$(this).removeClass('hover')
				},
			)
	}

		/**
		 * @title Add Study
		 * @deprecated this function is no longer used.
		 */
		$('#addReference').on('click', function () {
				const newReference = $('#reference').clone()
				const deleteButton = '<a href="#" class="referenceDelete">Delete</a>'

				$(newReference).children('#text').attr('value', '')
				$(newReference).children('#scripture').selectOptions('0')

				$(newReference).append(deleteButton)
				$(newReference).appendTo('#references')

				$('.referenceDelete').on('click', function () {
						$(this).parent('#reference').remove()
						return false
					},
				)
				return false
			},
		)
		$('.referenceDelete').on('click', function () {
				$(this).parent('#reference').remove()
				return false
			},
		)

		$('.imgChoose').on('change', function () {
				const targetImage = $('#img' + $(this).attr('id'))
				const activeDir = targetImage.attr('src').split('/')
				activeDir.pop() //Remove the previous image

				if (parseInt($(this).val()) === 0)
				{
					$.find(targetImage).hide()
				}
				else
				{
					$.find(targetImage).show()
				}

			$.find(targetImage).attr('src', activeDir.join('/') + '/' + $(this).val())
			},
		)

		/**
		 * @title Templating Procedures
		 */

		//Determine the type of template, and route to that function
		$('#type').on('change', function () {
				eval($('#type').find('option:selected').attr('value') + '()')
			},
		)

		function canvasItemFunctions()
		{
			$('#canvasDeleteItem').click(function () {
					//Delete Item, and update JSON string

					$(this).parent('#canvasListItem').draggable(
						{
							handle: 'div#canvasDeleteItem',
						},
					)

				},
			)
	}

	/**
	 * @desc Creates Controls for a item on the canvas.
	 */
	function canvasItemControls(itemLabel)
	{
		var itemOptions = '<div id="canvasItemOptions">&nbsp;</div>'
		var moveItem = '<div id="canvasMoveItem">&nbsp;</div>'
		var deleteItem = '<div id="canvasDeleteItem">&nbsp;</div>'
		var canvasItem = $('.canvasItem')

		canvasItem.append(itemOptions)
		canvasItem.append(moveItem)
		canvasItem.append(deleteItem)
		canvasItem.append('<div class="canvasItemName">' + itemLabel + '</div>')

		canvasItemFunctions()
	}

		function tmplList()
		{
			const canvasListItem = '<div id="canvasListItem" class="canvasItem"></div>'

			$('#tmplCanvas').append(canvasListItem)
			canvasItemControls('List Items')
		}

		function tmplListItem()
		{
			alert('this is the teacher list setup')
		}

		function tmplSingleItem()
		{
			alert('this is the teacher list setup')
		}

		function tmplModuleList()
		{
			alert('this is the teacher list setup')
		}

		function tmplModuleItem()
		{
			alert('this is the teacher list setup')
		}

		function tmplPopup()
		{
			alert('this is the teacher list setup')
		}
	}
)

function goTo()
{
	let sE = null, url
	if (document.getElementById)
	{
		sE = document.getElementById('urlList')
	}
	else
	{
		if (document.getElementsByName('urlList'))
		{
			sE = document.getElementsByName('urlList')
		}
	}

	if (sE && (url = sE.options[sE.selectedIndex].value))
	{
		location.href = url
	}
}

function ReverseDisplay()
{
	const ele = document.getElementById('scripture')
	const text = document.getElementById('heading')
	if (ele.style.display === 'block')
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

function HideContent(d)
{
	document.getElementById(d).style.display = 'none'
}

function ShowContent(d)
{
	document.getElementById(d).style.display = 'block'
}

function ReverseDisplay2(d)
{
	if (document.getElementById(d).style.display === 'none')
	{
		document.getElementById(d).style.display = 'block'
	}
	else
	{
		document.getElementById(d).style.display = 'none'
	}
}

function decOnly(i)
{
	let t = i.value
	if (t.length > 0)
	{
		t = t.replace(/[^\d\.]+/g, '')
	}

	const s = t.split('.')
	if (s.length > 1)
	{
		s[1] = s[0] + '.' + s[1]
		s.shift(s)
	}

	i.value = s.join('')
}

function bandwidth(bytees, type)
{
	let value = bytees
	let res
	if (!isNaN(value) && (value !== ''))
	{
		if (type.toUpperCase() === 'KB')
		{
			value *= 1024
		}
		else
		{
			if (type.toUpperCase() === 'MB')
			{
				value *= [Math.pow(1024, 2)]
			}
			else
			{
				if (type.toUpperCase() === 'GB')
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

function transferFileSize()
{
	const size = document.getElementById('Text1').value
	const ty = document.getElementById('Select1').value
	const ss = bandwidth(size, ty)
	if (ss === 'error')
	{
		alert('Numbers only please.')
		return false
	}
	else
	{
		document.getElementById('jform_params_size').value = ss
		return true
	}
}
