# Facebook Posts Scheduler API

API backend desenvolvida para agendamento automático de postagens no Facebook. Projeto de portfólio para demonstração técnica.

## 🚀 Começando

Esta API permite gerenciar usuários, criar postagens, agendar publicações e integrar diretamente com o Facebook Graph API. O sistema conta com scheduler automático em background para publicar posts no horário definido.

Consulte **Instalação** para saber como executar o projeto.

### 📋 Pré-requisitos

-   PHP ^8.3
-   Laravel Framework ^12.0
-   Docker & Docker Compose
-   L5 Swagger ^9.0
-   MySQL ^8.0
-   Redis ^7.2
-   Composer ^2.8
-   Conta de desenvolvedor do Facebook

### 🔧 Instalação

Dentro da pasta do projeto backend, rode os comandos na ordem abaixo.

```bash
cp .env.example .env
```

```bash
docker compose up -d
```

Aguarde alguns segundos para o MySQL estar pronto, então acesse a aplicação:

**API:** [http://localhost:8080](http://localhost:8080)

### ⚙️ Configuração do Facebook

Configure as chaves do Facebook no arquivo `.env`:

```env
FACEBOOK_APP_ID=seu_app_id
FACEBOOK_APP_SECRET=seu_app_secret
FACEBOOK_GRAPH_VERSION=v23.0
FACEBOOK_PAGE_ID=sua_page_id
FACEBOOK_PAGE_ACCESS_TOKEN=seu_page_access_token
```

Para obter essas chaves, consulte o [guia completo no README principal](../README.md#%EF%B8%8F-configuração-do-facebook).

### 🔄 Scheduler Automático

O sistema inclui um **scheduler automático** que roda via Supervisor:

-   **Laravel Server**: `php artisan serve` na porta 80
-   **Laravel Schedule**: `php artisan schedule:work` para processar agendamentos

**Verificar status dos processos:**

```bash
docker compose exec backend supervisorctl status
```

**Ver logs do scheduler:**

```bash
docker compose exec backend tail -f /var/log/laravel-schedule.log
```

## 📝 Documentação da API com Swagger

A documentação completa da API está disponível em:

**Link:** [http://localhost:8080/api/documentation](http://localhost:8080/api/documentation)

### 🔐 Endpoints principais:

**Autenticação:**

-   `POST /api/register` - Registrar usuário
-   `POST /api/login` - Autenticar usuário
-   `POST /api/logout` - Logout
-   `GET /api/user` - Dados do usuário autenticado

**Posts:**

-   `GET /api/posts` - Listar posts
-   `POST /api/posts` - Criar post
-   `GET /api/posts/{id}` - Visualizar post específico
-   `DELETE /api/posts/{id}` - Deletar post
-   `POST /api/posts/{id}/retry` - Tentar reenviar post

**Logs:**

-   `GET /api/send-logs` - Listar logs de envio
-   `GET /api/send-logs/{post}` - Logs específicos de um post

## 🗄️ Banco de Dados

O sistema utiliza **MySQL 8.0** com as seguintes tabelas principais:

-   `users` - Usuários do sistema
-   `posts` - Postagens criadas
-   `scheduled_posts` - Agendamentos de publicação
-   `facebook_integrations` - Configurações do Facebook

## 🔧 Comandos Úteis

**Acessar container:**

```bash
docker compose exec backend bash
```

**Executar migrations:**

```bash
docker compose exec backend php artisan migrate
```

**Limpar cache:**

```bash
docker compose exec backend php artisan cache:clear
docker compose exec backend php artisan config:clear
```

**Tinker (console interativo):**

```bash
docker compose exec backend php artisan tinker
```

**Ver logs do Laravel:**

```bash
docker compose exec backend tail -f storage/logs/laravel.log
```

## 🛠️ Construído com

-   [Laravel Framework 12.0](https://laravel.com/) - Framework PHP
-   [Laravel Sanctum 4.0](https://laravel.com/docs/sanctum) - Autenticação API
-   [Laravel Facebook Graph 1.5](https://github.com/joelbutcher/laravel-facebook-graph) - Integração Facebook
-   [L5 Swagger 9.0](https://github.com/DarkaOnLine/L5-Swagger) - Documentação API
-   [Docker](https://www.docker.com/) - Containerização
-   [MySQL 8.0](https://www.mysql.com/) - Banco de dados
-   [Redis 7.2](https://redis.io/) - Cache e filas
-   [Supervisor](http://supervisord.org/) - Gerenciador de processos

## 📁 Estrutura do Projeto

```
backend/
├── app/
│   ├── Http/Controllers/     # Controllers da API
│   ├── Models/              # Models Eloquent
│   ├── Services/            # Services (FacebookService, etc.)
│   └── Console/Commands/    # Comandos artisan
├── config/                  # Configurações
├── database/
│   ├── migrations/          # Migrations do banco
│   └── seeders/            # Seeders
├── routes/
│   ├── api.php             # Rotas da API
│   └── web.php             # Rotas web
├── storage/                # Logs e arquivos
├── Dockerfile              # Container do Laravel
├── supervisord.conf        # Configuração do Supervisor
└── docker compose.yml     # Orquestração dos containers
```

## 🚀 Deploy

Para produção, considere:

-   Configurar tokens de longa duração do Facebook
-   Usar `APP_ENV=production` no `.env`
-   Configurar rate limiting adequado
-   Implementar monitoramento de logs
-   Configurar backup automático do banco

## ✒️ Autor

**Kevin Smith** - _Desenvolvimento_ - [LinkedIn](https://www.linkedin.com/in/kevin-smith-130a04154/)

## 📄 Licença

Este projeto está sob a licença MIT - veja o arquivo [LICENSE](../LICENSE) para detalhes.
