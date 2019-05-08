<?php
/**
 * Created by PhpStorm.
 * User: a.krylov
 * Date: 06.06.2018
 * Time: 15:22
 */

namespace Kdteam;

class Store
{
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

        if (!\CModule::IncludeModule("catalog")) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' module catalog not included');
            return false;
        }
    }

    /**
     * возвращает ид склада по его коду
     * @param string $strStoreCode код склада
     * @return bool|int ид склада или false
     * @throws \Exception
     */
    public static function getIdByCode($strStoreCode = '')
    {
        if (!$strStoreCode) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' getIdByCode is incorrect');
            return false;
        }

        $dbResult = \CCatalogStore::GetList(
            array('PRODUCT_ID' => 'ASC', 'ID' => 'ASC'),
            array('ACTIVE' => 'Y', 'CODE' => $strStoreCode),
            false,
            false,
            array("ID")
        );
        if ($arRes = $dbResult->GetNext()) {
            return $arRes['ID'];
        }
        return false;
    }

    /**
     * возвращает все склады согласно фильтру
     * @param array $arFilter массив данных фильтрации
     * @param array $arSelect массив полей для возвращения
     * @return array массив складов
     */
    public function getList($arFilter = array(), $arSelect = array())
    {
        $arFilterFields = array();
        foreach ($arFilter as $key => $value) {
            $arFilterFields[$key] = $value;
        }

        $arSelectFields = array('ID');
        foreach ($arSelect as $value) {
            if (!in_array($arSelectFields, $value)) {
                array_push($arSelectFields, $value);
            }
        }

        $dbResult = \CCatalogStore::GetList(
            array('PRODUCT_ID' => 'ASC', 'ID' => 'ASC'),
            $arFilterFields,
            false,
            false,
            $arSelectFields
        );
        $arReturn = array();
        while ($arRes = $dbResult->GetNext()) {
            array_push($arReturn, $arRes);
        }
        return $arReturn;
    }
}