<?php

namespace App\Contracts;

interface IdentityProvider
{
    public function verify(string $documentNumber, string $documentCode): bool;
}
