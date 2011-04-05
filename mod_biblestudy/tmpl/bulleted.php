<?php defined('_JEXEC') or die('Restriced Access'); ?>
<style type="text/css">
/* CSS goes here */
#

a[title]:after{


a[title]:hover:after{
/*Shows the generated content*/
	content: attr(title) " (" attr(href) ")";
	visibility: visible;
}

#

</STYLE>
<ul>
<?php
$linebreak = $params->get('linebreak');
$show_link = $params->get('show_link',1);
$pagetext = $params->get('pagetext');
foreach ($list as $study)
{
	echo "<li>";
	modBiblestudyHelper::renderStudy($study, $params);
	echo "</li>";
}
?>
</ul>
<?php
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