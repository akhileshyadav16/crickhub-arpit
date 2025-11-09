# 🚀 Vercel (Frontend) + Railway (Backend) Setup

## Architecture

- **Frontend**: Deployed on Vercel (static HTML/CSS/JS)
- **Backend**: Deployed on Railway (PHP API + MySQL)

---

## 📦 Step 1: Deploy Backend to Railway

### 1.1 Create Railway Project

1. Go to [railway.app](https://railway.app) and sign in
2. Click **"New Project"**
3. Select **"Deploy from GitHub repo"** → Choose your repository

### 1.2 Add MySQL Database

1. In Railway project, click **"+ New"**
2. Select **"Database"** → **"MySQL"**
3. Wait for MySQL to be ready (green status)
4. Note the connection details (you'll need them)

### 1.3 Deploy Backend Service

1. In same Railway project, click **"+ New"**
2. Select **"GitHub Repo"** → Choose your repository
3. Configure:
   - **Root Directory**: `backend`
   - **Start Command**: `php -S 0.0.0.0:$PORT public/index.php`
   - **Healthcheck Path**: `/api/health`

### 1.4 Set Environment Variables in Railway

Go to backend service → **"Variables"** tab → Add:

```env
CRICKHUB_DB_HOST=${{MySQL.MYSQLHOST}}
CRICKHUB_DB_PORT=${{MySQL.MYSQLPORT}}
CRICKHUB_DB_NAME=${{MySQL.MYSQLDATABASE}}
CRICKHUB_DB_USER=${{MySQL.MYSQLUSER}}
CRICKHUB_DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
CRICKHUB_DEBUG=false
CRICKHUB_ALLOWED_ORIGINS=https://crickhubarpit.vercel.app
```

**Important:** Replace `crickhubarpit.vercel.app` with your actual Vercel domain!

### 1.5 Run Database Migration

**Option A: Via Railway Dashboard**
1. Go to MySQL service → **"Connect"** tab
2. Click **"MySQL"** → Opens MySQL client
3. Copy contents of `backend/schema-mysql.sql`
4. Paste and execute in MySQL client

**Option B: Via Railway CLI**
```bash
npm i -g @railway/cli
railway login
railway link
railway connect mysql < backend/schema-mysql.sql
```

### 1.6 Get Backend URL

1. Go to backend service in Railway
2. Copy the URL (e.g., `https://crickhub-api-production.up.railway.app`)
3. **Save this URL** - you'll need it for Vercel configuration

---

## 📦 Step 2: Deploy Frontend to Vercel

### 2.1 Push Code to GitHub

```bash
git add .
git commit -m "Configure for Vercel frontend + Railway backend"
git push
```

### 2.2 Deploy on Vercel

1. Go to [vercel.com/new](https://vercel.com/new)
2. Import your GitHub repository
3. Configure:
   - **Framework Preset**: Other
   - **Root Directory**: `./` (root)
   - **Build Command**: `npm run build` (already configured)
   - **Output Directory**: `./` (root)
4. Click **"Deploy"**

### 2.3 Set Environment Variables in Vercel

**Critical Step!** Set the Railway backend URL:

1. Go to Vercel Dashboard → Your Project → **Settings** → **Environment Variables**
2. Click **"Add New"**
3. Add:
   - **Key**: `CRICKHUB_API_BASE`
   - **Value**: `https://your-backend.railway.app/api`
     - Replace `your-backend.railway.app` with your actual Railway backend URL
   - **Environment**: Select all (Production, Preview, Development)
4. Click **"Save"**

### 2.4 Redeploy Frontend

After adding the environment variable:
1. Go to **Deployments** tab
2. Click **⋯** (three dots) on latest deployment
3. Click **"Redeploy"**

Or push a new commit to trigger auto-deploy.

---

## ✅ Step 3: Verify Deployment

### Test Frontend
- URL: `https://crickhubarpit.vercel.app`
- Should load without errors

### Test Backend
- Health Check: `https://your-backend.railway.app/api/health`
- Should return: `{"status":"ok","database":"connected"}`

### Test Integration
1. Open frontend in browser
2. Open DevTools (F12) → Console
3. Should see: `[CrickHub] API Base URL: https://your-backend.railway.app/api`
4. No CORS errors
5. Data should load from database

---

## 🔧 Configuration Summary

### Vercel Environment Variables
```
CRICKHUB_API_BASE = https://your-backend.railway.app/api
```

### Railway Environment Variables
```
CRICKHUB_DB_HOST = ${{MySQL.MYSQLHOST}}
CRICKHUB_DB_PORT = ${{MySQL.MYSQLPORT}}
CRICKHUB_DB_NAME = ${{MySQL.MYSQLDATABASE}}
CRICKHUB_DB_USER = ${{MySQL.MYSQLUSER}}
CRICKHUB_DB_PASSWORD = ${{MySQL.MYSQLPASSWORD}}
CRICKHUB_DEBUG = false
CRICKHUB_ALLOWED_ORIGINS = https://crickhubarpit.vercel.app
```

---

## 🐛 Troubleshooting

### CORS Errors
**Fix:** Update `CRICKHUB_ALLOWED_ORIGINS` in Railway with your Vercel domain

### API Not Found
**Fix:** 
- Verify `CRICKHUB_API_BASE` is set in Vercel
- Check Railway backend is running
- Verify backend URL is correct

### Database Connection Failed
**Fix:**
- Check Railway MySQL service is running
- Verify environment variables in Railway
- Check database migration completed

---

## 📝 Quick Reference

**Frontend (Vercel):**
- Domain: `https://crickhubarpit.vercel.app`
- Build: Automatic on git push
- Config: Set `CRICKHUB_API_BASE` env var

**Backend (Railway):**
- Domain: `https://your-backend.railway.app`
- Database: Railway MySQL (managed)
- Config: Set via Railway Variables tab

---

**Made by: Arpit Yadav**

