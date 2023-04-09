<?php
namespace Tribe\Vimeo_WP\Settings;

class Embed {

	const AUTOPAUSE   = 'autopause';
	const AUTOPLAY    = 'autoplay';
	const BACKGROUND  = 'background';
	const BYLINE      = 'byline';
	const COLOR       = 'color';
	const CONTROLS    = 'controls';
	const DNT         = 'dnt';
	const HEIGHT      = 'height';
	const ID          = 'id';
	const LOOP        = 'loop';
	const MAXHEIGHT   = 'maxheight';
	const MAXWIDTH    = 'maxwidth';
	const MUTED       = 'muted';
	const PIP         = 'pip';
	const PLAYSINLINE = 'playsinline';
	const PORTRAIT    = 'portrait';
	const QUALITY     = 'quality';
	const RESPONSIVE  = 'responsive';
	const SPEED       = 'speed';
	const TEXTTRACK   = 'texttrack';
	const TITLE       = 'title';
	const TRANSPARENT = 'transparent';
	const URL         = 'url';
	const WIDTH       = 'width';

	private $autopause   = true;
	private $autoplay    = false;
	private $background  = false;
	private $byline      = true;
	private $color       = 'none';
	private $controls    = true;
	private $dnt         = false;
	private $height      = 'none';
	private $id          = false;
	private $loop        = false;
	private $maxheight   = 'none';
	private $maxwidth    = 'none';
	private $muted       = false;
	private $pip         = false;
	private $playsinline = true;
	private $portrait    = true;
	private $quality     = 'auto';
	private $responsive  = false;
	private $speed       = false;
	private $texttrack   = 'none';
	private $title       = true;
	private $transparent = true;
	private $url         = '';
	private $width       = 'none';

	protected $embed_settings = [];

	public function __construct() {
		$this->embed_settings = $this->set_defaults();
	}

	/**
	 * Returns the default settings for the Vimeo Embed as an array.
	 *
	 * @return array
	 */
	public function get_defaults() {
		return $this->embed_settings;
	}

	/**
	 * Sets the Vimeo Player API Default Settings
	 *
	 * @return array
	 */
	private function set_defaults() {
		return [
			self::AUTOPAUSE   => $this->autopause,
			self::AUTOPLAY    => $this->autoplay,
			self::BACKGROUND  => $this->background,
			self::BYLINE      => $this->byline,
			self::COLOR       => $this->color,
			self::CONTROLS    => $this->controls,
			self::DNT         => $this->dnt,
			self::HEIGHT      => $this->height,
			self::ID          => $this->id,
			self::LOOP        => $this->loop,
			self::MAXHEIGHT   => $this->maxheight,
			self::MAXWIDTH    => $this->maxwidth,
			self::MUTED       => $this->muted,
			self::PIP         => $this->pip,
			self::PLAYSINLINE => $this->playsinline,
			self::PORTRAIT    => $this->portrait,
			self::QUALITY     => $this->quality,
			self::RESPONSIVE  => $this->responsive,
			self::SPEED       => $this->speed,
			self::TEXTTRACK   => $this->texttrack,
			self::TITLE       => $this->title,
			self::TRANSPARENT => $this->transparent,
			self::URL         => $this->url,
			self::WIDTH       => $this->width,
		];
	}
}
