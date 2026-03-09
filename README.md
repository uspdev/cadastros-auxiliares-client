# Cadastros auxiliares client

Cliente Laravel para consumo dos endpoints do `cadastros-auxiliares`.

## Objetivo

Centralizar no backend dos sistemas locais:
- requisicoes HTTP para mensagens;
- comportamento fail-silent (sem quebrar interface).

## Instalação

```bash
composer require uspdev/cadastros-auxiliares-client
```

Publicar config (opcional):

```bash
php artisan vendor:publish --tag=cadastros-auxiliares-client-config
```

## Configuração (`.env`)

```dotenv
CADASTROS_AUXILIARES_MENSAGENS_INTEGRACAO=true
CADASTROS_AUXILIARES_MENSAGENS_ENDPOINT_URL=https://cadastros-auxiliares.seu-dominio/api/mensagens
# Obrigatoria: a mesma senha configurada no cadastros-auxiliares
CADASTROS_AUXILIARES_PASSWORD=
CADASTROS_AUXILIARES_SISTEMA_NAME=ponto
CADASTROS_AUXILIARES_MENSAGENS_LIMITE=5
CADASTROS_AUXILIARES_MENSAGENS_TIMEOUT=0
CADASTROS_AUXILIARES_MENSAGENS_REFRESH=30
```

## Uso básico

```php
use Uspdev\CadastrosAuxiliaresClient\Contracts\MensagensClientInterface;

$mensagens = app(MensagensClientInterface::class)->fetch([
    'sistema' => 'ponto',
    'publico' => auth()->check() ? 'usuario' : true,
    'ativos' => true,
    'limite' => 5,
]);
```

Retorno: `Collection` de mensagens (arrays do payload JSON).

## Passos para implementar em sistemas locais

1. Adicionar dependência do client no sistema.
2. Configurar variáveis `CADASTROS_AUXILIARES_*` no `.env`.
3. Garantir que o endpoint esteja acessível pelo backend do sistema.
4. Se usar `laravel-usp-theme`, atualizar o tema para versão com suporte ao client.
5. Limpar cache/config (`php artisan optimize:clear`).
