<?php
/**
 * 支付宝接口
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
class PaymentAdapterAlipay extends PaymentAdapterAbstract
{
	/**
	 * 构造器
	 *
	 * @param 数组 $config 支付宝接口配置参数。该参数至少需要包括 mem_id、notify_url、return_url 和 code 等四个元素
	 **/
	public function __construct($config = array())
	{
		$this->addRequireItem('mem_id');	 // 商户ID
		$this->addRequireItem('notify_url');	// 交易结果通知URL
		$this->addRequireItem('return_url');	// 交易成功返回页面URL地址
		$this->addRequireItem('code');	// 商户加密钥匙
		if (FALSE == empty($config)) $this->setConfig($config);
		// 强制支付网关地址
		$this->config['gateway_url'] = 'http://www.alipay.com/cooperate/gateway.do?_input_charset=utf-8';
		// 强制数据发送方式
		$this->config['gateway_method'] = 'POST';
	}

	/**
	 * 获取订单发送准备数据
	 *
	 * @return 数组
	 **/
	public function getPrepareData()
	{
		$prepare_data['service'] = 'trade_create_by_buyer';
		$prepare_data['partner'] = $this->config['mem_id'];
		$prepare_data['notify_url'] = $this->config['notify_url'];
		$prepare_data['return_url'] = $this->config['return_url'];
		$prepare_data['sign_type'] = 'MD5';
		// 商品信息
		$prepare_data['subject'] = $this->product_info['name'];
		if (TRUE == array_key_exists('desc', $this->product_info)) $prepare_data['body'] = $this->product_info['desc'];
		$prepare_data['price'] = $this->product_info['price'];
		if (TRUE == array_key_exists('url', $this->product_info)) $prepare_data['show_url'] = $this->product_info['url'];
		// 订单信息
		if (TRUE == array_key_exists('discount', $this->order_info) && FALSE == empty($this->order_info['discount'])) $prepare_data['discount'] = $this->order_info['discount'];
		$prepare_data['out_trade_no'] = $this->order_info['id'];
		$prepare_data['quantity'] = $this->order_info['quantity'];
		$prepare_data['payment_type'] = 1;
		// 物流信息
		$prepare_data['logistics_type'] = $this->shipping_info['type'];
		$prepare_data['logistics_fee'] = $this->shipping_info['fee'];
		$prepare_data['logistics_payment'] = $this->shipping_info['payment'];
		// 收货人信息
		$prepare_data['receive_name'] = $this->customer_info['name'];
		$prepare_data['receive_address'] = $this->customer_info['address'];
		$prepare_data['receive_zip'] = $this->customer_info['postcode'];
		if (TRUE == array_key_exists('tel', $this->customer_info)) $prepare_data['receive_phone'] = $this->customer_info['tel'];
		if (TRUE == array_key_exists('mobile', $this->customer_info)) $prepare_data['receive_mobile'] = $this->customer_info['mobile'];
		// 卖家信息
		$prepare_data['seller_id'] = $this->config['mem_id'];
		// 数字签名
		ksort($prepare_data);
		$sign_strs = array();
		foreach ($prepare_data as $key => $value)
		{
			if (NULL != $value && '' != $value) $sign_strs[] = $key . '=' . $value;
		}
		$prepare_data['sign'] = md5(implode('=', $sign_strs));
		return $prepare_data;
	}

	/**
	 * 接收通知信息
	 *
	 * @return 数组 返回一个包括 order_id、order_total、order_status 的数组
	 **/
	public function receive()
	{
		$receive_data = $this->filterParameter($_POST);
		ksort($receive_data);
		reset($receive_data);
		if (FALSE == empty($receive_data))
		{
			$verify_result = $this->remoteGet('GET', 'http://notify.alipay.com/trade/notify_query.do?partner=' . $this->config['mem_id'] . '&notify_id=' . $receive_data['notify_id']);
			if (FALSE == preg_match('/true$/', $verify_result))
			{
				$this->logMessage($this->log_file, '非法通知: ' . serialize($receive_data));
				return array('order_id' => $receive_data['out_trade_no'], 'order_total' => $receive_data['total_fee'], 'order_status' => 1);
			}
			else
			{
				$args = '';
				foreach ($receive_data as $key => $value) $args .= $key . '=' . $value . '&';
				$args = substr($args, 0, -1);
				$sign = md5($args . $this->config['code']);
				if ($sign != $receive_data['sign'])
				{
					$this->logMessage($this->log_file, '签名错误: ' . $sign . ' - ' . $receive_data['sign']);
					return array('order_id' => $receive_data['out_trade_no'], 'order_total' => $receive_data['total_fee'], 'order_status' => 1);
				}
				else
				{
					$return_data['order_id'] = $receive_data['out_trade_no'];
					$return_data['order_total'] = $receive_data['total_fee'];
					switch ($receive_data['trade_status'])
					{
						case 'WAIT_BUYER_PAY': $return_data['order_status'] = 1; break;
						case 'WAIT_SELLER_SEND_GOODS': $return_data['order_status'] = 2; break;
						case 'WAIT_BUYER_CONFIRM_GOODS': $return_data['order_status'] = 3; break;
						case 'TRADE_FINISHED': $return_data['order_status'] = 4; break;
					}
					return $return_data;
				}
			}
		}
	}

	/**
	 * 响应服务器应答
	 *
	 * @param 布尔值 $result 是否成功收到合法的数据。其值为FALSE将导致远程服务器持续发送应答数据
	 **/
	public function response($result)
	{
		if (FALSE == $result) echo 'fail';
		else echo 'success';
	}

	/**
	 * 过滤参数
	 *
	 * @param 数组 $parameter
	 * @return 数组
	 **/
	private function filterParameter($parameter)
	{
		$para = array();
		foreach ($parameter as $key => $value)
		{
			if ('sign' == $key || 'sign_type' == $key || '' == $value) continue;
			else $para[$key] = $value;
		}
		return $para;
	}
}