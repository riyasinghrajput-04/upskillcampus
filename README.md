# QuickBite – Food Delivery App
## Internship Project | Stack: HTML · CSS · JavaScript · PHP · MySQL

---

## Project Structure

```
fooddelivery/
├── index.html              ← Main frontend (single-page app)
├── php/
│   ├── config.php          ← DB config + helper functions
│   ├── auth.php            ← Login / Register / Logout API
│   ├── restaurants.php     ← Restaurant listing + menu API
│   └── orders.php          ← Place order + view orders API
└── sql/
    └── schema.sql          ← Database schema + sample data
```

---

## Setup Instructions (XAMPP / WAMP)

### Step 1 – Start Local Server
- Open **XAMPP Control Panel**
- Start **Apache** and **MySQL**

### Step 2 – Copy Project Files
- Copy the entire `fooddelivery/` folder to:
  - XAMPP: `C:\xampp\htdocs\fooddelivery\`
  - WAMP:  `C:\wamp64\www\fooddelivery\`

### Step 3 – Create Database
- Open browser → go to `http://localhost/phpmyadmin`
- Click **Import** tab
- Choose file: `sql/schema.sql`
- Click **Go**

### Step 4 – Configure DB (if needed)
- Open `php/config.php`
- Update `DB_USER` and `DB_PASS` if your MySQL credentials differ

### Step 5 – Run the App
- Open browser → `http://localhost/fooddelivery/`

---

## Features Implemented

| Feature            | Where                         |
|--------------------|-------------------------------|
| User Registration  | `php/auth.php` → action=register |
| User Login/Logout  | `php/auth.php` → action=login    |
| Restaurant Listing | `php/restaurants.php` → action=list |
| Search & Filter    | Frontend JS + query params    |
| Menu Display       | `php/restaurants.php` → action=get |
| Add to Cart        | Frontend JS (session state)   |
| Place Order        | `php/orders.php` → action=place  |
| Order History      | `php/orders.php` → action=list   |

---

## Database Tables

| Table          | Purpose                      |
|----------------|------------------------------|
| `users`        | Registered user accounts     |
| `restaurants`  | Restaurant details           |
| `menu_items`   | Food items per restaurant    |
| `orders`       | Customer orders              |
| `order_items`  | Items within each order      |

---

## Test Credentials
- **Email:** rahul@example.com
- **Password:** password

---

## API Endpoints

### Auth (`php/auth.php`)
| Action     | Method | Parameters                          |
|------------|--------|-------------------------------------|
| `login`    | POST   | email, password                     |
| `register` | POST   | name, email, password, phone, address|
| `logout`   | POST   | –                                   |
| `check`    | GET    | –                                   |

### Restaurants (`php/restaurants.php`)
| Action     | Method | Parameters           |
|------------|--------|----------------------|
| `list`     | GET    | search, cuisine      |
| `get`      | GET    | id                   |
| `cuisines` | GET    | –                    |

### Orders (`php/orders.php`)
| Action        | Method | Parameters                                     |
|---------------|--------|------------------------------------------------|
| `place`       | POST   | restaurant_id, delivery_address, payment_method, items[] |
| `list`        | GET    | –                                              |
| `update_status`| POST  | order_id, status                              |
