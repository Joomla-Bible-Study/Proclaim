$(function () {
    $('.btnPlay').on('click', function () {
        const mediaId = $(this).attr('alt');
        const mediaid = $('#media-' + mediaId);

        $('.inlinePlayer:not(#media-' + mediaId + ')').hide();
        $('.inlinePlayer').html('');
        mediaid.toggle();
        mediaid.load(
            'index.php?option=com_proclaim&view=cwmstudieslist&controller=cwmstudieslist&task=inlinePlayer&tmpl=component',
        );
        return false;
    });

    if (Modernizr.touch) {
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
        const newReference = $('#reference').clone();
        const deleteButton = '<a href="#" class="referenceDelete">Delete</a>';

        $(newReference).children('#text').attr('value', '');
        $(newReference).children('#scripture').selectOptions('0');

        $(newReference).append(deleteButton);
        $(newReference).appendTo('#references');

        $('.referenceDelete').on('click', function () {
            $(this).parent('#reference').remove();
            return false;
        });
        return false;
    });

    $('.referenceDelete').on('click', function () {
        $(this).parent('#reference').remove();
        return false;
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

        targetImage.attr('src', activeDir.join('/') + '/' + _.escape($(this).val()));
    });

    $('#type').on('change', function () {
        const selectedFunction = $('#type').find('option:selected').attr('value');
        if (typeof window[selectedFunction] === 'function') {
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
        const itemOptions = '<div id="canvasItemOptions">&nbsp;</div>';
        const moveItem = '<div id="canvasMoveItem">&nbsp;</div>';
        const deleteItem = '<div id="canvasDeleteItem">&nbsp;</div>';
        const canvasItem = $('.canvasItem');

        canvasItem.append(itemOptions);
        canvasItem.append(moveItem);
        canvasItem.append(deleteItem);
        canvasItem.append('<div class="canvasItemName">' + itemLabel + '</div>');

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
        try {
            new URL(url);
            location.href = url;
        } catch (e) {
            console.error('Invalid URL:', url);
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
        element.style.display = 'block';
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