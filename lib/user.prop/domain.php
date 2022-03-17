<?

namespace Kit\MultiRegions\UserProp;

use Bitrix\Main\Localization\Loc;
use \Bitrix\Main\UserField\TypeBase;

Loc::loadMessages(__FILE__);

class Domain extends TypeBase
{
	const USER_TYPE = 'KitMultiRegionsDomain';

	public static function GetUserTypeDescription()
	{
		return array(
			"USER_TYPE_ID" => self::USER_TYPE,
			"CLASS_NAME" => __CLASS__,
			"DESCRIPTION" => Loc::getMessage("KIT_REGION_USERPROP_DOMAIN_DESCRIPTION"),
			"BASE_TYPE" => \CUserTypeManager::BASE_TYPE_INT,
		);
	}

	public static function GetDBColumnType($arUserField)
	{
		return "int";
	}

	public static function GetEditFormHTML($arUserField, $arHtmlControl)
	{
		if ($arUserField["ENTITY_VALUE_ID"] < 1 && amreg_strlen($arUserField["SETTINGS"]["DEFAULT_VALUE"]) > 0)
			$arHtmlControl["VALUE"] = htmlspecialcharsbx($arUserField["SETTINGS"]["DEFAULT_VALUE"]);
		global $APPLICATION;
		self::doInitJS();
		ob_start();
		$ident = $arHtmlControl["NAME"];
		$ident = str_replace("[", "_", $ident);
		$ident = str_replace("]", "_", $ident);
		$arHtmlControl['VALUE_TEXT'] = "";
		if ($arHtmlControl['VALUE'] > 0) {
			$arHtmlControl['VALUE_TEXT'] = self::getTextValue($arHtmlControl["VALUE"]);
		}
		?>
		<div class="bammultiregionsadm-area-item">
			<input type="hidden" name="<?= htmlspecialcharsbx($arHtmlControl["NAME"]) ?>" id="<?= htmlspecialcharsbx($ident) ?>" value="<?= $arHtmlControl["VALUE"] ?>"/>
			<input type="text" name="TEXTFIELD_<?= htmlspecialcharsbx($arHtmlControl["NAME"]) ?>" id="TEXTFIELD_<?= htmlspecialcharsbx($ident) ?>" size="50" value="<?= $arHtmlControl['VALUE_TEXT'] ?>" data-action="domain" data-result-id="<?= htmlspecialcharsbx($ident) ?>" data-min-length="0" data-cnt="30" class="amr-request-field" autocomplete="off"<?= ($arUserField["EDIT_IN_LIST"] != "Y" ? 'disabled="disabled" ' : '') ?> />
		</div>
		<div clas="bammultiregionsadm-area-item-clear"></div>
		<?
		$strResult = ob_get_contents();
		ob_end_clean();
		return $strResult;
	}


	public static function GetEditFormHTMLMulty($arUserField, $arHtmlControl)
	{
		if (!is_array($arHtmlControl["VALUE"])) {
			$arHtmlControl["VALUE"] = array();
		}
		$html = "";
		$i = -1;
		foreach ($arHtmlControl["VALUE"] as $i => $value) {

			if (
				(is_array($value) && (amreg_strlen(implode("", $value)) > 0))
				|| ((!is_array($value)) && (amreg_strlen($value) > 0))
			) {
				$html .= '<tr><td>' . call_user_func_array(
						array(self, "geteditformhtml"),
						array(
							$arUserField,
							array(
								"NAME" => $arUserField["FIELD_NAME"] . "[" . $i . "]",
								"VALUE" => (is_array($value) ? $value : htmlspecialcharsbx($value)),
							),
						)
					) . '</td></tr>';
			}
		}
		$rowClass = "";
		$FIELD_NAME_X = str_replace('_', 'x', $arUserField["FIELD_NAME"]);
		$fieldHtml = call_user_func_array(
			array(self, "geteditformhtml"),
			array(
				$arUserField,
				array(
					"NAME" => $arUserField["FIELD_NAME"] . "[" . ($i + 1) . "]",
					"VALUE" => "",
					"ROWCLASS" => &$rowClass,
				),
			)
		);
		ob_start();
		?>
		<table id="table_<?= $arUserField["FIELD_NAME"] ?>">
			<?= $html ?>
			<tr>
				<td><?= $fieldHtml ?></td>
			</tr>
			<tr>
				<td style="padding-top: 6px;">
					<input type="button" value="<?= Loc::getMessage("USER_TYPE_PROP_ADD") ?>" onClick="addNewRow('table_<?= $arUserField["FIELD_NAME"] ?>', '<?= $FIELD_NAME_X ?>|<?= $arUserField["FIELD_NAME"] ?>|<?= $arUserField["FIELD_NAME"] ?>_old_id');$('#table_<?= $arUserField["FIELD_NAME"] ?> .amr-request-field:last').kitMultiRegionsAdminQueryField();">
				</td>
			</tr>
		</table>
		<?
		$cont = ob_get_contents();
		ob_end_clean();
		return $cont;
	}

	function GetAdminListViewHTML($arUserField, $arHtmlControl)
	{
		$strResult = '';
		$arResult = self::GetPropertyValue($arUserField, $arHtmlControl);
		if (is_array($arResult)) {
			$strResult = '<a href="/bitrix/admin/kit.multiregions.domain.edit.php?ID=' . $arResult['ID'] . '" title="' . Loc::getMessage("MAIN_EDIT") . '">' . $arResult['NAME'] . '</a>';
		}
		return $strResult;
	}

	protected static function GetPropertyValue($arUserField, $arHtmlControl)
	{
		$mxResult = false;
		if ((int)$arHtmlControl['VALUE'] > 0) {
			$mxResult = array(
				"ID" => $arHtmlControl['VALUE'],
				"NAME" => self::getTextValue($arHtmlControl['VALUE']),
			);
		}
		return $mxResult;
	}

	protected static function doInitJS()
	{
		global $APPLICATION;
		\CJSCore::Init(array("jquery2"));
		\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/js/kit.multiregions/admin/queryfield.js");
		$APPLICATION->SetAdditionalCSS("/bitrix/themes/.default/kit.multiregions.css");
	}

	protected static function getTextValue($ID)
	{
		$strResult = "";
		if ($ID > 0) {
			$arSelect = array(
				"ID",
				"NAME",
				"DOMAIN",
			);
			$arFilter = array(
				"ID" => $ID,
			);
			$arItem = \Kit\MultiRegions\DomainTable::getList(array(
				"filter" => $arFilter,
				"select" => $arSelect,
			))->fetch();
			$arData = array(
				"ID" => $arItem['ID'],
				"NAME" => $arItem['NAME'],
				"DOMAIN" => $arItem['DOMAIN'],
			);
			$strResult = "[" . $arData['ID'] . "] " . $arData['NAME'] . " (" . $arData['DOMAIN'] . ")";
		}
		return $strResult;
	}

}