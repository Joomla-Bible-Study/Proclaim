<?php
defined('_JEXEC') or die();

function getHeader($params)
{

	$headercheck = array( array(  'position' => $params->get('position1')),
	  array( 'position' => $params->get('position2')),
	  array( 'position' => $params->get('position3')),
	  array( 'position' => $params->get('position4')),
	  array( 'position' => $params->get('position5')),
	  array( 'position' => $params->get('position6')),
	  array( 'position' => $params->get('position7')),
	  array( 'position' => $params->get('position8')),
	  array( 'position' => $params->get('position9')),
	  array( 'position' => $params->get('position10')),
	  array( 'position' => $params->get('position11')),
	  array( 'position' => $params->get('position12')),
	  array( 'position' => $params->get('position13')),
	  array( 'position' => $params->get('position14')),
	  array( 'position' => $params->get('position15')),
	  array( 'position' => $params->get('position16')),
	  array( 'position' => $params->get('position17')),
	  array( 'position' => $params->get('position18'))
	  ); //print_r($headercheck);
	
	  //Beginning of header rows
	  $isheader1 = 0;
	  $isheader2 = 0;
	  $isheader3 = 0;
	  $isheader4 = 0;
	  $header = '';
	  if ($params->get('use_headers') >0) {
	   //$header_count = count($headercheck);
	   //dump ($header_count, 'Header_count');
	   $rows1=count($headercheck);
	   for($j=0;$j<$rows1;$j++)
	   {
		if ($headercheck[$j]['position']==1){ $isheader1 = 1;}
		if ($headercheck[$j]['position']==2){ $isheader2 = 1;}
		if ($headercheck[$j]['position']==3){ $isheader3 = 1;}
		if ($headercheck[$j]['position']==4){ $isheader4 = 1;}
	   }
	   if ($isheader1 == 1)
	   {$header = '<th align="'.$params->get('header_align').'" bgcolor="'.$params->get('header_color').'" width="'.$params->get('header1_width').'"><span '.$params->get('header_span').'>'.$params->get('header1').'</span></th>';}
	   if ($isheader2 == 1)
	   {$header .= '<th align="'.$params->get('header_align').'" bgcolor="'.$params->get('header_color').'" width="'.$params->get('header2_width').'"><span '.$params->get('header_span').'>'.$params->get('header2').'</span></th>';}
	   if ($isheader3 == 1)
	   {$header .= '<th align="'.$params->get('header_align').'" bgcolor="'.$params->get('header_color').'" width="'.$params->get('header3_width').'"><span '.$params->get('header_span').'>'.$params->get('header3').'</span></th>';}
	   if ($isheader4 == 1)
	   {$header .= '<th align="'.$params->get('header_align').'" bgcolor="'.$params->get('header_color').'" width="'.$params->get('header4_width').'"><span '.$params->get('header_span').'>'.$params->get('header4').'</span></th>';}
	   
	  } // end of if use headers
	return $header;
}