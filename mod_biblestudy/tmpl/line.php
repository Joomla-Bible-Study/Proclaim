<?php 

/**
* @version		$Id: line.php 8591 2007-08-27 21:09:32Z Tom Fuller $
* @package		mod_biblestudy
* @copyright            2010-2011
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

defined('_JEXEC') or die('Restriced Access'); ?>
<style type="text/css">
/* CSS goes here */

a[title]:after{}


a[title]:hover:after{
/*Shows the generated content*/
	content: attr(title) " (" attr(href) ")";
	visibility: visible;
}
</STYLE>
<?php
$linebreak = $params->get('linebreak');
$show_link = $params->get('show_link',1);
$pagetext = $params->get('pagetext');
foreach ($list as $study)
{
	modBiblestudyHelper::renderStudy($study, $params); 
	echo '<img src="'.JURI::base().'components/com_biblestudy/images/square.gif" height="2" width="100%">';
}

if ($show_link > 0)
		{
			if ($linebreak > 0){
			echo '<br>';
			}
			$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist');
			?>
			<a href="<?php echo $link; ?>">
            	<?php if (!$pagetext)
					{ 
						$pagetext = 'More Bible Studies';
					}
			echo $pagetext;?> </a>
            <?php
         }