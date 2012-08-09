<style type="text/css">
.license {
    border:1px solid #ccc;padding:6px;
    background:#fff;
    background:rgba(255,255,255,0.6);
    height:400px;
    overflow:auto;
}
.license h1 {
    font-size: 14px;
    padding-bottom: 10px;
    text-align: center;
}
.license h3 {
    color: #666666;
    margin: 0;
    font-weight: 700;
    font-size: 12px;
}
.license p {
    line-height: 150%;
    margin: 10px 0;
    text-indent: 25px;
}
</style>
<div class="license">
<?php View::factory('admin/install/license')->render();?>
</div>

<div style="text-align:center;padding:30px;">

<input type="button" class="submit" style="padding: 2px" onclick="document.location='<?php echo Core::url('install/step_1/');?>';" value="我同意" />&nbsp;
<input type="button" onclick="javascript:window.close();return false;" style="padding: 2px" value="我不同意" />
</div>
