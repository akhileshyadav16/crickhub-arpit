# 🚀 Quick Deploy Guide - CrickHub on Vercel

## ⚡ Fastest Path to Production

### Prerequisites
- GitHub account
- Vercel account (free): [vercel.com](https://vercel.com)
- Railway account (free): [railway.app](https://railway.app)

---

## 📦 Step 1: Deploy Frontend to Vercel (5 minutes)

### Option A: Via Vercel Dashboard (Easiest)

1. **Push code to GitHub:**
   ```bash
   git init
   git add .
   git commit -m "Initial commit"
   git remote add origin https://github.com/yourusername/crickhub.git
   git push -u origin main
   ```

2. **Deploy on Vercel:**
   - Go to [vercel.com/new](https://vercel.com/new)
   - Import your GitHub repository
   - Configure:
     - **Framework Preset**: Other
     - **Root Directory**: `./` (leave default)
     - **Build Command**: (leave empty)
     - **Output Directory**: `./` (leave default)
   - Click **Deploy**

3. **Get your frontend URL:**
   - Vercel will provide: `https://your-project.vercel.app`
   - Copy this URL (you'll need it for backend CORS)

### Option B: Via Vercel CLI

```bash
# Install Vercel CLI
npm i -g vercel

# Login
vercel login

# Deploy
vercel

# Deploy to production
vercel --prod
```

---

## 🗄️ Step 2: Deploy Backend to Railway (10 minutes)

### 2.1 Create MySQL Database

1. **Go to [railway.app](https://railway.app)**
2. **Click "New Project"**
3. **Add MySQL Database:**
   - Click **"+ New"** → **"Database"** → **"MySQL"**
   - Railway creates a MySQL instance automatically
   - Wait for it to be ready (green status)

4. **Get Connection Details:**
   - Click on MySQL service
   - Go to **"Variables"** tab
   - Note these values:
     - `MYSQLHOST`
     - `MYSQLPORT` (usually 3306)
     - `MYSQLDATABASE`
     - `MYSQLUSER`
     - `MYSQLPASSWORD`

### 2.2 Deploy Backend API

1. **Add Backend Service:**
   - In same Railway project, click **"+ New"**
   - Select **"GitHub Repo"** → Choose your repository
   - Or **"Empty Project"** → **"Deploy from GitHub repo"**

2. **Configure Service:**
   - **Root Directory**: `backend`
   - **Start Command**: `php -S 0.0.0.0:$PORT public/index.php`
   - **Healthcheck Path**: `/api/health`

3. **Set Environment Variables:**
   - Go to backend service → **"Variables"** tab
   - Click **"Raw Editor"** and add:

```env
CRICKHUB_DB_HOST=${{MySQL.MYSQLHOST}}
CRICKHUB_DB_PORT=${{MySQL.MYSQLPORT}}
CRICKHUB_DB_NAME=${{MySQL.MYSQLDATABASE}}
CRICKHUB_DB_USER=${{MySQL.MYSQLUSER}}
CRICKHUB_DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
CRICKHUB_DEBUG=false
CRICKHUB_ALLOWED_ORIGINS=https://your-project.vercel.app
```

   **Replace `your-project.vercel.app` with your actual Vercel URL!**

4. **Deploy:**
   - Railway will automatically deploy
   - Wait for deployment to complete
   - Get your backend URL: `https://your-backend.railway.app`

### 2.3 Run Database Migration

**Option A: Via Railway Dashboard (Easiest)**

1. Go to MySQL service → **"Connect"** tab
2. Click **"MySQL"** → Opens MySQL client
3. Copy contents of `backend/schema-mysql.sql`
4. Paste and execute in MySQL client

**Option B: Via Railway CLI**

```bash
# Install Railway CLI
npm i -g @railway/cli

# Login
railway login

# Link to project
railway link

# Connect to MySQL and import schema
railway connect mysql < backend/schema-mysql.sql
```

**Option C: Via PHP Script (Recommended)**

1. **Temporarily add migration script:**
   - Railway → Backend service → **"Settings"** → **"Deploy"**
   - Add **"Deploy Command"**: `php migrate-schema.php && php -S 0.0.0.0:$PORT public/index.php`
   - Redeploy
   - After first deploy, remove the migration command

### 2.4 Seed Sample Data (Optional)

```bash
# Via Railway CLI
railway run php seed-data.php
```

---

## 🔗 Step 3: Connect Frontend to Backend

### 3.1 Update Frontend API URL

**Edit `scripts/data.js`:**

```javascript
window.CRICKHUB_CONFIG = Object.assign(
    {
        // Replace with your Railway backend URL
        apiBase: 'https://your-backend.railway.app/api',
        useMockData: false,
    },
    window.CRICKHUB_CONFIG || {}
);
```

### 3.2 Redeploy Frontend

```bash
# Commit and push changes
git add scripts/data.js
git commit -m "Update API URL for production"
git push

# Vercel will auto-deploy, or:
vercel --prod
```

---

## ✅ Step 4: Test Your Deployment

1. **Frontend:** `https://your-project.vercel.app`
2. **Backend Health:** `https://your-backend.railway.app/api/health`
3. **Admin Login:**
   - URL: `https://your-project.vercel.app/admin.html`
   - Email: `admin@crickhub.local`
   - Password: `admin123`

---

## 🔐 Security Checklist

- [ ] Set `CRICKHUB_DEBUG=false` in Railway
- [ ] Update `CRICKHUB_ALLOWED_ORIGINS` with your Vercel domain
- [ ] Use strong database password (Railway generates this)
- [ ] Don't commit `.env` files to Git

---

## 🐛 Common Issues

### CORS Errors
**Fix:** Update `CRICKHUB_ALLOWED_ORIGINS` in Railway with your Vercel URL

### Database Connection Failed
**Fix:** 
- Verify environment variables in Railway
- Check MySQL service is running
- Ensure variables use `${{MySQL.VARIABLE}}` syntax

### API Not Found (404)
**Fix:**
- Verify backend URL in `scripts/data.js`
- Check Railway deployment logs
- Ensure backend service is running

---

## 📊 Monitoring

- **Vercel:** Dashboard shows frontend deployments and analytics
- **Railway:** Dashboard shows backend logs and metrics
- **Backend Health:** `https://your-backend.railway.app/api/health`

---

## 🎉 You're Live!

Your CrickHub app is now deployed:
- ✅ Frontend: `https://your-project.vercel.app`
- ✅ Backend: `https://your-backend.railway.app/api`
- ✅ Database: Railway MySQL (managed)

**Made by: Arpit Yadav**

