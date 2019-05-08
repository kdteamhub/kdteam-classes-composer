<?php
/**
 * Created by PhpStorm.
 * User: a.krylov
 * Date: 25.01.2018
 * Time: 10:41
 */

namespace Kdteam;

class FacetIndex
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
     * пересоздает фасетный индекс для ИБ $this->iblockid
     * сразу запускает переиндексацию без ограничения по времени
     * НЕ ИСПОЛЬЗОВАТЬ ДЛЯ ИНТЕРФЕЙСНЫХ ВЕЩЕЙ, только для ssh-скриптов
     */
    public function reCreate()
    {
        \Bitrix\Iblock\PropertyIndex\Manager::DeleteIndex($this->iblockId);
        \Bitrix\Iblock\PropertyIndex\Manager::markAsInvalid($this->iblockId);
        $index = \Bitrix\Iblock\PropertyIndex\Manager::createIndexer($this->iblockId);
        $index->startIndex();
        $index->continueIndex(0); // создание без ограничения по времени
        $index->endIndex();
    }

}