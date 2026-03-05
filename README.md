# 📋 NF Organiza - Sistema de Gestão de NF-e

Um sistema web ágil e seguro para micro e pequenas empresas organizarem suas notas fiscais eletrônicas (NF-e) em formato XML. Desenvolvido com foco em performance e usabilidade, o sistema processa os dados no navegador do usuário e os consolida em um banco de dados relacional seguro.

## Principais Funcionalidades

* **Autenticação:** Sistema completo de Login e Cadastro com senhas criptografadas (`password_hash`) no banco de dados e controle de sessão dinâmico.
* **Isolamento de Dados (Relacional):** As notas fiscais são atreladas ao `id` do usuário. Cada cliente visualiza e gerencia exclusivamente os seus próprios documentos.
* **Processamento Frontend (DOMParser):** Upload inteligente via *Drag & Drop*. O sistema lê a estrutura do `.xml` diretamente no navegador, extraindo instantaneamente Número, Data, Fornecedor, CNPJ e Valor antes de enviar ao servidor.
* **Dashboard Analítico:** Visão gerencial automática com contadores de notas e soma de valores referentes ao mês vigente.
* **Filtros:** Tabela de listagem interativa com filtros combinados por período (Data Inicial/Final) e nome do Fornecedor.
* **Gestão Completa:** Visualização de detalhes em Modal, exclusão segura de registros e download do XML original.

---

## Stack Tecnológica

O projeto foi construído utilizando tecnologias consolidadas, sem frameworks pesados, garantindo alta performance e facilidade de implantação:

* **Frontend:** HTML5, CSS3 e Vanilla JavaScript (Arquitetura Single Page Application - SPA).
* **Backend:** PHP Estruturado com PDO (Proteção nativa contra SQL Injection) e tratamento dinâmico de cabeçalhos CORS.
* **Banco de Dados:** MySQL (Arquitetura relacional com chaves estrangeiras).

---

## Como Executar o Projeto (Guia Laragon)

O sistema foi otimizado para rodar nativamente no **Laragon** (ou XAMPP), dispensando a instalação de dependências via terminal.

### 1. Preparando o Banco de Dados
1. Inicie o Laragon (certifique-se de que os serviços MySQL e Nginx/Apache estão rodando).
2. Abra o gerenciador de banco de dados (HeidiSQL, phpMyAdmin, etc.).
3. Execute o script **`database.sql`** fornecido no repositório. Ele criará o banco `nforganiza` e as tabelas `usuarios` e `notas_fiscais`.
   > **Nota:** A API PHP (`api.php`) utiliza as credenciais padrão do Laragon: `host=localhost`, `user=root`, sem senha.

### 2. Rodando a Aplicação
1. Dentro do diretório raiz do seu servidor local (ex: `C:\laragon\www`), crie uma pasta chamada `nforganiza`.
2. Cole todos os arquivos do projeto (`index.html`, `api.php`, `landing_page.html`, etc.) dentro desta pasta.
3. Acesse via navegador: `http://localhost/nforganiza` (ou o domínio virtual gerado pelo Laragon, ex: `http://nforganiza.test`).

---

## Como Testar o Sistema

Como o sistema agora possui autenticação backend real com relacionamento de tabelas, siga estes passos para testar:

1. **Crie uma conta:** Na tela inicial, clique em "Cadastrar-se" e crie um usuário de teste (ex: `teste@teste.com`). As credenciais serão salvas de forma segura no MySQL.
2. **Faça o Upload:** Arraste um arquivo `.xml` válido de NF-e para a área pontilhada.
3. **Valide o Isolamento:** Se você criar uma segunda conta e fizer login com ela, não verá as notas fiscais enviadas pela primeira conta.

---

## Desenvolvedores (Projeto Integrador)

Projeto desenvolvido para o Projeto Integrador por:
* Ana Quezia Flores Costa e Silva
* Erasmo Eloi da Hora Neto
* Guilherme Ramos De Oliveira
* Henrique Dias Van Rossum Da Silva
* Lara Eugenia Campello da Silva Moreira
