<?php

/**
 * @author Joomla Bible Study
 * @copyright 2010
 * @desc Provides paths to image folders and correct path to image
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.html.parameter');
class jbsImages 
{
	
	function adminSettings()
	{
		$database	= & JFactory::getDBO();
		$database->setQuery ("SELECT params FROM #__bsms_admin WHERE id = 1");
		$database->query();
		$compat = $database->loadObject();
		$admin_params = new JParameter($compat->params);
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
		$admin_params = new JParameter($admin->params);
		if ($admin->main == '- JBS_CMN_DEFAULT_IMAGE -' || ($admin->main && !$admin_params->get('media_imagefolder')))   // 2010-11-12 santon: need to be changed
			{
				$path = 'components/com_biblestudy/images'; //dump ($path, 'path: ');
			}
		if ($admin->main && $admin_params->get('media_imagefolder'))
			{
				$path = 'images/'. $admin_params->get('media_imagefolder');
			}
		$image = ($admin->main == '- JBS_CMN_DEFAULT_IMAGE -' ? 'openbible.png' : $admin->main );  // 2010-11-12 santon: need to be changed
		$i_path = $path .'/'. $image; //dump ($i_path, 'i_path: ');
		$mainimage = $this->getImagePath($i_path);	//dump ($mainimage, 'mainimage: ');
		return $mainimage;	
	}	

	function getMediaImageFolder()
	{
		$admin_params = $this->adminSettings();
			if ($admin_params->get('media_imagefolder') == '- Use Default -' || !$admin_params->get('media_imagefolder'))
		{
			$mediaimagefolder = 'components/com_biblestudy/images';
		}
		else
		{
			$mediaimagefolder = 'images/'. $admin_params->get('media_imagefolder');
		}
//		$mediaimagefolder = ($admin_params->get('media_imagefolder') ? 'images' .DS. $admin_params->get('media_imagefolder') : 'components/com_biblestudy/images' );
		return $mediaimagefolder;
	}
	
	function getSeriesImageFolder()
	{
		$admin_params = $this->adminSettings();
		if ($admin_params->get('series_imagefolder') == '- Use Default -' || !$admin_params->get('series_imagefolder'))
		{
			$seriesimagefolder = 'images/stories';
		}
		else
		{
			$seriesimagefolder = 'images/'. $admin_params->get('series_imagefolder');
		}
	//	$seriesimagefolder = ($admin_params->get('series_imagefolder') ? 'images' .DS. $admin_params->get('series_imagefolder') : 'images/stories' );
		return $seriesimagefolder;
	}
	
	function getStudiesImageFolder()
	{
		$admin_params = $this->adminSettings();
			if ($admin_params->get('study_images') == '- Use Default -' || !$admin_params->get('study_images'))
		{
			$studiesimagefolder = 'images/stories';
		}
		else
		{
			$studiesimagefolder = 'images/'. $admin_params->get('study_images');
		}
	//	$studiesimagefolder = ($admin_params->get('study_images') ? 'images/'.$admin_params->get('study_images') : 'images/'.'stories');
		//$studiesimagefolder = ($admin_params->get('study_images') ? 'images/stories' : 'images' .DS. $admin_params->get('study_images'));
		return $studiesimagefolder;
	}
	
	function getTeacherImageFolder()
	{
		$admin_params = $this->adminSettings();
			if ($admin_params->get('teachers_imagefolder') == '- Use Default -' || !$admin_params->get('teachers_imagefolder'))
		{
			$teacherimagefolder = 'images/stories';
		}
		else
		{
			$teacherimagefolder = 'images/'. $admin_params->get('teachers_imagefolder');
		}
//		$teacherimagefolder = ($admin_params->get('teacher_imagefolder') ? 'images' .DS. $admin_params->get('teacher_imagefolder') : 'images/stories');
		return $teacherimagefolder;
	}
	
	function getStudyThumbnail($image='openbible.png')
	{
		$imagepath = array();
		$folder = $this->getStudiesImageFolder();
		$path = $folder .'/'. $image;
		$imagepath = $this->getImagePath($path);
		return $imagepath;
	}
	
	function getSeriesThumbnail($image='openbible.png')
	{
		$imagepath = array();
		$folder = $this->getSeriesImageFolder();
		$path = $folder .'/'. $image;
		$imagepath = $this->getImagePath($path); //dump ($imagepath, 'imagepath: ');
		return $imagepath;
	}
	
	function getTeacherThumbnail($image1=NULL, $image2=NULL)
	{
		$imagepath = array();
		//$image1 is teacher->thumbnail, $image2 is teacher->thumb
		if ($image1 == '- JBS_CMN_NO_IMAGE - ' || !$image1)     // 2010-11-12 santon: need to be changed
		{
			$path = $image2;
		}
		else
		{
			$folder = $this->getTeacherImageFolder();
			$path = $folder .'/'. $image1;
		}
		
		$imagepath = $this->getImagePath($path); //dump ($imagepath, 'imagepath: ');
		return $imagepath;
	}
	
	function getTeacherImage($image1=null, $image2=null)
	{
		if (!$image1)
		{
			$path = $image2;
		}
		else
		{
			$imagepath = array();
			$folder = $this->getTeacherImageFolder();
			$path = $folder .'/'. $image1;
		}
		$imagepath = $this->getImagePath($path); //dump ($imagepath, 'imagepath: ');
		return $imagepath;
	}
	
	function getMediaImage($media1=NULL, $media2=NULL) //$media1 is the new, $media2 is the old full path
	{
		$imagepath = array();
		if ($media1)
		{
			$folder = $this->getMediaImageFolder();
			$path = $folder .'/'. $media1;

		}
		else
		{
			$path = $media2;
		}
		$imagepath = $this->getImagePath($path); //dump ($imagepath, 'imagepath: ');
		return $imagepath;
	}
	
	function getShowHide($image)
	{
		if ($image == '- JBS_CMN_DEFAULT_IMAGE -' || !$image)     // 2010-11-12 santon: need to be changed
		{
			$image = 'showhide.gif'; $folder = 'components/com_biblestudy/images';
		}
		else
		{
			$folder = $this->getMediaImageFolder();
		}
		
		$path = $folder .'/'. $image;
		$imagepath = $this->getImagePath($path); //dump ($imagepath, 'imagepath: ');
		return $imagepath;
	}
	
	
} // End of class
	
?>