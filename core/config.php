<?php
$config['cookie'] = array
(
    'domain'   => '',
    'prefix'   => '',
    'path'     => '/',
    'expire'   => 0,
    'secure'   => false,
    'httponly' => false,
);



$config['session'] = array
(
    'name'           => 'SID',    // SessionID名称
    'expiration'     => 0,
    'gc_probability' => 0,
    'check_string'   => '$@de23#$%@.dG2.p4Ad',    // 校验字符串
);

/*
CREATE TABLE `cache` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `key` char(32) NOT NULL,
    `key_str` varchar(255) NOT NULL,
    `value` longtext,
    `expire_time` int(10) unsigned NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `key` (`key`),
    KEY `expire_time` (`expire_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
*/
$config['cache/database']['default'] = array
(
    'database'  => 'default',
    'tablename' => 'cache',
);


/**
 * 分页配置
 *
 * 参数            |  说明
 * ---------------|------------------------
 * source, key    | 这2个参数配合确定分页数获取源
 * auto_hide      | 设置 true 表示自动隐藏<=1个分页的URL的HTML（输出为空html）
 * total_items    | 告诉程序总共有多少项目，以便计算分页数
 * items_per_page | 每页显示的数目
 *
 *
 * source 有3个值为： query_string | route | default
 *
 * * 当 source = query_string 时，key建议为page，这样分页就是类似 `/uri/?page=1`  `/uri/?page=2` 这样形式
 * * 当 source = default 时，key建议为0，这样分页就类似 `/uri/2/`   `/uri/3/`  这样
 * * 当 source = route 时，表示使用路由配置，此时的key为路由设置中的对应的key
 *
 * @var array
 */
$config['pagination']['default'] = array
(
    'source'         => 'default',
    'key'            => '0',
    'total_items'    => 0,
    'items_per_page' => 10,
    'view'           => 'pagination/basic',     // 视图views中的文件
    'auto_hide'      => true,
);



/**
 * 日志配置
 *
 * 日志存放目录在index.php中配置
 *
 * format 可以有的参数包括：
 *
 *   :time     当前时间
 *   :url      请求的URL
 *   :msg      日志信息
 *   :type     日志类型
 *   :host     服务器
 *   :port     端口
 *   :ip       请求的IP
 *   :agent    客户端信息
 *   :referer  来源页面
 *
 * @var array
 */
$config['log'] = array
(
    'use'    => true,            // 是否启用记录日志，默认启用
    'format' => ':time - :host::port - :url - :msg',
);


/**
 * 文件保存同步模式
 *
 * 可选参数 default|rsync|none
 * default : 全部轮询同步
 * rsync   : 到主服务器上执行操作，然后由系统rsync进行同步更新
 * none    : 表示不同步操作
 *
 * @var string
 */
$config['file_sync_mode'] = 'default';


/**
 * 系统内部调用接口密钥，留空则系统会使用全部core配置和database序列化拼接后md5产生
 *
 * @var string
*/
$config['system_exec_key'] = '';


/**
 * WEB服务的服务器列表，留空则禁用同步功能（比如只有1台web服务器时请禁用此功能）
 *
 * 配置服务器后，可以实现服务器上data目录的文件同步功能，同步逻辑通过本系统完成，如果已经配置了data目录的sync同步机制，只需要配置1个主服务器即可
 * 可通过 HttpCall::sync_exec('test/abc','arg1','arg2','arg3'); 实现在所有服务器上各自运行一遍
 *
 *     //可以是内网IP，确保服务器之间可以相互访问到，端口请确保指定到apache/IIS/nginx等端口上
 *     array
 *     (
 *         '192.168.1.1',        //80端口可省略:80
 *         '192.168.1.2:81',
 *         '192.168.1.3:81',
 *     )
 *
 * @var array
 */
$config['web_server_list'] = array
(

);


/**
 * A list of mime types. Our list is generally more complete and accurate than
* the operating system MIME list.
*
* If there are any missing options, please create a ticket on our issue tracker,
* expected MIME type, as well as any additional information you can provide.
*/
$config['mimes'] = array
(
    '323'      => array('text/h323'),
    '7z'       => array('application/x-7z-compressed'),
    'abw'      => array('application/x-abiword'),
    'acx'      => array('application/internet-property-stream'),
    'ai'       => array('application/postscript'),
    'aif'      => array('audio/x-aiff'),
    'aifc'     => array('audio/x-aiff'),
    'aiff'     => array('audio/x-aiff'),
    'amf'      => array('application/x-amf'),
    'asf'      => array('video/x-ms-asf'),
    'asr'      => array('video/x-ms-asf'),
    'asx'      => array('video/x-ms-asf'),
    'atom'     => array('application/atom+xml'),
    'avi'      => array('video/avi', 'video/msvideo', 'video/x-msvideo'),
    'bin'      => array('application/octet-stream','application/macbinary'),
    'bmp'      => array('image/bmp'),
    'c'        => array('text/x-csrc'),
    'c++'      => array('text/x-c++src'),
    'cab'      => array('application/x-cab'),
    'cc'       => array('text/x-c++src'),
    'cda'      => array('application/x-cdf'),
    'class'    => array('application/octet-stream'),
    'cpp'      => array('text/x-c++src'),
    'cpt'      => array('application/mac-compactpro'),
    'csh'      => array('text/x-csh'),
    'css'      => array('text/css'),
    'csv'      => array('text/x-comma-separated-values', 'application/vnd.ms-excel', 'text/comma-separated-values', 'text/csv'),
    'dbk'      => array('application/docbook+xml'),
    'dcr'      => array('application/x-director'),
    'deb'      => array('application/x-debian-package'),
    'diff'     => array('text/x-diff'),
    'dir'      => array('application/x-director'),
    'divx'     => array('video/divx'),
    'dll'      => array('application/octet-stream', 'application/x-msdos-program'),
    'dmg'      => array('application/x-apple-diskimage'),
    'dms'      => array('application/octet-stream'),
    'doc'      => array('application/msword'),
    'docx'     => array('application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
    'dvi'      => array('application/x-dvi'),
    'dxr'      => array('application/x-director'),
    'eml'      => array('message/rfc822'),
    'eps'      => array('application/postscript'),
    'evy'      => array('application/envoy'),
    'exe'      => array('application/x-msdos-program', 'application/octet-stream'),
    'fla'      => array('application/octet-stream'),
    'flac'     => array('application/x-flac'),
    'flc'      => array('video/flc'),
    'fli'      => array('video/fli'),
    'flv'      => array('video/x-flv'),
    'gif'      => array('image/gif'),
    'gtar'     => array('application/x-gtar'),
    'gz'       => array('application/x-gzip'),
    'h'        => array('text/x-chdr'),
    'h++'      => array('text/x-c++hdr'),
    'hh'       => array('text/x-c++hdr'),
    'hpp'      => array('text/x-c++hdr'),
    'hqx'      => array('application/mac-binhex40'),
    'hs'       => array('text/x-haskell'),
    'htm'      => array('text/html'),
    'html'     => array('text/html'),
    'ico'      => array('image/x-icon'),
    'ics'      => array('text/calendar'),
    'iii'      => array('application/x-iphone'),
    'ins'      => array('application/x-internet-signup'),
    'iso'      => array('application/x-iso9660-image'),
    'isp'      => array('application/x-internet-signup'),
    'jar'      => array('application/java-archive'),
    'java'     => array('application/x-java-applet'),
    'jpe'      => array('image/jpeg', 'image/pjpeg'),
    'jpeg'     => array('image/jpeg', 'image/pjpeg'),
    'jpg'      => array('image/jpeg', 'image/pjpeg'),
    'js'       => array('application/x-javascript'),
    'json'     => array('application/json'),
    'latex'    => array('application/x-latex'),
    'lha'      => array('application/octet-stream'),
    'log'      => array('text/plain', 'text/x-log'),
    'lzh'      => array('application/octet-stream'),
    'm4a'      => array('audio/mpeg'),
    'm4p'      => array('video/mp4v-es'),
    'm4v'      => array('video/mp4'),
    'man'      => array('application/x-troff-man'),
    'mdb'      => array('application/x-msaccess'),
    'midi'     => array('audio/midi'),
    'mid'      => array('audio/midi'),
    'mif'      => array('application/vnd.mif'),
    'mka'      => array('audio/x-matroska'),
    'mkv'      => array('video/x-matroska'),
    'mov'      => array('video/quicktime'),
    'movie'    => array('video/x-sgi-movie'),
    'mp2'      => array('audio/mpeg'),
    'mp3'      => array('audio/mpeg'),
    'mp4'      => array('application/mp4','audio/mp4','video/mp4'),
    'mpa'      => array('video/mpeg'),
    'mpe'      => array('video/mpeg'),
    'mpeg'     => array('video/mpeg'),
    'mpg'      => array('video/mpeg'),
    'mpg4'     => array('video/mp4'),
    'mpga'     => array('audio/mpeg'),
    'mpp'      => array('application/vnd.ms-project'),
    'mpv'      => array('video/x-matroska'),
    'mpv2'     => array('video/mpeg'),
    'ms'       => array('application/x-troff-ms'),
    'msg'      => array('application/msoutlook','application/x-msg'),
    'msi'      => array('application/x-msi'),
    'nws'      => array('message/rfc822'),
    'oda'      => array('application/oda'),
    'odb'      => array('application/vnd.oasis.opendocument.database'),
    'odc'      => array('application/vnd.oasis.opendocument.chart'),
    'odf'      => array('application/vnd.oasis.opendocument.forumla'),
    'odg'      => array('application/vnd.oasis.opendocument.graphics'),
    'odi'      => array('application/vnd.oasis.opendocument.image'),
    'odm'      => array('application/vnd.oasis.opendocument.text-master'),
    'odp'      => array('application/vnd.oasis.opendocument.presentation'),
    'ods'      => array('application/vnd.oasis.opendocument.spreadsheet'),
    'odt'      => array('application/vnd.oasis.opendocument.text'),
    'oga'      => array('audio/ogg'),
    'ogg'      => array('application/ogg'),
    'ogv'      => array('video/ogg'),
    'otg'      => array('application/vnd.oasis.opendocument.graphics-template'),
    'oth'      => array('application/vnd.oasis.opendocument.web'),
    'otp'      => array('application/vnd.oasis.opendocument.presentation-template'),
    'ots'      => array('application/vnd.oasis.opendocument.spreadsheet-template'),
    'ott'      => array('application/vnd.oasis.opendocument.template'),
    'p'        => array('text/x-pascal'),
    'pas'      => array('text/x-pascal'),
    'patch'    => array('text/x-diff'),
    'pbm'      => array('image/x-portable-bitmap'),
    'pdf'      => array('application/pdf', 'application/x-download'),
    'php'      => array('application/x-httpd-php'),
    'php3'     => array('application/x-httpd-php'),
    'php4'     => array('application/x-httpd-php'),
    'php5'     => array('application/x-httpd-php'),
    'phps'     => array('application/x-httpd-php-source'),
    'phtml'    => array('application/x-httpd-php'),
    'pl'       => array('text/x-perl'),
    'pm'       => array('text/x-perl'),
    'png'      => array('image/png', 'image/x-png'),
    'po'       => array('text/x-gettext-translation'),
    'pot'      => array('application/vnd.ms-powerpoint'),
    'pps'      => array('application/vnd.ms-powerpoint'),
    'ppt'      => array('application/powerpoint'),
    'pptx'     => array('application/vnd.openxmlformats-officedocument.presentationml.presentation'),
    'ps'       => array('application/postscript'),
    'psd'      => array('application/x-photoshop', 'image/x-photoshop'),
    'pub'      => array('application/x-mspublisher'),
    'py'       => array('text/x-python'),
    'qt'       => array('video/quicktime'),
    'ra'       => array('audio/x-realaudio'),
    'ram'      => array('audio/x-realaudio', 'audio/x-pn-realaudio'),
    'rar'      => array('application/rar'),
    'rgb'      => array('image/x-rgb'),
    'rm'       => array('audio/x-pn-realaudio'),
    'rpm'      => array('audio/x-pn-realaudio-plugin', 'application/x-redhat-package-manager'),
    'rss'      => array('application/rss+xml'),
    'rtf'      => array('text/rtf'),
    'rtx'      => array('text/richtext'),
    'rv'       => array('video/vnd.rn-realvideo'),
    'sea'      => array('application/octet-stream'),
    'sh'       => array('text/x-sh'),
    'shtml'    => array('text/html'),
    'sit'      => array('application/x-stuffit'),
    'smi'      => array('application/smil'),
    'smil'     => array('application/smil'),
    'so'       => array('application/octet-stream'),
    'src'      => array('application/x-wais-source'),
    'svg'      => array('image/svg+xml'),
    'swf'      => array('application/x-shockwave-flash'),
    't'        => array('application/x-troff'),
    'tar'      => array('application/x-tar'),
    'tcl'      => array('text/x-tcl'),
    'tex'      => array('application/x-tex'),
    'text'     => array('text/plain'),
    'texti'    => array('application/x-texinfo'),
    'textinfo' => array('application/x-texinfo'),
    'tgz'      => array('application/x-tar'),
    'tif'      => array('image/tiff'),
    'tiff'     => array('image/tiff'),
    'torrent'  => array('application/x-bittorrent'),
    'tr'       => array('application/x-troff'),
    'tsv'      => array('text/tab-separated-values'),
    'txt'      => array('text/plain'),
    'wav'      => array('audio/x-wav'),
    'wax'      => array('audio/x-ms-wax'),
    'wbxml'    => array('application/wbxml'),
    'webm'     => array('video/webm'),
    'wm'       => array('video/x-ms-wm'),
    'wma'      => array('audio/x-ms-wma'),
    'wmd'      => array('application/x-ms-wmd'),
    'wmlc'     => array('application/wmlc'),
    'wmv'      => array('video/x-ms-wmv', 'application/octet-stream'),
    'wmx'      => array('video/x-ms-wmx'),
    'wmz'      => array('application/x-ms-wmz'),
    'word'     => array('application/msword', 'application/octet-stream'),
    'wp5'      => array('application/wordperfect5.1'),
    'wpd'      => array('application/vnd.wordperfect'),
    'wvx'      => array('video/x-ms-wvx'),
    'xbm'      => array('image/x-xbitmap'),
    'xcf'      => array('image/xcf'),
    'xhtml'    => array('application/xhtml+xml'),
    'xht'      => array('application/xhtml+xml'),
    'xl'       => array('application/excel', 'application/vnd.ms-excel'),
    'xla'      => array('application/excel', 'application/vnd.ms-excel'),
    'xlc'      => array('application/excel', 'application/vnd.ms-excel'),
    'xlm'      => array('application/excel', 'application/vnd.ms-excel'),
    'xls'      => array('application/excel', 'application/vnd.ms-excel'),
    'xlsx'     => array('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
    'xlt'      => array('application/excel', 'application/vnd.ms-excel'),
    'xml'      => array('text/xml', 'application/xml'),
    'xof'      => array('x-world/x-vrml'),
    'xpm'      => array('image/x-xpixmap'),
    'xsl'      => array('text/xml'),
    'xvid'     => array('video/x-xvid'),
    'xwd'      => array('image/x-xwindowdump'),
    'z'        => array('application/x-compress'),
    'zip'      => array('application/x-zip', 'application/zip', 'application/x-zip-compressed'),
);
