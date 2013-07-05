<?php

/**
 * Sae基类
 *
 * STDLib的所有class都应该继承本class,并实现SaeInterface接口
 *
 * @author Easychen <easychen@gmail.com>
 * @version $Id$
 * @package sae
 */

/**
 * SaeObject
 *
 * @package sae
 */
abstract class SaeObject implements SaeInterface
{
	function __construct()
	{
		//
	}

}

/**
 * SaeInterface , public interface of all sae client apis
 *
 * all sae client classes must implement these method for setting accesskey and secretkey , getting error infomation.
 * @package sae
 * @ignore
 **/
interface SaeInterface
{
	public function errmsg();
	public function errno();
}