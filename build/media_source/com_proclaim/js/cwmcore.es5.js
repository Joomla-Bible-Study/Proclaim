(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {

      document.querySelectorAll('.btnPlay').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
          e.preventDefault();
          var mediaId = this.getAttribute('alt');
          var mediaEl = document.getElementById('media-' + mediaId);
          document.querySelectorAll('.inlinePlayer').forEach(function (el) {
            if (el.id !== 'media-' + mediaId) {
              el.style.display = 'none';
            }
            el.innerHTML = '';
          });
          if (mediaEl) {
            if (window.getComputedStyle(mediaEl).display === 'none') {
              mediaEl.style.display = 'block';
            } else {
              mediaEl.style.display = 'none';
            }
            fetch('index.php?option=com_proclaim&view=cwmstudieslist&controller=cwmstudieslist&task=inlinePlayer&tmpl=component').then(function (response) {
              if (!response.ok) {
                throw new Error('Network response was not ok');
              }
              return response.text();
            }).then(function (html) {
              mediaEl.innerHTML = html;
            }).catch(function (error) {
              console.error('Error loading content:', error);
            });
          }
        });
      });
      var isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
      if (isTouchDevice) {
        var jbsmcloseoverlay = document.querySelectorAll('.jbsmclose-overlay');
        jbsmcloseoverlay.forEach(function (el) {
          el.classList.remove('hidden');
        });
        document.querySelectorAll('.jbsmimg').forEach(function (el) {
          el.addEventListener('click', function () {
            if (!this.classList.contains('hover')) {
              this.classList.add('hover');
            }
          });
        });
        jbsmcloseoverlay.forEach(function (el) {
          el.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var closestImg = this.closest('.jbsmimg');
            if (closestImg && closestImg.classList.contains('hover')) {
              closestImg.classList.remove('hover');
            }
          });
        });
      } else {
        document.querySelectorAll('.jbsmimg').forEach(function (el) {
          el.addEventListener('mouseenter', function () {
            this.classList.add('hover');
          });
          el.addEventListener('mouseleave', function () {
            this.classList.remove('hover');
          });
        });
      }
      var addReferenceBtn = document.getElementById('addReference');
      if (addReferenceBtn) {
        addReferenceBtn.addEventListener('click', function (e) {
          e.preventDefault();
          var referenceTemplate = document.getElementById('reference');
          if (!referenceTemplate) return;
          var newReference = referenceTemplate.cloneNode(true);
          var deleteButton = document.createElement('a');
          deleteButton.href = '#';
          deleteButton.className = 'referenceDelete';
          deleteButton.textContent = 'Delete';
          var textInput = newReference.querySelector('#text');
          if (textInput) {
            textInput.value = '';
            textInput.setAttribute('value', '');
          }
          var scriptureSelect = newReference.querySelector('#scripture');
          if (scriptureSelect) {
            scriptureSelect.value = '0';
          }
          newReference.appendChild(deleteButton);
          var referencesContainer = document.getElementById('references');
          if (referencesContainer) {
            referencesContainer.appendChild(newReference);
          }
        });
      }
      var referencesContainer = document.getElementById('references');
      if (referencesContainer) {
        referencesContainer.addEventListener('click', function (e) {
          if (e.target.classList.contains('referenceDelete')) {
            e.preventDefault();
            var parent = e.target.closest('#reference');
            if (parent) {
              parent.remove();
            }
          }
        });
      }
      document.querySelectorAll('.imgChoose').forEach(function (el) {
        el.addEventListener('change', function () {
          var targetImage = document.getElementById('img' + this.id);
          if (!targetImage) return;
          var src = targetImage.getAttribute('src');
          var activeDir = [];
          if (src) {
            activeDir = src.split('/');
            activeDir.pop();
          }
          if (parseInt(this.value) === 0) {
            targetImage.style.display = 'none';
          } else {
            targetImage.style.display = '';
          }
          var escapeHtml = function escapeHtml(str) {
            if (typeof _ !== 'undefined' && typeof _.escape === 'function') {
              return _.escape(str);
            }
            var entityMap = {
              '&': '&amp;',
              '<': '&lt;',
              '>': '&gt;',
              '"': '&quot;',
              "'": '&#039;'
            };
            return String(str).replace(/[&<>"']/g, function (s) {
              return entityMap[s];
            });
          };
          if (activeDir.length > 0) {
            targetImage.setAttribute('src', activeDir.join('/') + '/' + escapeHtml(this.value));
          }
        });
      });
    });
    var ProclaimA11y = {
      previousActiveElement: null,
      getFocusableElements: function getFocusableElements(container) {
        var focusableSelectors = ['a[href]', 'button:not([disabled])', 'input:not([disabled]):not([type="hidden"])', 'select:not([disabled])', 'textarea:not([disabled])', '[tabindex]:not([tabindex="-1"])', '[contenteditable="true"]'].join(', ');
        return Array.from(container.querySelectorAll(focusableSelectors)).filter(function (el) {
          return el.offsetParent !== null;
        });
      },
      trapFocus: function trapFocus(modal) {
        var _this = this;
        var modalElement = typeof modal === 'string' ? document.querySelector(modal) : modal;
        if (!modalElement) return;
        this.previousActiveElement = document.activeElement;
        modalElement.setAttribute('aria-modal', 'true');
        modalElement.setAttribute('role', 'dialog');
        var focusableElements = this.getFocusableElements(modalElement);
        if (focusableElements.length === 0) return;
        var firstFocusable = focusableElements[0];
        focusableElements[focusableElements.length - 1];
        firstFocusable.focus();
        var _handleKeyDown = function handleKeyDown(e) {
          if (e.key === 'Escape') {
            _this.releaseFocus(modalElement, _handleKeyDown);
            var closeBtn = modalElement.querySelector('[data-dismiss="modal"], .btn-close, .close');
            if (closeBtn) closeBtn.click();
            return;
          }
          if (e.key !== 'Tab') return;
          var currentFocusable = _this.getFocusableElements(modalElement);
          if (currentFocusable.length === 0) return;
          var first = currentFocusable[0];
          var last = currentFocusable[currentFocusable.length - 1];
          if (e.shiftKey) {
            if (document.activeElement === first) {
              e.preventDefault();
              last.focus();
            }
          } else {
            if (document.activeElement === last) {
              e.preventDefault();
              first.focus();
            }
          }
        };
        modalElement.addEventListener('keydown', _handleKeyDown);
        modalElement._a11yKeyHandler = _handleKeyDown;
      },
      releaseFocus: function releaseFocus(modal, handler) {
        var modalElement = typeof modal === 'string' ? document.querySelector(modal) : modal;
        if (!modalElement) return;
        if (handler) {
          modalElement.removeEventListener('keydown', handler);
        } else if (modalElement._a11yKeyHandler) {
          modalElement.removeEventListener('keydown', modalElement._a11yKeyHandler);
          delete modalElement._a11yKeyHandler;
        }
        if (this.previousActiveElement && this.previousActiveElement.focus) {
          this.previousActiveElement.focus();
          this.previousActiveElement = null;
        }
      },
      announce: function announce(message) {
        var priority = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'polite';
        var announcer = document.getElementById('proclaim-a11y-announcer');
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
        setTimeout(function () {
          announcer.textContent = message;
        }, 100);
      }
    };
    document.addEventListener('DOMContentLoaded', function () {
      document.addEventListener('shown.bs.modal', function (e) {
        ProclaimA11y.trapFocus(e.target);
      });
      document.addEventListener('hidden.bs.modal', function (e) {
        ProclaimA11y.releaseFocus(e.target);
      });
    });

})();
