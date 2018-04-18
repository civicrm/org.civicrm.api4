<?php


namespace Civi\Api4\Action\LocBlock;
use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\Result;

/**
 * Created by PhpStorm.
 * User: jazzman
 * Date: 3/22/18
 * Time: 7:10 PM
 */

class Create extends AbstractAction
{
    
    /**
     * Field values to set.
     *
     * @var array
     */
    protected $values = [];

    
    /**
     * @param $key
     *
     * @return mixed|null
     */
    public function getValue($key)
    {
        return isset($this->values[$key]) ? $this->values[$key] : null;
    }
    
    /**
     * @param \Civi\Api4\Generic\Result $result
     *
     * @throws \API_Exception
     */
    public function _run(Result $result)
    {
        if (!empty($this->values['id'])) {
            throw new \API_Exception('Cannot pass id to Create action. Use Update action instead.');
        }

        $resultArray = $this->writeObject($this->values);

        // fixme should return a single row array???
        $result->exchangeArray($resultArray);
    }
    
    /**
     * Write a bao object as part of a create/update action.
     *
     * @param $params
     *
     * @return array
     *
     * @throws \API_Exception
     */
    protected function writeObject($params)
    {
        $entityId = \CRM_Utils_Array::value('id', $params);
        $params = $this->formatCustomParams($params, $this->getEntity(), $entityId);
    
        $bao = new \CRM_Core_DAO_LocBlock();

        $bao->copyValues($params);
    
        $createResult = $bao->save();
    
        if (!$createResult) {
            $errMessage = sprintf('%s write operation failed', $this->getEntity());
            throw new \API_Exception($errMessage);
        }
    
        // trim back the junk and just get the array:
        return static::baoToArray($createResult);
    }


    /**
     * @param $params
     * @param $entity
     * @param $entityId
     *
     * @return mixed
     */
    private function formatCustomParams($params, $entity, $entityId)
    {
        $params['custom'] = $customParams = [];

        // $customValueID is the ID of the custom value in the custom table for this
        // entity (i guess this assumes it's not a multi value entity)
        foreach ($params as $name => $value) {
            if (false === mb_strpos($name, '.')) {
                continue;
            }

            list($customGroup, $customField) = explode('.', $name);

            $customFieldId = \CRM_Core_BAO_CustomField::getFieldValue(
                \CRM_Core_DAO_CustomField::class,
                $customField,
                'id',
                'name'
            );
            $customFieldType = \CRM_Core_BAO_CustomField::getFieldValue(
                \CRM_Core_DAO_CustomField::class,
                $customField,
                'html_type',
                'name'
            );
            $customFieldExtends = \CRM_Core_BAO_CustomGroup::getFieldValue(
                \CRM_Core_DAO_CustomGroup::class,
                $customGroup,
                'extends',
                'name'
            );

            // todo are we sure we don't want to allow setting to NULL? need to test
            if ($customFieldId && null !== $value) {
                if ('CheckBox' === $customFieldType) {
                    // this function should be part of a class
                    formatCheckBoxField($value, 'custom_'.$customFieldId, $entity);
                }

                \CRM_Core_BAO_CustomField::formatCustomField(
                    $customFieldId,
                    $customParams,
                    $value,
                    $customFieldExtends,
                    null, // todo check when this is needed
                    $entityId,
                    false,
                    false,
                    true
                );
            }
        }

        return $params;
    }
    
    
    
    
}