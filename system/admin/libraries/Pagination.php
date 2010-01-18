<?php
/**
 * Pagination library.
 *
 * $Id$
 *
 * @package    Core
 * @author     Myqee Team
 * @copyright  (c) 2007-2008 Myqee Team
 * @license    http://myqee.com/license.html
 */
class Pagination_Core {
	public $sql_offset;
	protected $page=1;
	protected $allpage=0;
	protected $config = array(
		'pagestr'=>'',
		'uri_segment'=>'',
		'total_items'=>0,
		'items_per_page'=>20,
		'query_string'=>'',
	);

	public function __construct($config = array()){
		if (is_array($config))$this->config=array_merge($this->config,$config);
		
		//计算总页数
		$this->allpage = ceil($this->config['total_items']/$this->config['items_per_page']);

		//获取当前页码
		$uri = $_SERVER['REQUEST_URI'];
		if ($this->config['query_string']){
			$uriarr = explode('?',$uri,2);
			parse_str($uriarr[1], $get);
			
			$page = $get[$this->config['query_string']];
			
			$get[$this->config['query_string']] = '{{page}}';
			$uriarr[1] = str_replace('=%7B%7Bpage%7D%7D','={{page}}',http_build_query($get,null,'&'));

			$this->config['pagestr'] or $this->config['pagestr'] = $uriarr[0] .'?'. $uriarr[1];
		}else{
			if(!$this->config['uri_segment'])$this->config['uri_segment'] = Myqee::$controller_name;
			$uriarr = explode($this->config['uri_segment'],$uri,2);
			if (count($uriarr)==1){
				$uriarr[1] = $uriarr[0];
				$uriarr[1] = '';
			}else{
				$uriarr[1] = ltrim($uriarr[1],'/');
			}
			//文件后缀
			$url_suffix = Myqee::config('core.url_suffix');
			$suffix_len = strlen($url_suffix);
			if ($uriarr[1]){
				if ($url_suffix){
					$split_preg = $url_suffix .'|';
				}
				$pagearr = preg_split("/({$split_preg}\/|\\|\?)/",$uriarr[1],2);
				
				//获取中间分割的字符
				if ($count_2=strlen($pagearr[1])){
					$splitstr = substr($uriarr[1],strlen($pagearr[0]),-$count_2);
				}else{
					$splitstr = substr($uriarr[1],strlen($pagearr[0]));
				}
				$page = (int)$pagearr[0];
				$pagestr = '{{page}}' . $splitstr . $pagearr[1];
			}else{
				$pagestr = '{{page}}' . $url_suffix;
			}
			$this->config['pagestr'] or $this->config['pagestr'] = rtrim($uriarr[0] .$this->config['uri_segment'],'/') . '/' . $pagestr;
		}
		if($config['page'])$page=$config['page'];
		
		if ( $page>0 && $page <= $this->allpage ){
			$this->page = $page;
		}else{
			$this->page = 1;
		}
		
		$this->sql_offset = ($this->page-1)*$this->config['items_per_page'];
	}
	/*返回页码*/
	//$pageArray：是一个一维数组，将根据它的key用value替换掉网页地址里 [key] 的内容（左右分别有“[”和“]”）
	public function render(){
		$page=$this->page;
		$allpage=$this->allpage;
		$weburl = $this->config['pagestr'];
		$tmphtml='<div class="pageDiv"><table border="0" align="center" cellspacing="0" cellpadding="2" style="white-space:nowrap;"><tr>';
		if ($page>1){
			$tmphtml.='<td><a href="'.str_replace('{{page}}',1,$weburl).'">&laquo;首页</a></td><td><a href="'.str_replace('{{page}}',$page-1,$weburl).'">&laquo;上一页</a></td>';
		}else{
			$tmphtml.='<td><a class="nolink">&laquo;首页</a></td><td><a class="nolink">&laquo;上一页</a></td>';
		}
		if ($page>6 && $allpage-$page>5){
			$forstart=$page-5;
		}else if($allpage-$page<10 && $allpage>10){
			$forstart=$allpage-10;
		}else{
			$forstart=1;
		}
		$minnum=min($forstart+11,$allpage+$forstart);
		//$minnum = $forstart+11;
		for ($i=$forstart;$i<$minnum;$i++){
			if ($i<=$allpage){
				if ($page==$i){
					$tmphtml.='<td><a class="linknow"><b>'.$i.'</b></a></td>';
				}else{
					$tmphtml.='<td><a href="'.str_replace('{{page}}',$i,$weburl).'">'.$i.'</a></td>';
				}
			}else{
				$tmphtml.='<td><a class="nolink">'.$i.'</a></td>';
			}
		}
		if ($page==$allpage || $allpage==0){
			$tmphtml.='<td><a class="nolink">下一页&raquo;</a></td><td><a class="nolink">尾页&raquo</a></td>';
		}else{
			$tmphtml.='<td><a href="'.str_replace('{{page}}',$page+1,$weburl).'">下一页&raquo;</a></td><td><a href="'.str_replace('{{page}}',$allpage,$weburl).'">尾页&raquo;</a></td>';
		}
		$tmphtml.='</tr></table></div>';
	
		if (is_array($pageArray)){
			foreach ($pageArray as $key=>$value){
				$tmphtml=str_replace('{{'.$key.'}}',$value,$tmphtml);
			}
		}
		return $tmphtml;
	}

	public function sql_offset(){
		return $this->sql_offset;
	}
}