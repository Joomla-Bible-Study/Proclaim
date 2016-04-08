<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */

defined('_JEXEC') or die;


/**
 * Controller for Archive
 *
 * @since  9.0.1
 */
class BiblestudyControllerArchive extends JControllerForm
{
  
  /**
	 * NOTE: This is needed to prevent Joomla 1.6's pluralization mechanism from kicking in
	 *
	 * @param  string
	 *
	 * @since 9.0.1
	 */
	protected $view_list = 'cpanel';

	/**
	 * Class constructor.
	 *
	 * @param   array  $config  A named array of configuration variables.
	 *
	 * @since    9.0.1
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

	}

   /**
    * Do Archive of Sermons and Media
    * 
    * @return void
    * 
    * @since  9.0.1
    */
	public function doArchive()
   {
     $input = JFactory::getApplication->input;
     
     // Used this field to show how long back to archive.
     $timeframe = $input->get('timeframe');
     
     // Use this to field (years, months, days)
     $swtich    = $input->get('switch');
     
     switch ($swith)
       {
           case('year'):
           
           done;
           case('months'):
           
           done;
       }

	}
  
}