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
	 * @param   int       $rowid         ID
	 * @param   object    $row           JTable
	 * @param   JRegistry $params        Item Params
	 * @param   JRegistry $admin_params  Admin Params
	 * @param   int       $template      ID
	 *
	 * @return string
	 */
	public static function getTooltip($rowid, $row, $params, $admin_params, $template)
	{
		$JBSMElements = new JBSMElements;

		// Tom added the below because tooltip wasn't working as of 6.1.1
		$toolTipArray = array(
			'className' => 'custom',
			'showDelay' => '500',
			'hideDelay' => '500',
			'fixed'     => true,
			'onShow'    => "function(tip) {tip.effect('opacity',
 		{duration: 500, wait: false}).start(0,1)}",
			'onHide'    => "function(tip) {tip.effect('opacity',
		{duration: 500, wait: false}).start(1,0)}"
		);
		JHTML::_('behavior.tooltip', '.hasTip', $toolTipArray);

		$linktext = '<span class="zoomTip" title="<strong>' . $params->get('tip_title') . '  :: ';
		$tip1     = $JBSMElements->getElementid($params->get('tip_item1'), $row, $params, $admin_params, $template);
		$tip2     = $JBSMElements->getElementid($params->get('tip_item2'), $row, $params, $admin_params, $template);
		$tip3     = $JBSMElements->getElementid($params->get('tip_item3'), $row, $params, $admin_params, $template);
		$tip4     = $JBSMElements->getElementid($params->get('tip_item4'), $row, $params, $admin_params, $template);
		$tip5     = $JBSMElements->getElementid($params->get('tip_item5'), $row, $params, $admin_params, $template);
		$test     = $params->get('tip_item1');
		$linktext .= '<strong>' . $params->get('tip_item1_title') . '</strong>: ' . $tip1->element . '<br />';
		$linktext .= '<strong>' . $params->get('tip_item2_title') . '</strong>: ' . $tip2->element . '<br /><br />';
		$linktext .= '<strong>' . $params->get('tip_item3_title') . '</strong>: ' . $tip3->element . '<br />';
		$linktext .= '<strong>' . $params->get('tip_item4_title') . '</strong>: ' . $tip4->element . '<br />';
		$linktext .= '<strong>' . $params->get('tip_item5_title') . '</strong>: ' . $tip5->element;
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

}
