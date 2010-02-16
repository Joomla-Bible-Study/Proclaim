<?php

/**
 * @author Joomla Bible Study
 * @copyright 2010
 * @desc Provides paths to image folders and correct path to image
 */

defined('_JEXEC') or die('Restricted access');
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
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
		if ($admin->main == '- Default Image -' || ($admin->main && !$admin_params->get('media_imagefolder')))
			{
				$path = 'components/com_biblestudy/images'; //dump ($path, 'path: ');
			}
		if ($admin->main && $admin_params->get('media_imagefolder'))
			{
				$path = 'images' .DS. $admin_params->get('media_imagefolder');
			}
		$image = ($admin->main == '- Default Image -' ? 'openbible.png' : $admin->main );
		$i_path = $path .DS. $image; //dump ($i_path, 'i_path: ');
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
			$mediaimagefolder = 'images' .DS. $admin_params->get('media_imagefolder');
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
			$seriesimagefolder = 'images' .DS. $admin_params->get('series_imagefolder');
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
			$studiesimagefolder = 'images' .DS. $admin_params->get('study_images');
		}
	//	$studiesimagefolder = ($admin_params->get('study_images') ? 'images/'.$admin_params->get('study_images') : 'images/'.'stories');
		//$studiesimagefolder = ($admin_params->get('study_images') ? 'images/stories' : 'images' .DS. $admin_params->get('study_images'));
		return $studiesimagefolder;
	}
	
	function getTeacherImageFolder()
	{
		$admin_params = $this->adminSettings();
			if ($admin_params->get('teacher_imagefolder') == '- Select Image -' || !$admin_params->get('teacher_imagefolder'))
		{
			$teacherimagefolder = 'images/stories';
		}
		else
		{
			$teacherimagefolder = 'images' .DS. $admin_params->get('teacher_imagefolder');
		}
//		$teacherimagefolder = ($admin_params->get('teacher_imagefolder') ? 'images' .DS. $admin_params->get('teacher_imagefolder') : 'images/stories');
		return $teacherimagefolder;
	}
	
	function getStudyThumbnail($image='openbible.png')
	{
		$imagepath = array();
		$folder = $this->getStudiesImageFolder();
		$path = $folder .DS. $image;
		$imagepath = $this->getImagePath($path);
		return $imagepath;
	}
	
	function getSeriesThumbnail($image='openbible.png')
	{
		$imagepath = array();
		$folder = $this->getSeriesImageFolder();
		$path = $folder .DS. $image;
		$imagepath = $this->getImagePath($path); //dump ($imagepath, 'imagepath: ');
		return $imagepath;
	}
	
	function getTeacherThumbnail($image1=NULL, $image2=NULL)
	{
		$imagepath = array();
		//$image1 is teacher->thumbnail, $image2 is teacher->thumb
		if ($image1 == '- No Image - ' || !$image1)
		{
			$path = $image2;
		}
		else
		{
			$folder = $this->getTeacherImageFolder();
			$path = $folder .DS. $image1;
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
			$path = $folder .DS. $image1;
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
			$path = $folder .DS. $media1;

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
		if ($image == '- Default Image -' || !$image)
		{
			$image = 'showhide.gif'; $folder = 'components/com_biblestudy/images';
		}
		else
		{
			$folder = $this->getMediaImageFolder();
		}
		
		$path = $folder .DS. $image;
		$imagepath = $this->getImagePath($path); //dump ($imagepath, 'imagepath: ');
		return $imagepath;
	}
	
	
} // End of class
	
?>