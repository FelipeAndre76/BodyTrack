# 🟢 BodyTrack

![Laravel](https://img.shields.io/badge/Laravel-13-red)
![PHP](https://img.shields.io/badge/PHP-8.5-blue)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5-purple)
![Status](https://img.shields.io/badge/Status-Em%20Desenvolvimento-green)
![Version](https://img.shields.io/badge/Version-v0.4.0-orange)

---

# 📌 Sobre o Projeto

O **BodyTrack** é uma plataforma web para acompanhamento de evolução corporal, saúde, nutrição e treinos.

Além das funcionalidades voltadas ao usuário, o projeto possui um painel administrativo completo para gerenciamento do sistema.

O projeto está sendo desenvolvido para:

* Aprimoramento em Laravel
* Desenvolvimento Full Stack
* Arquitetura de Sistemas Web
* Construção de portfólio profissional
* Evolução contínua de produto

---

# 🚀 Funcionalidades Implementadas

## 🔐 Autenticação

* Cadastro de usuários
* Login personalizado
* Logout
* Controle de sessão
* Proteção de rotas

---

## 📊 Dashboard do Usuário

* Peso atual
* Peso perdido
* Peso objetivo
* IMC
* Progresso corporal
* Gráficos interativos
* Cards motivacionais

---

## ⚖️ Controle Corporal

Cadastro de:

* Altura
* Peso inicial
* Peso atual
* Peso objetivo

Objetivos:

* Emagrecimento
* Ganho de massa
* Recomposição corporal

---

## 💧 Controle de Água

* Registro rápido
* Meta automática
* Histórico diário
* Atualização em tempo real
* Exclusão dinâmica
* SweetAlert2

---

## 🍽 Nutrição

* Cadastro de refeições
* Busca de alimentos
* Histórico alimentar

---

## 🏋️ Treinos

* Cadastro de treinos
* Histórico
* Organização por exercícios

---

# 👨‍💼 Painel Administrativo

## Dashboard Administrativo

* Total de usuários
* Total de administradores
* Total de exercícios
* Total de categorias
* Total de treinos
* Exercícios com foto
* Exercícios sem foto
* Barra de progresso de imagens
* Últimas atividades administrativas

---

## Exercícios

* Cadastro
* Edição
* Exclusão
* Busca instantânea

---

## Categorias

* Cadastro
* Edição
* Exclusão
* Exclusão em cascata dos exercícios

---

## Fotos dos Exercícios

* Upload individual
* Upload AJAX
* Remoção AJAX
* Sem recarregamento da página
* Visualização ampliada
* Barra de progresso dinâmica
* Filtro:

  * Todos
  * Com Foto
  * Sem Foto

---

## Usuários

* Listagem
* Busca instantânea
* Tornar administrador
* Remover administrador
* Bloquear usuário
* Desbloquear usuário
* Exclusão de usuário
* Estatísticas administrativas

---

## Segurança

* Middleware Admin
* Middleware Usuário Ativo
* Senha Mestre Administrativa
* Proteção contra autoexclusão
* Proteção contra autobloqueio
* SweetAlert de confirmação

---

## Logs Administrativos

Registro automático de:

* Alteração de administradores
* Bloqueio de usuários
* Exclusão de usuários

Recursos:

* Busca instantânea
* Histórico completo
* Exportação CSV

---

# 🎨 Interface

* Tema Dark
* Verde Lime
* Layout Responsivo
* Sidebar Premium
* Dashboard Moderno
* Cards Interativos
* SweetAlert2

---

# 🛠 Tecnologias Utilizadas

## Backend

* PHP 8.5
* Laravel 13
* Laravel Breeze
* Eloquent ORM
* SQLite

## Frontend

* Blade
* Bootstrap 5
* Bootstrap Icons
* JavaScript
* Fetch API
* ApexCharts
* SweetAlert2

---

# ⚙️ Instalação

```bash
git clone https://github.com/FelipeAndre76/BodyTrack.git

cd BodyTrack

composer install

cp .env.example .env

php artisan key:generate

php artisan migrate

php artisan storage:link

php artisan serve
```

---

# 📂 Estrutura Principal

```text
BodyTrack

├── Dashboard
├── Perfil Corporal
├── Pesagens
├── Água
├── Nutrição
├── Treinos

├── Admin
│   ├── Dashboard
│   ├── Exercícios
│   ├── Categorias
│   ├── Fotos
│   ├── Usuários
│   └── Logs

├── Login
├── Registro
└── Logout
```

---

# 🧠 Roadmap

## v0.5.0

* [ ] Logs completos de Exercícios
* [ ] Logs completos de Categorias
* [ ] Logs completos de Fotos
* [ ] Exportação PDF

## v0.6.0

* [ ] Upload em Massa de Fotos
* [ ] Dashboard Executivo
* [ ] Configurações Administrativas

## v0.7.0

* [ ] Exames Laboratoriais
* [ ] Controle de Medicamentos
* [ ] Evolução por Fotos

## v1.0.0

* [ ] BodyTrack AI
* [ ] Aplicativo Mobile
* [ ] Relatórios Avançados
* [ ] Inteligência Artificial para Saúde e Treinos

---

# 👨‍💻 Desenvolvedor

**Felipe André Sousa Brito**

Projeto desenvolvido para estudo, portfólio e aprofundamento em Laravel, Arquitetura Web, UX/UI e Desenvolvimento Full Stack.
