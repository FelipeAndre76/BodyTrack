# BodyTrack

![Laravel](https://img.shields.io/badge/Laravel-13-red)
![PHP](https://img.shields.io/badge/PHP-8.5-blue)
![Livewire](https://img.shields.io/badge/Livewire-3-purple)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5-purple)
![Status](https://img.shields.io/badge/Status-Em%20Desenvolvimento-green)
![Version](https://img.shields.io/badge/Version-v4.0.0-orange)

---

# Sobre o Projeto

O BodyTrack é uma plataforma web completa para acompanhamento de evolução corporal, saúde, nutrição, treinos e comunidade fitness.

O projeto nasceu como estudo em Laravel e evoluiu para uma aplicação full stack com dashboard premium, painel administrativo, notificações em tempo real, leitura de tabela nutricional por OCR, controle corporal, metas inteligentes, treinos, registros de água, check-ins semanais e feed social no estilo Instagram.

O projeto está sendo desenvolvido para:

* Aprimoramento em Laravel
* Desenvolvimento Full Stack
* Arquitetura de Sistemas Web
* Construção de portfólio profissional
* Evolução contínua de produto
* Experiência real de produto SaaS

---

# Funcionalidades Implementadas

## Autenticação

* Cadastro de usuários
* Login personalizado
* Logout
* Controle de sessão
* Proteção de rotas
* Middleware de usuário ativo
* Middleware administrativo

---

## Dashboard do Usuário

* Peso atual
* Peso perdido
* Peso objetivo
* IMC
* Progresso corporal
* Cards motivacionais
* Indicadores rápidos no topo
* Layout premium responsivo
* Navegação por sidebar organizada em categorias

---

## Perfil Corporal

Cadastro e acompanhamento de:

* Altura
* Peso inicial
* Peso atual
* Peso objetivo
* Idade
* Sexo
* Nível de atividade
* Objetivo corporal
* Meta calórica
* Estratégia de déficit calórico

Objetivos disponíveis:

* Emagrecimento
* Ganho de massa
* Recomposição corporal
* Manutenção

---

## Métricas Corporais Inteligentes

* Cálculo de IMC
* Classificação corporal
* Estimativa de gasto calórico diário
* Cálculo de meta de calorias
* Proteína diária recomendada
* Carboidratos recomendados
* Gorduras recomendadas
* Déficit calórico configurável
* Metas anatômicas e nutricionais

---

## Pesagens

* Registro de peso
* Histórico de pesagens
* Evolução visual
* Comparativo com objetivo
* Indicadores de progresso

---

## Água

* Registro rápido de consumo
* Meta automática
* Histórico diário
* Atualização dinâmica
* Exclusão sem reload
* Visual premium em cards
* Feedback com SweetAlert2

---

## Nutrição

* Cadastro de refeições
* Histórico alimentar
* Busca de alimentos
* Cadastro de alimentos próprios
* Meus alimentos
* Registro de macros
* Calorias consumidas
* Proteína consumida
* Carboidratos consumidos
* Gorduras consumidas
* Plano alimentar diário
* Dashboard nutricional

---

## OCR de Tabela Nutricional

* Upload de foto da tabela nutricional
* Leitura com Tesseract OCR
* Extração automática de dados
* Cadastro automático de alimento
* Modal de confirmação antes de salvar
* Registro de logs do OCR
* Histórico de leituras realizadas
* Foto nutricional salva no banco de dados
* Compressão automática da imagem para reduzir peso no servidor

Exemplo de leitura:

* Nome do alimento
* Porção
* Calorias
* Proteína
* Carboidratos
* Gorduras

---

## Meus Alimentos

* Listagem dos alimentos cadastrados pelo usuário
* Cards premium com informações nutricionais
* Foto da tabela nutricional
* Edição de alimento
* Exclusão de alimento
* Notificação ao cadastrar
* Notificação ao excluir

---

## Treinos

* Cadastro de treinos
* Histórico de treinos
* Organização por exercícios
* Seleção dinâmica de exercícios sem reload
* Plano semanal de treino
* Rodízio configurável pelo usuário
* Dias de descanso
* Treinos personalizados como Push, Pull, Legs ou qualquer nome escolhido

---

## Evolução

* Fotos de evolução
* Histórico visual
* Check-in semanal
* Comparativo corporal
* Relatórios de progresso
* Galeria de evolução
* Upload comprimido para economizar armazenamento

---

## Metas Inteligentes

* Metas corporais
* Metas nutricionais
* Metas de água
* Metas de treino
* Acompanhamento de progresso
* Indicadores automáticos

---

## Resumo Semanal

* Visão consolidada da semana
* Peso
* Água
* Nutrição
* Treinos
* Check-ins
* Evolução geral

---

# Comunidade BodyTrack

## Feed Social

* Feed no estilo Instagram
* Posts com foto
* Descrição do post
* Categorias como treino, receita, evolução e dica
* Layout compacto
* Visualização em modal
* Curtidas
* Comentários
* Respostas em comentários
* Curtidas em comentários
* Compartilhamento
* Salvar post
* Excluir post próprio

---

## Status

* Status no estilo stories
* Upload de foto vertical
* Visualização em tela modal
* Reações no status
* Exclusão do próprio status
* Imagens otimizadas para formato 1080x1920

---

## Perfil Social

* Perfil público do usuário
* Foto de perfil
* Bio
* Posts do usuário
* Posts salvos
* Seguidores
* Seguindo
* Sugestões de usuários
* Seguir e deixar de seguir

---

## Notificações em Tempo Real

* Sino de notificações
* Atualização automática sem reload
* Notificações de ações importantes
* Notificações de alimentos cadastrados
* Notificações de alimentos excluídos
* Notificações sociais
* Página completa de notificações
* Marcar como lidas
* Limpar notificações
* Toast visual
* Correção automática de acentuação em português

---

# Painel Administrativo

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
* Visual premium

---

## Exercícios

* Cadastro
* Edição
* Exclusão
* Busca instantânea
* Organização por categoria
* Visual moderno

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
* Filtros por status

Filtros:

* Todos
* Com foto
* Sem foto

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

## Logs Administrativos

Registro automático de:

* Alteração de administradores
* Bloqueio de usuários
* Exclusão de usuários
* Ações administrativas importantes

Recursos:

* Busca instantânea
* Histórico completo
* Exportação CSV
* Exportação PDF
* Relatórios administrativos

---

# Segurança

* Middleware Admin
* Middleware Usuário Ativo
* Senha mestre administrativa
* Proteção contra autoexclusão
* Proteção contra autobloqueio
* SweetAlert2 para confirmações
* Validações no backend
* Proteção de rotas autenticadas
* Controle de permissões por usuário

---

# Interface

* Tema dark premium
* Verde lime como cor principal
* Layout responsivo
* Sidebar organizada por categorias
* Topbar com indicadores rápidos
* Cards modernos
* Modais premium
* SweetAlert2
* Livewire para interações dinâmicas
* Componentes visuais reutilizáveis
* Experiência inspirada em apps modernos

---

# Otimização de Imagens

O BodyTrack possui compressão automática de imagens no navegador antes do envio ao servidor.

Presets implementados:

* Post social
* Status
* Foto de perfil
* Foto de tabela nutricional
* Foto de check-in

Benefícios:

* Menos peso no banco de dados
* Upload mais rápido
* Menor consumo de servidor
* Melhor experiência no navegador

---

# Tecnologias Utilizadas

## Backend

* PHP 8.5
* Laravel 13
* Laravel Breeze
* Laravel Livewire
* Eloquent ORM
* SQLite
* Tesseract OCR

## Frontend

* Blade
* Livewire
* Bootstrap 5
* Bootstrap Icons
* JavaScript
* Fetch API
* ApexCharts
* SweetAlert2
* CSS modularizado

---

# Instalação

```bash
git clone git@github.com:FelipeAndre76/BodyTrack.git

cd BodyTrack

composer install

cp .env.example .env

php artisan key:generate

php artisan migrate

php artisan storage:link

php artisan serve
```

---

# Configuração do Tesseract OCR

No Windows, instale o Tesseract OCR e configure no arquivo `.env`:

```env
TESSERACT_PATH="C:\\Program Files\\Tesseract-OCR\\tesseract.exe"
```

Para testar no PowerShell:

```powershell
& "C:\Program Files\Tesseract-OCR\tesseract.exe" --version
```

---

# Estrutura Principal

```text
BodyTrack

├── Dashboard
├── Perfil Corporal
├── Pesagens
├── Água
├── Nutrição
│   ├── Registrar Refeição
│   ├── Meus Alimentos
│   ├── OCR Nutricional
│   └── Plano Diário
├── Treinos
│   ├── Registro de Treino
│   ├── Plano Semanal
│   └── Histórico
├── Evolução
├── Check-in Semanal
├── Metas Inteligentes
├── Resumo Semanal
├── Comunidade
│   ├── Feed Social
│   ├── Status
│   ├── Perfil Social
│   ├── Seguidores
│   └── Seguindo
├── Notificações
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

# Roadmap

## v4.1.0

* [ ] Melhorar página individual do post
* [ ] Sistema de denúncia de post
* [ ] Moderação social no painel admin
* [ ] Privacidade de perfil social

## v4.2.0

* [ ] Ranking semanal de evolução
* [ ] Desafios entre usuários
* [ ] Badges e conquistas
* [ ] Estatísticas sociais

## v4.3.0

* [ ] Melhorias no OCR nutricional
* [ ] Banco interno de alimentos
* [ ] Sugestões alimentares automáticas
* [ ] Relatórios nutricionais avançados

## v5.0.0

* [ ] BodyTrack AI
* [ ] Aplicativo Mobile
* [ ] Relatórios avançados
* [ ] Inteligência artificial para saúde, treinos e nutrição

---

# Desenvolvedor

**Felipe André Sousa Brito**

Projeto desenvolvido para estudo, portfólio e aprofundamento em Laravel, Arquitetura Web, UX/UI, Desenvolvimento Full Stack e criação de produto digital completo.

