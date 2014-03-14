<?php

namespace MABI;

include_once __DIR__ . '/Model.php';
include_once __DIR__ . '/Inflector.php';
include_once __DIR__ . '/Utilities.php';

class IOSModelInterpreter {

  public static function getIOSDataModel() {
    $iosModel = new \SimpleXMLElement('<model/>');
    $iosModel->addAttribute('name', '');
    $iosModel->addAttribute('userDefinedModelVersionIdentifier', '');
    $iosModel->addAttribute('type', 'com.apple.IDECoreDataModeler.DataModel');
    $iosModel->addAttribute('documentVersion', '1.0');
    $iosModel->addAttribute('lastSavedToolsVersion', '2061');
    $iosModel->addAttribute('systemVersion', '12D78');
    $iosModel->addAttribute('minimumToolsVersion', 'Xcode 4.3');
    $iosModel->addAttribute('macOSVersion', 'Automatic');
    $iosModel->addAttribute('iOSVersion', 'Automatic');

    return $iosModel;
  }

  protected static function addIOSAttribute(\SimpleXMLElement &$iosEntity, $name, $mabiType, $multi = FALSE) {
    $setAttribute = TRUE;
    $type = 'String';
    switch ($mabiType) {
      case '':
      case 'string':
        break;
      case 'int':
        $type = 'Integer 32';
        break;
      case 'bool':
      case 'boolean':
        $type = 'Boolean';
        break;
      case 'float':
        $type = 'Float';
        break;
      case 'DateTime':
      case '\DateTime':
        $type = 'Date';
        break;
      case 'array':
        $type = 'Transformable';
        break;
      default:
        try {
          $rClass = new \ReflectionClass($mabiType);
          if ($rClass->isSubclassOf('\MABI\Model')) {
            $setAttribute = FALSE;
            $attribute = $iosEntity->addChild('relationship');
            $attribute->addAttribute('optional', 'YES');
            $attribute->addAttribute('syncable', 'YES');
            $attribute->addAttribute('deletionRule', 'Nullify');
            $attribute->addAttribute('destinationEntity', ReflectionHelper::stripClassName($mabiType));
            if ($multi) {
              $attribute->addAttribute('toMany', 'YES');
            }
            else {
              $attribute->addAttribute('minCount', '1');
              $attribute->addAttribute('maxCount', '1');
            }
            $attribute->addAttribute('name', $name);
            $attribute->addAttribute('attributeType', $type);
          }
          else {
            throw New \Exception('Class ' . $mabiType . ' does not derive from \MABI\Model');
          }
        } catch (\ReflectionException $ex) {
          throw New \Exception('Could not reflect class ' . $mabiType . "\n" . $ex->getMessage());
        }
    }
    if ($setAttribute) {
      $attribute = $iosEntity->addChild('attribute');
      $attribute->addAttribute('optional', 'YES');
      $attribute->addAttribute('syncable', 'YES');
      $attribute->addAttribute('name', $name);
      $attribute->addAttribute('attributeType', $type);
    }
  }

  public static function addModel(\SimpleXMLElement &$iosModel, \MABI\Model $mabiModel) {
    $entity = $iosModel->addChild('entity');
    $entity->addAttribute('name', ReflectionHelper::stripClassName(get_class($mabiModel)));
    $entity->addAttribute('syncable', 'YES');
    $entity->addAttribute('representedClassName', ReflectionHelper::stripClassName(get_class($mabiModel)));

    $attribute = $entity->addChild('attribute');
    $attribute->addAttribute('name', $mabiModel->getIdProperty());
    $attribute->addAttribute('optional', 'YES');
    $attribute->addAttribute('attributeType', 'String');
    $attribute->addAttribute('syncable', 'YES');

    $rClass = new \ReflectionClass($mabiModel);

    $rProperties = $rClass->getProperties(\ReflectionProperty::IS_PUBLIC);
    foreach ($rProperties as $rProperty) {
      /*
       * Ignores writing any model property with 'internal' or 'system' option
       */
      if (in_array('internal', ReflectionHelper::getDocDirective($rProperty->getDocComment(), 'field')) ||
        in_array('system', ReflectionHelper::getDocDirective($rProperty->getDocComment(), 'field'))
      ) {
        continue;
      }

      // Pulls out the type following the pattern @var <TYPE> from the doc comments of the property
      $varDocs = ReflectionHelper::getDocDirective($rProperty->getDocComment(), 'var');

      if (empty($varDocs)) {
        self::addIOSAttribute($entity, $rProperty->getName(), 'string');
      }
      else {
        $type = $varDocs[0];
        $matches = array();

        if (preg_match('/(.*)\[\]/', $type, $matches)) {
          // If the type follows the list of type pattern (<TYPE>[]), an array will be generated and filled
          // with that type
          $type = $matches[1];
          self::addIOSAttribute($entity, $rProperty->getName(), $type);
        }
        else {
          self::addIOSAttribute($entity, $rProperty->getName(), $type);
        }
      }
    }
  }
}