<?php

class Link_Controller_Core extends Controller {
	
	public $adminDate=FALSE;
	public $pageIndex='link';
	
	function __construct() {
		parent::__construct();
		
		$this->session = Passport::chkadmin();
	}
	
	public function index($p = 1, $site = "") {
		
		$pageSize = 20;
		
		$view = new View("admin/link_list");
		
		$db = new Database;
		
		$num = $db -> count_records('link');
		
		$page = new Pagination(array(
			"uri_segment" => "index",
			"total_items" => $num,
			"items_per_page" => $pageSize,
		));

		$rs = $db->select('*')->from('link') -> orderby('id','DESC')-> limit($pageSize,$page->sql_offset()) ->get()->result_array(FALSE);
		
		$view->set("list", $rs);
		
		$view->set("page", $page->create_links("digg"));
		
		$view->set("page_index", $this->pageIndex);
		
		$view->render(TRUE);
	}
	
	public function add() {
		$this->edit();
	}
	
	public function edit($id = 0) {
		
		$view = new View("admin/link_edit");
		
		if ($id > 0) {
			$db = new Database;
			$rs = $db->select("*")->from("link")->where("id", $id)->limit(1)->get()->result_array(FALSE);
			$view->set("link", $rs[0]);
		}
		
		$view->set("page_index", $this->pageIndex);
		
		$view->render(TRUE);
	}
	
	public function save() {
		if (!($post = $_REQUEST["link"])) {
			die('<script>alert("参数错误！");document.location="about:blank";</script>');
		}
		
		$post["id"] = (int)$post["id"];
		
		$link = array(
		"link_name" => htmlspecialchars($post["name"]),
		"link_url" => $post["url"],
		"link_desc" => $post["desc"],
		"audit_flag" => $post["audit_flag"],
		);
		
		if (empty($link["link_name"])) {
			die('<script>alert("链接名称不能空！");document.location="about:blank";</script>');
		}
		
		if (empty($link["link_url"])) {
			die('<script>alert("链接网址不能空！");document.location="about:blank";</script>');
		}
		
		$link["add_date"] = (int)strtotime($post["postdate"]);
		
		if ($link["add_date"] <= 0) {
			$link["add_date"] =$_SERVER['REQUEST_TIME'];
		}
		
		$link["audit_flag"] == 1 ? "" : $link["audit_flag"] = 0;
		
		if (($logo = $_FILES["logo"]) && $post["logoHasChanged"]) {
			if ($logo["tmp_name"]) {
			$upload = new Upload();
			$upload->setBaseDir(WWWROOT."upload");
			$upload->setSaveDir("logos");
			if ($err = $upload->saveFile($logo)) {
				die('<script>alert("'.$err.'");document.location="about:blank";</script>');
			}
			$link["link_logo"] = substr($upload->fileName,strlen(WWWROOT));
			} else {
				$link["link_logo"] = "";
			}
			
		}
		
		$db = new Database;
		
		if ($post["id"] > 0) {
			$status = $db->update("link", $link, array("id" => $post["id"]));
		} else {
			$status = $db->insert("link", $link);
		}
		
		if (count($status) > 0) {
			die('<script>alert("保存成功！");parent.document.location="'.Myqee::url('admin/link/index').'";</script>');
		} else {
			die('<script>alert("未更新任何数据！");document.location="about:blank";</script>');
		}
	}
	
	public function audit($id = 0) {
		if ($id > 0) {
			$db = new Database;
			$res = $db->select("audit_flag")->from("link")->where("id", $id)->limit(1)->get()->result_array(FALSE);
			if (count($res) > 0) {
				$status = $db->update("link", array("audit_flag" => $res[0]["audit_flag"] > 0 ? 0 : 1), array("id" => $id));
				if (count($status) > 0) {
					die('<script>alert("更新成功！");parent.location=parent.location;</script>');
				} else {
					die('<script>alert("未删除任何数据！");document.location="about:blank";</script>');
				}
				
			} else {
				die('<script>alert("数据不存在！");document.location="about:blank";</script>');
			}
		} else {
			die('<script>alert("参数错误！");document.location="about:blank";</script>');
		}
	}
	
	public function del($allid=''){
		$idArr=explode(',',$allid);
		$myId=array();
		foreach ($idArr as $value){
			if ($value>0){
				$myId[]=$value;
			}
		}
		if (count($myId)>0){
			$db = new Database;
			
			$configData=Myqee::config("database.default");
			$dbPrefix = $configData['table_prefix'];
			$status = $db->query('DELETE FROM '.$dbPrefix.'link WHERE id IN ("'.join(',',$myId).'")');
			if (count($status)>0){
				echo '<script>alert("恭喜，成功删除',count($status),'条链接！");parent.location=parent.location;</script>';
				exit();
			}else{
				echo '<script>alert("未删除任何数据！");document.location="about:blank";</script>';
				exit();
			}
		}else{
			echo '<script>alert("缺少参数！");document.location="about:blank";</script>';
			exit();
		}
	}
	
}
?>