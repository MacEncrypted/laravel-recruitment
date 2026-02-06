<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Recruitment Task

Simple Laravel project for recruitment purposes.

### Installation
```bash
git clone <repo-url>
cd <project-folder>
cp .env.example .env
composer install
php artisan key:generate
```

Configure database in `.env`.

```bash
php artisan migrate:fresh --seed
```
Users are created via seeders, no registration needed.

### Task 1 - Company Captains

Inspect how Avengers can be added/removed from Companies.

**Change Requirements:**
1. First user added to company becomes captain
2. Only captain can add/remove users
3. Captain can transfer captain role to another user (loses captain status)

**Hints:**
- Modify `company_user` pivot table
- Update authorization logic
- Show captain badge in UI


## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
