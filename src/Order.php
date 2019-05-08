<?php
/**
 * Created by PhpStorm.
 * User: a.krylov
 * Date: 22.03.2018
 * Time: 15:15
 */

namespace Kdteam;

class Order
{
    function __construct($intIblockId)
    {
        if (!\CModule::IncludeModule("iblock")) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' module iblock not included');
            return false;
        }

        if (!\CModule::IncludeModule("sale")) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' module sale not included');
            return false;
        }

        if (!\CModule::IncludeModule("catalog")) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' module catalog not included');
            return false;
        }
    }

    /**
     * возвращает список заказов
     * @param array $arFilter массив фильтра
     * @param array $arSelectFields массив полей
     * @param bool $prop Выбирать доп. поля
     * @return array
     * @example $arNotSentOrders = $o->getList(array('!PROPERTY_SEND_TO_KUPIVIP'=>1), array('ID'));
     */
    public function getList($arFilter = array(), $arSelectFields = array(), $prop = false)
    {
        $arReturn = array();
        $rsOrders = \CSaleOrder::GetList(array('ID' => 'desc'), $arFilter, false, false, $arSelectFields);
        while ($arOrder = $rsOrders->Fetch()) {
            if($prop) {
                //Свойства заказа
                $obProps = \Bitrix\Sale\Internals\OrderPropsValueTable::getList(array('filter' => array('ORDER_ID' => $arOrder['ID'])));
                while ($prop = $obProps->Fetch()) {
                    $arOrder['PROP'][$prop['CODE']] = $prop;
                }

                //Способ оплаты
                $paymentDataList = \Bitrix\Sale\Internals\PaymentTable::getList(array('filter' => array('ORDER_ID' => $arOrder['ID'])));
                while ($paymentData = $paymentDataList->fetch()) {
                    $arOrder['PAYMENT'][] = $paymentData;
                }

            }

            array_push($arReturn, $arOrder);
        }

        return $arReturn;
    }

    /**
     * отбирает ид-ры заказов, отфильтрованные по указанному свойству и его значению
     * @param string $strPropertyCode код свойства. без property_
     * @param string $strPropertyValue значение
     * @return array|bool массив результатов или False при ошибке
     * @throws \Exception
     * @example $arSentOrders = $o->getListFiltered('SEND_TO_KUPIVIP', 1);
     */
    public function getListFiltered($strPropertyCode = '', $strPropertyValue = '')
    {

        if (!$strPropertyCode) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $strPropertyCode is incorrect');
            return false;
        }

        $arFilter = Array(
            'PROPERTY_VAL_BY_CODE_'.$strPropertyCode =>  $strPropertyValue,
        );

        return $this->getList($arFilter, array('ID'));
    }
}