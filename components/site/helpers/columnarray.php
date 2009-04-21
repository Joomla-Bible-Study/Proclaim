<?php
defined('_JEXEC') or die();

function getColumnarray($a, $row, $columnnumber, $params) {
	//dump ($a, '$a: ');
	if (!$params->get('menuid')) { $menuid = 0;}
	else {$menuid = $params->get('menuid');}
	
   $rows1=count($a);
   for($j=0;$j<$rows1;$j++)
   {
    if ($a[$j]['position']!= $columnnumber)
    {
     unset($a[$j]);
    }
   }
	$rows2 = count($a);
	
	if ($rows2 < 1) {$column = null; return $column; }
	$j2 = 0;
	//dump ($rows2, 'rows2: ');
   $columnelements = array_values($a);
   //$c = $columnelements;
   if (isset($columnelements[0]['position'])) { //This tests to see if there is anything in column
//First we create the tables, rows, and column for this display
	//$column = '<div id="column'.$columnnumber.'">';
     //Now let's assign some elements and go through each of them.
     foreach ($columnelements as $c) {
      $element = $c['element'];
      $position = $c['position'];
      $isbullet=$c['isbullet'];
      $span=$c['span'];
      $islink=$c['islink'];
	  //dump ($element, 'element: ');
//dump ($islink, 'islink: ');
// Now we run an increment so that we only but a <br> at the end of every line except the last line
       //Now we produce each element in turn with its parameters
	   //dump ($columnnumber, 'columnnumber: ');
	   
      if ($isbullet == 1) {
       $column .= '<ul><li>'; }
       switch ($islink) {
        case 1 :
         $link = JRoute::_('index.php?option=com_biblestudy&view=studydetails' . '&id=' . $row->id );
		 if ($menuid > 0) {$link .= '&Itemid='.$menuid;}
         $column .= '<a href="'.$link.'">';
         break;
        case 2 :
         $link = JRoute::_($filepath);
         $column .= '<a href="'.$link.'">';
         break;
        case 3 :
         $link = JRoute::_('index.php?option=com_biblestudy&view=teacherdisplay' . '&id=' . $row->tid );
		 if ($menuid > 0) {$link .= '&Itemid='.$menuid;}
         $column .= '<a href="'.$link.'">';
         break;
       }
       $column .= $element;
       if ($islink > 0) { $column .= '</a>'; }
       if ($isbullet == 1) { $column .= '</li></ul>';} 
	   $j2++;
	   if ($j2<$rows2) {$column .= '<br>';}
	 } // end of foreach $columnelements
	 
 } //end of test to see if anything is in the column
 //dump ($column, '$column: ');
 if (!$column) {$column = '<br>';}
	return $column;
 }
