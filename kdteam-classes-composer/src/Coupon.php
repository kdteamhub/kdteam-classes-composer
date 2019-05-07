<?php

/**
 * Class Coupon
 */
namespace Kdteam;

class Coupon
{

    /**
     * Coupon constructor.
     */
    public function __construct()
    {
        if (!\CModule::IncludeModule("iblock")) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' module iblock not included');
            return false;
        }

        if (!\CModule::IncludeModule("sale")) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' module sale not included');
            return false;
        }
    }

    /**
     * возвращает параметры купоны по id заказа
     * @param integer $orderId - id заказа
     * @return array
     */
    public function getByOrderId($orderId)
    {
        $coupon = array();
        $couponList = \Bitrix\Sale\Internals\OrderCouponsTable::getList(array(
            'select' => array('*'),
            'filter' => array('=ORDER_ID' => $orderId)
        ));
        if($coupon = $couponList->fetch())
        {
            return $coupon;
        }

    }

}
