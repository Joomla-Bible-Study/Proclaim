(function () {
    'use strict';

    /**
     * @package     Proclaim.Fancybox
     * @subpackage  com_proclaim
     *
     * @copyright   Copyright (C) 2005-2023 Open Source Matters, Inc. All rights
     *   reserved.
     * @license     GNU General Public License version 2 or later; see LICENSE.txt
     */
    document.addEventListener("DOMContentLoaded", function () {
      document.querySelectorAll(".fancybox_player").forEach(function (element) {
        element.addEventListener("click", function () {
          var src = this.getAttribute('data-src');
          var height = this.getAttribute('data-height') || this.getAttribute('pheight') || '360';
          var width = this.getAttribute('data-width') || this.getAttribute('pwidth') || '640';
          var posterImage = this.getAttribute('data-image') || '';
          var autoplay = this.getAttribute('autostart') === 'true';
          var muted = this.getAttribute('data-mute') === 'true';
          var logo = this.getAttribute('data-logo') || '';
          var logoLink = this.getAttribute('data-logolink') || '';
          var headerText = this.getAttribute('data-header') || '';
          var footerText = this.getAttribute('data-footer') || '';
          var controls = this.getAttribute('controls') !== '0';
          var isVideo = /\.(mp4|m4v|webm|ogv|mov)(\?|$)/i.test(src) || /youtube\.com|youtu\.be|vimeo\.com/i.test(src);
          var isAudio = /\.(mp3|m4a|ogg|wav)(\?|$)/i.test(src);
          var caption = '';
          if (headerText || footerText) {
            caption = '<div class="proclaim-fancybox-caption">';
            if (headerText) {
              caption += '<div class="proclaim-fancybox-header">' + headerText + '</div>';
            }
            if (footerText) {
              caption += '<div class="proclaim-fancybox-footer">' + footerText + '</div>';
            }
            caption += '</div>';
          }
          var logoHtml = '';
          if (logo) {
            logoHtml = '<div class="proclaim-fancybox-logo">';
            if (logoLink) {
              logoHtml += '<a href="' + logoLink + '" target="_blank">';
            }
            logoHtml += '<img src="' + logo + '" alt="Logo" />';
            if (logoLink) {
              logoHtml += '</a>';
            }
            logoHtml += '</div>';
          }
          var contentType = 'iframe';
          var htmlContent = '';
          var errorHtml = '<div class="proclaim-media-error" style="display:none;padding:30px 20px;text-align:center;color:#fff;">' + '<p style="margin:0 0 10px;font-size:1.1em;font-weight:500;">Unable to load media file</p>' + '<p style="margin:0;font-size:0.9em;color:#888;">The requested file could not be found or is unavailable.</p>' + '</div>';
          if (isAudio) {
            contentType = 'html';
            htmlContent = '<div class="proclaim-player-wrapper" style="width:' + width + 'px;">';
            htmlContent += '<audio ' + (controls ? 'controls ' : '') + (autoplay ? 'autoplay ' : '') + (muted ? 'muted ' : '') + 'style="width:100%;">';
            htmlContent += '<source src="' + src + '" type="audio/mpeg">';
            htmlContent += '</audio>';
            htmlContent += errorHtml;
            htmlContent += logoHtml;
            htmlContent += '</div>';
          } else if (isVideo && !/youtube\.com|youtu\.be|vimeo\.com/i.test(src)) {
            contentType = 'html';
            htmlContent = '<div class="proclaim-player-wrapper" style="position:relative;width:' + width + 'px;height:' + height + 'px;">';
            htmlContent += '<video ' + (controls ? 'controls ' : '') + (autoplay ? 'autoplay ' : '') + (muted ? 'muted ' : '') + (posterImage ? 'poster="' + posterImage + '" ' : '') + 'style="width:100%;height:100%;">';
            htmlContent += '<source src="' + src + '" type="video/mp4">';
            htmlContent += '</video>';
            htmlContent += errorHtml;
            htmlContent += logoHtml;
            htmlContent += '</div>';
          }
          var fancyboxOptions = {
            Carousel: {
              infinite: false
            },
            Toolbar: {
              display: {
                left: [],
                middle: [],
                right: ["close"]
              }
            },
            on: {
              reveal: function reveal(fancybox) {
                var container = fancybox.container;
                var mediaElement = container.querySelector('audio, video');
                if (mediaElement) {
                  mediaElement.addEventListener('error', function () {
                    var wrapper = this.closest('.proclaim-player-wrapper');
                    if (wrapper) {
                      var errorDiv = wrapper.querySelector('.proclaim-media-error');
                      if (errorDiv) {
                        this.style.display = 'none';
                        errorDiv.style.display = 'block';
                      }
                    }
                  });
                  var source = mediaElement.querySelector('source');
                  if (source) {
                    source.addEventListener('error', function () {
                      var wrapper = mediaElement.closest('.proclaim-player-wrapper');
                      if (wrapper) {
                        var errorDiv = wrapper.querySelector('.proclaim-media-error');
                        if (errorDiv) {
                          mediaElement.style.display = 'none';
                          errorDiv.style.display = 'block';
                        }
                      }
                    });
                  }
                }
              }
            }
          };
          if (contentType === 'html' && htmlContent) {
            Fancybox.show([{
              html: htmlContent,
              caption: caption
            }], fancyboxOptions);
          } else {
            Fancybox.show([{
              src: src,
              type: 'iframe',
              width: width,
              height: height,
              thumb: posterImage,
              preload: false,
              caption: caption
            }], fancyboxOptions);
          }
        });
      });
    });

})();
