<?php
/**
 * Default Main
 *
 * @package    BibleStudy.Site
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;
$listingcall   = JViewLegacy::loadHelper('serieslist');
$studylistcall = JViewLegacy::loadHelper('listing');
$t             = $this->params->get('serieslisttemplateid');
$input         = new JInput;
if (!$t)
{

	$t = $input->get('t', 1, 'int');
}
?>
<form action="<?php echo str_replace("&", "&amp;", $this->request_url); ?>" method="post" name="adminForm">
    <div id="biblestudy" class="noRefTagger"> <!-- This div is the container for the whole page -->

        <!--header-->
    <table id="seriestable">
    <tbody>
		<?php
		$listing = getSerieslist($this->items, $this->params, $oddeven = 'bsodd', $this->admin_params, $this->template, $view = 1);
		echo $listing;
		?>
		<?php
		switch ($this->params->get('series_detail_listtype'))
		{
			case 0:
				?>
	</tbody>
	</table>
	<table id="seriesstudytable">
        <tbody>
			<?php
			$studies = getSeriesstudies($this->items->id, $this->params, $this->admin_params, $this->template);
			echo $studies;
			?>
        </tbody>
    </table>
				<?php
				break;

			case 1:
				?></tbody></table>
                <table class="bslisttable">
                    <tr>
                        <td><?php
							$headerCall = JViewLegacy::loadHelper('header');
							$header     = JBSMHeader::getHeader($this->seriesstudies, $this->params, $this->admin_params, $this->template, $showheader = $this->params->get('use_headers_list'), $ismodule = 0);
							echo $header;

							$class1  = 'bsodd';
							$class2  = 'bseven';
							$oddeven = $class1;

							foreach ($this->seriesstudies as $row)
							{ // Run through each row of the data result from the model
								if ($oddeven == $class1)
								{ // Alternate the color background
									$oddeven = $class2;
								}
								else
								{
									$oddeven = $class1;
								}
								$studylisting = JBSMListing::getListing($row, $this->params, $oddeven, $this->admin_params, $this->template, $ismodule = 0);
								echo $studylisting;
							}
							?>
                        </td>
                    </tr>
                </table>
				<?php
				break;

			case 2:
				?>
                </table>
                <table id="seriesstudytable">
                    <tr>
                        <td><?php
							$studies = getSeriesstudiesExp($this->items->id, $this->params, $this->admin_params, $this->template);
							echo $studies;
							?>
                        </td>
                    </tr>
                </table><?php
				break;
		}
		if ($this->params->get('series_list_return') > 0)
		{
			echo '<table><tr class="seriesreturnlink"><td><a href="' . JRoute::_('index.php?option=com_biblestudy&view=seriesdisplays&t=' . $t) . '"><< '
					. JText::_('JBS_SER_RETURN_SERIES_LIST') . '</a> | <a href="' . JRoute::_('index.php?option=com_biblestudy&view=sermons&filter_series='
					. $this->items->id . '&t=' . $t) . '">' . JText::_('JBS_CMN_SHOW_ALL') . ' ' . JText::_('JBS_SER_STUDIES_FROM_THIS_SERIES')
					. ' >></a></td></tr></table>';
		}
		?>

    </div>
    <!--end of bspagecontainer div-->
    <input name="option" value="com_biblestudy" type="hidden">
    <input name="task" value="" type="hidden">
    <input name="boxchecked" value="0" type="hidden">
    <input name="controller" value="seriesdisplay" type="hidden">
</form>