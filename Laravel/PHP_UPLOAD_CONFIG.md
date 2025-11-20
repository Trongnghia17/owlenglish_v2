# PHP Configuration for Large File Uploads

## Problem
The default PHP configuration has limits that are too small for uploading audio/video files:
- `upload_max_filesize` = 2M
- `post_max_size` = 8M

This causes `PostTooLargeException` errors when uploading files.

## Solution
A custom `php-dev.ini` configuration file has been created with increased limits:
- `upload_max_filesize` = 100M
- `post_max_size` = 100M
- `max_execution_time` = 300 seconds
- `memory_limit` = 256M

## Usage

### Development Server
Start the Laravel development server with the custom configuration:

```bash
cd Laravel
php -c php-dev.ini artisan serve
```

### Production
For production environments, update the system php.ini file or use .htaccess/.user.ini files depending on your server configuration.

For Apache with PHP-FPM, the `.user.ini` file in the `public/` directory will be used automatically.

## Notes
- The custom php-dev.ini is for local development only
- For production, configure your web server's PHP settings appropriately
- Adjust the limits based on your actual file size requirements
