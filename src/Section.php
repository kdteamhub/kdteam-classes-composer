<?php
/**
 * Created by PhpStorm.
 * User: a.krylov
 * Date: 23.01.2018
 * Time: 9:24
 */

namespace Kdteam;

class Section
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
    }

    /**
     * возвращает массив всех категорий инфоблока
     * @param array $arFilterFields массив полей, по которым будет отфильтрован результат
     * @param array $arSelectFields массив названий полей, которые должны быть получены по каждой категории
     * @return array
     */
    public function getList($arFilterFields = array(), $arSelectFields = array())
    {
        $arFilter = array('IBLOCK_ID' => $this->iblockId);
        $arSelect = array('ID');


        if (count($arFilterFields) > 0) {
            foreach ($arFilterFields as $key => $value) {
                $arFilter[$key] = $value;
            }
        }
        if (count($arSelectFields) > 0) {
            foreach ($arSelectFields as $key => $value) {
                $arSelect[] = $value;
            }
        }

        $db_list = \CIBlockSection::GetList(false, $arFilter, false, $arSelect);
        $arReturn = array();
        while ($obEl = $db_list->GetNext()) {
            array_push($arReturn, $obEl);
        }

        return $arReturn;
    }

    /**
     * возвращает массив всех АКТИВНЫХ категорий инфоблока
     * @param array $arFilterFields массив полей, по которым будет отфильтрован результат
     * @param array $arSelectFields массив названий полей, которые должны быть получены по каждой категории
     * @return array
     */
    public function getListActive($arFilterFields = array(), $arSelectFields = array())
    {
        unset($arFilterFields['ACTIVE']);
        $arFilterFields['ACTIVE'] = 'Y';
        return $this->getList($arFilterFields, $arSelectFields);
    }

    /**
     * возвращает ид категории по ее символьному коду
     * @param string $strCategoryCode символьный код
     * @return bool|int ид категории или false при ошибке
     * @throws \Exception
     */
    public function getByCode($strCategoryCode = '', $arSelectFields = array())
    {
        if (!$strCategoryCode) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $strCategoryCode is incorrect');
            return false;
        }

        $arFilter = Array('IBLOCK_ID' => $this->iblockId, 'CODE' => $strCategoryCode);
        $db_list = \CIBlockSection::GetList(false, $arFilter, false, $arSelectFields);
        if ($obEl = $db_list->GetNext()) {
            return $obEl;
        }

        return false;
    }

    public function getIdByCode($strCategoryCode = '')
    {
        return $this->getByCode($strCategoryCode, array('ID'))['ID'];
    }

    public function getName($intId = '')
    {
        if (!is_numeric($intId)) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $intId is incorrect');
            return false;
        }

        $arFilter = Array('IBLOCK_ID' => $this->iblockId, 'ID' => $intId);
        $db_list = \CIBlockSection::GetList(false, $arFilter, false, array('NAME'));
        if ($obEl = $db_list->GetNext()) {
            return $obEl;
        }

        return false;
    }


    /**
     * возвращает массив ид-ров категорий самого верхнего уровня
     * @return array
     * @todo написана по старому стандарту. переписать в новый
     */
    public function getListActiveToplevel()
    {
        $arReturn = array();
        $items = GetIBlockSectionList($this->iblockId, 0, Array("sort" => "asc"), false, array('GLOBAL_ACTIVE' => 'Y'));
        while ($arItem = $items->GetNext()) {
            $arReturn[] = $arItem['ID'];
        }
        return $arReturn;
    }

    /**
     * возвращает ид прямого предка на 1 шаг (НЕ РЕКУРСИВНАЯ ФУНКЦИЯ)
     * @param int $intSectionId ид секции
     * @return bool|int ид прямого предка или false
     * @throws \Exception
     */
    public function getParentOne($intSectionId = 0)
    {
        if (!is_numeric($intSectionId)) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $intSectionId is incorrect');
            return false;
        }

        $res_parent = \CIBlockSection::GetByID($intSectionId);
        if ($ar_res_parent = $res_parent->GetNext()) {
            return $ar_res_parent['IBLOCK_SECTION_ID'];
        }
    }
}

class SectionAdditionalFields extends Section
{
    /**
     * обновляет кастомное свойство (дополнительное UF_ поле) секции
     * @param int $intSectionId ид секции
     * @param string $strPropertyName название поля
     * @param string $strPropertyValue значение поля
     * @return bool успех/неудача
     * @throws \Exception неправильные аргументы
     */
    public function set($intSectionId = 0, $strPropertyName = '', $strPropertyValue = '')
    {
        if (!is_numeric($intSectionId)) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' intSectionId is incorrect');
            return false;
        }

        if (!$strPropertyName) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' strPropertyName is incorrect');
            return false;
        }

        if (!$strPropertyValue) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' strPropertyValue is incorrect');
            return false;
        }

        $bs = new \CIBlockSection;
        $arFields = array(
            $strPropertyName => $strPropertyValue,
        );
        if ($bs->Update($intSectionId, $arFields) == true) {
            return true;
        } else {
            throw new \Exception($bs->LAST_ERROR);
            return false;
        }
    }

    /**
     * получает значение кастомного свойства (дополнительное UF_ поля) секции
     * @param int $intSectionId ид секции
     * @param string $strPropertyName название поля
     * @return bool успех/неудача
     * @throws \Exception неправильные аргументы
     */
    public function get($intSectionId = 0, $strPropertyName = '')
    {

        if (!is_numeric($intSectionId)) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' intSectionId is incorrect');
            return false;
        }

        if (!$strPropertyName) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' strPropertyName is incorrect');
            return false;
        }

        $arFilter = array('IBLOCK_ID' => $this->iblockId, 'ID' => $intSectionId);
        $arSelect = array('ID', $strPropertyName);

        $db_list = \CIBlockSection::GetList(false, $arFilter, false, $arSelect);
        if ($obEl = $db_list->GetNext()) {
            return $obEl[$strPropertyName];
        }

        return false;
    }
}

class SectionMainFields extends Section
{

}