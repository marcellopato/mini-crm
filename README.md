# Mini CRM - Sistema de Gerenciamento de Pedidos

Sistema desenvolvido com Laravel para controle de Pedidos, Produtos, Cupons e Estoque.

## Requisitos

- PHP 8.1+
- MySQL 5.7+
- Composer
- Node.js & NPM
- Laravel 10.x

## Instalação

### Usando Docker (Recomendado)

1. Clone o repositório
```bash
git clone [https://github.com/marcellopato/mini-crm]
cd mini-crm
```

2. Configure o ambiente
```bash
cp .env.example .env
composer install
```

3. Inicie os containers
```bash
./vendor/bin/sail up -d
```

4. Configure a aplicação
```bash
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan storage:link
```

5. Execute as migrations
```bash
./vendor/bin/sail artisan migrate
```

### Instalação Local (Sem Docker)

1. Clone o repositório
```bash
git clone [url-do-repositorio]
cd mini-crm
```

2. Configure o ambiente
```bash
cp .env.example .env
composer install
```

3. Configure seu .env com as credenciais do banco de dados

4. Você pode criar o banco de dados de duas formas:

   a. Usando migrations:
   ```bash
   php artisan migrate
   ```

   b. Usando o script SQL:
   ```bash
   mysql -u seu_usuario -p < database.sql
   ```

5. Configure a aplicação
```bash
php artisan key:generate
php artisan storage:link
```

## Testes do Webhook

Para testar atualizações de status via webhook:

```bash
# Atualizar status
curl -X POST http://localhost/api/webhooks/order-status \
     -H "Content-Type: application/json" \
     -d '{"order_id": 1, "status": "paid"}'

# Cancelar pedido
curl -X POST http://localhost/api/webhooks/order-status \
     -H "Content-Type: application/json" \
     -d '{
         "order_id": 1,
         "status": "cancelled",
         "reason": "Cancelado a pedido do cliente"
     }'
```

## Funcionalidades

- Gestão de produtos com variações
- Controle de estoque
- Cupons de desconto
- Cálculo automático de frete
- Validação de CEP via API
- Envio de e-mails de confirmação
- Webhook para atualizações de status

## Ambiente de E-mails

O sistema usa Mailpit para teste de e-mails em desenvolvimento. Acesse:
- http://localhost:8025

## Contato

Marcello Dantas Correia  
Email: marcello@dantascorreia.com.br
