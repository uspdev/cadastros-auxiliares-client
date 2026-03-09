<?php

namespace Uspdev\CadastrosAuxiliaresClient\Contracts;

use Illuminate\Support\Collection;

interface MensagensClientInterface
{
    /**
     * @param  array{sistema?: string|null, publico?: bool|string|null, ativos?: bool|string|null, limite?: int|null}  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function fetch(array $filters = []): Collection;
}
