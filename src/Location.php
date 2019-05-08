<?php
/**
 * Created by PhpStorm.
 * User: a.krylov
 * Date: 22.03.2018
 * Time: 18:59
 */

namespace Kdteam;


class Location
{
    function __construct($intIblockId)
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

    public function getById($intLocationId = 0)
    {
        if (!is_numeric($intLocationId)) {
            throw new \Exception(__CLASS__ . ' ' . __METHOD__ . ' $intLocationId is incorrect');
            return false;
        }

        $db_vars = \CSaleLocation::GetList(
            array(
                "SORT" => "ASC",
                "COUNTRY_NAME_LANG" => "ASC",
                "CITY_NAME_LANG" => "ASC"

            ),
            array("LID" => LANGUAGE_ID, 'ID' => $intLocationId),
            false,
            false,
            array()
        );
        while ($vars = $db_vars->Fetch()):
            return $vars;
        endwhile;
    }
}