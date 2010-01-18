<?php defined('MYQEEPATH') or die('No direct script access.');

//加载视图类
Myqee::auto_load('View');


/**
 * Template library.
 *
 * $Id$
 *
 * @package    Core
 * @author     Myqee Team
 * @copyright  (c) 2007-2008 Myqee Team
 * @license    http://myqee.com/license.html
 */
class Template_Core{
	
	/**
	 * 模板引擎解析接口
	 * 用于给其它(例如smatry)模板引擎扩展，只要在生成解析文件时Template::parse_api($compiled_content)即可
	 *
	 * @param string $content 模板内容
	 * @return string $content 替换后的内容
	 */
	public static function compile_api($content){
		return preg_replace(
		array(
			"#{myqee\.block\(([a-z0-9_]+),([0-9]+)\)}#is",
		),array(
			'<?php Template::block("$1",$2);?>'."\r\n",
		),$content);
	}
	
	//######################################################
	
	protected static $viewcreate;
	public static function block($type='index',$no=0,$isrender=true){
		if (!Template::$viewcreate)Template::$viewcreate = new _Template_create();
		
		Template::$viewcreate -> block($type,$no,$isrender);
	}
	
	//######################################################
	
	
	
	/**
	 * 模板所在模板组
	 *
	 * @var string $group
	 */
	protected $_group = 'default';
	
	/**
	 * 模板内容
	 *
	 * @var string $contents
	 */
	protected $_contents = '';
	
	/**
	 * 传入模板值
	 *
	 * @var array $vars
	 */
	protected $_vars = array();
	
	/**
	 * 模板文件名(完整路径)
	 *
	 * @var string $templage_file
	 */
	protected $_templage_file;
	
	/**
	 * 模板名称(带后缀)
	 *
	 * @var string $tplname
	 */
	protected $_tplname;
	
	/**
	 * 是否临时模板
	 *
	 * @var boolean $_istemptpl
	 */
	protected $_istemptpl = false;
	
	public function __construct($tplname,$group=null){
		$this -> load($tplname,$group);
	}
	
	/**
	 * 加载模板文件
	 *
	 * @param string $tplname 模板名称
	 * @param string $group 模板组名称，若为true则系统认为是临时模板，$tplname所传的内容代表模板内容
	 * @return string 模板文件内容
	 */
	public function load($tplname,$group=null){
		if (!$tplname)return false;
		if ($group===true){
			$this -> _contents = $tplname;
			$this -> _group = '__~temptpl__'.time().rand(0,9999999);
			$this -> _tplname = 'temp.tpl';
			$this -> _istemptpl = true;
			
			//删除意外错误造成的垃圾文件
			foreach (glob(MYAPPPATH . 'data/template/__~temptpl__*') as $filename) {
			    Tools::remove_dir( $filename);
			}
		}else{
			$group or $group = Myqee::config('template.default');
			$this -> _group = $group;
			$this -> _tplname = $tplname;
			$this -> _groupset = Myqee::config('template.group.'.$this -> _group);
			if (!$this -> _groupset){
				Myqee::show_500('Template Group Set Not Found.');
			}
			$this -> _templage_file = MYAPPPATH .'views/'.$this -> _group.'/'.$this -> _tplname;
			
			if (!is_file($this -> _templage_file)){
				Myqee::show_500('Template Not Found.');
			}
			
			$fileinfo = explode('.',$this -> _tplname);
			$fileext = $fileinfo[count($fileinfo)-1];
			if (!in_array('.'.$fileext,explode('|',$this -> _groupset['allsuffix']))){
				Myqee::show_500('Template Suffix Not Allow.');
			}
			
			$this -> _contents = file_get_contents( $this -> _templage_file );
		}
		
		return true;
	}
	
	/**
	 * 编译模板
	 *
	 * @return boolean
	 */
	public function compile(){
		if (!$this -> _tplname)return false;
		$this -> _parse();
		return $this -> _write();
	}
	
	public function set($tpl_var,$value=null){
		if (is_array($tpl_var)){
			foreach ($tpl_var as $k=>$value){
				if ($tpl_var != '')
					$this -> _vars[$k] = $value;
			}
		}else{
			if ($tpl_var != '')
				$this -> _vars[$tpl_var] = $value;
		}
	}
	
	public function render($print = FALSE,$data=null){
		$file = $this -> _get_phpfile();
		if (!is_file($file) || fileatime($this -> _templage_file)>fileatime($file) ){
			//模板有修改即重新编译
			//$this -> compile();
		}
		$this -> compile();
		
		if ($data && is_array($data)){
			$this -> set($data);
		}
		
		//加载文件
		$templage = new _Template_create();
		
		$outdata = $templage -> _myqee_load_view( $file, $this -> _vars );
		
		if ($print){
			echo $outdata;
			$outdata = true;
		}
		
		if ($this -> _istemptpl == true){
    		//删除临时编译的文件夹
    		Tools::remove_dir( $this -> _get_phpfilepath() );
    	}
		return $outdata;
	}
	
	/**
	 * 写入文件
	 *
	 * @return boolean
	 */
	protected function _write(){
		if (!$this -> _tplname)return false;
		$filepath = $this -> _get_phpfilepath();
		if (!is_dir($filepath)){
			Tools::create_dir($filepath);
		}
		
		return file_put_contents($this->_get_phpfile(),'<?php defined(\'MYQEEPATH\') or die(\'No direct script access.\');?>' . $this -> _contents );
	}

	protected function _get_phpfile(){
		return $this->_get_phpfilepath() . md5( $this -> _tplname ). EXT;
	}
	
	protected function _get_phpfilepath(){
		return MYAPPPATH . 'data/template/' . $this -> _group . '/';
	}
	
	/**
	 * 解析模板字符
	 *
	 * @return void
	 */
	protected function _parse(){
		//移去特殊标签
		$randstr = '{{{__MYQEE_PhP_LEFTTAG__'.rand(100000000,999999999).'__}}}';
		$this -> _contents = str_replace(array('<?','?>',$randstr),array($randstr,'<?php echo \'?>\';?>','<?php echo \'<?\';?>'),$this -> _contents);
		
		//include
		if ( !$this -> _istemptpl && preg_match_all( '/<!--(?:\s*)inc(?:lude)?(?:\s*)(?:"|\'*)(.+?)(?:"|\'*)(?:\s*)-->/i',$this -> _contents, $rs ) )
		{
			static $hasloadfile;
			foreach( $rs[1] as $key => $name )
			{
				if (!isset($hasloadfile[$this -> _group.'/'.$name])){
					//防止重复加载陷入死循环
					$hasloadfile[$this -> _group.'/'.$name] = true;
					
					//解析include的模板
					$tpl = new Template($name,$this -> _group);
					$tpl -> compile();
					unset($tpl);
				}
				
				$this -> _contents = str_replace( $rs[0][$key], '<?php include(dirname(__FILE__).DIRECTORY_SEPARATOR.\''.md5($name).'\'.EXT);?>', $this -> _contents );
			}
		}
		
		
		//foreach
		$this -> _contents = preg_replace('#<!--{(?:loop|foreach)\s+(\$[a-zA-Z0-9{}._]+)\s+as\s+(\$[a-zA-Z0-9{}._]+)(?:(?:\s*)=>(?:\s*)(\$[a-zA-Z0-9{}._]+))?(?:\s*)}-->#e', "\$this->_set_foreach('\\1','\\2','\\3')", $this -> _contents);
		$this -> _contents = str_replace(array('<!--{/foreach}-->','<!--{/loop}-->'),"<?php }}?>",$this -> _contents);
		
		
		//for
		$this -> _contents = preg_replace('#<!--{for\s+(.*)}-->#Ue', "'<?php for('.\$this->_parse_vars('\\1',1).'){?>'", $this -> _contents);
		
		
		//if/elseif/else
		$this -> _contents = preg_replace('#<!--{if\s+([a-zA-Z0-9${}._=;&\|!<>+-\s]+)(?:\s*)}-->#eU', "'<?php if('.\$this->_parse_vars('\\1',1).'){?>'", $this -> _contents);
		$this -> _contents = preg_replace("/<!--{elseif\s+(.+?)\}-->/e", "'<?php }elseif('.\$this->_parse_vars('\\1',1).'){?>'", $this -> _contents);
		$this -> _contents = str_replace('<!--{else}-->', '<?php } else { ?>', $this -> _contents);
		
		
		//递增递减
		$this -> _contents = preg_replace('#<!--{\$([a-zA-Z0-9._]+)(\+\+|--)}-->#e',"\$this->_parse_vars('<?php {\$\\1}++;?>')",$this -> _contents);
		//赋值
		$this -> _contents = preg_replace('#<!--{\$([a-zA-Z0-9._]+)(?:\s*)=(?:\s*)(.*)}-->#Ue',"\$this->_parse_vars('<?php \$\\1=\\2;?>',1)",$this -> _contents);
		
		//lang
		$this -> _contents = preg_replace('/<!--{lang\s+([a-z0-9\/._]+)}-->/','<?php echo Myqee::lang(\'$1\');?>',$this -> _contents);
		
		$this -> _contents = str_replace(array('<!--{/for}-->','<!--{/if}-->'),"<?php }?>",$this -> _contents);
		
		
		$this -> _contents = $this -> _parse_vars($this -> _contents,2,true,false);
		$this -> _contents = $this -> _parse_vars($this -> _contents,0,true,false);
		
		$this -> _contents = Template::compile_api( $this -> _contents );
	}
	
	
	protected function _set_foreach($v1,$v2,$v3){
		$v1 = $this -> _parse_vars($v1,1);
		$v2 = $this -> _parse_vars($v2,1);
		$v3 = $this -> _parse_vars($v3,1);
		return "<?php if(isset($v1)&&is_array($v1)){foreach($v1 as $v2".($v3?' => '.$v3:'')."){?>";
	}
	
	protected function _parse_fun($value){
		preg_match_all("#([\$a-zA-Z0-9_:\->]+)(?:\s*)\((.*)\)#Us",$value,$match);
		static $allowfun;
		if (!$allowfun){
			$allowfun = array('count','time','date','substr','addcslashes','addslashes');
		}
		$c=count($match[0]);
		for($i=0;$i<$c;$i++){
			if (!in_array($match[1][$i],$allowfun)){
				$value = str_replace($match[0][$i],'NULL',$value);
			}else{
				$value = str_replace($match[2][$i],$this->_parse_vars($match[2][$i],1),$value);
			}
		}
		return $value;
	}
	
	protected function _parse_vars($value,$type=0,$echo=false,$chkfun=true){
		if ($type==1){
			$m = '/(\$[0-9a-zA-Z\._]+)/';
		}elseif($type==2){
			$m = '/{(\$[0-9a-zA-Z\._]+)}#(.*)#/';
		}else{
			$m = '/{(\$[0-9a-zA-Z\._]+)}/';
		}
		if($chkfun && preg_match("#([\$a-zA-Z0-9_:\->]+)(?:\s*)\(.*\)#Us",$value)){
			//函数
			$value = $this -> _parse_fun($value);
		}
		if ( preg_match_all($m,$value,$match ) )
		{
			$mc = count($match[0]);
			for($i=0;$i<$mc;$i++){
				$arr = explode('.',$match[1][$i]);
				$c = count($arr);
				$tmpstr = $arr[0];
				for ($ii=1;$ii<$c;$ii++){
					$tmpstr .= '['.var_export($arr[$ii],true).']';
				}
				if ($match[2][$i]){
					if (is_numeric($match[2][$i])){
						$tmpstr = 'Tools::substr('.$tmpstr.',0,'.$match[2][$i].')';
					}else{
						$tmpstr = 'date("'.$match[2][$i].'",'.$tmpstr.')';
					}
				}
				if ($echo==true)$tmpstr = '<?php echo '.$tmpstr.';?>';
				$value = str_replace($match[0][$i],$tmpstr,$value);
			}
		}
		return $value;
	}
}


class _Template_create extends _myqee_view_create{
	public function block($type='index',$no=0,$isrender=true){
		return parent::block($type,$no,$isrender);
	}
	
	public function _myqee_load_view($__myqee_view_filename_,$__myqee_input_data_){
		return parent::_myqee_load_view($__myqee_view_filename_,$__myqee_input_data_);
	}
}