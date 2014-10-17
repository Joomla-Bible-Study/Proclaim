<?php
defined('_JEXEC') or die;

class JBSMThumbnail
{

	/**
	 * Creates a thumbnail for an uploaded image
	 *
	 * @param      $file
	 * @param      $path
	 * @param null $size
	 *
	 * @since 8.1.0
	 */
	public static function create($file, $path, $size = 100)
	{
		$original = JPATH_ROOT . '/' . $path . '/original_' . $file['name'];
		$thumb    = JPATH_ROOT . '/' . $path . '/thumb_' . $file['name'];

		// Delete destination folder if it exists
		if (JFolder::exists(JPATH_ROOT . '/' . $path))
		{
			JFolder::delete(JPATH_ROOT . '/' . $path);
		}

		// Move uploaded image to destination
		JFolder::create(JPATH_ROOT . '/' . $path);
		JFile::move($file[tmp_name], $original);

		// Create thumbnail
		$image     = new JImage($original);
		$thumbnail = $image->resize($size, $size);
		$thumbnail->toFile($thumb, IMAGETYPE_PNG);
	}

	public static function resize($path, $new_size)
	{
		$filename = str_replace('original_', '', basename($path));

		// Delete existing thumbnail
		$old_thumbs = JFolder::files(dirname($path), 'thumb_', true, true);
		foreach ($old_thumbs as $thumb)
		{
			Jfile::delete($thumb);
		}

		//Create new thumbnail
		$image     = new JImage($path);
		$thumbnail = $image->resize($new_size, $new_size);
		$thumbnail->toFile(dirname($path) . '/thumb_' . $filename, IMAGETYPE_PNG);
	}
}
