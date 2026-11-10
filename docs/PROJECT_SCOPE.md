# Escopo do produto

Estado do código em novembro/2026. Não trata intenção de README como funcionalidade pronta.

## Objetivo geral

Site institucional da H. A Consultoria & Desenvolvimento, com canal de contato e área interna para a equipe operar conteúdo, mensagens, usuários e um funil simples de propostas.

A primeira implantação é da própria consultoria. O mesmo código pode ser copiado para **outra empresa**, cada uma com instalação, `.env` e banco **próprios**. Isso não é SaaS multi-tenant: não há contas de clientes compartilhando um único runtime, nem isolamento por tenant no código.

## Público-alvo

- Visitantes do site (conhecer serviços, enviar mensagem, acionar WhatsApp/agendar).
- Equipe interna autenticada (`admin`, `editor`, `viewer`).

Não há cadastro público nem área do cliente final.

## Problemas que o software resolve

- Presença web com textos e imagem de início editáveis sem alterar PHP.
- Receber e guardar mensagens do formulário (e tentar notificar por e-mail).
- Controlar quem acessa a área interna.
- Registrar propostas (cliente, valores, prazos, estágio), inclusive ocultação por soft-delete.
- Evoluir o schema de várias instalações com o mesmo repositório Git (`migrate` / `baseline`).

## Módulos existentes

1. **Institucional** — landing (hero, sobre, serviços estáticos, contato, CTA).
2. **Autenticação** — login, sessão, logout.
3. **Dashboard** — atalhos e dois indicadores.
4. **Configuração** — início, sobre, contato, usuários, propostas, mensagens.
5. **Migrations CLI** — schema versionado, fora do HTTP.
6. **Seeders CLI** — dados iniciais idempotentes, fora do HTTP.

## Funcionalidades implementadas

- Landing lendo `dados_inicio`, `sobre`, `dados_contato`.
- POST `/contato/enviar` com CSRF, validação, persistência e `mail()` condicional.
- Login com CSRF, rate limit, `password_verify`.
- Perfis `admin` / `editor` / `viewer`.
- CRUD operacional de contato, sobre, início (upload de imagem), propostas (soft-delete).
- Criação de usuários e troca de tipo (admin).
- Listagem de mensagens (autenticados).
- CLI: `status`, `migrate`, `rollback --confirm`, `baseline --confirm`.
- CLI de seeders: `php bin/seed.php run` (admin via ambiente; conteúdo neutro).

## Em desenvolvimento / incompleto no código

- Indicador “acessos hoje” ainda mock.
- WhatsApp da landing: a view ignora o número do banco.
- Anos de experiência na home: ano final fixo `2026` na view.
- Logout por GET (sem CSRF).
- Tela `email-template` residual (GET redireciona; sem persistência).
- `users.email` sem UNIQUE.
- README descreve CMS/API/SaaS que **não existem**.

## Futuro (não implementado)

Itens citados em material antigo, **sem código**:

- Produto SaaS / multi-tenant.
- API pública.
- CMS genérico além das telas de config atuais.
- Módulo amplo de e-mail marketing.
- Cadastro ou recuperação de senha pelo visitante.

Tratar como ideia, não como compromisso do repositório.

## Regras de negócio conhecidas

Ver também `PROJECT_CONTEXT.md` §8.

- Conteúdo institucional editável só por `admin` e `editor`.
- Só `admin` cria usuários e muda tipos.
- Visitante autenticado (inclui `viewer`) lê mensagens.
- Proposta com `estagio = 'excluido'` não aparece no funil.
- Baseline **não** cria o primeiro admin.
- Uma instalação, um banco.

## Limites do escopo atual

- Sem faturamento, contratos digitais, chat ou CRM completo.
- Sem HTTPS imposto pela aplicação.
- Sem testes automatizados de HTTP.
- Serviços da landing não são editáveis pelo painel.
- Uploads de início ficam em `public/uploads/` (nome de arquivo no banco).
- CLI de migrations não deve ser exposto como URL.
