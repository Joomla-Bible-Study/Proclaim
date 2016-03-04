<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * Core Bible Study Helper
 *
 * @package  BibleStudy.Admin
 * @since    7.1.0
 * */
class JBSMHelper
{
	/**
	 * Extension Name
	 *
	 * @var string
	 */
	public static $extension = 'com_biblestudy';

	/**
	 * Get tooltip.
	 *
	 * @param   object                    $row       JTable
	 * @param   Joomla\Registry\Registry  $params    Item Params
	 * @param   TableTemplate             $template  ID
	 *
	 * @return string
	 */
	public static function getTooltip($row, $params, $template)
	{
		$JBSMElements = new JBSMListing;

		JHTML::_('behavior.tooltip');
		$linktext = '<span class="hasTip" title="<strong>' . $params->get('tip_title') . '  :: ';

		$tip1     = $JBSMElements->getElement($params->get('tip_item1'), $row, $params, $template, $type = 0);
		$tip2     = $JBSMElements->getElement($params->get('tip_item2'), $row, $params, $template, $type = 0);
		$tip3     = $JBSMElements->getElement($params->get('tip_item3'), $row, $params, $template, $type = 0);
		$tip4     = $JBSMElements->getElement($params->get('tip_item4'), $row, $params, $template, $type = 0);
		$tip5     = $JBSMElements->getElement($params->get('tip_item5'), $row, $params, $template, $type = 0);

		$linktext .= '<strong>' . $params->get('tip_item1_title') . '</strong>: ' . $tip1 . '<br />';
		$linktext .= '<strong>' . $params->get('tip_item2_title') . '</strong>: ' . $tip2 . '<br />';
		$linktext .= '<strong>' . $params->get('tip_item3_title') . '</strong>: ' . $tip3 . '<br />';
		$linktext .= '<strong>' . $params->get('tip_item4_title') . '</strong>: ' . $tip4 . '<br />';
		$linktext .= '<strong>' . $params->get('tip_item5_title') . '</strong>: ' . $tip5;
		$linktext .= '">';

		return $linktext;
	}

	/**
	 * Get ShowHide.
	 *
	 * @return string
	 *
	 * @deprecated 7.1.8
	 */
	public static function getShowhide()
	{
		$showhide = '
        function HideContent(d) {
        document.getElementById(d).style.display = "none";
        }
        function ShowContent(d) {
        document.getElementById(d).style.display = "block";
        }
        function ReverseDisplay(d) {
        if(document.getElementById(d).style.display == "none") { document.getElementById(d).style.display = "block"; }
        else { document.getElementById(d).style.display = "none"; }
        }
        ';

		return $showhide;
	}

	/**
	 * Method to get file size
	 *
	 * @param   string  $url  URL
	 *
	 * @return  int|boolean  Return size or false read.
	 */
	public static function getRemoteFileSize($url)
	{
		$parsed = parse_url($url);
		$host   = $parsed["host"];
		$fp     = null;

		if (function_exists('fsockopen'))
		{
			$fp = @fsockopen($host, 80, $errno, $errstr, 20);
		}
		if (!$fp)
		{
			return false;
		}
		else
		{
			@fputs($fp, "HEAD $url HTTP/1.1\r\n");
			@fputs($fp, "HOST: $host\r\n");
			@fputs($fp, "Connection: close\r\n\r\n");
			$headers = "";

			while (!@feof($fp))
			{
				$headers .= @fgets($fp, 128);
			}
		}
		@fclose($fp);
		$return      = false;
		$arr_headers = explode("\n", $headers);

		if (strpos($arr_headers[0], '200'))
		{
			foreach ($arr_headers as $header)
			{
				$s = "Content-Length: ";

				if (substr(strtolower($header), 0, strlen($s)) == strtolower($s))
				{
					$return = trim(substr($header, strlen($s)));
					break;
				}
			}
		}

		return (int) $return;
	}

}
