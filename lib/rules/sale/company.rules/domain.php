<?

namespace Kit\MultiRegions\Rules\Sale\CompanyRules;

use Kit\MultiRegions\DomainTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Services\Base;
use Bitrix\Sale\Internals\Entity;

Loc::loadMessages(__FILE__);

class Domain extends Base\Restriction
{
	public static function onSaleCompanyRulesClassNamesBuildList()
	{
		return new \Bitrix\Main\EventResult(
			\Bitrix\Main\EventResult::SUCCESS,
			array(
				'\Kit\MultiRegions\Rules\Sale\CompanyRules\Domain' => '/bitrix/modules/kit.multiregions/lib/rules/sale/company.rules/domain.php',
			)
		);
	}

	public static function getClassTitle()
	{
		return Loc::getMessage("KIT_MULTIREGIONS_COMPANY_RULES_DOMAIN_NAME");
	}

	public static function getClassDescription()
	{
		return Loc::getMessage("KIT_MULTIREGIONS_COMPANY_RULES_DOMAIN_DESCRIPTION");
	}

	public static function check($params, array $restrictionParams, $serviceId = 0)
	{
		if (isset($restrictionParams) && is_array($restrictionParams['DOMAIN']))
			return in_array($params, $restrictionParams['DOMAIN']);

		return true;
	}

	protected static function extractParams(Entity $entity)
	{
		return $GLOBALS['KIT_MULTIREGIONS']['SYS_CURRENT_DOMAIN_ID'];
	}

	public static function getParamsStructure($entityId = 0)
	{
		$arDomainList = array();
		$rDomain = DomainTable::getList(array(
			"select" => array("ID", "NAME", "DOMAIN"),
			"order" => array("NAME" => "ASC", "DOMAIN" => "ASC"),
		));
		while ($arDomain = $rDomain->fetch()) {
			$arDomainList[$arDomain['ID']] = "[" . $arDomain['ID'] . "] " . $arDomain['NAME'] . " (" . $arDomain['DOMAIN'] . ")";
		}
		return array(
			"DOMAIN" => array(
				"TYPE" => "ENUM",
				'MULTIPLE' => 'Y',
				"LABEL" => Loc::getMessage("KIT_MULTIREGIONS_COMPANY_RULES_DOMAIN_PARAMETERS_DOMAIN"),
				"OPTIONS" => $arDomainList,
			),
		);
	}
}