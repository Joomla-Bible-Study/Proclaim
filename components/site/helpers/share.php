<?php

/**
 * @author Joomla Bible Study
 * @copyright 2009
 */
defined('_JEXEC') or die();
//Share Helper file
function getShare($link, $row, $params, $admin_params)
{
	$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
	include_once($path1.'elements.php');
	$sharetitle = 'Share This'; //this will come from $admin_params
	//Get the information from the database on what social networking sites to use
	$db = JFactory::getDBO();
	$query = 'SELECT * FROM #__bsms_share where published = 1 ORDER BY name ASC';
	$db->setQuery($query);
	$rows = $db->loadObjectList();
	$sharerows = count($rows);
	if ($sharerows < 1) { $share = null; return $share; }
	
	//Begin to form the table
	$shareit = '<table class="bsmsshare">
	<tr class="bsmssharetitlerow">
	<td id="bsmssharetitle">'.$sharetitle.'</td></tr>
	<tr class="bsmsshareiconrow">';
	foreach ($rows as $sharerow)
	{
		$share_params = new JParameter($sharerow->params);
		//dump ($share_params);
		$image = $share_params->get('shareimage');
		$height = $share_params->get('shareimageh','44px');
		$width = $share_params->get('shareimagew', '44px');
		$totalchars = $share_params->get('totalcharacters');
		$use_bitly = $share_params->get('use_bitly');
		$mainlink = $share_params->get('mainlink');
		$appkey = $share_params->get('api','R_dc86635ad2d1e883cab8fad316ca12f6');
		$login = $share_params->get('username','joomlabiblestudy');
		//dump ($share_params);
		if ($use_bitly == 1)
		{
		$url = make_bitly_url($link, $login, $appkey, 'json', '2.0.1');
		}
		else {$url = $link;}

	
	$element1 = '';
	$element2 = '';
	$element3 = '';
	$element4 = '';
	
	if ($share_params->get('item1'))
	{
		if ($share_params->get('item1') == 200)
		{
			$element1->element = $url;
		}
		else {$element1 = getElementid($share_params->get('item1'), $row, $params, $admin_params, $template=1);}
	}
	if ($share_params->get('item2'))
	{
		if ($share_params->get('item2') == 200)
		{
			$element2->element = $url;
		}
		else {$element2 = getElementid($share_params->get('item2'), $row, $params, $admin_params, $template=1); }
	}
	if ($share_params->get('item3'))
	{
		if ($share_params->get('item3') == 200)
		{
			$element3->element = $url;
		}
		else {$element3 = getElementid($share_params->get('item3'), $row, $params, $admin_params, $template=1);}
	}
	if ($share_params->get('item4'))
	{
		if ($share_params->get('item4') == 200)
		{
			$element4->element = $url;
		}
		else {$element4 = getElementid($share_params->get('item4'), $row, $params, $admin_params, $template=1);}
	}
	
	$sharelink = $element1->element.' '.$share_params->get('item2prefix').$element2->element.' '.$share_params->get('item3prefix').$element3->element.' '.$share_params->get('item4prefix').$element4->element;
	
	if ($share_params->get('totalcharacters'))
	{
	$sharelength = strlen($sharelink); 	
		if ($sharelength > $share_params->get('totalcharacters'))
		{
			$linkstartposition = strpos($sharelink,'http://',0);
			$linkendposition = strpos($sharelink,' ',$linkstartposition);
			$linkextract = substr($sharelink,$linkstartposition,$linkendposition);
			$linklength = strlen($linkextract);
			$sharelink = substr_replace($sharelink,'',$linkstartposition,$linkendposition);
			//$sharelink = substr($sharelink,0,$share_params->get('totalcharacters'));
			$newsharelinklength = $share_params->get('totalcharacters') - $linklength - 1;
			$sharelink = substr($sharelink,0,$newsharelinklength);
			$sharelink = $sharelink.' '.$linkextract;
		}
	}
	//dump($element1);
	$shareit .= '
	
	<td id="bsmsshareicons">
	<a href="'.$mainlink.$share_params->get('item1prefix').$sharelink.'" target="_blank"><img src="'.$image.'" alt="Share" width="'.$width.'" height="'.$height.'" border="0"></a>
	</td>';
	
} //end of foreach
$shareit .=
'</tr>
</table>';

	return $shareit;
}

/* make a URL small */
function make_bitly_url($url,$login,$appkey,$format = 'xml',$version = '2.0.1')
{
	//create the URL
	
	$bitly = 'http://api.bit.ly/shorten?version='.$version.'&longUrl='.urlencode($url).'&login='.$login.'&apiKey='.$appkey.'&format='.$format;
	
	//get the url
	//could also use cURL here
	$response = file_get_contents($bitly);
	
	//parse depending on desired format
	if(strtolower($format) == 'json')
	{
		$json = @json_decode($response,true);
		$short =  $json['results'][$url]['shortUrl'];
	}
	else //xml
	{
		$xml = simplexml_load_string($response);
		$short =  'http://bit.ly/'.$xml->results->nodeKeyVal->hash;
	}
return $short;
}



?>