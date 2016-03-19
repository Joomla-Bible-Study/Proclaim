<?php
/**
 * Default Custom
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

$mainframe = JFactory::getApplication();
$input = new JInput;
$option = $input->get('option', '', 'cmd');
$message = $input->get('msg', '', 'string');
$database = JFactory::getDbo();
$teacher_menu1 = $this->params->get('teacher_id');
$teacher_menu = $teacher_menu1[0];
$topic_menu1 = $this->params->get('topic_id');
$topic_menu = $topic_menu1[0];
$book_menu1 = $this->params->get('booknumber');
$book_menu = $book_menu1[0];
$location_menu1 = $this->params->get('locations');
$location_menu = $location_menu1[0];
$series_menu1 = $this->params->get('series_id');
$series_menu = $series_menu1[0];
$messagetype_menu1 = $this->params->get('messagetype');
$messagetype_menu = $messagetype_menu1[0];
$params = $this->params;
$teachers = $params->get('teacher_id');
$JBSMTeacher = new JBSMTeacher;
?>
<form
	action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=sermons&t=' . $input->get('t', '1', 'int')); ?>"
	method="post">
	<div id="biblestudy" class="noRefTagger"> <!-- This div is the container for the whole page -->
		<div id="bsheader">
			<h1 class="componentheading">
				<?php
				if ($this->params->get('show_page_image') > 0)
				{
					?>
					<img src="<?php echo JUri::base() . $this->main->path; ?>"
					     alt="<?php echo $this->params->get('page_title'); ?>"
					     width="<?php echo $this->main->width; ?>"
					     height="<?php echo $this->main->height; ?>"/>
					<?php
					// End of column for logo
				}
				?>
				<?php
				if ($this->params->get('show_page_title') > 0)
				{
					echo $this->params->get('page_title');
				}
				?>
			</h1>
			<?php
			if ($params->get('listteachers') && $params->get('list_teacher_show') > 0)
			{
				$teacher = $JBSMTeacher->getTeacher($params, (int) $id = 0);

				if ($teacher)
				{
					echo $teacher;
				}
			}
			?>
		</div>
		<!--header-->

		<table class="table table-striped" id="listintro">
			<tr>
				<td>
					<p>
						<?php
						if ($params->get('intro_show') == 1)
						{
							echo $params->get('list_intro');
						}
						?>
					</p>
				</td>
			</tr>
		</table>

		<div id="bsdropdownmenu">
			<?php
			if ($this->params->get('use_go_button') > 0)
			{
				?><span id="gobutton"><input type="submit" value="<?php echo JText::_('JBS_STY_GO_BUTTON'); ?>"/></span>
			<?php
			}

			if ($this->params->get('show_pagination') == 1)
			{
				echo '<span class="display-limit">' . JText::_('JGLOBAL_DISPLAY_NUM');
				?>
				<?php
				echo $this->pagination->getLimitBox() . '</span>';
			}
			if (($this->params->get('show_locations_search') > 0 && ($location_menu == -1)) || $this->params->get('show_locations_search') > 1)
			{
				echo $this->lists['locations'];
			}
			if (($this->params->get('show_book_search') > 0 && $book_menu == -1) || $this->params->get('show_book_search') > 1)
			{
				echo $this->lists['books'] . ' ';
				echo JText::_('JBS_STY_FROM_CHAPTER') . ' <input type="text" id="minChapt" name="minChapt" size="3"';

			}
			if (($this->params->get('show_teacher_search') > 0 && ($teacher_menu == -1)) || $this->params->get('show_teacher_search') > 1)
			{
				echo $this->lists['teacher_id'];
			}
			if (($this->params->get('show_series_search') > 0 && ($series_menu == -1)) || $this->params->get('show_series_search') > 1)
			{
				echo $this->lists['seriesid'];
			}
			if (($this->params->get('show_type_search') > 0 && ($messagetype_menu == -1)) || $this->params->get('show_type_search') > 1)
			{
				echo $this->lists['messagetypeid'];
			}
			if ($this->params->get('show_year_search') > 0)
			{
				echo $this->lists['studyyear'];
			}
			if ($this->params->get('show_order_search') > 0)
			{
				echo $this->lists['orders'];
			}
			if (($this->params->get('show_topic_search') > 0 && ($topic_menu == -1)) || $this->params->get('show_topic_search') > 1)
			{
				echo $this->lists['topics'];
			}
			if ($this->params->get('show_popular') > 0)
			{
				echo $this->popular;
			}
			?>
		</div>
		<!--dropdownmenu-->
		<?php
		switch ($params->get('wrapcode'))
		{
			case '0':
				// Do Nothing
				break;
			case 'T':
				// Table
				echo '<table class="table table-striped" id="bsms_studytable" width="100%">';
				break;
			case 'D':
				// DIV
				echo '<div>';
				break;
		}
		echo $params->get('headercode');

		foreach ($this->items as $row)
		{ // Run through each row of the data result from the model
			$listing = $JBSMTeacher->getListingExp($row, $params, $this->template);
			echo $listing;
		}

		switch ($params->get('wrapcode'))
		{
			case '0':
				// Do Nothing
				break;
			case 'T':
				// Table
				echo '</table>';
				break;
			case 'D':
				// DIV
				echo '</div>';
				break;
		}
		?>
		<div class="listingfooter">
			<?php
			echo $this->pagination->getPagesLinks();
			echo $this->pagination->getPagesCounter();
			?>
		</div>
		<!--end of bsfooter div-->
	</div>
	<!--end of bspagecontainer div-->
	<input name="option" value="com_biblestudy" type="hidden">
	<input name="task" value="" type="hidden">
	<input name="boxchecked" value="0" type="hidden">
	<input name="controller" value="sermons" type="hidden">
</form>
