# Cadastros auxiliares client

Cliente Laravel para consumo dos endpoints do `cadastros-auxiliares`.

## Objetivo

Centralizar no backend dos sistemas locais:
- requisições HTTP para mensagens (opcional) e programas;
- comportamento fail-silent (sem quebrar interface).

## Endpoints disponíveis no serviço

O client consome, por padrão, estes endpoints a partir de `CADASTROS_AUXILIARES_URL`:

- `GET /api/mensagens`
- `GET /api/pos/programas`
- `GET /api/pos/programas/{codcur}`

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
CADASTROS_AUXILIARES_URL=https://cadastros-auxiliares.seu-dominio

# Opcional: ativa/desativa consumo de mensagens
CADASTROS_AUXILIARES_MENSAGENS_INTEGRACAO=true

# Obrigatória: a mesma senha configurada no cadastros-auxiliares
CADASTROS_AUXILIARES_PASSWORD=

CADASTROS_AUXILIARES_SISTEMA_NAME=ponto
CADASTROS_AUXILIARES_MENSAGENS_LIMITE=5
CADASTROS_AUXILIARES_MENSAGENS_TIMEOUT=0
CADASTROS_AUXILIARES_MENSAGENS_REFRESH=30
```

Observações:
- a integração de mensagens é opcional (`CADASTROS_AUXILIARES_MENSAGENS_INTEGRACAO`);
- o cliente de programas fica disponível por padrão após instalar a biblioteca (sem flag dedicada);
- para casos especiais, ainda é possível sobrescrever endpoint por config (`mensagens.endpoint_url` e `programas.endpoint_url`).

## Uso básico - mensagens

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

## Uso básico - programas

```php
use Uspdev\CadastrosAuxiliaresClient\Contracts\ProgramasClientInterface;

$programas = app(ProgramasClientInterface::class)->listar();
$programa = app(ProgramasClientInterface::class)->obter(1001);
```

Retornos:
- `listar()`: `Collection` de programas (`id`, `codcur`, `nomcur`, `codslg`);
- `obter($codcur)`: `array|null` com os dados do programa.

## Passos para implementar em sistemas locais

1. Adicionar dependência do client no sistema.
2. Configurar variáveis `CADASTROS_AUXILIARES_*` no `.env`.
3. Garantir que os endpoints estejam acessíveis pelo backend do sistema.
4. Se usar `laravel-usp-theme`, atualizar o tema para versão com suporte ao client.
5. Limpar cache/config (`php artisan optimize:clear`).
