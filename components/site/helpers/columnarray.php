<?php
defined('_JEXEC') or die();

function getColumnarray($a, $row, $columnnumber) {
	
   $rows1=count($a);
   for($j=0;$j<$rows1;$j++)
   {
    if ($a[$j]['position']!= $columnnumber)
    {
     unset($a[$j]);
    }
   }

   $columnelements = array_values($a);
   //$c = $columnelements;
   if (isset($columnelements[0]['position'])) { //This tests to see if there is anything in column
    
     //Now let's assign some elements and go through each of them.
     foreach ($columnelements as $c) {
      $element = $c['element'];
      $position = $c['position'];
      $isbullet=$c['isbullet'];
      $span=$c['span'];
      $islink=$c['islink'];
	 

      //Now we produce each element in turn with its parameters
      $column .= '<span '.$span.'>';
      if ($isbullet == 1) {
       $column .= '<ul><li>'; }
       switch ($islink) {
        case 1 :
         $link1 = JRoute::_('index.php?option=com_biblestudy&view=studydetails' . '&id=' . $row->id );
         $column .= '<a href="'.$link1.'">';
         break;
        case 2 :
         $link1 = JRoute::_($filepath);
         $column .= '<a href="'.$link.'">';
         break;
        case 3 :
         $link1 = JRoute::_('index.php?option=com_biblestudy&view=teacherdisplay' . '&id=' . $row->tid );
         $column .= '<a href="'.$link.'">';
         break;
       }
       $column .= $element;
       if ($islink > 0) { $column .= '</a>'; }
       if ($isbullet == 1) { $column .= '</li></ul>';} else {$column .= '<br>';}
       $column .= '</span>';
	 } // end of foreach
 } //end of test to see if anything is in the column
	return $column;
 }
//Need to fix ending <br> problem - perhaps with elseif to test to see if it is the last row in the array - but which array?