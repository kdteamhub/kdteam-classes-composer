<?php
/**
 * класс остатков на складах для торг предложений
 */

namespace Kdteam;


class ProductStockStatus
{
    protected $iblockId;

    public function __construct($intIblockId = 0)
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
     * устанавливает наличие товара в дополнительное свойство.
     * свойство должно называться STOCK_STATUS_OFFER
     * значения: 1 - товар в наличии, 0 - товара нет в наличии
     * @param int $intOfferId ид-р торгового предложения
     * @param int $bStockStatus 0|1 есть/нет в наличии
     * @return bool true|false в зависимости от результатов обновления
     * @throws \Exception
     */
    public function setSimple($intOfferId = 0, $bStockStatus = 0)
    {
        if (!is_numeric($intOfferId)) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $intOfferId is incorrect');
            return false;
        }

        if ($intOfferId > 0 && ($bStockStatus == 1 || $bStockStatus == 0)) {
            \CIBlockElement::SetPropertyValues($intOfferId, $this->iblockId, $bStockStatus, 'STOCK_STATUS_OFFER');
            return true;
        }

        return false;
    }

    /**
     * устанавливает наличие товара в запись склада
     * @param int $intOfferId ид товара / торг предложения
     * @param int $intStoreId ид склада
     * @param int $intAmount количество товара
     * @return bool|int
     * @throws \Exception
     */
    public function setStore($intOfferId = 0, $intStoreId = 0, $intAmount = 0)
    {
        if (!is_numeric($intOfferId)) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $intOfferId is incorrect');
            return false;
        }

        if (!is_numeric($intStoreId)) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $intStoreId is incorrect');
            return false;
        }

        $intStoreRecId = 0;

        $rsStore = \CCatalogStoreProduct::GetList(
            array(),
            array(
                'PRODUCT_ID' => $intOfferId,
                'STORE_ID' => $intStoreId
            ),
            false,
            false,
            array()
        );
        if ($arStore = $rsStore->Fetch()) {
            $intStoreRecId = $arStore['ID'];
        }

        if ($intStoreRecId > 0) {
            $arFields = array(
                "PRODUCT_ID" => $intOfferId,
                "STORE_ID" => $intStoreId,
                "AMOUNT" => $intAmount
            );
            return \CCatalogStoreProduct::Update($intStoreRecId, $arFields);
        } else {
            // нет езе информации о наличии на этом складе
            $arFields = array(
                "PRODUCT_ID" => $intOfferId,
                "STORE_ID" => $intStoreId,
                "AMOUNT" => $intAmount,
            );
            return \CCatalogStoreProduct::Add($arFields);
        }

        return false;
    }

    /**
     * Наличие онлайн
     * @param $product
     * @return array
     */
    public function getOnline($productId)
    {
        if (!is_numeric($productId)) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $productId is incorrect');
            return false;
        }

        $quantity = 0;

        $arFilter = array('ACTIVE' => 'Y', 'PRODUCT_ID' => $productId, 'STORE_ID' => STOCK);

        $dbResult = \CCatalogStoreProduct::GetList(
            array('PRODUCT_ID' => 'ASC', 'ID' => 'ASC'),
            $arFilter,
            false,
            false,
            array()
        );

        if ($arItem = $dbResult->GetNext()) {
            $quantity = $arItem['AMOUNT'];
        }

        if ($quantity > 0) {
            return $quantity;
        }
        return false;
    }

    /**
     * Наличие
     * @param $product
     * @return array
     */
    public function getAllCount($productId)
    {
        if (!is_numeric($productId)) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $productId is incorrect');
            return false;
        }

        $quantity = 0;

        $arFilter = array('ACTIVE' => 'Y', 'PRODUCT_ID' => $productId);

        $dbResult = \CCatalogStoreProduct::GetList(
            array('PRODUCT_ID' => 'ASC', 'ID' => 'ASC'),
            $arFilter,
            false,
            false,
            array()
        );

        while ($arItem = $dbResult->GetNext()) {
            if($arItem['AMOUNT']>0) {
                $quantity = $quantity + $arItem['AMOUNT'];
            }
        }

        if ($quantity > 0) {
            return $quantity;
        }
        return false;
    }
}
