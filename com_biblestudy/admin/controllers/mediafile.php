<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Controller For MediaFile
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyControllerMediafile extends JControllerForm
{

	/**
	 * NOTE: This is needed to prevent Joomla 1.6's pluralization mechanisim from kicking in
	 *
	 * @since 7.0
	 */
	protected $view_list = 'mediafiles';

	/**
	 * Class constructor.
	 *
	 * @param   array $config  A named array of configuration variables.
	 *
	 * @since    7.0.0
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}

    public function upload() {
    	$location = array();
    	
    	$app = JFactory::getApplication();
    	$input = $app->input;
    	
    	$location_data = explode(".", $input->get("location", null, "STRING"));
        $location['server_id'] = $location_data[0];
        $location['folder_id'] = $location_data[1];
        
        //Get Server information
        $server = $this->getModel("Server")->getItem($location['server_id']);
        
    	$adapter = JBSServer::getInstance();
    	
    	die($server->test());
    }

	/**
	 * Method to run batch operations.
	 *
	 * @param   object $model  The model.
	 *
	 * @return  boolean     True if successful, false otherwise and internal error is set.
	 *
	 * @since   1.6
	 */
	public function batch($model = null)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Set the model
		$model = $this->getModel('Mediafile', '', array());

		// Preset the redirect
		$this->setRedirect(JRoute::_('index.php?option=com_biblestudy&view=mediafiles' . $this->getRedirectToListAppend(), false));

		return parent::batch($model);
	}

    /**
     * Sets the server for this media record
     *
     * @return  void
     * @since   8.1.0
     */
    function setServer() {
        $app = JFactory::getApplication();
        $input = $app->input;

        $data = $input->get('jform', array(), 'post', 'array');
        $data = json_decode(base64_decode($data['server_id']));

        $media_id = isset($data->media_id) ? $data->media_id : 0;
        $server_id = isset($data->server_id) ? $data->server_id : 0;

        // Save server in the session
        $app->setUserState('com_biblestudy.edit.mediafile.server_id', $server_id);

        $this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_item.$this->getRedirectToItemAppend($media_id), false));
    }

}
