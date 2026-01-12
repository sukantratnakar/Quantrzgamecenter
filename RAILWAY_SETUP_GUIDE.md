# Railway Setup Guide for Quantraz Game Center
## Complete Migration from Hostinger to Railway (Non-Technical Guide)

---

## Step 1: Create Railway Account

1. Go to https://railway.app
2. Click **"Login"**
3. Choose **"Login with GitHub"**
4. Authorize Railway to access your GitHub account

---

## Step 2: Create a New Project

1. Once logged in, click **"New Project"**
2. You'll see an empty project dashboard

---

## Step 3: Add MySQL Database

1. In your project, click **"+ New"**
2. Select **"Database"**
3. Click **"Add MySQL"**
4. Wait for the database to be created (takes about 30 seconds)

### Save Your Database Credentials

1. Click on the **MySQL** service in your project
2. Click on the **"Variables"** tab
3. You'll see these values - **COPY THEM TO A NOTEPAD**:
   - `MYSQL_HOST` (example: `containers-us-west-123.railway.app`)
   - `MYSQL_PORT` (usually `3306`)
   - `MYSQL_DATABASE` (example: `railway`)
   - `MYSQL_USER` (example: `root`)
   - `MYSQL_PASSWORD` (a long random password)

---

## Step 4: Import Your Database

### Option A: Using Railway's Built-in Query Tool (Easiest)

1. In your MySQL service, click on the **"Data"** tab
2. Click **"Query"**
3. Open the file `u755186149_test.20260104192759.sql` from your computer with a text editor
4. Copy ALL the contents
5. Paste into Railway's query box
6. Click **"Run"** or **"Execute"**
7. You should see "Query executed successfully"

### Option B: Using TablePlus (Recommended if Option A doesn't work)

1. Download TablePlus from https://tableplus.com (free trial is fine)
2. Open TablePlus
3. Click **"Create a new connection"**
4. Choose **"MySQL"**
5. Enter the credentials you saved earlier:
   - **Host:** Your `MYSQL_HOST`
   - **Port:** `3306`
   - **User:** Your `MYSQL_USER`
   - **Password:** Your `MYSQL_PASSWORD`
   - **Database:** Your `MYSQL_DATABASE`
6. Click **"Test"** to verify connection
7. Click **"Connect"**
8. Once connected, go to **File** → **Import** → **From SQL Dump**
9. Select the file: `u755186149_test.20260104192759.sql`
10. Click **"Import"**
11. Wait for completion

---

## Step 5: Deploy Your Application

1. Go back to your Railway project dashboard
2. Click **"+ New"**
3. Select **"GitHub Repo"**
4. Find and select: **`sukantratnakar/Quantrzgamecenter`**
5. Railway will automatically start building and deploying

---

## Step 6: Configure Environment Variables

1. Click on your **application service** (not the MySQL one)
2. Click on the **"Variables"** tab
3. Click **"+ New Variable"** and add these **ONE BY ONE**:

### Required Variables (Copy from your MySQL service):
```
Variable Name: DB_HOST
Value: [Paste your MYSQL_HOST here]

Variable Name: DB_PORT
Value: 3306

Variable Name: DB_NAME
Value: [Paste your MYSQL_DATABASE here]

Variable Name: DB_USER
Value: [Paste your MYSQL_USER here]

Variable Name: DB_PASSWORD
Value: [Paste your MYSQL_PASSWORD here]
```

### Optional Variables:
```
Variable Name: APP_ENV
Value: production

Variable Name: APP_DEBUG
Value: false
```

4. After adding all variables, Railway will automatically redeploy

---

## Step 7: Get Your Website URL

1. Click on your **application service**
2. Click on the **"Settings"** tab
3. Scroll to **"Networking"** section
4. Click **"Generate Domain"**
5. Railway will give you a URL like: `your-app-name.up.railway.app`
6. Click on this URL to visit your website!

---

## Step 8: Add Custom Domain (Optional)

If you want to use your own domain (like `quantrazgamecenter.com`):

1. In your application service, go to **"Settings"** → **"Networking"**
2. Click **"Custom Domain"**
3. Enter your domain name
4. Railway will show you DNS records to add
5. Go to your domain registrar (like GoDaddy, Namecheap, etc.)
6. Add the DNS records Railway provided
7. Wait 24-48 hours for DNS to propagate

---

## Troubleshooting

### If your website shows an error:

1. **Check Deployment Logs:**
   - Click on your application service
   - Click **"Deployments"**
   - Click on the latest deployment
   - Check the logs for errors

2. **Common Issues:**
   - **"Application failed to respond"** - Wait a few minutes and refresh
   - **Database connection error** - Double-check all DB variables match your MySQL credentials
   - **500 Error** - Check that you imported the SQL file successfully

### If the database import fails:

1. Make sure you're copying the ENTIRE SQL file content
2. Try using TablePlus instead (Option B above)
3. The database should have a table called `users`

---

## Verifying Everything Works

1. Visit your Railway URL
2. You should see the Quantraz Game Center login/signup page
3. Try logging in with one of these test accounts from your database:
   - **Email:** `sukantratnakar@gmail.com`
   - **Password:** You'll need to use the "Forgot Password" feature OR create a new account

---

## What's Already Done For You

✅ Application code is on GitHub
✅ Docker configuration is ready
✅ Database file is prepared
✅ Environment variable support is configured

## What You Need To Do

1. ☐ Create Railway account
2. ☐ Create new project
3. ☐ Add MySQL database
4. ☐ Import SQL file to database
5. ☐ Deploy from GitHub
6. ☐ Add environment variables
7. ☐ Generate domain and visit website

---

## Need Help?

- Railway Documentation: https://docs.railway.app
- Railway Discord: https://discord.gg/railway
- Your GitHub Repository: https://github.com/sukantratnakar/Quantrzgamecenter

---

**Good luck with your deployment! 🚀**
