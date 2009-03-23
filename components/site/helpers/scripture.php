<?php defined('_JEXEC') or die();

function format_scripture2($id2, $esv, $booknumber, $ch_b, $ch_e, $v_b, $v_e, $show_verses) {
	global $mainframe, $option;
	$params =& $mainframe->getPageParameters();
	$db	= & JFactory::getDBO();
	$query = 'SELECT #__bsms_studies.*, #__bsms_books.bookname, #__bsms_books.id as bid '
			. ' FROM #__bsms_studies'
			. ' LEFT JOIN #__bsms_books ON (#__bsms_studies.booknumber = #__bsms_books.booknumber)'
			. '  WHERE #__bsms_studies.id = '.$id2; 
	$db->setQuery($query);
	$bookresults = $db->loadObject();
	$query = 'SELECT bookname, booknumber FROM #__bsms_books WHERE booknumber = '.$booknumber;
	$db->setQuery($query);
	$booknameresults = $db->loadObject();
	//dump ($show_verses, 'show_verses ');
	if ($booknameresults->bookname) {$book = JText::_($booknameresults->bookname);} else {$book = '';}
	$b1 = ' ';
	$b2 = ':';
	$b2a = ':';
	$b3 = '-';
	$b3a = '-';
	if ($show_verses == 1)
	{
		$scripture = $book.$b1.$ch_b.$b2.$v_b.$b3.$ch_e.$b2a.$v_e;
		if ($ch_e == $ch_b) {
			$ch_e = '';
			$b2a = '';
		}
		if ($v_b == 0){
			$v_b = '';
			$v_e = '';
			$b2a = '';
			$b2 = '';
		}
		if ($v_e == 0) {
			$v_e = '';
			$b2a = '';
		}
		if ($ch_e == 0) {
			$b2a = '';
			$ch_e = '';
			if ($v_e == 0) {
				$b3 = '';
			}
		}
		$scripture = $book.$b1.$ch_b.$b2.$v_b.$b3.$ch_e.$b2a.$v_e;
		

	}
	//else
	if ($show_verses == 0)
	{
		if ($ch_e > $ch_b) {
			$scripture = $book.$b1.$ch_b.$b3.$ch_e;
		}
		else {
			$scripture = $book.$b1.$ch_b;
		}
	}
	if ($esv == 1){
	 $scripture = $book.$b1.$ch_b.$b2.$v_b.$b3.$ch_e.$b2a.$v_e;
		if ($ch_e == $ch_b) {
			$ch_e = '';
			$b2a = '';
		}
		if ($v_b == 0){
			$v_b = '';
			$v_e = '';
			$b2a = '';
			$b2 = '';
		}
		if ($v_e == 0) {
			$v_e = '';
			$b2a = '';
		}
		if ($ch_e == 0) {
			$b2a = '';
			$ch_e = '';
			if ($v_e == 0) {
				$b3 = '';
			}
		}
		$scripture = $book.$b1.$ch_b.$b2.$v_b.$b3.$ch_e.$b2a.$v_e;
		
	}
	
	return $scripture;
}

?>