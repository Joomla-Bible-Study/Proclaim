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
	  $w1 = $params->get('widthcol1');
	  $w2 = $w1 + $params->get('widthcol2');
	  $w3 = $w2 + $params->get('widthcol3');

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
	   		{$header .= '<div class="header1">'.$params->get('header1').'</div>';}
	   if ($isheader2 == 1)
	   		{$header .= '<div class="header2">'.$params->get('header2').'</div>';}
	   if ($isheader3 == 1)
	   		{$header .= '<div class="header3">'.$params->get('header3').'</div>';}
	   if ($isheader4 == 1)
	   		{$header .= '<div class="header4">'.$params->get('header4').'</div>';}
	   if ($params->get('show_full_text') > 0) 
			{$header .= '<div class="header5">'.$params->get('header5').'</div>';}
	   if ($params->get('show_store') > 0)
	   		{$header .= '<div class="header6">'.$params->get('store_name').'</div>';}
	   if ($params->get('show_media') > 0) 
	   		{$header .= '<div class="header7">'.$params->get('header6').'</div>';}
		
	  
	   
	  } // end of if use headers
	 return $header;
}