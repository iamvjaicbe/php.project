# PHP E-Commerce Website

## Quick Setup (5 minutes)

### XAMPP Installation
1. Download XAMPP (PHP 8.0+) from https://www.apachefriends.org/
2. Install and start Apache + MySQL
3. Extract this ZIP to `C:\xampp\htdocs\ecommerce\`

### Database Setup
1. Open http://localhost/phpmyadmin
2. Create database: `ecommerce_shop`
3. Import: `database_schema.sql`

### Access Your Store
- **Customer Site**: http://localhost/ecommerce
- **Admin Panel**: http://localhost/ecommerce/admin/login.php
- **Login**: admin / admin123

## Features Included

✅ **Customer Frontend**
- Responsive product catalog
- Shopping cart with live updates
- WhatsApp order sharing
- Category filtering
- Modern UI/UX

✅ **Admin Panel** 
- Secure login system
- Dashboard with statistics
- Product management
- Category management
- Shop settings

✅ **Security**
- Password hashing
- SQL injection prevention
- Input sanitization
- CSRF protection
- Secure file uploads

✅ **Database**
- Optimized MySQL schema
- Sample data included
- Foreign key relationships
- Performance indexes

## Configuration

Edit `config.php` to update:
- Database credentials
- Site URL
- Debug settings
- Upload limits

## Production Deployment

1. Upload files to web server
2. Import database schema
3. Update config.php with production settings
4. Set file permissions (755 for directories, 644 for files)
5. Enable HTTPS

## Support

Default admin: admin / admin123
Change this immediately after first login!

For issues, check the logs/ directory for error details.

## Requirements

- PHP 8.0+
- MySQL 5.7+
- Apache with mod_rewrite
- GD extension for images

**Built with security and performance in mind!**
