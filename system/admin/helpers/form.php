<?php defined('MYQEEPATH') OR die('No direct access allowed.');
/**
 * Form helper class.
 *
 * $Id$
 *
 * @package    Core
 * @author     Myqee Team
 * @copyright  (c) 2008-2010 Myqee Team
 * @license    http://myqee.com/license.html
 */
class form_Core {

	/**
	 * Generates an opening HTML form tag.
	 *
	 * @param   string  form action attribute
	 * @param   array   extra attributes
	 * @param   array   hidden fields to be created immediately after the form tag
	 * @return  string
	 */
	public static function open($action = NULL, $attr = array(), $hidden = NULL)
	{
		// Make sure that the method is always set
		empty($attr['method']) and $attr['method'] = 'post';

		if ($attr['method'] !== 'post' AND $attr['method'] !== 'get')
		{
			// If the method is invalid, use post
			$attr['method'] = 'post';
		}

		if ($action === NULL)
		{
			// Use the current URL as the default action
			$action = Myqee::url(Router::$complete_uri);
		}
		elseif (strpos($action, '://') === FALSE)
		{
			// Make the action URI into a URL
			$action = Myqee::url($action);
		}

		// Set action
		$attr['action'] = $action;

		// Form opening tag
		$form = '<form'.form::attributes($attr).'>'."\n";

		// Add hidden fields immediate after opening tag
		empty($hidden) or $form .= form::hidden($hidden);

		return $form;
	}

	/**
	 * Generates an opening HTML form tag that can be used for uploading files.
	 *
	 * @param   string  form action attribute
	 * @param   array   extra attributes
	 * @param   array   hidden fields to be created immediately after the form tag
	 * @return  string
	 */
	public static function open_multipart($action = NULL, $attr = array(), $hidden = array())
	{
		// Set multi-part form type
		$attr['enctype'] = 'multipart/form-data';

		return form::open($action, $attr, $hidden);
	}

	/**
	 * Generates a fieldset opening tag.
	 *
	 * @param   array   html attributes
	 * @param   string  a string to be attached to the end of the attributes
	 * @return  string
	 */
	public static function open_fieldset($data = NULL, $extra = '')
	{
		return '<fieldset'.html::attributes((array) $data).' '.$extra.'>'."\n";
	}

	/**
	 * Generates a fieldset closing tag.
	 *
	 * @return  string
	 */
	public static function close_fieldset()
	{
		return '</fieldset>'."\n";
	}

	/**
	 * Generates a legend tag for use with a fieldset.
	 *
	 * @param   string  legend text
	 * @param   array   HTML attributes
	 * @param   string  a string to be attached to the end of the attributes
	 * @return  string
	 */
	public static function legend($text = '', $data = NULL, $extra = '')
	{
		return '<legend'.form::attributes((array) $data).' '.$extra.'>'.$text.'</legend>'."\n";
	}

	/**
	 * Generates hidden form fields.
	 * You can pass a simple key/value string or an associative array with multiple values.
	 *
	 * @param   string|array  input name (string) or key/value pairs (array)
	 * @param   string        input value, if using an input name
	 * @return  string
	 */
	public static function hidden($data, $value = '')
	{
		if ( ! is_array($data))
		{
			$data = array
			(
				$data => $value
			);
		}

		$input = '';
		foreach ($data as $name => $value)
		{
			$attr = array
			(
				'type'  => 'hidden',
				'name'  => $name,
				'value' => $value
			);

			$input .= form::input($attr)."\n";
		}

		return $input;
	}

	/**
	 * Creates an HTML form input tag. Defaults to a text type.
	 *
	 * @param   string|array  input name or an array of HTML attributes
	 * @param   string        input value, when using a name
	 * @param   string        a string to be attached to the end of the attributes
	 * @return  string
	 */
	public static function input($data, $value = '', $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		// Type and value are required attributes
		$data += array
		(
			'type'  => 'text',
			'value' => $value
		);

		return '<input'.form::attributes($data).' '.$extra.' />';
	}

	/**
	 * Creates a HTML form password input tag.
	 *
	 * @param   string|array  input name or an array of HTML attributes
	 * @param   string        input value, when using a name
	 * @param   string        a string to be attached to the end of the attributes
	 * @return  string
	 */
	public static function password($data, $value = '', $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		$data['type'] = 'password';

		return form::input($data, $value, $extra);
	}

	/**
	 * Creates an HTML form upload input tag.
	 *
	 * @param   string|array  input name or an array of HTML attributes
	 * @param   string        input value, when using a name
	 * @param   string        a string to be attached to the end of the attributes
	 * @return  string
	 */
	public static function upload($data, $value = '', $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		$data['type'] = 'file';

		return form::input($data, $value, $extra);
	}

	/**
	 * Creates an HTML form textarea tag.
	 *
	 * @param   string|array  input name or an array of HTML attributes
	 * @param   string        input value, when using a name
	 * @param   string        a string to be attached to the end of the attributes
	 * @param   boolean       encode existing entities
	 * @return  string
	 */
	public static function textarea($data, $value = '', $extra = '', $double_encode = TRUE)
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		// Use the value from $data if possible, or use $value
		$value = isset($data['value']) ? $data['value'] : $value;

		// Value is not part of the attributes
		unset($data['value']);

		return '<textarea'.form::attributes($data, 'textarea').' '.$extra.'>'.html::specialchars($value, $double_encode).'</textarea>';
	}

	/**
	 * Creates an HTML form select tag, or "dropdown menu".
	 *
	 * @param   string|array  input name or an array of HTML attributes
	 * @param   array         select options, when using a name
	 * @param   string        option key that should be selected by default
	 * @param   string        a string to be attached to the end of the attributes
	 * @return  string
	 */
	public static function dropdown($data, $options = NULL, $selected = NULL, $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}
		else
		{
			if (isset($data['options']))
			{
				// Use data options
				$options = $data['options'];
			}

			if (isset($data['selected']))
			{
				// Use data selected
				$selected = $data['selected'];
			}
		}

		if (is_array($selected))
		{
			// Multi-select box
			$data['multiple'] = 'multiple';
		}
		else
		{
			// Single selection (but converted to an array)
			$selected = array($selected);
		}

		$input = '<select'.form::attributes($data, 'select').' '.$extra.'>'."\n";
		foreach ((array) $options as $key => $val)
		{
			// Key should always be a string
			$key = (string) $key;

			if (is_array($val))
			{
				$input .= '<optgroup label="'.$key.'">'."\n";
				foreach ($val as $inner_key => $inner_val)
				{
					// Inner key should always be a string
					$inner_key = (string) $inner_key;

					$sel = in_array($inner_key, $selected) ? ' selected="selected"' : '';
					$input .= '<option value="'.$inner_key.'"'.$sel.'>'.$inner_val.'</option>'."\n";
				}
				$input .= '</optgroup>'."\n";
			}
			else
			{
				$sel = in_array($key, $selected) ? ' selected="selected"' : '';
				$input .= '<option value="'.$key.'"'.$sel.'>'.$val.'</option>'."\n";
			}
		}
		$input .= '</select>';

		return $input;
	}

	/**
	 * Creates an HTML form checkbox input tag.
	 *
	 * @param   string|array  input name or an array of HTML attributes
	 * @param   string        input value, when using a name
	 * @param   boolean       make the checkbox checked by default
	 * @param   string        a string to be attached to the end of the attributes
	 * @return  string
	 */
	public static function checkbox($data, $value = '', $checked = FALSE, $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		$data['type'] = 'checkbox';

		if ($checked == TRUE OR (isset($data['checked']) AND $data['checked'] == TRUE))
		{
			$data['checked'] = 'checked';
		}
		else
		{
			unset($data['checked']);
		}

		return form::input($data, $value, $extra);
	}

	/**
	 * Creates an HTML form radio input tag.
	 *
	 * @param   string|array  input name or an array of HTML attributes
	 * @param   string        input value, when using a name
	 * @param   boolean       make the radio selected by default
	 * @param   string        a string to be attached to the end of the attributes
	 * @return  string
	 */
	public static function radio($data = '', $value = '', $checked = FALSE, $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		$data['type'] = 'radio';

		if ($checked == TRUE OR (isset($data['checked']) AND $data['checked'] == TRUE))
		{
			$data['checked'] = 'checked';
		}
		else
		{
			unset($data['checked']);
		}

		return form::input($data, $value, $extra);
	}

	/**
	 * Creates an HTML form submit input tag.
	 *
	 * @param   string|array  input name or an array of HTML attributes
	 * @param   string        input value, when using a name
	 * @param   string        a string to be attached to the end of the attributes
	 * @return  string
	 */
	public static function submit($data = '', $value = '', $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		if (empty($data['name']))
		{
			// Remove the name if it is empty
			unset($data['name']);
		}

		$data['type'] = 'submit';

		return form::input($data, $value, $extra);
	}

	/**
	 * Creates an HTML form button input tag.
	 *
	 * @param   string|array  input name or an array of HTML attributes
	 * @param   string        input value, when using a name
	 * @param   string        a string to be attached to the end of the attributes
	 * @return  string
	 */
	public static function button($data = '', $value = '', $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		if (empty($data['name']))
		{
			// Remove the name if it is empty
			unset($data['name']);
		}

		if (isset($data['value']) AND empty($value))
		{
			$value = arr::remove('value', $data);
		}

		return '<button'.form::attributes($data, 'button').' '.$extra.'>'.$value.'</button>';
	}

	/**
	 * Closes an open form tag.
	 *
	 * @param   string  string to be attached after the closing tag
	 * @return  string
	 */
	public static function close($extra = '')
	{
		return '</form>'."\n".$extra;
	}

	/**
	 * Creates an HTML form label tag.
	 *
	 * @param   string|array  label "for" name or an array of HTML attributes
	 * @param   string        label text or HTML
	 * @param   string        a string to be attached to the end of the attributes
	 * @return  string
	 */
	public static function label($data = '', $text = NULL, $extra = '')
	{
		if ( ! is_array($data))
		{
			if (is_string($data))
			{
				// Specify the input this label is for
				$data = array('for' => $data);
			}
			else
			{
				// No input specified
				$data = array();
			}
		}

		if ($text === NULL AND isset($data['for']))
		{
			// Make the text the human-readable input name
			$text = ucwords(inflector::humanize($data['for']));
		}

		return '<label'.form::attributes($data).' '.$extra.'>'.$text.'</label>';
	}

	/**
	 * Sorts a key/value array of HTML attributes, putting form attributes first,
	 * and returns an attribute string.
	 *
	 * @param   array   HTML attributes array
	 * @return  string
	 */
	public static function attributes($attr, $type = NULL)
	{
		if (empty($attr))
			return '';

		if (isset($attr['name']) AND empty($attr['id']) AND strpos($attr['name'], '[') === FALSE)
		{
			if ($type === NULL AND ! empty($attr['type']))
			{
				// Set the type by the attributes
				$type = $attr['type'];
			}

			switch ($type)
			{
				case 'text':
				case 'textarea':
				case 'password':
				case 'select':
				case 'checkbox':
				case 'file':
				case 'image':
				case 'button':
				case 'submit':
					// Only specific types of inputs use name to id matching
					$attr['id'] = $attr['name'];
				break;
			}
		}

		$order = array
		(
			'action',
			'method',
			'type',
			'id',
			'name',
			'value',
			'src',
			'size',
			'maxlength',
			'rows',
			'cols',
			'accept',
			'tabindex',
			'accesskey',
			'align',
			'alt',
			'title',
			'class',
			'style',
			'selected',
			'checked',
			'readonly',
			'disabled'
		);

		$sorted = array();
		foreach ($order as $key)
		{
			if (isset($attr[$key]))
			{
				// Move the attribute to the sorted array
				$sorted[$key] = $attr[$key];

				// Remove the attribute from unsorted array
				unset($attr[$key]);
			}
		}

		// Combine the sorted and unsorted attributes and create an HTML string
		return html::attributes(array_merge($sorted, $attr));
	}

// End form







	public static function imginput($data, $value = '', $extra = '', $allownum = 1){
		return self::uploadfile($data, $value, $extra, 'upimg',$allownum);
	}

	public static function flash($data, $value = '', $extra = '', $allownum = 1){
		return self::uploadfile($data, $value, $extra, 'upflash',$allownum);
	}
	
	public static function uploadfile($data, $value = '', $extra = '' , $uptype = null , $allownum = 1){
		if (!in_array($uptype , array('upimg','upfile','upflash')))$uptype='upfile';
		if (is_array($data)){
			if (!$data['id']){$data['id']=$data['name'];}
		}else{
			$data = array(
				'name'=>$data,
				'id'=>$data,
			);
		}
		$data['disabled'] and $uptype .= '_disabled';
		$data['class'] = 'input';
		if ($data['_config']){
			$config = $data['config'];
		}elseif($extra && preg_match("/_config=\"([a-zA-Z0-9]+)\"/",$extra,$match)){
			$config = $match[1];
		}
		return '<span style="white-space:nowrap;">'.form::input($data , $value , $extra .'style="width:300px;"' ) . 
		'<img title="点击查看" width="10" height="10" src="' . ADMIN_IMGPATH. '/admin/external.png" style="position:absolute;margin:0 0 0 -10px;cursor:pointer;" onclick="var myinput=$(\''.$data['id'].'\');if(myinput.value){goUrl(\''.Myqee::url('uploadfile/showfile').'?url=\'+encodeURIComponent(myinput.value),\'_blank\')}else{alert(\'请先上传文件！\')}" />&nbsp;<img src="' . ADMIN_IMGPATH. '/admin/'.$uptype.'.gif" align="absmiddle"'.($data['disabled']?' disabled="disabled"':'').' style="cursor:pointer" onclick="if(this.getAttribute(\'disabled\')==\'disabled\')return;show_upload_frame(\'' . $data['id'] . '\',\''. Myqee::url('uploadfile/inframe/'.$uptype.'/'.$allownum.'/'.$config) .'\',\''.$uptype.'\')" /></span>';
	}
	
	public static function classlist($data  , $class_array = FALSE , $extra = '' ,$selected = FALSE ,$isaddroot = TRUE ,$isshowcolor = false,$setcolorby = '', $spacer=''){
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}
		if (is_array($isaddroot)){

		}elseif (is_string($isaddroot)){
			$isaddroot = array(''=>$isaddroot);
		}else {
			$isaddroot = array('根目录');
		}
		$roothtml = '';
		foreach ($isaddroot as $key=>$value){
			if (is_array($selected))
			{
				$sel = in_array($key, $selected);
			}
			else
			{
				$sel = ($selected === $key);
			}
			$sel = ($sel === TRUE) ? ' selected="selected"' : '';
			$roothtml .= '<option value="'.$key.'"'.$sel.'>'.$value.'</option>';
		}
	
		return '<select'.form::attributes($data, 'select').' '.$extra.'>'."\n" . $roothtml . form::_listclass($class_array,$selected,$isshowcolor,$setcolorby,$spacer) .'</select>';
	}


	protected static $option_html = '';
	protected static function _listclass($list,$selectclassid,$isshowcolor = false,$setcolorby='',$spacer=''){
		if($list){
			$countlist = count($list);
			if (!is_array($setcolorby))$setcolorby = array('iscontent',0);
			$i = 0;
			foreach ($list as $item){
				$i++;
				if ( $i == $countlist){
					$outspacer = $spacer . '└';
				}else{
					$outspacer = $spacer . '├';
				}
				$style = '';
				if ($isshowcolor && $item[$setcolorby[0]]==$setcolorby[1]){
					if (is_array($isshowcolor)){
						$style = ' style="' . $isshowcolor[0] .'"';
					}else{
						$style = ' style="background:#f4f4f4;color:#ccc;"';
					}
				}else{
					if (is_array($isshowcolor)){
						$style = ' style="' . $isshowcolor[1] .'"';
					}else{
						$style = '';
					}
				}
				$option_html .= '<option value="'.$item['classid'].'"' . $style . (is_array($selectclassid)?(in_array($item['classid'],$selectclassid)?' selected="selected"':''):( $selectclassid ==$item['classid'] ? ' selected="selected"':'')) .'>'.$outspacer . $item['classname'] . '</option>';
	
				if ($item['sonclassarray']){
					if ( $i == $countlist){
						$spacer .= '　';
					}else{
						$spacer .= '│';
					}
					$option_html .= form::_listclass($item['sonclassarray'],$selectclassid,$isshowcolor,$setcolorby,$spacer);
					$spacer = substr($spacer,0,-strlen('　'));
				}
			}
		}
		return $option_html;
	}
	
	public static function htmlarea($data,$value='',$extra='',$textconfig='',$autoload=true){
		if (is_array($data)){
			$thename = $data['id']?$data['id']:($data['name']?$data['name']:$data);
		}else{
			$thename = $data;
			$data = array('name'=>$data,'id'=>$data);
		}
		
		return '<span style="display:none;">' .form::textarea($data,$value,$extra) . '<input id="'.$thename.'___Config" value="BaseHref=http://'.Myqee::config('core.mysite_domain').'/&'.$textconfig.'" style="display:none;" type="hidden" /></span><iframe name="'.
		$thename.'___Frame" id="'.$thename.'___Frame" '.($autoload?'src':'onclick="this.src=this.title;this.title=\'\';this.onclick=null;" title').'="'.ADMIN_IMGPATH.'/admin/fckeditor/editor/fckeditor.html?InstanceName='.$thename.'&Toolbar='.($data['toolbar']?$data['toolbar']:'Default').'" frameBorder="no" width="'.($data['size']?($data['size']*6).'px':'100%').'" scrolling="no" height="'.($data['rows']?($data['rows']*12+60):'350').'"></iframe>';
	}
	
	public static function basehtmlarea($data,$value='',$extra=''){
		if(is_array($data)){
			$data['toolbar']='Basic';
		}else{
			$data = array(
				'name'=>$data,
				'toolbar'=>'Basic',
			);
		}
		return self::htmlarea($data,$value,$extra);
	}
	
	public static function pagehtmlarea($data,$value='',$extra=''){
		if(is_array($data)){
			//$data['toolbar']='Basic';
		}else{
			$data = array(
				'name'=>$data,
			//	'toolbar'=>'Basic',
			);
		}
		$data['id'] or $data['id'] = 'htmlarea_'.$data['name'];
		$theid = $data['id'];
		$dataname = $data['name'];
		/*
		$tmphtml = '<table cellpadding="2" cellspacing="1" style="width:100%" class="tableborder">
		<tr><th class="td1" width="40">序号</th><th class="td1">分页标题(选填)</th><th width="220" class="td1">操作</th></tr>
		<tr>
			<td class="td2" align="center">正文</td>
			<td class="td2" align="center"><input type="text" class="input" size="30" style="width:96%" /></td>
			<td class="td2"><input type="button" class="btns" value="编辑" /></td>
		</tr>
		</table>
		<table cellpadding="2" cellspacing="1" style="width:100%" class="tableborder" style="border-top:none;">
		<tr>
		<td class="td1" width="80" align="center"><input type="button" class="btn" value="添加分页" /></td>
		<td class="td1">现有分页 2 个, 当前正在编辑<b>[文章正文]</b></td>
		</tr>
		</table>';
*/

		$out = MyqeeCMS::get_title_info_array($value);
		$hidden = '';
		$taghtml = '';
		$pagenum = count($out['info']);
		for ($key=1;$key<=$pagenum;$key++){
			$data['name'] = $dataname . '[info_'.$key.']';
			$data['id'] =$theid.'_'.$key;
			$out['title'][$key-1] == ' ' and $out['title'][$key-1] = NULL;
			if (!$out['title'][$key-1]){
				$out['title'][$key-1] ='-'.$key.'-';
			}
			$thetitle['id_'.$key] = $out['title'][$key-1];
			$taghtml .= '<li '.($key==1?'class="now" ':'').'id="pagehtmlareaTitle_'.$key.'" onclick="htmlarea_tclick(this.id.substr(18))">'.$out['title'][($key-1)].self::input(array('type'=>'hidden','name'=>'page_title[id_'.$key.']'),$out['title'][($key-1)]) .'</li>';
			
			$htmlarea =  self::htmlarea($data,$out['info'][($key-1)],$extra,'ToolbarCanCollapse=false&ToolbarLocation=Out:xToolbar',$key==1?true:false);
			
			$html .= '<div id="pagehtmlareaDiv_'.$key.'"'.$hidden.'>'.$htmlarea.'</div>';
			$hidden or $hidden = ' style="display:none;"';
		}
		
//		$data['id'] = $theid .='_2';
//		$html .= self::htmlarea($data,$value,$extra,'ToolbarLocation=Out:xToolbar');
		
		$data['name'] = $dataname.'[info_\'+theid+\']';
		$data['id'] = $theid.'_\'+theid+\'';
		return '<div id="xToolbar" style="height:85px;overflow:hidden;"></div>
<script type="text/javascript">
var nowEditId = 1;
var allHtmlNum = '.count($thetitle).';
var pageTitle = '.Tools::json_encode($thetitle).';
function htmlarea_tclick(theid){
	tag("pagehtmlareaTitle_"+theid,\'pagehtmlareaTitle\',\'pagehtmlareaDiv\',\'pageHtmlEditDiv\');
	changeHtmlArea(theid,\''.$theid.'_\'+theid);
}
function changeHtmlArea(id,theid){
	var obj = $("pagehtmlareaDiv_"+id);
	if (!obj)return;
	var frame = $(theid+"___Frame");
	if (frame.onclick){
		frame.onclick();
		frame.onclick = function(){};
	}
	var objtitle = $("objtitleInput");
	objtitle.value = pageTitle["id_"+id]||"";
	nowEditId = id;
	changeHeight(true);
	var fckeditor = FCKeditorAPI.GetInstance(theid);
	if (fckeditor){
		fckeditor.Focus();
	}
}
function add_htmlarea(){
	var theid = allHtmlNum+1;
	
	var ul = $("pageUlDiv");
	var lis = ul.getElementsByTagName("li");
	var pagetitle = "-"+(lis.length+1)+"-";
	pageTitle["id_"+(lis.length+1)] = pagetitle;
	
	var obj1 = document.createElement("div");
	obj1.id = "pagehtmlareaDiv_"+theid;
	obj1.innerHTML = \''.str_replace('&#039;','\'',self::htmlarea($data,$out['info'][$key],$extra,'ToolbarLocation=Out:xToolbar')).'\';
	$("TEXTAREA_DIV").appendChild(obj1);
	
	var obj2 = document.createElement("li");
	obj2.id = "pagehtmlareaTitle_"+theid;
	obj2.onclick = function(){htmlarea_tclick(this.id.substr(18));};
	obj2.innerHTML = pagetitle+\'<input type="hidden" name="page_title[id_\'+theid+\']" value="\'+pagetitle+\'" />\';
	ul.appendChild(obj2);
	
	obj2.onclick();
	allHtmlNum++;
	
	$("objtitleInput").value = pagetitle;
	pageTitle["id_"+theid] = pagetitle;
	html_rename_all();
}

function htmlorder_seveorder(){
	var tmphtml="";
	var objs = $("myEditorTable").rows;
	var lis=frameFrame.$("pageUlDiv").getElementsByTagName("li");
	for(var i=0;i<objs.length-1;i++){
		if (!lis[i])continue;
		var theid=objs[(i+1)].getElementsByTagName("input")[1].id.substr("15");
		lis[i].id="pagehtmlareaTitle_"+theid;
		if(frameFrame.nowEditId==theid){
			lis[i].className="now";
		}else{
			lis[i].className="";
		}
	}
	frameFrame.html_rename_all();
	closeMsgBox();
};

function paixu_htmlarea(){
	//获取标题
	var ul = $("pageUlDiv");
	var lis = ul.getElementsByTagName("li");
	var tmphtml = \'<div style="padding:10px 5px 0 20px;"><div style="\'+(lis.length>10?"height:320px;":"")+\'overflow:auto;"><table id="myEditorTable" border="0" style="width:580px" cellpadding="2" cellspacing="1" class="tableborder"><tr><th class="td1" width="25" align="center">&nbsp;</th><th class="td1">分页标题</th><th class="td1" width="180">操作</th></tr></table></div>\';
	tmphtml +=\'<table border="0" cellpadding="2" style="width:580px;border-top:none;" cellspacing="1" class="tableborder"><tr><td class="td1"><input type="button" value="上移选定" onclick="if (!myTable[\\\'mytable\\\'])return;myTable[\\\'mytable\\\'].up(1);" class="btn" /><input type="button" value="下移选定" class="btn" onclick="if (!myTable[\\\'mytable\\\'])return;myTable[\\\'mytable\\\'].down();" />  修改完排序需要点“修改排序”才会保存</td><td width="180" class="td1" align="center"><input type="button" value="取消" onclick="closewin()" class="btns" align="asbmiddle" /><input type="button" value="修改排序" onclick="htmlorder_seveorder()" class="bbtn" /></td></tr></table></div>\';
	win(tmphtml,630,400,"修改分页排序");
	
	var tableInfo= new Array();
	for(var i=0;i<lis.length;i++){
		var theid = lis[i].id.substr(18);
		var thetitle = pageTitle["id_"+theid]||"";
		tableInfo[i] = new Array(
			\'<center><input type="text" onchange="frameFrame.changeTitle(this.value,\'+theid+\')" class="input" size="52" id="edithtml_order_\'+theid+\'" value="\'+(thetitle.replace(/"/g,"&quot;"))+\'" maxlength="200" /></center>\',
			\'<center><input type="button" class="btns" value="删除" onclick="if(frameFrame.del_htmlarea(\'+theid+\',true)){var _obj=this.parentNode.parentNode.parentNode;_obj.parentNode.removeChild(_obj)}" /><input type="button" class="btns" onclick="if (!myTable[\\\'mytable\\\'])return;myTable[\\\'mytable\\\'].up(1,this.parentNode.parentNode.parentNode);" value="上移" /><input type="button" class="btns" value="下移" onclick="if (!myTable[\\\'mytable\\\'])return;myTable[\\\'mytable\\\'].down(0,this.parentNode.parentNode.parentNode);" /></center>\'
		);
	}
	parentFrame.myTable["mytable"] = new parentFrame.CreateTable("myEditorTable",tableInfo);
	parentFrame.myqee(parentFrame.$("myEditorTable"));
}

function changeTitle(value,theid){
	theid = theid||nowEditId;
	value = value.replace(/</g,"&lt;").replace(/>/g,"&gt;");
	if (value==""){return;}
	var objtitle = $("pagehtmlareaTitle_"+theid);
	if (!objtitle){return false;}
	objtitle.innerHTML = value +\'<input type="hidden" name="page_title[id_\'+theid+\']" value="\'+value+\'" />\';
	
	var orderinput = parentFrame.$("edithtml_order_"+theid);
	if (orderinput)orderinput.value = value;
	if (theid==nowEditId)$("objtitleInput").value=value;
	pageTitle["id_"+theid] = value;
	return true;
}

function del_htmlarea(delid,newconfirm){
	delid = delid ||nowEditId;
	if (newconfirm){
		if (_confirm("你确实要删除此分页？")){
			do_del_htmlarea(delid);
			return true;
		}else{
			return false;
		}
	}else{
		confirm("你确实要删除此分页？",400,null,"确定要删除？",function(el){
			if (el=="ok")do_del_htmlarea(delid);
		});
	}
}

function do_del_htmlarea(delid){
	if (!delid)return false;
	var ul = window.$("pageUlDiv");
	var lis = ul.getElementsByTagName("li");
	if (lis.length==1){
		alert("抱歉，至少保留一个编辑区！");
		return false;
	}
	
	var objtitle = window.$("pagehtmlareaTitle_"+delid);
	if (objtitle){
		objtitle.parentNode.removeChild(objtitle);
	}
	
	var objdiv = window.$("pagehtmlareaDiv_"+delid);
	if (objdiv){
		if(ie){
			//IE里直接删除
			objdiv.parentNode.removeChild(objdiv);
		}else{
			var objarea = window.$("_myqee_input_info[content]_"+delid);
			if (objarea){
				objarea.name=null;
			}
			objdiv.style.display = "none";
		}
	}
	
	html_rename_all();
	
	var ula = window.$("pageUlDiv");
	var lisa = ula.getElementsByTagName("li");
	lisa[0].onclick(lisa[0]);
	
	pageTitle["id_"+delid] = null;
	ul = null;
	lis =null;
	objdiv = null;
	objtitle = null;
	if (ie)CollectGarbage();
	return true;
}

function html_rename_all(){
	var ul = $("pageUlDiv");
	var lis = ul.getElementsByTagName("li");
	for (var i=0;i<lis.length;i++){
		var theid = lis[i].id.substr(18);
		var thetitle = pageTitle["id_"+theid];
		if ( thetitle.match(/^-[0-9]+-$/) ){
			thetitle = "-"+(i+1)+"-";
		}
		changeTitle(thetitle,theid);
	}
}
</script>
<div id="TEXTAREA_DIV">'.$html.'</div>
<div style="padding:0 1px;">
<table cellpadding="2" cellspacing="1" style="width:100%" class="tableborder" style="border-top:none;">
<tr>
<td class="td1" width="60" align="center">正在编辑：</td>
<td class="td1"><input type="text" onchange="changeTitle(this.value)" id="objtitleInput" class="input" value="'.$out['title'][0].'" size="20" /> 
<input type="button" class="btn" value="删除分页" onclick="del_htmlarea()" />
<input type="button" class="btn" value="添加分页" onclick="add_htmlarea()" />
<input type="button" class="btn" value="修改排序" onclick="paixu_htmlarea()" />
</td>
</tr>
</table>
<script type="text/javascript" src="'.ADMIN_IMGPATH.'/admin/fckeditor/fckeditor.js"></script>
</div>
<div id="pageHtmlEditDiv">
<ul class="ul tag2" id="pageUlDiv">
'.$taghtml.'
</ul>
<div class="clear"></div>
</div>';
	}
	
	public static function timeinput($data,$value=null, $extra = '' ,$showinput = true){
		static $run;
		if ($run!==true){
			$run = true;
			$tmpinput = '<script type="text/javascript" defer="defer">$import("'.ADMIN_IMGPATH.'/admin/calendar.js");</script>';
		}else{
			$tmpinput = '';
		}
		$thename = $data['name']?$data['name']:$data;
		if(!$data['size'])$data['size'] = 18;
		$data['class'] or $data['class'] = 'input';
		$data['id'] or $data['id'] = '_calendar_'.$thename;
		$tmpinput .= '<span style="white-space:nowrap;">'.form::input($data,$value==''?'':date("Y-m-d".($data['time']?' H:i:s':''),$value>0?$value:$_SERVER['REQUEST_TIME']),'onclick="showcalendar(event,this,'.($data['time']?'true':'false').');" '.$extra) ;
		if ($showinput)$tmpinput .= '<input type="button" value="设为当前时间" onclick="var mydate=new Date;var mmmm=mydate.getMonth()+1;var dddd=mydate.getDate();var hhhh=mydate.getHours();var iiii=mydate.getMinutes();var ssss=mydate.getSeconds();var myobj=$(\''.$data['id'].'\');if(myobj){myobj.value=mydate.getFullYear()+\'-\'+(mmmm<10?\'0\'+mmmm:mmmm)+\'-\'+(dddd<10?\'0\'+dddd:dddd)'.($data['time']?'+\' \'+(hhhh<10?\'0\'+hhhh:hhhh)+\':\'+(iiii<10?\'0\'+iiii:iiii)+\':\'+(ssss<10?\'0\'+ssss:ssss)':'').'}" class="btnl" />';
		$tmpinput .= '</span>';
		return $tmpinput;
	}

	public static function changeinput($data,$value='',$extra='',$options=array(),$array_unshift=null,$selected=null,$extra2=''){
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}
		$data['id'] = $data['name'];
		
		if (!is_array($options))$options=array();
		if (is_array($array_unshift)){
			$options = array_merge ($array_unshift,$options);
		}
		return form::input($data,$value,$extra.' style="padding-right:25px;"') .
		'<span style="position:absolute;margin:2px 0 0 -20px;width:18px;height:18px;_height:16px;overflow:hidden;">'.
		'<span style="position:absolute;right:-2px;top:-2px;_right:-1px;_top:-1px;">'.
		form::dropdown(array(),$options,$selected,' onmouseover="this.value=$(\''.$data['id'].'\').value;this.style.width=$(\''.$data['id'].'\').clientWidth+\'px\';" onchange="$(\''.$data['id'].'\').value=this.value;" '.$extra2).
		'</span></span>';
	}
	
	
	/**
	 * 
	 * 弹出式下拉列表
	 * @param string/array $data 表单信息，可以是数组
	 * @param string $value 数据值
	 * @param string/array $extratable 扩展表信息，可以数组，也可以用,分开的字符串。顺序为：数据表,关联字段，显示字段（显示字段可省略）。
	 * 例如：default/news,id,title或array('default/news','id','title')
	 * @param string $extra 表单扩展
	 * @param string $showvalue 显示信息，若留空，则程序自动获取
	 * @param string $expstr 多数据之间分隔符，默认“|”
	 * @param boolean $isappend 是否允许追加，默认为否
	 * @param boolean $isreadonly 是否只读，默认为是，只有显示字段和实际字段为同一字段时才可设为false
	 * @param boolean $addselectbutton 是否添加选择按钮，默认是
	 * @param boolean $addclearbutton 是否添加清除按钮，默认否
	 * @return string 表单字符串
	 */
	public static function pageselect($data,$value,$extratable,$extra='',$showvalue=null,$expstr='|',$isappend=false,$isreadonly=true,$addselectbutton=true,$addclearbutton=false){
		//extract($others);
		$inputstr = '';
		if (!is_array($data)){
			$data = array('name'=>$data);
		}
		if (!is_array($extratable)){
			$extratable = explode(',',$extratable);
		}
		list($dbname,$idfield,$showfield) = $extratable;
		if (!$dbname || !$idfield){
			//对传递错误参数的操作
			return form::input($data,$value,$extra);
		}
		$showfield or $showfield = $idfield;
		$expstr or $expstr = '|';
		
		//hidden表单
		$data['id'] or $data['id'] = '__myqee_input_'.$data['name'];
		$data['type'] = 'hidden';
		$inputstr = form::input($data,$value);
		
		$clickstr = 'showSelectValueFrame(\''.Des::Encrypt(Tools::json_encode(array($data['name'],$dbname,$idfield,$showfield,$expstr,($isappend?1:0) ))).'\')';
		
		//显示用表单
		$datainput = array(
			'id' => $data['id'].'_showinput',
			'class' => 'input',
		);
		if ($isreadonly || ($showfield!=$idfield)){
			$datainput['readonly'] = 'readonly';
		}
		$datainput['onchange'] = '$(\'__myqee_input_'.$data['name'].'\').value=this.value';
		if ($showvalue===null && $dbname && $idfield && $showfield){
			//自动读取显示信息
			if (strpos($dbname,'/')===false){
				$database = 'defalut';
				$tablename = $dbname;
				$dbname = $database.'/'.$tablename;
			}else{
				list($database,$tablename) = explode('/',$dbname,2);
			}
			$info = Database::instance($database);
			$info = $info -> select($showfield.' as v');
			if ($expstr && strpos($value,$expstr)!==false){
				$info = $info -> in($idfield,explode($expstr,$value));
			}else{
				$info = $info -> where($idfield,$value);
			}
			$info = $info -> get($tablename) -> result_array(false);
			$showvalue = '';
			foreach ($info as $v){
				$showvalue .= $expstr.$v['v'];
			}
			$showvalue = trim($showvalue,$expstr);
		}
		if (!$addselectbutton){
			$datainput['onclick'] = $clickstr;
		}
		$inputstr .= form::input($datainput,$showvalue,$extra);
		
		if ($addselectbutton){
			//选择按钮
			$inputstr .= '<input type="button" onclick="'.$clickstr.'" class="btnss" value="选择" />';
		}
		if ($addclearbutton){
			//清除按钮
			$inputstr .= '<input type="button" onclick="$(\'__myqee_input_'.$data['name'].'\').value=$(\'__myqee_input_'.$data['name'].'_showinput\').value=\'\'" class="btnss" value="清除" />';
		}
		return $inputstr;
	}
	
	
	public static function outhtml($configset,$value,$fieldstr='info',$tagname='默认设置'){
		$tag_title_id = 'tag_'.Tools::get_rand(6);
		$tag_main_id = 'main_'.Tools::get_rand(6);
		$title_html = '<div class="mainTable"><ul class="ul tag" id="'.$tag_title_id.'_div">';
		$main_html = '';
		$i=0;
		$count = count($configset);
		$showtag = false;
		foreach ($configset as $key => $item){
			$i++;
			$item['field_width']>0 or $item['field_width'] = 120;
			if ($i==1 || $item['tag_name']){
				if ($i>1){
					$main_html .= '</table></div>'."\r\n";
					$showtag = true;
				}
				$item['tag_name'] and $tagname = $item['tag_name'];
				$title_html .= '<li'.($i==1?' class="now"':'').' id="'.$tag_title_id.'_'.$i.'" onclick="tag(this.id,\''.$tag_title_id.'\',\''.$tag_main_id.'\',\''.$tag_title_id.'_div\');set_tag(\'#tag'.$i.'\');">'.$tagname.'</li>';
				$main_html .= '<div id="'.$tag_main_id.'_'.$i.'"'.($i>1?' style="display:none;"':'').'>
				<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder"><tr><th class="td1" colspan="2">'.$tagname.'</th></tr>';
			}
			
			if (!isset($value[$key])){
				$value[$key] = $item['default'];
			}
			
			if ($item['type']=='hidden'){
				$main_html .= form::edithtml($item,$value[$key],$key,$fieldstr);
			}else{
				$main_html .=	'<tr><td class="td1" align="right" width="'.$item['field_width'].'"><div style="height:1em;"><div style="position:relative;_position:absolute;margin-left:-'.$item['field_width'].'px;width:'.$item['field_width'].'px;overflow:hidden;" onmouseover="this.style.overflow=\'\';" onmouseout="this.style.overflow=\'hidden\';"><font style="background:#E8F3F8;white-space:nowrap;">'.$item['name'].($item['noempty']?'(<font color="red">*</font>)':'').'：</font></div></div></td>'.
								'<td class="td2">'.($item['sub']?form::outhtml($item['sub'],$value[$key],$fieldstr.'['.$key.']',$item['name']):form::edithtml($item,$value[$key],$key,$fieldstr)).($item['description']?' <span class="helpicon" title="'. str_replace(array("\r","\n","\"",'&',),array('<br/>','<br/>','&quot;','&amp;'),$item['description']) .'">&nbsp;</span>':'').'</td></tr>'
				;
			}
			
		}
		$main_html .= '</table></div>'."\r\n";
		
		if ($showtag==false){
			return $main_html;
		}else{
			$title_html .='</ul></div><div style="clear:both"></div>' . $main_html;
			return $title_html;
		}
	}
	
	public static function edithtml($myset,$value=null,$fieldname='',$fieldstr='info'){
		$myset['set']['name'] = $fieldstr.'['.$fieldname.']';
		$myset['set']['id'] or $myset['set']['id'] = '_myqee_input_'.$myset['set']['name'];
		$type = $myset['type'];
		$data = $myset['set'];
		$extra = $data['other'];
		
		unset($data['other']);
		
		if ($myset['usehtml']=='1' && !empty($myset['html'])){
			//自定义html
			$html = str_ireplace(array('{{value}}','{{name}}','{{id}}'),array($value,$fieldname,$data['id']),$myset['html']);
			return $html;
		}elseif($myset['usehtml']=='2'){
			//多维数组编辑
			$myset['adv']['_g']['flag'] = $fieldname;
			$myset['adv']['_g']['name'] = $myset['title'];
			if (isset($value) && !empty($value) && is_array($value)){
				$value = Tools::json_encode($value);
			}else{
				$value = '[]';
			}
			static $runadvinput;
			if ($runadvinput!==true){
				$runadvinput = true;
				$html = '<script type="text/javascript">$import("'.ADMIN_IMGPATH.'/admin/info_edit.js");</script>'."\r\n";
			}else{
				$html = '';
			}
			$fieldnamestr = explode('.',$fieldname);
			$vstr = '';
			foreach ($fieldnamestr as $v){
				$vstr .= '["'.$v .'"]';
				$html2 .= '_advValue'.$vstr.'={};'."\r\n";
				$html2 .= '_advArr'.$vstr.'={};'."\r\n";
			}
			$fieldnamestr = implode('"]["',$fieldnamestr);
			
			$html .= '<script type="text/javascript">'."\r\n".
			'var _mynamestr = "'.$fieldstr.'";'."\r\n".
			$html2.
			'_advValue["'.$fieldnamestr.'"] = '.$value.';'."\r\n".
			'_advArr["'.$fieldnamestr.'"] = '.Tools::json_encode(form::get_advfield_array($myset['adv'],$fieldname,$fieldname,$fieldstr)).';'."\r\n".
			'document.write(add_adv_field(".'.$fieldname.'"));'."\r\n".
			'ini_adv_field(".'.$fieldname.'");'."\r\n".
			'</script>';
			return $html;
		}
		//$options = (array)$this -> dbset['edit'][$fieldname]['candidate'];
		if($getcode=$myset['getcode']){
			static $getcodeclass;
			if ( $getcodeclass===null ){
				 $getcodeclass = new Field_get_Api;
			}
			$options = (array)$getcodeclass -> $getcode($myset['candidate']);
		}elseif (is_array($myset['candidate'])){
			$options = $myset['candidate'];
		}else{
			$options = array();
		}
		
		$html = '';
		switch ($type){
			case 'textarea':
				$data['class'] or $data['class'] = 'input';
				$html = form::textarea($data,$value,$extra);
				break;
			case 'select':
				$value = (string)$value;
				$html = form::dropdown($data,(array)$options,$value,$extra);
				break;
			case 'selectinput':
				$value = (string)$value;
				$html = form::changeinput($data,$value,$extra,(array)$options);
				break;
			case 'pageselect':
				//分页式下拉框,是弹出式的，需要扩展表支持，因为数据时从扩展表中查询出来的
				$fset = array(
					$myset['fdatabase']	.'/' . $myset['ftablename'],
					$myset['ffieldsave'],
					$myset['ffieldshow']
				);
				$html = form::pageselect($myset['fieldname'],$myset['savevalue'],$fset,$myset['savevalue'],null,$myset['isappend'],$myset['isreadonly']);
				break;
			case 'checkbox':
				$tmphtml='';
				if (substr($data['name'],-2,2)!='[]'){
					$data['name'] = $data['name'].'[]';
				}
				
				$value = explode('|',trim($value,'|'));
//				print_r($value);
				foreach ((array)$options as $k1=>$v1){
					if (is_array($value)){
						if (in_array($k1,$value)){
							$chked = TRUE;
						}else{
							$chked = FALSE;
						}
					}else{
						if ($k1==$value){
							$chked = TRUE;
						}else{
							$chked = FALSE;
						}
					}
					$tmphtml .= form::checkbox($data,$k1,$chked,$extra).$v1.' ';
				}
				$html = $tmphtml;
				break;
			case 'radio':
				$tmphtml='';
				foreach ((array)$options as $k1=>$v1){
					$tmphtml .= form::radio($data,$k1,$k1==$value?true:false,$extra).$v1.' ';
				}
				$html = $tmphtml;
				break;
			case 'htmlarea':
				$html = form::htmlarea($data,$value,$extra);
				break;
			case 'basehtmlarea':
				$html = form::basehtmlarea($data,$value,$extra);
				break;
			case 'pagehtmlarea':
				//不能出现2个分页输入框
				static $havepagehtml;
				if (!$havepagehtml){
					$havepagehtml = true;
					$html = form::pagehtmlarea($data,$value,$extra);
				}else{
					$html = form::htmlarea($data,$value,$extra);
				}
				break;
			case 'time':
				$data['time'] = true;
				$html = form::timeinput($data,$value,$extra);
				break;
			case 'date':
				$data['time'] = null;
				$html = form::timeinput($data,$value,$extra);
				break;
			case 'hidden':
				$data['type'] = 'hidden';
				$html = form::input($data,$value,$extra);
				break;
			case 'imginput':
				$html = form::imginput($data,$value,$extra);
				break;
			case 'flash':
				$html = form::flash($data,$value,$extra);
				break;
			case 'classlist':
				if (substr($data['name'],-2,2)!='[]'){
					$data['name'] = $data['name'].'[]';
				}
				$html = form::classlist($data,$options,$extra,$value);
				break;	
			default:
				$data['class'] or $data['class'] = 'input';
				$html = form::input($data,$value,$extra);
		}
		return $html;
	}
	
	
	public function viewhtml($myset,$value='',$fieldname=''){
		$type = $myset['type'];
		if ($type == 'select' || $type =='checkbox' || $type == 'radio'){
			if (in_array($fieldname, (array)$this -> dbset['sys_field'])){
				$trans = array_flip((array)$this -> dbset['sys_field']);
				if ($trans[$fieldname]=='class_id'){
					$dataarray =  $this -> get_class_array($value,'classname');
					return $dataarray['classname'];
				}elseif ($trans[$fieldname]=='template_id'){
					$dataarray = $this -> get_template_array($value,'tplname');
					return $dataarray['tplname'];
				}
			}
			$showdefault = false;
		}else{
			$showdefault = true;
		}
		$options = (array)$this -> dbset['edit'][$fieldname]['candidate'];
		
		
		$html = '';
		switch ($type){
			case 'textarea':
				$html = str_replace("\n",'<br/>',$value);
				break;
			case 'select':
				$html = $options[(string)$value];
				break;
			case 'checkbox':
				$html = $options[(string)$value];
				break;
			case 'radio':
				$html = $options[(string)$value];
				break;
			case 'htmlarea':
				$html = $value;
				break;
			case 'basehtmlarea':
				$html = $value;
				break;
			case 'pagehtmlarea':
				$html = $value;
				break;
			case 'time':
				$html = date("Y-m-d H:i:s",(int)$value);
				break;
			case 'date':
				$html = date("Y-m-d",$value);
				break;
			case 'hidden':
				$html = $value;
				break;
			case 'imginput':
				if (!$value){
					$value = ADMIN_IMGPATH.'/admin/none.gif';
				}elseif(substr($value,0,1)=='/'){
					$value = 'http://'.Myqee::config('core.mysite_domain').'/'.Myqee::config('core.mysite_path').$value;
				}
				$html = '<img src="'.$value.'" />';
				break;
			default:
				$html = $value;
		}
		
		return $html.'&nbsp;';
	}
	
	
	public static function get_advfield_array($arr,$fieldname='',$idstr='',$fieldstr='info'){
		if (!is_array($arr))return array();
		if (!isset($arr['_g'])||!is_array($arr['_g'])){
			//缺少设置属性
			return array();
		}
		
		$myarray=array(
			'_set'=>$arr['_g'],
		);
		if (isset($myarray['_set']['group_auto'])){
			$arr = array('_groupauto'=>$myarray['_set']['group_auto']);
		}
		foreach ($arr as $k => $myset){
			//设置字段
			if ($k=='_g'){
				//或略
				continue;
			}
			
			$newidstr = $idstr.'.'.$k;
			$newfieldname = $fieldname.'][{{.'.$idstr.'}}]['.$k;

			if (isset($myset['_g']) && is_array($myset['_g'])){
				$myarray[$k]['_set'] = $myset['_g'];
				$myarray[$k] = form::get_advfield_array($myset,$newfieldname,$newidstr,$fieldstr);
			}else{
				$myarray[$k] = array(
					'_set' => array(
						'flag'=>$myset['flag'],
						'name'=>$myset['name'],
						'editwidth'=>$myset['editwidth'],
						'type'=>$myset['type'],
						'default'=>$myset['default'],
						'isfield'=>TRUE,
					),
					'_html'=>form::edithtml($myset,NULL,$newfieldname,$fieldstr),
				);
			}
		}
		
		return $myarray;
	}
	
}