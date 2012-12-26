<?php
/**
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;

/**
 * Filter Class
 *
 * @package  BibleStudy.admin
 * @since    7.1.0
 */
class JBSMFilter
{
	/**
	 * @var string
	 */
	public static $extension = 'com_biblestudy';

	/**
	 *
	 */
	const BADTAGSHTML = '<title><link><mata>';

	/**
	 * Strip HTML Tags out.
	 *
	 * @param   string   $str           String to parse
	 * @param   string   $tags          Tags
	 * @param   boolean  $stripContent  When to Strip Content
	 *
	 * @return string
	 *
	 * @since 7.1.0
	 * @todo  need to work this some more but work now. BCC
	 */
	public static function strip_only($str, $tags = self::BADTAGSHTML, $stripContent = true)
	{
		$content = '';

		if (!is_array($tags))
		{
			$tags = (strpos($str, '>') !== false ? explode('>', str_replace('<', '', $tags)) : array($tags));

			if (end($tags) == '')
			{
				array_pop($tags);
			}
		}
		foreach ($tags as $tag)
		{
			if ($stripContent)
			{
				$content = '(.+</' . $tag . '[^>]*>|)';
			}
			$str = preg_replace('#</?' . $tag . '[^>]*>' . $content . '#is', '', $str);
		}

		return $str;
	}

}