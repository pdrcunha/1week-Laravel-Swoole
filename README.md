
# Laravel High-Performance API com Swoole, PostgreSQL e Docker

## Sobre o Projeto

Este projeto é um estudo de fixação para aplicações de alta performance utilizando **Laravel**, **Docker**, **Redis** e **PostgreSQL**. A API é **multitenante**, permitindo que empresas cadastrem seu **CNPJ**, gerenciem seus usuários e administrem seus produtos.

O foco principal é otimizar a performance, mesmo em máquinas com recursos limitados, explorando soluções como:

-   **Swoole** para melhorar a performance do Laravel
    
-   **Redis** para caching eficiente e filas assíncronas
    
-   **PostgreSQL** como banco de dados robusto
    
-   **Docker** para ambiente de desenvolvimento isolado e reprodutível
    
-   **Nginx** para proxy e balanceamento de carga
    

Estou em um período de estudos de **6 semanas**. Esta aplicação corresponde à **Semana 1**

----------

## Como Executar o Projeto

### 1. Clonar o Repositório

### 2. Configurar Variáveis de Ambiente

Copiar o arquivo de exemplo `.env.example` para `.env`:

### 3. Subir os Containers Docker

Isso iniciará os containers para **Laravel, PostgreSQL, Redis e Swoole**.

O comando abaixo inicia o consumidor da fila de "notificações":

### 4. Executar Migrations

### 5. Acessar a API

A API estará rodando em: [http://localhost](http://localhost/)

----------

## Práticas Utilizadas

-   Explorando o **Laravel 11**
    
-   Implementação de **cache** para performance
    
-   **Documentação** da API com **OpenAPI** e **L5 Swagger**
    
-   Testes de **performance** com **Swoole**
    

----------

## Comandos Úteis

Rodar migrations com seed:

Acessar o container do Laravel:

Monitorar logs:

Parar os containers:

----------

## Anotações e Aprendizado

-   A função **apiResource** no Laravel já monta as rotas CRUD automaticamente. Também é possível criar o controlador de recurso de forma automática. [Documentação](https://laravel.com/docs/12.x/controllers#resource-controllers).
    
-   Para documentar APIs, é quase uma regra utilizar o **OpenAPI (3.0)**. No PHP, o [L5 Swagger](https://github.com/DarkaOnLine/L5-Swagger) percorre os comentários nos controllers para gerar um `.yml` e disponibilizar a Swagger UI. No entanto, isso polui o código. Prefiro manter um arquivo `.yml` ou `.json` atualizado manualmente.
    
-   **Swoole e Xdebug não são compatíveis**. Existe uma ferramenta chamada **Yasd**, feita pela equipe do Swoole, mas ainda não testei.
    
-   Como o **Swoole é um script de execução contínua (long-lived)**, não posso simplesmente usar `exit` ou `print_r`. Em vez disso, posso usar `flush()` para forçar a saída para o cliente.
    
-   Aplicações de execução contínua geralmente não rodam um servidor HTTP e um consumidor de filas juntos no Swoole. Empresas costumam criar **comandos separados** para processar filas e usam **Supervisor** para manter o serviço ativo.
    
-   **Sobre timestamps no banco:** Sempre projetei bancos com `timestamps` em todas as tabelas, mas desta vez fiz um modelo híbrido. Agora, preciso validar os dados antes de deletá-los por causa de chaves estrangeiras. Isso é um bom aprendizado.
    
-   **Documentação nos models:** Algumas empresas documentam os **schemas** diretamente nos **models** para definir os retornos das APIs. Outras fazem isso nos **controllers**.
    
-   **Cache Simples e Eficaz:** Minha abordagem de cache segue a lógica:
    
    -   Cache para **getOne** e **getAll**.
        
    -   Invalidação do cache ao criar, deletar ou atualizar um registro.
        
    -   Simples, mas eficaz quando o número de leituras é **muito maior** que o de escritas.
        
-   **Estudo sobre as rotinas do Swoole:**
    
    -   Diferença entre `Process`, `go`, `run` e `channel` (este último acredito que não precisarei usar ativamente).
        
    -   Rodar tarefas assíncronas com Swoole funciona quase como um **event loop**, permitindo escalabilidade impressionante.
        
    -   **Gerenciamento de erros** dentro de rotinas assíncronas é complexo. Logs parecem ser a melhor abordagem para capturá-los.
        
    -   Depuração de rotinas assíncronas é **simples**, desde que não sejam milhares rodando ao mesmo tempo.
        

----------

## Observação

Infelizmente, não finalizei o projeto em **uma semana**. Trabalhei apenas algumas horas, pois minha disponibilidade varia entre **2 a 8 horas por semana** para estudos. Esta semana, em particular, foi muito corrida.