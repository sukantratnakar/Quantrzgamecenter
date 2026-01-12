# Quantraz Game Center - Railway Deployment Guide

## Prerequisites
- GitHub account
- Railway account (sign up at https://railway.app)
- Your code pushed to GitHub repository

## Step 1: Set Up MySQL Database on Railway

1. Go to https://railway.app and log in
2. Create a new project
3. Click "New" → "Database" → "Add MySQL"
4. Railway will provision a MySQL database
5. Note down the following connection details from the MySQL service:
   - `MYSQL_HOST`
   - `MYSQL_PORT`
   - `MYSQL_USER`
   - `MYSQL_PASSWORD`
   - `MYSQL_DATABASE`

## Step 2: Import Database Schema

1. In your Railway MySQL database, go to the "Data" tab
2. Click "Query" and paste the contents of `u755186149_test.20260104192759.sql`
3. Execute the query to create tables and import initial data
4. Alternatively, use a MySQL client like TablePlus or MySQL Workbench:
   ```bash
   mysql -h <MYSQL_HOST> -P <MYSQL_PORT> -u <MYSQL_USER> -p<MYSQL_PASSWORD> <MYSQL_DATABASE> < u755186149_test.20260104192759.sql
   ```

## Step 3: Deploy Application to Railway

1. In Railway, click "New" → "GitHub Repo"
2. Select your repository: `sukantratnakar/Quantrzgamecenter`
3. Railway will detect the Dockerfile and build automatically

## Step 4: Configure Environment Variables

In your Railway service settings, add the following environment variables:

### Database Configuration
- `DB_HOST` = Your Railway MySQL host (from Step 1)
- `DB_PORT` = Your Railway MySQL port (usually 3306)
- `DB_NAME` = Your Railway MySQL database name
- `DB_USER` = Your Railway MySQL username
- `DB_PASSWORD` = Your Railway MySQL password

### Application Configuration
- `APP_ENV` = `production`
- `APP_DEBUG` = `false`

### Email Configuration (Optional - for PHPMailer)
- `MAIL_HOST` = `smtp.gmail.com`
- `MAIL_PORT` = `587`
- `MAIL_USERNAME` = Your email
- `MAIL_PASSWORD` = Your app password
- `MAIL_FROM_ADDRESS` = `noreply@quantrazgamecenter.com`
- `MAIL_FROM_NAME` = `Quantraz Game Center`

## Step 5: Deploy and Test

1. Railway will automatically deploy after adding environment variables
2. Once deployment is complete, Railway will provide a public URL
3. Visit the URL to test your application
4. You can add a custom domain in Railway settings if needed

## Troubleshooting

### Database Connection Issues
- Verify environment variables are set correctly
- Check MySQL service is running in Railway
- Ensure database schema is imported

### Application Errors
- Check Railway deployment logs
- Verify all PHP files are uploaded
- Ensure Composer dependencies are installed

### Session Issues
- Railway provides persistent storage by default
- Sessions should work out of the box

## Connecting MySQL Database to Railway Service

Railway automatically connects services in the same project. Make sure:
1. Both MySQL and your application are in the same Railway project
2. Use the MySQL connection details provided by Railway
3. The database service is running before deploying the application

## Post-Deployment

### Custom Domain
1. Go to your Railway service settings
2. Click "Settings" → "Domains"
3. Add your custom domain
4. Update DNS records as instructed

### Monitoring
- Check Railway logs for any errors
- Monitor resource usage in Railway dashboard
- Set up notifications for deployment failures

## Migration from Hostinger Checklist

- [x] Database schema exported
- [x] Files uploaded to GitHub
- [x] Environment variables configured
- [ ] Database imported to Railway MySQL
- [ ] Application deployed and tested
- [ ] Custom domain configured (optional)
- [ ] Email service configured (if using email features)
- [ ] SSL certificate active (Railway provides this automatically)

## Important Notes

1. **Database Password**: The old Hostinger database password is still in the code as a fallback. Once Railway is confirmed working, you should remove it for security.

2. **File Uploads**: If your application handles file uploads, ensure you have Railway's persistent storage configured or use external storage like AWS S3.

3. **Environment Variables**: Never commit `.env` file to Git. Always use Railway's environment variable settings.

4. **Vendor Directory**: The `vendor/` directory is in `.gitignore` and will be installed during Docker build via Composer.

## Support

For Railway-specific issues: https://railway.app/help
For application issues: Check your GitHub repository issues
