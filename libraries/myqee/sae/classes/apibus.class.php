<?php

//define("_APIBUS_HOME",  dirname( __FILE__ ) );
define("_APIBUS_HOME",  '/usr/local/sae/php/lib/php/apibus_lib/' );

	class apibus {

    private $accesskey;
    private $secretkey;

    function __construct(  $accesskey=null, $secretkey=null ) {

        $this->accesskey = ($accesskey!=null)?$accesskey:SAE_ACCESSKEY;
        $this->secretkey = ($secretkey!=null)?$secretkey:SAE_SECRETKEY;

    }

    /**
      * 创建服务对象 (静态方法)
     * @params String $name  服务名称
     * @params String $ver=1     版本号默认为 1 ( 阿拉伯数字 1~9 )
     * @params String $accesskey=null    Access Key默认为NULL 使用当前 Access Key
     * @params String $secretkey=null    Secret Key默认为NULL 使用当前 Secret Key
     * @return Mix 服务对象
     */
    static function init( $name, $ver=1, $accesskey=null, $secretkey=null ) {

        $accesskey = ($accesskey!=null)?$accesskey:SAE_ACCESSKEY;
        $secretkey = ($secretkey!=null)?$secretkey:SAE_SECRETKEY;

        $name = strtolower($name);
        $name = "apibus_".$name;
        $sdk_file = _APIBUS_HOME . "/apibus/service/$name/$ver/$name.php";

        if ( !file_exists($sdk_file) ) { // SDK not exist
             sae_debug(" <b>ApiBus_Fatal_error: </b> Service <b> [$name version: $ver ]</b> SDK Does Not Exist !
                          http://apibus.io/s/$name " );
             return ApiBusError( -1, " Service:[$name version: $ver ] SDK Does Not Exist ! " ) ;
        }
        require_once( $sdk_file );
        if ( !class_exists($name) ) { // Class not exist
             sae_debug(" <b>ApiBus_Fatal_error: </b> Class <b> [$name ]</b>  Does Not Exist !
                          http://apibus.io/s/$name " );
             return ApiBusError( -2, " Class:[$name] Does Not Exist ! "  );
        }
        return new $name( $accesskey,  $secretkey );
    }

    /**
      * 创建服务对象
     * @params String $name  服务名称
     * @params String $ver=1     版本号默认为 1 ( 阿拉伯数字 1~9 )
     * @params String $accesskey=null    Access Key默认为NULL 使用当前 Access Key
     * @params String $secretkey=null    Secret Key默认为NULL 使用当前 Secret Key
     * @return Mix 服务对象
     */
    function load( $name, $ver=1, $accesskey=null, $secretkey=null ) {

        $accesskey = ($accesskey!=null)?$accesskey:$this->accesskey;
        $secretkey = ($secretkey!=null)?$secretkey:$this->secretkey;

        $name = strtolower($name);
        $name = "apibus_".$name;
        $sdk_file = _APIBUS_HOME . "/apibus/service/$name/$ver/$name.php";

        if ( !file_exists($sdk_file) ) { // SDK不存在
             sae_debug(" <b>ApiBus_Fatal_error: </b> Service <b> [$name version: $ver ]</b> SDK Does Not Exist !
                          http://apibus.io/s/$name " );
             return ApiBusError( -1, " Service:[$name version: $ver ] SDK Does Not Exist ! " ) ;
        }
        require_once( $sdk_file );
        if ( !class_exists($name) ) { // Class不存在
             sae_debug(" <b>ApiBus_Fatal_error: </b> Class <b> [$name ]</b>  Does Not Exist !
                          http://apibus.io/s/$name " );
             return ApiBusError( -2, " Class:[$name] Does Not Exist ! "  );
        }
        return new $name( $accesskey,  $secretkey );
    }
}

/**
 * 创建错误对象
 * @params Number $errcode  错误号
 * @params String $errdesc    错误描述
 * @return Object 错误结构体
 */

 function ApiBusError( $errcode, $errdesc ) {
     return (object)array(
         "ApiBusError"=> (object)array (
              "errcode"=> $errcode,
              "errdesc"=>$errdesc
          )
     );
 }
