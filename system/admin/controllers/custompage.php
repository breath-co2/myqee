<?php
class Custompage_Controller_Core extends Controller {
	public function __construct() {
		parent::__construct ();
	}
	
	protected function _chkadmin() {
		Passport::chkadmin();
	}
	
	public function index($page = 1, $catename = false) {
		$this->_chkadmin ();
		Passport::checkallow ( 'info.custompage' );
		$per = 20;
		$view = new View ( 'admin/custompage_list' );
		
		$this->db = Database::instance ();
		$listwhere = array ('id>' => 0 );
		if ( !empty($catename) && is_string ( $catename ) ) {
			$listwhere ['cate'] = $catename;
			$view->set ( 'cate', htmlspecialchars ( $catename ) );
		}
		$num = $this->db->where ( $listwhere )->count_records ( '[custompage]' );
		
		$this->pagination = new Pagination ( array ('uri_segment' => 'index', 'total_items' => $num, 'items_per_page' => $per ) );
		
		$view->set ( 'list', $this->db->where ( $listwhere )->limit ( $per, $this->pagination->sql_offset () )->orderby ( 'id', 'DESC' )->getwhere ( '[custompage]' )->result_array ( FALSE ) );
		$view->set ( 'page', $this->pagination->render ( 'digg' ) );
		$view->render ( TRUE );
	}
	/**
	 * 添加自定义页面
	 */
	public function add() {
		$this->_chkadmin ();
		Passport::checkallow ( 'info.custompageadd' );
		$this->edit ( 0 );
	}
	/**
	 * 修改自定义页面
	 */
	public function edit($id) {
		$this->_chkadmin ();
		Passport::checkallow ( 'info.custompageedit' );
		$view = new View ( 'admin/custompage_edit' );
		$id = ( int ) $id;
		if ($id < 0) {
			MyqeeCMS::show_error ( '参数错误!', true );
		}
		$this->db = Database::instance ();
		$page_info = $this->db->getwhere ( '[custompage]', array ('id' => $id ) )->result_array ( FALSE );
		$page_info = $page_info [0];
		$adminmodel = new Admin_Model ( ); //load models
		$result = $this->db->select ( 'cate' )->from ( '[custompage]' )->groupby ( 'cate' )->get ()->result_array ( FALSE );
		$pagecate = array ();
		foreach ( $result as $item ) {
			$pagecate [$item ['cate']] = $item ['cate'];
		}
		$view->pagecate = $pagecate;
		
		$result = $this->db->select ( 'filepath' )->from ( '[custompage]' )->groupby ( 'filepath' )->get ()->result_array ( FALSE );
		$filepatharr = array ();
		foreach ( $result as $item ) {
			$filepatharr [$item ['filepath']] = $item ['filepath'];
		}
		if ($id > 0) {
			$paramarr = ( array ) unserialize ( $page_info ['param'] );
		}else{
			$paramarr = NULL;
		}
		$view->paramarr = Tools::json_encode($paramarr);
		$view->filepatharr = $filepatharr;
		$view->custompage = $page_info;
		$view->set ( 'tplarray', $adminmodel->get_alltemplate ( 'page' ) ); //list template
		$view->render ( TRUE );
	}
	
	
	
	public function save($id = 0) {
		$this->_chkadmin ();
		$id = ( int ) $id;
		if ($id > 0) {
			Passport::checkallow ( 'info.custompageedit' ,null,true);
		} else {
			Passport::checkallow ( 'info.custompageadd' ,null,true);
		}
		$post = $_POST ['custompage'];
		$post ['tplid'] = ( int ) $post ['tplid'];
		$tplid = $post ['tplid'];
		if (empty ( $post['pagename'] )) {
			MyqeeCMS::show_error ( '页面名称不能为空！', true );
		}
		if (empty ( $post['pagetitle'] )) {
			$post['pagetitle'] = $post['pagename'];
		}
		$post ['cate'] = trim ( $post ['cate'] );
		if (empty ( $post ['cate'] )) {
			$post ['cate'] = '默认';
		}
		$post ['filepath'] = trim ( $post ['filepath'], '/. ' ); //将两边的空格，斜线，.清除掉
		if (! empty ( $post ['filepath'] ) && ! preg_match ( "/^[0-9a-zA-Z_\/]+$/", $post ['filepath'] )) {
			MyqeeCMS::show_error ( '文件路径只允许允许“数字、英文、下划线、斜线！', true );
		}
		if (strripos ( $post ['filepath'], '/' ) == strlen ( $post ['filepath'] ) - 1) {
			$post ['filepath'] = substr ( $post ['filepath'], 0, strlen ( $post ['filepath'] ) - 1 );
		}
		if (empty ( $post ['filename'] ) || ! preg_match ( "/^[0-9a-zA-Z_,]+$/", $post ['filename'] )) {
			MyqeeCMS::show_error ( '文件名称只允许允许“数字、英文、下划线、逗号”且不能空！', true );
		}
		$post ['filename_suffix'] = in_array ( $post ['filename_suffix'], array ('.html', '.htm', '.js', '.css', '.txt' ) ) ? $post ['filename_suffix'] : '.html';
		
		$this->db = Database::instance ();
		$post ['pagename'] = Tools::formatstr ( $post ['pagename'] );
		$data = array (
			'pagename' => $post ['pagename'],
			'pagetitle' => $post ['pagetitle'], 
			'keyword' => $post ['keyword'], 
			'pagedesc' => $post ['pagedesc'], 
			'cate' => Tools::formatstr ( $post ['cate'] ), 
			'filepath' => Tools::formatstr ( $post ['filepath'] ), 
			'content' => $post ['content'], 
			'filename' => $post ['filename'], 
			'filename_suffix' => $post ['filename_suffix'], 
			'createtime' => $_SERVER ['REQUEST_TIME'],
			'edit_type' => $post['edit_type']==1?1:0,
			'istpl' => $post['istpl']==1?1:0,
		);
		
		if ($data['istpl']) {
			$data ['tplid'] = (int)$post['tplid'];
		}
		
		$data ['isuse'] = empty ( $post ['isuse'] ) ? 0 : 1;
		$data ['title_flag'] = preg_replace ( "/[^a-z0-9_]/i", '', $post ['title_flag'] );
		$data ['title_flag'] or $data ['title_flag'] = 'title';
		
		$data ['keywords_flag'] = preg_replace ( "/[^a-z0-9_]/i", '', $post ['keywords_flag'] );
		$data ['keywords_flag'] or $data ['keywords_flag'] = 'keywords';
		
		$data ['pagedesc_flag'] = preg_replace ( "/[^a-z0-9_]/i", '', $post ['pagedesc_flag'] );
		$data ['pagedesc_flag'] or $data ['pagedesc_flag'] = 'description';
		
		$data ['content_flag'] = preg_replace ( "/[^a-z0-9_]/i", '', $post ['content_flag'] );
		$data ['content_flag'] or $data ['content_flag'] = 'content';
		
		//自定义参数
		$param_config = array();
		if (is_array($post['param_flag']) && $count_flag = count($post['param_flag'])){
			for ($i=0;$i<$count_flag;$i++){
				$post['param_flag'][$i] = trim($post['param_flag'][$i]);
				if (!empty($post['param_flag'][$i])){
					if (preg_match("/^[a-z][a-z0-9_]*$/i",$post['param_flag'][$i]) && !in_array($post['param_flag'][$i],array('title','keywords','description','content'))){
						$param_config[$post['param_flag'][$i]] = array(
							'flag' => $post['param_flag'][$i],
							'name' => $post['param_name'][$i],
							'value' => $post['param_value'][$i],
						);
					}else{
						MyqeeCMS::show_error('自定义参数存在不符合条件的“替换标签”，请修改！',true);
					}
				}
			}
		}
		
		$data ['param'] = serialize ( $param_config );
		//检测是否已经存在的文件
		$chkwhere = array ('filepath' => $post ['filepath'], 'filename' => $post ['filename'], 'filename_suffix' => $post ['filename_suffix'] );
		if ($id > 0)
			$chkwhere ['id!='] = $id;
		$chkpage = $this->db->getwhere ( '[custompage]', $chkwhere )->result_array ( FALSE );
		$chkpage = $chkpage [0];
		
		if ($chkpage)
			MyqeeCMS::show_error ( '已经存在相同的文件!', true );
			
		//自定义页文件夹
		$filepath = empty ( $post ['filepath'] ) ? WWWROOT : WWWROOT . $data ['filepath'] . '/';
		//完整路径
		$fullfile = $filepath . $data ['filename'] . $data ['filename_suffix'];
		if ($id > 0) {
			$olddata = $this->db->getwhere ( '[custompage]', array ('id' => $id ) )->result_array ( FALSE );
			$olddata = $olddata [0];
			if (! $olddata) {
				MyqeeCMS::show_error ( '不存在指定页面，可能已经删除', true );
			}
			$fulloldfile = $this->_get_fullpagename ( $olddata );
			//文件路径发生变化时删除旧文件
			if ($fullfile != $fulloldfile) {
				if (file_exists ( $fulloldfile ))
					@unlink ( $fulloldfile ); //移除文件
			}
			$status = $this->db->update ( '[custompage]', $data, array ('id' => $id ) ) -> count();
		} else {
			$status = $this->db->insert ( '[custompage]', $data );
			$id = $status -> insert_id();
			$status = $status -> count();
		}
		
		if ($status) {
			//如果选择模板 
			if ($data ['isuse'] == 1) {
				if ($tplid > 0) {
					$result = Tohtml::tocustompage($id);
					if ($result['ok']){
						MyqeeCMS::show_ok ( '自定义页已保持到数据，并已生成静态页！', true );
					}elseif($result['error']){
						MyqeeCMS::show_info ( '自定义页已保持到数据，但生成静态页错误：<br/>'.$result['error'], true );
					}else{
						print_r($result);exit;
						MyqeeCMS::show_info ( '自定义页已保持到数据，<br/>但生成静态页面错误，请检查模板！', true );
					}
				} else { //没有模板直接生成页面
					Tools::create_dir ( $filepath );
					$flagarr = array ('{{' . $data ['title_flag'] . '}}', '{{' . $data ['keywords_flag'] . '}}', '{{' . $data ['pagedesc_flag'] . '}}' );
					$replacearr = array ($data ['pagetitle'], $data ['keyword'], $data ['pagedesc'] );
					
					//自定义页
					foreach ($param_config as $item){
						$flagarr[] = '{{' . $item ['param_flag'] . '}}';
						$replacearr[] = $item ['param_value'];
					}
					$data ['content'] = str_replace ( $flagarr, $replacearr, $data ['content'] );
					@file_put_contents ( $fullfile, $data ['content'] );
				}
			} else {
				if (file_exists ( $fullfile ))
					@unlink ( $fullfile ); //移除文件
			}
			//输出提示信息
			MyqeeCMS::show_ok ( '自定义页保存成功！', true );
		} else {
			MyqeeCMS::show_info ( '未保存任何信息！', true );
		}
	}
	
	public function renew($allid = 0) {
		$this->db = Database::instance ();
		if ($allid) {
			$ids = Tools::formatids ( $allid );
			$this -> db = Database::instance ();
			$datas = $this -> db -> from ( '[custompage]' );
		    $datas = $datas->in ( 'id', $ids )->get ()->result_array ( false );
		}
		$tmpid = "";
		foreach ( $datas as $data ) {
			//如果选择模板 
			$filepath = empty ( $data ['filepath'] ) ? WWWROOT : WWWROOT . $data ['filepath'] . '/';
			//完整路径
			$fullfile = $filepath . $data ['filename'] . $data ['filename_suffix'];
			if ($data ['isuse'] == 1) {
				if ($data['istpl'] && $data['tplid'] > 0) {
					$tmpid .= $data ['tplid'] . ',';
				} else { //没有模板直接生成页面
					Tools::create_dir ( $filepath );
					$flagarr = array ('{{' . $data ['title_flag'] . '}}', '{{' . $data ['keywords_flag'] . '}}', '{{' . $data ['pagedesc_flag'] . '}}' );
					$replacearr = array ($data ['pagetitle'], $data ['keyword'], $data ['pagedesc'] );
					$data['content'] = str_replace ( $flagarr, $replacearr, $data ['content'] );
					
					$param_config = unserialize($data['param']);
					if (is_array($param_config)){
						foreach ($param_config as $item){
							$flagarr[] = '{{' . $item ['param_flag'] . '}}';
							$replacearr[] = $item ['param_value'];
						}
					}
					@file_put_contents ( $fullfile, $data ['content'] );
				}
			} else {
				if (file_exists ( $fullfile ))
					@unlink ( $fullfile ); //移除文件
			}
			
		}
		
		$tmpid = trim ( $tmpid, ',' );
		if ($tmpid) {
			$tohtmlurl = _get_tohtmlurl ( 'tocustompage', MyqeeCMS::config ( 'encryption.default.key' ), '&theid=' . $allid );
			header ( 'location:' . $tohtmlurl );
			exit ();
		}
		MyqeeCMS::show_info ( '更新自定义页面成功！', true );
	}
	
	/**
	 * 删除自定义页面
	 */
	public function del($allid) {
		Passport::checkallow ( 'info.custompagedel' );
		$allid = Tools::formatids ( $allid, false );
		$this->db = Database::instance ();
		$page_infos = $this->db->from ( '[custompage]' )->in ( 'id', $allid )->get ()->result_array ( false );
		
		foreach ( $page_infos as $page_info ) {
			$filename = $this->_get_fullpagename($page_info);
			if (is_file ( $filename )) {
				//删除文件
				@unlink ( $filename );
			}
		}
		$delNum = $this->db->in ( 'id', $allid )->delete ( '[custompage]' );
		if (count ( $delNum ) > 0) {
			MyqeeCMS::show_info ( '删除自定义页面成功！', true, 'refresh' );
		} else {
			MyqeeCMS::show_info ( Myqee::lang ( 'admin/info.info.nodelete' ), true );
		}
	}
	
	protected function _get_fullpagename($pagearray) {
		return WWWROOT . (empty ( $pagearray ['filepath'] ) ? '' : $pagearray ['filepath'] . '/') . $pagearray ['filename'] . $pagearray ['filename_suffix'];
	}
}