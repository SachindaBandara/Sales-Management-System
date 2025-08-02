# Sales Management System

## Overview

A comprehensive web-based Sales Management System built with Laravel and Vue.js. This application provides a complete solution for managing products, orders, customers, brands, and categories with advanced features like data export/import and invoice generation.

## Features

### Dashboard
- Admin dashboard with sales analytics
- Revenue statistics and charts
- Recent orders overview
- Product inventory status
- Customer dashboard with order history

### User Management
- Role-based authentication (Admin and Customer roles)
- User activity tracking
- User profile management
- User status management (active/inactive)

### Product Management
- Complete product CRUD operations
- Product categorization and brand association
- Product inventory tracking
- Product status management (active, draft, archived)
- Product image management
- Product metadata and SEO optimization

### Order Management
- Order creation and processing
- Order status tracking (pending, processing, shipped, delivered, cancelled)
- Payment status tracking (pending, paid, failed, refunded)
- Shipping and billing address management
- Order notes and timestamps
- Bulk order actions
- Order statistics and reporting

### Brand & Category Management
- Brand CRUD operations
- Category CRUD operations
- Hierarchical category structure

### Shopping Features
- Product browsing and search
- Shopping cart functionality
- Checkout process
- Order history for customers
- Invoice download for customers

### Data Import/Export
- Export products, orders, users, brands, and categories to Excel
- Import products, users, brands, and categories from Excel
- Customizable export filters
- CSV validation for data integrity
- Import error handling and reporting

### Invoice Generation
- PDF invoice generation
- Customizable invoice templates

## Technology Stack

### Backend
- **Framework**: Laravel 12.x
- **PHP Version**: 8.2+
- **Database**: MySQL/PostgreSQL
- **PDF Generation**: DomPDF
- **Excel Processing**: Maatwebsite Excel

### Frontend
- **Framework**: Vue.js 3.x with Composition API
- **Build Tool**: Vite
- **TypeScript**: Full TypeScript support
- **UI Framework**: Tailwind CSS
- **SPA Integration**: Inertia.js
- **Charts**: Chart.js
- **Tables**: TanStack Table
- **Icons**: Lucide Vue

## Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js and NPM
- MySQL or PostgreSQL

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd sales-management
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install JavaScript dependencies**
   ```bash
   npm install
   ```

4. **Set up environment file**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure your database in the .env file**
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=sales_management
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. **Run database migrations and seeders**
   ```bash
   php artisan migrate --seed
   ```

7. **Link storage for file uploads**
   ```bash
   php artisan storage:link
   ```

8. **Build frontend assets**
   ```bash
   npm run dev
   ```

9. **Start the development server**
   ```bash
   php artisan serve
   ```

   The application will be available at `http://localhost:8000`

## Development

### Running in Development Mode

Use the following command to run the application with hot-reloading:

```bash
composer dev
```

This will concurrently run:
- Laravel development server
- Queue worker
- Vite development server

### Running with SSR

For server-side rendering:

```bash
composer dev:ssr
```

## Testing

Run the test suite with:

```bash
composer test
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
