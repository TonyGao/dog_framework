<?php

namespace App\Exception;

use Exception;

class EntityException extends Exception
{
  public static function alreadyExistsProperty($entity, $property)
  {
    return new self(sprintf('The field %s already exists in the entity %s.', $property, $entity->getName()));
  }

  public static function noEntityTokenFound($token)
  {
    return new Self(sprintf('Unable to find "%s" entity token by the request', $token));
  }
}
