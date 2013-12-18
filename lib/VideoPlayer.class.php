<?php
  class VideoPlayer {

    private $errors = array(
      1 => 'No archive joined.',
      2 => 'Invalid extension.',
      3 => 'File error.',
      4 => 'The file does not exist.'
    );
    private $error = 0;

    private $video = NULL;
    private $extension = NULL;
    private $accepted_extensions = array('wmv', 'asx','flv','mov','rmv','rmvb','swf', 'mpg', 'mpeg');
    private $player =  NULL;
    private $width = 0;
    private $height = 0;
    private $autoplay = 'false';
    private $controls = 'false';
    private $swfObject = NULL;

    /**
     * Class constructor
     *
     * @param String video // Url of the video
     * @param int width // Width of the player
     * @param int height // Height of the player
     * @param String autoplay (true, false) // If the player is autoplay or no
     * @param String constrols (true, false) // If the player show controls or no
     */
    public function __construct($video, $width, $height, $autoplay = 'false', $controls = 'true') {

      $this->video    = $video;
      $this->width    = $width;
      $this->height   = $height;
      $this->autoplay = $autoplay;
      $this->controls = $controls;

      if (!$this->verifyVideo()) { return false; }

      if(!$this->verifyExtension()) { return false; }

    }

    /**
     * Verify if archive exists
     *
     */
    private function verifyVideo() {

      $video = isset( $this->video ) ? $this->video : false;

      if( !$this->video || $this->video == '' ){
        $this->error = 1;
        return false;
      } else {
        return true;
      }

    }

    /**
     * Verify the video extension
     *
     */
    private function verifyExtension() {

      $this->extension = end(explode('.', $this->video));

      if (!in_array($this->extension, $this->accepted_extensions)) {
        $this->error = 2;
        return false;
      } else {
        return true;
      }

    }

    /**
     * Return the correct
     *
     */
    private function returnPlayer() {

      /// Windows media player
      if (in_array($this->extension, array('wmv', 'asx'))) {

        $this->player  =
          '<object type="video/x-ms-wmv"
          data="'.$this->video.'"
          width="'.$this->width.'" height="'.$this->height.'">
          <param name="src" value="'.$this->video.'" />
          <param name="autostart" value="'.$this->autoplay.'" />
          <param name="controller" value="'.$this->controls.'" />
          </object>';

        return true;

      /// Real player
      } else if (in_array($this->extension, array('rmv', 'rmvb'))) {

        if ($this->controls == 'true') {
          $control  = 'all';
          $control_ = 'controlpanel';
        } else {
          $control  = 'false';
          $control_ = 'false';
        }

        $this->player  =
          '<object id="myMovie" classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA"
            width="'.$this->width.'" height="'.$this->height.'">
          <param name="src" value="'.$this->video.'">
          <param name="console" value="video1">
          <param name="controls" value="'.$control.'">
          <param name="autostart" value="'.$this->autoplay.'">
          <param name="loop" value="false">
          <embed name="myMovie" src="'.$this->video.'" width="'.$this->width.'" height="'.$this->height.'"
            autostart="'.$this->autoplay.'" loop="false" nojava="false" console="video1" controls="'.$control_.'">
          </embed>
          <noembed><a href="'.$this->video.'">Play first clip</a></noembed>
          </object>';

        return true;

      /// Quick time
      } else if (in_array($this->extension, array('mov'))) {

        $this->player  =
          '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B"
            codebase="http://www.apple.com/qtactivex/qtplugin.cab"
            width="'.$this->width.'" height="'.$this->height.'">
          <param name="src" value="'.$this->video.'" />
          <param name="controller" value="'.$this->controls.'" />
          <param name="autoplay" value="'.$this->autoplay.'" />
          <!--[if !IE]>-->
            <object type="video/quicktime"
              data="'.$this->video.'"
              width="'.$this->width.'" height="'.$this->height.'">
            <param name="autoplay" value="'.$this->autoplay.'" />
            <param name="controller" value="'.$this->controls.'" />
            </object>
          <!--<![endif]-->
          </object>';

        return true;

      /// Mpeg
      } else if (in_array($this->extension, array('mpg', 'mpeg'))) {

        $this->player =
        '<embed src="'.$this->video.'" autostart='.$this->autoplay.'
          loop=false controller='.$this->controls.'
          width="'.$this->width.'" height="'.$this->height.'" />';

        return true;

      /// Flash video player  
      } else if (in_array($this->extension, array('flv'))) {

        if ($this->swfObject || $this->swfObject != NULL) {
          $this->player = '<script type="text/javascript" src="'.$this->swfObject.'"></script>';
        } else {
          $this->player = '<script type="text/javascript" src="http://www.ianitsky.com.br/_extras/phpclasses/flashplayer/swfobject.js"></script>';
        }
        $this->player .=
          '<div id="MyMovie">
            <a href="http://www.macromedia.com/go/getflashplayer">Baixe o flash player</a> para visualizar este.
          </div>
          <script type="text/javascript">
            var s1 = new SWFObject("http://www.ianitsky.com.br/_extras/phpclasses/flashplayer/flvplayer.swf", "single","'.$this->width.'","'.$this->height.'","7");
            s1.addParam("allowfullscreen","true");
            s1.addVariable("file","'.$this->video.'");
            s1.addVariable("showdigits", "'.$this->controls.'");
            s1.addVariable("autostart", "'.$this->autoplay.'");
            s1.write("MyMovie");
          </script>';

        return true;

      /// Flash player
      } else if (in_array($this->extension, array('swf'))) {

        $this->player  =
        '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"
        codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0"
         width="'.$this->width.'"
         height="'.$this->height.'">
        <param name="movie" value="'.$this->video.'">
        <param name="wmode" value="transparent">
        <embed src="'.$this->video.'"
         wmode="transparent"
         pluginspage="http://www.macromedia.com/go/getflashplayer"
         type="application/x-shockwave-flash"
         width="'.$this->width.'"
         height="'.$this->height.'">
        </embed>
        </object>';

        return true;

      /// Error
      } else {
        $this->error = 3;
        return false;
      }

    }

    /**
     * Return the errors if exist
     *
     * @return String
     */
    public function getLastError(){

      if ($this->error != 0) {
        return $this->errors[$this->error];
      } else {
        return NULL;
      }

    }

    public function setSwfObject($file) {

      if (is_file($file)) {
        $this->swfObject = $file;
        return true;
      } else {
        $this->error = 4;
        return false;
      }

    }

    /**
     * Return the player
     *
     * @return String
     */
    public function player() {

      if(!$this->returnPlayer()) { return false; }

      return $this->player;

    }

  }

?> 