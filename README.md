# NF Organiza - Sistema de Gestao de NF-e

Um sistema web agil e seguro para micro e pequenas empresas organizarem suas notas fiscais eletronicas (NF-e) em formato XML.

## Principais Funcionalidades

- Autenticacao com cadastro/login e senha com `password_hash`.
- Isolamento de dados por usuario.
- Upload de XML com leitura no frontend (DOMParser).
- Dashboard com total de notas e soma do mes.
- Filtros por periodo e fornecedor.
- Visualizacao, exclusao e download do XML.

---

## Stack Tecnologica

- Frontend: HTML5, CSS3 e JavaScript puro.
- Backend: PHP com PDO.
- Banco de Dados: MySQL.

---

## Como Executar o Projeto

O sistema roda sem build, apenas com PHP + MySQL.

### 1. Configurando o banco de dados
1. Crie um banco MySQL no seu ambiente (local ou hospedagem).
2. Selecione esse banco no gerenciador (phpMyAdmin, HeidiSQL, etc.).
3. Execute o script `database.sql` para criar as tabelas `usuarios` e `notas_fiscais`.
   - Em hospedagem compartilhada, nao use `CREATE DATABASE` e `USE`.

### 2. Configurando variaveis de ambiente
1. Crie um arquivo `.env` na mesma pasta do `api.php`.
2. Use este modelo:

```env
DB_HOST=localhost
DB_NAME=seu_banco
DB_USER=seu_usuario
DB_PASS=sua_senha
DB_CHARSET=utf8mb4
```

3. O arquivo `.env` esta no `.gitignore` e nao deve ser versionado.
4. O arquivo `.env.example` pode ser versionado como modelo.

### 3. Publicando os arquivos
1. Envie os arquivos do projeto para a raiz do site/subdominio.
2. Garanta que `index.html` e `api.php` estejam na mesma pasta.
3. Acesse a URL do dominio/subdominio no navegador.

---

## Como Testar o Sistema

1. Crie uma conta na tela inicial.
2. Faca login.
3. Envie um arquivo `.xml` valido de NF-e.
4. Verifique o isolamento criando outra conta.

---

## Desenvolvedores (Projeto Integrador)

- Ana Quezia Flores Costa e Silva
- Erasmo Eloi da Hora Neto
- Guilherme Ramos De Oliveira
- Henrique Dias Van Rossum Da Silva
- Lara Eugenia Campello da Silva Moreira
