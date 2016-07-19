<?php

/*
 * This file is part of the Admin Bundle.
 *
 * Copyright (c) 2015-2016 LIN3S <info@lin3s.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LIN3S\AdminBundle\Repository;

use Doctrine\ORM\EntityManager;
use LIN3S\AdminBundle\Configuration\EntityConfiguration;
use Symfony\Component\HttpFoundation\Request;

class DefaultQueryBuilder implements QueryBuilder
{
    /**
     * The entity manager.
     *
     * @var EntityManager
     */
    protected $manager;

    /**
     * DefaultQueryBuilder constructor.
     *
     * @param EntityManager $manager The entity manager
     */
    public function __construct(EntityManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(Request $request, EntityConfiguration $config)
    {
        $queryBuilder = $this->manager->getRepository($config->className())->createQueryBuilder('a');
        $metadata = $this->manager->getClassMetadata($config->className());
        $associations = $this->resolveAssociations($config, $metadata);

        $queryBuilder->groupBy('a.' . $metadata->identifier[0]);

        foreach ($associations as $association) {
            $queryBuilder->join('a.' . $association, 'join_' . $association);
        }

        if ($request->get('orderBy')) {
            $possibleAssociation = explode('.', $request->get('orderBy'))[0];

            $found = false;
            foreach ($metadata->associationMappings as $associationMapping) {
                if ($possibleAssociation === $associationMapping['fieldName']) {
                    $queryBuilder->addOrderBy(
                        'join_' . $associationMapping['fieldName'] . '.' . explode('.', $request->get('orderBy'))[1],
                        $request->get('order', 'ASC')
                    );
                    $found = true;
                    continue;
                }
            }
            if (!$found) {
                $queryBuilder->addOrderBy('a.' . $request->get('orderBy'), $request->get('order', 'ASC'));
            }
        }

        if ($request->get('filterBy') && $request->get('filter')) {
            $previousId = 97;
            $associations = explode('.', $request->get('filterBy'));

            for ($i = 0; $i < count($associations) - 1; ++$i) {
                if (false === $this->isTableRelation($metadata, $associations[$i] . '.' . $associations[$i + 1])) {
                    break;
                }
                $queryBuilder->innerJoin(chr($previousId) . '.' . $associations[$i], chr($previousId + 1));
                ++$previousId;

                foreach ($metadata->associationMappings as $associationMapping) {
                    if ($associationMapping['fieldName'] === $associations[$i]) {
                        $metadata = $this->manager->getClassMetadata($associationMapping['targetEntity']);
                    }
                }
            }
            $queryBuilder->where($queryBuilder->expr()->like(
                chr($previousId) . '.' . $associations[count($associations) - 1],
                "'%" . $request->get('filter') . "%'"
            ));
        }

        return $queryBuilder;
    }

    /**
     * Checks if the association is between two tables returning true,
     * otherwise, returns false if associations is based on inheritance.
     *
     * @param \Doctrine\ORM\Mapping\ClassMetadata $metadata    The class metadata
     * @param string                              $association The association field name
     *
     * @return bool
     */
    private function isTableRelation($metadata, $association)
    {
        foreach ($metadata->fieldMappings as $fieldMapping) {
            if ($fieldMapping['fieldName'] === $association) {
                return false;
            }
        }

        return true;
    }

    private function resolveAssociations(EntityConfiguration $config, $metadata)
    {
        $associations = [];
        foreach ($metadata->associationMappings as $associationMapping) {
            $fieldName = $associationMapping['fieldName'];
            $fieldClass = array_filter($config->listFields(), function ($field) use ($fieldName) {
                return $fieldName === explode('.', $field->options()['field'])[0];
            });

            if (count($fieldClass) > 0) {
                $associations[] = $fieldName;
            }
        }

        return $associations;
    }
}
