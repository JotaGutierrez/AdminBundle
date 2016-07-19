<?php

/*
 * This file is part of the Admin Bundle.
 *
 * Copyright (c) 2015-2016 LIN3S <info@lin3s.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LIN3S\AdminBundle\Action\Type;

use LIN3S\AdminBundle\Configuration\EntityConfiguration;

/**
 * Entity id trait.
 *
 * @author Beñat Espiña <benatespina@gmail.com>
 * @author Gorka Laucirica <gorka.lauzirika@gmail.com>
 */
trait EntityId
{
    protected function getEntityId($entity, EntityConfiguration $config)
    {
        if (method_exists($entity, $config->idField())) {
            return call_user_func([$entity, $config->idField()]);
        } elseif (method_exists($entity, 'get' . ucfirst($config->idField()))) {
            return call_user_func([$entity, 'get' . ucfirst($config->idField())]);
        } else {
            throw new \Exception(
                sprintf(
                    'You have configured "%s" as id field, not %s public property found nor %s() nor, get%s() methods found',
                    $config->idField(),
                    $config->idField(),
                    $config->idField(),
                    ucfirst($config->idField())
                )
            );
        }
    }
}
