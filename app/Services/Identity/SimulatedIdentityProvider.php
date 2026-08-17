<?php

namespace App\Services\Identity;

use App\Contracts\IdentityProvider;

class SimulatedIdentityProvider implements IdentityProvider
{
    public function verify(string $documentNumber, string $documentCode): bool
    {
        return ! app()->environment('production')
            && config('identity.allow_test_identities')
            && $documentNumber !== ''
            && $documentCode !== '';
    }
}
