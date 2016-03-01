<?php

/*
Plugin Name: Image sizes in custom folder
Description: Put all resized images into separate folders (instad of throwing everything into wp-content/uploads)
Author: Ionuț Staicu
Version: 1.0.1
Author URI: http://ionutstaicu.com
 */

// based on Robbert code: http://wordpress.stackexchange.com/a/126369/223

if (!defined('ABSPATH')) {
	exit;
}

add_filter("wp_image_editors", function ($editors) {
	array_unshift($editors, "WP_Image_Editor_Custom_GD");
	array_unshift($editors, "WP_Image_Editor_Custom_Imagick");
	return $editors;
});


require_once ABSPATH . WPINC . "/class-wp-image-editor.php";
require_once ABSPATH . WPINC . "/class-wp-image-editor-gd.php";
require_once ABSPATH . WPINC . "/class-wp-image-editor-imagick.php";

abstract class WP_Image_Editor_Custom_Abstract extends WP_Image_Editor_GD
{
	protected $baseFolderName = 'resized/';

	public function generate_filename($prefix = null, $dest_path = null, $extension = null)
	{
		// If empty, generate a prefix with the parent method get_suffix().
		if (!$prefix) {
			$prefix = $this->get_suffix();
		}

		// Determine extension and directory based on file path.
		$info = pathinfo($this->file);
		$dir = $info['dirname'];

		$ext = $info['extension'];

		// Determine image name.
		$name = wp_basename($this->file, ".$ext");

		// Allow extension to be changed via method argument.
		$new_ext = strtolower($extension ? $extension : $ext);

		// Default to $_dest_path if method argument is not set or invalid.
		if (!is_null($dest_path) && $_dest_path = realpath($dest_path)) {
			$dir = $_dest_path;
		}

		// Return our new prefixed filename.
		return trailingslashit($dir) . "{$this->baseFolderName}{$prefix}/{$name}.{$new_ext}";
	}

	public function multi_resize($sizes)
	{
		$sizes = parent::multi_resize($sizes);

		foreach ($sizes as $slug => $data) {
			$sizes[$slug]['file'] = $this->baseFolderName . $data['width'] . "x" . $data['height'] . "/" . $data['file'];
		}

		return $sizes;
	}
}

class WP_Image_Editor_Custom_Imagick extends WP_Image_Editor_Custom_Abstract
{}
class WP_Image_Editor_Custom_GD extends WP_Image_Editor_Custom_Abstract
{}
