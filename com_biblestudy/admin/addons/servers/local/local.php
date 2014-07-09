<?php
defined('_JEXEC') or die;

class JBSMAddonLocal extends JBSMAddon {
    protected $config;

    public function __construct($config = array()) {
        parent::__construct($config);
    }

    public function upload($data)
    {
        $path = $data->get('path', null, 'PATH');
        $fileName = $_FILES["file"]["name"];
        if (!move_uploaded_file($_FILES["file"]["tmp_name"], JPATH_ROOT . '/' . $path . '/' . $fileName))
            die('false');

        return array(
            'filename' => $_FILES['file']['name'],
            'size' => $_FILES['file']['size']
        );
    }
}