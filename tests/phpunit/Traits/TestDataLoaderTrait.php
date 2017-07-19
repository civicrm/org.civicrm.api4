<?php

namespace Civi\Test\Api4\Traits;

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
   * @param $name
   *   Can be the name of a data set inside the DataSets directory or the full
   *   path to any JSON data set file
   */
  protected function loadDataSet($name) {
    if (!file_exists($name)) {
      $name = __DIR__ . '/../DataSets/' . $name . '.json';
    }

    $dataSet = json_decode(file_get_contents($name), TRUE);

    if (NULL === $dataSet) {
      throw new \Exception(sprintf('Invalid JSON in %s', $name));
    }

    foreach ($dataSet as $entityName => $entities) {
      foreach ($entities as $entityValues) {
        $entityValues = $this->replaceReferences($entityValues);

        $result = civicrm_api4($entityName, 'create', $entityValues);

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

        if (!isset($this->references[$reference])) {
          throw new \Exception(sprintf('Undefined reference "%s"', $reference));
        }

        $entityValues[$name] =  $this->references[$reference][$property];
      }
    }

    return $entityValues;
  }
}
