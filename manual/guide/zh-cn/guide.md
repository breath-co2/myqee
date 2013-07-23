# 文档编写、生成说明

MyQEE的文档采用流行的Markdown格式，后缀为.md，Mac系统推荐使用[Mou](http://mouapp.com/)程序编写，最终可生成html文件。

主文档存放在 `manual/guide/` 目录下。

`menu.md` 为特殊文件，可以自定义设置当前文档的目录菜单，支持2级列表结构。

每个类库、项目都可以定义自己的文档，可在对应目录的guide目录中编写，比如核心类库目录`core/guide/`中就存放在核心类库的.md文档

## 如何重新生成文档html文件

进入manual/bin/目录，执行 `./re-create` 命令，或 `php re-create`，本脚本用php编写，所以需要安装PHP，如果是window系统，则可执行 `php.exe re-create`

执行完毕后，文档将会全部生成到 `manual/html` 目录中，可以直接点击 `index.html` 进行访问。

### 希望在站点里直接访问文档怎么做？

* window下可以把整个html目录复制到wwwroot目录下并改名，比如docs,这样可通过类似 `http://yourhost/docs/` 来直接访问到
* linux, mac下推荐建立连接，首先cd到wwwroot目录，然后执行 `ln -s ../manual/html/ docs` 这样就建立了一个文件夹连接，就可以通过URL直接访问了



## 以下为md的一些样例

代码

	<?php
	phpinfo();


!!! **注意**内容 

> ###说明
> 
>     array array_merge ( array $array1 [, array $... ] )
>
> **array_merge()** 将一个或多个数组的单元合并起来，一个数组中的值附加在前一个数组的后面。返回作为结果的数组。
>
> 如果输入的数组中有相同的字符串键名，则该键名后面的值将覆盖前一个值。然而，如果数组包含数字键名，后面的值将不会覆盖原来的值，而是附加到后面。
>
> 如果只给了一个数组并且该数组是数字索引的，则键名会以连续方式重新索引。  

-----------

> ###参数
>
> > array1
> > 
> >      Initial array to merge.
> >
> > `…`
> >
> >      Variable list of arrays to merge.
> >

------------

> ###返回值
> 
> 返回结果数组。

&nbsp;

> ###更新日志
> 
>    | 版本 | 说明
>    |-----|-----
>    | 5.0.0 | **array_merge()** 的行为在 PHP 5 中被修改了。和 PHP 4 不同， array_merge() 现在只接受 array 类型的参数。不过可以用强制转换来合并其它类型。请看下面的例子。
>
| Left align | Right align | Center align |
|:-----------|------------:|:------------:|
| This       |        This |     This     |
| column     |      column |    column    |
| will       |        will |     will     |
| be         |          be |      be      |
| left       |       right |    center    |
| aligned    |     aligned |   aligned    |

----------

> ###测试 
>
>
    <?php
    $array1 = array(0 => 'zero_a', 2 => 'two_a', 3 => 'three_a');
    $array2 = array(1 => 'one_b', 3 => 'three_b', 4 => 'four_b');
    $result = $array1 + $array2;
    var_dump($result);
    ?>
>   内容

------------

> ###参见
>
* <function>array_merge_recursive</function>
* <function>array_combine</function>
* [array operators](language.operators.array.html)

