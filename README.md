# Guia do Projeto Laravel

Este README foi criado para ajudar você a entender e executar o projeto de Gestão de Viagens. Use os links abaixo para navegar pelas seções.

---

[Instalação](#instalação)
[Sobre o Projeto](#sobre-o-projeto)
[Arquitetura Utilizada](#arquitetura-utilizada)
[Testes](#testes)

---

## Instalação

### Requisitos

Para rodar este projeto, você precisa ter as seguintes ferramentas instaladas:

* **Docker:** Para gerenciar os containers do projeto.
    * [Link para download](https://www.docker.com/get-started/)
* **Composer:** Gerenciador de dependências do PHP.
    * [Link para download](https://getcomposer.org/download/)
* **Node.js:** Para gerenciar as dependências do frontend (Vite).
    * [Link para download](https://nodejs.org/en/download/)

### Passos de Instalação

Siga os comandos abaixo para configurar o projeto:

1.  **Clone o repositório:**
    ```bash
    git clone [https://github.com/UhCardoso/travel-manager-back.git](https://github.com/UhCardoso/travel-manager-back.git)
    ```

2.  **Acesse o diretório do projeto:**
    ```bash
    cd travel-manager-back
    ```

3.  **Instale as dependências do Composer (PHP):**
    ```bash
    composer install
    ```

4.  **Instale as dependências do NPM (Node.js):**
    ```bash
    npm install
    ```

5.  **Configure as variáveis de ambiente:**
    Para este projeto, um arquivo `.env` já foi fornecido com as variáveis preenchidas para facilitar a execução em ambiente de desenvolvimento. Basta copiar o arquivo de exemplo.
    ```bash
    cp .env.example .env
    ```

6.  **Inicie os containers com Sail:**
    ```bash
    ./vendor/bin/sail up -d
    ```

7.  **Execute as migrations e seeds:**
    ```bash
    ./vendor/bin/sail artisan migrate --seed
    ```

8.  **Verifique se o projeto está funcionando:**
    O projeto deve estar rodando em [http://localhost](http://localhost). Se tudo estiver configurado corretamente, você não terá erros.

---

## Sobre o Projeto

Este projeto é um portal de gerenciamento de viagens com dois tipos de acesso distintos: um para usuários comuns e outro para administradores.

* **Usuário Comum:** Pode registrar novas viagens, visualizar o histórico de solicitações e solicitar o cancelamento de uma viagem, desde que ela ainda não tenha sido aprovada.

* **Administrador:** Possui um portal exclusivo para gerenciar todos os pedidos de viagem. Ele pode alterar o status de uma solicitação para **aprovado** ou **cancelado**. Uma vez que a viagem é aprovada, seu status não pode mais ser alterado para cancelado.

Qualquer alteração no status de uma viagem envia uma notificação por e-mail para o usuário responsável pela solicitação.

---

## Arquitetura Utilizada

Este projeto foi desenvolvido utilizando a arquitetura **Repository Pattern + Service Layer** para garantir a aderência aos princípios do **SOLID** e da **Clean Architecture**.

O fluxo de dados segue a sequência **Controller → Service → Contract (Interface) → Repository → Model**, o que proporciona:

* **Separação de Responsabilidades (SRP):** Cada camada tem uma responsabilidade bem definida.
* **Inversão de Dependência (DIP):** O código de alto nível não depende de implementações de baixo nível.
* **Testabilidade, Manutenibilidade e Escalabilidade:** O design modular facilita a criação de testes, a manutenção do código e futuras expansões.

**Padrões de projeto utilizados:**

* **Repository Pattern:** Abstrai a camada de acesso a dados.
* **Service Layer:** Centraliza a lógica de negócio.
* **Dependency Injection:** Permite a inversão de controle.
* **Interface Segregation:** Utiliza contratos específicos.
* **Observer Pattern:** Usado para eventos, como o **TravelRequestObserver** que lida com notificações.

---

## Testes

Foram criados testes de **Feature** e **Unitários** para validar os fluxos da aplicação.

* **Rodar todos os testes:**
    ```bash
    ./vendor/bin/sail pest
    ```

* **Rodar um arquivo de teste específico:**
    ```bash
    ./vendor/bin/sail pest tests/Feature/UserTravelRequestTest.php
    ```

* **Rodar uma classe de teste específica:**
    ```bash
    ./vendor/bin/sail pest --filter=UserTravelRequestTest
    ```

### Outras Rotas Úteis

* **Documentação da API (Swagger):**
    [http://localhost/api/documentation](http://localhost/api/documentation)

* **Visualização de E-mails de Teste:**
    [http://localhost:8025](http://localhost:8025)