<?php
/**
 * @package     BibleStudy.Admin
 * @subpackage  HTML
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utility class for PLUpload
 *
 * @package     BibleStudy.Admin
 * @subpackage  HTML
 * @since       8.1.0
 */
abstract class JHtmlPlupload
{

    /**
     * @var array   Array containing information for loaded files
     * @since   8.1.0
     */
    protected static $loaded = array();

    public static function framework($debug = null)
    {
        // Only load once
        if (!empty(static::$loaded[__METHOD__])) {
            return;
        }

        // Load jQuery
        JHtml::_('jquery.framework');

        // if no debugging value is set, use the configuration setting
        if ($debug === null) {
            $config = JFactory::getConfig();
            $debug = (boolean)$config->get('debug');
        }

        JHtml::_('script', 'com_biblestudy/jui/moxie.min.js', false, true, false, false, $debug);
        JHtml::_('script', 'com_biblestudy/jui/plupload.min.js', false, true, false, false, $debug);
        static::$loaded[__METHOD__] = true;

        return true;
    }

    public static function uploader($params = array())
    {
        $sig = md5(serialize(array($params)));

        if (!isset(static::$loaded[__METHOD__][$sig])) {

            // Include plupload library
            static::framework();

            $params['runtimes'] = isset($params['runtimes']) ? $params['runtimes'] : 'html5';
            $params['multi_selection'] = isset($params['multi_selection']) ? (boolean)$params['multi_selection'] : false;
            $params['browse_button'] = isset($params['browse_button']) ? $params['browse_button'] : 'plupload_browse';
            $params['drop_element'] = isset($params['drop_element']) ? $params['drop_element'] : 'plupload_dropzone';

            // Clear upload queue when browsing for files
            $params['init']['Browse'] = '\
            function() {
                uploader.splice();
            }';

            // Handle errors
            $params['init']['Error'] = '\
                function(uploader, error) {
                    if(error.code === -600) {
                        alert("File is too large. Maximum file upload size is: " + uploader.settings.filters.max_file_size);
                    }
                }
                ';

            $options = JHtml::getJSObject($params);

            // Attach the uploader to document
            JFactory::getDocument()->addScriptDeclaration(
                "
                mOxie.Mime.addMimeType(\"audio/x-aiff,aif aiff\"); // notice that it is
                jQuery(document).ready(function()
                {
                    window.uploader = new plupload.Uploader($options);
                    uploader.init();
                });"
            );
        }

        return;

    }
}