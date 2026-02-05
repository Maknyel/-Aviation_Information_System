# Aviation Information System

A full-stack application built with Laravel (backend) and Ionic Vue (frontend).

## Project Structure

- `backend/` - Laravel API backend
- `frontend/` - Ionic Vue frontend application

## Features

- User authentication with Laravel Sanctum
- Role-based access (Admin, HR, Employees, Staff, Students)
- Responsive UI with Ionic Framework + Tailwind CSS
- Custom color scheme (Olive secondary, White primary)
- Environment-based API configuration
- Modern, clean design with gradient backgrounds

## Setup Instructions

### Prerequisites

- PHP 7.4 or higher
- Composer
- Node.js and npm
- MySQL database

### Backend Setup

1. Navigate to the backend directory:
```bash
cd backend
```

2. Create the MySQL database:
```sql
CREATE DATABASE aviation_information_system;
```

3. The `.env` file is already configured. If you need to change database credentials, edit:
```
DB_DATABASE=aviation_information_system
DB_USERNAME=root
DB_PASSWORD=
```

4. Run migrations:
```bash
php artisan migrate
```

5. Seed the database with test users:
```bash
php artisan db:seed
```

6. Start the Laravel development server:
```bash
php artisan serve
```

The backend API will be available at `http://localhost:8000`

### Frontend Setup

1. Navigate to the frontend directory:
```bash
cd frontend
```

2. Install dependencies (already done):
```bash
npm install
```

3. Configure environment variables (already created):
The `.env` file contains the API URL configuration:
```
VITE_API_URL=http://localhost:8000/api
```
You can modify this if your backend is running on a different URL.

4. Start the development server:
```bash
ionic serve
```

The frontend will be available at `http://localhost:8100`

## Test Accounts

After seeding the database, you can login with these accounts:

- **Admin**: admin@aviation.com / password
- **HR**: hr@aviation.com / password
- **Employees**: employees@aviation.com / password
- **Staff**: staff@aviation.com / password
- **Students**: students@aviation.com / password

## Color Scheme

- **Primary (White)**: #F0F5F0
- **Secondary (Olive)**: #6C9A6C

## API Endpoints

### Authentication

- `POST /api/login` - User login
- `POST /api/logout` - User logout (requires authentication)
- `GET /api/user` - Get authenticated user (requires authentication)

## Database Schema

### Users Table

- `id` - Primary key
- `name` - User's full name
- `email` - User's email (unique)
- `password` - Hashed password
- `role` - User role (Admin, HR, Employees, Staff, Students)
- `email_verified_at` - Email verification timestamp
- `remember_token` - Remember me token
- `created_at` - Created timestamp
- `updated_at` - Updated timestamp

## Development

### Backend

The Laravel backend uses:
- Laravel Sanctum for API authentication
- RESTful API architecture
- MySQL database

### Frontend

The Ionic Vue frontend uses:
- Vue 3 with Composition API
- TypeScript
- Ionic Framework components
- Tailwind CSS for styling
- Vue Router for navigation
- Axios for HTTP requests
- Environment-based configuration (Vite)

#### Environment Configuration

The frontend uses a centralized API configuration:
- API URL is defined in `frontend/.env`
- Imported via `@/config/api.ts`
- Easy to update for different environments (development, staging, production)

## License

This project is open-source and available under the MIT License.