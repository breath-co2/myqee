Form 表单输出辅助类
==============
通常在视图页面里使用

Form::open($action=null , $attributes = null)
----------

    <?php
    // <form action="/test" method="post">
    echo Form::open('/test');

    // <form action="/test" method="get">
    echo Form::open('/test' , array('method'=>'get'));
    
Form::close()
-----------

    <?php
    // </form>
    echo Form::close();

Form::input($name, $value = null, array $attributes = null)
-----------

    <?php
    // <input type="text" name="test" value="1" />
    echo Form::input('test' , 1 );
    
    // <input type="text" name="test" value="1" size="20" />
    echo Form::input('test' , 1 , array('size'=>20) );
    
    // <input type="text" ie="test" value="1" onclick="alert(1)" />
    echo Form::input( null , 1 , array('id'=>'test' , onclick=>'alert(1)') );

Form::hidden($name, $value = null, array $attributes = null);
------------

    <?php
    // <input type="hidden" name="test" value="123" />
    echo Form::hidden('test',123);

Form::password($name, $value = null, array $attributes = null);
------------

    <?php
    // <input type="password" name="test" value="123" />
    echo Form::password('test',123);

Form::file($name , array $attributes = null);
------------

    <?php
    // <input type="file" name="test" />
    echo Form::file('test');

Form::checkbox($name, $value = null, $checked = false, array $attributes = null)
------------

    <?php
    // <input type="checkbox" name="test[]" value="1" />
    echo Form::checkbox('test[]' , 1 );
    
    // <input type="checkbox" name="test[]" value="1" checked="checked" />
    echo Form::checkbox('test[]' , 1 , true );

Form::radio($name, $value = null, $checked = false, array $attributes = null)
--------------

    <?php
    // <input type="radio" name="test" value="1" />
    echo Form::radio('test' , 1 );
    
    // <input type="radio" name="test" value="1" checked="checked" />
    echo Form::radio('test' , 1 , true );

Form::textarea($name, $body = '', array $attributes = null, $double_encode = false)
---------------

    <?php
    // <textarea name="test">content</textarea>
    echo Form::textarea('test','content');

Form::select($name, array $options = null, $selected = null, array $attributes = null)
--------------

    <?php
    /*
    <select name="test">
    <option value="">请选择</option>
    <option value="1" selected="selected">栏目一</option>
    <option value="2">栏目二</option>
    <option value="3">栏目三</option>
    </select>
    */
    $op = array(
        '' => '请选择',
        1  => '栏目一',
        2  => '栏目二',
        3  => '栏目三',
    );
    echo Form::select('test' , $op , 1 );

多选效果:
<select name="test[]" size="4" multiple="multiple">
<option value="1" selected="selected">栏目一</option>
<option value="2">栏目二</option>
<option value="3" selected="selected">栏目三</option>
</select>

    /*
    多选
    <select name="test[]" size="4" multiple="multiple">
    <option value="1" selected="selected">栏目一</option>
    <option value="2">栏目二</option>
    <option value="3" selected="selected">栏目三</option>
    </select>
    */
    $op = array(
        1  => '栏目一',
        2  => '栏目二',
        3  => '栏目三',
    );
    echo Form::select('test[]' , $op , array(1,3) , array('size'=>4 , 'multiple'=>'multiple' ) );
    
分组功能效果:
<select name="test">
<optgroup label="分组一">
<option value="1">栏目一</option>
<option value="2" selected="selected">栏目二</option>
<option value="3">栏目三</option>
</optgroup>
<optgroup label="分组二">
<option value="4">栏目四</option>
<option value="5">栏目五</option>
</optgroup>
</select>

    /*
    <select name="test">
    <optgroup label="分组一">
    <option value="1">栏目一</option>
    <option value="2" selected="selected">栏目二</option>
    <option value="3">栏目三</option>
    </optgroup>
    <optgroup label="分组二">
    <option value="4">栏目四</option>
    <option value="5">栏目五</option>
    </optgroup>
    </select>
    */
    $op = array(
        '分组一' => array(
            1  => '栏目一',
            2  => '栏目二',
            3  => '栏目三',
        ),
        '分组二' => array(
            4  => '栏目四',
            5  => '栏目五',
        ),
    );
    echo Form::select('test' , $op , 2 );

Form::submit($name , $value, array $attributes = null)
-------------

    <?php
    // <input type="submit" value="提交" />
    echo Form::submit(null , '提交');

Form::image($name, $value, array $attributes = null, $index = false)
-------------

    <?php
    // <input type="image" name="test" src="/test.gif" />
    echo Form::image('test' , null , array('src'=>'test.gif') );

Form::button($name, $body, array $attributes = null)
-------------

    <?php
    // <button name="test">按钮</button>
    echo Form::image('test' , '按钮' );

Form::label($input, $text = null, array $attributes = null)
--------------

    // <label for="username">用户名</label>
    echo Form::label('username', '用户名');
    
    // <label for="test"><input type="checkbox" id="test" name="test" value="1" />测试</label>
    echo Form::label('test', Form::checkbox('test' , 1 , false , array('id'=>'test') ) . '测试' );
    