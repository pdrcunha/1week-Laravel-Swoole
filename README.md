
# Laravel High Performance API with Swoole, PostgreSQL, and Docker

## Sobre o Projeto

Este projeto é um estudo de fixação para aplicações de alta performance utilizando Laravel, Docker, Redis e PostgreSQL. A API é multitenante, permitindo que empresas cadastrem seu CNPJ, gerenciem seus usuários e administrem seus produtos.

O foco principal é otimizar a performance mesmo em máquinas com recursos limitados, explorando soluções como:

-   **Swoole** para melhorar a performance do Laravel
    
-   **Redis** para caching eficiente e filas assíncronas
    
-   **PostgreSQL** como banco de dados robusto
    
-   **Docker** para ambiente de desenvolvimento isolado e reprodutível
    
-   **Nginx** para proxy e balanceamento de carga
    

Estou em um período de estudos de **6 semanas**. Esta aplicação corresponde à **Semana 1**. O desenvolvimento ainda está em andamento.

## Como Executar o Projeto

### 1. Clonar o Repositório

```
git clone https://github.com/pdrcunha/1week-Laravel-Swoole.git
cd pasta_criada
```

### 2. Configurar Variáveis de Ambiente

Copiar o arquivo de exemplo `.env.example` para `.env`:

```
cp .env.example .env
```

### 3. Subir os Containers Docker

```
docker-compose up -d
```

Isso iniciará os containers para Laravel, PostgreSQL, Redis e Swoole.

### 4. Executar Migrations

```
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
```

### 5. Acessar a API

A API estará rodando em: [http://localhost](http://localhost/)

## Práticas

-   Explorando o Laravel 11
    
-   Cache para performance
    
-   Documentação com OpenAPI e L5 Swagger
    
-   Teste de performance com Swoole
    

## Comandos Úteis

Rodar migrations:

```
docker-compose exec app php artisan migrate --seed
```

Acessar o container do Laravel:

```
docker-compose exec app bash
```

Monitorar logs:

```
docker-compose logs -f app
```

Parar os containers:

```
docker-compose down
```

## Anotações e Aprendizado (Pessoal)

-   A função **apiResource** no Laravel já monta as rotas CRUD automaticamente, e também é possível criar o controlador de recurso de forma automática. [Documentação](https://laravel.com/docs/12.x/controllers#resource-controllers).
    
-   Para documentar sua API, é quase uma regra utilizar o padrão **OpenAPI (3.0)**. No PHP, temos o [L5 Swagger](https://github.com/DarkaOnLine/L5-Swagger), que percorre comentários nos controllers para gerar um `.yml` e disponibilizar o Swagger UI. No entanto, o código fica poluído com esses comentários, e é necessário aprender mais uma forma de escrita. Em minha visão, é melhor manter o arquivo `.yml` ou `.json` atualizado manualmente.
    
-   Gastei um bom tempo tentando configurar o Xdebug para depuração, mas parece que o Swoole e o Xdebug não são compatíveis. Encontrei uma ferramenta chamada Yasd, criada pela mesma equipe do Swoole, mas não estou disposto a testá-la no momento.
    
-   Como o Swoole é um script de execução contínua (long-lived), não posso simplesmente dar `exit` ou `print_r`, pois isso afetaria a execução no terminal. Em vez disso, posso usar a função `flush()` para forçar a saída para o cliente.
    
-   Mesmo sendo uma aplicação de execução contínua, percebi que não é comum rodar um servidor HTTP e um consumidor de fila RabbitMQ juntos no Swoole. Após uma pesquisa rápida, vi que os projetos normalmente possuem um comando separado para processar as filas, e algumas empresas usam até o Supervisor para manter esse serviço ativo.
    
-   Eu SEMPRE projeto meus bancos usando `timestamps` em TODAS as tabelas, no estilo de "dado paranoico", mas desta vez decidi fazer um modelo híbrido. Agora, preciso validar dados antes de deletá-los devido a chaves estrangeiras. Isso é uma boa prática para aprendizado.
    
-   Ao pesquisar um pouco mais, vi que é comum empresas grandes criarem os schemas nos modelos para documentar os retornos. Algumas até fazem isso nos controllers.

- Pratica de cachear os getOne e getAll e deletar tudo em caso de um novo post, delete ou put, e uma pratica extremamente simples de cache mas bem eficas. Mais simples ainde seria so simplismente usar ttl de 30segundo ou um minuto mas isso só funciona para apis de grande numero de leitura onde ter o dado em tempo "habil" pouco importa. O melhor dos mundos mesmo poderia ser a duplicação botando os posts no cache e os updates tambem mas isso consome um tempo infernal. A abordagem que usei só é realmente eficaz se o numero de leituras e muito maior que o numero de escritas pos um taio limit.
    

## Observação

-   Infelizmente, não finalizei o projeto em uma semana. Levei apenas algumas horas, mas minhas semanas às vezes têm apenas 2 a 8 horas disponíveis para estudo, e essa semans tem sido corridas