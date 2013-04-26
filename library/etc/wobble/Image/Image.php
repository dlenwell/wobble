<?php
/**
 * Image class file
 *
 * @author	David Lenwell
 * @date	May 30, 2006
 * @file	Image.php
 */

/*
 * The Image class is used to manipulate images
 *
 * This class wraps the PHP image manipulation functions. All manipulations are cached until the Display or Write methods
 * are called, allowing the caller to perform multiple nested operations before retrieving the final image. If PHP is compiled
 * with the support, the class will dynamically reallocate memory in PHP to circumvent 'out of memory' errors most of the 
 * time.
 */
class Image_d {
	
	/**
	 * Default assumed values. These exist to ensure no zeroes are used during memory calculation during dynamic 
	 * PHP memory reallocation.
	 */
	const DEFAULT_BITS = 3;
	const DEFAULT_CHANNEL = 6;
	
	/**
	 * Standard private Image data (x, y, mime type)
	 */
	private $x = null;
	private $y = null;
	private $type = null;
	
	/**
	 * Private image data used to calculate memory usage
	 */
	private $bits = Image_d::DEFAULT_BITS; 
	private $channels = Image_d::DEFAULT_CHANNEL;
	
	/**
	 * GD resource cache
	 */
	private $resourceCache = null;
	
	/**
	 * Constructor
	 *
	 * Dynamically allocates memory in PHP as necessary.
	 *
	 * @param	mixed	Either a path to the image file or a GD resource handle
	 * @param	string	(optional) Mime type of the image if a resource was provided in the first argument. Valid values are 'gif', 'jpeg', and 'png'.
	 */
	public function __construct($image = NULL, $type = 'jpeg') {
		$this->read($image, $type);
	}
	
	/**
	 * Destructor
	 *
	 * Frees up the memory used by the image cache
	 *
	 * @return	void
	 */
	public function __destruct() {
		@imagedestroy($this->resourceCache);
		$this->resourceCache = NULL;
	}
	
	/**
	 * Reads in image data from a file. Any previous data in the object is destroyed. Also
	 * reallocates memory as necessary.
	 *
	 * @param	mixed	Either a path to the image file or a GD resource handle
	 * @param	string	(optional) Mime type of the image if a resource was provided in the first argument. Valid values are 'gif', 'jpeg', and 'png'.
	 * @return	void
	 */
	public function read($image, $type = 'jpeg') {
		$this->x = 				NULL;
		$this->y = 				NULL;
		$this->type = 			NULL;
		$this->bits = 			Image_d::DEFAULT_BITS;
		$this->channels = 		Image_d::DEFAULT_CHANNEL;
		$this->resourceCache =	NULL;

		if($image) {
			/*
			 * A path was passed
			 */		 
			if(@get_resource_type($image) != 'gd' && is_file($image)) {
				$imageInfo = @getimagesize($image);
				$this->x = 			$imageInfo[0];
				$this->y = 			$imageInfo[1];
				$this->type = 		$imageInfo[2];
				if(isset($imageInfo['bits'])) {
					$this->bits = $imageInfo['bits'];
				}
				if(isset($imageInfo['channels'])) {
					$this->channels = $imageInfo['channels'];
				}
				$this->reallocate_memory();
				$this->create_resource($image);

			/*
			 * A GD resource was passed
			 */
			} else {
				$this->type = strtolower($type);
				$this->x = imagesx($image);
				$this->y = imagesy($image);
				$this->reallocate_memory();
				$this->resourceCache = $image;
			}
			
			/*
			 * Reallocate again for when we have to process images
			 */
			$this->reallocate_memory();
		}		
	}
	
	/**
	 * Return the image width
	 *
	 * @return	integer
	 */
	public function GetX() {
		return $this->x;
	}
	
	/**
	 * Return the image height
	 *
	 * @return	integer
	 */
	public function GetY() {
		return $this->y;
	}
	
	/**
	 * Return the image type (gif, jpeg, png)
	 *
	 * @return	string
	 */
	public function GetType() {
		return $this->type;
	}
	
	/**
	 * Send the relevant headers to the browser. This method shouldn't need to be manually called. Use Display() instead.
	 *
	 * @return	void
	 */
	public function Header() {
		switch($this->type) {
			case IMAGETYPE_GIF:
				header("Content-type: image/gif");
				break;
			case IMAGETYPE_JPEG:
				header("Content-type: image/jpeg");
				break;
			case IMAGETYPE_PNG:
				header("Content-type: image/png");
				break;
			default:
				header("Content-type: image/jpeg");
				break;		
		}
		
	}
		
	/**
	 * Resizes the image
	 *
	 * @param	int	Target X dimension
	 * @param	int	Target Y dimension
	 * @return	void
	 */
	public function Resize($newX, $newY) {
		/*
		 * Figure out the constraining axis to determine the scale.
		 * The constraining axis is the one where with the smallest
		 * ratio between the new length and the original
		 */
		$ratio = 1;
		
		$ratioX = $newX / $this->x;
		$ratioY = $newY / $this->y;

		if($ratioX <= $ratioY) {
			$ratio = $ratioX;
		} else {
			$ratio = $ratioY;	
		}

		$newX = intval($this->x * $ratio);
		$newY = intval($this->y * $ratio);
		
		$holderImage = imagecreatetruecolor($newX,$newY);

		imagecopyresampled($holderImage, $this->resourceCache, 0, 0, 0, 0, $newX, $newY, $this->x, $this->y);

		/*
		 * Remember to update our internal caches of the x and y values, but only after they've been used to scale
		 */
		$this->x = $newX;
		$this->y = $newY;

		$this->resourceCache = $holderImage;
	}
	
	/**
	 * Crops the image at the desired location. The new size parameters are optional because
	 * it is assumed the crop goes from the top left point specified all the way to the bottom
	 * right corner. If a no value or too large a value is passed into the size parameters,
	 * they are redefined to match the maximum possible values of their respective axis.
	 *
	 * @param	integer	Horizontal offset, starting from the left side
	 * @param	integer	Vertical offset, starting from the top
 	 * @param	integer	(optional) Horizontal size of new image
 	 * @param	integer	(optional) Vertical size of new image
	 * @return	void
	 */
	public function Crop($offsetX, $offsetY, $lengthX = 0, $lengthY = 0) {
		
		/*if($lengthX <= 0 || $lengthX + $offsetX > $this->x) {
			$lengthX = $this->x - $offsetX;
		}
		
		if($lengthY <= 0 || $lengthY + $offsetY > $this->y) {
			$lengthY = $this->y - $offsetY;
		}
		*/
		$holderImage = imagecreatetruecolor($lengthX, $lengthY);

		imagecopy($holderImage, $this->resourceCache, 0, 0, $offsetX, $offsetY, $lengthX, $lengthY);

		$this->x = intval($lengthX);
		$this->y = intval($lengthY);
		
		$this->resourceCache = $holderImage;
	}
	
	/**
	 * Flips the image along the Y axis. 
	 *
	 * @return	void
	 */
	public function FlipY() {
		$holderImage = imagecreatetruecolor($this->x, $this->y);

		for ($loopY = 0; $loopY < $this->y; $loopY++) {
			imagecopy($holderImage, $this->resourceCache, 0, $loopY, 0, $this->y - $loopY - 1, $this->x, 1);
		}
		
		$this->resourceCache = $holderImage;
	}

	/**
	 * Flips the image along the X axis. 
	 *
	 * @return	void
	 */
	public function FlipX() {
		$holderImage = imagecreatetruecolor($this->x, $this->y);

		for ($loopX = 0; $loopX < $this->x; $loopX++) {
			imagecopy($holderImage, $this->resourceCache, $loopX, 0, $this->x - $loopX - 1, 0, 1, $this->y);
		}

		$this->resourceCache = $holderImage;
	}
	
	/** 
	 * Rotates the image by the number of degrees specified. Also
	 * resizes the canvas if necessary.
	 */
	//public function Rotate($degrees, $fillColor = '000000');
	
	/**
	 * Sets a watermark that is inserted into any output images. 
	 *
	 * @param	string	Watermark text
	 * @param	string	X position of watermark
	 * @param	string	Y position of watermark
	 * @param	string	Hex color value
	 * @param	integer	Size of the text (1 - 5)
	 */
	public function Watermark($text, $horizontal, $vertical, $color = '000000', $size = 3) {
		$textcolor = imagecolorallocate($this->resourceCache, hexdec(substr($color, 0, 2)), hexdec(substr($color, 2, 2)), hexdec(substr($color, 4, 2)));
		imagestring($this->resourceCache, $size, $horizontal, $vertical, $text, $textcolor);
	}

	/**
	 * Return the GD resource being worked on
	 *
	 * @return	resource	GD resource
	 */
	public function Resource() { 
		return $this->resourceCache;
	}
	
	/**
	 * Dump the current image data into a file
	 *
	 * @param	string	Filename
	 * @param	integer	Quality of the image (0 - 100, 100 being the best). Only applicable for JPEG images.
	 * @return	void
	 */
	public function Write($filename, $quality = -1) {
		switch($this->type) {
			case IMAGETYPE_GIF:
				if($filename) { // PHP is very sensitive about the second parameter
					imagegif($this->resourceCache, $filename);
				} else {
					imagegif($this->resourceCache);
				}
				break;
			case IMAGETYPE_JPEG:
				imagejpeg($this->resourceCache, $filename, $quality);
				break;
			case IMAGETYPE_PNG:
				if($filename) { // PHP is very sensitive about the second parameter
					imagepng($this->resourceCache, $filename);
				} else {
					imagepng($this->resourceCache);
				}
				break;
			default:
				imagejpeg($this->resourceCache, $filename, $quality);
				break;		
		}
	}
	
	/**
	 * Display the current image
	 *
	 * @param	integer	Quality of the image (0 - 100, 100 being the best). Only applicable for JPEG images.
	 * @return	void
	 */
	public function Display($quality = -1) {
		$this->Header();
		$this->Write(NULL, $quality); // passing null simply dumps the data to the output buffer
	}
	
	/**
	 * Send the raw image data to the output buffer
	 *
	 * @param	integer	Quality of the image (0 - 100, 100 being the best). Only applicable for JPEG images.
	 * @return	void
	 */
	public function Output($quality = -1) {
		$this->Write(NULL, $quality); // passing null simply dumps the data to the output buffer
	}
	
	
	/**
	 * PHP can crash if it runs out of memory, which is very likely with this class. To get around this problem
	 * the image dimensions are caculated and extra memeory is dynamically allocated as necessary.
	 *
	 * @private
	 *
	 * @return	void
	 */
	private function reallocate_memory() {
		if ($this->y && $this->x && function_exists('memory_get_usage')) {
			$memoryNeeded = round((($this->x * $this->y * $this->bits * $this->channels / 8) + Pow(2, 16)) * 1.65);
			if(memory_get_usage() + $memoryNeeded > intval(ini_get('memory_limit') * pow(1024, 2))) {
				ini_set('memory_limit', intval(ini_get('memory_limit') + ceil(
						(
							(memory_get_usage() + $memoryNeeded) - intval(ini_get('memory_limit') * pow(1024, 2))
						) / pow(1024, 2)
					) . 'M'));
			}
		}
	}
	
	/**
	 * Create a GD resource from the provided path and store it in the internal cache for processing
	 *
	 * @private
	 *
	 * @param	string	Path to file
	 * @return	void
	 */
	private function create_resource($path) {
		switch($this->type) {
			case IMAGETYPE_GIF:
				$this->resourceCache = imagecreatefromgif($path);
				break;
			case IMAGETYPE_JPEG:
				$this->resourceCache = imagecreatefromjpeg($path);
				break;
			case IMAGETYPE_PNG:
				$this->resourceCache = imagecreatefrompng($path);
				break;
			default:
				$this->resourceCache = NULL;
				break;		
		}
	}
	
}