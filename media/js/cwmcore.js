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

/**
 * Accessibility: Modal Focus Management
 * Traps focus within modal dialogs and restores focus on close
 */
const ProclaimA11y = {
    /** Store the element that opened the modal */
    previousActiveElement: null,

    /**
     * Get all focusable elements within a container
     * @param {HTMLElement} container - The container element
     * @returns {Array} Array of focusable elements
     */
    getFocusableElements: function(container) {
        const focusableSelectors = [
            'a[href]',
            'button:not([disabled])',
            'input:not([disabled]):not([type="hidden"])',
            'select:not([disabled])',
            'textarea:not([disabled])',
            '[tabindex]:not([tabindex="-1"])',
            '[contenteditable="true"]'
        ].join(', ');

        return Array.from(container.querySelectorAll(focusableSelectors))
            .filter(el => el.offsetParent !== null); // Only visible elements
    },

    /**
     * Initialize focus trap for a modal
     * @param {HTMLElement|string} modal - Modal element or selector
     */
    trapFocus: function(modal) {
        const modalElement = typeof modal === 'string' ? document.querySelector(modal) : modal;
        if (!modalElement) return;

        // Store the currently focused element
        this.previousActiveElement = document.activeElement;

        // Set aria-modal for screen readers
        modalElement.setAttribute('aria-modal', 'true');
        modalElement.setAttribute('role', 'dialog');

        const focusableElements = this.getFocusableElements(modalElement);
        if (focusableElements.length === 0) return;

        const firstFocusable = focusableElements[0];
        const lastFocusable = focusableElements[focusableElements.length - 1];

        // Focus the first focusable element
        firstFocusable.focus();

        // Handle Tab key to trap focus
        const handleKeyDown = (e) => {
            if (e.key === 'Escape') {
                this.releaseFocus(modalElement, handleKeyDown);
                // Trigger close if modal has a close method
                const closeBtn = modalElement.querySelector('[data-dismiss="modal"], .btn-close, .close');
                if (closeBtn) closeBtn.click();
                return;
            }

            if (e.key !== 'Tab') return;

            // Update focusable elements (in case of dynamic content)
            const currentFocusable = this.getFocusableElements(modalElement);
            if (currentFocusable.length === 0) return;

            const first = currentFocusable[0];
            const last = currentFocusable[currentFocusable.length - 1];

            if (e.shiftKey) {
                // Shift + Tab
                if (document.activeElement === first) {
                    e.preventDefault();
                    last.focus();
                }
            } else {
                // Tab
                if (document.activeElement === last) {
                    e.preventDefault();
                    first.focus();
                }
            }
        };

        modalElement.addEventListener('keydown', handleKeyDown);
        modalElement._a11yKeyHandler = handleKeyDown;
    },

    /**
     * Release focus trap and restore previous focus
     * @param {HTMLElement|string} modal - Modal element or selector
     * @param {Function} handler - The keydown handler to remove (optional)
     */
    releaseFocus: function(modal, handler) {
        const modalElement = typeof modal === 'string' ? document.querySelector(modal) : modal;
        if (!modalElement) return;

        // Remove the keydown handler
        if (handler) {
            modalElement.removeEventListener('keydown', handler);
        } else if (modalElement._a11yKeyHandler) {
            modalElement.removeEventListener('keydown', modalElement._a11yKeyHandler);
            delete modalElement._a11yKeyHandler;
        }

        // Restore focus to the previous element
        if (this.previousActiveElement && this.previousActiveElement.focus) {
            this.previousActiveElement.focus();
            this.previousActiveElement = null;
        }
    },

    /**
     * Announce message to screen readers
     * @param {string} message - Message to announce
     * @param {string} priority - 'polite' or 'assertive'
     */
    announce: function(message, priority = 'polite') {
        let announcer = document.getElementById('proclaim-a11y-announcer');
        if (!announcer) {
            announcer = document.createElement('div');
            announcer.id = 'proclaim-a11y-announcer';
            announcer.setAttribute('aria-live', priority);
            announcer.setAttribute('aria-atomic', 'true');
            announcer.className = 'visually-hidden';
            announcer.style.cssText = 'position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);border:0;';
            document.body.appendChild(announcer);
        }
        announcer.setAttribute('aria-live', priority);
        announcer.textContent = '';
        // Use setTimeout to ensure screen readers pick up the change
        setTimeout(() => { announcer.textContent = message; }, 100);
    }
};

// Auto-initialize focus trap for Bootstrap modals
document.addEventListener('DOMContentLoaded', function() {
    // Listen for Bootstrap modal events
    document.addEventListener('shown.bs.modal', function(e) {
        ProclaimA11y.trapFocus(e.target);
    });

    document.addEventListener('hidden.bs.modal', function(e) {
        ProclaimA11y.releaseFocus(e.target);
    });

    // Also support jQuery Bootstrap modals if present
    if (typeof jQuery !== 'undefined') {
        jQuery(document).on('shown.bs.modal', function(e) {
            ProclaimA11y.trapFocus(e.target);
        });
        jQuery(document).on('hidden.bs.modal', function(e) {
            ProclaimA11y.releaseFocus(e.target);
        });
    }
});