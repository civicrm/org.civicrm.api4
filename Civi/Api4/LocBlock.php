<?php
/**
 * Created by PhpStorm.
 * User: jazzman
 * Date: 3/22/18
 * Time: 5:04 PM.
 */

namespace Civi\Api4;


use Civi\API\Exception\NotImplementedException;
use Civi\Api4\Generic\AbstractEntity;

class LocBlock extends AbstractEntity
{
    public static $entity = 'Location';
    
    /**
     * @param $action
     * @param $ignore
     *
     * @return mixed
     * @throws \Civi\API\Exception\NotImplementedException
     */
    public static function __callStatic($action, $ignore)
    {
    
        $entity = 'LocBlock';
        
        // Find class for this action
        $entityAction = "\\Civi\\Api4\\Action\\$entity\\".ucfirst($action);
        $genericAction = '\Civi\Api4\Action\\'.ucfirst($action);
        if (class_exists($entityAction)) {
            return new $entityAction($entity);
        } elseif (class_exists($genericAction)) {
            return new $genericAction($entity);
        }
        throw new NotImplementedException("Api $entity $action version 4 does not exist.");
    }
    
}
