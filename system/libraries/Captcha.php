<?php defined('MYQEEPATH') or die('No direct script access.');

class Captcha_Core{
	// Image resource identifier and type ("png", "gif" or "jpeg")
	protected static $image_type = 'png';
	// Config values
	public static $config = array(
		'width'      => 150,
		'height'     => 50,
		'complexity' => 4,
		'background' => '',
		'fontpath'   => MYAPPPATH,
		'fonts'      => array('fonts/DejaVuSerif.ttf'),
		'promote'    => FALSE,
		'life'		 => 1800,		//有效时间，单位秒
	);
	protected static $image;
	protected static $response = '';
	protected static $background_image;
	
	protected static $sessionname = '_img_code';
	protected static $valid_countname = '_img_captcha_valid_count';

	public static function valid($mycode,$delsession=false){
		if (!($code = Session::instance() -> get(self::$sessionname))){
			return 0;
		}else{
			if ($_SERVER['REQUEST_TIME'] - $code['time'] <= self::$config['life'] && $code['time']>0 && strtoupper($mycode)==strtoupper($code['code'])){
				if ($delsession)Session::instance() -> delete(self::$sessionname,self::$valid_countname);
				return 1;
			}else{
				$errornum = (int)Session::instance() -> get(self::$valid_countname)+1;
				Session::instance() -> set(self::$valid_countname,$errornum);
				return -$errornum;
			}
		}
	}
	
	/**
	 * Gets or sets the number of valid Captcha responses for this session.
	 *
	 * @param   integer  new counter value
	 * @param   boolean  trigger invalid counter (for internal use only)
	 * @return  integer  counter value
	 */
	public static function valid_count($new_count = NULL, $invalid = FALSE)
	{
		// Pick the right session to use
		$session = self::$valid_countname;

		// Update counter
		if ($new_count !== NULL)
		{
			$new_count = (int) $new_count;

			// Reset counter = delete session
			if ($new_count < 1)
			{
				Session::instance()->delete($session);
			}
			// Set counter to new value
			else
			{
				Session::instance()->set($session, (int) $new_count);
			}

			// Return new count
			return (int) $new_count;
		}

		// Return current count
		return (int) Session::instance()->get($session);
	}

	/**
	 * Gets or sets the number of invalid Captcha responses for this session.
	 *
	 * @param   integer  new counter value
	 * @return  integer  counter value
	 */
	public function invalid_count($new_count = NULL)
	{
		return $this->valid_count($new_count, TRUE);
	}
	
	/**
	 * Checks whether user has been promoted after having given enough valid responses.
	 *
	 * @param   integer  valid response count threshold
	 * @return  boolean
	 */
	public static function promoted($threshold = NULL)
	{
		// Promotion has been disabled
		if (self::$config['promote'] === FALSE)
			return FALSE;

		// Use the config threshold
		if ($threshold === NULL)
		{
			$threshold = self::$config['promote'];
		}

		// Compare the valid response count to the threshold
		return (self::valid_count() >= $threshold);
	}

	/**
	 * render image
	 *
	 * @param array $config
	 * @return image
	 */
	public static function render($config = false)
	{
		if (is_array($config)){
			self::$config = array_merge(self::$config,$config);
		}
		if (empty(self::$response)){
			self::generate_challenge();
		}
		// Creates self::$image
		self::image_create(self::$config['background']);

		// Add a random gradient
		if (empty(self::$config['background']))
		{
			$color1 = imagecolorallocate(self::$image, mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 100));
			$color2 = imagecolorallocate(self::$image, mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 100));
			self::image_gradient($color1, $color2);
		}

		// Add a few random circles
		for ($i = 0, $count = mt_rand(10, self::$config['complexity'] * 3); $i < $count; $i++)
		{
			$color = imagecolorallocatealpha(self::$image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255), mt_rand(80, 120));
			$size = mt_rand(5, self::$config['height'] / 3);
			imagefilledellipse(self::$image, mt_rand(0, self::$config['width']), mt_rand(0, self::$config['height']), $size, $size, $color);
		}

		// Calculate character font-size and spacing
		$default_size = min(self::$config['width'], self::$config['height'] * 2) / strlen(self::$response);
		$spacing = (int) (self::$config['width'] * 0.9 / strlen(self::$response));

		// Background alphabetic character attributes
		$color_limit = mt_rand(96, 160);
		$chars = 'ABEFGJKLPQRTVY';

		// Draw each captcha character with varying attributes
		for ($i = 0, $strlen = strlen(self::$response); $i < $strlen; $i++)
		{
			// Use different fonts if available
			$font = self::$config['fontpath'].self::$config['fonts'][array_rand(self::$config['fonts'])];

			$angle = mt_rand(-40, 20);
			// Scale the character size on image height
			$size = $default_size / 10 * mt_rand(8, 12);
			$box = imageftbbox($size, $angle, $font, self::$response[$i]);

			// Calculate character starting coordinates
			$x = $spacing / 4 + $i * $spacing;
			$y = self::$config['height'] / 2 + ($box[2] - $box[5]) / 4;

			// Draw captcha text character
			// Allocate random color, size and rotation attributes to text
			$color = imagecolorallocate(self::$image, mt_rand(150, 255), mt_rand(200, 255), mt_rand(0, 255));

			// Write text character to image
			imagefttext(self::$image, $size, $angle, $x, $y, $color, $font, self::$response[$i]);

			// Draw "ghost" alphabetic character
			$text_color = imagecolorallocatealpha(self::$image, mt_rand($color_limit + 8, 255), mt_rand($color_limit + 8, 255), mt_rand($color_limit + 8, 255), mt_rand(70, 120));
			$char = substr($chars, mt_rand(0, 14), 1);
			imagettftext(self::$image, $size * 1.4, mt_rand(-45, 45), ($x - (mt_rand(5, 10))), ($y + (mt_rand(5, 10))), $text_color, $font, $char);
		}

		// Output
		return self::image_render();
	}
	/**
	 * Generates a new captcha challenge.
	 *
	 * @return  string  the challenge answer
	 */
	protected static function generate_challenge()
	{
		// Complexity setting is used as character count
		self::$response = self::random(max(1, self::$config['complexity']));
		Session::instance() -> set(self::$sessionname,array('code'=>self::$response,'time'=>$_SERVER['REQUEST_TIME']));
	}

	protected static function random($length = 8)
	{
		$pool = '2345679ACDEFHJKLMNPRSTUVWXYZ';

		$str = '';

		$pool_size = strlen($pool);

		for ($i = 0; $i < $length; $i++)
		{
			$str .= substr($pool, mt_rand(0, $pool_size - 1), 1);
		}

		return $str;
	}


	/**
	 * Creates an image resource with the dimensions specified in config.
	 * If a background image is supplied, the image dimensions are used.
	 *
	 * @throws  Kohana_Exception  if no GD2 support
	 * @param   string  path to the background image file
	 * @return  void
	 */
	protected function image_create($background = NULL)
	{
		// Check for GD2 support
		if ( ! function_exists('imagegd2'))
		Myqee::show500('captcha.requires_GD2');

		// Create a new image (black)
		self::$image = imagecreatetruecolor(self::$config['width'], self::$config['height']);

		// Use a background image
		if ( ! empty($background))
		{
			// Create the image using the right function for the filetype
			$function = 'imagecreatefrom'.self::image_type($filename);
			self::$background_image = $function($background);

			// Resize the image if needed
			if (imagesx(self::background_image) !== self::$config['width']
			OR imagesy(self::background_image) !== self::$config['height'])
			{
				imagecopyresampled
				(
				self::image, self::background_image, 0, 0, 0, 0,
				self::$config['width'], self::$config['height'],
				imagesx(self::background_image), imagesy(self::background_image)
				);
			}

			// Free up resources
			imagedestroy(self::background_image);
		}
	}

	/**
	 * Fills the background with a gradient.
	 *
	 * @param   resource  gd image color identifier for start color
	 * @param   resource  gd image color identifier for end color
	 * @param   string    direction: 'horizontal' or 'vertical', 'random' by default
	 * @return  void
	 */
	protected function image_gradient($color1, $color2, $direction = NULL)
	{
		$directions = array('horizontal', 'vertical');

		// Pick a random direction if needed
		if ( ! in_array($direction, $directions))
		{
			$direction = $directions[array_rand($directions)];

			// Switch colors
			if (mt_rand(0, 1) === 1)
			{
				$temp = $color1;
				$color1 = $color2;
				$color2 = $temp;
			}
		}

		// Extract RGB values
		$color1 = imagecolorsforindex(self::$image, $color1);
		$color2 = imagecolorsforindex(self::$image, $color2);

		// Preparations for the gradient loop
		$steps = ($direction === 'horizontal') ? self::$config['width'] : self::$config['height'];

		$r1 = ($color1['red'] - $color2['red']) / $steps;
		$g1 = ($color1['green'] - $color2['green']) / $steps;
		$b1 = ($color1['blue'] - $color2['blue']) / $steps;

		if ($direction === 'horizontal')
		{
			$x1 =& $i;
			$y1 = 0;
			$x2 =& $i;
			$y2 = self::$config['height'];
		}
		else
		{
			$x1 = 0;
			$y1 =& $i;
			$x2 = self::$config['width'];
			$y2 =& $i;
		}

		// Execute the gradient loop
		for ($i = 0; $i <= $steps; $i++)
		{
			$r2 = $color1['red'] - floor($i * $r1);
			$g2 = $color1['green'] - floor($i * $g1);
			$b2 = $color1['blue'] - floor($i * $b1);
			$color = imagecolorallocate(self::$image, $r2, $g2, $b2);

			imageline(self::$image, $x1, $y1, $x2, $y2, $color);
		}
	}

	/**
	 * Returns the img html element or outputs the image to the browser.
	 *
	 * @param   boolean  html output
	 * @return  mixed    html string or void
	 */
	protected function image_render()
	{
		// Send the correct HTTP header

		header("Cache-Control:no-cache,must-revalidate");
		header("Pragma:no-cache");
		header('Content-Type: image/'.self::$image_type);
		header("Connection:close");

		// Pick the correct output function
		$function = 'image'.self::$image_type;
		$function(self::$image);

		// Free up resources
		imagedestroy(self::$image);
	}

}
