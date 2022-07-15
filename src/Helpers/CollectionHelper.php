<?php

namespace ZnDomain\Entity\Helpers;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use ZnCore\Code\Helpers\PropertyHelper;
use ZnCore\Collection\Interfaces\Enumerable;
use ZnCore\Collection\Libs\Collection;
use ZnDomain\Entity\Factories\PropertyAccess;
use ZnDomain\Query\Entities\Query;
use ZnDomain\Query\Entities\Where;

class CollectionHelper
{

    public static function filterByQuery(Enumerable $collection, Query $query): Enumerable
    {
        $criteria = self::query2criteria($query);
        return $collection->matching($criteria);
    }

    public static function query2criteria(Query $query): Criteria
    {
        $criteria = new Criteria();
        if ($query->getWhere()) {
            foreach ($query->getWhere() as $where) {
                $expr = new Comparison($where->column, $where->operator, $where->value);
                $criteria->andWhere($expr);
            }
        }
        return $criteria;
    }

    /**
     * @param Enumerable $collection
     * @param array | Where[] $whereArray
     * @return mixed
     */
    public static function whereArr(Enumerable $collection, array $whereArray)
    {
        $criteria = new Criteria();
        foreach ($whereArray as $where) {
            $expr = new Comparison($where->column, $where->operator, $where->value);
            $criteria->andWhere($expr);
        }
        return $collection->matching($criteria);
    }

    public static function where(Enumerable $collection, $field, $operator, $value)
    {
        $expr = new Comparison($field, $operator, $value);
        $criteria = new Criteria();
        $criteria->andWhere($expr);
        return $collection->matching($criteria);
    }

    public static function merge(Enumerable $collection, Enumerable $source): Enumerable
    {
        $result = clone $collection;
        self::appendCollection($result, $source);
        return $result;
    }

    public static function appendCollection(Enumerable $collection, Enumerable $source): void
    {
        foreach ($source as $item) {
            $collection->add($item);
        }
    }

    public static function chunk(Enumerable $collection, $size)
    {
        if ($size <= 0) {
            return new Collection();
        }
        $chunks = [];
        foreach (array_chunk($collection->toArray(), $size, true) as $chunk) {
            $chunks[] = new Collection($chunk);
        }
        return new Collection($chunks);
    }


    public static function indexing(Enumerable $collection, string $fieldName): array
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $array = [];
        foreach ($collection as $item) {
            $pkValue = $propertyAccessor->getValue($item, $fieldName);
            $array[$pkValue] = $item;
        }
        return $array;
    }

    public static function create(string $entityClass, array $data = [], array $filedsOnly = []): Enumerable
    {
        foreach ($data as $key => $item) {
            $entity = new $entityClass;
            PropertyHelper::setAttributes($entity, $item, $filedsOnly);
            $data[$key] = $entity;
        }
        $collection = new Collection($data);
        return $collection;
    }

    public static function toArray(Enumerable $collection): array
    {
        $serializer = new Serializer([new ObjectNormalizer()]);
        $normalizeHandler = function ($value) use ($serializer) {
            return $serializer->normalize($value);
            //return is_object($value) ? EntityHelper::toArray($value) : $value;
        };
        $normalizeCollection = $collection->map($normalizeHandler);
        return $normalizeCollection->toArray();
    }

    public static function getColumn(Enumerable $collection, string $key): array
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $array = [];
        foreach ($collection as $entity) {
            $array[] = $propertyAccessor->getValue($entity, $key);
        }
        $array = array_values($array);
        return $array;
    }
}
