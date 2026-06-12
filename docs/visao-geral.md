# Agendamento SaaS — Visão Geral do Projeto

## O que é

Plataforma SaaS de agendamento multi-nicho. Cada cliente (tenant) recebe um painel administrativo, uma página pública de presença online e um formulário de agendamento público — tudo isolado por tenant.

O sistema se adapta ao nicho do negócio (consultório, salão, car wash, etc.), podendo variar linguagem, campos e fluxos de status conforme o tipo de empresa.

---

## Stack

| Camada | Tecnologia |
|---|---|
| Backend | Laravel 13, PHP 8.5.6 |
| Admin panel | Filament v4 |
| Componentes reativos | Livewire v3 |
| Motor de agenda | Laravel Zap (`laraveljutsu/zap`) encapsulado em `SchedulingService` |
| Banco de dados | MySQL (XAMPP local) |
| Pagamentos | Asaas (gateway brasileiro) |
| Frontend público | Tailwind CSS CDN v3 |
| Ambiente local | XAMPP — PHP em `C:\xampp\php85`, MySQL em `C:\xampp\mysql` |

---

## Como rodar localmente

**Pré-requisitos:** XAMPP rodando (Apache + MySQL), PHP 8.5 no PATH.

```bash
# 1. Instalar dependências
composer install

# 2. Configurar ambiente
cp .env.example .env
php artisan key:generate

# 3. Criar o banco e rodar migrations
# Criar DB "agendamento" no phpMyAdmin ou via MySQL CLI
php artisan migrate

# 4. Link de storage (para logos e banners)
php artisan storage:link

# 5. Iniciar servidor de desenvolvimento
php artisan serve
```

Acesso: `http://127.0.0.1:8000/admin`

---

## Estrutura de Pastas (principais)

```
app/
├── Filament/
│   ├── Pages/
│   │   ├── Calendario.php          # Página de calendário (Filament page)
│   │   └── EmpresaSettings.php     # Configurações da página pública
│   ├── Resources/                  # CRUD: Tenants, Clientes, Serviços,
│   │   ...                         # Profissionais, Agendamentos, Planos, etc.
│   └── Widgets/
│       ├── AppointmentsChartWidget.php
│       ├── StatsOverviewWidget.php
│       └── TodayAppointmentsWidget.php
├── Http/Controllers/
│   ├── BookingController.php       # Wizard público /agendar/{slug}
│   ├── CalendarioFrameController.php # API do calendário (events, status, reschedule)
│   └── TenantPageController.php    # Landing page pública /{slug}
├── Livewire/
│   └── Booking/BookingWizard.php   # Wizard de agendamento (multi-step)
├── Models/                         # Tenant, User, Customer, Service,
│   ...                             # Professional, Appointment, Plan, etc.
├── Providers/Filament/
│   └── AdminPanelProvider.php      # Configuração do painel Filament
├── Services/
│   ├── AsaasService.php            # Integração pagamentos
│   ├── SchedulingService.php       # Encapsula Laravel Zap
│   └── SubscriptionService.php
└── Support/
    └── Permission.php              # Helpers de role/permissão

resources/views/
├── filament/pages/
│   ├── calendario.blade.php        # Calendário visual (HTML + JS inline)
│   └── empresa-settings.blade.php
├── tenant/
│   └── landing.blade.php           # Landing page pública do tenant
└── livewire/booking/               # Views do BookingWizard

database/migrations/                # Todas as migrations do projeto
routes/
└── web.php                         # Todas as rotas HTTP
```

---

## Arquitetura Multi-Tenant

- Coluna `tenant_id` em todas as tabelas de negócio.
- Trait `BelongsToTenant` aplica global scope automático nas queries — cada usuário só enxerga dados do seu tenant.
- Acesso público (booking wizard, landing page) usa `Model::forTenant($id)` para bypass controlado do global scope.
- Sem banco separado por tenant no MVP.

---

## Roles e Permissões

| Role | Acesso |
|---|---|
| `super_admin` | Tudo — gerencia todos os tenants |
| `gestor` | Painel completo do seu tenant |
| `profissional` | Painel restrito — permissões configuráveis pelo gestor |
| `cliente` | Sem acesso ao painel Filament |

Permissões granulares opcionais para profissionais: `criar_agendamento`, `editar_agendamento`, `cancelar_agendamento`, `ver_agenda_geral`, `ver_todos_clientes`, `cadastrar_servicos`, `ver_relatorios`.

---

## Rotas Principais

| Método | URL | Descrição |
|---|---|---|
| `GET` | `/admin` | Painel Filament (requer login) |
| `GET` | `/admin/calendario` | Calendário de agendamentos |
| `GET` | `/agendar/{slug}` | Wizard de agendamento público |
| `GET` | `/{slug}` | Landing page pública do tenant |
| `GET` | `/interno/calendario/events` | API — lista eventos do calendário |
| `GET` | `/interno/calendario/options` | API — opções para novo agendamento |
| `POST` | `/interno/calendario/store` | API — criar agendamento pelo calendário |
| `POST` | `/interno/calendario/status` | API — alterar status do agendamento |
| `POST` | `/interno/calendario/reschedule` | API — reagendar (drag-and-drop) |
| `POST` | `/interno/calendario/quick-customer` | API — criar cliente rápido |
| `POST` | `/asaas/webhook` | Webhook de pagamentos Asaas |

---

## Features Implementadas

### Painel Administrativo
- CRUD completo: Tenants, Clientes, Serviços, Profissionais, Agendamentos, Usuários, Planos
- Dashboard com widgets: resumo do dia, estatísticas, gráfico de 7 dias
- Largura total em todas as páginas, suporte a dark mode

### Calendário (`/admin/calendario`)
- Grade visual de horários (06:00–23:30, slots de 30 min)
- Drag-and-drop para reagendar com auto-scroll
- Drawer lateral para alterar status, editar ou criar agendamentos
- Filtros por data, profissional e status

### Agendamento Online
- Wizard multi-step em Livewire: escolha de serviço → profissional → data/hora → dados do cliente → confirmação
- E-mail de confirmação automático ao cliente

### Página Pública por Tenant (`/{slug}`)
- Gerada automaticamente a partir dos dados do cadastro
- Seções: navbar, hero com banner, sobre, serviços, equipe, formulário de agendamento embutido, botão WhatsApp flutuante, footer
- Configurável pelo gestor em **Configurações → Minha Empresa**

### Monetização (Asaas)
- Planos com limites (usuários, profissionais, agendamentos)
- Cobranças e assinaturas via Asaas
- Webhook para atualização automática de status de pagamento
- Command `CheckOverdueSubscriptions` para verificar assinaturas vencidas

---

## Responsividade (refatoração 2026-06-04)

UI refatorada para ser 100% responsiva em 360px, 768px, 1024px e 1920px. Nenhuma funcionalidade removida.

| Área | O que mudou |
|---|---|
| Calendário (`/admin/calendario`) | CSS responsivo: drawer full-width em mobile, grid com scroll horizontal em tablet, topbar com wrap, modal com stacking de colunas em mobile |
| Booking Wizard | Grid de horários: `grid-cols-2` no mobile (era `grid-cols-3`, gerava overflow em 360px) |
| Landing page (`/{slug}`) | Navbar com hamburguer em mobile, hero com padding adaptativo, equipe com scroll horizontal em mobile, botão WhatsApp com `safe-area-inset-bottom` para iPhone |
| Formulários Filament | Todas as seções com `->columns(2)` e `->columns(3)` passaram a usar array responsivo: `['default' => 1, 'sm' => 2]` / `['default' => 1, 'sm' => 2, 'lg' => 3]` |
| Tabelas Filament | Colunas secundárias marcadas como `->toggleable()`: service/professional em AppointmentsTable, appointments_count em CustomersTable, limites em PlansTable |

---

## Roadmap

| Fase | Descrição | Status |
|---|---|---|
| 1 | Fundação — auth, tenants, roles, planos, Filament | ✅ Completo |
| 2 | Motor de agenda — serviços, disponibilidade, agendamentos | ✅ Completo |
| 3 | Templates por nicho — labels dinâmicos, status customizados | 🔄 Parcial |
| 4 | Página pública + landing page por tenant | ✅ Completo |
| 5 | Monetização freemium (Asaas) | ✅ Implementado |
| 6 | Automações (e-mail, WhatsApp) | ⏳ Pendente |
| 7 | Beta comercial | ⏳ Pendente |

---

## Decisões Técnicas Relevantes

- **SchedulingService encapsula Laravel Zap** — desacopla o código do pacote externo, facilita troca futura.
- **Calendário sem iframe** — renderizado diretamente na Filament page via `@push('styles')` / `@push('scripts')` para evitar conflito com o parser do Livewire.
- **`TenantSetting.settings` (JSON)** — campo único flexível para configurações da landing page; sem migrations adicionais.
- **Rota `/{slug}` definida por último** em `routes/web.php` para não conflitar com `/admin`, `/agendar`, etc.
