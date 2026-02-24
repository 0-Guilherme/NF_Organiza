# 📋 NF Organiza - MVP

Prova de conceito (MVP) de um sistema web para micro e pequenas empresas organizarem suas notas fiscais eletrônicas (NF-e) em formato XML. Focado em simplicidade, agilidade e acessibilidade, o sistema extrai dados do XML no próprio navegador e os consolida em um banco de dados relacional.

## ⚙️ Stack Tecnológica
Desenvolvido para atender aos requisitos da 2ª etapa do Projeto Integrador:
* **Frontend:** HTML5, CSS3 e Vanilla JavaScript (Single Page Application).
* **Processamento de Dados:** API nativa `DOMParser` do JavaScript (processamento no cliente).
* **Backend:** PHP Estruturado utilizando PDO (proteção contra SQL Injection).
* **Banco de Dados:** MySQL.

## 🚀 Funcionalidades Principais
* **Autenticação Simulada (Mock):** Login e sessão controlados via `localStorage` no frontend para agilidade de testes.
* **Upload Dinâmico:** Área de "Drag & Drop" que lê arquivos `.xml` e extrai instantaneamente Número, Data, Fornecedor, CNPJ e Valor.
* **Dashboard Analítico:** Métricas e soma de valores referentes ao mês vigente.
* **Listagem e Filtros:** Tabela de notas com filtros combinados por período (Data Inicial/Final) e Fornecedor.
* **Download de XML:** Reconstrução e download da nota fiscal original armazenada no banco em formato BLOB/Text.

---

## ▶️ Como Executar e Testar (Guia Rápido)

O projeto foi configurado para rodar nativamente no **Laragon** (ou XAMPP), sem necessidade de instalação de dependências via terminal.

### 1. Configurando o Banco de Dados
1. Abra o Laragon e inicie o MySQL e o Apache.
2. Abra o gerenciador de banco de dados (ex: HeidiSQL ou phpMyAdmin).
3. Execute integralmente o script **`database.sql`**. Ele criará o banco `nforganiza` e as tabelas necessárias.
   * *Nota técnica:* A conexão no `api.php` utiliza o padrão do Laragon (`host=localhost`, `user=root`, sem senha).

### 2. Rodando a Aplicação
1. Crie uma pasta chamada `nforganiza` dentro da raiz do seu servidor (`C:\laragon\www\nforganiza`).
2. Cole os arquivos do projeto (`index.html`, `api.php`, `landing_page.html`) nesta pasta.
3. Acesse via navegador: `http://localhost/nforganiza/landing_page.html` (para ver a página de apresentação) ou diretamente `http://localhost/nforganiza/index.html` (para o sistema).

### 3. Como testar o Login (Importante)
Para facilitar a avaliação deste MVP, o sistema de autenticação atua de forma simulada no frontend:
* Na tela de login, insira **qualquer e-mail válido** (ex: `teste@teste.com`) e **qualquer senha** (mínimo de 3 caracteres).
* O acesso será liberado imediatamente e a sessão salva no seu navegador.
* *Nota técnica:* Embora a tabela `usuarios` exista no banco de dados estruturalmente, o salvamento das notas fiscais via `api.php` neste MVP grava os documentos diretamente na tabela `notas_fiscais` sem exigir chave estrangeira obrigatória, agilizando a demonstração.

---

## 👥 Desenvolvedores (Projeto Integrador)
* Ana Quezia Flores Costa e Silva
* Erasmo Eloi da Hora Neto
* Guilherme Ramos De Oliveira
* Henrique Dias Van Rossum Da Silva
* Lara Eugenia Campello da Silva Moreira
