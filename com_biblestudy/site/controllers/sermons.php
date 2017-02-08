<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.joomlabiblestudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * Controller class for Sermons
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class BiblestudyControllerSermons extends JControllerLegacy
{
	/**
	 * Media Code
	 *
	 * @var string
	 * @since 7.0
	 */
	public $mediaCode;

	/**
	 * Download?
	 *
	 * @return void
	 *
	 * @since 7.0
	 */
	public function download()
	{
		$input = new JInput;
		$task  = $input->get('task');
		$mid   = $input->getInt('id');

		if ($task == 'download')
		{
			$downloader = new JBSMDownload;
			$downloader->download($mid);
		}
	}

	/**
	 * Avplayer
	 *
	 * @return void
	 *
	 * @since 7.0
	 */
	public function avplayer()
	{
		$input = new JInput;
		$task  = $input->get('task');

		if ($task == 'avplayer')
		{
			$mediacode       = $input->get('code', '', 'string');
			$this->mediaCode = $mediacode;
		}
	}

	/**
	 * Add hits to the play count.
	 *
	 * @return null
	 *
	 * @since 7.0
	 */
	public function playHit()
	{
		$getMedia = new JBSMMedia;
		$input    = new JInput;
		$getMedia->hitPlay($input->get('id', '', 'int'));
	}

	/**
	 * This function is supposed to generate the Media Player that is requested via AJAX
	 * from the studiesList view "default.php". It has not been implemented yet, so its not used.
	 *
	 * @return null
	 *
	 * @since 7.0
	 * @deprecated since version 7.0.4
	 */
	public function inlinePlayer()
	{
		echo('{m4vremote}http://www.livingwatersweb.com/video/John_14_15-31.m4v{/m4vremote}');
	}
}
