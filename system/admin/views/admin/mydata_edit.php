<?php
$page_index = 'info';
$page_title = '数据调用';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>

<div class="loaction"> 您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('mydata/index');?>">数据调用</a> -&gt;
	<?php
if ($iscopy==true){
	echo '复制任务';
}elseif ($isedit == true){
	echo '编辑任务：'.$data['name'];
}else{
	echo '添加任务';
}
?>
</div>
<div class="mainTable">
	<ul class="ul tag" id="mytag">
		<li class="now" id="mytag_1" onclick="tag(this.id,'mytag','mytagmain','mytag');document.location.hash='#tag1'">基本属性</li>
		<li id="mytag_2" onclick="tag(this.id,'mytag','mytagmain','mytag');document.location.hash='#tag2'">查询设置</li>
	</ul>
</div>
<div style="clear: both"></div>
<form action="<?php echo Myqee::url('mydata/save/'.$id);?>" method="POST" target="hiddenFrame">
	<div id="mytagmain_1">
		<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
			<tr>
				<th class="td1" colspan="2">基本设置</th>
			</tr>
			<tr>
				<td class="td1" align="right" width="120">任务名称：</td>
				<td class="td2" align="left"><?php echo form::input('mydata[name]',$data['name'],'class="input" size="30"');?></td>
			</tr>
			<tr>
				<td class="td1" align="right">传入模板变量名称：</td>
				<td class="td2" align="left"><?php echo form::input('mydata[var_name]',$data['var_name'],'class="input" size="12"');?>
				<font color="#999999">留空则默认，默认为:data，只允许数字字母下划线，且字母开头</font>
				</td>
			</tr>
			<tr>
				<td class="td1" align="right">排序(序号)：</td>
				<td class="td2" align="left"><?php echo form::input('mydata[myorder]',$data['myorder'],'class="input" size="5"');?> 
				<font color="#999999">数字越小越靠前</font>
				</td>
			</tr>
			<tr>
				<td class="td1" align="right">是否启用：</td>
				<td class="td2" align="left"><?php echo form::radio('mydata[is_use]',1,!isset($data['is_use']) || (int)$data['is_use']==1);?>是 <?php echo form::radio('mydata[is_use]',0,isset($data['is_use']) && (int)$data['is_use']==0);?>否 </td>
			</tr>
			<tr>
				<td class="td1" align="right">缓存时间：</td>
				<td class="td2" align="left"><?php echo form::input('mydata[cache_time]',(int)$data['cache_time'],'class="input" size="5"');?> 
				<font color="#999999">0表示不缓存，单位：秒。</font>
				</td>
			</tr>
			<tr>
				<td class="td1" align="right">数据调用分类：</td>
				<td class="td2"><?php echo form::changeinput('mydata[cate]',$data['cate'],' size="10" class="input"',$tplcate,array('默认'=>'默认'));?> &nbsp; <font color="#a3a3a3">输入模板分类以区分不同类型模板，留空则为默认分类</font></td>
			</tr>
			<tr>
				<td class="td1" align="right">区块模板：</td>
				<td class="td2" align="left"><?php echo form::dropdown('mydata[template_id]',$template_id,$data['template_id'],'id="mydata[template_id]"'); ?></td>
			</tr>
		</table>
	</div>
	<div id="mytagmain_2" style="display:none;">
		<div style="display:none">
			<ul class="ul tag">
				<li class="now" id="mytag2_0" onclick="if($('mydata[type]').value==1){return;}tag(this.id,'mytag2','mytagmain2');">自动生成</li>
				<li id="mytag2_1" onclick="if($('mydata[type]').value==0){return;}tag(this.id,'mytag2','mytagmain2');">SQL设置</li>
			</ul>
		</div>
		<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
			<tr>
				<th class="td1" colspan="2">查询参数设置</th>
			</tr>
			<tr>
				<td class="td1" width="120" align="right">指定操作方式：</td>
				<td class="td2" align="left"><?php echo form::dropdown('mydata[type]',array('设定查询条件','SQL语句查询'),$data['type'],'id="mydata[type]" onchange="if (this.value==0 || this.value==1){$(\'mytag2_\'+this.value).onclick();}"'); ?> <font color="#999999">选择相应的数据库操作方式请确保相关设置正确。</font></td>
			</tr>
			<tr>
				<td class="td1" align="right">查询设置：</td>
				<td class="td2" align="left">
					<div id="mytagmain2_0">
						<table border="0" cellpadding="4" cellspacing="1" align="left" class="tableborder">
							<tr>
								<th class="td1" colspan="2">查询设置</th>
							</tr>
							<tr>
								<td class="td1" align="right" width="80">头条：</td>
								<td class="td2" align="left"><?php echo form::dropdown('mydata[isheadlines]',array('否','是'),$data['isheadlines'],'id="mydata[isheadlines]" size="1"');?></td>
							</tr>
							<tr>
								<td class="td1" align="right">热门：</td>
								<td class="td2" align="left"><?php echo form::dropdown('mydata[is_hot]',array('否','是'),$data['is_hot'],'id="mydata[is_hot]" size="1"');?></td>
							</tr>
							<tr>
								<td class="td1" align="right">首页显示：</td>
								<td class="td2" align="left"><?php echo form::dropdown('mydata[is_indexshow]',array('否','是'),$data['is_indexshow'],'id="mydata[is_indexshow]" size="1"');?></td>
							</tr>
							<tr>
								<td class="td1" align="right">置顶：</td>
								<td class="td2" align="left"><?php echo form::dropdown('mydata[ontop]',array('','全部置顶','1 级置顶','2 级置顶','3 级置顶','4 级置顶','5 级置顶','6 级置顶','7 级置顶','8 级置顶','9 级置顶'),$data['ontop'],'id="mydata[ontop]" size="1"');?></td>
							</tr>
							<tr>
								<td class="td1" align="right">推荐：</td>
								<td class="td2" align="left"><?php echo form::dropdown('mydata[commend]',array('','全部推荐','1 级推荐','2 级推荐','3 级推荐','4 级推荐','5 级推荐','6 级推荐','7 级推荐','8 级推荐','9 级推荐'),$data['commend'],'id="mydata[commend]" size="1"');?></td>
							</tr>
							<tr>
								<td class="td1" align="right">所属栏目：</td>
								<td class="td2" align="left"><?php echo form::classlist('mydata[classid]',$class,'id="mydata[classid]" onchange="if(this.value==0){$(\'mydata[modelid]\').disabled=false;$(\'mydata[modelid]\').onchange();}else{$(\'mydata[modelid]\').disabled=$(\'mydata[dbname]\').disabled=true;}"',$data['classid'],array('请选择栏目'),true);?> 若选择本项，则下面的“模型”和“数据表”不需要选择，因为每一个栏目都已对应相应的模型和数据表。</td>
							</tr>
							<tr>
								<td class="td1" align="right">所属模型：</td>
								<td class="td2" align="left"><?php echo form::dropdown('mydata[modelid]',(array)$model,$data['modelid'],'id="mydata[modelid]" onchange="if(this.value==0){$(\'mydata[dbname]\').disabled=false;}else{$(\'mydata[dbname]\').disabled=true;}"');?> 若选择栏目，则此项不需要选择。</td>
							</tr>
							<tr>
								<td class="td1" align="right">所属数据表：</td>
								<td class="td2" align="left"><?php echo form::dropdown('mydata[dbname]',(array)$dblist,$data['dbname'],'id="mydata[dbname]"');?> 若选择栏目或模型，则此项不需要选择。</td>
							</tr>
							<tr>
								<td class="td1" align="right">排序方式：</td>
								<td class="td2" align="left"> 按字段 <?php echo form::input('mydata[list_byfield]',$data['list_byfield']?$data['list_byfield']:'id','size="14" class="input" id="mydata[list_byfield]"') ?>
									，排列方式 <?php echo form::dropdown('mydata[list_orderby]',array('DESC'=>'倒序排序','ASC'=>'顺序排序'),$data['list_orderby']); ?></td>
							</tr>
							<tr>
								<td class="td1" align="right">开始位置：</td>
								<td class="td2" align="left">从第&nbsp;<?php echo form::input('mydata[start_number]',$data['start_number'],'id="start_number" class="input" size="6"');?>&nbsp;条开始</td>
							</tr>
							<tr>
								<td class="td1" align="right">条数(条)：</td>
								<td class="td2" align="left"><?php echo form::input('mydata[limit]',$data['limit'],'id="limit" class="input" size="6"');?></td>
							</tr>
						</table>
						<script type="text/javascript">
						$('mydata[classid]').onchange();
						</script>
					</div>
					<div id="mytagmain2_1" style="display:none;">
						<table border="0" cellpadding="4" cellspacing="1" align="left" class="tableborder">
							<tr>
								<th class="td1" colspan="2">SQL设置</th>
							</tr>
							<tr>
								<td class="td1" align="right" width="80">数据表配置：</td>
								<td class="td2" align="left"><?php echo form::dropdown('mydata[table_config]',$dbselect,$data['table_config']);?></td>
							</tr>
							<tr>
								<td class="td1" align="right">SQL语句：</td>
								<td class="td2" align="left"><?php echo form::textarea('mydata[sql]',$data['sql'],'class="input" style="width:92%" cols="80" rows="12"');?><br>
									<div style="color:#999999;padding-top:4px;line-height:1.6em;">只允许查询语句，SQL语句请以<font style="color:red">select</font>开头（忽略大小写）。<br/>
									表前缀可用<font style="color:red">{{table_prefix}}</font>代替，分页用offset可用<font style="color:red">{{offset}}</font>代替，此值默认为0。<br/>
									常用SQL关键字：SELECT,WHERE,FROM,ORDER BY,LIMIT,LIKE,AND,OR。
									</div>
									</td>
							</tr>
						</table>
					</div>
				</td>
			</tr>
		</table>
	</div>
	<script type="text/javascript">
	$('mydata[type]').onchange();
	</script>
	<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-top:none;">
		<tr>
			<td class="td1" width="120">&nbsp;</td>
			<td class="td1" align="left"><input type="submit" class="bbtn" value="保存调用" />
				<input type="button" onclick="goback(-1,'<?php echo Myqee::url('mydata/index');?>')" class="btns" value="返回" /></td>
		</tr>
	</table>
</form>

<script type="text/javascript">
if (document.location.hash =='#tag1'||document.location.hash =='#tag2'){
	tag('mytag_'+document.location.hash.substr(4),'mytag','mytagmain');
}
</script>
<?php View::factory('admin/footer') -> render(TRUE);?>
