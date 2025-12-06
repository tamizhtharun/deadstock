# TODO: Secure DB Connection Passwords for VPS Hosting

## Completed Tasks
- [x] Create .gitignore file to exclude .env file from version control
- [x] Create .env.example file with template for environment variables
- [x] Update db_connection.php to load DB credentials from .env file with fallback
- [x] Update config.php to load Razorpay keys from .env file with fallback
- [x] Create page_views table and run script to fix index page loading issue

## Next Steps for Deployment
- [ ] Copy .env.example to .env on VPS
- [ ] Update .env with actual production values (DB credentials, Razorpay keys)
- [ ] Ensure .env is not committed to repository (already in .gitignore)
- [ ] Run `php database/run_page_views_sql.php` on server (update DB credentials if needed)
- [ ] Test the application on VPS to verify connections work
