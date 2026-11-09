<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Recruitment Project

Simple Laravel project for recruitment purposes.

## Installation

Requirements: PHP 8.2+ with the `pdo_sqlite` extension, and Composer.
The project uses SQLite out of the box, so no database server or `.env` database configuration is needed.
Node/npm is **not** required (Bootstrap is loaded from a CDN).

```bash
# 1. Clone the repository
git clone <repo-url>
cd <project-folder>

# 2. Install PHP dependencies
composer install

# 3. Create the environment file and app key
cp .env.example .env
php artisan key:generate

# 4. Create the SQLite database file (database/database.sqlite, ignored by git)
php -r "touch('database/database.sqlite');"

# 5. Build the database from scratch and seed it
php artisan migrate:fresh --seed

# 6. Run the app
php artisan serve
```

Users are created via seeders, no registration needed.

Every company has one Captain. Log in as Steve Rogers to see all three roles: Captain of Stark Industries, a regular member of Wayne Enterprises
and an outsider to E.S.W.A.T.

To reset the database at any time, run `php artisan migrate:fresh --seed` again.

## Task - Captain's Warehouse

Each company has one Captain (stored in the `company_user` pivot table), who manages the company and its members.
Other members can only see the company and its members; people outside the company see only its name.
Whoever creates a company becomes its Captain. Each company has a warehouse that only its Captain can manage.

**What is already in the repo:**
- The **Warehouse** screen for each company, which only the Captain of that company can see. Its **Add item** button is not wired up yet.
- An empty **My items** screen in the top navigation.

**Requirements:**
1. **Add items to the warehouse**: a name and a number of pieces (e.g. "Captain's Shield", 3 pieces).
2. **Assign items**: an item from the warehouse can be assigned to a person from that company.
3. **Reassign items**: change which person from the company an item is assigned to.
4. **My items**: every user sees a list of the items currently assigned to them.
5. **Item history**: every item has a visible history of who had it.

**Deliverable:**
- ETA on t
- A Pull Request with a short description of your decisions.


## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
