<?php

/**
 * Created by PhpStorm.
 * User: djunny
 * Date: 2015-09-18
 * Time: 17:51
 */
class pay_yeepay {
    /**
     * @var int
     */
    private $p1_MerId = 0;
    /**
     * @var string
     */
    private $merchantKey = '';

    private $conf;

    /**
     * @var string
     */
    private $api_url = 'https://www.yeepay.com/app-merchant-proxy/node';

    /**
     * @var string
     */
    private $refund_url = 'https://cha.yeepay.com/app-merchant-proxy/command';

    /*
    requestURL=https://www.yeepay.com/app-merchant-proxy/node
    #订单查询请求地址
    queryURL=https://cha.yeepay.com/app-merchant-proxy/command
    #退款请求地址
    refundURL=
    #退款查询请求地址
    refundQueryURL=https://www.yeepay.com/app-merchant-proxy/node
    #订单取消请求地址
    cancelOrderURL=https://cha.yeepay.com/app-merchant-proxy/command
    */

    function __construct($conf) {
        $this->set_conf($conf);
    }

    /**
     * set yeepay conf
     *
     * @param $conf
     */
    function set_conf(&$conf) {
        $this->p1_MerId = $conf['yeepay']['p1_MerId'];
        $this->merchantKey = $conf['yeepay']['merchantKey'];
        $this->conf = &$conf;
    }

    /**
     *  build array for form
     *
     * @param $post
     * @return array
     */
    function get_form_array($post) {
        // build array
        $params = array(
            'p0_Cmd' => 'Buy',
            'p1_MerId' => $this->p1_MerId,
            'p2_Order' => $post['id'],
            'p3_Amt' => $post['price'],
            'p4_Cur' => 'CNY',
            'p5_Pid' => $post['title'],
            'p6_Pcat' => $post['type'],
            'p7_Pdesc' => $post['desc'],
            'p8_Url' => $post['callback'],
            'p9_SAF' => 0,
            'pa_MP' => $post['user_id'],
            'pd_FrpId' => '',
            'pr_NeedResponse' => 1,
            '_' => array(
                'submit_url' => $this->api_url,
                // charset for form submit
                'charset' => 'gbk',
            ),
        );

        // get hmac hash
        $params['hmac'] = $this->get_req_hmac($params['p0_Cmd'], $params['p2_Order'],
            $params['p3_Amt'],
            $params['p4_Cur'],
            $params['p5_Pid'],
            $params['p6_Pcat'],
            $params['p7_Pdesc'],
            $params['p8_Url'],
            $params['p9_SAF'],
            $params['pa_MP'],
            $params['pd_FrpId'],
            $params['pr_NeedResponse']);
        //
        return $params;
    }

    /**
     * @return array
     */
    function response($control) {
        $return = array(
            'info' => '',
            'price' => 0,
            'id' => 0,
            'succ' => false,
            'response' => '',
        );
        $this->get_callback_value(&$r0_Cmd, &$r1_Code, &$r2_TrxId, &$r3_Amt, &$r4_Cur, &$r5_Pid, &$r6_Order, &$r7_Uid, &$r8_MP, &$r9_BType, &$hmac);

        $hmac_match = $this->check_hmac($r0_Cmd, $r1_Code, $r2_TrxId, $r3_Amt, $r4_Cur, $r5_Pid, $r6_Order, $r7_Uid, $r8_MP, $r9_BType, $hmac);
        if ($hmac_match) {
            if ($r1_Code == "1") {
                $info = array_merge($_GET, $_REQUEST, $_POST);
                unset($info['FORM_HASH'], $info['a'], $info['c'], $info['rewrite']);
                foreach ($info as $k => $v) {
                    $info[$k] = iconv('gbk', 'utf-8', $v);
                }
                $return['succ'] = 1;
                $return['id'] = $r6_Order;
                $return['data'] = $info;
                $return['price'] = $r3_Amt;
                #并且需要对返回的处理进行事务控制，进行记录的排它性处理，在接收到支付结果通知后，
                #判断是否进行过业务逻辑处理，不要重复进行业务逻辑处理，防止对同一条交易重复发货的情况发生.
                if ($r9_BType == "2") {
                    #如果需要应答机制则必须回写流,以success开头,大小写不敏感.
                    $return['response'] = "success\r\n";
                }
            }
        } else {
            $return['message'] = 'SignError(' . $r2_TrxId . ')';
        }
        return $return;
    }

    function query($orderid) {
        $p0_Cmd = "QueryOrdDetail";
        #	进行签名处理，一定按照文档中标明的签名顺序进行
        $sbOld = "";
        #	加入订单查询请求，固定值"QueryOrdDetail"
        $sbOld = $sbOld . $p0_Cmd;
        #	加入商户编号
        $sbOld = $sbOld . $this->p1_MerId;
        #	加入商户订单号
        $sbOld = $sbOld . $orderid;
        $hmac = $this->hmac_md5($sbOld, $this->merchantKey);
        $params = array(
            'p0_Cmd' => $p0_Cmd,
            #	商户编号
            'p1_MerId' => $this->p1_MerId,
            #	商户订单号
            'p2_Order' => $orderid,
            #	校验码
            'hmac' => $hmac,
        );

        $data = spider::POST($this->api_url, $params);
        $lines = explode("\n", $data);
        $result = array();
        foreach ($lines as $line) {
            list($key, $val) = explode('=', trim($line), 2);
            if ($key) {
                $result[$key] = urldecode($val);
            }
        }

        #进行校验码检查 取得加密前的字符串
        $sbOld = "";
        #加入业务类型
        $sbOld = $sbOld . $result['r0_Cmd'];
        #加入查询操作是否成功
        $sbOld = $sbOld . $result['r1_Code'];
        #加入易宝支付交易流水号
        $sbOld = $sbOld . $result['r2_TrxId'];
        #加入支付金额
        $sbOld = $sbOld . $result['r3_Amt'];
        #加入交易币种
        $sbOld = $sbOld . $result['r4_Cur'];
        #加入商品名称
        $sbOld = $sbOld . $result['r5_Pid'];
        #加入商户订单号
        $sbOld = $sbOld . $result['r6_Order'];
        #加入商户扩展信息
        $sbOld = $sbOld . $result['r8_MP'];
        #加入支付状态
        $sbOld = $sbOld . $result['rb_PayStatus'];
        #加入已退款次数
        $sbOld = $sbOld . $result['rc_RefundCount'];
        #加入已退款金额
        $sbOld = $sbOld . $result['rd_RefundAmt'];

        $hash_hmac = $this->hmac_md5($sbOld, $this->merchantKey, 'gbk');

        if ($result) {
            if ($hash_hmac != $result['hmac']) {
                log::info('hmacError', $hash_hmac, $result['hmac']);
                return array();
            }
            return array(
                'ispaied' => $result['rb_PayStatus'] == 'SUCCESS' ? 1 : 0,
                'iscacnel' => $result['rb_PayStatus'] == 'CANCELED' ? 1 : 0,
                'isrefund' => $result['rz_RefundAmount'] > 0 ? 1 : 0,
                'data' => $result,
            );
        }

        return array();
    }

    function refund($invest, $data) {
        $p0_Cmd = "RefundOrd";
        #易宝支付交易流水号
        $pb_TrxId = '';
        foreach ($data as $key => $val) {
            if (strpos($key, '_TrxId') !== false) {
                $pb_TrxId = $val;
            }
        }
        $p3_Amt = $invest['price'];                    #退款金额
        $p4_Cur = "CNY";                                        #交易币种,固定值"CNY".
        $p5_Desc = $data['p5_Desc'];              #详细描述退款原因的信息.

        #	进行签名处理，一定按照文档中标明的签名顺序进行
        $sbOld = "";
        #	加入订单查询请求
        $sbOld = $sbOld . $p0_Cmd;
        #	加入商户编号
        $sbOld = $sbOld . $this->p1_MerId;
        #	加入易宝支付交易流水号
        $sbOld = $sbOld . $pb_TrxId;
        #	加入退款金额
        $sbOld = $sbOld . $p3_Amt;
        #	加入交易币种
        $sbOld = $sbOld . $p4_Cur;
        #	加入退款说明
        $sbOld = $sbOld . $p5_Desc;

        $hmac = $this->hmac_md5($sbOld, $this->merchantKey);
        $params = array('p0_Cmd' => $p0_Cmd,
            #	加入商户编号
            'p1_MerId' => $this->p1_MerId,
            #	加入易宝支付交易流水号
            'pb_TrxId' => $pb_TrxId,
            #	加入易宝支付交易流水号
            'p3_Amt' => $p3_Amt,
            #	加入易宝支付交易流水号
            'p4_Cur' => $p4_Cur,
            #	加入易宝支付交易流水号
            'p5_Desc' => $p5_Desc,
            #	加入校验码
            'hmac' => $hmac
        );
        $data = spider::POST($this->refund_url, $params);
        $lines = explode("\n", $data);
        $result = array();
        foreach ($lines as $line) {
            list($key, $val) = explode('=', trim($line), 2);
            if ($key) {
                $result[$key] = urldecode($val);
            }
        }
        if ($result['errorMsg']) {
            return array('message' => $result['errorMsg']);
        }

        #进行校验码检查 取得加密前的字符串
        $sbOld = "";
        #加入业务类型
        $sbOld = $sbOld . $result['r0_Cmd'];
        #加入退款申请是否成功
        $sbOld = $sbOld . $result['r1_Code'];
        #加入易宝支付交易流水号
        $sbOld = $sbOld . $result['r2_TrxId'];
        #加入退款金额
        $sbOld = $sbOld . $result['r3_Amt'];
        #加入交易币种
        $sbOld = $sbOld . $result['r4_Cur'];

        $hash_hmac = $this->hmac_md5($sbOld, $this->merchantKey);

        if ($hash_hmac != $result['hmac']) {
            log::info('hmacError', $hash_hmac, $result['hmac']);
            return array('message' => 'hmacError');
        }

        return array(
            'code' => $result['r2_TrxId'],
            'succ' => 1,
        );

    }

    #	取得返回串中的所有参数
    function get_callback_value(&$r0_Cmd, &$r1_Code, &$r2_TrxId, &$r3_Amt, &$r4_Cur, &$r5_Pid, &$r6_Order, &$r7_Uid, &$r8_MP, &$r9_BType, &$hmac) {
        $req = array_merge($_GET, $_POST, $_REQUEST);
        $r0_Cmd = $req['r0_Cmd'];
        $r1_Code = $req['r1_Code'];
        $r2_TrxId = $req['r2_TrxId'];
        $r3_Amt = $req['r3_Amt'];
        $r4_Cur = $req['r4_Cur'];
        $r5_Pid = $req['r5_Pid'];
        $r6_Order = $req['r6_Order'];
        $r7_Uid = $req['r7_Uid'];
        $r8_MP = $req['r8_MP'];
        $r9_BType = $req['r9_BType'];
        $hmac = $req['hmac'];
        return func_get_args();
    }

    # 业务类型
    # 支付请求，固定值"Buy" .
    //$p0_Cmd = "Buy";

    #	送货地址
    # 为"1": 需要用户将送货地址留在易宝支付系统;为"0": 不需要，默认为 "0".
    //$p9_SAF = "0";
    #签名函数生成签名串
    function get_req_hmac($p0_Cmd, $p2_Order, $p3_Amt, $p4_Cur, $p5_Pid, $p6_Pcat, $p7_Pdesc, $p8_Url, $p9_SAF, $pa_MP, $pd_FrpId, $pr_NeedResponse) {
        //print_r(func_get_args());exit;
        #进行签名处理，一定按照文档中标明的签名顺序进行
        $sbOld = "";
        #加入业务类型
        $sbOld = $sbOld . $p0_Cmd;
        #加入商户编号
        $sbOld = $sbOld . $this->p1_MerId;
        #加入商户订单号
        $sbOld = $sbOld . $p2_Order;
        #加入支付金额
        $sbOld = $sbOld . $p3_Amt;
        #加入交易币种
        $sbOld = $sbOld . $p4_Cur;
        #加入商品名称
        $sbOld = $sbOld . $p5_Pid;
        #加入商品分类
        $sbOld = $sbOld . $p6_Pcat;
        #加入商品描述
        $sbOld = $sbOld . $p7_Pdesc;
        #加入商户接收支付成功数据的地址
        $sbOld = $sbOld . $p8_Url;
        #加入送货地址标识
        $sbOld = $sbOld . $p9_SAF;
        #加入商户扩展信息
        $sbOld = $sbOld . $pa_MP;
        #加入支付通道编码
        $sbOld = $sbOld . $pd_FrpId;
        #加入是否需要应答机制
        $sbOld = $sbOld . $pr_NeedResponse;
        // echo "\r\n ",$sbOld;exit;
        $hmac_key = $this->hmac_md5($sbOld, $this->merchantKey);

        log::info($p2_Order, $sbOld, $hmac_key);

        return $hmac_key;
    }


    /**
     * get callback hmac
     *
     * @param $r0_Cmd
     * @param $r1_Code
     * @param $r2_TrxId
     * @param $r3_Amt
     * @param $r4_Cur
     * @param $r5_Pid
     * @param $r6_Order
     * @param $r7_Uid
     * @param $r8_MP
     * @param $r9_BType
     * @return string
     */
    function get_callback_hmac($r0_Cmd, $r1_Code, $r2_TrxId, $r3_Amt, $r4_Cur, $r5_Pid, $r6_Order, $r7_Uid, $r8_MP, $r9_BType) {

        #取得加密前的字符串
        $sbOld = "";
        #加入商家ID
        $sbOld = $sbOld . $this->p1_MerId;
        #加入消息类型
        $sbOld = $sbOld . $r0_Cmd;
        #加入业务返回码
        $sbOld = $sbOld . $r1_Code;
        #加入交易ID
        $sbOld = $sbOld . $r2_TrxId;
        #加入交易金额
        $sbOld = $sbOld . $r3_Amt;
        #加入货币单位
        $sbOld = $sbOld . $r4_Cur;
        #加入产品Id
        $sbOld = $sbOld . $r5_Pid;
        #加入订单ID
        $sbOld = $sbOld . $r6_Order;
        #加入用户ID
        $sbOld = $sbOld . $r7_Uid;
        #加入商家扩展信息
        $sbOld = $sbOld . $r8_MP;
        #加入交易结果返回类型
        $sbOld = $sbOld . $r9_BType;

        $hmac_key = $this->hmac_md5($sbOld, $this->merchantKey, 'gbk');
        log::info($r6_Order, $sbOld, $hmac_key);

        return $hmac_key;
    }

    /**
     * check hmac
     *
     * @param $r0_Cmd
     * @param $r1_Code
     * @param $r2_TrxId
     * @param $r3_Amt
     * @param $r4_Cur
     * @param $r5_Pid
     * @param $r6_Order
     * @param $r7_Uid
     * @param $r8_MP
     * @param $r9_BType
     * @param $hmac
     * @return bool
     */
    function check_hmac($r0_Cmd, $r1_Code, $r2_TrxId, $r3_Amt, $r4_Cur, $r5_Pid, $r6_Order, $r7_Uid, $r8_MP, $r9_BType, $hmac) {
        if ($hmac == $this->get_callback_hmac($r0_Cmd, $r1_Code, $r2_TrxId, $r3_Amt, $r4_Cur, $r5_Pid, $r6_Order, $r7_Uid, $r8_MP, $r9_BType))
            return true;
        else
            return false;
    }

    function hmac_md5($data, $key, $charset = 'utf-8') {
        // RFC 2104 HMAC implementation for php.
        // Creates an md5 HMAC.
        // Eliminates the need to install mhash to compute a HMAC
        // Hacked by Lance Rushing(NOTE: Hacked means written)
        if ($charset != 'utf-8') {
            $data = iconv('gbk', 'utf-8', $data);
            $key = iconv('gbk', 'utf-8', $key);
        }
        $b = 64; // byte length for md5
        if (strlen($key) > $b) {
            $key = pack("H*", md5($key));
        }
        $key = str_pad($key, $b, chr(0x00));
        $ipad = str_pad('', $b, chr(0x36));
        $opad = str_pad('', $b, chr(0x5c));
        $k_ipad = $key ^ $ipad;
        $k_opad = $key ^ $opad;
        return md5($k_opad . pack("H*", md5($k_ipad . $data)));
    }


}
