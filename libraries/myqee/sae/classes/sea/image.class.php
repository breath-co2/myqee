<?php
/** errno define:
 *   0,   success
 *   111, parameter not exist
 *   112, parameter invalid
 *   113, image data length more than 2M
 *   116, lack some request headers
 *   117, invalid accesskey or secretkey
 *   118, access fordden for quota limit
 *   119, processing internal error
 *   199, unknown error
 */
class sae_image
{
  private static $__accesskey = "";
  private static $__secretkey = "";
  private static $__errno=0;
  private static $__errmsg="";
  private static $__img_data = "";
  const baseimgurl = "http://image.sae.sina.com.cn/?";
  const image_limitsize = 2097152;

  function __construct($accesskey="", $secretkey="", $img_data="") { // if not call composite, you must set img_data!!
    self::$__accesskey = $accesskey;
    self::$__secretkey = $secretkey;
    if(strlen($img_data) > self::image_limitsize) {
      self::$__errno = 112;
      self::$__errmsg = "image data length more than 2M";
      return false;
    }
    self::$__img_data = $img_data;
  }

  public function errmsg() {
    return self::$__errmsg;
  }

  public function errno() {
    return self::$__errno;
  }

  public function setAuth( $accesskey, $secretkey) {
    self::$__accesskey = $accesskey;
    self::$__secretkey = $secretkey;
  }

  public function setData( $img_data ) {
    if(strlen($img_data) > self::image_limitsize) {
      self::$__errno = 112;
      self::$__errmsg = "image data length more than 2M";
      return false;
    }
    self::$__img_data = $img_data;
  }

  public function resize($width=0, $height=0, $format="jpg") {
    if(self::imageNull()) return false;
    $get = "act=resize&width=$width&height=$height&format=$format";
    $post = array("imagedata"=>self::$__img_data);
    return self::postImgData($get, $post);
  }

  public function resizeRatio($ratio, $format="jpg") {
    if(self::imageNull()) return false;
    $get = "act=resizeratio&ratio=$ratio&format=$format";
    $post = array("imagedata"=>self::$__img_data);
    return self::postImgData($get, $post);
  }
  public function crop($lx,$rx,$by,$ty, $format="jpg") {
    if(self::imageNull()) return false;
    $get = "act=crop&lx=$lx&ty=$ty&rx=$rx&by=$by&format=$format";
    $post = array("imagedata"=>self::$__img_data);
    return self::postImgData($get, $post);
  }

  public function rotate($degree=90, $format="jpg") {
    if(self::imageNull()) return false;
    $get = "act=rotate&degree=$degree&format=$format";
    $post = array("imagedata"=>self::$__img_data);
    return self::postImgData($get, $post);
  }

  public function flip_h($format="jpg") {
    if(self::imageNull()) return false;
    $get =  "act=flip_h&format=$format";
    $post = array("imagedata"=>self::$__img_data);
    return self::postImgData($get, $post);
  }

  public function flip_v($format="jpg") {
    if(self::imageNull()) return false;
    $get =  "act=flip_v&format=$format";
    $post = array("imagedata"=>self::$__img_data);
    //$post = urlencode("imagedata=" . self::$__img_data);
    return self::postImgData($get, $post);
  }

  public function convertFormat($format="jpg") {
    if(self::imageNull()) return false;
    $get = "act=format&format=$format";
    $post = array("imagedata"=>self::$__img_data);
    return self::postImgData($get, $post);
  }

  public function composite($input, $width, $height, $color=0, $format="jpg") {
    $get = "act=composite&width=$width&height=$height&color=$color&format=$format";
    return self::postImgData($get, $input);
  }

  private static function postImgData($getpara, $post) {
    $url = self::baseimgurl . $getpara;
    $s = curl_init();
    curl_setopt($s,CURLOPT_URL,$url);
    curl_setopt($s,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_0);
    curl_setopt($s,CURLOPT_TIMEOUT,15);
    curl_setopt($s,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($s,CURLOPT_HEADER, 1);
    curl_setopt($s,CURLINFO_HEADER_OUT, true);
    curl_setopt($s,CURLOPT_HTTPHEADER, self::genReqestHeader($post));
    curl_setopt($s,CURLOPT_POST,true);
    curl_setopt($s,CURLOPT_POSTFIELDS,$post);
    $ret = curl_exec($s);
    // exception handle, if error happens, set errno/errmsg, and return false
    $info = curl_getinfo($s);
    //print_r($info);
    //echo 'abab';
    //print_r($ret);
    //echo 'abab';
    if(empty($info['http_code'])) {
      self::$__errno = 120;
      self::$__errmsg = "image service segment fault";
      return false;
    } else if($info['http_code'] != 200) {
      self::$__errno = 121;
      self::$__errmsg = "image service internal error";
      return false;
    } else {
      if($info['size_download'] == 0) { // get ImageError header
        $header = substr($ret, 0, $info['header_size']);
        $imageheader = self::extractCustomHeader("ImageError", $header);
        if($imageheader == false) { // not found ImageError header
          self::$__errno = 199;
          self::$__errmsg = "unknown error";
          return false;
        }
        $err = explode(",", $imageheader, 2);
        self::$__errno = $err[0];
        self::$__errmsg = $err[1];
        return false;
      } else {
        $body = substr($ret, -$info['size_download']);
        return $body;
      }
    }
    return $ret;
  }

  private static function genSignature($content, $secretkey) {
    $sig = base64_encode(hash_hmac('sha256',$content,$secretkey,true));
    return $sig;
  }

  private static function genReqestHeader($post) {
    $timestamp = date('Y-m-d H:i:s');
    $cont1 = "ACCESSKEY".self::$__accesskey."TIMESTAMP".$timestamp;
    $reqhead = array("TimeStamp: $timestamp","AccessKey: ".self::$__accesskey, "Signature: " . self::genSignature($cont1, self::$__secretkey));
    return $reqhead;
  }

  private static function extractCustomHeader($key, $header) {
    $pattern = '/'.$key.'(.*?)'."\n/";
    if (preg_match($pattern, $header, $result)) {
      return $result[1];
    } else {
      return false;
    }
  }

  private static function imageNull() {
    if(self::$__img_data == "") {
      self::$__errno = 112;
      self::$__errmsg = "image data cannot be null";
      return true;
    }
    return false;
  }
}
