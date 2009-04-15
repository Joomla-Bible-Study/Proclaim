<?php
defined('_JEXEC') or die();

function getColumnarray($a, $row, $columnnumber, $params) {
	//dump ($a, '$a: ');
	if (!$params->get('menuid')) { $menuid = 0;}
	else {$menuid = $params->get('menuid');}
	switch ($colmnnumber) {
	case 1 :
		$colwidth = $params->get('widthcol1');
		break;
	case 2:
		$colwidth = $params->get('widthcol2');
		break;
	case 3 :
		$colwidth = $params->get('widthcol3');
		break;
	case 4 :
	$colwidth = $params->get('widthcol4');
		break;
}
   $rows1=count($a);
   for($j=0;$j<$rows1;$j++)
   {
    if ($a[$j]['position']!= $columnnumber)
    {
     unset($a[$j]);
    }
   }
	$rows2 = count($a);
	$j2 = 0;
	//dump ($rows2, 'rows2: ');
   $columnelements = array_values($a);
   //$c = $columnelements;
   if (isset($columnelements[0]['position'])) { //This tests to see if there is anything in column
//First we create the tables, rows, and column for this display


$column = '<td width="'.$colwidth.'">'
    .'<table border="'.$params->get('border').'"'
    .' cellpadding="'.$params->get('padding').'"'
    .' cellspacing="'.$params->get('spacing').'">'
    .' <tr valign="'.$params->get('colalign').'">'
    .' <td valign="'.$params->get('colalign').'">'; 
   
     //Now let's assign some elements and go through each of them.
     foreach ($columnelements as $c) {
      $element = $c['element'];
      $position = $c['position'];
      $isbullet=$c['isbullet'];
      $span=$c['span'];
      $islink=$c['islink'];
//dump ($islink, 'islink: ');
// Now we run an increment so that we only but a <br> at the end of every line except the last line
       //Now we produce each element in turn with its parameters
      $column .= '<span '.$span.'>';
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
       $column .= '</span>';
	 } // end of foreach $columnelements
	$column .= '</td></tr></table></td>';
 } //end of test to see if anything is in the column
 //dump ($column, '$column: ');
	return $column;
 }
