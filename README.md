# 👽 Aliens Store

Sistema de cadastro e gerenciamento de produtos alienígenas, desenvolvido com **Laravel 11**, **Vue 3**, **MySQL 8** e **Docker**.

---

## 📐 Arquitetura

```
aliens-store/
├── backend/                        # API Laravel 11
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/        # AuthController, ProductController, UserController
│   │   │   └── Requests/           # StoreProductRequest, UpdateProductRequest
│   │   ├── Models/                 # User, Product, ProductImage, ProductLog
│   │   └── Rules/                  # AllowedHtmlTags, SalePriceRule
│   ├── database/
│   │   ├── migrations/             # Tabelas: users, products, product_images, product_logs
│   │   └── seeders/                # Usuário padrão admin
│   ├── routes/
│   │   └── api.php                 # Rotas da API (12 rotas de produtos + 4 de usuários)
│   └── tests/Unit/                 # Testes unitários (13 assertions)
│
├── frontend/                       # Vue 3 + Vite + Tailwind CSS
│   └── src/
│       ├── views/                  # LoginView, ProductsView, UsersView
│       ├── components/product/     # ProductCard, ProductFormModal, ProductDetailModal
│       ├── stores/                 # Pinia: auth, products
│       ├── services/               # Axios (api.js) com interceptor de token
│       └── router/                 # Vue Router com rotas protegidas
│
└── docker/
    ├── php/                        # Dockerfile PHP 8.2-FPM
    ├── nginx/                      # Config Nginx
    └── node/                       # Dockerfile Node 20
```

### Stack

| Camada     | Tecnologia               |
|------------|--------------------------|
| Backend    | PHP 8.2 + Laravel 11     |
| Banco      | MySQL 8.0                |
| Auth       | Laravel Sanctum (tokens) |
| Frontend   | Vue 3 + Vite             |
| Estado     | Pinia                    |
| Estilo     | Tailwind CSS 3           |
| HTTP       | Axios                    |
| Container  | Docker + Docker Compose  |

### Banco de dados

```
users
  id, name, email, password, timestamps

products
  id, title, description, sale_price, cost_price,
  category, is_active, created_by (FK), updated_by (FK),
  deleted_at (soft delete), timestamps

product_images
  id, product_id (FK), path, original_name, timestamps

product_logs
  id, product_id (FK), user_id (FK),
  action, changes (JSON), logged_at
```

---

## 🚀 Manual de Instalação

### Pré-requisitos

- [Docker](https://docs.docker.com/get-docker/) (versão 20+)
- [Docker Compose](https://docs.docker.com/compose/install/) (v2+)
- Git

### Passo a passo

```bash
# 1. Clone o repositório
git clone https://github.com/alandsilva01/aliens-store.git
cd aliens-store

# 2. Suba os containers
docker compose up -d --build

# 3. Aguarde o MySQL iniciar (~15s) e instale o Laravel
docker compose exec -u root app bash -c "composer create-project laravel/laravel /tmp/fresh --prefer-dist --no-interaction && cp -rn /tmp/fresh/. /var/www/ && chown -R www-data:www-data /var/www"

# 4. Configure o banco
docker compose exec -u root app bash -c "sed -i 's/DB_CONNECTION=sqlite/DB_CONNECTION=mysql/' /var/www/.env"
docker compose exec -u root app bash -c "echo 'DB_HOST=db\nDB_PORT=3306\nDB_DATABASE=aliens_store\nDB_USERNAME=aliens_user\nDB_PASSWORD=aliens_pass' >> /var/www/.env"

# 5. Configure o bootstrap/app.php para rotas de API
docker compose exec -u root app bash -c "cat > /var/www/bootstrap/app.php << 'EOF'
<?php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(web: __DIR__.'/../routes/web.php', api: __DIR__.'/../routes/api.php', commands: __DIR__.'/../routes/console.php', health: '/up')
    ->withMiddleware(function (Middleware \$middleware): void { \$middleware->statefulApi(); })
    ->withExceptions(function (Exceptions \$exceptions): void {})->create();
EOF"

# 6. Instale dependências e rode migrations
docker compose exec -u root app bash -c "cd /var/www && composer require laravel/sanctum --no-interaction -q && composer dump-autoload -q && php artisan migrate:fresh --force && php artisan db:seed --force && php artisan storage:link && chmod -R 777 storage bootstrap/cache"
```

### Acessos após instalação

| Serviço  | URL                          |
|----------|------------------------------|
| Frontend | http://localhost:5173        |
| API      | http://localhost:8000/api    |
| MySQL    | localhost:3307               |

### Credenciais padrão

```
E-mail: admin@aliensstore.com
Senha:  Admin@123
```

---

## 👤 Manual do Usuário

### Login

1. Acesse **http://localhost:5173**
2. Insira o e-mail e senha padrão
3. Clique em **Iniciar Transmissão**

### Lista de Produtos

Após o login você verá a listagem com:
- **Busca** por título em tempo real
- **Filtro** por espécie/categoria
- **Filtro** por status (Ativos / Inativos)
- **Paginação** automática (12 produtos por página)

### Ver Detalhes do Produto

- Clique em qualquer card para abrir o modal de detalhes
- Visualize a galeria de imagens (clique para ampliar)
- Veja a descrição HTML renderizada
- Confira o histórico completo de alterações

### Cadastrar Produto

1. Clique no botão **+ Cadastrar**
2. Preencha os campos:
   - **Título** — nome do produto
   - **Espécie/Categoria** — filtra automaticamente ao digitar categorias existentes
   - **Custo** — valor de custo
   - **Preço de Venda** — mínimo 10% acima do custo (indicador em tempo real)
   - **Descrição** — aceita HTML restrito: `<p>`, `<br>`, `<b>`, `<strong>`
   - **Imagens** — arraste ou clique (JPG/PNG, máx. 5MB cada)
3. Clique em **Cadastrar Entidade**

### Editar Produto

1. Clique em **Editar** no card ou no modal de detalhes
2. Altere os campos desejados
3. Para remover imagens, passe o mouse sobre a imagem e clique no ícone de lixeira
4. Clique em **Salvar Alterações**

### Ativar / Inativar Produto

- Clique em **Inativar** ou **Ativar** no card do produto
- O status muda imediatamente

### Gestão de Usuários

1. Clique em **Usuários** no header
2. Visualize todos os membros cadastrados
3. Clique em **Novo Usuário** para cadastrar
4. Use **Editar** para alterar nome, e-mail ou senha
5. Use **Excluir** para remover (não é possível excluir sua própria conta)

### Validações

| Campo        | Regra                                             |
|--------------|---------------------------------------------------|
| Preço venda  | Mínimo: custo + 10%                               |
| Descrição    | Apenas tags `<p>`, `<br>`, `<b>`, `<strong>`      |
| Imagens      | Apenas JPG e PNG, máx. 5MB por arquivo            |

---

## 🧪 Testes Unitários

```bash
docker compose exec app bash -c "cd /var/www && php artisan test --testsuite=Unit"
```

Cobertura (13 assertions):
- `SalePriceRule` — valida margem mínima de 10% sobre o custo
- `AllowedHtmlTags` — aceita tags permitidas, rejeita `<script>`, `<img>`, `<div>`, `<iframe>`

---

## 🔐 Segurança

- Autenticação via **Laravel Sanctum** (Bearer Tokens)
- Todas as rotas protegidas por middleware `auth:sanctum`
- Senhas criptografadas com **bcrypt**
- Validação e sanitização de inputs no backend via Form Requests
- Proteção contra XSS via regra `AllowedHtmlTags`
- Interceptor Axios redireciona para `/login` em caso de token expirado (401)
- Usuário não pode excluir a própria conta

---

## 📋 Log de Auditoria

Todo evento de criação, edição, ativação, inativação e exclusão de produtos é registrado na tabela `product_logs` com:

- **Usuário** responsável pela ação
- **Ação** realizada (`created`, `updated`, `activated`, `inactivated`, `deleted`)
- **Campos alterados** (antes e depois, em JSON)
- **Data/hora** do evento

O histórico é visível no modal de detalhes de cada produto.

---

## 🛸 Diferenciais Implementados

- ✅ Log de criação/modificação de produtos
- ✅ CRUD completo de usuários
- ✅ Boas práticas de segurança (Sanctum, bcrypt, XSS protection)
- ✅ Documentação completa (arquitetura, instalação e manual do usuário)
- ✅ Testes unitários (13 assertions, 100% passando)
- ✅ Filtro por categoria dinâmico
- ✅ Galeria de imagens com lightbox
- ✅ Indicador de margem em tempo real
