Instruções de Deploy no HostGator e Atualização do Banco - Intranet Bastards v1.1

1. Preparar os arquivos:
   - Compacte toda a pasta `intranet/` (exceto `.git/`) em um arquivo ZIP.

2. Acessar o cPanel do HostGator:
   - Faça login no cPanel da sua conta HostGator.
   - Vá em **Arquivos > Gerenciador de Arquivos (File Manager)**.
   - Navegue até a pasta `public_html` ou a subpasta onde deseja hospedar a intranet.
   - Clique em **Upload** e envie o ZIP.

3. Descompactar os arquivos:
   - No File Manager, selecione o ZIP e clique em **Extract**.
   - Verifique se foi criada a pasta `intranet/` com toda a estrutura.

4. Configurar o Banco de Dados:
   a. Criar o database:
      - Em cPanel, vá em **Bancos de Dados > MySQL® Databases**.
      - Em “Create New Database”, insira o nome (ex: `intra_bastards`) e clique em **Create**.
   b. Criar usuário MySQL:
      - Na seção “MySQL Users”, crie um usuário (ex: `intra_user`) com senha forte.
      - Anote usuário e senha.
   c. Associar usuário ao database:
      - Em “Add User to Database”, selecione usuário e database criados.
      - Marque **All Privileges** e confirme.

5. Importar estrutura e dados:
   - Ainda no cPanel, vá em **phpMyAdmin**.
   - Selecione o database `intra_bastards`.
   - Clique em **Import** e selecione o arquivo SQL de estrutura (ex: `intra_bastards.sql`) que você exportou localmente.
   - Aguarde importação e verifique se as tabelas `usuarios`, `modulos`, `modulos_usuarios`, etc., estão presentes.

6. Atualizar arquivo de configuração:
   - No File Manager, navegue até `intranet/config/db.php`.
   - Edite-o (clique em **Edit**):
     ```php
     <?php
     $host = 'localhost';
     $dbname = 'nome_do_seu_database';
     $user = 'seu_usuario_mysql';
     $pass = 'sua_senha_mysql';
     ```
   - Salve as alterações.

7. Ajustar BASE_URL (opcional):
   - Se você usa `config/app.php` com `define('BASE_URL', '/intranet');`, garanta que esteja correto.
   - Se hospedado diretamente em `public_html`, use `define('BASE_URL', '');`.

8. Gerar hash de senha do admin (se necessário):
   - Crie temporariamente `intranet/gerar_hash.php` com o conteúdo:
     ```php
     <?php echo password_hash('SenhaAdmin123', PASSWORD_DEFAULT); ?>
     ```
   - Acesse `https://seusite.com/intranet/gerar_hash.php`, copie o hash.
   - No phpMyAdmin, atualize o admin:
     ```sql
     UPDATE usuarios
     SET senha_hash = '...HASH_COPIADO...'
     WHERE email = 'admin@empresa.com';
     ```
   - Apague `gerar_hash.php`.

9. Definir permissões de pasta:
   - Certifique-se de que `sidebar.php`, `config/`, `modules/` e `assets/` estejam acessíveis.
   - Arquivos PHP em 644, pastas em 755.

10. Testar o sistema:
    - Acesse `https://seusite.com/intranet/login.php`.
    - Realize login com admin e senha testada.
    - Verifique as funcionalidades: listagem, cadastro, edição, permissões e painéis.

11. Manutenção futura:
    - Para atualizar versões, substitua os arquivos via FTP ou File Manager.
    - Para alterações de banco, use phpMyAdmin ou scripts `.sql`.

Pronto! Seu sistema Intranet Bastards v1.1 está em produção.