
# Laravel High Performance API with Swoole, PostgreSQL, and Docker

## Sobre o Projeto

Este projeto é um estudo de fixação para aplicações de alta performance utilizando Laravel, Docker, Redis e PostgreSQL. A API é multitenante, permitindo que empresas cadastrem seu CNPJ, gerenciem seus usuários e administrem seus produtos.

O foco principal é otimizar a performance mesmo em máquinas com recursos limitados, explorando soluções como:

-   **Swoole** para melhorar a performance do Laravel
-   **Redis** para caching eficiente e filas assíncronas
-   **PostgreSQL** como banco de dados robusto
-   **Docker** para ambiente de desenvolvimento isolado e reprodutível
-   **Nginx** para proxy e balanceamento de carga

Este projeto faz parte de um plano de estudos de **12 semanas**, e estamos atualmente na **Semana 1**. O desenvolvimento ainda está em andamento.

## Como Executar o Projeto

### 1. Clonar o Repositório

```sh
git clone https://github.com/pdrcunha/1week-Laravel-Swoole.git
cd **pasta_criada**
```

### 2. Configurar Variáveis de Ambiente

Vamos somente copiar o env.example para o .env

```ini
cp .env.example .env
```

### 3. Subir os Containers Docker

```sh
docker-compose up -d
```

Isso iniciará os containers para Laravel, PostgreSQL, Redis e Swoole.

### 4. Executar Migrations

```sh
docker-compose exec app php artisan migrate

```

### 5. Acessar a API

A API estará rodando em: [http://localhost](http://localhost/)

## Prática com Filas Assíncronas

O projeto também explora o uso de **filas assíncronas** com Redis. Como exemplo prático, há uma funcionalidade que **notifica todos os usuários quando um produto está com estoque baixo**, sem regras de negócio aplicadas, apenas para fins de aprendizado sobre filas e notificações assíncronas.

## Comandos Úteis

Rodar migrations:

```sh
docker-compose exec app php artisan migrate --seed
```

Acessar o container do Laravel:

```sh
docker-compose exec app bash
```

Monitorar logs:

```sh
docker-compose logs -f app
```

Parar os containers:

```sh
docker-compose down
```
