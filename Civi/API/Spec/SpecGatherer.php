<?php

namespace Civi\API\Spec;

use Civi\API\Spec\Provider\SpecProviderInterface;

class SpecGatherer {

  /**
   * @var SpecProviderInterface[]
   */
  protected $specProviders = array();

  /**
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

    return $specification;
  }

  /**
   * @param SpecProviderInterface $provider
   */
  public function addSpecProvider(SpecProviderInterface $provider) {
    $this->specProviders[] = $provider;
  }

  private function addDAOFields($entity, RequestSpec $specification) {
    $DAOFields = $this->getDAOFields($entity);

    foreach ($DAOFields as $DAOField) {
      $field = SpecFormatter::arrayToField($DAOField);
      $specification->addFieldSpec($field);
    }
  }

  /**
   * todo This shouldn't rely on api3 code.
   *
   * @param $entityName
   *
   * @return null|string
   */
  private function getDAOFields($entityName) {
    require_once 'api/v3/utils.php';
    /** @var \CRM_Core_DAO $bao */
    $dao = \_civicrm_api3_get_DAO($entityName);

    return $dao::fields();
  }
}
