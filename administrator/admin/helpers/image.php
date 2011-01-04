<?php defined( '_JEXEC' ) or die( 'Restricted access' );

function getImage($path)
{
	error_reporting(0);
	jimport('joomla.filesystem.folder');
	jimport('joomla.filesystem.file');
	//dump ($path, 'ppath: ');
	//dump ($file, 'file: ');
	$tmp = new JObject();
	//$tmp->name = $file;
	$tmp->path = $path;
	//dump ($path, 'path: ');

	$tmp->size = filesize($tmp->path);	
	$ext = strtolower(JFile::getExt($path));
	switch ($ext)
	{
		// Image
		case 'jpg':
		case 'png':
		case 'gif':
		case 'xcf':
		case 'odg':
		case 'bmp':
		case 'jpeg':
			$info = @getimagesize($tmp->path);
			$tmp->width		= @$info[0];
			$tmp->height	= @$info[1];
			$tmp->type		= @$info[2];
			$tmp->mime		= @$info['mime'];
			if (!$tmp->width) {$tmp->width=0;}
			if (!$tmp->height) {$tmp->height=0;}
	}
return $tmp;
}
