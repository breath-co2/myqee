<?php
/**
 * 支付接口适配器
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
 **/
abstract class PaymentAdapterAbstract
{
	protected $require_config_items = array();
	protected $config = array();
	protected $product_info = array();
	protected $customter_info = array();
	protected $order_info = array();
	protected $shipping_info = array();

	/**
	 * 设置支付接口适配器参数
	 *
	 * @param 数组 $config
	 * @return PaymentAdapterAbstract
	 * @exception PaymentAdapterException
	 **/
	public function setConfig($config)
	{
		foreach ($this->require_config_items as $config_item_name)
		{
			if (FALSE == array_key_exists($config_item_name, $config)) throw new Error_Exception(sprintf('The require config item was not existed', $config_item_name));
		}
		foreach ($config as $key => $value) $this->config[$key] = $value;
		return $this;
	}

	/**
	 * 添加必要配置参数名称
	 *
	 * @param 数组|字符串 $require_items
	 * @return PaymentAdapterAbstract
	 **/
	protected function addRequireItem($require_items)
	{
		if (TRUE == is_array($require_items))
		{
			foreach ($require_items as $require_item) $this->addRequireItem($require_item);
		}
		elseif (TRUE == is_string($require_items)) $this->require_config_items[] = $require_items;
		else throw new Error_Exception('Invalid require item name');
		return $this;
	}

	/**
	 * 设置商品信息
	 *
	 * @param 数组 $product_info 一个包括 name、desc、price、url 的数组
	 * @return PaymentAdapterAbstract
	 * @exception PaymentAdapterException
	 **/
	public function setProductInfo($product_info)
	{
		$require_items = array('name', 'desc', 'price', 'url');
		foreach ($require_items as $require_item)
		{
			if (FALSE == array_key_exists($require_item, $product_info)) throw new Error_Exception(sprintf('Paramter %s for the product had not been set', $require_item));
		}
		$this->product_info = $product_info;
		return $this;
	}

	/**
	 * 设置收货人信息
	 *
	 * @param 数组 $customer_info 一个包括  name、address、postcode、tel、mobile 的数组
	 * @return PaymentAdapterAbstract
	 * @exception PaymentAdapterException
	 **/
	public function setCustomerInfo($customer_info)
	{
		$require_items = array('name', 'address', 'postcode', 'tel', 'mobile');
		foreach ($require_items as $require_item)
		{
			if (FALSE == array_key_exists($require_item, $customer_info)) throw new Error_Exception(sprintf('Paramter %s for the customer had not been set', $require_item));
		}
		$this->customer_info = $customer_info;
		return $this;
	}

	/**
	 * 设置订单信息
	 *
	 * @param 数组 $order_info 一个包括 id、discount、quantity、date、amount 的数组
	 * @return PaymentAdapterAbstract
	 * @exception PaymentAdapterException
	 **/
	public function setOrderInfo($order_info)
	{
		$require_items = array('id', 'discount', 'quantity', 'date', 'amount');
		foreach ($require_items as $require_item)
		{
			if (FALSE == array_key_exists($require_item, $order_info)) throw new Error_Exception(sprintf('Paramter %s for the order had not been set', $require_item));
		}
		$this->order_info = $order_info;
		return $this;
	}

	/**
	 * 设置物流信息
	 *
	 * @param 数组 $shipping_info 一个包括 type、fee、payment 的数组
	 * @return PaymentAdapterAbstract
	 * @exception PaymentAdapterException
	 **/
	public function setShippingInfo($shipping_info)
	{
		$require_items = array('type', 'fee', 'payment');
		foreach ($require_items as $require_item)
		{
			if (FALSE == array_key_exists($require_item, $shipping_info)) throw new Error_Exception(sprintf('Paramter %s for the shipping had not been set', $require_item));
		}
		$this->shipping_info = $shipping_info;
		return $this;
	}

	/**
	 * 获取发送订单信息的Form信息
	 *
	 * @return 字符串
	 **/
	public function getForm()
	{
		$str = '';
		$prepare_data = $this->getPrepareData();
		foreach ($prepare_data as $key => $value) $str .= '<input type="hidden" name="' . $key . '" value="' . $value . '" />';
		return array(
			'url'		=> $this->config['gateway_url'],
			'method'	=> 'POST' == strtoupper($this->config['gateway_method'])?'POST':'GET',
			'input'		=> $str,
		);
	}

	/**
	 * 记录日志
	 *
	 * @param 字符串 $content 需要记录的内容
	 **/
	protected function logMessage($content)
	{
		return true;
		/*$file_id = @fopen($this->log_file, 'a+b');
		@flock($file_id, LOCK_EX);
		@fwrite($file_id, date('Y-m-d H:i:s') . ': ' . $content);
		@flock($file_id, LOCK_UN);
		@fclose($file_id);*/
	}

	/**
	 * 设置日志保存目录
	 *
	 * @param 字符串 $log_dir
	 * @param PaymentAdapterAbstract
	 **/
	public function setLogDirectory($log_dir)
	{
		//if (TRUE == @file_exists($log_dir) && TRUE == @is_dir($log_dir)) $this->log_dir = $log_dir;
		return $this;
	}

	/**
	 * 设置日志保存文件
	 *
	 * @param 字符串 $log_file
	 * @param PaymentAdapterAbstract
	 **/
	public function setLogFile($log_file)
	{
		if ('' != dirname($log_file)) $this->log_file = $log_file;
		else $this->log_file = $this->log_dir . DIRECTORY_SEPARATOR . $log_file;
		return $this;
	}

	/**
	 * 远程获取指定地址的内容
	 *
	 * @param 字符串 $method 获取方式。为"GET"或“POST”之一
	 * @param 字符串 $remote_address 需要获取的远程地址
	 * @param 数组 $data 需要发送的数据列表
	 **/
	protected function remoteGet($method, $remote_address, $data = array())
	{
		$error_no = 0;
		$error_str = '';
		$url_parsed = parse_url($remote_address);
		if ('https' == $url_parsed['scheme'])
		{
			$address_url = 'ssl://';
			if (TRUE == empty($url_parsed['port'])) $url_parsed['port'] = 443;
		}
		else
		{
			$address_url = 'tcp://';
			if (TRUE == empty($url_parsed['port'])) $url_parsed['port'] = 80;
		}
		foreach ($data as $key => $value) $url_parsed['query'] .= '&' . $key . '=' . $value;
		$file_id = @fsockopen($address_url . $url_parsed['host'], $url_parsed['port'], $error_no, $error_str, 60);
		if (FALSE == $file_id) die('ERROR: ' . $error_no . ' - ' . $error_str);
		else
		{
			if ('POST' == strtoupper($method)) fputs($file_id, "POST ".$url_parsed["path"]." HTTP/1.1\r\n");
			else fputs($file_id, 'HEAD ' . (TRUE == isset($url_parsed['path']) ? $url_parsed['path'] : '/') . (TRUE == isset($url_parsed['query']) ? '?' . $url_parsed['query'] : '') . " HTTP/1.1\r\n");
			fputs($file_id, "Host: ".$url_parsed["host"]."\r\n");
			if ('POST' == strtoupper($method))
			{
				fputs($file_id, "Content-type: application/x-www-form-urlencoded\r\n");
				fputs($file_id, "Content-length: ". strlen($url_parsed["query"]) ."\r\n");
			}
			fputs($file_id, "Connection: close\r\n\r\n");
			fputs($file_id, $url_parsed["query"] . "\r\n\r\n");
			$info = '';
			while (FALSE != @feof($file_id)) $info .= @fgets($file_id, 1024);
			@fclose($file_id);
			return $info;
		}
	}

	/**
	 * 回调方法
	 **/
	abstract public function receive();

	/**
	 * 返回响应内容
	 **/
	abstract public function response($result);

	/**
	 * 获取订单发送准备数据
	 *
	 * @return 数组
	 **/
	abstract public function getPrepareData();
}