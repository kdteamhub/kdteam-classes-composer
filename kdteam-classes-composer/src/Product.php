<?php
/**
 * Created by PhpStorm.
 * User: a.krylov
 * Date: 23.01.2018
 * Time: 9:24
 */

namespace Kdteam;

class Product
{
    public $iblockId;

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

    public function getList($arFilterFields = array(), $arSelectFields = array())
    {
        $arFilter = array(
            "IBLOCK_ID" => $this->iblockId
        );
        $arSelect = array('ID');

        if (count($arFilterFields) > 0) {
            foreach ($arFilterFields as $key => $value) {
                if ($key && $value) {
                    $arFilter[$key] = $value;
                }
            }
        }
        if (count($arSelectFields) > 0) {
            foreach ($arSelectFields as $key => $value) {
                $arSelect[] = $value;
            }
        }

        $res = \CIBlockElement::GetList(array("UPDATED" => "ASC"), $arFilter, false, false, $arSelect);
        $arReturn = array();

        while ($ob = $res->GetNextElement()) {
            $arProduct = $ob->GetFields();
            array_push($arReturn, $arProduct);
        }

        return $arReturn;
    }

    public function getListActive($arFilterFields = array(), $arSelectFields = array())
    {
        unset($arFilterFields['ACTIVE']);
        $arFilterFields['ACTIVE'] = 'Y';
        return $this->getList($arFilterFields, $arSelectFields);
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

    public function update($intProductId = 0, $strPropertyName = '', $strPropertyValue = '')
    {
        if (!is_numeric($intProductId)) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $intProductId is incorrect');
            return false;
        }
        if (!$strPropertyName) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $strPropertyName is incorrect');
            return false;
        }

        $db_old_groups = \CIBlockElement::GetElementGroups($intProductId, true);
        $arGroups = array();
        while ($ar_group = $db_old_groups->Fetch()) {
            array_push($arGroups, $ar_group['ID']);
        }

        $el = new \CIBlockElement;
        $arProductProps = Array(
            $strPropertyName => $strPropertyValue,
        );

        // если устанавливается основная секция для продукта и она отсуствует в списке секций - добавить в него
        if ($arProductProps['IBLOCK_SECTION_ID'] && !in_array($arProductProps['IBLOCK_SECTION_ID'], $arGroups)) {
            array_push($arGroups, $arProductProps['IBLOCK_SECTION_ID']);
        }

        if ($res = $el->Update($intProductId, $arProductProps)) {
            \CIBlockElement::SetElementSection($intProductId, $arGroups);
            return true;
        }

    }

    public function deactivate($intProductId = 0)
    {
        if (!is_numeric($intProductId)) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $intProductId is incorrect');
            return false;
        }

        $this->update($intProductId, 'ACTIVE', 'N');
        return true;
    }

    public function activate($intProductId = 0)
    {
        if (!is_numeric($intProductId)) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $intProductId is incorrect');
            return false;
        }

        $this->update($intProductId, 'ACTIVE', 'Y');
        return true;
    }

    /**
     * проверяет наличие в бд товара с заданным артикулом
     * @param string $strSku артикул
     * @param string $strSkuFieldName имя поля, в котором нужно искать артикул (доп свойство)
     * @return bool true - товар существует
     * @throws \Exception
     */
    public function checkIfExistBySku($strSku = '', $strSkuFieldName = '')
    {
        if (!$strSku) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $strSku is incorrect');
            return false;
        }

        if (!$strSkuFieldName) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $strSkuFieldName is incorrect');
            return false;
        }

        $arrFilter = array(
            "IBLOCK_ID" => $this->iblockId,
            'PROPERTY_' . $strSkuFieldName => $strSku
        );
        $res = \CIBlockElement::GetList(
            array("sort" => "ASC"),
            $arrFilter,
            false,
            false,
            array("ID", 'PROPERTY_' . $strSkuFieldName)
        );
        if ($res->SelectedRowsCount() > 0) {
            return true;
        }
        return false;
    }

    /**
     * возвращает ид элемента по его названию
     * @param string $strName имя элемента
     * @param null|string $strActive null - без учета активности; Y|N - явное указание нужной активности
     * @return bool|int ид элемента или false при ошибке
     * @throws \Exception
     */
    public function getIdByName($strName = '', $strActive = null)
    {
        if (!$strName) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $strName is incorrect');
            return false;
        }

        if ($strActive != 'Y' && $strActive != 'N' && $strActive != null) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $strActive is incorrect');
            return false;
        }

        $arrFilter = array(
            "NAME" => $strName
        );
        if (in_array($strActive, array('Y', 'N'))) {
            $arrFilter['ACTIVE'] = $strActive;
        }

        $arReturn = $this->getList($arrFilter, array('NAME'))[0];

        if (!is_numeric($arReturn['ID'])) {
            return false;
        }
        return $arReturn['ID'];
    }

    /**
     * создает товар по имени, генерирует симв код и возвращает ид созданного продукта
     * @param string $strName
     * @param array $arSections
     * @param array $arProperties
     * @return bool
     * @throws \Exception
     */
    public function add($strName = '', $arSections = array(), $arProperties = array())
    {
        if (!$strName) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $strProductName is incorrect');
            return false;
        }

        $params = array(
            "max_len" => "100", // обрезает символьный код до 100 символов
            "change_case" => "L", // буквы преобразуются к нижнему регистру
            "replace_space" => "_", // меняем пробелы на нижнее подчеркивание
            "replace_other" => "_", // меняем левые символы на нижнее подчеркивание
            "delete_repeat_replace" => "true", // удаляем повторяющиеся нижние подчеркивания
            "use_google" => "false", // отключаем использование google
        );

        $arFields = array(
            "IBLOCK_ID" => $this->iblockId,
            'IBLOCK_SECTION_ID' => $arSections,
            "NAME" => $strName,
            "CODE" => \CUtil::translit($strName, "ru", $params) . rand(0, 9999999999999999),
            "ACTIVE" => "Y",
            "DETAIL_TEXT_TYPE" => "html",
            'PROPERTY_VALUES' => $arProperties
        );

        $obElement = new \CIBlockElement();

        $ID = $obElement->Add($arFields);
        if ($ID < 1) {
            throw new \Exception(
                __CLASS__ . ' '
                . __METHOD__
                . ' can not add new product! '
                . $obElement->LAST_ERROR
            );
            return false;
        }

        return $ID;

    }

}
