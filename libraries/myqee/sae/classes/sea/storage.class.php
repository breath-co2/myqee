<?php
/**
* $Id: SAE_Storage.php 1 2009-10-03
*
* Copyright (c) 2009, sina.  All rights reserved.
*
* @author William Hanwoody <hanwoody@gmail.com>
*
* ChangeLog:
* - 20091003  0.1 version published
*/

/**
* SAE Storage PHP class
*
*/
class sae_storage {

    public static $useSSL = false;

    private $__errno = 0;
    private static $__saeStorHandle;
    private static $__saeStorDomainHandle;
    private static $__saeAdminDomainHandle;
    private static $__accessKey; // Access key
    private static $__secretKey; // Secret key
    private static $__domAttr = array(
        'permission' => 48,
        'distribution' => array("bj"),
    );
    private $__fileAttr = array(
                'exprie' => 3600,
    );

    private static $__storEngine = "MongoGFS";

    /**
    * Constructor - if you're not using the class statically
    *
    * @param string $accessKey Access key
    * @param string $secretKey Secret key
    * @param boolean $useSSL Enable SSL
    * @return void
    */
    public function __construct($accessKey = null, $secretKey = null, $useSSL = false) {
        if ($accessKey !== null && $secretKey !== null)
            self::setAuth($accessKey, $secretKey);
        self::$useSSL = $useSSL;
        self::__getHandle();
        self::__getAdminDomainHandle();
    }

    /**
    * Set access key and secret key
    *
    * @param string $accessKey Access key
    * @param string $secretKey Secret key
    * @return void
    */
    public static function setAuth($accessKey, $secretKey) {
        self::$__accessKey = $accessKey;
        self::$__secretKey = $secretKey;
    }

    /**
    public static function error() {
        switch (self->$__errno) {
            case 0:
                return "success";
            case 1:
                return "";
            case 118:
                return "domain has existed";
            default:
                return "unknown error";
        }
    }

    */

    /**
    * Get a list of domains
    *
    * @param boolean $detailed Returns detailed domain list when true
    * @return array | false
    */
    public static function listDomains($detailed = false) {
        $attr = self::$__saeAdminDomainHandle->selectCollection("attr");
        $cursor = $attr->find(array("owner"=>self::$__accessKey));
        $ret = array();
        if(!$detailed) {
            foreach ($cursor as $id => $value) {
                array_push($ret, $value['domain']);
            }
            return $ret;
        }
        foreach ($cursor as $id => $value) {
            $dom = $value["domain"];
            unset($value["domain"]);
            unset($value["_id"]);
            $ret[$dom] = $value;
        }
        return $ret;
    }


    /*
    * Get attributes for a domain
    * If $key is Null, will return all attributes for this domain
    *
    * @param string $domain Domain name
    * @param array $key  attribute key name
    * @return array | false
    */
    public static function getDomainAttr($domain, $attr = array()) {
                if(0 == count($attr)) {
                        $ret = self::$__saeAdminDomainHandle->selectCollection("attr")->findOne(array("domain"=>"$domain"));
            unset($ret["domain"]);
            unset($ret["_id"]);
            return $ret;
                }

                $ret = self::$__saeAdminDomainHandle->selectCollection("attr")->findOne(array("domain"=>"$domain"), $attr);
        unset($ret["_id"]);
        return $ret;
    }


    /**
    * create a domain
    *
    * @param string $domain domain name
    * @param array $attr, if attribute array is empty, this domain will use default domattr!
    * @return boolean
    */
    public static function createDomain($domain, $attr=array()) {
        //if(self::$__saeStorHandle->selectDB($domain)) {
        //    return false;
        //}
                //$mgdb = new MongoDB(self::$__saeStorHandle, $domain);
        $attr_coll = self::$__saeAdminDomainHandle->selectCollection("attr");
        if($attr_coll->findOne(array("domain"=>$domain))) {
            $__errno = 118;
            return false;
        }
                // we should let $attr include all necessary k/v
        $attr['domain'] = $domain;
        $attr['owner'] = self::$__accessKey;
        $attr['permission'] = self::$__domAttr['permission'];// if (not isset($attr['permission']));
        $attr['distribution'] = self::$__domAttr['distribution'];// if (not exist $attr['distribution']);
                $ret = $attr_coll->insert($attr);
                if(!$ret) { $__errno = 115; return false; }

                return true;
    }


    /**
        * set attributes for a domain
        *
        * @param string $domain domain name
        * @param array $attr
        * @return boolean
        */
        public static function setDomainAttr($domain, $attr) {
                $attr['domain'] = $domain;
                $attr['owner'] = self::$__accessKey;
        self::$__saeAdminDomainHandle->selectCollection("attr")->update(array("domain"=>"$domain"), array('$set', $attr));
        return true;
        }

    /**
    * Delete an domain
    *
    * @param string $bucket Bucket name
    * @param bool $force default false, means cannot delete when not empty domain.
    * @return boolean
    function deleteDomain($domain, $force = false) {
        if($force) {
            self::$__saeStorHandle->dropDB($domain);
        } else {
            $mgcoll = self::$__saeStorHandle->selectCollection($domain, "fs.files");
            if($mgcoll->findOne()) { $__errno = 116; return false; }
            self::$__saeStorHandle->dropDB($domain);
        }
        return self::$__saeAdminDomainHandle->selectCollection("attr")->remove(array("domain"=>"$domain"));
    }
    */

    /**
    * Write a File
    *
    * @param string $domain domain name
    * @param string $destfile destination file uri
    * @param string $srcfile source file location
    * @param array $attr file attributes
    * @return boolean
    */
    public static function writeFile($domain, $destfile, $srcfile, $attr=array()) {
        self::__getDomainHandle($domain);
        $attr['filename'] = "$destfile";
        self::$__saeStorDomainHandle->getGridFS()->storeFile($srcfile, $attr);

        return true;
    }

    /**
    * Write a file from a string
    *
    * @param string $domain domain name
    * @param string $destfile file uri
    * @param string $data content of dest file
    * @param array $attr file's attributes
    * @return boolean
    */
    public static function writeData($domain, $destfile, $data, $attr=array()) {
        self::__getDomainHandle($domain);
        $attr['filename'] = "$destfile";
        $gfs = self::$__saeStorDomainHandle->getGridFS();
        $gfs->remove(array("filename" => $destfile));
        $gfs->storeBytes($data, $attr);
        //$gfs->update(array("filename" => $destfile), $attr, true);
        //$gfs->storeBytes($data);

        return true;
    }

        /**
        * Write a file from a resource handle
        *
        * @param string $domain domain name
        * @param string $destfile file uri
        * @param resource $fd file handler
        * @param array $attr file's attributes
        * @return boolean
        */
    public static function writeFileHandler($domain, $destfile, $fd, $attr=array()) {
        $content = "";
        while (!feof($fd)) {
            $content .= fread($fd, 8192);
        }
        fclose($fd);
        $this->writeData($domain, $destfile, $content, $attr);
    }

        /**
        * Write a file from a uploaded file
        *
        * @param string $domain domain name
        * @param string $destfile file uri
        * @param string $uploadname the name of upload in form
        * @param array $attr file's attributes
        * @return boolean
        */
        public static function writeFileUploaded($domain, $destfile, $uploadname, $attr=array()) {
        self::__getDomainHandle($domain);
                $gfs = self::$__saeStorDomainHandle->getGridFS();
        $gfs->storeBytes($data, $attr);
        //$gfs->update(array("filename" => $destfile), $attr, true);
        $gfs->storeUpload($uploadname, $destfile);

                return true;
        }

    /**
    * Set attributes of one file
    *
    * @param string $domain Domain name
    * @param string $filename File to be set
    * @attr array $attr Attributes to be set to this file
    * @return true/false
    */
    public static function setFileAttr($domain, $filename, array $attr) {
        self::__getDomainHandle($domain);
        return self::$__saeStorDomainHandle->getGridFS()->update(array('filename'=>"$filename"),$attr);
    }

    /**
    * Read a file content
    *
    * @param string $domain Domain name
    * @param string $filename Filename in database
    * @local string $local Local Location to be wrote, optional.
    * @return mixed return content of this file if no $local, otherwise return true/false
    */
    public static function readFile($domain, $filename, $local = '') {
                self::__getDomainHandle($domain);
                $gfsfile = self::$__saeStorDomainHandle->getGridFS()->findOne(array('filename'=>"$filename"));
                if(!$gfsfile) return false;
                $data= $gfsfile->getBytes();

        if(!$local) return $data;

        return @file_put_contents($local, $data);
    }

    /**
    * Get attributes of a file
    *
    * @param string $domain Domain name
    * @param string $filename Filename in database
    * @param array $keys, optional arg for some attributes
    * @return array | false
    */
    public static function getFileAttr($domain, $filename, $keys=array()) {
        self::__getDomainHandle($domain);
        $gridfs = self::$__saeStorDomainHandle->getGridFS();

        if($keys) {
            return $gridfs->findOne(array('filename'=>"$filename"), $keys);
        }

        return $gridfs->find(array('filename'=>"$filename"));
    }

        /**
        * Get http url of a file
        *
        * @param string $domain Domain name
        * @param string $filename Filename in database
        * @return string | false
        */
    public static function getFileUrl($domain, $filename) {
        $basedomain = ".stor.sinaapp.com";
        return "http://$domain" . $basedomain . "/$filename";
        // return "http://" . $basedomain . "/domain/filename";
    }

    /**
    * Get filename lists
    * @param string $domain Domain name
    * @param string $presuf prefix or suffix search, abc* means prefix, *abc means suffix.default is prefix.
    * @limit integer $limit, default is 10, max is 100.
    * @return array | false
    */
    public static function getFileList($domain, $presuf, $limit=10) {
        self::__getDomainHandle($domain);
        $c = self::$__saeStorDomainHandle->selectCollection("fs.files");

        // determine prefix or suffix query
        $prefix = true;
        if($presuf[0] == "*") $prefix = false;
        // remove * from begin and end, then quote especial char
        $s = trim($presuf, "*");
        $s = preg_quote($s);

        if($prefix) $p = "^$s";
        else $p = "$s" . '$';
        $ss = new MongoRegex("/$p/");

        $cu = $c->find(array('filename'=>$ss), array("filename"));
        $result = array();
        while($cu->hasNext()) {
            $r = $cu->getNext();
            array_push($result, $r['filename']);
        }

        return $result;
    }

    /**
    * Delete a file
    *
    * @param string $domain Domain name
    * @param string $filename filename in databases
    * @return boolean
    public static function deleteFile($domain, $filename) {
        self::__getDomainHandle($domain);
                $gridfs = self::$__saeStorDomainHandle->getGridFS();
        return $gridfs->remove(array('filename'=>$filename));
    }
    */


    /**
    * Get MIME type for file
    *
    * @internal Used to get mime types
    * @param string &$file File path
    * @return string
    */
    public static function __getMimeType(&$file) {
        $type = false;
        // Fileinfo documentation says fileinfo_open() will use the
        // MAGIC env var for the magic file
        if (extension_loaded('fileinfo') && isset($_ENV['MAGIC']) &&
        ($finfo = finfo_open(FILEINFO_MIME, $_ENV['MAGIC'])) !== false) {
            if (($type = finfo_file($finfo, $file)) !== false) {
                // Remove the charset and grab the last content-type
                $type = explode(' ', str_replace('; charset=', ';charset=', $type));
                $type = array_pop($type);
                $type = explode(';', $type);
                $type = trim(array_shift($type));
            }
            finfo_close($finfo);

        // If anyone is still using mime_content_type()
        } elseif (function_exists('mime_content_type'))
            $type = trim(mime_content_type($file));

        if ($type !== false && strlen($type) > 0) return $type;

        // Otherwise do it the old fashioned way
        static $exts = array(
            'jpg' => 'image/jpeg', 'gif' => 'image/gif', 'png' => 'image/png',
            'tif' => 'image/tiff', 'tiff' => 'image/tiff', 'ico' => 'image/x-icon',
            'swf' => 'application/x-shockwave-flash', 'pdf' => 'application/pdf',
            'zip' => 'application/zip', 'gz' => 'application/x-gzip',
            'tar' => 'application/x-tar', 'bz' => 'application/x-bzip',
            'bz2' => 'application/x-bzip2', 'txt' => 'text/plain',
            'asc' => 'text/plain', 'htm' => 'text/html', 'html' => 'text/html',
            'css' => 'text/css', 'js' => 'text/javascript',
            'xml' => 'text/xml', 'xsl' => 'application/xsl+xml',
            'ogg' => 'application/ogg', 'mp3' => 'audio/mpeg', 'wav' => 'audio/x-wav',
            'avi' => 'video/x-msvideo', 'mpg' => 'video/mpeg', 'mpeg' => 'video/mpeg',
            'mov' => 'video/quicktime', 'flv' => 'video/x-flv', 'php' => 'text/x-php'
        );
        $ext = strtolower(pathInfo($file, PATHINFO_EXTENSION));
        return isset($exts[$ext]) ? $exts[$ext] : 'application/octet-stream';
    }

        private function __getHandle() {
                if(!self::$__saeStorHandle) {
                        self::$__saeStorHandle = new Mongo("stor.sae.sina.com.cn");
                        //$__saeStorHandle->connectUtil(self::$__accessKey, self::$__secretKey);
                        //self::$__saeStorHandle = MongaGridFS($domain);
                }
        }
    private function __getDomainHandle($domain) {
        if(!self::$__saeStorDomainHandle) {
            self::$__saeStorDomainHandle = self::$__saeStorHandle->selectDB($domain);
            //$mgdb = new MongoDB($__saeStorHandle, $domain)
            //self::$__saeStorDomainHandle = new MongoGFS($mgdb);
        }
    }
    private function __getAdminDomainHandle() {
        if(!self::$__saeAdminDomainHandle) {
            self::$__saeAdminDomainHandle = self::$__saeStorHandle->selectDB("admin");
        }
    }
    public function addDomainGroup($dom, $group) {
        self::$__saeAdminDomainHandle->selectCollection("attr")->update(array("domain"=>"$dom"), array('$push'=>array('group'=>"$group")));
    }
    public function delDomainGroup($dom, $group) {
        self::$__saeAdminDomainHandle->selectCollection("attr")->update(array("domain"=>"$dom"), array('$pull'=>array('group'=>"$group")));
    }
    public function setDomainDefGroup($dom, $group) {
        $groups = getDomainAttr($dom, array("group"));
        foreach ($groups['group'] AS $k=>$v) {
            if($v=="$group") unlink($groups['group'][$k]);
        }
        array_unshift($groups['group'], $group);
        setDomainAttr($dom, $groups);
    }

    public function getDomainDefGroup($dom) {
        $group = getDomainGroup($dom);
        return $group[0];
    }
    public function getDomainGroup($dom) {
        $groups = self::$__saeAdminDomainHandle->selectCollection("attr")->findOne(array("domain"=>"$dom"), array("group"));
        return $groups["group"];
    }

    public function getDomainDistribute($dom) {
        $dist = self::$__saeAdminDomainHandle->selectCollection("attr")->findOne(array("domain"=>"$dom"), array("distribution"));
        if(!$dist) return false;
        return $dist["distribution"];
    }
    /**
    *
    * @return array|false unreg,owner,group,other
    * $ret['owner'][read]=1, $ret[group][write]=0
    * 1    1 1      1 1       1 1
    * s    r w      r w       r w
    */
    public function getDomainPermission($dom) {
        $perm = self::$__saeAdminDomainHandle->selectCollection("attr")->findOne(array("domain"=>"$dom"), "permission");
        $ret = array();
        $ret['other']['write'] = $perm & 1;
        $ret['other']['read'] = ($perm>>1) & 1;
        $ret['group']['write'] = ($perm>>2) & 1;
        $ret['group']['read'] = ($perm>>3) & 1;
        $ret['owner']['write'] = ($perm>>4) & 1;
        $ret['owner']['read'] = ($perm>>5) & 1;
        $ret['unreg']['read'] = ($perm>>6) & 1;
        return $ret;
    }

}