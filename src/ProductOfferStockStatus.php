<?php
/**
 * класс остатков на складах для торг предложений
 * расширяет класс остатков на складах продуктов
 */

namespace Kdteam;

class ProductOfferStockStatus extends ProductStockStatus
{

    /**
     * устанавливает для всех торговых предложений одинаковое количество товаров (по умолчанию 0)
     * метод работает со складами
     * @param int $intStoreAmount количество товаров (0 по умолчанию)
     * @return bool
     * @throws \Exception
     */
    public function setStoreAll($intStoreAmount = 0)
    {
        if (!is_numeric($intStoreAmount)) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $intStoreAmount is incorrect');
            return false;
        }

        $store = new \Kdteam\Store();
        $arStores = $store->getList();

        $offer = new \Kdteam\ProductOffer($this->iblockId);
        $arProductOffers = $offer->getList();

        foreach ($arProductOffers as $strOfferId) {
            foreach ($arStores as $intStoreId) {
                $intStoreId = $intStoreId['ID'];
                parent::setStore($strOfferId, $intStoreId, $intStoreAmount);

                echo udate('H:i:s.u')
                    . ' CLEANING STORE. OFFER ID ' . $strOfferId
                    . ';  STORE ID: ' . $intStoreId
                    . EOL;
            }
        }

        return true;
    }
}