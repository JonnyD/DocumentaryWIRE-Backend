<?php

namespace App\Hydrator;

interface HydratorInterface
{
    public function toArray();

    public function toObject(array $data);
}