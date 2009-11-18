<?php

class Test_Controller_Core{

	public function index(){
		
		$ad = Des::Encrypt('SADFewrw#$%#%345','id:12');
		echo $ad,'<br>';
		
		echo Des::Decrypt('SADFewrw#$%#%345',$ad).'<br>';
		
		echo '<br>';
		echo Des::Decrypt('sdfsdf','058ba365d1ef3d48eeeea48f43c86964eae571fd542a386eef46142e32f617461fbd885868f9794b7e75585e004aa745ccce44579cc1fb3e75ec38fbad157f3e5a7b812217e79221620418e1164ba87679adf19d7f8ebb4c').'<br>';

//		echo Myqee::runtime(10);
	}
	
	public function test1()
	{
		
		$smarty = new Smarty('index.tpl','smarty');
		
//		$smarty->compile_check = true;
//		$smarty->debugging = true;
		
		$smarty->assign("Name","Fred Irving Johnathan Bradley Peppergill");
		$smarty->assign("FirstName",array("John","Mary","James","Henry"));
		$smarty->assign("LastName",array("Doe","Smith","Johnson","Case"));
		$smarty->assign("Class",array(array("A","B","C","D"), array("E", "F", "G", "H"),
			  array("I", "J", "K", "L"), array("M", "N", "O", "P")));
		
		$smarty->assign("contacts", array(array("phone" => "1", "fax" => "2", "cell" => "3"),
			  array("phone" => "555-4444", "fax" => "555-3333", "cell" => "760-1234")));
		
		$smarty->assign("option_values", array("NY","NE","KS","IA","OK","TX"));
		$smarty->assign("option_output", array("New York","Nebraska","Kansas","Iowa","Oklahoma","Texas"));
		$smarty->assign("option_selected", "NE");
		
		$smarty->render(TRUE);
	}
	
	
	function test3($d=0){
		$this -> contents = 'asd[!--LANG tes/.dasss--]flkjasdfj [!--INC test.php--]sadfsadf';
		$this -> contents = preg_replace('/\[!--(?:\s*)LANG ([a-z0-9\/._]+)--\]/i','<?php echo Myqee::lang(\'$1\');?>',$this -> contents);
		$this -> contents = preg_replace('/\[!--(?:\s*)INC(?:LUDE)?(?:\s*)(?:"|\'*)(.+?)(?:"|\'*)(?:\s*)--\]/','<?php include(MYAPPPATH.$this->group()."/$1");?>', $this -> contents);
		echo '<pre>';
		echo htmlspecialchars($this -> contents);
		
		$randstr = '{{__MYQEE_PhP_LEFTTAG__'.rand(100000000,999999999).'}}';
		$this -> contents = str_replace(array('<?','?>',$randstr),array($randstr,'<?php echo \'?>\';?>','<?php echo \'<?\';?>'),$this -> contents);
		
		echo '<pre>';
		echo htmlspecialchars($this -> contents);
		
		
		echo "\r\n\r\n";
		
		echo dirname(__FILE__).DIRECTORY_SEPARATOR;
	}
	
	
	function tt(){
		$tpl = array();
		$tpl['title'] = '猫扑文学原创频道';
		$tpl['images'] = './templates/default/{images}';
		
		$tpl['category'][0]['name'] = '情感';
		
		$tpl['category'][0]['sub'][0]['name'] = '小分类一';
		$tpl['category'][0]['sub'][1]['name'] = '小分类二';
		$tpl['category'][0]['sub'][2]['name'] = '情感小分类三';
		
		$tpl['category'][1]['name'] = '生活';
		
		$tpl['category'][1]['sub'][0]['name'] = '小分类一';
		$tpl['category'][1]['sub'][1]['name'] = '小分类二';
		$tpl['category'][1]['sub'][2]['name'] = '小分类三';
		$tpl['category'][1]['sub'][3]['name'] = '小分类四';
		
		$tpl['category'][2]['name'] = '历史';
		
		$tpl['category'][3]['name'] = '玄幻';
		$tpl['category'][4]['name'] = '其他';
		
		$tpl['contents']			= '我是内容,哇哈哈~!';
		$tpl['current_time']		= time();
		
		$t = new Template('index.tpl','test2');
		
		$t -> render(true,$tpl);
	}
}
