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