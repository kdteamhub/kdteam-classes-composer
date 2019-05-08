<?php
/**
 * Created by PhpStorm.
 * User: a.krylov
 * Date: 29.01.2018
 * Time: 16:35
 */

namespace Kdteam;

class ProductDiscount
{
    public $iblockId;

    function __construct($intIblockId)
    {
        if ($intIblockId > 0) {
            $this->iblockId = $intIblockId;
        } else {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' intIblockId is incorrect');
            return false;
        }

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
     * создает скидку на фикс сумму
     * @param int $intProductId ид продукта
     * @param string $strComment комментарий к скидке
     * @param int $intDiscount объем скидки
     * @return bool
     * @throws \Exception
     */
    function create($intProductId = 0, $strComment = '', $intDiscount = 0)
    {
        if (!is_numeric($intProductId)) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $intProductId is incorrect');
            return false;
        }

        if (!is_numeric($intDiscount)) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $intDiscount is incorrect');
            return false;
        }

        $arLogic = array(
            "CLASS_ID" => "CondGroup",
            "DATA" => array(
                "All" => "OR",
                "True" => "True",
            ),
            "CHILDREN" => array(
                "0" => array(
                    "CLASS_ID" => "CondIBElement",
                    "DATA" => array(
                        "logic" => "Equal",
                        "value" => $intProductId,
                    ),
                ),
            ),
        );

        $arDiscount = array(
            "SITE_ID" => "s1",
            "ACTIVE" => "Y",
            "NAME" => $strComment . ' скидка ' . $intDiscount . ' руб.',
            "MAX_USES" => 0,
            "COUNT_USES" => 0,
            "COUPON" => "",
            "SORT" => 100,
            "MAX_DISCOUNT" => 0.0000,
            "VALUE_TYPE" => "F",
            "VALUE" => $intDiscount,
            "CURRENCY" => "RUB",
            "MIN_ORDER_SUM" => 0.0000,
            "NOTES" => "",
            "RENEWAL" => "N",
            "ACTIVE_FROM" => "",
            "ACTIVE_TO" => "",
            "PRIORITY" => 999999,
            "LAST_DISCOUNT" => "Y",
            "CONDITIONS" => serialize($arLogic),
        );

        $ID = \CCatalogDiscount::Add($arDiscount);
        $res = $ID > 0;
        if (!$res) {
            return false;
        }

        return true;
    }

    /**
     * создает скидку на %
     * @param int $intProductId ид-р продукта
     * @param string $strComment комментарий (название) скидки
     * @param int $intDiscountPercent объем скидки %
     * @return bool
     * @throws \Exception
     */
    function createPercent($intProductId = 0, $strComment = '', $intDiscountPercent = 0)
    {
        if (!is_numeric($intProductId)) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $intProductId is incorrect');
            return false;
        }

        if (!is_numeric($intDiscountPercent)) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $intDiscountPercent is incorrect');
            return false;
        }

        $arLogic = array(
            "CLASS_ID" => "CondGroup",
            "DATA" => array(
                "All" => "OR",
                "True" => "True",
            ),
            "CHILDREN" => array(
                "0" => array(
                    "CLASS_ID" => "CondIBElement",
                    "DATA" => array(
                        "logic" => "Equal",
                        "value" => $intProductId,
                    ),
                ),
            ),
        );

        $arDiscount = array(
            "SITE_ID" => "s1",
            "ACTIVE" => "Y",
            "NAME" => $strComment . ' скидка ' . $intDiscountPercent . '%.',
            "MAX_USES" => 0,
            "COUNT_USES" => 0,
            "COUPON" => "",
            "SORT" => 100,
            "MAX_DISCOUNT" => 0.0000,
            "VALUE_TYPE" => "P",
            "VALUE" => $intDiscountPercent,
            "CURRENCY" => "RUB",
            "MIN_ORDER_SUM" => 0.0000,
            "NOTES" => "",
            "RENEWAL" => "N",
            "ACTIVE_FROM" => "",
            "ACTIVE_TO" => "",
            "PRIORITY" => 999999,
            "LAST_DISCOUNT" => "Y",
            "CONDITIONS" => serialize($arLogic),
        );

        $ID = \CCatalogDiscount::Add($arDiscount);
        $res = $ID > 0;
        if (!$res) {
            return false;
        }

        return true;
    }

    /**
     * получает массив скидок на продукт по его ид
     * @param int $intProductId ид продукта
     * @return array|bool массив скидок или false
     * @throws \Exception
     */
    function getListByProductId($intProductId = 0)
    {
        if (!is_numeric($intProductId)) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $intProductId is incorrect');
            return false;
        }

        global $DB;

        if ($intProductId) {
            $dbProductDiscounts = \CCatalogDiscount::GetList(
                array("PRIORITY" => "ASC"),
                array(
                    "PRODUCT_ID" => $intProductId,
                    "ACTIVE" => "Y",
                    "!>ACTIVE_FROM" => $DB->FormatDate(date("Y-m-d H:i:s"),
                        "YYYY-MM-DD HH:MI:SS",
                        \CSite::GetDateFormat("FULL")),
                    "!<ACTIVE_TO" => $DB->FormatDate(date("Y-m-d H:i:s"),
                        "YYYY-MM-DD HH:MI:SS",
                        \CSite::GetDateFormat("FULL")),
                    "COUPON" => ""
                ),
                false,
                false,
                array(
                    "ID", "SITE_ID", "ACTIVE", "ACTIVE_FROM", "ACTIVE_TO",
                    "RENEWAL", "NAME", "SORT", "MAX_DISCOUNT", "VALUE_TYPE",
                    "VALUE", "CURRENCY", "PRODUCT_ID", 'PRIORITY'
                )
            );
            $arReturnDiscounts = array();
            while ($arProductDiscounts = $dbProductDiscounts->Fetch()) {
                array_push($arReturnDiscounts, $arProductDiscounts);
            }
            return $arReturnDiscounts;
        }
    }

    /**
     * удаляет скидку по ее ид
     * @param int $intDiscountId ид скидки
     * @return bool
     * @throws \Exception
     */
    function deleteById($intDiscountId = 0)
    {
        if (!is_numeric($intDiscountId)) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $intDiscountId is incorrect');
            return false;
        }

        return \CCatalogDiscount::Delete($intDiscountId);

    }

    /**
     * создает скидку для товар (не правило корзины) в соответствии с указанными параметрами
     * скидка создается на ХХХХ руб.
     * при создании скидки на товар, она будет распостранятся на все ТП
     * @param int $intProductId ид продукта
     * @param string $strProductName название продукта (для названия скидки)
     * @param string $strSku артикул продукта (для названия скидки)
     * @param int $intDiscount объем скидки (скидка ХХХХ руб)
     * @return bool успех/неудача
     */
    function discountApiCreate($intProductId = 0, $strProductName = '', $strSku = '', $intDiscount = 0)
    {

        $arLogic = array(
            "CLASS_ID" => "CondGroup",
            "DATA" => array(
                "All" => "OR",
                "True" => "True",
            ),
            "CHILDREN" => array(
                "0" => array(
                    "CLASS_ID" => "CondIBElement",
                    "DATA" => array(
                        "logic" => "Equal",
                        "value" => $intProductId,
                    ),
                ),
            ),
        );

        $arDiscount = array(
            "SITE_ID" => "s1",
            "ACTIVE" => "Y",
            "NAME" => $strProductName . ' (ID: ' . $intProductId . ', артикул: ' . $strSku . ') скидка ' . $intDiscount . ' руб.',
            "MAX_USES" => 0,
            "COUNT_USES" => 0,
            "COUPON" => "",
            "SORT" => 100,
            "MAX_DISCOUNT" => 0.0000,
            "VALUE_TYPE" => "F",
            "VALUE" => $intDiscount,
            "CURRENCY" => "RUB",
            "MIN_ORDER_SUM" => 0.0000,
            "NOTES" => "",
            "RENEWAL" => "N",
            "ACTIVE_FROM" => "",
            "ACTIVE_TO" => "",
            "PRIORITY" => 10,
            "LAST_DISCOUNT" => "Y",
            "CONDITIONS" => serialize($arLogic),
        );

        $ID = \CCatalogDiscount::Add($arDiscount);
        $res = $ID > 0;
        if (!$res) {
            return false;
        }

        return true;
    }
}