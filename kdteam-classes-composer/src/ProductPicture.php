<?php
/**
 * Created by PhpStorm.
 * User: a.krylov
 * Date: 01.03.2018
 * Time: 17:31
 */

namespace Kdteam;


class ProductPicture extends Product
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

    public function getListByProductId($intProductId = 0)
    {
        if (!is_numeric($intProductId)) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $intProductId is incorrect');
            return false;
        }

        $arProductPictures = parent::getListActive(array('ID' => $intProductId), array('PREVIEW_PICTURE', 'DETAIL_PICTURE'));
        $arProductPictures = $arProductPictures[0];

        if ($arProductPictures['PREVIEW_PICTURE'] > 0) {
            $arProductPictures['PREVIEW_PICTURE'] = $this->getLink($arProductPictures['PREVIEW_PICTURE']);
        }

        if ($arProductPictures['DETAIL_PICTURE'] > 0) {
            $arProductPictures['DETAIL_PICTURE'] = $this->getLink($arProductPictures['DETAIL_PICTURE']);
        }

        return $arProductPictures;
    }

    public function getLink($intFileId = 0)
    {
        if (!is_numeric($intFileId)) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $intProductId is incorrect');
            return false;
        }

        $rsFile = \CFile::GetByID($intFileId);
        if ($arFile = $rsFile->Fetch()) {
            return '/upload/' . $arFile['SUBDIR'] . '/' . $arFile['FILE_NAME'];
        }
        return false;

    }


}