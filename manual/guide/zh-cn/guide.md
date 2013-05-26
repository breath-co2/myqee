文档编写说明
------

欢迎使用迈启PHP多项目框架
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
    <?php
    $array1 = array(0 => 'zero_a', 2 => 'two_a', 3 => 'three_a');
    $array2 = array(1 => 'one_b', 3 => 'three_b', 4 => 'four_b');
    $result = $array1 + $array2;
    var_dump($result);
    ?>
>   asdfads

------------

> ###参见
>
* <function>array_merge_recursive</function>
* <function>array_combine</function>
* [array operators](language.operators.array.html)

