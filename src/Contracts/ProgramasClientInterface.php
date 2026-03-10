<?php

namespace Uspdev\CadastrosAuxiliaresClient\Contracts;

use Illuminate\Support\Collection;

interface ProgramasClientInterface
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function listar(): Collection;

    /**
     * @return array<string, mixed>|null
     */
    public function obter(int $codcur): ?array;
}
