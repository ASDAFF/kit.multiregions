<?

use Bitrix\Main\Localization\Loc;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
Bitrix\Main\Loader::includeModule('kit.multiregions');
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/kit.multiregions/prolog.php");

Loc::loadMessages(__FILE__);

$modulePermissions = $APPLICATION->GetGroupRight("kit.multiregions");
if ($modulePermissions < "W") {
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

$isSaleModule = CKitMultiRegions::isIMExists();

$sTableID = "tbl_kit_multiregions_country";

$oSort = new CAdminSorting($sTableID, "NAME", "asc");
$arOrder = (amreg_strtoupper($by) === "ID" ? array($by => $order) : array($by => $order, "ID" => "ASC"));
$lAdmin = new CAdminUiList($sTableID, $oSort);

$filterFields = array(
	array(
		"id" => "ID",
		"name" =>  Loc::getMessage("KIT_MULTIREGIONS_FILTER_ID"),
		"type" => "number",
		"filterable" => ""
	),
	array(
		"id" => "CODE",
		"name" => Loc::getMessage("KIT_MULTIREGIONS_FILTER_CODE"),
		"filterable" => "?",
		"quickSearch" => "?",
		"default" => false,
	),
	array(
		"id" => "CONTINENT",
		"name" => Loc::getMessage("KIT_MULTIREGIONS_FILTER_CONTINENT"),
		"filterable" => "",
		"params" => array("multiple" => "Y"),
		"type" => "list",
		"items" => array(
			"AF" => Loc::getMessage("kit.multiregions_CONTINENT_AF"),
			"AN" => Loc::getMessage("kit.multiregions_CONTINENT_AN"),
			"AS" => Loc::getMessage("kit.multiregions_CONTINENT_AS"),
			"EU" => Loc::getMessage("kit.multiregions_CONTINENT_EU"),
			"NA" => Loc::getMessage("kit.multiregions_CONTINENT_NA"),
			"OC" => Loc::getMessage("kit.multiregions_CONTINENT_OC"),
			"SA" => Loc::getMessage("kit.multiregions_CONTINENT_SA"),
		),
		"default" => false,
	),
	array(
		"id" => "NAME",
		"name" => Loc::getMessage("KIT_MULTIREGIONS_FILTER_NAME"),
		"filterable" => "?",
		"quickSearch" => "?",
		"default" => true,
	),
);
$arFilter = array();
$lAdmin->AddFilter($filterFields, $arFilter);

if (($arID = $lAdmin->GroupAction()) && $modulePermissions >= "W") {
	if ($_REQUEST['action_target'] == 'selected') {
		$arID = Array();
		$dbResultList = \Kit\MultiRegions\CountryTable::getList(array(
			"order" => $arOrder,
			"filter" => $arFilter,
			"select" => array("ID")));
		while ($arResult = $dbResultList->Fetch()) {
			$arID[] = $arResult['ID'];
		}
	}
	foreach ($arID as $ID) {
		if (amreg_strlen($ID) <= 0) {
			continue;
		}

		switch ($_REQUEST['action']) {
			case "delete":
				@set_time_limit(0);
				$rRecord = \Kit\MultiRegions\CountryTable::getList(array(
					"filter" => array("ID" => $ID),
					"select" => array("ID"),
				));
				$arRecordOld = $rRecord->Fetch();
				$DB->StartTransaction();
				$rLinkRecord = \Kit\MultiRegions\CityTable::getList(array(
					"filter" => array(
						"REGION.COUNTRY_ID" => $ID,
					),
				));
				$bComplete = true;
				while ($arLinkRecord = $rLinkRecord->fetch()) {
					$rOperation = \Kit\MultiRegions\CityTable::delete($arLinkRecord['ID']);
					if (!$rOperation->isSuccess()) {
						$bComplete = false;
					}
				}
				if ($bComplete) {
					$rLinkRecord = \Kit\MultiRegions\RegionTable::getList(array(
						"filter" => array(
							"COUNTRY_ID" => $ID,
						),
					));
					$bComplete = true;
					while ($arLinkRecord = $rLinkRecord->fetch()) {
						$rOperation = \Kit\MultiRegions\RegionTable::delete($arLinkRecord['ID']);
						if (!$rOperation->isSuccess()) {
							$bComplete = false;
						}
					}
				}
				if ($bComplete) {
					$rOperation = \Kit\MultiRegions\CountryTable::delete($ID);
				}
				if (!$rOperation->isSuccess()) {
					$DB->Rollback();
					if ($ex = $APPLICATION->GetException()) {
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					} else {
						$lAdmin->AddGroupError(Loc::getMessage("KIT_MULTIREGIONS_DELETE_ERROR"), $ID);
					}
				}
				$DB->Commit();
				break;
		}
	}
}

$arHeader = array(
	array(
		"id" => "ID",
		"content" => Loc::getMessage("KIT_MULTIREGIONS_FIELD_ID"),
		"sort" => "ID",
		"default" => true,
	),
	array(
		"id" => "CODE",
		"content" => Loc::getMessage("KIT_MULTIREGIONS_FIELD_CODE"),
		"sort" => "CODE",
		"default" => true,
	),
	array(
		"id" => "CONTINENT",
		"content" => Loc::getMessage("KIT_MULTIREGIONS_FIELD_CONTINENT"),
		"sort" => "CONTINENT",
		"default" => true,
	),
	array(
		"id" => "NAME",
		"content" => Loc::getMessage("KIT_MULTIREGIONS_FIELD_NAME"),
		"sort" => "NAME",
		"default" => true,
	),
	array(
		"id" => "LAT",
		"content" => Loc::getMessage("KIT_MULTIREGIONS_FIELD_LAT"),
		"sort" => "LAT",
		"default" => false,
	),
	array(
		"id" => "LON",
		"content" => Loc::getMessage("KIT_MULTIREGIONS_FIELD_LON"),
		"sort" => "LON",
		"default" => false,
	),
	array(
		"id" => "TIMEZONE",
		"content" => Loc::getMessage("KIT_MULTIREGIONS_FIELD_TIMEZONE"),
		"sort" => "TIMEZONE",
		"default" => false,
	),
	array(
		"id" => "LOCATION_ID",
		"content" => Loc::getMessage("KIT_MULTIREGIONS_FIELD_LOCATION_ID"),
		"sort" => "LOCATION_ID",
		"default" => $isSaleModule,
	),
	array(
		"id" => "EXT_ID",
		"content" => Loc::getMessage("KIT_MULTIREGIONS_FIELD_EXT_ID"),
		"sort" => "EXT_ID",
		"default" => false,
	),
	array(
		"id" => "REGION_CNT",
		"content" => Loc::getMessage("KIT_MULTIREGIONS_FIELD_REGION_CNT"),
		"sort" => "REGION_CNT",
		"default" => true,
	),
	array(
		"id" => "CITY_CNT",
		"content" => Loc::getMessage("KIT_MULTIREGIONS_FIELD_CITY_CNT"),
		"sort" => "CITY_CNT",
		"default" => true,
	),
);

$lAdmin->AddHeaders($arHeader);

$usePageNavigation = true;
$navyParams = array();
if (isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'excel') {
	$usePageNavigation = false;
} else {
	$navyParams = CDBResult::GetNavParams(CAdminUiResult::GetNavSize($sTableID));
	if ($navyParams['SHOW_ALL']) {
		$usePageNavigation = false;
	} else {
		$navyParams['PAGEN'] = (int)$navyParams['PAGEN'];
		$navyParams['SIZEN'] = (int)$navyParams['SIZEN'];
	}
}
$getListParams = array(
	"order" => $arOrder,
	"filter" => $arFilter,
	"select" => array("*", "REGION_CNT", "CITY_CNT")
);

if ($usePageNavigation) {
	$getListParams['limit'] = $navyParams['SIZEN'];
	$getListParams['offset'] = $navyParams['SIZEN'] * ($navyParams['PAGEN'] - 1);
}
$totalCount = 0;
$totalPages = 0;
if ($usePageNavigation) {
	$totalCount = \Kit\MultiRegions\CountryTable::getCount($getListParams['filter']);
	if ($totalCount > 0) {
		$totalPages = ceil($totalCount / $navyParams['SIZEN']);
		if ($navyParams['PAGEN'] > $totalPages)
			$navyParams['PAGEN'] = $totalPages;
	} else {
		$navyParams['PAGEN'] = 1;
	}
	$getListParams['limit'] = $navyParams['SIZEN'];
	$getListParams['offset'] = $navyParams['SIZEN'] * ($navyParams['PAGEN'] - 1);
}

$rsItems = \Kit\MultiRegions\CountryTable::getList($getListParams);
$rsItems = new CAdminUiResult($rsItems, $sTableID);
if ($usePageNavigation) {
	$rsItems->NavStart($getListParams['limit'], $navyParams['SHOW_ALL'], $navyParams['PAGEN']);
	$rsItems->NavRecordCount = $totalCount;
	$rsItems->NavPageCount = $totalPages;
	$rsItems->NavPageNomer = $navyParams['PAGEN'];
} else {
	$rsItems->NavStart();
}
$lAdmin->SetNavigationParams($rsItems);

while ($arData = $rsItems->NavNext()) {
	$row =& $lAdmin->AddRow($arData['ID'], $arData, 'kit.multiregions.country.edit.php?ID=' . $arData['ID'] . '&lang=' . LANGUAGE_ID, Loc::getMessage("KIT_MULTIREGIONS_RECORD_EDIT"));
	$row->AddViewField("ID", '<a href="kit.multiregions.country.edit.php?ID=' . $arData['ID'] . '&lang=' . LANGUAGE_ID . '">' . $arData['ID'] . '</a>');
	$row->AddViewField("CONTINENT", Loc::getMessage("kit.multiregions_CONTINENT_" . $arData['CONTINENT']));
	if ($isSaleModule) {
		if ($arData['LOCATION_ID'] > 0) {
			$arLocation = Bitrix\Sale\Location\LocationTable::getList(array(
				"filter" => array(
					"ID" => $arData['LOCATION_ID'],
					"NAME.LANGUAGE_ID" => LANGUAGE_ID,
				),
				"select" => array("*", "NAME_LANG" => "NAME.NAME"),
			))->fetch();
			$row->AddViewField("LOCATION_ID", '[' . $arData['LOCATION_ID'] . '] <a href="/bitrix/admin/sale_location_node_edit.php?id=' . $arData['LOCATION_ID'] . '&lang=' . LANGUAGE_ID . '">' . $arLocation['NAME_LANG'] . '</a>');
		} else {
			$row->AddViewField("LOCATION_ID", "&nbsp;");
		}
	}
	$arActions = array();
	if ($modulePermissions >= "W") {
		$arActions[] = array(
			"ICON" => "edit",
			"TEXT" => Loc::getMessage("MAIN_ADMIN_MENU_EDIT"),
			"DEFAULT" => true,
			"ACTION" => $lAdmin->ActionRedirect("kit.multiregions.country.edit.php?ID=" . $arData['ID'] . "&lang=" . LANGUAGE_ID),
		);
		$arActions[] = array(
			"SEPARATOR" => true,
		);
		$arActions[] = array(
			"ICON" => "delete",
			"TEXT" => GetMessage("KIT_MULTIREGIONS_ACTION_DELETE"),
			"ACTION" => "if(confirm('" . GetMessage('KIT_MULTIREGIONS_ACTION_DELETE_CONFIRM') . "')) " . $lAdmin->ActionDoGroup($arData['ID'], "delete"),
		);
	}

	if (count($arActions) > 0) {
		$row->AddActions($arActions);
	}
}

$lAdmin->AddFooter(
	array(
		array("title" => Loc::getMessage("MAIN_ADMIN_LIST_SELECTED"), "value" => $rsItems->SelectedRowsCount()),
		array("counter" => true, "title" => Loc::getMessage("MAIN_ADMIN_LIST_CHECKED"), "value" => "0"),
	)
);

if ($modulePermissions >= "W") {
	$aContext = array();

	$lAdmin->AddAdminContextMenu($aContext);

	$lAdmin->AddGroupActionTable(Array(
		"delete" => true,
	));
}

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage("KIT_MULTIREGIONS_PAGE_TITLE"));

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayFilter($filterFields);

$lAdmin->DisplayList();
CKitMultiRegions::showSupportForm();
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");