<?php
/**
 * 财付通即时到账接口
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
class PaymentAdapterTenpay extends PaymentAdapterAbstract
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
		$this->config['gateway_url'] = 'https://www.tenpay.com/cgi-bin/v1.0/pay_gate.cgi';
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
		$prepare_data['cmdno'] = 1;
		$prepare_data['date'] = date('Ymd');
		$prepare_data['bank_type'] = 0;
		$prepare_data['desc'] = $this->product_info['name'];
		$prepare_data['bargainor_id'] = $this->config['mem_id'];
		$prepare_data['transaction_id'] = $this->config['mem_id'] . date('Ymd') . sprintf('%010s', $this->order_info['id']);
		$prepare_data['sp_billno'] = $this->order_info['id'];
		$prepare_data['total_fee'] = (FALSE == array_key_exists('amount', $this->order_info)) ? intval($this->product_info['price'] * $this->order_info['quantity'] * 100) : $this->order_info['amount'] * 100;
		$prepare_data['fee_type'] = '1';
		$prepare_data['return_url'] = $this->config['notify_url'];
		$prepare_data['attach'] = '1';
		$prepare_data['spbill_create_ip'] = $this->getClientIp();//'124.161.252.243';
		$prepare_data['sign'] = strtoupper(md5('cmdno=1&date=' . $prepare_data['date'] . '&bargainor_id=' . $prepare_data['bargainor_id'] . '&transaction_id=' . $prepare_data['transaction_id'] . '&sp_billno=' . $prepare_data['sp_billno'] . '&total_fee=' . $prepare_data['total_fee'] . '&fee_type=' . $prepare_data['fee_type'] . '&return_url=' . $this->config['notify_url'] . '&attach=' . $prepare_data['attach'] . '&spbill_create_ip=' . $prepare_data['spbill_create_ip'] . '&key=' . $this->config['code']));
		$prepare_data['cs'] = 'utf-8';

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
		// cmdno=1&pay_result=0&date=20051220&transaction_id=1000000301200512200000000004& sp_billno=k0000000001&total_fee=100& fee_type=1&attach=test_attach&key=1000000301
		$sign = strtoupper(md5('cmdno=' . $receive_data['cmdno'] . '&pay_result=' . $receive_data['pay_result'] . '&date=' . $receive_data['date'] . '&transaction_id=' . $receive_data['transaction_id'] . '&sp_billno=' . $receive_data['sp_billno'] . '&total_fee=' . $receive_data['total_fee'] . '&fee_type=' . $receive_data['fee_type'] . '&attach=' . $receive_data['attach'] . '&key=' . $this->config['key']));
		if ($sign != strtoupper($receive_data['sign']))
		{
			$this->logMessage($this->log_file, '签名错误: ' . $sign . ' - ' . $receive_data['sign']);
			return array('order_id' => substr($receive_data['transaction_id'], -10), 'order_total' => $receive_data['total_fee'], 'order_status' => 1);
		}
		else
		{
			$return_data['order_id'] = substr($receive_data['transaction_id'], -10);
			$return_data['order_total'] = $receive_data['total_fee'];
			switch ($receive_data['pay_result'])
			{
				case '0': $return_data['order_status'] = 4; break;
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
		if (TRUE == $result) echo "<meta name=\"TENCENT_ONLINE_PAYMENT\" content=\"China TENCENT\">\n<html><script language=javascript>\nwindow.location.href='{$this->config['return_url']}';\n</script>\n</html>";
	}

	/**
	 * 获取客户端IP
	 *
	 * @return 字符串 返回客户端的IP，如果获取失败则返回 0.0.0.0
	 **/
	protected function getClientIp()
	{
		if (TRUE == array_key_exists('REMOTE_ADDR', $_SERVER) AND TRUE == array_key_exists('HTTP_CLIENT_IP', $_SERVER)) $ip_address = $_SERVER['HTTP_CLIENT_IP'];
		elseif (TRUE == array_key_exists('REMOTE_ADDR', $_SERVER)) $ip_address = $_SERVER['REMOTE_ADDR'];
		elseif (TRUE == array_key_exists('HTTP_CLIENT_IP', $_SERVER)) $ip_address = $_SERVER['HTTP_CLIENT_IP'];
		elseif (TRUE == array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];

		if (FALSE == isset($ip_address))
		{
			$ip_address = '0.0.0.0';
			return $ip_address;
		}

		if (strstr($ip_address, ','))
		{
			$x = explode(',', $ip_address);
			$ip_address = end($x);
		}
		return $ip_address;
	}
}