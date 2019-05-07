<?php
/**
 * Created by PhpStorm.
 * User: a.krylov
 * Date: 22.03.2018
 * Time: 15:59
 */

namespace Kdteam;


class OrderCustomField extends Order
{
    /**
     * метод создает/записывает кастомные свойства в заказ
     * @param int $intOrderId ид-р заказа
     * @param int $intOrderPropsId ид-р кастомного свойства
     * @param string $strPropsCode символьный код кастомного свойства
     * @param string $strPropsValue значение кастомного свойства
     * @return bool успех/неудача
     * @throws \Exception
     */
    public function set($intOrderId = 0, $intOrderPropsId = 0, $strPropsCode = '', $strPropsValue = '')
    {
        if (!is_numeric($intOrderId)) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $intOrderId is incorrect');
            return false;
        }

        if (!is_numeric($intOrderPropsId)) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $intOrderPropsId is incorrect');
            return false;
        }

        $rsVals = \CSaleOrderPropsValue::GetList(array("SORT" => "ASC"), array("ORDER_ID" => $intOrderId, "ORDER_PROPS_ID" => $intOrderPropsId));
        if ($arVals = $rsVals->Fetch()) {
            \CSaleOrderPropsValue::Update($arVals['ID'], array("VALUE" => $strPropsValue));
            return true;
        } else {
            $arFields = array(
                "ORDER_ID" => $intOrderId,
                "ORDER_PROPS_ID" => $intOrderPropsId,
                "NAME" => $strPropsCode,
                "VALUE" => $strPropsValue,
            );
            \CSaleOrderPropsValue::Add($arFields);
            return true;
        }
        return false;
    }

    /**
     * возвращает свойства заказа LOCATION (ГОРОД) и ADDRESS (адрес)
     * @param int $intOrderId ид-р свойства
     * @return array|bool массив / false при ошибке
     * @throws \Exception
     * #example $arLocationData = $ocf->getAddress($intOrderId);
     */
    public function getAddress($intOrderId = 0)
    {
        if (!is_numeric($intOrderId)) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $intOrderId is incorrect');
            return false;
        }


        $arLocationData = array();

        $obProps = \Bitrix\Sale\Internals\OrderPropsValueTable::getList(array('filter' => array('ORDER_ID' => $intOrderId)));
        while ($prop = $obProps->Fetch()) {
            if (in_array($prop['CODE'], array('LOCATION', 'ADDRESS'))) {
                $arLocationData[$prop['CODE']] = $prop['VALUE'];
            }
        }

        return $arLocationData;
    }

    /**
     * возвращает значение свойства, ид которого передаано, для заказа с переданным ид
     * @param int $intOrderId ид-р заказа
     * @param int $intPropertyId ид-р свойства (числовой)
     * @return bool|string
     * @throws \Exception
     */
    public function get($intOrderId = 0, $intPropertyId = 0)
    {
        if (!is_numeric($intOrderId)) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $intOrderId is incorrect');
            return false;
        }

        if (!is_numeric($intPropertyId)) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $intPropertyId is incorrect');
            return false;
        }

        $rsVals = \CSaleOrderPropsValue::GetList(array("SORT" => "ASC"), array("ORDER_ID" => $intOrderId, "ORDER_PROPS_ID" => $intPropertyId));
        if ($arVals = $rsVals->Fetch()) {
            return $arVals['VALUE'];
        }

        return false;
    }


}