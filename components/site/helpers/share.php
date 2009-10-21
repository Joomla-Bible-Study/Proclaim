<?php

/**
 * @author Joomla Bible Study
 * @copyright 2009
 */
defined('_JEXEC') or die();
//Share Helper file
function biblestudyShare()
{
	$login = 'joomlabiblestudy';
	$appkey = 'R_dc86635ad2d1e883cab8fad316ca12f6';
	$url = 'http://www.joomlabiblestudy.org';
	$short = make_bitly_url($url, $login, $appkey, 'json', '2.0.1');
	$sharetitle = 'Share This';
	$image = 'http://www.joomlaoregon.org/biblestudy/components/com_biblestudy/images/facebook.png';
	$height = '44px';
	$width = '44px';
	$mainlink = 'http://www.facebook.com/sharer.php?';
	$item1prefix = 'u=';
	$item1 = '';
	$item2prefix = '&t=';
	$item2 = 'JoomlaBibleStudy';
	$shareit = '<table class="bsmsshare"><tr class="bsmssharetitlerow"><td id="bsmssharetitle">'.$sharetitle.'</td></tr><tr class="bsmsshareiconrow"><td id="bsmsshareicons"><a href="'.$mainlink.$item1prefix.$short.$item2prefix.$item2.'" target="_blank"><img src="'.$image.'" alt="Share" width="'.$width.'" height="'.$height.'" border="0"></a></td></tr></table>';
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