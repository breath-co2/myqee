<?php
/**
 * 财付通中介支付接口
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
class PaymentAdapterTenpaymed extends PaymentAdapterAbstract
{
	/**
	 * 构造器
	 *
	 * @param 数组 $config
	 **/
	public function __construct($config = array())
	{
		$this->addRequireItem('mem_id');	 // 商户ID
		$this->addRequireItem('notify_url');	// 交易结果通知URL
		$this->addRequireItem('return_url');	// 交易成功返回页面URL地址
		$this->addRequireItem('code');	// 商户加密钥匙
		if (FALSE == empty($config)) $this->setConfig($config);
		// 强制支付网关地址
		$this->config['gateway_url'] = 'https://www.tenpay.com/cgi-bin/med/show_opentrans.cgi';
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
		$prepare_data['version'] = '2';
		$prepare_data['cmdno'] = 12;
		$prepare_data['encode_type'] = 2;
		$prepare_data['chnid'] = $this->config['mem_id'];
		$prepare_data['seller'] = $this->config['mem_id'];
		$prepare_data['mch_name'] = $this->product_info['name'];
		$prepare_data['mch_price'] = (FALSE == array_key_exists('amount', $this->order_info)) ? intval($this->product_info['price'] * $this->order_info['quantity'] * 100) : $this->order_info['amount'] * 100;
		$prepare_data['transport_desc'] = '';
		if (TRUE == array_key_exists('fee', $this->shipping_info)) $prepare_data['transport_fee'] = $this->shipping_info['fee'];
		$prepare_data['mch_desc'] = $this->product_info['desc'];
		$prepare_data['need_buyerinfo'] = 2;
		$prepare_data['mch_type'] = 1;
		$prepare_data['mch_vno'] = $this->order_info['id'];
		$prepare_data['mch_returl'] = $this->config['notify_url'];
		$prepare_data['show_url'] = $this->config['return_url'];
		$prepare_data['attach'] = $this->product_info['name'];
		$sign_str = '';
		ksort($prepare_data);
		foreach ($prepare_data as $key => $value)
		{
			if ('' != $value) $sign_str .= $key . '=' . $value . '&';
		}
		$sign_str .= 'key=' . $this->config['code'];
		$prepare_data['sign'] = md5($sign_str);

		return $prepare_data;
	}

	/**
	 * 接收通知信息
	 *
	 * @return 数组 返回一个包括 order_id、order_total、order_status 的数组
	 **/
	public function receive()
	{
		$receive_data = $_GET;

		// 验证签名
		ksort($receive_data);
		foreach ($receive_data as $key => $value)
		{
			if ('sign' != $key && '' != $value) $sign_str .= $key . '=' . $value . '&';
		}
		$sign_str .= 'key=' . $this->config['code'];
		if (strtolower(md5($sign_str)) != strtolower($receive_data['sign']))
		{
			$this->logMessage('签名错误: ' . $sign . ' - ' . $receive_data['sign']);
			return array('order_id' => substr($receive_data['mch_vno'], -10), 'order_total' => $receive_data['total_fee'], 'order_status' => 1);
		}
		elseif (FALSE == array_key_exists('retcode', $receive_data) || 0 != $receive_data['retcode'])
		{
			$this->logMessage('返回代码错误');
			return array('order_id' => substr($receive_data['mch_vno'], -10), 'order_total' => $receive_data['total_fee'], 'order_status' => 1);
		}
		else
		{
			$return_data['order_id'] = substr($receive_data['mch_vno'], -10);
			$return_data['order_total'] = intval($receive_data['total_fee']) / 100;
			switch ($receive_data['status'])
			{
				case '1': $return_data['order_status'] = 1; break;
				case '4': $return_data['order_status'] = 2; break;
				case '5': $return_data['order_status'] = 4; break;
				default: $return_data['order_status'] = 1; break;
			}
			return $return_data;
		}
	}

	/**
	 * 响应服务器应答
	 *
	 * @param 布尔值 $result 是否成功收到合法的数据。其值为FALSE将导致远程服务器持续发送应答数据
	 **/
	public function response($result)
	{
		if (TRUE == $result) echo '<meta name="TENCENT_ONLINE_PAYMENT" content="China TENCENT">';
	}
}