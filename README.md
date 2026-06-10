# nave-crm-iss

Pacote Laravel privado para integração com o CRM da Nave. Ele é consumido pelos projetos clientes via Composer usando repositório VCS.

## Requisitos

- PHP 8.0 ou superior
- Laravel 8, 9, 10, 11 ou 12
- Acesso ao repositório privado no GitHub
- Token GitHub com permissão de leitura no repositório

## Acesso a Repositórios Privados

No projeto cliente, adicione o repositório VCS em `composer.json`:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/appnave/nave-crm-iss"
    }
  ]
}
```

Instale o pacote:

```bash
composer require appnave/nave-crm-iss
```

Autenticação local do Composer com token do GitHub:

```bash
composer config -g github-oauth.github.com <YOUR_TOKEN>
```

GitHub Actions:

```yaml
env:
  COMPOSER_AUTH: >-
    {"github-oauth":{"github.com":"${{ secrets.COMPOSER_GITHUB_TOKEN }}"}}
```

## Instalação Local

Publique a configuração do pacote:

```bash
php artisan vendor:publish --provider="Bildvitta\IssCrm\IssCrmServiceProvider" --tag="iss-crm-config"
```

Configure as variáveis de ambiente no projeto cliente:

```dotenv
MS_CRM_BASE_URI="https://crm-server.nave.dev.br"
MS_CRM_API_PREFIX="/api"
MS_HUB_FRONT_URI="https://crm.nave.dev.br"

MS_CRM_DB_URL=
MS_CRM_DB_HOST=
MS_CRM_DB_PORT=
MS_CRM_DB_DATABASE=
MS_CRM_DB_USERNAME=
MS_CRM_DB_PASSWORD=
```

Use `MS_CRM_DB_*` quando o projeto cliente precisar dos modelos e consultas que acessam o banco do CRM.

## Uso Básico

O pacote registra o singleton `crm` no container. Em geral, ele usa o Bearer token da requisição atual.

```php
use Bildvitta\IssCrm\IssCrm;

$crm = app('crm');
$customer = $crm->customers()->find($uuid);
$customers = $crm->customers()->search(['page' => 1]);
$showUrl = $crm->customers()->getShowUrl($uuid);
```

Se precisar passar o token manualmente:

```php
use Bildvitta\IssCrm\IssCrm;

$crm = new IssCrm($token);
```

Recursos programáticos disponíveis:

```php
$crm->programmatic()->customers()->searchByCompany($companyId)->search();
$crm->programmatic()->customers()->documents()->search($customerUuid);
$crm->programmatic()->customers()->facts()->create($customerUuid, $payload);
$crm->programmatic()->creditProcesses()->store($payload);
```

## Comandos Úteis

```bash
composer test
composer psalm
composer check-style
composer fix-style
```

## Informações Adicionais

- Nome do pacote: `appnave/nave-crm-iss`
- Namespace principal: `Bildvitta\IssCrm`
- Service provider: `Bildvitta\IssCrm\IssCrmServiceProvider`
- Tag de configuração: `iss-crm-config`
- Arquivo de configuração: `config/iss-crm.php`
- Changelog: `CHANGELOG.md`
- Contribuição: `.github/CONTRIBUTING.md`
- Segurança: `.github/SECURITY.md`
- Licença: MIT
