<?php

/**
 * Class Coupon
 */
namespace Kdteam;

class Discount
{

    /**
     * Discount constructor.
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
     * возвращает параметры скидки по id
     * @param integer $discountId - id заказа
     * @return array
     */
    public function getById($discountId)
    {
        $arReturn = array();
        $couponList = \Bitrix\Sale\Internals\OrderCouponsTable::getList(array(
            'select' => array('*'),
            'filter' => array('=ORDER_ID' => $discountId)
        ));
        if($coupon = $couponList->fetch())
        {
            return $coupon;
        }

    }

}
