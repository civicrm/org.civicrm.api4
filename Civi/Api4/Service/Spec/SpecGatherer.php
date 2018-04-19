<?php

namespace Civi\Api4\Service\Spec;

use Civi\Api4\CustomField;
use Civi\Api4\Service\Spec\Provider\SpecProviderInterface;

/**
 * Class SpecGatherer.
 */
class SpecGatherer {

  /**
   * @var \Civi\Api4\Service\Spec\Provider\SpecProviderInterface[]
   */
  protected $specProviders = [];

  /**
   * A cache of DAOs based on entity.
   *
   * @var \CRM_Core_DAO[]
   */
  protected $DAONames;

  /**
   * Returns a RequestSpec with all the fields available. Uses spec providers
   * to add or modify field specifications.
   * For an example @see CustomFieldSpecProvider.
   *
   * @param string $entity
   * @param string $action
   * @param $includeCustom
   *
   * @throws \API_Exception
   * @throws \Civi\API\Exception\NotImplementedException
   * @throws \Civi\API\Exception\UnauthorizedException
   * @throws \Exception
   *
   * @return \Civi\Api4\Service\Spec\RequestSpec
   */
  public function getSpec($entity, $action, $includeCustom) {
    $specification = new RequestSpec($entity, $action);
    $this->addDAOFields($entity, $action, $specification);
    if ($includeCustom) {
      $this->addCustomFields($entity, $specification);
    }
    foreach ($this->specProviders as $provider) {
      if ($provider->applies($entity, $action)) {
        $provider->modifySpec($specification);
      }
    }
    $this->addFieldOptions($specification);
    return $specification;
  }

  /**
   * @param string $entity
   * @param             $action
   * @param RequestSpec $specification
   *
   * @throws \Exception
   */
  private function addDAOFields($entity, $action, RequestSpec $specification) {
    $DAOFields = $this->getDAOFields($entity);
    foreach ($DAOFields as $DAOField) {
      if ('id' === $DAOField['name'] && 'create' === $action) {
        continue;
      }
      $field = SpecFormatter::arrayToField($DAOField);
      $specification->addFieldSpec($field);
    }
  }

  /**
   * @param string $entityName
   *
   * @return array
   */
  private function getDAOFields($entityName) {
    $dao = $this->getDAO($entityName);
    return $dao::fields();
  }

  /**
   * Todo this class should not rely on api3 code.
   *
   * @param $entityName
   *
   * @return \CRM_Core_DAO|string
   *   The DAO name for use in static calls. Return doc block is hacked to allow
   *   auto-completion of static methods
   */
  private function getDAO($entityName) {
    if (!isset($this->DAONames[$entityName])) {
      require_once 'api/v3/utils.php';
      $daoName                     = \_civicrm_api3_get_DAO($entityName);
      $this->DAONames[$entityName] = $daoName;
    }
    return $this->DAONames[$entityName];
  }

  /**
   * @param string $entity
   * @param RequestSpec $specification
   *
   * @throws \API_Exception
   * @throws \Civi\API\Exception\NotImplementedException
   * @throws \Civi\API\Exception\UnauthorizedException
   * @throws \Exception
   */
  private function addCustomFields($entity, RequestSpec $specification) {
    if ('Contact' === $entity) {
      $entity = ['Contact', 'Individual', 'Organization', 'Household'];
    }
    $customFields = CustomField::get()
      ->addWhere('custom_group.extends', 'IN', $entity)
      ->setSelect([
        'custom_group.name',
        'custom_group_id',
        'name',
        'label',
        'data_type',
        'html_type',
        'is_required',
        'is_searchable',
        'is_search_range',
        'weight',
        'is_active',
        'is_view',
        'option_group_id',
        'default_value',
      ])
      ->execute();
    foreach ($customFields as $fieldArray) {
      $field = SpecFormatter::arrayToField($fieldArray);
      $specification->addFieldSpec($field);
    }
  }

  /**
   * @param RequestSpec $spec
   */
  private function addFieldOptions(RequestSpec $spec) {
    $dao = $this->getDAO($spec->getEntity());
    foreach ($spec->getFields() as $field) {
      $fieldName = $field->getName();
      if ($field instanceof CustomFieldSpec) {
        // buildOptions relies on the custom_* type of field names.
        $fieldName = sprintf('custom_%d', $field->getCustomFieldId());
      }
      $options = $dao::buildOptions($fieldName);
      if (!is_array($options)) {
        continue;
      }
      $field->setOptions($options);
    }
  }

  /**
   * @param \Civi\Api4\Service\Spec\Provider\SpecProviderInterface $provider
   */
  public function addSpecProvider(SpecProviderInterface $provider) {
    $this->specProviders[] = $provider;
  }

}
