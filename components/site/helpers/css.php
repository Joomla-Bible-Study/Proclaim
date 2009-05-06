<?php
defined('_JEXEC') or die();

function getCss($params)
{
	
	switch ($params->get('type'))
	{
	 case 'studieslist':
		
		$styles = '
			.headercontainer {float: left; width:100%; border-bottom: thin dotted #707070; background-color: '.$params->get('header_color').'; padding: 3px 3px 3px 3px; font-weight: bold; color: '.$params->get('header_font_color').';}
			.header1 {float: left; width:'.$params->get('widthcol1').'; text-align:'.$params->get('header_align').'; padding-right: 2px;}
			.header2 {float: left; width:'.$params->get('widthcol2').'; text-align:'.$params->get('header_align').'; padding-left: 2px; padding-right: 2px;}
			.header3 {float: left; width:'.$params->get('widthcol3').'; text-align:'.$params->get('header_align').'; padding-left: 2px; padding-right: 2px;}
			.header4 {float: left; width:'.$params->get('widthcol4').'; text-align:'.$params->get('header_align').'; padding-left: 2px; padding-right: 2px;}
			.header5 {float: left; text-align:'.$params->get('header_align').'; width: '.$params->get('details_width').';  padding-left: 2px; padding-right: 2px;}
			.header6 {float: left; text-align:'.$params->get('header_align').'; width: '.$params->get('storewidth').';padding-left: 2px; padding-right: 2px;}
			.header7 {float: left; text-align:'.$params->get('header_align').'; width: '.$params->get('media_width').'; padding-left: 2px}
			.column1 {float: left; width:'.$params->get('widthcol1').';}
			.column2 {float: left; width:'.$params->get('widthcol2').';}
			.column3 {float: left; width:'.$params->get('widthcol3').';}	
			.column4 {float: left; width:'.$params->get('widthcol4').';}
			.listingpagecontainer {position:relative; width: '.$params->get('page_width').';}
			.listingdropdownmenu {float: left; padding-bottom: 5px; padding-top: 8px; border-bottom: medium solid #707070; width: '.$params->get('page_width').';}
			.bslistingcontainer {float: left; width: 100%;}
			
		
			';
			if ($params->get('line_break') > 0) 
			{ 
				$styles .= '.bslistingcontainer {float: left; width: 100%; padding: 3px 3px 18px 3px; border-bottom: thin solid #707070; width:'.$params->get('page_width').';}';
			}
			else {
				$styles .= '.bslistingcontainer {float: left; width: 100%; padding: 3px 3px 3px 3px; border-bottom: thin solid #707070; width:'.$params->get('page_width').';}';
			}
		break;
	 case 'studydetails':
	 
			 $styles = '
			
			.detailsheadercontainer {float: left; width:100%; border-bottom: thin dotted #707070; background-color: '.$params->get('header_color').'; padding-top: 5px; font-weight: bold; color: '.$params->get('header_font_color').';}
			.header1 {float: left; width:'.$params->get('widthcol1').'; text-align:'.$params->get('header_align').';}
			.header2 {float: left; width:'.$params->get('widthcol2').'; text-align:'.$params->get('header_align').';}
			.header3 {float: left; width:'.$params->get('widthcol3').'; text-align:'.$params->get('header_align').';}
			.header4 {float: left; width:'.$params->get('widthcol4').'; text-align:'.$params->get('header_align').';}
			.header5 {float: left; text-align:'.$params->get('header_align').'; width: '.$params->get('details_width').'; padding-right: 2px}
			.header6 {float: left; text-align:'.$params->get('header_align').'; width: '.$params->get('storewidth').';padding-left: 2px; padding-right: 2px;}
			.header7 {float: left; text-align:'.$params->get('header_align').'; width: '.$params->get('media_width').'; padding-left: 2px}
			.column1 {float: left; width:'.$params->get('widthcol1').';}
			.column2 {float: left; width:'.$params->get('widthcol2').';}
			.column3 {float: left; width:'.$params->get('widthcol3').';}	
			.column4 {float: left; width:'.$params->get('widthcol4').';}
			.detailsstore {float: left; padding-left: 2px; padding-right: 2px; width: '.$params->get('storewidth').';}
			.detailsfooter {clear: both; float: left; border-top: thin solid #707070; padding-top: 5px; position: relative; top: 5px; width: 100%;}
			.detailslistingcontainer {float: left; width: 100%; padding-top: 5px; padding-bottom: 5px; border-bottom: thin dotted #707070;}
		
		#studyheader {float: left; font-weight: bold; width: 100%;}
		
			';
			if ($params->get('line_break') > 0) 
			{ 
				$styles .= '.detailslistingcontainer {float: left; width: 100%; padding: 3px 3px 18px 3px; border-bottom: thin solid #707070; width:'.$params->get('page_width').';}';
			}
			else {
				$styles .= '.detailslistingcontainer {float: left; width: 100%; padding: 3px 3px 3px 3px; border-bottom: thin solid #707070; width:'.$params->get('page_width').';}';
			}
	 	break;
		
	case 'module':
	
			$styles = '
			.headercontainer {float: left; width:100%; border-bottom: thin dotted #707070; background-color: '.$params->get('header_color').'; padding: 3px 3px 3px 3px; font-weight: bold; color: '.$params->get('header_font_color').';}
			.header1 {float: left; width:'.$params->get('widthcol1').'; text-align:'.$params->get('header_align').'; padding-right: 2px;}
			.header2 {float: left; width:'.$params->get('widthcol2').'; text-align:'.$params->get('header_align').'; padding-left: 2px; padding-right: 2px;}
			.header3 {float: left; width:'.$params->get('widthcol3').'; text-align:'.$params->get('header_align').'; padding-left: 2px; padding-right: 2px;}
			.header4 {float: left; width:'.$params->get('widthcol4').'; text-align:'.$params->get('header_align').'; padding-left: 2px; padding-right: 2px;}
			.header5 {float: left; text-align:'.$params->get('header_align').'; width: '.$params->get('details_width').';  padding-left: 2px; padding-right: 2px;}
			.header6 {float: left; text-align:'.$params->get('header_align').'; width: '.$params->get('storewidth').';padding-left: 2px; padding-right: 2px;}
			.header7 {float: left; text-align:'.$params->get('header_align').'; width: '.$params->get('media_width').'; padding-left: 2px}
			.column1 {float: left; width:'.$params->get('widthcol1').';}
			.column2 {float: left; width:'.$params->get('widthcol2').';}
			.column3 {float: left; width:'.$params->get('widthcol3').';}	
			.column4 {float: left; width:'.$params->get('widthcol4').';}
			.listingpagecontainer {position:relative; width: '.$params->get('page_width').';}
			.listingdropdownmenu {float: left; padding-bottom: 5px; padding-top: 8px; border-bottom: medium solid #707070; width: '.$params->get('page_width').';}
			.bslistingcontainer {float: left; width: 100%;}
			
		
			';
			if ($params->get('line_break') > 0) 
			{ 
				$styles .= '.bslistingcontainer {float: left; width: 100%; padding: 3px 3px 18px 3px; border-bottom: thin solid #707070; width:'.$params->get('page_width').';}';
			}
			else {
				$styles .= '.bslistingcontainer {float: left; width: 100%; padding: 3px 3px 3px 3px; border-bottom: thin solid #707070; width:'.$params->get('page_width').';}';
			}
				break;
			}

	return $styles;
}