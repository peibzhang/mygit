<?php

/**
 * ECSHOP ��Ʒ����
 * ============================================================================
 * * ��Ȩ���� 2005-2012 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.ecshop.com��
 * ----------------------------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�
 * ʹ�ã�������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Author: liubo $
 * $Id: category.php 17217 2011-01-19 06:29:08Z liubo $
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}
assign_template();
$position = assign_ur_here();
$smarty->assign('page_title', $position['title']);    // ҳ�����
$smarty->assign('ur_here',    $position['ur_here']);  // ��ǰλ��
//print_r(get_flash_sale_goods());
$smarty->assign('flash_sale_goods', get_flash_sale_goods()); // �ؼ���Ʒ
$smarty->assign('helps',            get_shop_help());              // �������
 
/* ��ʾģ�� */
$smarty->display('flash_sale.dwt');


/**
 * ��ô�����Ʒ
 *
 * @access  public
 * @return  array
 */
function get_flash_sale_goods($cats = '')
{
    $time = gmtime();
    $order_type = $GLOBALS['_CFG']['recommend_order'];

    /* ȡ�ô���lbi���������� */
    $num = get_library_number("recommend_promotion");
    $sql = 'SELECT g.goods_id, g.goods_name, g.goods_name_style, g.market_price, g.shop_price AS org_price, g.promote_price, ' .
                "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, ".
                "promote_start_date, promote_end_date, g.goods_brief, g.goods_thumb, goods_img, b.brand_name, " .
                "g.is_best, g.is_new, g.is_hot, g.is_promote, RAND() AS rnd " .
            'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' .
            'LEFT JOIN ' . $GLOBALS['ecs']->table('brand') . ' AS b ON b.brand_id = g.brand_id ' .
            "LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp ".
                "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' ".
            'WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 ' .
            " AND g.is_promote = 1 AND promote_start_date <= '$time' AND promote_end_date >= '$time' ";
    $sql .= $order_type == 0 ? ' ORDER BY g.sort_order, g.last_update DESC' : ' ORDER BY rnd';

    $result = $GLOBALS['db']->getAll($sql);

    $goods = array();
    foreach ($result AS $idx => $row)
    {
        if ($row['promote_price'] > 0)
        {
            $promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
            $goods[$idx]['promote_price'] = $promote_price > 0 ? $promote_price : '';
        }
        else
        {
            $goods[$idx]['promote_price'] = '';
        }
		
		$sql = 'SELECT sum(goods_number) AS goods_number FROM '.$GLOBALS['ecs']->table('order_goods').' WHERE goods_id ='.$row['goods_id'].' GROUP BY goods_id';
		$goods_number = $GLOBALS['db']->getOne($sql);
		if(empty($goods_number))
		{
			$goods_number = 0;
		}
		
		if(!empty($row['promote_end_date']))
		{
			 $goods[$idx]['end_date']           = $row['promote_end_date'];
		}
		$goods[$idx]['soldnum']      = get_soldnum($row['goods_id']);
 
	 
		$goods[$idx]['jiesheng']     = $row['market_price'] - $row['promote_price'];
		$goods[$idx]['zhekou']       = sprintf("%1\$.1f",($row['promote_price']/$row['shop_price'])*10);
        $goods[$idx]['id']           = $row['goods_id'];
		$goods[$idx]['number']       = $goods_number;
        $goods[$idx]['name']         = $row['goods_name'];
        $goods[$idx]['brief']        = $row['goods_brief'];
        $goods[$idx]['brand_name']   = $row['brand_name'];
        $goods[$idx]['goods_style_name']   = add_style($row['goods_name'],$row['goods_name_style']);
        $goods[$idx]['short_name']   = $GLOBALS['_CFG']['goods_name_length'] > 0 ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
        $goods[$idx]['short_style_name']   = add_style($goods[$idx]['short_name'],$row['goods_name_style']);
        $goods[$idx]['market_price'] = $row['market_price'];
        $goods[$idx]['shop_price']   = $row['shop_price'];
        $goods[$idx]['thumb']        = get_image_path($row['goods_id'], $row['goods_thumb'], true);
        $goods[$idx]['goods_img']    = get_image_path($row['goods_id'], $row['goods_img']);
        $goods[$idx]['url']          = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
		$properties                  = get_goods_properties($row['goods_id']);  // �����Ʒ�Ĺ�������
		$goods[$idx]['pro']          = $properties['pro']['��Ʒ����'];

    }

   return $goods;
}


?>

