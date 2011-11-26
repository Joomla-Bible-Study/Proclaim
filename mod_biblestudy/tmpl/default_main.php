
<?php
/**
 * @version		$Id: default_main.php 8591 2007-08-27 21:09:32Z Tom Fuller $
 * @package		mod_biblestudy
 * @copyright            2010-2011
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

defined('_JEXEC') or die('Restriced Access');

$show_link = $params->get('show_link',1);
$pagetext = $params->get('pagetext');
$document =& JFactory::getDocument();
$document->addStyleSheet(JURI::base().'components/com_biblestudy/assets/css/biblestudy.css');
$url = $params->get('stylesheet');
$ismodule = 1;
if ($url) {
	$document->addStyleSheet($url);
}
?>
<div
	id="biblestudy" class="noRefTagger">
	<!-- This div is the container for the whole page -->
	<table id="bsmsmoduletable" cellspacing="0">


	<?php
	$path1 = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_biblestudy/helpers/';
	include_once($path1.'header.php');
	include_once($path1.'helper.php');
	include_once($path1.'listing.php');
	$header = getHeader($list[0], $params, $admin_params, $template, $params->get('use_headers'), $ismodule);
	echo $header;
	?>

		<tbody>
			
			
		<?php
		$class1 = 'bsodd';
		$class2 = 'bseven';
		$oddeven = $class1;
		foreach ($list as $study) {
			if($oddeven == $class1){
				//Alternate the color background
				$oddeven = $class2;
			} else {
				$oddeven = $class1;
			}
			$listing = getListing($study, $params, $oddeven, $admin_params, $template, $ismodule);
			echo $listing;
		}
		?>
		</tbody>
	</table>
</div>
<div class="modulelistingfooter">
	<br />
	
	
	
	
    <?php $link_text = $params->get('pagetext', 'More Bible Studies');
			
			if ($params->get('show_link') > 0){
					$t = $params->get('studielisttemplateid');
					if (!$t) {$t = JRequest::getVar('t',1,'get','int');}
					
					$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&t='.$t);?>
			<a href="<?php echo $link;?>"> <?php echo $link_text.'<br />'; ?> </a> <?php } //End of if view_link not 0?>
    </div>
<!--end of footer div-->
