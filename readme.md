<a name="readme-top">

<br/>

<br />
<div align="center">
  <a href="https://github.com/thirdworld3/">
  <img src="./assets/img/logo.png" alt="The Forbidden Codex" width="400" height="100">
  </a>

  <h3 align="center">The Forbidden Codex</h3>
</div>

<div align="center">
  A Black Market Mythology Website
</div>

<br />

![](https://visit-counter.vercel.app/counter.png?page=a-manalo/AD-Final-Project)

---

<br />
<br />

<details>
  <summary>Table of Contents</summary>
  <ol>
    <li>
      <a href="#overview">Overview</a>
      <ol>
        <li>
          <a href="#key-components">Key Components</a>
        </li>
        <li>
          <a href="#technology">Technology</a>
        </li>
      </ol>
    </li>
    <li>
      <a href="#rule,-practices-and-principles">Rules, Practices and Principles</a>
    </li>
    <li>
      <a href="#resources">Resources</a>
    </li>
  </ol>
</details>

---

## Overview

The Forbidden Codex is a mysterious black market platform set in a mythology theme. Users step into a shadowy marketplace where they can buy forbidden products, manage their account, and interact based on their assigned roles. The website has user authentication, role-based functionalities, transactions, and CRUD operations. Whether you’re a Buyer looking to acquire rare artifacts, a Seller managing secret listings, or an Admin maintaining the order behind the chaos, this website is a functional dark e-commerce platform.

### Key Components

- Home Page
  - Hero Section
  - Our Offers
  - The Forbidden Codex of Conduct
  - Footer
- Products Page
  - Product Categories
    - Product Details
    - Buy Product for Buyers
    - Sell Product for Sellers
- Cart & Checkout
  - Add to Cart, Remove from Cart
  - Cart Summary and Item Counts
  - Checkout and Order Placement
- Payment Page
  - Payment Form Submission
- Account Page
  - User Profile
  - Orders and Order Details
  - Dynamic Sidebars Based on Role
- Admin Pages
  - Users, Products, Orders, Categories
- Authentication System
  - Login
  - Signup
  - Role Authentication
- Error Page
- CRUD Operations
- Database Integration (PostgreSQL & MongoDB)

### Technology

#### Language
![HTML](https://img.shields.io/badge/HTML-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS](https://img.shields.io/badge/CSS-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)

#### Framework/Library
![Bootstrap](https://img.shields.io/badge/Bootstrap-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)

#### Databases
![MongoDB](https://img.shields.io/badge/MongoDB-47A248?style=for-the-badge&logo=mongodb&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-336791?style=for-the-badge&logo=postgresql&logoColor=white)

## Rules, Practices and Principles

<!-- Do not Change this -->

1. Always use `AD-` in the front of the Title of the Project for the Subject followed by your custom naming.
2. Do not rename `.php` files if they are pages; always use `index.php` as the filename.
3. Add `.component` to the `.php` files if they are components code; example: `footer.component.php`.
4. Add `.util` to the `.php` files if they are utility codes; example: `account.util.php`.
5. Place Files in their respective folders.
6. Different file naming Cases
   | Naming Case | Type of code         | Example                           |
   | ----------- | -------------------- | --------------------------------- |
   | Pascal      | Utility              | Accoun.util.php                   |
   | Camel       | Components and Pages | index.php or footer.component.php |
8. Renaming of Pages folder names are a must, and relates to what it is doing or data it holding.
9. Use proper label in your github commits: `feat`, `fix`, `refactor` and `docs`
10. File Structure to follow below.

```
AD-Final-Project
├── add_cart_table.sql
├── add_image_url_column.sql
├── bootstrap.php
├── compose.yml
├── composer.json
├── composer.lock
├── config.php
├── database
│   ├── project_users.model.sql
│   ├── projects.model.sql
│   ├── tasks.model.sql
│   └── users.model.sql
├── docker_setup.bat
├── Dockerfile
├── favicon.ico
├── handlers
│   ├── auth.handler.php
│   ├── mongodbChecker.handler.php
│   └── postgreChecker.handler.php
├── index.php
├── public
│   ├── 404.php
│   ├── account
│   │   ├── index.php
│   │   ├── order-details.php
│   │   └── orders.php
│   ├── admin
│   │   ├── categories.php
│   │   ├── index.php
│   │   ├── order-details.php
│   │   ├── orders.php
│   │   ├── products.php
│   │   └── users.php
│   ├── assets
│   │   ├── css
│   │   │   └── style.css
│   │   ├── images
│   │   │   └── products/...
│   │   └── js
│   │       └── script.js
│   ├── cart
│   │   ├── add.php
│   │   ├── checkout.php
│   │   ├── count.php
│   │   ├── index.php
│   │   ├── simple_add.php
│   │   └── simple_count.php
│   ├── includes
│   │   ├── auth.php
│   │   ├── db.php
│   │   ├── footer.php
│   │   ├── header.php
│   │   ├── helpers.php
│   │   └── sidebar.php
│   ├── index.php
│   ├── login.php
│   ├── logout.php
│   ├── payment.php
│   ├── product.php
│   ├── products.php
│   └── signup.php
├── README.md
├── schema.sql
├── staticData
│   └── dummies
│       └── users.staticData.php
├── utils
│   ├── auth.util.php
│   ├── dbMigratePostgresql.util.php
│   ├── dbResetPostgresql.util.php
│   ├── dbSeederPostgresql.util.php
│   └── envSetter.util.php
└── vendor
    └── ...
```
> The following should be renamed: name.css, name.js, name.jpeg/.jpg/.webp/.png, name.component.php(but not the part of the `component.php`), Name.utils.php(but not the part of the `utils.php`)

## Resources

| Title        | Purpose                                                                       | Link          |
| ------------ | ----------------------------------------------------------------------------- | ------------- |
| Google Fonts API          | Used for integrating custom fonts for styling                                                                           | https://fonts.googleapis.com           |
| ChatGPT | Used for debugging, dummy data creation, and proper code structure. | https://chatgpt.com/ |
| Bootstrap | Used for frontend framework to make UI responsive. | https://getbootstrap.com |
| Codehal | Referenced for PHP/MySQL e-commerce tutorials | https://www.youtube.com/@Codehal |