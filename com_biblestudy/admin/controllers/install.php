<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2015 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * Controller for Admin
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyControllerInstall extends JControllerForm
{

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   12.2
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}
<<<<<<< HEAD
=======

	/**
	 * Fix Assets
	 *
	 * @return void
	 */
	public function fixAssets()
	{
		$asset      = new fixJBSAssets;
		$fix_assets = $asset->fixAssets();
		/** @var BibleStudyModelInstall $modale */
		$modale     = $this->getModel('Install', 'BibleStudyModel');
		$modale->subcleanCache();
		$input      = new JInput;
		$input->set('messages', $fix_assets);

		$jbsname = $input->get('jbsname');
		$jbstype = $input->get('jbstype');

		if ($jbsname)
		{
			$this->setRedirect('index.php?option=com_biblestudy&view=install&jbsname=' . $jbsname . '&jbstype=' . $jbstype);
		}
	}

>>>>>>> Joomla-Bible-Study/master
}
