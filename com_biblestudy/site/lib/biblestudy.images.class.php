<?php

/**
 * @author Joomla Bible Study
 * @copyright 2010
 * @desc Provides paths to image folders and correct path to image
 */

defined('_JEXEC') or die('Restricted access');
//require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
jimport('joomla.html.parameter');
class jbsImages 
{
	
	function adminSettings()
	{
		$database	= & JFactory::getDBO();
		$database->setQuery ("SELECT params FROM #__bsms_admin WHERE id = 1");
		$database->query();
		$compat = $database->loadObject();
	//	$admin_params = new JParameter($compat->params);
        
          // Convert parameter fields to objects.
				$registry = new JRegistry;
				$registry->loadJSON($compat->params);
                $admin_params = $registry;
                
		return $admin_params;
	}		


	
	function getImagePath($path)
	{
		error_reporting(0);
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		$tmp = new JObject();
		$tmp->path = $path;
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
		} //dump ($tmp, 'image: ');
	return $tmp;
	}

	function mainStudyImage()
	{
		$mainimage = array();
		$path = null;
		$image = null;
		$database	= & JFactory::getDBO();
		$database->setQuery ("SELECT * FROM #__bsms_admin WHERE id = 1");
		$admin = $database->loadObject(); //print_r($admin);
		//	$admin_params = new JParameter($compat->params);
        
          // Convert parameter fields to objects.
				$registry = new JRegistry;
				$registry->loadJSON($admin->params);
                $admin_params = $registry;
                
		if (!$admin_params->get('default_main_image') )   
			{
				$path = 'components/com_biblestudy/images/openbible.png';
			}
		else
            {
                $path = $admin_params->get('default_main_image');
            }
		
		$mainimage = $this->getImagePath($path);	//dump ($mainimage, 'mainimage: ');
		return $mainimage;	
	}	

	function getMediaImageFolder()
	{

			$mediaimagefolder = 'components/com_biblestudy/images';


		return $mediaimagefolder;
	}
	
	function getSeriesImageFolder()
	{

			$seriesimagefolder = 'images/stories';

		return $seriesimagefolder;
	}
	
	function getStudiesImageFolder()
	{

			$studiesimagefolder = 'images/stories';

		return $studiesimagefolder;
	}
	
	function getTeacherImageFolder()
	{

			$teacherimagefolder = 'images/stories';

		return $teacherimagefolder;
	}
	
	function getStudyThumbnail($image='openbible.png')
	{
		$imagepath = array();
		$folder = $this->getStudiesImageFolder();
		$path = $folder .'/'. $image;
        if (substr_count($image,'/')) {$path = $image;}
		$imagepath = $this->getImagePath($path);
		return $imagepath;
	}
	
	function getSeriesThumbnail($image='openbible.png')
	{
		$imagepath = array();
		$folder = $this->getSeriesImageFolder();
		$path = $folder .'/'. $image;
        if (substr_count($image,'/')) {$path = $image;}
		$imagepath = $this->getImagePath($path); //dump ($imagepath, 'imagepath: ');
		return $imagepath;
	}
	
	function getTeacherThumbnail($image1=NULL, $image2=NULL)
	{
		$imagepath = array();
        $folder = $this->getTeacherImageFolder();
		
		if (!$image1 || $image1 == '0' || strncmp($image1, '- ', 2) == 0)
		{
			$path = $image2;
            if (!substr_count($path,'/')) {$path = $folder .'/'.$image2;}
		}
		else
		{
			$path = $folder .'/'. $image1;
            if (substr_count($image1,'/') > 0)
            {$path = $image1;}
		}
		
		$imagepath = $this->getImagePath($path); //dump ($folder, 'folder: '); dump ($path, 'path: ');
		return $imagepath;
	}
	
	function getTeacherImage($image1=null, $image2=null)
	{
		$imagepath = array();
        $folder = $this->getTeacherImageFolder();
		//$image1 is teacher->teacher_image, $image2 is teacher->image
		//compatibility check: test for '0' or '- no image -' or similar
		if (!$image1 || $image1 == '0' || strncmp($image1, '- ', 2) == 0)
		{
			$path = $image2;
            if (!substr_count($path,'/')) {$path = $folder .'/'.$image2;}
		}
		else
		{
			$path = $folder .'/'. $image1;
            if (substr_count($media1,'/') > 0)
            {$path = $image1;}
		}
		$imagepath = $this->getImagePath($path); //dump ($imagepath, 'imagepath: ');
		return $imagepath;
	}
	
	function getMediaImage($media1=NULL, $media2=NULL)
	{
		$imagepath = array();
        $folder = $this->getMediaImageFolder();
		//$media1 is the new, $media2 is the old full path
		//compatibility check: test for '0' or '- no image -' or similar
		if (!$media1 || $media1 == '0' || strncmp($media1, '- ', 2) == 0)
		{
			$path = $media2;
            if (!substr_count($path,'/')) {$path = $folder .'/'.$media2;}
		}
		else
		{
			$path = $folder .'/'. $media1;
            if (substr_count($media1,'/') > 0)
            {$path = $media1;}
//dump ($folder); dump($path);
		}
		$imagepath = $this->getImagePath($path); //dump ($imagepath, 'imagepath: ');
		return $imagepath;
	}
	
	function getShowHide()
	{
		$database	= & JFactory::getDBO();
		$database->setQuery ("SELECT * FROM #__bsms_admin WHERE id = 1");
		$admin = $database->loadObject(); //print_r($admin);
	//	$admin_params = new JParameter($admin->params);
        
          // Convert parameter fields to objects.
				$registry = new JRegistry;
				$registry->loadJSON($admin->params);
                $admin_params = $registry;
                
        if (!$admin_params->get('default_showHide_image') )     
		{
			$path = 'components/com_biblestudy/images/showhide.gif';
		}
		else
		{
			$path = $admin_params->get('default_showHide_image');
		}
		
		$imagepath = $this->getImagePath($path); //dump ($imagepath, 'imagepath: ');
		return $imagepath;
	}
	
	
} // End of class
	
?>