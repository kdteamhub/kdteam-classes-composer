<?php
/**
 * Created by PhpStorm.
 * User: a.krylov
 * Date: 29.01.2018
 * Time: 17:31
 */

namespace Kdteam;


class ProductPrice
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

    public function get($intProductId = 0)
    {

        if (!is_numeric($intProductId)) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $intProductId is incorrect');
            return false;
        }

        global $USER;

        $arPrice = \CCatalogProduct::GetOptimalPrice($intProductId, 1, $USER->GetUserGroupArray(), 'N');
        if (!$arPrice || count($arPrice) <= 0) {
            if ($nearestQuantity = \CCatalogProduct::GetNearestQuantityPrice(
                $intProductId,
                1,
                $USER->GetUserGroupArray()
            )) {
                $quantity = $nearestQuantity;
                $arPrice = \CCatalogProduct::GetOptimalPrice(
                    $intProductId,
                    $quantity,
                    $USER->GetUserGroupArray(), $renewal
                );
            }
        }

        return $arPrice;
    }


    /**
     * обновляет розничную цену по ид торгового предложения
     * @param int $intOfferId ид торгового предложение
     * @param float $floatOfferPrice цена
     * @return bool true|false результат обновления
     * @throws \Exception
     */
    public function set($intOfferId = 0, $floatOfferPrice = 0)
    {
        if (!is_numeric($intOfferId)) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $intOfferId is incorrect');
            return false;
        }

        if (!is_numeric($floatOfferPrice)) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $floatOfferPrice is incorrect');
            return false;
        }

        $arFields = array(
            "PRODUCT_ID" => $intOfferId,
            "CATALOG_GROUP_ID" => 1,
            "PRICE" => $floatOfferPrice,
            "CURRENCY" => "RUB",
            "QUANTITY_FROM" => 1,
            "QUANTITY_TO" => 9999
        );
        $res = \CPrice::GetList(
            array(),
            array(
                "PRODUCT_ID" => $intOfferId,
                "CATALOG_GROUP_ID" => 1
            )
        );
        if ($arr = $res->Fetch()) {
            \CPrice::Update($arr["ID"], $arFields);
            return true;
        } else {
            \CPrice::Add($arFields);
            return true;
        }
        return false;
    }

}