<?php

namespace Civi;

/**
 * This probably should be a separate class
 */
trait TestDataLoaderTrait {

  /**
   * @var array
   *   References to entities used for loading test data
   */
  protected $references;

  /**
   * Creates entities from a JSON data set
   *
   * @param $path
   */
  protected function loadDataSet($path) {
    $dataSet = json_decode(file_get_contents($path), TRUE);
    foreach ($dataSet as $entityName => $entities) {
      foreach ($entities as $entityValues) {

        $entityValues = $this->replaceReferences($entityValues);

        $params = array('values' => $entityValues, 'checkPermissions' => FALSE);
        $result = civicrm_api4($entityName, 'create', $params);
        if (isset($entityValues['@ref'])) {
          $this->references[$entityValues['@ref']] = $result->getArrayCopy();
        }
      }
    }
  }

  /**
   * @param $name
   *
   * @return null|mixed
   */
  protected function getReference($name) {
    return isset($this->references[$name]) ? $this->references[$name] : NULL;
  }

  /**
   * @param array $entityValues
   *
   * @return array
   */
  private function replaceReferences($entityValues) {
    foreach ($entityValues as $name => $value) {
      if (is_array($value)) {
        $entityValues[$name] = $this->replaceReferences($value);
      } else if (substr($value, 0, 4) === '@ref') {
        $referenceName = substr($value, 5);
        list ($reference, $property) = explode('.', $referenceName);
        $entityValues[$name] =  $this->references[$reference][$property];
      }
    }

    return $entityValues;
  }
}