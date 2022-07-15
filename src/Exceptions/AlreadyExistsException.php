<?php

namespace ZnDomain\Entity\Exceptions;

use Exception;

/**
 * Сущность уже существует в хранилище
 */
class AlreadyExistsException extends Exception
{

    /**
     * Сущность
     * @var object
     */
    private $entity;

    /**
     * Уникальные поля сущности, по которым оно найдено в хранилище
     * @var array 
     */
    private $fields = [];

    public function getEntity(): ?object
    {
        return $this->entity;
    }

    public function setEntity(object $entity): void
    {
        $this->entity = $entity;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function setFields($fields): void
    {
        $this->fields = $fields;
    }
}
