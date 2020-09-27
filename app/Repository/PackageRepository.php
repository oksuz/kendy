<?php

namespace Application\Repository;

use Library\AbstractRepository;

class PackageRepository extends AbstractRepository
{
    public function findAll()
    {
        return $this->getConnection()->fetchAll("SELECT id, packagE_name FROM packages WHERE is_active = ?", [true]);
    }


}