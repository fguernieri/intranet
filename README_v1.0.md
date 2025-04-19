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