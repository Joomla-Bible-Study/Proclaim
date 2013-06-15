<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;

/**
 * Inserts some css code to fix pagination problem and add a tag for the captcha of comments
 *
 * @package  BibleStudy.Admin
 * @since    7.0.2
 */
class JBSM702Update
{

	/**
	 * Update CSS for 7.0.2
	 *
	 * @return boolean
	 */
	public function css702()
	{
		$newcss = '#main ul, #main li
{
display: inline;
}

.component-content ul
{
text-align: center;
}

.component-content li
{
display: inline;
}

.pagenav
{
margin-left: 10px;
margin-right: 10px;
}

#recaptcha_widget_div {
position:static !important;}';

		$csscheck = '#main ul, #main li';

		$dest      = JPATH_SITE . DIRECTORY_SEPARATOR . 'media/com_biblestudy/css/biblestudy.css';
		$cssexists = JFile::exists($dest);

		if ($cssexists)
		{
			$cssread = file_get_contents($dest);

			$csstest = substr_count($cssread, $csscheck);

			if (!$csstest)
			{
				$cssread = $cssread . $newcss;
			}

			if (!JFile::write($dest, $cssread))
			{
				return false;
			}
		}

		return true;
	}

}
