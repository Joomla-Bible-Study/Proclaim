<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2017 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.joomlabiblestudy.org
 * */
// No direct access to this file
defined('_JEXEC') or die();

/**
 * Template Code controller class
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyControllerTemplatecode extends JControllerForm
{
	/**
	 * Protect the view
	 *
	 * @var string
	 *
	 * @since 1.5
	 */
	protected $view_list = 'templatecodes';

	/**
	 * Proxy for getModel
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return BiblestudyModelTemplatecode
	 *
	 * @since 7.1.0
	 */
	public function getModel($name = 'Templatecode', $prefix = 'BiblestudyModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}
}
