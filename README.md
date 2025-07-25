# 🛒 Mini E-commerce em PHP (Slim 4 + Twig)

Este é um projeto de **mini e-commerce** desenvolvido em **PHP 8.3**, utilizando o micro-framework [Slim 4](https://www.slimframework.com/) e o motor de templates [Twig 3](https://twig.symfony.com/) com arquitetura **MVC pura**.

---

## 📌 Funcionalidades

- **Produtos** (CRUD completo)
  - Cadastro com variações (ex: cor, tamanho)
- **Cupons** (CRUD completo)
- **Carrinho de compras**
  - Adição de produtos
  - Cálculo de frete
  - Finalização do pedido
- **Pedidos**
  - Visualização dos pedidos realizados
  - Integração com webhook para atualização de status

---

## 🗂️ Estrutura do Projeto

```bash
/
├── app/                  # Front-end (views, templates Twig)
│   ├── public/
│   ├── Views/
├── api/                  # Back-end (controllers, models, rotas)
│   ├── Controllers/
│   ├── Models/
│   ├── Core/
│   ├── database/
│   ├── config/
│       └── .env.example
├── vendor/               # Dependências via Composer
├── composer.json
````

---

## 🚀 Tecnologias Utilizadas

* **PHP 8.3**
* **Slim 4** (`slim/slim`)
* **Twig 3** (`twig/twig`)
* **Slim PSR-7** (`slim/psr7`)
* **Slim HTTP** (`slim/http`)
* **PHP dotenv** (`vlucas/phpdotenv`)
* **PHPMailer** (`phpmailer/phpmailer`)

---

## ⚙️ Configuração do Ambiente Local

---

### 🧰 Requisitos

* PHP 8.3+
* Composer ([getcomposer.org](https://getcomposer.org/download/))
* Servidor local: **XAMPP ou Laragon**

---

## 📦 Instalação

### 1. Clonar o projeto

```bash
git clone https://github.com/estevaotl/projeto-erp.git
cd projeto-erp
```

### 2. Instalar dependências

> ⚠️ Após instalar o Composer, execute:

```bash
composer install
```

### 3. Copiar o arquivo `.env`

```bash
cp api/config/.env.example api/config/.env
```

Edite o arquivo `.env` com as configurações do seu banco:

```dotenv
DB_HOST=localhost
DB_NAME=nome_banco
DB_USER=usuario
DB_PASS=senha
```

---

## 🖥️ Configuração no XAMPP

### 1. Criar Virtual Hosts

Abra o arquivo:

```
C:/xampp/apache/conf/extra/httpd-vhosts.conf
```

Adicione:

```apache
<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs/projeto-erp/app/public"
    ServerName projeto-erp.test
    <Directory "C:/xampp/htdocs/seu-projeto/app/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 2. Editar o arquivo `hosts`

```
C:/Windows/System32/drivers/etc/hosts
```

Adicione:

```
127.0.0.1 projeto-erp.test
```

### 3. Reiniciar Apache

Abra o XAMPP e clique em **"Stop" → "Start"** no módulo Apache.

---

## 🖥️ Configuração no Laragon

### 1. Copiar o projeto para a pasta do Laragon

Coloque o projeto em:

```
C:/laragon/www
```

```bash
git clone https://github.com/estevaotl/projeto-erp.git
cd projeto-erp
```

### 2. Abrir Laragon > Menu > Sites > Add

Adicione o dominio:

```
projeto-erp.test    →  apontando para: C:/laragon/www/projeto-erp/app/public
```

O Laragon se encarrega de configurar tudo (inclusive hosts e vhosts).

### 4. Reinicie o Laragon

Clique em **Menu > Restart all** para aplicar as alterações.

---

## 🌐 Acesso ao Projeto

* **Loja :** [http://projeto-erp.test](http://projeto-erp.test)

---

## 🧪 Testes

* Acesse `/produtos` para gerenciar produtos
* Use `/carrinho` para testar a experiência de compra
* Finalize pedidos e visualize no painel de pedidos
* Gerencie cupons e variações

---

## 👨‍💻 Autor

- Desenvolvido por **Estêvão Leite**
- 📧 Email: [estevaotlnf@gmail.com](mailto:estevaotlnf@gmail.com)
- 🔗 [LinkedIn](https://linkedin.com/in/estevao-leite)

---

## 📄 Licença

- Este projeto está sob a licença MIT.
- Sinta-se livre para usar, modificar e contribuir.
