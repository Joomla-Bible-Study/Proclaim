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
    protected $state_context = 'com_biblestudy.edit.mediafile';

    /**
     * Method to add a new mediafile item
     *
     * @return  bool    True if access level checks pass, false otherwise
     *
     * @since   8.1.0
     */
    public function add()
    {
        $app = JFactory::getApplication();

        $result = parent::add();
        if ($result) {
            $app->setUserState($this->state_context . '.server.id', null);

            $this->setRedirect(JRoute::_('index.php?option=com_biblestudy&view=mediafile' . $this->getRedirectToItemAppend(), false));
        }

        return $result;
    }

    /**
     * Method to cancel an edit
     *
     * @return  bool    True if access level checks pass, false otherwise
     *
     * @since   8.1.0
     */
    public function cancel()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();
        $result = parent::cancel();

        if ($result) {
            // Clear data from the session
            $app->setUserState($this->state_context . '.server.id', null);
        }

        return $result;
    }

    /**
     * Class constructor.
     *
     * @param   array $config A named array of configuration variables.
     *
     * @since    7.0.0
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
    }

    /**
     * Handles XHR requests (i.e. File uploads)
     *
     * @throws  Exception
     * @since   8.1.0
     */
    public function xhr()
    {
        JSession::checkToken('get') or die('Invalid Token');
        $input = JFactory::getApplication()->input;

        $addonType = $input->get('type', 'Legacy', 'string');
        $handler = $input->get('handler');

        // Load the addon
        $addon = JBSMAddon::getInstance($addonType);

        if (method_exists($addon, $handler)) {
            echo new JResponseJson($addon->upload($input));

            $app = JApplicationCms::getInstance();
            $app->close();
        } else
            throw new Exception(JText::sprintf('Handler: "' . $handler . '" does not exist!'), 404);
    }

    /**
     * Method to run batch operations.
     *
     * @param   object $model The model.
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
    function setServerId()
    {
        $app = JFactory::getApplication();
        $input = $app->input;

        $data = $input->get('jform', array(), 'post', 'array');

        // Get the server type


        // Save session
        $app->setUserState($this->state_context . '.server.id', $data['server_id']);

        $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($data['id']), false));
    }
}
