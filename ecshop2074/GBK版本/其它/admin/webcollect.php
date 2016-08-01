<?php

/**
 * ECSHOP ��������
 * ===========================================================
 * ��Ȩ���� 2005-2010 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.ecshop.com��
 * ----------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�
 * ʹ�ã�������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ==========================================================
 * $Author: wangleisvn $
 * $Id: webcollect.php 16131 2009-05-31 08:21:41Z wangleisvn $
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . 'includes/lib_license.php');

/* ���Ȩ�� */
admin_priv('webcollect_manage');
$smarty->assign('ur_here', $_LANG['ur_here']);

$license = get_shop_license();  // ȡ������ license

if (!empty($license['certificate_id']) && !empty($license['token']) && !empty($license['certi']))
{
    /* ������¼��֤ */
    $certi_login['certi_app'] = 'certi.login'; // ֤�鷽��
    $certi_login['app_id'] = 'ecshop_b2c'; // ˵���ͻ�����Դ
    $certi_login['app_instance_id'] = 'cert_auth'; // Ӧ�÷���ID
    $certi_login['version'] = VERSION . '#' .  RELEASE; // ��������汾��
    $certi_login['certi_url'] = sprintf($GLOBALS['ecs']->url()); // ����URL
    $certi_login['certi_session'] = $GLOBALS['sess']->get_session_id(); // ����SESSION��ʶ
    $certi_login['certi_validate_url'] = sprintf($GLOBALS['ecs']->url() . 'certi.php'); // �����ṩ�ڹٷ�����ӿ�
    $certi_login['format'] = 'json'; // �ٷ��������ݸ�ʽ
    $certi_login['certificate_id'] = $license['certificate_id']; // ����֤��ID
    $certi_login['certi_ac'] = make_shopex_ac($certi_login, $license['token']); // ������֤�ַ���

    $request_login_arr = exchange_shop_license($certi_login, $license, 1);

    /* ͨ�õ���֤���� */
    $certi['certificate_id'] = $license['certificate_id']; // ����֤��ID
    $certi['app_id'] = 'ecshop_b2c'; // ˵���ͻ�����Դ
    $certi['app_instance_id'] = 'webcollect'; // Ӧ�÷���ID
    $certi['version'] = VERSION . '#' .  RELEASE; // ��������汾��
    $certi['format'] = 'json'; // �ٷ��������ݸ�ʽ

    if (is_array($request_login_arr) && $request_login_arr['res'] == 'succ')    //�鿴�Ƿ������������·���
    {
        if (isset($_GET['act']) && $_GET['act'] == 'open')  //��������
        {
            $certi['certi_app'] = 'co.open_se'; // ֤�鷽��
            $certi['certi_ac'] = make_shopex_ac($certi, $license['token']); // ������֤�ַ���

            exchange_shop_license($certi, $license, 1);
        }
        elseif (isset($_GET['act']) && $_GET['act'] == 'close') //��ͣ����
        {
            $certi['certi_app'] = 'co.close_se'; // ֤�鷽��
            $certi['certi_ac'] = make_shopex_ac($certi, $license['token']); // ������֤�ַ���

            exchange_shop_license($certi, $license, 1);
        }

        $certi['certi_app'] = 'co.valid_se'; // ֤�鷽��
        $certi['certi_ac'] = make_shopex_ac($certi, $license['token']); // ������֤�ַ���

        $request_arr = exchange_shop_license($certi, $license, 1);

        if ($request_arr['res'] == 'succ')
        {
            $now = time();
            if ($request_arr['info']['service_status'] == 'expire')
            {
                $smarty->assign('case', 2);    //�ѹ���ҳ��
                $smarty->assign('open', 2);    //�������¿���
            }
            elseif ($request_arr['info']['service_close_time'] - $now < 1296000)
            {
                $smarty->assign('case', 1);    //������ҳ��
                $smarty->assign('open', $request_arr['info']['service_status'] == 'open' ? 1 : 0);

                $out_days = floor(($request_arr['info']['service_close_time'] - $now) / 86400);
                $smarty->assign('out_notice', sprintf($_LANG['soon_out'], $out_days));  //����ʱ����ʾ
            }
            else
            {
                $smarty->assign('case', 3);    //����ҳ��
                $smarty->assign('open', $request_arr['info']['service_status'] == 'open' ? 1 : 0);
            }

            $smarty->assign('lic_code', $license['certificate_id']);    //֤��ID
            $smarty->assign('lic_btime', local_date('Y-m-d', $request_arr['info']['service_open_time']));   //����ʼʱ��
            $smarty->assign('lic_etime', local_date('Y-m-d', $request_arr['info']['service_close_time']));   //�������ʱ��
            $smarty->assign('col_goods_num', $request_arr['info']['collect_num']);   //��¼��Ʒ����
            $smarty->assign('col_goods', $request_arr['info']['collect_se']);   //��¼��Ʒ����
        }
        else
        {
            $smarty->assign('msg', $request_arr['info']);    //��ʾ��Ϣ
            $smarty->assign('case', 0);    //��ͨ����ҳ��
        }
    }
    else
    {
        $smarty->assign('msg', $_LANG['no-open']);    //��ʾ��Ϣ
        $smarty->assign('case', 0);    //��ͨ����ҳ��
    }

    //������վ�б�
    $certi['certi_app'] = 'co.show_se'; // ֤�鷽��
    $certi['certi_ac'] = make_shopex_ac($certi, $license['token']); // ������֤�ַ���

    $request_arr = exchange_shop_license($certi, $license, 1);

    if ($request_arr['res'] == 'succ')    //�ɹ���ȡ������վ��Ϣ
    {
        $smarty->assign('site_arr', $request_arr['info']['se']);
    }
    else
    {
        $smarty->assign('site_msg', $request_arr['info']);
    }
}
else
{
    $smarty->assign('msg', $_LANG['no-open']);    //��ʾ��Ϣ
    $smarty->assign('case', 0);    //��ͨ����ҳ��
}

$smarty->display('webcollect.htm');
?>
