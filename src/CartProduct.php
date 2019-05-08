<?php
/**
 * Created by PhpStorm.
 * User: a.krylov
 * Date: 21.02.2018
 * Time: 16:48
 */

namespace Kdteam;


class CartProduct extends \Kdteam\Cart
{
    private $basket;
    private $sku;
    private $element;

    function __construct()
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

        $this->basket = new \CSaleBasket;
        $this->sku = new \CCatalogSku;
        $this->element = new \CIBlockElement;
    }

    /**
     * возвращает список товаров в текущей корзине
     * @return mixed
     */
    public function getList()
    {
        $arID = array();
        $arResult = array();
        $arBasketItems = array();

        $dbBasketItems = $this->basket->GetList(
            array(
                "NAME" => "ASC",
                "ID" => "ASC"
            ),
            array(
                "FUSER_ID" => $this->basket->GetBasketUserID(),
                "LID" => SITE_ID,
                "ORDER_ID" => "NULL"
            ),
            false,
            false,
            array("ID", "PRODUCT_PROVIDER_CLASS", "MODULE",
                "PRODUCT_ID", "QUANTITY", "NAME", "DETAIL_PAGE_URL", "DELAY",
                "CAN_BUY", "PRICE", "WEIGHT")

        );
        while ($arItems = $dbBasketItems->Fetch()) {
            if ('' != $arItems['PRODUCT_PROVIDER_CLASS'] || '' != $arItems["CALLBACK_FUNC"]) {
                $this->basket->UpdatePrice($arItems["ID"],
                    $arItems["CALLBACK_FUNC"],
                    $arItems["MODULE"],
                    $arItems["PRODUCT_ID"],
                    $arItems["QUANTITY"],
                    "N",
                    $arItems["PRODUCT_PROVIDER_CLASS"]
                );
                $arID[] = $arItems["ID"];
            }
        }

        if (!empty($arID)) {
            $dbBasketItems = $this->basket->GetList(
                array(
                    "NAME" => "ASC",
                    "ID" => "ASC"
                ),
                array(
                    "ID" => $arID,
                    "ORDER_ID" => "NULL"
                ),
                false,
                false,
                array("ID", "PRODUCT_PROVIDER_CLASS", "NAME", "DETAIL_PAGE_URL", "MODULE",
                    "PRODUCT_ID", "QUANTITY", "DELAY",
                    "CAN_BUY", "PRICE", "WEIGHT")
            );
            if (intval($dbBasketItems->SelectedRowsCount()) > 0) {

                $allPrice = 0;
                $quantity = 0;
                while ($arItems = $dbBasketItems->Fetch()) {
                    $allPrice = $allPrice + $arItems['PRICE'] * $arItems['QUANTITY'];
                    $quantity = $quantity + $arItems['QUANTITY'];

                    $mxResult = $this->sku->GetProductInfo(
                        $arItems["PRODUCT_ID"]
                    );
                    if (is_array($mxResult)) {
                        $id_prod = $mxResult['ID'];
                    };

                    $res = $this->element->GetByID($id_prod);
                    if ($ar_res = $res->GetNext()) {
                        if (!empty($ar_res['~PREVIEW_PICTURE'])) {
                            $arImage = CFile::ResizeImageGet(
                                $ar_res['~PREVIEW_PICTURE'],
                                array("width" => 120, "height" => 180),
                                BX_RESIZE_IMAGE_PROPORTIONAL
                            );
                        } else {
                            $arImage = array('src' => ELEMPL);
                        }
                        $arItems['NAME'] = mb_ucfirst($ar_res['NAME']);//название товара а не торг.предл
                    }
                    $arItems['IMAGE'] = $arImage;

                    //  все свойства элемента корзины
                    $db_res = $this->basket->GetPropsList(
                        array(
                            "SORT" => "ASC",
                            "NAME" => "ASC"
                        ),
                        array("BASKET_ID" => $arItems['ID'])
                    );
                    while ($ar_res = $db_res->Fetch()) {
                        $arItems['PROPS'][] = $ar_res;
                    }

                    $arBasketItems[] = $arItems;
                    $arOrder["BASKET_ITEMS"][] = $arItems;

                }
                global $USER;
                $arOrder['SITE_ID'] = SITE_ID;
                $arOrder['USER_ID'] = $USER->GetID();
                \CSaleDiscount::DoProcessOrder($arOrder, array(), $arErrors);
                $arResult['ITEMS'] = $arOrder["BASKET_ITEMS"];
                $arResult['ALL_PRICE'] = $allPrice;
                $arResult['QUANTITY'] = $quantity;
                $this->result['status'] = true;
                $this->result['result'] = $arResult;
            }
        }

        return $this->result;
    }

    /**
     * возвращает список товаров по id заказа
     * @param int $orderId id заказа
     * @return mixed
     */
    public function getById($orderId)
    {
        $arID = array();
        $arResult = array();
        $arBasketItems = array();


        $dbBasketItems = $this->basket->GetList(
            array(
                "NAME" => "ASC",
                "ID" => "ASC"
            ),
            array(
                "ORDER_ID" => $orderId
            ),
            false,
            false,
            array("*")
        );
        if (intval($dbBasketItems->SelectedRowsCount()) > 0) {

            $allPrice = 0;
            $quantity = 0;
            $allBasePrice = 0;
            while ($arItems = $dbBasketItems->Fetch()) {
                $allPrice = $allPrice + $arItems['PRICE'] * $arItems['QUANTITY'];
                $allBasePrice = $allBasePrice + $arItems['BASE_PRICE'] * $arItems['QUANTITY'];
                $quantity = $quantity + $arItems['QUANTITY'];

                $mxResult = $this->sku->GetProductInfo(
                    $arItems["PRODUCT_ID"]
                );
                if (is_array($mxResult)) {
                    $id_prod = $mxResult['ID'];
                };

                $res = $this->element->GetByID($id_prod);
                if ($ar_res = $res->GetNext()) {
                    if (!empty($ar_res['~PREVIEW_PICTURE'])) {
                        $arImage = \CFile::ResizeImageGet(
                            $ar_res['~PREVIEW_PICTURE'],
                            array("width" => 120, "height" => 180),
                            BX_RESIZE_IMAGE_PROPORTIONAL
                        );
                    } else {
                        $arImage = array('src' => ELEMPL);
                    }
                    $arItems['NAME'] = mb_ucfirst($ar_res['NAME']);//название товара а не торг.предл
                }
                $arItems['IMAGE'] = $arImage;

                //  все свойства элемента корзины
                $db_res = $this->basket->GetPropsList(
                    array(
                        "SORT" => "ASC",
                        "NAME" => "ASC"
                    ),
                    array("BASKET_ID" => $arItems['ID'])
                );
                while ($ar_res = $db_res->Fetch()) {
                    $arItems['PROPS'][$ar_res['CODE']] = $ar_res;
                }

                $arBasketItems[] = $arItems;
                $arOrder["BASKET_ITEMS"][] = $arItems;

            }
            global $USER;
            $arOrder['SITE_ID'] = SITE_ID;
            $arOrder['USER_ID'] = $USER->GetID();
            $arResult['ALL_PRICE'] = $allPrice;
            $arResult['ALL_BASE_PRICE'] = $allBasePrice;
            $arResult['QUANTITY'] = $quantity;
//            \CSaleDiscount::DoProcessOrder($arOrder, array(), $arErrors);
            $arResult["BASKET_ITEMS"] = $arOrder["BASKET_ITEMS"];
        }

        return $arResult;
    }
}