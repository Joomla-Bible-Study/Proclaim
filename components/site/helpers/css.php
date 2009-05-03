<?php
defined('_JEXEC') or die();

function getCss($params)
{
$view = JRequest::getVar('view');
//dump ($view, 'view: ');
if ($view == 'studieslist') {
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
	#studyheader {float: left; font-weight: bold; width: 100%;}

.editcontainer {float: left; width: 100%; border: thin #FFFFFF;}

.message {float: left; width:100%;}

.studyedit {float: left; width: 100%;}

.podcastlist {float: left: width: 100%;}

.editlink {float: left; width: 10px;}

.pageheadertext {float: left; position: relative; top: 15px; font-size: 32px; font-weight: bold; padding: 5px 10px 3px 0px; line-height: 110%;}

.listingpageheader {float: left; padding: 6px 6px 6px 6px; position: relative; top: 10px;}

.listingteacher {float:left; padding: 3px 0px 3px 0px; background-color: #F2F2F2; outline: #707070 solid thin; margin-left: auto; margin-right: auto;}

.teacher {float:left; font-weight: bold; padding: 0px 3px 0px 3px; text-align: center;}

.listinglistings {float: left; width: 100%;}

.bslistingcontainer {float: left; width: 100%;}

.listingtext {float:left; padding-right: 2px}

.listingstore {float: left; padding-left: 2px; padding-right: 2px; }

.listingmediatable {float:left; padding-left: 2px; }

.mediaimage {float:left; }

.mediasize {float: left; font-size: 8px; position: relative; top: -7px;  }

.listingbottomlisting {float: left; width:100%; padding-top: 3px; }

.listelements {float: left;}

.listingfooter {clear: both; float: left; border-top: thin solid #707070; padding-top: 5px; position: relative; top: 5px; width: 100%;}

	';
	if ($params->get('line_break') > 0) 
	{ 
		$styles .= '.bslistingcontainer {float: left; width: 100%; padding: 3px 3px 18px 3px; border-bottom: thin solid #707070; width:'.$params->get('page_width').';}';
	}
	else {
		$styles .= '.bslistingcontainer {float: left; width: 100%; padding: 3px 3px 3px 3px; border-bottom: thin solid #707070; width:'.$params->get('page_width').';}';
	}
}//end if studielist view

if ($view == 'studydetails') {
//Details css

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

#studyheader {float: left; font-weight: bold; width: 100%;}

.detailspagecontainer {float: left; width: 100%;}

.detailspageheadertext {float: left; width: 100%;  font-size: 20px; font-weight: bold; padding: 5px 10px 3px 0px;}

.bspageheader {float: left; padding: 6px 6px 6px 6px; position: relative; top: 10px;}

.detailsteacher {float:left; padding: 3px 0px 3px 0px; background-color: #F2F2F2; outline: #707070 solid thin; }

.teacher {float:left; font-weight: bold; padding: 0px 3px 0px 3px; text-align: center;}

.bslistings {float: left; width: 100%;}

.detailslistingcontainer {float: left; width: 100%; padding-top: 5px; padding-bottom: 5px; border-bottom: thin dotted #707070;}

.detailstext {float:left; padding-right: 2px}

.detailsmediatable {float:left; padding-left: 2px; z-index:1; }

.mediaimage {float:left; z-index: 2;}

.mediasize {float: left; z-index:3; font-size: 8px; position: relative; top: -7px;  }

.detailsbottomlisting {float: left; width:100%; padding-top: 3px; }

#detailstitlecontainer {float: left; width: 100%; border-bottom: thick solid #707070; padding-bottom: 5px;}

#detailsheadertext {float: left; padding-left: 5px; font-size: 20px; font-weight: bold;}

#detailstitle2 {float: left; font-style: italic; font-size: 15px; font-weight: bold;}

.detailsstudytext {float: left; width: 100%; padding-left: 5px;}

#commentsheader {float: left; padding-top: 5px; padding-bottom: 5px; font-weight: bold; font-size: 18px; width: 100%;}

.commentstable {float: left; padding-left: 5px; padding-top: 5px; padding-bottom: 5px; width: 100%;}

#commentstext {float: left; padding-top: 5px; padding-bottom: 5px; width: 100%; border-bottom: thin dotted #707070;}

.commentssubmittable {float: left; width: 100%;}

#passagecontainer {float: left; width: 100%; padding-top: 5px; padding-bottom: 5px;}

#scripture {float: left;}

.heading {float: left;}

#register {float: left;}

	';
	if ($params->get('line_break') > 0) 
	{ 
		$styles .= '.bslistingcontainer {float: left; width: 100%; padding: 3px 3px 18px 3px; border-bottom: thin solid #707070; width:'.$params->get('page_width').';}';
	}
	else {
		$styles .= '.bslistingcontainer {float: left; width: 100%; padding: 3px 3px 3px 3px; border-bottom: thin solid #707070; width:'.$params->get('page_width').';}';
	}
} // end if details view

	return $styles;
}