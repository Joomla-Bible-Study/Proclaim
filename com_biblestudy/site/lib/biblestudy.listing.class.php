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

require_once JPATH_ADMINISTRATOR . '/components/com_biblestudy/lib/biblestudy.defines.php';

// Helper file - master list creater for study lists
JLoader::register('JBSMImages', BIBLESTUDY_PATH_LIB . '/biblestudy.images.class.php');
JLoader::register('jbsMedia', BIBLESTUDY_PATH_LIB . '/biblestudy.media.class.php');
JLoader::register('JBSMHelperRoute', BIBLESTUDY_PATH_HELPERS . '/route.php');
JLoader::register('JBSMElements', BIBLESTUDY_PATH_HELPERS . '/elements.php');
JLoader::register('JBSMCustom', BIBLESTUDY_PATH_HELPERS . '/custom.php');
JLoader::register('JBSMHelper', BIBLESTUDY_PATH_ADMIN_HELPERS . '/helper.php');

/**
 * BibleStudy listing class
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class JBSMListing extends JBSMElements
{
	/**
	 * Get listing
	 *
	 * @param   object    $row           Item Info
	 * @param   JRegistry $params        Item Params
	 * @param   string    $oddeven       ?Number patten?
	 * @param   object    $admin_params  Admin info
	 * @param   int       $template      Template ID
	 * @param   string    $ismodule      If coming form a Module
	 *
	 * @return string
	 */
	public function getListing($row, $params, $oddeven, $admin_params, $template, $ismodule)
	{
		/* Here we test to see if this is a sermon or list view. If details, we reset the params to the details.
		   this keeps us from having to rewrite all this code. */
		$input = new JInput;
		$view  = $input->get('view');

		$custom = new JBSMCustom;

		if ($view == 'sermon' && $ismodule < 1)
		{

			$params->set('row1col1', $params->get('drow1col1'));
			$params->set('r1c1custom', $params->get('dr1c1custom'));
			$params->set('r1c1customlabel', $params->get('dr1c1customlabel'));
			$params->set('r1c1span', $params->get('dr1c1span'));
			$params->set('linkr1c1', $params->get('dlinkr1c1'));

			$params->set('row1col2', $params->get('drow1col2'));
			$params->set('r1c2custom', $params->get('dr1c2custom'));
			$params->set('r1c2customlabel', $params->get('dr1c1customlabel'));
			$params->set('r1c2span', $params->get('dr1c2span'));
			$params->set('linkr1c2', $params->get('dlinkr1c2'));

			$params->set('row1col3', $params->get('drow1col3'));
			$params->set('r1c3custom', $params->get('dr1c3custom'));
			$params->set('r1c3customlabel', $params->get('dr1c1customlabel'));
			$params->set('r1c3span', $params->get('dr1c3span'));
			$params->set('linkr1c3', $params->get('dlinkr1c3'));

			$params->set('row1col4', $params->get('drow1col4'));
			$params->set('r1c4custom', $params->get('dr1c4custom'));
			$params->set('r1c4customlabel', $params->get('dr1c1customlabel'));
			$params->set('linkr1c4', $params->get('dlinkr1c4'));

			$params->set('row2col1', $params->get('drow2col1'));
			$params->set('r2c1custom', $params->get('dr2c1custom'));
			$params->set('r2c1customlabel', $params->get('dr1c1customlabel'));
			$params->set('r2c1span', $params->get('dr2c1span'));
			$params->set('linkr2c1', $params->get('dlinkr2c1'));

			$params->set('row2col2', $params->get('drow2col2'));
			$params->set('r2c2custom', $params->get('dr2c2custom'));
			$params->set('r2c2customlabel', $params->get('dr1c1customlabel'));
			$params->set('r2c2span', $params->get('dr2c2span'));
			$params->set('linkr2c2', $params->get('dlinkr2c2'));

			$params->set('row2col3', $params->get('drow2col3'));
			$params->set('r2c3custom', $params->get('dr2c3custom'));
			$params->set('r2c3customlabel', $params->get('dr1c1customlabel'));
			$params->set('r2c3span', $params->get('dr2c3span'));
			$params->set('linkr2c3', $params->get('dlinkr2c3'));

			$params->set('row2col4', $params->get('drow2col4'));
			$params->set('r2c4custom', $params->get('dr2c4custom'));
			$params->set('r2c4customlabel', $params->get('dr1c1customlabel'));
			$params->set('linkr2c4', $params->get('dlinkr2c4'));

			$params->set('row3col1', $params->get('drow3col1'));
			$params->set('r3c1custom', $params->get('dr3c1custom'));
			$params->set('r3c1customlabel', $params->get('dr1c1customlabel'));
			$params->set('r3c1span', $params->get('dr3c1span'));
			$params->set('linkr3c1', $params->get('dlinkr3c1'));

			$params->set('row3col2', $params->get('drow3col2'));
			$params->set('r3c2custom', $params->get('dr3c2custom'));
			$params->set('r3c2customlabel', $params->get('dr1c1customlabel'));
			$params->set('r3c2span', $params->get('dr3c2span'));
			$params->set('linkr3c2', $params->get('dlinkr3c2'));

			$params->set('row3col3', $params->get('drow3col3'));
			$params->set('r3c3custom', $params->get('dr3c3custom'));
			$params->set('r3c3customlabel', $params->get('dr1c1customlabel'));
			$params->set('r3c3span', $params->get('dr3c3span'));
			$params->set('linkr3c3', $params->get('dlinkr3c3'));

			$params->set('row3col4', $params->get('drow3col4'));
			$params->set('r3c4custom', $params->get('dr3c4custom'));
			$params->set('r3c4customlabel', $params->get('dr1c1customlabel'));
			$params->set('linkr3c4', $params->get('dlinkr3c4'));

			$params->set('row4col1', $params->get('drow4col1'));
			$params->set('r4c1custom', $params->get('dr4c1custom'));
			$params->set('r4c1customlabel', $params->get('dr1c1customlabel'));
			$params->set('r4c1span', $params->get('dr4c1span'));
			$params->set('linkr4c1', $params->get('dlinkr4c1'));

			$params->set('row4col2', $params->get('drow4col2'));
			$params->set('r4c2custom', $params->get('dr4c2custom'));
			$params->set('r4c2customlabel', $params->get('dr1c1customlabel'));
			$params->set('r4c2span', $params->get('dr4c2span'));
			$params->set('linkr4c2', $params->get('dlinkr4c2'));

			$params->set('row4col3', $params->get('drow4col3'));
			$params->set('r4c3custom', $params->get('dr4c3custom'));
			$params->set('r4c3customlabel', $params->get('dr1c1customlabel'));
			$params->set('r4c3span', $params->get('dr4c3span'));
			$params->set('linkr4c3', $params->get('dlinkr4c3'));

			$params->set('row4col4', $params->get('drow4col4'));
			$params->set('r4c4custom', $params->get('dr4c4custom'));
			$params->set('r4c4customlabel', $params->get('dr1c1customlabel'));
			$params->set('linkr4c4', $params->get('dlinkr4c4'));
		}

		// Need to know if last column and last row
		$columns = 1;

		if ($params->get('row1col2') > 0 || $params->get('row2col2') > 0 || $params->get('row3col2') > 0 || $params->get('row4col2') > 0)
		{
			$columns = 2;
		}
		if ($params->get('row1col3') > 0 || $params->get('row2col3') > 0 || $params->get('row3col3') > 0 || $params->get('row4col3') > 0)
		{
			$columns = 3;
		}
		if ($params->get('row1col4') > 0 || $params->get('row2col4') > 0 || $params->get('row3col4') > 0 || $params->get('row4col4') > 0)
		{
			$columns = 4;
		}
		$rows = 1;

		if ($params->get('row2col1') > 0 || $params->get('row2col2') > 0 || $params->get('row2col3') > 0 || $params->get('row2col4') > 0)
		{
			$rows = 2;
		}
		if ($params->get('row3col1') > 0 || $params->get('row3col2') > 0 || $params->get('row3col3') > 0 || $params->get('row3col4') > 0)
		{
			$rows = 3;
		}
		if ($params->get('row4col1') > 0 || $params->get('row4col2') > 0 || $params->get('row4col3') > 0 || $params->get('row4col4') > 0)
		{
			$rows = 4;
		}

		$id3          = $row->id;
		$smenu        = $params->get('detailsitemid');
		$tmenu        = $params->get('teacheritemid');
		$tid          = $row->teacher_id;
		$entry_access = $admin_params->get('entry_access');
		$allow_entry  = $admin_params->get('allow_entry_study');

		// This is the beginning of row 1
		$lastrow = 0;

		if ($rows == 1)
		{
			$lastrow = 1;
		}

		// This begins the row of the display data
		$listing = '<tr class="' . $oddeven;

		if ($lastrow == 1)
		{
			$listing .= ' lastrow';
		}
		$listing .= '">';

		$rowcolid = 'row1col1';

		if ($params->get('row1col1') < 1)
		{
			$params->set('row1col1', 100);
		}
		if ($params->get('row1col1') == 24)
		{
			$elementid             = $custom->getCustom($params->get('row1col1'), $params->get('r1c1custom'), $row, $params, $admin_params, $template);
			$elementid->headertext = $params->get('r1c1customlabel');
		}
		else
		{
			$elementid = JBSMElements::getElementid($params->get('row1col1'), $row, $params, $admin_params, $template);
		}
		$colspan = $params->get('r1c1span');
		$rowspan = $params->get('rowspanr1c1');
		$lastcol = 0;

		if ($columns == 1 || $colspan > 3)
		{
			$lastcol = 1;
		}

		if (isset($elementid))
		{
			$listing .= self::getCell(
				$elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr1c1'),
				$id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry, $params, $admin_params, $row, $template
			);
		}

		if ($columns > 1 && $params->get('r1c1span') < 2)
		{
			$rowcolid = 'row1col2';

			if ($params->get('row1col2') < 1)
			{
				$params->set('row1col2', 100);
			}

			if ($params->get('row1col2') == 24)
			{
				$elementid             = $custom->getCustom($params->get('row1col2'), $params->get('r1c2custom'), $row, $params, $admin_params, $template);
				$elementid->headertext = $params->get('r1c2customlabel');
			}
			else
			{
				$elementid = JBSMElements::getElementid($params->get('row1col2'), $row, $params, $admin_params, $template);
			}
			$colspan = $params->get('r1c2span');
			$rowspan = $params->get('rowspanr1c2');
			$lastcol = 0;

			if ($columns == 2 || $colspan > 2)
			{
				$lastcol = 1;
			}

			if (isset($elementid))
			{
				$listing .= self::getCell(
					$elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol,
					$params->get('linkr1c2'), $id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry, $params, $admin_params, $row, $template
				);
			}
		}

		if ($columns > 2 && ($params->get('r1c1span') < 3 && $params->get('r1c2span') < 2))
		{
			$rowcolid = 'row1col3';

			if ($params->get('row1col3') < 1)
			{
				$params->set('row1col3', 100);
			}
			if ($params->get('row1col3') == 24)
			{
				$elementid             = $custom->getCustom($params->get('row1col3'), $params->get('r1c3custom'), $row, $params, $admin_params, $template);
				$elementid->headertext = $params->get('r1c3customlabel');
			}
			else
			{
				$elementid = JBSMElements::getElementid($params->get('row1col3'), $row, $params, $admin_params, $template);
			}
			$colspan = $params->get('r1c3span');
			$rowspan = $params->get('rowspanr1c3');
			$lastcol = 0;

			if ($columns == 3 || $colspan > 1)
			{
				$lastcol = 1;
			}
			if (isset($elementid))
			{
				$listing .= self::getCell(
					$elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr1c3'),
					$id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry, $params, $admin_params, $row, $template
				);
			}
		}

		if ($columns > 3 && ($params->get('r1c1span') < 4 && $params->get('r1c2span') < 3 && $params->get('r1c3span') < 2))
		{
			$rowcolid = 'row1col4';

			if ($params->get('row1col4') < 1)
			{
				$params->set('row1col4', 100);
			}
			if ($params->get('row1col4') == 24)
			{
				$elementid             = $custom->getCustom($params->get('row1col4'), $params->get('r1c4custom'), $row, $params, $admin_params, $template);
				$elementid->headertext = $params->get('r1c4customlabel');
			}
			else
			{
				$elementid = JBSMElements::getElementid($params->get('row1col4'), $row, $params, $admin_params, $template);
			}
			$colspan = $params->get('r1c4span');
			$rowspan = $params->get('rowspanr1c4');
			$lastcol = 0;

			if ($columns == 4)
			{
				$lastcol = 1;
			}
			if (isset($elementid))
			{

				$listing .= self::getCell(
					$elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol,
					$params->get('linkr1c4'), $id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry, $params, $admin_params, $row, $template
				);
			}
		}
		$listing .= '</tr>';

		// This ends the row of the data to be displayed

		// This is the end of row 1

		// This is the beginning of row 2

		$lastrow = 0;

		if ($rows == 2)
		{
			$lastrow = 1;
		}
		$listing .= '<tr class="' . $oddeven;

		// This begins the row of the display data

		if ($lastrow == 1)
		{
			$listing .= ' lastrow';
		}

		$listing .= '">';

		$rowcolid = 'row2col1';

		if ($params->get('row2col1') < 1)
		{
			$params->set('row2col1', 100);
		}
		if ($params->get('row2col1') == 24)
		{
			$elementid             = $custom->getCustom($params->get('row2col1'), $params->get('r2c1custom'), $row, $params, $admin_params, $template);
			$elementid->headertext = $params->get('r2c1customlabel');
		}
		else
		{
			$elementid = JBSMElements::getElementid($params->get('row2col1'), $row, $params, $admin_params, $template);
		}
		$colspan = $params->get('r2c1span');
		$rowspan = $params->get('rowspanr2c1');

		$lastcol = 0;

		if ($columns == 1 || $colspan > 3)
		{
			$lastcol = 1;
		}
		if (isset($elementid))
		{
			$listing .= self::getCell(
				$elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr2c1'),
				$id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry, $params, $admin_params, $row, $template
			);
		}
		if ($columns > 1 && $params->get('r2c1span') < 2)
		{
			$rowcolid = 'row2col2';

			if ($params->get('row2col2') < 1)
			{
				$params->set('row2col2', 100);
			}
			if ($params->get('row2col2') == 24)
			{
				$elementid             = $custom->getCustom($params->get('row2col2'), $params->get('r2c2custom'), $row, $params, $admin_params, $template);
				$elementid->headertext = $params->get('r2c2customlabel');
			}
			else
			{
				$elementid = JBSMElements::getElementid($params->get('row2col2'), $row, $params, $admin_params, $template);
			}
			$colspan = $params->get('r2c2span');
			$rowspan = $params->get('rowspanr2c2');
			$lastcol = 0;

			if ($columns == 2 || $colspan > 2)
			{
				$lastcol = 1;
			}
			if (isset($elementid))
			{
				$listing .= self::getCell(
					$elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr2c2'),
					$id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry, $params, $admin_params, $row, $template
				);
			}
		}

		if ($columns > 2 && ($params->get('r2c1span') < 3 && $params->get('r2c2span') < 2))
		{
			$rowcolid = 'row2col3';

			if ($params->get('row2col3') < 1)
			{
				$params->set('row2col3', 100);
			}
			if ($params->get('row2col3') == 24)
			{
				$elementid             = $custom->getCustom($params->get('row2col3'), $params->get('r2c3custom'), $row, $params, $admin_params, $template);
				$elementid->headertext = $params->get('r2c3customlabel');
			}
			else
			{
				$elementid = JBSMElements::getElementid($params->get('row2col3'), $row, $params, $admin_params, $template);
			}
			$colspan = $params->get('r2c3span');
			$rowspan = $params->get('rowspanr2c3');
			$lastcol = 0;

			if ($columns == 3 || $colspan > 1)
			{
				$lastcol = 1;
			}
			if (isset($elementid))
			{
				$listing .= self::getCell(
					$elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr2c3'),
					$id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry, $params, $admin_params, $row, $template
				);
			}
		}

		if ($columns > 3 && ($params->get('r2c1span') < 4 && $params->get('r2c2span') < 3 && $params->get('r2c3span') < 2))
		{
			$rowcolid = 'row2col4';

			if ($params->get('row2col4') < 1)
			{
				$params->set('row2col4', 100);
			}
			if ($params->get('row2col4') == 24)
			{
				$elementid             = $custom->getCustom($params->get('row2col4'), $params->get('r2c4custom'), $row, $params, $admin_params, $template);
				$elementid->headertext = $params->get('r2c4customlabel');
			}
			else
			{
				$elementid = JBSMElements::getElementid($params->get('row2col4'), $row, $params, $admin_params, $template);
			}
			$colspan = $params->get('r2c4span');
			$rowspan = $params->get('rowspanr2c4');
			$lastcol = 0;

			if ($columns == 4)
			{
				$lastcol = 1;
			}
			if (isset($elementid))
			{
				$listing .= self::getCell(
					$elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr2c4'),
					$id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry, $params, $admin_params, $row, $template
				);
			}
		}
		$listing .= '</tr>';

		// This ends the row of the data to be displayed

		// End of row 2

		// Beginning of row 3

		$lastrow = 0;

		if ($rows == 3)
		{
			$lastrow = 1;
		}

		// This begins the row of the display data
		$listing .= '<tr class="' . $oddeven;

		if ($lastrow == 1)
		{
			$listing .= ' lastrow';
		}

		$listing .= '">';

		$rowcolid = 'row3col1';

		if ($params->get('row3col1') < 1)
		{
			$params->set('row3col1', 100);
		}
		if ($params->get('row3col1') == 24)
		{
			$elementid             = $custom->getCustom($params->get('row3col1'), $params->get('r3c1custom'), $row, $params, $admin_params, $template);
			$elementid->headertext = $params->get('r3c1customlabel');
		}
		else
		{
			$elementid = JBSMElements::getElementid($params->get('row3col1'), $row, $params, $admin_params, $template);
		}
		$colspan = $params->get('r3c1span');
		$rowspan = $params->get('rowspanr3c1');

		$lastcol = 0;

		if ($columns == 1 || $colspan > 3)
		{
			$lastcol = 1;
		}
		if (isset($elementid))
		{
			$listing .= self::getCell(
				$elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr3c1'), $id3,
				$tid, $smenu, $tmenu, $entry_access, $allow_entry, $params, $admin_params, $row, $template
			);
		}
		if ($columns > 1 && $params->get('r3c1span') < 2)
		{
			$rowcolid = 'row3col2';

			if ($params->get('row3col2') < 1)
			{
				$params->set('row3col2', 100);
			}
			if ($params->get('row3col2') == 24)
			{
				$elementid             = $custom->getCustom($params->get('row3col2'), $params->get('r3c2custom'), $row, $params, $admin_params, $template);
				$elementid->headertext = $params->get('r3c2customlabel');
			}
			else
			{
				$elementid = JBSMElements::getElementid($params->get('row3col2'), $row, $params, $admin_params, $template);
			}
			$colspan = $params->get('r3c2span');
			$rowspan = $params->get('rowspanr3c2');
			$lastcol = 0;

			if ($columns == 2 || $colspan > 2)
			{
				$lastcol = 1;
			}
			if (isset($elementid))
			{
				$listing .= self::getCell(
					$elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr3c2'),
					$id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry, $params, $admin_params, $row, $template
				);
			}
		}

		if ($columns > 2 && ($params->get('r3c1span') < 3 && $params->get('r3c2span') < 2))
		{
			$rowcolid = 'row3col3';

			if ($params->get('row3col3') < 1)
			{
				$params->set('row3col3', 100);
			}
			if ($params->get('row3col3') == 24)
			{
				$elementid             = $custom->getCustom($params->get('row3col3'), $params->get('r3c3custom'), $row, $params, $admin_params, $template);
				$elementid->headertext = $params->get('r3c3customlabel');
			}
			else
			{
				$elementid = JBSMElements::getElementid($params->get('row3col3'), $row, $params, $admin_params, $template);
			}
			$colspan = $params->get('r3c3span');
			$rowspan = $params->get('rowspanr3c3');
			$lastcol = 0;

			if ($columns == 3 || $colspan > 1)
			{
				$lastcol = 1;
			}
			if (isset($elementid))
			{
				$listing .= self::getCell(
					$elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr3c3'),
					$id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry, $params, $admin_params, $row, $template
				);
			}
		}

		if ($columns > 3 && ($params->get('r3c1span') < 4 && $params->get('r3c2span') < 3 && $params->get('r3c3span') < 2))
		{
			$rowcolid = 'row3col4';

			if ($params->get('row3col4') < 1)
			{
				$params->set('row3col4', 100);
			}
			if ($params->get('row3col4') == 24)
			{
				$elementid             = $custom->getCustom($params->get('row3col4'), $params->get('r3c4custom'), $row, $params, $admin_params, $template);
				$elementid->headertext = $params->get('r3c4customlabel');
			}
			else
			{
				$elementid = JBSMElements::getElementid($params->get('row3col4'), $row, $params, $admin_params, $template);
			}
			$colspan = $params->get('r3c4span');
			$rowspan = $params->get('rowspanr3c4');
			$lastcol = 0;

			if ($columns == 4)
			{
				$lastcol = 1;
			}
			if (isset($elementid))
			{
				$listing .= self::getCell(
					$elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr3c4'),
					$id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry, $params, $admin_params, $row, $template
				);
			}
		}

		// This ends the row of the data to be displayed
		$listing .= '</tr>';

		// End of row 3
		// Beginning of row 4
		$lastrow = 0;

		if ($rows == 4)
		{
			$lastrow = 1;
		}

		// This begins the row of the display data
		$listing .= '<tr class="' . $oddeven;

		if ($lastrow == 1)
		{
			$listing .= ' lastrow';
		}

		$listing .= '">';

		$rowcolid = 'row4col1';

		if ($params->get('row4col1') < 1)
		{
			$params->set('row4col1', 100);
		}
		if ($params->get('row4col1') == 24)
		{
			$elementid             = $custom->getCustom($params->get('row4col1'), $params->get('r4c1custom'), $row, $params, $admin_params, $template);
			$elementid->headertext = $params->get('r4c1customlabel');
		}
		else
		{
			$elementid = JBSMElements::getElementid($params->get('row4col1'), $row, $params, $admin_params, $template);
		}
		$colspan = $params->get('r4c1span');
		$rowspan = $params->get('rowspanr4c1');

		$lastcol = 0;

		if ($columns == 1 || $colspan > 3)
		{
			$lastcol = 1;
		}
		if (isset($elementid))
		{
			$listing .= self::getCell(
				$elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr4c1'), $id3,
				$tid, $smenu, $tmenu, $entry_access, $allow_entry, $params, $admin_params, $row, $template
			);
		}

		if ($columns > 1 && $params->get('r4c1span') < 2)
		{
			$rowcolid = 'row4col2';

			if ($params->get('row4col2') < 1)
			{
				$params->set('row4col2', 100);
			}
			if ($params->get('row4col2') == 24)
			{
				$elementid             = $custom->getCustom($params->get('row4col2'), $params->get('r4c2custom'), $row, $params, $admin_params, $template);
				$elementid->headertext = $params->get('r4c2customlabel');
			}
			else
			{
				$elementid = JBSMElements::getElementid($params->get('row4col2'), $row, $params, $admin_params, $template);
			}
			$colspan = $params->get('r4c2span');
			$rowspan = $params->get('rowspanr4c2');
			$lastcol = 0;

			if ($columns == 2 || $colspan > 2)
			{
				$lastcol = 1;
			}
			if (isset($elementid))
			{
				$listing .= self::getCell(
					$elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr4c2'),
					$id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry, $params, $admin_params, $row, $template
				);
			}
		}

		if ($columns > 2 && ($params->get('r4c1span') < 3 && $params->get('r4c2span') < 2))
		{
			$rowcolid = 'row4col3';

			if ($params->get('row4col3') < 1)
			{
				$params->set('row4col3', 100);
			}
			if ($params->get('row4col3') == 24)
			{
				$elementid             = $custom->getCustom($params->get('row4col3'), $params->get('r4c3custom'), $row, $params, $admin_params, $template);
				$elementid->headertext = $params->get('r4c3customlabel');
			}
			else
			{
				$elementid = JBSMElements::getElementid($params->get('row4col3'), $row, $params, $admin_params, $template);
			}
			$colspan = $params->get('r4c3span');
			$rowspan = $params->get('rowspanr4c3');
			$lastcol = 0;

			if ($columns == 3 || $colspan > 1)
			{
				$lastcol = 1;
			}
			if (isset($elementid))
			{
				$listing .= self::getCell(
					$elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol,
					$params->get('linkr4c3'), $id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry, $params, $admin_params, $row, $template
				);
			}
		}

		if ($columns > 3 && ($params->get('r4c1span') < 4 && $params->get('r4c2span') < 3 && $params->get('r4c3span') < 2))
		{
			$rowcolid = 'row4col4';

			if ($params->get('row4col4') < 1)
			{
				$params->set('row4col4', 100);
			}
			if ($params->get('row4col4') == 24)
			{
				$elementid             = $custom->getCustom($params->get('row4col4'), $params->get('r4c4custom'), $row, $params, $admin_params, $template);
				$elementid->headertext = $params->get('r4c4customlabel');
			}
			else
			{
				$elementid = JBSMElements::getElementid($params->get('row4col4'), $row, $params, $admin_params, $template);
			}
			$colspan = $params->get('r4c4span');
			$rowspan = $params->get('rowspanr4c4');
			$lastcol = 0;

			if ($columns == 4)
			{
				$lastcol = 1;
			}
			if (isset($elementid))
			{
				$listing .= self::getCell(
					$elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr4c4'),
					$id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry, $params, $admin_params, $row, $row, $template
				);
			}
		}

		// This ends the row of the data to be displayed
		$listing .= '</tr>';

		return $listing;
	}

	/**
	 * Get Cell
	 *
	 * @param   int       $elementid     Element ID
	 * @param   string    $element       Element
	 * @param   int       $rowcolid      Row Column ID
	 * @param   string    $colspan       Column Span
	 * @param   string    $rowspan       Row Span
	 * @param   string    $lastcol       Last Column
	 * @param   string    $islink        is a Link
	 * @param   string    $id3           ID3
	 * @param   int       $tid           Template ID
	 * @param   string    $smenu         Sermon Menu
	 * @param   string    $tmenu         Template Menu
	 * @param   string    $entry_access  Access Entry
	 * @param   string    $allow_entry   Allow Entry
	 * @param   JRegistry $params        Itom Params
	 * @param   JRegistry $admin_params  Admin Params
	 * @param   object    $row           Row info
	 * @param   int       $template      Template ID
	 *
	 * @return string
	 */
	private function getCell(
		$elementid,
		$element,
		$rowcolid,
		$colspan,
		$rowspan,
		$lastcol,
		$islink,
		$id3,
		$tid,
		$smenu,
		$tmenu,
		$entry_access,
		$allow_entry,
		$params,
		$admin_params,
		$row,
		$template)
	{

		$cell = '<td class="' . $rowcolid . ' ' . $elementid;

		if ($lastcol == 1)
		{
			$cell .= ' lastcol';
		}
		$cell .= '" ';

		if ($colspan > 1)
		{
			$cell .= 'colspan="' . $colspan . '" ';
		}
		$cell .= '>';

		if ($islink > 0)
		{
			$cell .= self::getLink($islink, $id3, $tid, $smenu, $tmenu, $params, $admin_params, $row, $template);
		}
		$cell .= $element;

		switch ($islink)
		{
			case 0:
				break;

			case 1:
				$cell .= '</a>';
				break;

			case 3:
				$cell .= '</a>';
				break;

			case 4:
				$cell .= '</a></span>';
				break;

			case 5:
				$cell .= '</a></span>';
				break;

			case 6:
				$cell .= '</a>';
				break;

			case 7:
				$cell .= '</a>';
				break;

			case 8:
				$cell .= '</a>';
				break;
		}
		$cell .= '</td>';

		return $cell;
	}

	/**
	 * Get Link
	 *
	 * @param   string    $islink        IS A Link
	 * @param   string    $id3           ID3
	 * @param   int       $tid           Template Id
	 * @param   string    $smenu         Sermon Menu
	 * @param   string    $tmenu         Teacher Menu
	 * @param   JRegistry $params        Item Params
	 * @param   JRegistry $admin_params  Admin Params
	 * @param   object    $row           Item Info
	 * @param   int       $template      Template
	 *
	 * @return string
	 */
	private function getLink($islink, $id3, $tid, $smenu, $tmenu, $params, $admin_params, $row, $template)
	{
		$input    = new JInput;
		$Itemid   = $input->get('Itemid', '', 'int');
		$column   = '';
		$mime     = ' AND #__bsms_mediafiles.mime_type = 1';
		$itemlink = $params->get('itemidlinktype');

		switch ($islink)
		{

			case 1 :
				$Itemid = $input->get('Itemid', '', 'int');

				if (!$Itemid)
				{
					$link = JRoute::_('index.php?option=com_biblestudy&view=sermon&id=' . $row->slug . '&t=' . $params->get('detailstemplateid'));
				}
				else
				{
					$link = JRoute::_('index.php?option=com_biblestudy&view=sermon&id=' . $row->slug . '&t=' . $params->get('detailstemplateid'));
				}
				$column = '<a href="' . $link . '">';
				break;

			case 2 :
				$filepath = $this->getFilepath($id3, 'study_id', $mime);
				$link     = JRoute::_($filepath);
				$column .= '<a href="' . $link . '">';
				break;

			case 3 :
				$link = JRoute::_('index.php?option=com_biblestudy&view=teacher&id=' . $tid . '&t=' . $params->get('teachertemplateid'));

				if ($tmenu > 0)
				{
					$link .= '&Itemid=' . $tmenu;
				}
				$column .= '<a href="' . $link . '">';
				break;

			case 4 :
				// Case 4 is a details link with tooltip
				if (!$Itemid)
				{
					$link = JRoute::_(JBSMHelperRoute::getArticleRoute($row->slug) . '&t=' . $params->get('detailstemplateid'));
				}
				else
				{
					$link = JRoute::_(JBSMHelperRoute::getArticleRoute($row->slug) . '&t=' . $params->get('detailstemplateid'));
				}
				$column = JBSMHelper::getTooltip($row->id, $row, $params, $admin_params, $template);
				$column .= '<a href="' . $link . '">';

				break;

			case 5 :
				// Case 5 is a file link with Tooltip
				$filepath = $this->getFilepath($id3, 'study_id', $mime);
				$link     = JRoute::_($filepath);
				$column   = JBSMHelper::getTooltip($row->id, $row, $params, $admin_params, $template);
				$column .= '<a href="' . $link . '">';

				break;

			case 6 :
				// Case 6 is for a link to the 1st article in the media file records
				$column .= '<a href="' . $this->getOtherlinks($id3, $islink, $params) . '">';
				break;

			case 7 :
				// Case 7 is for Virtuemart
				$column .= '<a href="' . $this->getOtherlinks($id3, $islink, $params) . '">';
				break;

			case 8 :
				// Case 8 is for Docman
				$column .= '<a href="' . $this->getOtherlinks($id3, $islink, $params) . '">';
				break;

			case 9 :
				// Case 9 is a link to download
				$column .= '<a href="index.php?option=com_biblestudy&amp;mid=' .
					$row->download_id . '&amp;view=sermons&amp;task=download">';
		}

		return $column;
	}

	/**
	 * Get Listing Exp
	 *
	 * @param   object    $row           Item Info
	 * @param   JRegistry $params        Item Params
	 * @param   JRegistry $admin_params  Admin Params
	 * @param   object    $template      Template
	 *
	 * @return object
	 */
	public function getListingExp($row, $params, $admin_params, $template)
	{
		$Media  = new jbsMedia;
		$images = new JBSMImages;
		$image  = $images->getStudyThumbnail($row->thumbnailm);
		$label  = $params->get('templatecode');
		$label  = str_replace('{{teacher}}', $row->teachername, $label);
		$label  = str_replace('{{title}}', $row->studytitle, $label);
		$label  = str_replace('{{date}}', $this->getStudydate($params, $row->studydate), $label);
		$label  = str_replace('{{studyintro}}', $row->studyintro, $label);
		$label  = str_replace('{{scripture}}', $this->getScripture($params, $row, 0, 1), $label);
		$label  = str_replace('{{topics}}', $row->topic_text, $label);
		$label  = str_replace('{{url}}', JRoute::_('index.php?option=com_biblestudy&view=sermon&id=' . $row->id . '&t=' . $template->id), $label);
		$label  = str_replace('{{mediatime}}', $this->getDuration($params, $row), $label);
		$label  = str_replace('{{thumbnail}}', '<img src="' . $image->path . '" width="' . $image->width . '" height="'
			. $image->height . '" id="bsms_studyThumbnail" />', $label
		);
		$label  = str_replace('{{seriestext}}', $row->series_text, $label);
		$label  = str_replace('{{messagetype}}', $row->message_type, $label);
		$label  = str_replace('{{bookname}}', $row->bookname, $label);
		$label  = str_replace('{{topics}}', $row->topic_text, $label);
		$label  = str_replace('{{hits}}', $row->hits, $label);
		$label  = str_replace('{{location}}', $row->location_text, $label);
		$label  = str_replace('{{plays}}', $row->totalplays, $label);
		$label  = str_replace('{{downloads}}', $row->totaldownloads, $label);

		// For now we need to use the existing mediatable function to get all the media
		$mediaTable = $Media->getMediaTable($row, $params, $admin_params);
		$label      = str_replace('{{media}}', $mediaTable, $label);

		// Need to add template items for media...

		return $label;
	}

	/**
	 * Get Study Exp
	 *
	 * @param   object    $row           Item Info
	 * @param   JRegistry $params        Item Params
	 * @param   JRegistry $admin_params  Admin Params
	 * @param   object    $template      Template
	 *
	 * @return object
	 */
	public function getStudyExp($row, $params, $admin_params, $template)
	{
		$Media = new jbsMedia;

		$images = new JBSMImages;
		$image  = $images->getStudyThumbnail($row->thumbnailm);
		$label  = $params->get('study_detailtemplate');
		$label  = str_replace('{{teacher}}', $row->teachername, $label);
		$label  = str_replace('{{title}}', $row->studytitle, $label);
		$label  = str_replace('{{date}}', $this->getStudydate($params, $row->studydate), $label);
		$label  = str_replace('{{studyintro}}', $row->studyintro, $label);
		$label  = str_replace('{{scripture}}', $this->getScripture($params, $row, 0, 1), $label);
		$label  = str_replace('{{topics}}', $row->topic_text, $label);
		$label  = str_replace('{{mediatime}}', $this->getDuration($params, $row), $label);
		$label  = str_replace('{{thumbnail}}', '<img src="' . $image->path . '" width="' . $image->width . '" height="'
			. $image->height . '" id="bsms_studyThumbnail" />', $label
		);
		$label  = str_replace('{{seriestext}}', $row->seriestext, $label);
		$label  = str_replace('{{messagetype}}', $row->message_type, $label);
		$label  = str_replace('{{bookname}}', $row->bname, $label);
		$label  = str_replace('{{studytext}}', $row->studytext, $label);
		$label  = str_replace('{{hits}}', $row->hits, $label);
		$label  = str_replace('{{location}}', $row->location_text, $label);

		// Passage
		$link = '<strong><a class="heading" href="javascript:ReverseDisplay(\'bsms_scripture\')">>>' . JText::_('JBS_CMN_SHOW_HIDE_SCRIPTURE') . '<<</a>';
		$link .= '<div id="bsms_scripture" style="display:none;"></strong>';
		$response = $this->getPassage($params, $row);
		$link .= $response;
		$link .= '</div>';
		$label = str_replace('{{scripturelink}}', $link, $label);
		$label = str_replace('{{plays}}', $row->totalplays, $label);
		$label = str_replace('{{downloads}}', $row->totaldownloads, $label);


		$mediaTable = $Media->getMediaTable($row, $params, $admin_params);
		$label      = str_replace('{{media}}', $mediaTable, $label);

		// Share
		// Prepares a link string for use in social networking
		$u           = JURI::getInstance();
		$detailslink = htmlspecialchars($u->toString());
		$detailslink = JRoute::_($detailslink);

		// End social networking
		$share = $this->getShare($detailslink, $row, $params, $admin_params);
		$label = str_replace('{{share}}', $share, $label);

		// PrintableView
		$printview = JHTML::_('image.site', 'printButton.png', '/images/M_images/', null, null, JText::_('JBS_CMN_PRINT'));
		$printview = '<a href="#&tmpl=component" onclick="window.print();return false;">' . $printview . '</a>';

		$label = str_replace('{{printview}}', $printview, $label);

		// PDF View
		$url                = 'index.php?option=com_biblestudy&view=sermon&id=' . $row->id . '&format=pdf';
		$status             = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';
		$text               = JHTML::_(
			'image.site', 'pdf24.png', '/media/com_biblestudy/images/', null, null, JText::_('JBS_MED_PDF'), JText::_('JBS_MED_PDF')
		);
		$attribs['title']   = JText::_('JBS_MED_PDF');
		$attribs['onclick'] = "window.open(this.href,'win2','" . $status . "'); return false;";
		$attribs['rel']     = 'nofollow';
		$link               = JHTML::_('link', JRoute::_($url), $text, $attribs);

		$label = str_replace('{{pdfview}}', $link, $label);

		// Comments

		return $label;
	}

	/**
	 * Share Helper file
	 *
	 * @param   string    $link          Link
	 * @param   object    $row           Item Info
	 * @param   JRegistry $params        Item Params
	 * @param   JRegistry $admin_params  Admin Params
	 *
	 * @return null|string
	 *
	 * FIXME Look like this is missing the $template var
	 */
	public function getShare($link, $row, $params, $admin_params)
	{
		jimport('joomla.html.parameter');

		// Finde a better way to do this.
		$template = (int) '1';

		$sharetype = $admin_params->get('sharetype', 1);

		if ($sharetype == 1)
		{
			$shareit = '<div id="bsms_share"><table class="table" id="bsmsshare"><thead>
						<tr class="bsmssharetitlerow">
						<th id="bsmssharetitle" </th></tr></thead>
						<tbody><tr class="bsmsshareiconrow">';
			$shareit .= '<td id="bsmsshareicons"><!-- AddThis Button BEGIN -->
						<a class="addthis_button" href="http://www.addthis.com/bookmark.php?v=250&amp;username=tomfuller2">
						<img src="http://s7.addthis.com/static/btn/v2/lg-share-en.gif" width="125" height="16" alt="Bookmark and Share" style="border:0"/>
						</a>
						<script type="text/javascript">var addthis_config = {"data_track_clickback":true};</script>
						<script type="text/javascript" src="//s7.addthis.com/js/250/addthis_widget.js#username="></script>
						<!-- AddThis Button END --></td>';
		}
		else
		{
			// This will come from $admin_params
			$sharetitle = 'Share This';

			// Get the information from the database on what social networking sites to use
			$db    = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select('*')->from('#__bsms_share')->where('published = ' . 1)->order('name asc');
			$db->setQuery($query);
			$rows      = $db->loadObjectList();
			$sharerows = count($rows);

			if ($sharerows < 1)
			{
				$share = null;

				return $share;
			}

			// Begin to form the table
			$shareit = '<div id="bsms_share"><table class="table" id="bsmsshare"><thead>
						<tr class="bsmssharetitlerow">
						<th id="bsmssharetitle" colspan=' . $sharerows . '>' . $sharetitle . '</th></tr></thead>
						<tbody><tr class="bsmsshareiconrow">';

			foreach ($rows as $sharerow)
			{

				// Convert parameter fields to objects.
				$registry = new JRegistry;
				$registry->loadString($sharerow->params);
				$share_params = $registry;
				$custom       = new JBSMCustom;

				$image      = $share_params->get('shareimage');
				$height     = $share_params->get('shareimageh', '44px');
				$width      = $share_params->get('shareimagew', '44px');
				$totalchars = $share_params->get('totalcharacters');
				$use_bitly  = $share_params->get('use_bitly');
				$mainlink   = $share_params->get('mainlink');
				$appkey     = $share_params->get('api', 'R_dc86635ad2d1e883cab8fad316ca12f6');
				$login      = $share_params->get('username', 'joomlabiblestudy');

				if ($use_bitly == 1)
				{
					$url = $this->make_bitly_url($link, $login, $appkey, 'json', '2.0.1');
				}
				else
				{
					$url = $link;
				}
				$element1          = new stdClass;
				$element1->element = '';
				$element2          = new stdClass;
				$element2->element = '';
				$element3          = new stdClass;
				$element3->element = '';
				$element4          = new stdClass;
				$element4->element = '';

				if ($share_params->get('item1'))
				{
					if ($share_params->get('item1') == 200)
					{
						$element1->element = $url;
					}
					elseif ($share_params->get('item1') == 24)
					{
						$element           = $custom->getCustom(
							$share_params->get('item1'), $share_params->get('item1custom'), $row, $params, $admin_params, $template
						);
						$element1->element = $element->element;
					}
					else
					{
						$element1 = JBSMElements::getElementid($share_params->get('item1'), $row, $params, $admin_params, $template);
					}
				}
				if ($share_params->get('item2'))
				{
					if ($share_params->get('item2') == 200)
					{
						$element2->element = $url;
					}
					elseif ($share_params->get('item2') == 24)
					{
						$element           = $custom->getCustom(
							$share_params->get('item2'), $share_params->get('item2custom'), $row, $params, $admin_params, $template
						);
						$element2->element = $element->element;
					}
					else
					{
						$element2 = JBSMElements::getElementid((int) $share_params->get('item2'), $row, $params, $admin_params, $template);
					}
				}
				if ($share_params->get('item3'))
				{
					if ($share_params->get('item3') == 200)
					{
						$element3->element = $url;
					}
					elseif ($share_params->get('item3') == 24)
					{
						$element           = $custom->getCustom(
							$share_params->get('item3'), $share_params->get('item3custom'),
							$row, $params, $admin_params, $template
						);
						$element3->element = $element->element;
					}
					else
					{
						$element3 = JBSMElements::getElementid($share_params->get('item3'), $row, $params, $admin_params, $template);
					}
				}
				if ($share_params->get('item4'))
				{
					if ($share_params->get('item4') == 200)
					{
						$element4->element = $url;
					}
					elseif ($share_params->get('item4') == 24)
					{
						$element           = $custom->getCustom(
							$share_params->get('item4'), $share_params->get('item4custom'), $row, $params, $admin_params, $template
						);
						$element4->element = $element->element;
					}
					else
					{
						$element4 = JBSMElements::getElementid($share_params->get('item4'), $row, $params, $admin_params, $template);
					}
				}

				$sharelink = $element1->element . ' ' . $share_params->get('item2prefix') . $element2->element . ' ' . $share_params->get('item3prefix')
					. $element3->element . ' ' . $share_params->get('item4prefix') . $element4->element;

				// Added to see if would make Facebook sharer work
				$sharelink = urlencode($sharelink);

				if ($share_params->get('totalcharacters'))
				{
					$sharelength = strlen($sharelink);

					if ($sharelength > $share_params->get('totalcharacters'))
					{
						$linkstartposition  = strpos($sharelink, '//', 0);
						$linkendposition    = strpos($sharelink, ' ', $linkstartposition);
						$linkextract        = substr($sharelink, $linkstartposition, $linkendposition);
						$linklength         = strlen($linkextract);
						$sharelink          = substr_replace($sharelink, '', $linkstartposition, $linkendposition);
						$newsharelinklength = $share_params->get('totalcharacters') - $linklength - 1;
						$sharelink          = substr($sharelink, 0, $newsharelinklength);
						$sharelink          = $sharelink . ' ' . $linkextract;
					}
				}
				$shareit .= '<td id="bsmsshareicons">
							<a href="' . $mainlink . $share_params->get('item1prefix') . $sharelink . '" target="_blank">
							<img src="' . JURI::base() . $image . '" alt="' . $share_params->get('alttext') . '" title="'
					. $share_params->get('alttext') . '" width="' . $width . '" height="' . $height . '" border="0">
							</a></td>';

			} // End of foreach

		} // End of else $sharetype
		$shareit .= '</tr></tbody></table></div>';

		return $shareit;
	}

	/**
	 * make a URL small
	 *
	 * @param   string $url      Url
	 * @param   string $login    Login
	 * @param   string $appkey   AppKey
	 * @param   string $format   Format
	 * @param   string $version  Version
	 *
	 * @return string
	 */
	private function make_bitly_url($url, $login, $appkey, $format = 'xml', $version = '2.0.1')
	{
		// Create the URL

		$bitly = '//api.bit.ly/shorten?version=' . $version . '&longUrl=' . urlencode($url) . '&login='
			. $login . '&apiKey=' . $appkey . '&format=' . $format;

		// Get the url
		// Could also use cURL here
		$response = file_get_contents($bitly);

		// Parse depending on desired format
		if (strtolower($format) == 'json')
		{
			$json  = json_decode($response, true);
			$short = $json['results'][$url]['shortUrl'];
		}
		else
		{ // Xml
			$xml   = simplexml_load_string($response);
			$short = '//bit.ly/' . $xml->results->nodeKeyVal->hash;
		}

		return $short;
	}

	/**
	 * Get Passage
	 *
	 * @param   object $params  Item Params
	 * @param   object $row     Item Info
	 *
	 * @return string
	 */
	public function getPassage($params, $row)
	{
		$esv          = 1;
		$scripturerow = 1;
		$scripture    = $this->getScripture($params, $row, $esv, $scripturerow);

		if ($scripture)
		{
			$key      = "IP";
			$response = "" . $scripture . " (ESV)";
			$passage  = urlencode($scripture);
			$options  = "include-passage-references=false";
			$url      = "//www.esvapi.org/v2/rest/passageQuery?key=$key&passage=$passage&$options";

			// This tests to see if the curl functions are there. It will return false if curl not installed
			$p = (get_extension_funcs("curl"));

			if ($p)
			{ // If curl is installed then we go on

				// This will return false if curl is not enabled
				$ch = curl_init($url);

				if ($ch)
				{ // This will return false if curl is not enabled
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					$response .= curl_exec($ch);
					curl_close($ch);

				} // End of if ($ch)

			} // End if ($p)
		}
		else
		{
			$response = JText::_('JBS_STY_NO_PASSAGE_INCLUDED');
		}

		return $response;
	}

	/**
	 * Get Other Links
	 *
	 * @param   int    $id3     Study ID ID
	 * @param   string $islink  Is a Link
	 * @param   object $params  Item Params
	 *
	 * @return string
	 */
	public function getOtherlinks($id3, $islink, $params)
	{
		$link  = '';
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('#__bsms_mediafiles.*')
			->from('#__bsms_mediafiles')
			->where('study_id = ' . $db->q($id3))
			->where('#__bsms_mediafiles.published = 1');
		$db->setQuery($query);
		$db->query();
		$num_rows = $db->getNumRows();

		if ($num_rows > 0)
		{
			$mediafiles = $db->loadObjectList();

			foreach ($mediafiles AS $media)
			{
				switch ($islink)
				{
					case 6:
						if ($media->article_id > 0)
						{
							$link = 'index.php?option=com_content&view=article&id=' . $media->article_id;
						}
						break;

					case 7:
						if ($media->virtueMart_id > 0)
						{
							$link = 'index.php?option=com_virtuemart&page=shop.product_details&flypage='
								. $params->get('store_page', 'flypage.tpl') . '&product_id=' . $media->virtueMart_id;
						}
						break;

					case 8:
						if ($media->docMan_id > 0)
						{
							$link = 'index.php?option=com_docman&task=doc_download&gid=' . $media->docMan_id;
						}
						break;
				}
			}
		}

		return $link;
	}

	/**
	 * Get Title
	 *
	 * @param   JRegistry $params        System Params
	 * @param   object    $row           Item info
	 * @param   JRegistry $admin_params  Admin Params
	 * @param   int       $template      Template
	 *
	 * @return string
	 */
	public function getTitle($params, $row, $admin_params, $template)
	{

		$title  = null;
		$custom = new JBSMCustom;

		if ($params->get('title_line_1') > 0)
		{
			$title = '<table class="table" id="titletable"><tbody><tr><td class="titlefirstline">';

			switch ($params->get('title_line_1'))
			{
				case 0:
					$title .= null;
					break;
				case 1:
					$title .= $row->studytitle;
					break;
				case 2:
					$title .= $row->teachername;
					break;
				case 3:
					$title .= $row->title . ' ' . $row->teachername;
					break;
				case 4:
					$esv       = 0;
					$scripture = $this->getScripture($params, $row, $esv, $scripturerow = 1);
					$title .= $scripture;
					break;
				case 5:
					$title .= $row->stext;
					break;
				case 6:
					$title .= $row->topics_text;
					break;
				case 7:
					$elementid = $custom->getCustom($rowid = 0, $params->get('customtitle1'), $row, $params, $admin_params, $template);
					$title .= $elementid->element;
					break;
			}
			$title .= '</td></tr>';
		}

		if ($params->get('title_line_2') > 0)
		{
			$title .= '<tr><td class="titlesecondline" >';

			switch ($params->get('title_line_2'))
			{
				case 0:
					$title .= null;
					break;
				case 1:
					$title .= $row->studytitle;
					break;
				case 2:
					$title .= $row->teachername;
					break;
				case 3:
					$title .= $row->title . ' ' . $row->teachername;
					break;
				case 4:
					$esv       = 0;
					$scripture = $this->getScripture($params, $row, $esv, $scripturerow = 1);
					$title .= $scripture;
					break;
				case 5:
					$title .= $row->stext;
					break;
				case 6:
					$title .= $row->topics_text;
					break;
				case 7:
					$elementid = $custom->getCustom($rowid = 0, $params->get('customtitle2'), $row, $params, $admin_params, $template);
					$title .= $elementid->element;
					break;
			}
			$title .= '</td></tr>';

		} // End of if title2
		$title .= '</tbody></table>';

		return $title;
	}

	/**
	 * Get Header
	 *
	 * @param   object    $row           JTable
	 * @param   JRegistry $params        Item Params
	 * @param   JRegistry $admin_params  Admin Params
	 * @param   int       $template      Template ID
	 * @param   int       $showheader    Show Hide item
	 * @param   int       $ismodule      ?
	 *
	 * @return string
	 */
	public function getHeader($row, $params, $admin_params, $template, $showheader, $ismodule)
	{
		if (!$row)
		{
			return null;
		}
		// $nh checks to see if there is a header in use, otherwise it puts a line at the top of the listing
		$nh = false;

		if ($showheader < 1)
		{
			$nh = true;
		}

		/* Here we test to see if this is a sermon or list view. If details, we reset the params to the details.
		 this keeps us from having to rewrite all this code. */
		$input = new JInput;
		$view  = $input->get('view');

		if ($view == 'sermon' && $ismodule < 1)
		{

			$params->set('row1col1', $params->get('drow1col1'));
			$params->set('r1c1custom', $params->get('dr1c1custom'));
			$params->set('r1c1customlabel', $params->get('dr1c1customlabel'));
			$params->set('r1c1span', $params->get('dr1c1span'));
			$params->set('linkr1c1', $params->get('dlinkr1c1'));

			$params->set('row1col2', $params->get('drow1col2'));
			$params->set('r1c2custom', $params->get('dr1c2custom'));
			$params->set('r1c2customlabel', $params->get('dr1c1customlabel'));
			$params->set('r1c2span', $params->get('dr1c2span'));
			$params->set('linkr1c2', $params->get('dlinkr1c2'));

			$params->set('row1col3', $params->get('drow1col3'));
			$params->set('r1c3custom', $params->get('dr1c3custom'));
			$params->set('r1c3customlabel', $params->get('dr1c1customlabel'));
			$params->set('r1c3span', $params->get('dr1c3span'));
			$params->set('linkr1c3', $params->get('dlinkr1c3'));

			$params->set('row1col4', $params->get('drow1col4'));
			$params->set('r1c4custom', $params->get('dr1c4custom'));
			$params->set('r1c4customlabel', $params->get('dr1c1customlabel'));
			$params->set('linkr1c4', $params->get('dlinkr1c4'));

			$params->set('row2col1', $params->get('drow2col1'));
			$params->set('r2c1custom', $params->get('dr2c1custom'));
			$params->set('r2c1customlabel', $params->get('dr1c1customlabel'));
			$params->set('r2c1span', $params->get('dr2c1span'));
			$params->set('linkr2c1', $params->get('dlinkr2c1'));

			$params->set('row2col2', $params->get('drow2col2'));
			$params->set('r2c2custom', $params->get('dr2c2custom'));
			$params->set('r2c2customlabel', $params->get('dr1c1customlabel'));
			$params->set('r2c2span', $params->get('dr2c2span'));
			$params->set('linkr2c2', $params->get('dlinkr2c2'));

			$params->set('row2col3', $params->get('drow2col3'));
			$params->set('r2c3custom', $params->get('dr2c3custom'));
			$params->set('r2c3customlabel', $params->get('dr1c1customlabel'));
			$params->set('r2c3span', $params->get('dr2c3span'));
			$params->set('linkr2c3', $params->get('dlinkr2c3'));

			$params->set('row2col4', $params->get('drow2col4'));
			$params->set('r2c4custom', $params->get('dr2c4custom'));
			$params->set('r2c4customlabel', $params->get('dr1c1customlabel'));
			$params->set('linkr2c4', $params->get('dlinkr2c4'));

			$params->set('row3col1', $params->get('drow3col1'));
			$params->set('r3c1custom', $params->get('dr3c1custom'));
			$params->set('r3c1customlabel', $params->get('dr1c1customlabel'));
			$params->set('r3c1span', $params->get('dr3c1span'));
			$params->set('linkr3c1', $params->get('dlinkr3c1'));

			$params->set('row3col2', $params->get('drow3col2'));
			$params->set('r3c2custom', $params->get('dr3c2custom'));
			$params->set('r3c2customlabel', $params->get('dr1c1customlabel'));
			$params->set('r3c2span', $params->get('dr3c2span'));
			$params->set('linkr3c2', $params->get('dlinkr3c2'));

			$params->set('row3col3', $params->get('drow3col3'));
			$params->set('r3c3custom', $params->get('dr3c3custom'));
			$params->set('r3c3customlabel', $params->get('dr1c1customlabel'));
			$params->set('r3c3span', $params->get('dr3c3span'));
			$params->set('linkr3c3', $params->get('dlinkr3c3'));

			$params->set('row3col4', $params->get('drow3col4'));
			$params->set('r3c4custom', $params->get('dr3c4custom'));
			$params->set('r3c4customlabel', $params->get('dr1c1customlabel'));
			$params->set('linkr3c4', $params->get('dlinkr3c4'));

			$params->set('row4col1', $params->get('drow4col1'));
			$params->set('r4c1custom', $params->get('dr4c1custom'));
			$params->set('r4c1customlabel', $params->get('dr1c1customlabel'));
			$params->set('r4c1span', $params->get('dr4c1span'));
			$params->set('linkr4c1', $params->get('dlinkr4c1'));

			$params->set('row4col2', $params->get('drow4col2'));
			$params->set('r4c2custom', $params->get('dr4c2custom'));
			$params->set('r4c2customlabel', $params->get('dr1c1customlabel'));
			$params->set('r4c2span', $params->get('dr4c2span'));
			$params->set('linkr4c2', $params->get('dlinkr4c2'));

			$params->set('row4col3', $params->get('drow4col3'));
			$params->set('r4c3custom', $params->get('dr4c3custom'));
			$params->set('r4c3customlabel', $params->get('dr1c1customlabel'));
			$params->set('r4c3span', $params->get('dr4c3span'));
			$params->set('linkr4c3', $params->get('dlinkr4c3'));

			$params->set('row4col4', $params->get('drow4col4'));
			$params->set('r4c4custom', $params->get('dr4c4custom'));
			$params->set('r4c4customlabel', $params->get('dr1c1customlabel'));
			$params->set('linkr4c4', $params->get('dlinkr4c4'));
		}

		$columns = 1;

		if ($params->get('row1col2') > 0 || $params->get('row2col2') > 0 || $params->get('row3col2') > 0 || $params->get('row4col2') > 0)
		{
			$columns = 2;
		}
		if ($params->get('row1col3') > 0 || $params->get('row2col3') > 0 || $params->get('row3col3') > 0 || $params->get('row4col3') > 0)
		{
			$columns = 3;
		}
		if ($params->get('row1col4') > 0 || $params->get('row2col4') > 0 || $params->get('row3col4') > 0 || $params->get('row4col4') > 0)
		{
			$columns = 4;
		}
		$rows = 1;

		if ($params->get('row2col1') > 0 || $params->get('row2col2') > 0 || $params->get('row2col3') > 0 || $params->get('row2col4') > 0)
		{
			$rows = 2;
		}
		if ($params->get('row3col1') > 0 || $params->get('row3col2') > 0 || $params->get('row3col3') > 0 || $params->get('row3col4') > 0)
		{
			$rows = 3;
		}
		if ($params->get('row4col1') > 0 || $params->get('row4col2') > 0 || $params->get('row4col3') > 0 || $params->get('row4col4') > 0)
		{
			$rows = 4;
		}

		if ($nh)
		{
			$listing = '<tr>';

			while ($columns > 0)
			{
				$listing .= '<th class="firstrow"></th>';
				$columns--;
			}
			$listing .= '</tr>';
		}
		else
		{
			// Here we go through each position to see if it has a positive value, get the cell using getHeadercell and return the final header

			$listing = '<tr';

			if ($rows == 1)
			{
				$listing .= ' class = "lastrow"';
			}
			$listing .= '>';

			// Beginning of first column
			$colspan  = $params->get('r1c1span');
			$rowspan  = $params->get('rowspanr1c1');
			$rowcolid = 'row1col1';
			$lastcol  = 0;

			if ($columns == 1 || $colspan > 3)
			{
				$lastcol = 1;
			}
			if ($params->get('row1col1') < 1)
			{
				$params->set('row1col1', 100);
			}
			$listing .= $this->getHeadercell($params->get('row1col1'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);


			if ($columns > 1 && $params->get('r1c1span') < 2)
			{
				$colspan  = $params->get('r1c2span');
				$rowspan  = $params->get('rowspanr1c2');
				$rowcolid = 'row1col2';
				$lastcol  = 0;

				if ($columns == 2 || $colspan > 2)
				{
					$lastcol = 1;
				}
				if ($params->get('row1col2') < 1)
				{
					$params->set('row1col2', 100);
				}
				$listing .= $this->getHeadercell($params->get('row1col2'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);
			}
			if ($columns > 2 && ($params->get('r1c1span') < 3 && $params->get('r1c2span') < 2))
			{
				$colspan  = $params->get('r1c3span');
				$rowspan  = $params->get('rowspanr1c3');
				$rowcolid = 'row1col3';
				$lastcol  = 0;

				if ($columns == 3 || $colspan > 1)
				{
					$lastcol = 1;
				}
				if ($params->get('row1col3') < 1)
				{
					$params->set('row1col3', 100);
				}
				$listing .= $this->getHeadercell($params->get('row1col3'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);
			}
			if ($columns > 3 && ($params->get('r1c1span') < 4 && $params->get('r1c2span') < 3 && $params->get('r1c3span') < 2))
			{
				$colspan  = $params->get('r1c4span');
				$rowspan  = $params->get('rowspanr1c4');
				$rowcolid = 'row1col4';
				$lastcol  = 0;

				if ($columns == 4)
				{
					$lastcol = 1;
				}
				if ($params->get('row1col4') < 1)
				{
					$params->set('row1col4', 100);
				}
				$listing .= $this->getHeadercell($params->get('row1col4'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);
			}
			$listing .= '</tr>';

			$lastrow = 0;

			if ($rows == 2)
			{
				$lastrow = 1;
			}
			// This begins the row of the display data
			$listing .= '<tr';

			if ($lastrow == 1)
			{
				$listing .= ' class="lastrow"';
			}
			$listing .= '>';
			$colspan  = $params->get('r2c1span');
			$rowspan  = $params->get('rowspanr2c1');
			$rowcolid = 'row2col1';
			$lastcol  = 0;

			if ($columns == 1 || $colspan > 3)
			{
				$lastcol = 1;
			}
			if ($params->get('row2col1') < 1)
			{
				$params->set('row2col1', 100);
			}
			$listing .= $this->getHeadercell($params->get('row2col1'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);

			if ($columns > 1 && $params->get('r2c1span') < 2)
			{
				$colspan  = $params->get('r2c2span');
				$rowspan  = $params->get('rowspanr2c2');
				$rowcolid = 'row2col2';
				$lastcol  = 0;

				if ($columns == 2 || $colspan > 2)
				{
					$lastcol = 1;
				}
				if ($params->get('row1col2') < 1)
				{
					$params->set('row1col2', 100);
				}
				$listing .= $this->getHeadercell($params->get('row2col2'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);
			}
			if ($columns > 2 && ($params->get('r2c1span') < 3 && $params->get('r2c2span') < 2))
			{
				$colspan  = $params->get('r2c3span');
				$rowspan  = $params->get('rowspanr2c3');
				$rowcolid = 'row2col3';
				$lastcol  = 0;

				if ($columns == 3 || $colspan > 1)
				{
					$lastcol = 1;
				}
				if ($params->get('row2col3') < 1)
				{
					$params->set('row2col3', 100);
				}
				$listing .= $this->getHeadercell($params->get('row2col3'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);
			}
			if ($columns > 3 && ($params->get('r2c1span') < 4 && $params->get('r2c2span') < 3 && $params->get('r2c3span') < 2))
			{
				$colspan  = $params->get('r2c4span');
				$rowspan  = $params->get('rowspanr2c4');
				$rowcolid = 'row2col4';
				$lastcol  = 0;

				if ($columns == 4)
				{
					$lastcol = 1;
				}
				if ($params->get('row2col4') < 1)
				{
					$params->set('row2col4', 100);
				}
				$listing .= $this->getHeadercell($params->get('row2col4'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);
			}
			$listing .= '</tr>';

			// Test to see if Lastrow is not (int) 0
			if ($lastrow == 0)
			{
				$lastrow = 0;
			}

			// This begins the row of the display data
			$listing .= '<tr';

			if ($rows == 3)
			{
				$listing .= ' class= "lastrow"';
			}

			$listing .= '>';
			$colspan  = $params->get('r3c1span');
			$rowspan  = $params->get('rowspanr3c1');
			$rowcolid = 'row3col1';
			$lastcol  = 0;

			if ($columns == 1 || $colspan > 3)
			{
				$lastcol = 1;
			}
			if ($params->get('row3col1') < 1)
			{
				$params->set('row3col1', 100);
			}
			$listing .= $this->getHeadercell($params->get('row3col1'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);

			if ($columns > 1 && $params->get('r3c1span') < 2)
			{
				$colspan  = $params->get('r3c2span');
				$rowspan  = $params->get('rowspanr3c2');
				$rowcolid = 'row3col2';
				$lastcol  = 0;

				if ($columns == 2 || $colspan > 2)
				{
					$lastcol = 1;
				}
				if ($params->get('row3col3') < 1)
				{
					$params->set('row3col2', 100);
				}
				$listing .= $this->getHeadercell($params->get('row3col2'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);
			}
			if ($columns > 2 && ($params->get('r3c1span') < 3 && $params->get('r3c2span') < 2))
			{
				$colspan  = $params->get('r3c3span');
				$rowspan  = $params->get('rowspanr3c3');
				$rowcolid = 'row3col3';
				$lastcol  = 0;

				if ($columns == 3 || $colspan > 1)
				{
					$lastcol = 1;
				}
				if ($params->get('row3col3') < 1)
				{
					$params->set('row3col3', 100);
				}
				$listing .= $this->getHeadercell($params->get('row3col3'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);
			}
			if ($columns > 3 && ($params->get('r3c1span') < 4 && $params->get('r3c2span') < 3 && $params->get('r3c3span') < 2))
			{
				$colspan  = $params->get('r3c4span');
				$rowspan  = $params->get('rowspanr3c4');
				$rowcolid = 'row3col4';
				$lastcol  = 0;

				if ($columns == 4)
				{
					$lastcol = 1;
				}
				if ($params->get('row3col4') < 1)
				{
					$params->set('row3col4', 100);
				}
				$listing .= $this->getHeadercell($params->get('row3col4'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);
			}
			$listing .= '</tr>';

			// This begins the row of the display data
			$listing .= '<tr';
			$lastrow = 0;

			if ($rows == 4)
			{
				$listing .= ' class="lastrow"';
			}

			$listing .= '>';
			$colspan  = $params->get('r4c1span');
			$rowspan  = $params->get('rowspanr4c1');
			$rowcolid = 'row4col1';
			$lastcol  = 0;

			if ($columns == 1 || $colspan > 3)
			{
				$lastcol = 1;
			}
			if ($params->get('row4col1') < 1)
			{
				$params->set('row4col1', 100);
			}
			$listing .= $this->getHeadercell($params->get('row4col1'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);

			if ($columns > 1 && $params->get('r4c1span') < 2)
			{
				$colspan  = $params->get('r4c2span');
				$rowspan  = $params->get('rowspanr4c2');
				$rowcolid = 'row4col2';
				$lastcol  = 0;

				if ($columns == 2 || $colspan > 2)
				{
					$lastcol = 1;
				}
				if ($params->get('row4col2') < 1)
				{
					$params->set('row4col2', 100);
				}
				$listing .= $this->getHeadercell($params->get('row4col2'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);
			}
			if ($columns > 2 && ($params->get('r4c1span') < 3 && $params->get('r4c2span') < 2))
			{
				$colspan  = $params->get('r4c3span');
				$rowspan  = $params->get('rowspanr4c3');
				$rowcolid = 'row4col3';
				$lastcol  = 0;

				if ($columns == 3 || $colspan > 1)
				{
					$lastcol = 1;
				}
				if ($params->get('row4col3') < 1)
				{
					$params->set('row4col3', 100);
				}
				$listing .= $this->getHeadercell($params->get('row4col3'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);
			}
			if ($columns > 3 && ($params->get('r4c1span') < 4 && $params->get('r4c2span') < 3 && $params->get('r4c3span') < 2))
			{
				$colspan  = $params->get('r4c4span');
				$rowspan  = $params->get('rowspanr4c4');
				$rowcolid = 'row4col4';
				$lastcol  = 0;

				if ($columns == 4)
				{
					$lastcol = 1;
				}
				if ($params->get('row4col4') < 1)
				{
					$params->set('row4col4', 100);
				}
				$listing .= $this->getHeadercell($params->get('row4col4'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);
			}
			$listing .= '</tr>';
		}

		// End of if else for $nh
		return $listing;
	}

	/**
	 * Get Header Cell
	 *
	 * @param   int       $rowid         Table Row ID
	 * @param   object    $row           Item info
	 * @param   JRegistry $params        Item Params
	 * @param   int       $lastcol       Last Column
	 * @param   int       $colspan       Column Span
	 * @param   int       $rowspan       Row Span
	 * @param   int       $rowcolid      RowCol Id
	 * @param   string    $nh            ?
	 * @param   JRegistry $admin_params  Admin Params
	 * @param   int       $template      Template ID
	 *
	 * @return string
	 */
	private function getHeadercell($rowid, $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template)
	{
		$headercell = '<th ';
		$elementid  = new stdClass;

		if ($rowid == '20')
		{
			$elementid->headertext = JText::_('JBS_CMN_MEDIA');
			$elementid->id         = 'jbsmedia';
		}
		else
		{
			$elementid = JBSMElements::getElementid($rowid, $row, $params, $admin_params, $template);
		}

		if (!isset($elementid->id))
		{
			$headercell .= 'customhead';
		}
		else
		{
			$headercell .= 'class="' . $rowcolid . ' ' . $elementid->id . 'head ';
		}

		if ($lastcol == 1)
		{
			$headercell .= ' lastcol';
		}
		$headercell .= '"';

		if ($colspan > 1)
		{
			$headercell .= 'colspan="' . $colspan . '" ';
		}
		if ($rowspan > 1)
		{
			$headercell .= 'rowspan="' . $rowspan . '"';
		}
		$headercell .= '>';

		if (isset($elementid->headertext))
		{
			$headercell .= $elementid->headertext;
		}
		else
		{
			$headercell .= $this->getCustomhead($rowcolid, $params);
		}
		$headercell .= '</th>';

		return $headercell;
	}

	/**
	 * Get CustomHead
	 *
	 * @param   int       $rowcolid  Row ID Column
	 * @param   JRegistry $params    Item Params
	 *
	 * @return string
	 */
	private function getCustomhead($rowcolid, $params)
	{
		$row        = substr($rowcolid, 3, 1);
		$col        = substr($rowcolid, 7, 1);
		$customhead = $params->get('r' . $row . 'c' . $col . 'customlabel');

		return $customhead;
	}

}
