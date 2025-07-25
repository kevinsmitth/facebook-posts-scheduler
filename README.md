# Facebook Posts Scheduler

Projeto de portfólio para demonstração técnica de agendamento automático de postagens no Facebook.

## 🚀 Começando

Este projeto permite criar, agendar e publicar automaticamente postagens no Facebook através de uma interface web moderna. O sistema conta com autenticação de usuários, integração com Facebook Graph API e scheduler automático em background.

Consulte **Instalação** para saber como executar o projeto.

### 📋 Pré-requisitos

- Docker & Docker Compose
- PHP ^8.3
- Laravel Framework ^12.0
- Node.js ^20
- Composer ^2.8
- Conta de desenvolvedor do Facebook

### 🔧 Instalação

**1. Clone o repositório e acesse a pasta do projeto**

**2. Configure o ambiente Laravel**

```bash
cd backend
cp .env.example .env
```

**3. Configure o ambiente Vue.js**

```bash
cd frontend
cp .env.example .env
```

**4. Inicie os containers Docker**

```bash
docker compose up -d
```

**5. Aguarde a inicialização completa dos serviços**
O MySQL pode demorar alguns segundos para estar pronto.

**6. Acesse a aplicação**

- **Frontend (Vue.js)**: [http://localhost:5174](http://localhost:5174)
- **Backend (Laravel API)**: [http://localhost:8080](http://localhost:8080)
- **Documentação API (Swagger)**: [http://localhost:8080/api/documentation](http://localhost:8080/api/documentation)

### ⚙️ Configuração do Facebook

Para que o sistema funcione corretamente, você precisa configurar as chaves do Facebook no arquivo `.env` do Laravel.

#### 📝 Passo a passo para obter as chaves:

**1. Acesse o Facebook for Developers**

- Vá para [Facebook for Developers](https://developers.facebook.com/)
- Faça login com sua conta Facebook
- Clique em "Meus Apps" no menu superior

**2. Crie um novo App**

- Clique em "Criar App"
- Selecione "Outro" como tipo de uso
- Escolha "Empresa" como tipo de app
- Preencha o nome do app e email de contato
- Clique em "Criar App"

**3. Obtenha o App ID e App Secret**

- No painel do app, vá em "Configurações" → "Básico"
- Copie o **ID do App** (`FACEBOOK_APP_ID`)
- Clique em "Mostrar" ao lado de **Segredo do App** e copie (`FACEBOOK_APP_SECRET`)

**4. Configure o Graph API Version**

- A versão atual recomendada é `v23.0` (`FACEBOOK_GRAPH_VERSION`)
- Você pode verificar versões disponíveis em [Graph API Changelog](https://developers.facebook.com/docs/graph-api/changelog)

**5. Adicione produtos necessários**

- No painel lateral, clique em "Adicionar Produto"
- Adicione **"Login do Facebook"** e **"API Graph"**

**6. Obtenha o Page ID**

- Acesse sua página do Facebook
- Vá em "Configurações da Página" → "Informações da Página"
- Copie o **ID da Página** (`FACEBOOK_PAGE_ID`)

**7. Gere o Page Access Token**

- No painel do app, vá em "Ferramentas" → "Graph API Explorer"
- Em "Facebook App", selecione seu app criado
- Em "User or Page", selecione "Get Page Access Token"
- Escolha a página desejada e as permissões necessárias:
  - `pages_manage_posts`
  - `pages_read_engagement`
  - `pages_show_list`
- Clique em "Generate Access Token"
- Copie o token gerado (`FACEBOOK_PAGE_ACCESS_TOKEN`)

**8. Configure as URLs de redirecionamento**

- Vá em "Login do Facebook" → "Configurações"
- Em "URIs de redirecionamento válidos do OAuth", adicione:
  ```
  http://localhost:8080/auth/facebook/callback
  ```

**9. Configure no arquivo .env**

```env
FACEBOOK_APP_ID=seu_app_id_aqui
FACEBOOK_APP_SECRET=seu_app_secret_aqui
FACEBOOK_GRAPH_VERSION=v23.0
FACEBOOK_PAGE_ID=sua_page_id_aqui
FACEBOOK_PAGE_ACCESS_TOKEN=seu_page_access_token_aqui
FACEBOOK_REDIRECT_URI=http://localhost:8080/auth/facebook/callback
```

#### ⚠️ Observações importantes:

- **Page Access Token**: Possui data de expiração. Para produção, configure tokens de longa duração
- **Permissões**: Certifique-se de que sua página tem as permissões necessárias para publicar
- **App Review**: Para apps em produção, algumas permissões precisam de revisão do Facebook
- **Rate Limits**: O Facebook possui limites de API. Consulte a [documentação oficial](https://developers.facebook.com/docs/graph-api/overview/rate-limiting)

#### 🔧 Testando a configuração:

Após configurar as chaves, você pode testar se estão funcionando:

```bash
docker compose exec backend php artisan tinker
```

Dentro do tinker, execute:

```php
use App\Services\FacebookService;
$facebook = app(FacebookService::class);
$facebook->testConnection();
```

### 🔄 Scheduler Automático

O sistema conta com um **scheduler automático** que roda em background via Supervisor. Ele é responsável por:

- Processar postagens agendadas
- Publicar automaticamente no Facebook no horário definido
- Gerenciar fila de jobs
- Executar tarefas de manutenção

**O scheduler é iniciado automaticamente junto com o container e não requer configuração adicional.**

## 📚 Funcionalidades

- **Autenticação**: Sistema completo de login, registro e logout via Laravel Sanctum
- **Agendamento**: Interface para criar e agendar postagens com date-fns
- **Publicação Automática**: Posts são publicados automaticamente no horário definido
- **Integração Facebook**: Publicação direta via Laravel Facebook Graph
- **API Documentada**: Documentação completa via L5 Swagger
- **Interface Moderna**: Frontend responsivo em Vue.js 3 + TypeScript + Tailwind CSS
- **Gerenciamento de Estado**: Pinia para estado global da aplicação
- **Code Quality**: ESLint + Prettier para padronização de código

## 📝 Documentação da API com Swagger

Acesse a documentação completa da API em: [http://localhost:8080/api/documentation](http://localhost:8080/api/documentation)

A documentação inclui todos os endpoints disponíveis:

- Autenticação (login, register, logout)
- Gerenciamento de posts
- Agendamento de publicações
- Integração com Facebook

## 🗄️ Estrutura do Banco de Dados

O sistema utiliza **MySQL 8.0** com as seguintes principais tabelas:

- `users` - Usuários do sistema
- `posts` - Postagens criadas
- `scheduled_posts` - Agendamentos de publicação
- `facebook_integrations` - Configurações do Facebook

## 🐳 Serviços Docker

O projeto utiliza os seguintes serviços:

| Serviço  | Porta | Tecnologia                         | Descrição                         |
| -------- | ----- | ---------------------------------- | --------------------------------- |
| Backend  | 8080  | Laravel 12.0 + PHP 8.3             | API REST com Sanctum + Supervisor |
| Frontend | 5174  | Vue.js 3.5 + Vite 7.0 + TypeScript | SPA com Tailwind CSS              |
| MySQL    | 3306  | MySQL 8.0                          | Banco de dados principal          |
| Redis    | 6379  | Redis 7.2-alpine                   | Cache, sessões e filas            |

## 🔧 Comandos Úteis

**Ver logs do scheduler:**

```bash
docker compose exec backend tail -f /var/log/laravel-schedule.log
```

**Ver logs do servidor:**

```bash
docker compose exec backend tail -f /var/log/laravel-server.log
```

**Build do frontend para produção:**

```bash
docker compose exec frontend npm run build
```

**Verificar tipos TypeScript:**

```bash
docker compose exec frontend npm run type-check
```

**Code quality (linting e formatação):**

```bash
docker compose exec frontend npm run lint
docker compose exec frontend npm run format
```

**Acessar container do backend:**

```bash
docker compose exec backend bash
```

**Acessar container do frontend:**

```bash
docker compose exec frontend sh
```

**Verificar status do supervisor:**

```bash
docker compose exec backend supervisorctl status
```

**Rebuild containers:**

```bash
docker compose down
docker compose build --no-cache
docker compose up -d
```

**Parar todos os serviços:**

```bash
docker compose down
```

## 🛠️ Construído com

**Backend:**

- [Laravel Framework 12.0](https://laravel.com/) - Framework PHP
- [Laravel Sanctum 4.0](https://laravel.com/docs/sanctum) - Autenticação API
- [Laravel Facebook Graph 1.5](https://github.com/joelbutcher/laravel-facebook-graph) - Integração Facebook
- [L5 Swagger 9.0](https://github.com/DarkaOnLine/L5-Swagger) - Documentação API

**Frontend:**

- [Vue.js 3.5.18](https://vuejs.org/) - Framework JavaScript
- [Vite 7.0.0](https://vitejs.dev/) - Build tool e dev server
- [TypeScript 5.8](https://www.typescriptlang.org/) - Tipagem estática
- [Tailwind CSS 4.1.11](https://tailwindcss.com/) - Framework CSS
- [Pinia 3.0.3](https://pinia.vuejs.org/) - Gerenciamento de estado
- [Vue Router 4.5.1](https://router.vuejs.org/) - Roteamento
- [Axios 1.11.0](https://axios-http.com/) - Cliente HTTP

**Infraestrutura:**

- [Docker](https://www.docker.com/) - Containerização
- [MySQL 8.0](https://www.mysql.com/) - Banco de dados
- [Redis 7.2](https://redis.io/) - Cache e filas
- [Supervisor](http://supervisord.org/) - Gerenciador de processos

## ✒️ Autor

**Kevin Smith** - _Desenvolvimento_ - [LinkedIn](https://www.linkedin.com/in/kevin-smith-130a04154/)

## 📄 Licença

Este projeto está sob a licença MIT - veja o arquivo [LICENSE](LICENSE) para detalhes.

## 📞 Suporte

Para dúvidas ou problemas:

1. Verifique se todos os containers estão rodando: `docker compose ps`
2. Consulte os logs dos serviços
3. Verifique se as chaves do Facebook estão configuradas corretamente
