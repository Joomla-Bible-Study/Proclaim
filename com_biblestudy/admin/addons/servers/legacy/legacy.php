<?php
defined('_JEXEC') or die;

class JBSMAddonLegacy extends JBSMAddon
{
    protected $config;

    public function __construct($config = array())
    {
        parent::__construct($config);
    }

    public function upload($data)
    {
        // Convert back slashes to forward slashes
        $file = str_replace('\\', '/', $data->get('path', null, 'PATH'));
        $slash = strrpos($file, '/');

        $path = substr($file, 0, $slash + 1);

        // Remove domain from path
        preg_match('/\/+.+/', $path, $matches);

        // Make filename safe and move it to correct folder
        $destFile = JApplication::stringURLSafe($_FILES["file"]["name"]);
        if (!JFile::upload($_FILES['file']['tmp_name'], JPATH_ROOT.$matches[0].$destFile))
            die('false');

        return array(
            'data' =>array(
                'filename' => $path.$destFile,
                'size' => $_FILES['file']['size']
                )
            );
    }
}