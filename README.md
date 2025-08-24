# Blog Management Portal

A simple blog management system with JWT authentication and Pixabay media integration built with PHP and MySQL.

## Features

- **JWT Authentication** with role-based access control (admin/user)
- **Blog Post Management** with CRUD operations and soft delete
- **Pixabay Integration** for adding cover images and videos to posts
- **Public Post Viewing** with SEO-friendly URLs
- **Dashboard** with search, filtering, and pagination
- **Responsive Design** that works on desktop and mobile

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache web server with mod_rewrite enabled
- Pixabay API account (free registration at https://pixabay.com/api/docs/)

## Installation

### 1. Clone or Download the Project

```bash
git clone <repository-url>
cd blog-management-portal
```

### 2. Database Setup

1. Create a MySQL database:
```sql
CREATE DATABASE blog_system;
```

2. Import the database schema and seed data:
```bash
mysql -u root -p blog_system < database.sql
```

### 3. Environment Configuration

1. Copy the example environment file:
```bash
cp .env.example .env
```

2. Edit `.env` with your configuration:
```env
# Database Configuration
DB_HOST=localhost
DB_NAME=blog_system
DB_USER=root
DB_PASS=your_mysql_password

# JWT Configuration (generate a strong random secret)
JWT_SECRET=your-super-secret-jwt-key-here-make-it-long-and-random

# Pixabay API Configuration
PIXABAY_API_KEY=your-pixabay-api-key-here

# Application Settings
BASE_URL=http://localhost:8000
```

### 4. Get Your Pixabay API Key

1. Register for a free account at https://pixabay.com/accounts/register/
2. Go to https://pixabay.com/api/docs/
3. Copy your API key and add it to your `.env` file

### 5. Start the Application

#### Option A: PHP Built-in Server (Development)
```bash
php -S localhost:8000
```

#### Option B: Apache/XAMPP (Production-like)
1. Copy files to your web server directory (e.g., `/var/www/html/blog`)
2. Ensure Apache has mod_rewrite enabled
3. Access via your web server

## Usage

### Login Credentials

The system comes with pre-configured test users:

**Admin User:**
- Email: `admin@blog.com`
- Password: `password`
- Permissions: Can create, edit, delete any post

**Regular Users:**
- Email: `john@example.com` / Password: `password`
- Email: `jane@example.com` / Password: `password`
- Permissions: Can create posts and edit/delete their own posts

### Dashboard Features

1. **Login** - JWT-based authentication
2. **Post Management** - Create, edit, delete posts with rich media
3. **Pixabay Integration** - Search and add cover images/videos
4. **Search & Filter** - Find posts by title or author
5. **Pagination** - Browse through posts efficiently
6. **Public View** - Click post titles to view them publicly

### API Endpoints

#### Authentication
- `POST /api/auth/login` - Login and get JWT token

#### Posts
- `GET /api/posts` - List posts (with pagination, search, status filter)
- `GET /api/posts/{id}` - Get single post by ID
- `POST /api/posts` - Create new post (authenticated)
- `PUT /api/posts/{id}` - Update post (authenticated, owner/admin only)
- `DELETE /api/posts/{id}` - Soft delete post (authenticated, owner/admin only)

#### Media
- `GET /api/pixabay/search` - Search Pixabay for images/videos (authenticated)

### Public URLs

- `/` - Dashboard login page
- `/post/{slug}` - Public post view (SEO-friendly URLs)

## User Roles & Permissions

### Admin Users
- Can view, create, edit, and delete ANY post
- Full access to dashboard features
- Can manage posts from all authors

### Regular Users  
- Can view all posts
- Can create new posts
- Can edit and delete ONLY their own posts
- Cannot modify posts by other users

## Architecture & Design Decisions

### Security Features
- **JWT Authentication** - Secure token-based authentication
- **Password Hashing** - Using PHP's `password_hash()` with bcrypt
- **PDO Prepared Statements** - Protection against SQL injection
- **Input Validation** - Server-side validation of all inputs
- **Output Escaping** - XSS protection in templates
- **CORS Headers** - Proper cross-origin request handling
- **Environment Variables** - Sensitive data kept out of code

### Database Design
- **Soft Deletes** - Posts marked as deleted instead of actual deletion
- **Unique Slugs** - Auto-generated SEO-friendly URLs with collision handling
- **Foreign Keys** - Proper relational integrity
- **Indexes** - Performance optimization on commonly queried columns

### Code Structure
- **MVC Pattern** - Separation of concerns
- **Database Abstraction** - Custom Database class for common operations
- **JWT Helper** - Reusable token management
- **Middleware Pattern** - Authentication/authorization checks

## Libraries & Dependencies

- **No external PHP libraries** - Pure PHP implementation
- **JWT Implementation** - Custom lightweight JWT class
- **Pixabay API** - For media search and integration
- **Responsive CSS** - Modern CSS Grid and Flexbox
- **Vanilla JavaScript** - No frontend frameworks, pure JS for interactivity

## Known Limitations

1. **JWT Refresh** - No automatic token refresh (tokens expire after 24 hours)
2. **File Uploads** - Only supports Pixabay media, no direct file uploads
3. **Rich Text Editor** - Plain textarea for content editing
4. **Email Verification** - No email verification for new users
5. **User Management** - No admin interface for user management
6. **Comments** - No comment system implemented
7. **Categories/Tags** - No categorization system

## Future Enhancements

- Rich text editor (TinyMCE/CKEditor)
- File upload system for custom media
- User registration and email verification
- Admin user management interface
- Post categories and tags
- Comment system
- SEO meta tags management
- Social media sharing
- Post scheduling
- Analytics dashboard

## Troubleshooting

### Common Issues

1. **Database Connection Failed**
   - Check your `.env` database credentials
   - Ensure MySQL is running
   - Verify database exists

2. **JWT Token Errors**
   - Check that `JWT_SECRET` is set in `.env`
   - Ensure the secret is long and random

3. **Pixabay Search Not Working**
   - Verify your `PIXABAY_API_KEY` in `.env`
   - Check API key is valid at Pixabay
   - Ensure server can make outbound HTTP requests

4. **URL Rewriting Issues**
   - Enable Apache mod_rewrite
   - Check `.htaccess` file permissions
   - Verify Apache configuration allows .htaccess overrides

5. **Permission Denied Errors**
   - Check file permissions (644 for files, 755 for directories)
   - Ensure web server can read all files

### Debug Mode

To enable detailed error reporting, edit `config.php`:

```php
// For development only
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## License

This project is open source and available under the MIT License.

## Support

For issues and questions:
1. Check this README for common solutions
2. Review the code comments for implementation details
3. Test with the provided sample data and users