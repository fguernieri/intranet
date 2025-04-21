# Intranet Bastards - Versão 1.0

Sistema web modular para gestão interna da empresa, com foco inicial em controle de usuários e permissões. Desenvolvido em PHP + MySQL, rodando no ambiente Laragon (localhost) e pronto para ser migrado para servidores como HostGator.

---

## ✅ Funcionalidades da Versão 1.0

- Login com controle de sessão
- Autenticação segura com `password_hash()` e `password_verify()`
- Proteção de páginas via `auth.php`
- Cadastro de novos usuários
- Listagem de usuários com informações completas
- Desativação e reativação de usuários
- Edição de dados de usuários (exceto senha, por enquanto)

---

## 🗂 Estrutura de Pastas

```
/intranet/
├── index.php
├── login.php
├── logout.php
├── painel.php
├── verifica_login.php
├── auth.php
├── config/
│   └── db.php
├── modules/
│   └── usuarios/
│       ├── novo.php
│       ├── salvar.php
│       ├── listar.php
│       ├── editar.php
│       ├── atualizar.php
│       ├── desativar.php
│       └── ativar.php
```

---

## 🔐 Controle de Acesso

- Apenas usuários com `perfil = 'admin'` podem:
  - Acessar `listar.php`
  - Cadastrar usuários (`novo.php`)
  - Editar ou ativar/desativar usuários

- Perfis disponíveis:
  - `admin`
  - `supervisor`
  - `user`

---

## 💾 Banco de Dados

### Nome: `intra_bastards`

#### Tabela: `usuarios`
```sql
CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  senha_hash VARCHAR(255) NOT NULL,
  cargo VARCHAR(50),
  setor VARCHAR(50),
  perfil ENUM('admin','supervisor','user') DEFAULT 'user',
  telefone VARCHAR(20),
  ativo TINYINT(1) DEFAULT 1,
  criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

---

## ⚙️ Ambiente de Desenvolvimento

- PHP 8.3.16 (Laragon)
- MySQL (localhost)
- Testado via navegador em `http://localhost/intranet/login.php`
- Front-end com uso opcional de TailwindCSS CDN

---

## 🧪 Como testar

1. Acesse `/login.php`
2. Faça login com o admin cadastrado
3. Navegue até `/modules/usuarios/listar.php`
4. Cadastre, edite, ative ou desative usuários

---

## 🚀 Próximos passos sugeridos (versões futuras)

- Módulo de dashboards com permissões
- Filtro e busca de usuários
- Paginação
- Alteração de senha com verificação da senha atual
- Logs de acesso e ações

---

Desenvolvido por Xico com suporte do assistente técnico.

Intranet Bastards - Versão 1.1

Resumo das alterações de 1.0 para 1.1:

1. Layout e Estilização
   - Implementado Tailwind CSS via CDN.
   - Criado arquivo externo de estilos: assets/css/style.css.
   - Definidas classes customizadas (.sidebar, .layout-main, .card, .btn-primary) usando @apply.

2. Tela de Login
   - Redesenho completo com Tailwind: fundo escuro, logo centralizado, campos e botão estilizados.
   - Mensagem de erro de login exibida em destaque no card.

3. Layout Base e Sidebar
   - Extraído menu lateral para sidebar.php na raiz.
   - Sidebar dinâmica: exibe módulos permitidos via consulta ao banco e links de Admin, Alterar Senha, Sair.
   - Sidebar oculta em telas <md (planejado para mobile na próxima versão).

4. Painel (painel.php)
   - Atualizado para carregar sidebar.php.
   - Formatação de date usando IntlDateFormatter (substituindo strftime e utf8_encode).
   - Grid de cards dinâmico com módulos permitidos extraídos do banco (tabelas modulos e modulos_usuarios).

5. CRUD de Usuários (modules/usuarios)
   - Consolidado em pasta modules/usuarios.
   - Telas: novo.php, listar.php, editar.php com redesign Tailwind.
   - Processamentos: salvar.php, atualizar.php e atualização de senha com views de sucesso e redirecionamento.
   - Funcionalidade de ativar/desativar usuário integrada aos botões de ação.

6. Gestão de Permissões (admin_permissoes.php)
   - Tela unificada para listar usuários, criar novo e definir permissões de módulos.
   - Listagem de usuários com colunas Nome, E-mail e Perfil.
   - Checkbox para habilitar módulos por usuário, gravando em modulos_usuarios.
   - Feedback de sucesso e redirecionamento pós-salvar.

7. Segurança e qualidade de código
   - Senhas sempre armazenadas com password_hash() e validadas com password_verify().
   - Includes e paths estruturados com __DIR__ para evitar erros de caminho.
   - Preparado para usar BASE_URL via config/app.php.

8. Preparação para Responsividade
   - Classes responsivas (hidden md:flex, p-4 md:p-10) definidas no CSS.
   - Estrutura pronta para implementar header mobile e toggle de sidebar na próxima versão.

Próximos passos:
- Implementar totalmente o layout mobile (menu toggle).
- Criar e integrar módulo de dashboards.
- Aprimorar validações front-end e feedback em tempo real.
- Introduzir BASE_URL para gerenciamento de caminhos.

Desenvolvido por Xico com suporte técnico do assistente.

README — Intranet | Versão v1.2
================================

Módulo de Permissões & Gerenciamento de Módulos
Desenvolvido em colaboração com Jake 🥷 — AI Dev Hacker Partner™

Versão: v1.2
Status: ✅ Estável | 🔒 Testado localmente e em servidor (HostGator)


✨ Novidades desta versão
-------------------------

🔧 CRUD de Módulos (Novo!)
- Nova seção no painel de administração para criar, editar e excluir módulos
- Campos: nome, descrição, link
- Validação obrigatória para todos os campos
- Exclusão apenas desativa o módulo (ativo = 0)
- Estilo visual harmonizado com o restante do sistema (TailwindCSS)
- Feedback visual para ações (módulo criado, editado, excluído)


🎯 Melhorias de Permissões
- Interface para selecionar permissões por usuário mantida
- Lógica duplicada de submissão foi removida
- Redirecionamento com mensagem de sucesso implementado


⚙️ Ajustes Visuais (Tailwind)
- Botões uniformes (min-w-[90px] h-[30px])
- Inputs em dark mode: fundo cinza escuro, borda cinza, texto branco
- Mensagens verdes para ações concluídas com sucesso
- Alinhamento corrigido entre inputs e botões


⚠️ Correções de Erros
- Corrigido HTML inválido: <form> não envolve mais <tr>
- Corrigido carregamento dos campos descrição e link
- Adicionado id="modulos" na seção para redirecionamento suave via #modulos


🔗 Redirecionamentos Suaves
- Após salvar, editar ou excluir, redireciona com âncora para #modulos
- Reduz a sensação de “piscar” em ambientes compartilhados como HostGator


🗂️ Estrutura Atingida
- admin_permissoes.php: atualizado com todo o gerenciamento
- modulos: tabela utilizada no CRUD
- modulos_usuarios: tabela de vínculo mantida


🧠 Futuras melhorias sugeridas (v1.3+)
- AJAX com fetch() para evitar reloads completos
- Toast messages com expiração automática
- Filtro e pesquisa nos módulos
- Paginação de usuários e módulos


🧑‍💻 Desenvolvido por:
- Você, Dev Master 😎
- Com assistência de Jake 🥷💻 (AI hacker partner by OpenAI)
