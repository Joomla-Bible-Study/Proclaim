<?php
defined('_JEXEC') or die;

class JBSServerAmazonS3 extends JBSServer {
    public $name = 'amazonS3';

    protected function __construct($options) {
        $options['key'] = (isset($options['key'])) ?  $options['key'] : '';
        $options['secret'] = (isset($options['secret'])) ?  $options['secret'] : '';

        // Include the S3 class
        JLoader::register('S3', dirname(__FILE__).'/S3.class.php');

        $this->connection = new S3($options['key'], $options['secret']);
    }

    protected function upload($target, $overwrite = true)
    {
        // TODO: Implement upload() method.
    }
    
    public function test() {
    	return "hello from amazon";
    }
}