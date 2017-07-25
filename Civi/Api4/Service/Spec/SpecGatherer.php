<?php

namespace Civi\Api4\Service\Spec;

use Civi\Api4\Service\Spec\Provider\SpecProviderInterface;
use Civi\Api4\Utils\DAOFinder;

class SpecGatherer {

  /**
   * @var SpecProviderInterface[]
   */
  protected $specProviders = array();

  /**
   * Returns a RequestSpec with all the fields available. Uses spec providers
   * to add or modify field specifications.
   * For an example @see CustomFieldSpecProvider.
   *
   * @param string $action
   * @param string $entity
   *
   * @return RequestSpec
   */
  public function getSpec($entity, $action) {
    $specification = new RequestSpec($entity, $action);

    $this->addDAOFields($entity, $specification);

    foreach ($this->specProviders as $provider) {
      if ($provider->applies($entity, $action)) {
        $provider->modifySpec($specification);
      }
    }

    $this->addFieldOptions($specification);

    return $specification;
  }

  /**
   * @param SpecProviderInterface $provider
   */
  public function addSpecProvider(SpecProviderInterface $provider) {
    $this->specProviders[] = $provider;
  }

  /**
   * @param string $entity
   * @param RequestSpec $specification
   */
  private function addDAOFields($entity, RequestSpec $specification) {
    $DAOFields = $this->getDAOFields($entity);

    foreach ($DAOFields as $DAOField) {
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
    $daoName = DAOFinder::getDaoNameForEntity($entityName);

    return $daoName::fields();
  }

  /**
   * @param RequestSpec $spec
   */
  private function addFieldOptions(RequestSpec $spec) {
    $dao = DAOFinder::getDaoNameForEntity($spec->getEntity());

    foreach ($spec->getFields() as $field) {
      $fieldName = $field->getName();

      if ($field instanceof CustomFieldSpec) {
        // buildOptions relies on the custom_* type of field names
        $fieldName = sprintf('custom_%d', $field->getCustomFieldId());
      }

      $options = $dao::buildOptions($fieldName);

      if (!is_array($options)) {
        continue;
      }

      $field->setOptions($options);
    }
  }
}
