# ⚔️ Rinha de Backend 2023

O objetivo do desafio foi desenvolver uma **API de alta performance** capaz de **inserir e consultar pessoas** no banco de dados, resistindo a uma **bateria intensa de testes de carga e concorrência**.

---

## 🧭 Rotas Desenvolvidas

| Método | Endpoint | Descrição |
|:-------:|:----------|:-----------|
| 🟢 **GET** | `/` | Retorna o status da API |
| 🟢 **GET** | `/pessoas/{id:.+}` | Busca uma pessoa específica através do ID |
| 🟢 **GET** | `/pessoas?t=` | Busca pessoas por termo |
| 🟠 **POST** | `/pessoas` | Insere uma pessoa no banco |
| 🟢 **GET** | `/contagem-pessoas` | Conta o total de pessoas cadastradas |

---

## ⚙️ Tecnologias Utilizadas

- 🐘 **PHP 8.2.12 **
- ⚡ **Swoole** — Framework de alta performance para lidar com múltiplas conexões simultâneas.
- 🐬 **MySQL** — Banco de dados relacional utilizado para armazenar as pessoas.
- 🌐 **Nginx** — Proxy reverso e balanceador de carga entre as instâncias da API.
- 🚀 **Gatling** — Ferramenta utilizada para os testes de carga e estresse.

---

## ⚙️ Requisitos Técnicos

📋 **Limites de Recursos (por especificação do desafio):**  
- 🧠 **CPU:** 1.5  
- 💾 **Memória:** 3.0 GB  

🏗️ **Componentes obrigatórios:**  
- 2 instâncias da API  
- 1 instância Nginx  
- 1 banco de dados MySQL  

---

## 🚀 Como Executar o Projeto

1. **Instalar as dependências:**
   ```bash
   composer install

2. **Rodar o projeto:**
   ```bash
   php server.php
