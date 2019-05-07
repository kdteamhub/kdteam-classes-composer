<?php
/**
 * Created by PhpStorm.
 * User: a.krylov
 * Date: 25.10.2018
 * Time: 19:14
 */

namespace Kdteam;

use Bitrix\Main\Web\DOM\Element;

class ProductOffer
{
    private $iblockId;

    public function __construct($intIblockId)
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

    public function updateCustom($intProductId = 0, $strPropertyName = '', $strPropertyValue = '')
    {
        if (!is_numeric($intProductId)) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $intProductId is incorrect');
            return false;
        }
        if (!$strPropertyName) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $strPropertyName is incorrect');
            return false;
        }

        return \CIBlockElement::SetPropertyValues($intProductId, $this->iblockId, $strPropertyValue, $strPropertyName);
    }

    /**
     * ищет ид торгового предложения по уникальному артикулу в иб торг предложений
     * @param string $strSKU артикул торгового предложения
     * @return bool|int|string ид торг предложения или false при ошибке
     * @throws \Exception
     */
    public function getBySku($strSKU = '')
    {
        if (!$strSKU) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $strSKU is incorrect');
            return false;
        }

        $arrFilter = array(
            "PROPERTY_ARTNUMBER" => $strSKU,
            "IBLOCK_ID" => $this->iblockId,
            "ACTIVE" => "Y"
        );

        $res = \CIBlockElement::GetList(array("sort" => "ASC"), $arrFilter, false, false, array("ID"));
        if ($arId = $res->GetNext()) {
            if (is_numeric($arId['ID'])) {
                return $arId['ID'];
            }
        }
        return false;
    }

    /**
     * обновляет розничную цену по ид торгового предложения
     * @param int $intOfferId ид торгового предложение
     * @param int $floatOfferPrice цена
     * @return bool true|false результат обновления
     * @throws \Exception
     */
    public function setPrice($intOfferId = 0, $floatOfferPrice = 0)
    {
        if (!is_numeric($intOfferId)) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $intOfferId is incorrect');
            return false;
        }

        if (!is_numeric($floatOfferPrice)) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $floatOfferPrice is incorrect');
            return false;
        }

        if ($intOfferId > 0 && $floatOfferPrice > 0) {
            $arFields = array(
                "PRODUCT_ID" => $intOfferId,
                "CATALOG_GROUP_ID" => 1,
                "PRICE" => $floatOfferPrice,
                "CURRENCY" => "RUB",
                "QUANTITY_FROM" => 1
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
        return false;
    }

    /**
     * получает список торговых предложений по ид товара
     * @param int $intProductId ид товара
     * @return array|bool массив торг предложений или false при ошибке
     * @throws \Exception
     */
    public function getListByProductId($intProductId = 0)
    {
        return $this->getList($intProductId, array(), array());
    }

    /**
     * возвращает список торговых предложений
     * @param int $intProductId ид продукта
     * @param array $arFilter массив для фильтрации
     * @param array $arFields массив названий возвращаемых полей
     * @return array|bool
     * @throws \Exception
     */
    public function getList($intProductId = 0, $arFilter = array(), $arFields = array())
    {
        if (!is_numeric($intProductId)) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $intProductId is incorrect');
            return false;
        }

        $arFilterFields = array(
            'IBLOCK_ID' => $this->iblockId,
            'PROPERTY_CML2_LINK' => $intProductId
        );

        if ($arFilter) {
            foreach ($arFilter as $key => $value) {
                $arFilterFields[$key] = $value;
            }
        }

        $rsOffers = \CIBlockElement::GetList(
            array(),
            $arFilterFields,
            false,
            false,
            $arFields
        );
        $arReturn = array();
        while ($arOffer = $rsOffers->GetNext()) {
            $arReturn[] = $arOffer['ID'];
        }
        return $arReturn;
    }

    public function getListFull($intProductId = 0, $arFilter = array(), $arFields = array())
    {
        if (!is_numeric($intProductId)) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $intProductId is incorrect');
            return false;
        }

        $arFilterFields = array(
            'IBLOCK_ID' => $this->iblockId,
            'PROPERTY_CML2_LINK' => $intProductId
        );

        if ($arFilter) {
            foreach ($arFilter as $key => $value) {
                $arFilterFields[$key] = $value;
            }
        }

        $rsOffers = \CIBlockElement::GetList(
            array(),
            $arFilterFields,
            false,
            false,
            $arFields
        );
        $arReturn = array();
        while ($arOffer = $rsOffers->GetNext()) {
            array_push(
                $arReturn,
                $arOffer
            );

        }
        return $arReturn;
    }

    /**
     *
     * @param int $intProductId
     * @param string $strOfferName
     * @param array $arOfferData
     * @return bool
     * @throws \Exception
     */
    public function add($intProductId = 0, $strOfferName = '', $arOfferData = array())
    {
        if (!is_numeric($intProductId)) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $intProductId is incorrect');
            return false;
        }

        if (!$strOfferName) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $strOfferName is incorrect');
            return false;
        }

        $obElement = new \CIBlockElement();
        $arProp = $arOfferData;
        $arProp['CML2_LINK'] = $intProductId;


        $arFields = array(
            'NAME' => $strOfferName,
            'IBLOCK_ID' => $this->iblockId,
            'ACTIVE' => 'Y',
            'PROPERTY_VALUES' => $arProp
        );

        $intOfferID = $obElement->Add($arFields);

        $fields = array(
            'ID' => $intOfferID,
            'QUANTITY_TRACE' => \Bitrix\Catalog\ProductTable::STATUS_DEFAULT,
            'CAN_BUY_ZERO' => \Bitrix\Catalog\ProductTable::STATUS_DEFAULT,
            'WEIGHT' => 0,
            'MEASURE' => 5
        );
        \CCatalogProduct::Add($fields);

        return $intOfferID;

    }

}