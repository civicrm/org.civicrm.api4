<?php

namespace Civi\Api4\Service\Spec;

use CRM_Core_DAO_AllCoreTables as TableHelper;
use CRM_Utils_Array as ArrayHelper;

/**
 * Class SpecFormatter.
 */
class SpecFormatter
{
	/**
	 * @param RequestSpec $spec
	 *
	 * @return array
	 */
	public static function specToArray(RequestSpec $spec)
	{
		$specArray = [];
		$specArray['entity'] = $spec->getEntity();
		$specArray['action'] = $spec->getAction();
		$specArray['fields'] = [];

		foreach ($spec->getFields() as $field) {
			$specArray['fields'][$field->getName()] = $field->toArray();
		}

		return $specArray;
	}

	/**
	 * @param array $data
	 *
	 * @throws \Exception
	 *
	 * @return FieldSpec
	 */
	public static function arrayToField(array $data)
	{
		$dataTypeName = self::getDataType($data);

		if (!empty($data['custom_group_id'])) {
			$name = $data['custom_group']['name'].'.'.$data['name'];
			$field = new CustomFieldSpec($name, $dataTypeName);
			$field->setCustomFieldId(ArrayHelper::value('id', $data));
			$field->setCustomGroupId($data['custom_group_id']);
			$field->setRequired((bool) ArrayHelper::value('is_required', $data, false));
			$field->setTitle(ArrayHelper::value('label', $data));
			if (\CRM_Core_BAO_CustomField::isSerialized($data)) {
				$field->setSerialize(\CRM_Core_DAO::SERIALIZE_SEPARATOR_BOOKEND);
			}
		} else {
			$name = ArrayHelper::value('name', $data);
			$field = new FieldSpec($name, $dataTypeName);
			$field->setRequired((bool) ArrayHelper::value('required', $data, false));
			$field->setTitle(ArrayHelper::value('title', $data));
			$field->setSerialize(ArrayHelper::value('serialize', $data));
		}

		$field->setDefaultValue(ArrayHelper::value('default', $data));
		$field->setDescription(ArrayHelper::value('description', $data));

		$fkClassName = ArrayHelper::value('FKClassName', $data);
		$fkAPIName = ArrayHelper::value('FKApiName', $data);
		$fkEntity = $fkAPIName ?: TableHelper::getBriefName($fkClassName);
		$field->setFkEntity($fkEntity);

		return $field;
	}

	/**
	 * Get the data type from an array. Defaults to 'data_type' with fallback to
	 * mapping for the integer value 'type'.
	 *
	 * @param array $data
	 *
	 * @return string
	 */
	private static function getDataType(array $data)
	{
		if (isset($data['data_type'])) {
			return $data['data_type'];
		}

		$dataTypeInt = ArrayHelper::value('type', $data);
		$dataTypeName = \CRM_Utils_Type::typeToString($dataTypeInt);

		return $dataTypeName;
	}
}
