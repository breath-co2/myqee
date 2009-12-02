<?php
/**
 * 支付接口整合入口
 *
 *  Copyright (C) 2009, 2010  feeling@farcore.com.cn
 *   This library is free software; you can redistribute it and/or
 *   modify it under the terms of the GNU Library General Public
 *   License as published by the Free Software Foundation; either
 *   version 2 of the License, or (at your option) any later version.
 *
 *   This library is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 *   Library General Public License for more details.
 *
 *   You should have received a copy of the GNU Library General Public
 *   License along with this library; if not, write to the Free
 *   Software Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 *
 * @author feeling<feeling@farcore.com.cn>
 * @version 1.0
 *
 * edited by jonwang<rovewang@hotmail.com> @ 2009.12
 * 
 * @example
 * 
 * 获取支付接口表单
 * 此页面通常要在数据库里创建一个订单
 * <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
 * Myqee::api_load('payment','Payment');
 * $payment = new Payment('Tenpaymed');
 * $formdata = $payment -> getForm();
 * unset($payment);
 * >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
 * 此时，$formdata将获得一个数组，类似于：
 * array(
 *  'url'=>'支付接口地址',
 *  'method'=>'POST',
 *  'input'=>'<input type="hidden" name="" value="" /><input type="hidden" name="" value="" />...',
 * );
 * 可根据自己的需要传给视图
 * 
 * 
 * 
 * 通知页面，用于被支付接口调用，并处理订单
 * <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
 * Myqee::api_load('payment','Payment');
 * $payment = new Payment('Tenpaymed');
 * $do_result = FALSE;
 * $notify_info = $payment->receive();
 * if (FALSE == array_key_exists('order_status', $notify_info) || FALSE == array_key_exists('order_id', $notify_info)) $do_result = FALSE;
 * elseif (4 === $notify_info['order_status'])
 * {// 订单成功支付的状态
 * 	// 根据$notify_info['order_id']订单ID查询相关订单，修改订单状态
 * 	// 如果处理成功$do_result = TRUE;
 * 	// 否则$do_result = FALSE;
 * }else $do_result = TRUE;	//如果订单支付失败
 * $payment->response($do_result);
 * unset($payment);
 * >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
 *
 *
 *
 *
 * 返回页面就是处理成功后跳转到的页面，按网站自己需求加载视图即可
 * 
 * 支付配置在application/config/payment.php中
 **/

class Payment
{
	/**
	 * 文件所在目录位置
	 *
	 * @var 字符串
	 **/
	private $current_path;

	/**
	 * 支付接口适配器实例
	 *
	 * @var PaymentAdapterAbstract
	 **/
	private $adapter_instance;

	/**
	 * 构造器
	 *
	 * @param 字符串 $adapter_name 支付接口适配器名称
	 * @param 数组 $adapter_config 支付接口适配器配置参数列表
	 **/
	public function __construct($adapter_name = '', $adapter_config = array())
	{
		$this->current_path = dirname(__FILE__);
		include_once($this->current_path . DIRECTORY_SEPARATOR . 'Abstract.php');
		
		if (FALSE == empty($adapter_name)) {
			if (!is_array($adapter_config)||!count($adapter_config)){
				$payconfig = Myqee::config('payment');
				$adapter_config = $payconfig['api'][strtolower($adapter_name)];
				if (!$adapter_config)throw new Error_Exception('Invalid adapter name');
				
				$adapter_config['notify_url'] or $adapter_config['notify_url'] = $payconfig['notify_url'];
				$adapter_config['return_url'] or $adapter_config['return_url'] = $payconfig['return_url'];
			}
			$this->setAdapter($adapter_name, $adapter_config);
		}
	}

	/**
	 * 设置当前使用的支付接口适配器
	 *
	 * @param 字符串 $adapter_name 支付接口适配器名称
	 * @param 数组 $adapter_config 支付接口适配器参数列表
	 * @return PaymentAdapterAbstract
	 * @exception PaymenException
	 **/
	public function setAdapter($adapter_name, $adapter_config = array())
	{
		if (FALSE == is_string($adapter_name)) throw new Error_Exception('Invalid adapter name');
		else
		{
			$adapter_name = ucwords(strtolower($adapter_name));
			$class_name = 'PaymentAdapter' . $adapter_name;
			
			Myqee::api_load('payment/adapter',$class_name);
			$this->adapter_instance = new $class_name($adapter_config);
			if (FALSE == is_subclass_of($class_name, 'PaymentAdapterAbstract')) throw new Error_Exception(sprintf('Invalida adapter', $adapter_name));
		}
		return $this->adapter_instance;
	}

	/**
	 * Magic: 方法调用
	 *
	 * @param 字符串 $method_name
	 * @param 数组 $method_args
	 **/
	public function __call($method_name, $method_args)
	{
		if (TRUE == method_exists($this, $method_name))
			return call_user_func_array(array(& $this, $method_name), $method_args);
		elseif (
			FALSE == empty($this->adapter_instance)
			&& TRUE == ($this->adapter_instance instanceof PaymentAdapterAbstract)
			&& TRUE == method_exists($this->adapter_instance, $method_name)
		) return call_user_func_array(array(& $this->adapter_instance, $method_name), $method_args);
		else throw new Error_Exception(sprintf('Tryied to call unknown method: %s', $method_name));
	}
}