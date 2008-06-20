<?php
/**
 * Bible Study default controller
 * 
 * @license		GNU/GPL
 */

jimport('joomla.application.component.controller');

/**
 * Bible Study Component Controller
 *
 * 
 */
class biblestudyController extends JController
{
	/**
	 * Method to display the view
	 *
	 * @access	public
	 */
	function display()
	{
				$type = JRequest::getVar('view');
				if (!$type){
				JRequest::setVar( 'view'  , 'studieslist');
				$model = $this->getModel('studieslist');
				}
		
		if(JRequest::getCmd('view') == 'studydetails')
		{
			$model =& $this->getModel('studydetails');
			$model->hit();
		}
		
		parent::display();
	}
	
}

?>
