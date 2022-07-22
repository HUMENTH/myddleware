<?php
/*********************************************************************************
 * This file is part of Myddleware.

 * @package Myddleware
 * @copyright Copyright (C) 2013 - 2015  Stéphane Faure - CRMconsult EURL
 * @copyright Copyright (C) 2015 - 2016  Stéphane Faure - Myddleware ltd - contact@myddleware.com
 * @link http://www.myddleware.com

    This file is part of Myddleware.

    Myddleware is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Myddleware is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Myddleware.  If not, see <http://www.gnu.org/licenses/>.
*********************************************************************************/

namespace App\Repository;

use App\Entity\Rule;
use App\Entity\RuleRelationship;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

class RuleRelationshipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RuleRelationship::class);
    }

    /**
     * @return RuleRelationship[]
     */
    public function findDocumentChildsRules(Rule $rule)
    {
        $qb = $this->createQueryBuilder('rule_relationship');
        $qb
            ->select('rule_relationship')
            ->innerJoin('App\Entity\Rule', 'rule', Join::WITH, 'rule_relationship.fieldId = rule.id AND rule_relationship.parent = 1')
            ->leftJoin('rule.documents', 'document')
            ->andWhere('rule.deleted = 0')
            ->andWhere('document.deleted = 0')
            ->andWhere('document.rule = :rule')
            ->setParameter('rule', $rule)
            ->addOrderBy('document.sourceDateModified', 'ASC');

        return $qb->getQuery()->getResult();
    }
}