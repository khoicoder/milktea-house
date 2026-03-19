# MilkTea House Project Overview

MilkTea House is a web application developed using PHP and MySQL, designed for managing a milk tea shop. It features a complete workflow from product browsing and filtering to a shopping cart system and an admin dashboard.

## Tech Stack
- **Backend:** PHP (Procedural style with `mysqli` extension)
- **Frontend:** HTML5, Vanilla CSS, Vanilla JavaScript
- **Database:** MySQL
- **Environment:** Optimized for XAMPP/WAMP (runs on `localhost`)

## Project Structure
- `admin/`: Contains administrative pages like `dashboard.php`, `add_product.php`, and `edit_product.php`.
- `ajax/`: Backend handlers for asynchronous requests (e.g., `add_cart.php`, `remove_cart.php`).
- `config/`: Core configuration, including database connection and global constants (`config.php`).
- `includes/`: Reusable UI components such as `header.php`, `footer.php`, and `filter.php`.
- `pages/`: Public-facing pages like `cart.php`, `login.php`, `menu.php`, and `product_detail.php`.
- `css/` & `js/`: Static assets for styling and client-side logic.
- `images/`: Stores product images and user avatars.

## Core Features
- **Product Management:** Browsing products by category, searching by name, and filtering by price.
- **Shopping Cart:** Asynchronous "Add to Cart" functionality using JavaScript `fetch` and PHP sessions.
- **User Authentication:** Login, registration, and profile management with role-based access (User vs. Admin).
- **Admin Dashboard:** Interface for administrators to manage products and view site statistics.

## Development Conventions
- **Database Interaction:** Uses `mysqli` for executing SQL queries.
- **Pathing:** Uses a global `BASE_URL` constant (defined in `config/config.php`) to ensure consistent link and asset paths.
- **State Management:** Uses PHP `$_SESSION` for user authentication and persistent shopping cart data.
- **Logic Separation:** Shared components are modularized in the `includes/` directory.

## Building and Running
### Prerequisites
- PHP 7.4 or higher
- MySQL / MariaDB
- A local server environment like XAMPP or Laragon

### Setup Instructions
1.  **Database:** Create a database named `milktea_house` in your MySQL server.
2.  **Configuration:** Ensure `config/config.php` matches your local database credentials (host, user, password).
3.  **Deployment:** Place the project folder in your server's web root (e.g., `C:/xampp/htdocs/milktea-house`).
4.  **Access:** Open your browser and navigate to `http://localhost/milktea-house/`.

## Key Commands/Tasks (TODO)
- [ ] Implement a full SQL export/import script for database schema initialization.
- [ ] Add more comprehensive form validation on both client and server sides.
- [ ] Improve security by migrating from raw `mysqli_query` to prepared statements for all user inputs.
