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
defined('_JEXEC')
or die;

jimport('joomla.application.component.modeladmin');

/**
 * Upload model class
 *
 * @property mixed _id
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyModelUpload extends JModelAdmin
{
    /**
     * Admin
     *
     * @var string
     */
    private $_admin;

    /**
     * @var    string  The prefix to use with controller messages.
     * @since  1.6
     */
    protected $text_prefix = 'COM_BIBLESTUDY';

    /**
     * Get the form data
     *
     * @param   array   $data      Data for the form.
     * @param   boolean $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return boolean|object
     *
     * @since 7.0
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $array = array('control' => 'jform', 'load_data' => $loadData);
        $form  = $this->loadForm('com_biblestudy.upload', 'upload', $array);

        if (empty($form))
        {
            return false;
        }


        return $form;
    }

}