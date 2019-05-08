<?php

namespace Kdteam;

/**
 * Class ProductSection
 * Операции с секциями (разделами) продукта
 * @package Kdteam
 */
class ProductSection extends Product
{
    /**
     * устанавливает основной раздел для товара (если его использование включено в настройках ИБ)
     * @param int $intProductId ид продукта
     * @param int $intSectionId ид секции(раздела), которую нужно сделать основной
     * @return bool
     * @throws \Exception
     */
    public static function setMain($intProductId = 0, $intSectionId = 0)
    {
        return parent::update($intProductId, 'IBLOCK_SECTION_ID', $intSectionId);
    }

    /**
     * возвращает ид основного раздела иб (если его использование включено в настройках ИБ)
     * @param int $intProductId ид-р продукта
     * @return bool|int false при ошибке, id раздела в остальных случаях
     */
    public static function getMain($intProductId = 0)
    {
        $arReturn = parent::getList(array('ID' => $intProductId), array('IBLOCK_SECTION_ID'))[0];
        if (!is_numeric($arReturn['IBLOCK_SECTION_ID'])) {
            return false;
        }
        return $arReturn['IBLOCK_SECTION_ID'];
    }
}