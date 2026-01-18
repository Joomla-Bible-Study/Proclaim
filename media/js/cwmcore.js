$(function () {
    'use strict';

    $('.btnPlay').on('click', function () {
        const $this = $(this);
        const mediaId = $this.attr('alt');
        const $mediaid = $('#media-' + mediaId);

        $('.inlinePlayer:not(#media-' + mediaId + ')').hide();
        $('.inlinePlayer').html('');
        $mediaid.toggle();
        $mediaid.load(
            'index.php?option=com_proclaim&view=cwmstudieslist&controller=cwmstudieslist&task=inlinePlayer&tmpl=component'
        );
        return false;
    });

    // Check for touch support using native browser APIs
    const isTouchDevice = ('ontouchstart' in window) || (navigator.maxTouchPoints > 0);
    if (isTouchDevice) {
        const jbsmcloseoverlay = $('.jbsmclose-overlay');

        jbsmcloseoverlay.removeClass('hidden');

        $('.jbsmimg').on('click', function () {
            if (!$(this).hasClass('hover')) {
                $(this).addClass('hover');
            }
        });

        jbsmcloseoverlay.on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            if ($(this).closest('.jbsmimg').hasClass('hover')) {
                $(this).closest('.jbsmimg').removeClass('hover');
            }
        });
    } else {
        $('.jbsmimg').on('mouseenter', function () {
            $(this).addClass('hover');
        }).mouseleave(function () {
            $(this).removeClass('hover');
        });
    }

    $('#addReference').on('click', function () {
        const $newReference = $('#reference').clone();
        const $deleteButton = $('<a>', {
            href: '#',
            'class': 'referenceDelete',
            text: 'Delete'
        });

        $newReference.children('#text').attr('value', '');
        $newReference.children('#scripture').selectOptions('0');

        $newReference.append($deleteButton);
        $newReference.appendTo('#references');
        return false;
    });

    // Use event delegation for dynamically added delete buttons
    $('#references').on('click', '.referenceDelete', function (e) {
        e.preventDefault();
        $(this).parent('#reference').remove();
    });

    $('.imgChoose').on('change', function () {
        const targetImage = $('#img' + $(this).attr('id'));
        const activeDir = targetImage.attr('src').split('/');
        activeDir.pop();

        if (parseInt($(this).val()) === 0) {
            targetImage.hide();
        } else {
            targetImage.show();
        }

        // Safely escape the value - use underscore if available, otherwise basic HTML escape
        const escapeHtml = (str) => {
            if (typeof _ !== 'undefined' && typeof _.escape === 'function') {
                return _.escape(str);
            }
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        };
        targetImage.attr('src', activeDir.join('/') + '/' + escapeHtml($(this).val()));
    });

    $('#type').on('change', function () {
        const selectedFunction = $('#type').find('option:selected').attr('value');
        // Whitelist of allowed template functions to prevent arbitrary code execution
        const allowedFunctions = ['tmplList'];
        if (allowedFunctions.includes(selectedFunction) && typeof window[selectedFunction] === 'function') {
            window[selectedFunction]();
        }
    });

    function canvasItemFunctions()
    {
        $('#canvasDeleteItem').click(function () {
            $(this).parent('#canvasListItem').draggable({
                handle: 'div#canvasDeleteItem',
            });
        });
    }

    function canvasItemControls(itemLabel)
    {
        const $canvasItem = $('.canvasItem');

        // Create elements safely to prevent XSS
        $canvasItem.append($('<div>', { id: 'canvasItemOptions', html: '&nbsp;' }));
        $canvasItem.append($('<div>', { id: 'canvasMoveItem', html: '&nbsp;' }));
        $canvasItem.append($('<div>', { id: 'canvasDeleteItem', html: '&nbsp;' }));
        // Use .text() to safely escape itemLabel and prevent XSS
        $canvasItem.append($('<div>', { 'class': 'canvasItemName' }).text(itemLabel));

        canvasItemFunctions();
    }

    function tmplList()
    {
        const canvasListItem = '<div id="canvasListItem" class="canvasItem"></div>';

        $('#tmplCanvas').append(canvasListItem);
        canvasItemControls('List Items');
    }

    // Removed unused functions tmplListItem, tmplSingleItem, tmplModuleList, tmplModuleItem, tmplPopup
});

/**
 * Returns true if the URL is a relative path (does not begin with scheme or '//')
 */
function isSafeRelativeUrl(url) {
    // Only allow URLs that do not start with a scheme or '//'
    return typeof url === 'string' &&
        url.trim().length > 0 &&
        !/^[a-z][a-z0-9+.-]*:/.test(url) && // No scheme like http:, https:, javascript:, data:, etc.
        !/^\/\//.test(url); // Not protocol-relative
}

function goTo()
{
    let sE = null, url;
    if (document.getElementById) {
        sE = document.getElementById('urlList');
    } else {
        if (document.getElementsByName('urlList')) {
            sE = document.getElementsByName('urlList');
        }
    }

    if (sE && (url = sE.options[sE.selectedIndex].value)) {
        if (isSafeRelativeUrl(url)) {
            location.href = url;
        } else {
            alert('Navigation to external or potentially unsafe URL is not allowed.');
            console.error('Unsafe navigation attempt:', url);
        }
    }
}

function ReverseDisplay()
{
    const ele = document.getElementById('scripture');
    const text = document.getElementById('heading');
    if (ele.style.display === 'block') {
        ele.style.display = 'none';
        text.innerHTML = 'show';
    } else {
        ele.style.display = 'block';
        text.innerHTML = 'hide';
    }
}

function HideContent(d)
{
    document.getElementById(d).style.display = 'none';
}

function ShowContent(d)
{
    document.getElementById(d).style.display = 'block';
}

function ReverseDisplay2(d)
{
    const element = document.getElementById(d);
    if (element.style.display === 'none') {
        // Use 'contents' so children flow with parent's flex/grid layout
        element.style.display = 'contents';
    } else {
        element.style.display = 'none';
    }
}

function decOnly(i)
{
    let t = i.value;
    if (t.length > 0) {
        t = t.replace(/[^\d.]+/g, '');
    }

    const s = t.split('.');
    if (s.length > 1) {
        s[1] = s[0] + '.' + s[1];
        s.shift();
    }

    i.value = s.join('');
}

function bandwidth(bytees, type)
{
    let value = bytees;
    if (!isNaN(value) && (value !== '')) {
        switch (type.toUpperCase()) {
            case 'KB':
                value *= 1024;
                break;
            case 'MB':
                value *= Math.pow(1024, 2);
                break;
            case 'GB':
                value *= Math.pow(1024, 3);
                break;
            default:
                return 'error';
        }

        return parseInt(value);
    } else {
        return 'error';
    }
}

function transferFileSize()
{
    const size = document.getElementById('Text1').value;
    const ty = document.getElementById('Select1').value;
    const ss = bandwidth(size, ty);
    if (ss === 'error') {
        alert('Numbers only please.');
        return false;
    } else {
        document.getElementById('jform_params_size').value = ss;
        return true;
    }
}