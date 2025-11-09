# 🚀 CrickHub Deployment Guide - Vercel

This guide covers deploying CrickHub frontend and backend on Vercel.

## 📋 Overview

**CrickHub Architecture:**
- **Frontend**: HTML/CSS/JS (static files) → Deploy on Vercel ✅
- **Backend**: PHP API with MySQL → Options below

## 🎯 Deployment Options

### Option 1: Frontend on Vercel + Backend on Railway/Render (Recommended)
- ✅ Best for PHP applications
- ✅ Full MySQL support
- ✅ Easy environment variable management
- ✅ Better performance for PHP

### Option 2: Everything on Vercel (PHP Runtime)
- ⚠️ Limited PHP support
- ⚠️ May require adjustments
- ✅ Single platform

### Option 3: Convert Backend to Node.js (Advanced)
- ✅ Full Vercel support
- ⚠️ Requires code conversion

---

## 📦 Option 1: Frontend on Vercel + Backend on Railway (Recommended)

### Step 1: Prepare Frontend for Vercel

1. **Create `vercel.json` in project root:**

```json
{
  "version": 2,
  "builds": [
    {
      "src": "index.html",
      "use": "@vercel/static"
    }
  ],
  "routes": [
    {
      "src": "/(.*)",
      "dest": "/$1"
    }
  ],
  "headers": [
    {
      "source": "/(.*)",
      "headers": [
        {
          "key": "Access-Control-Allow-Origin",
          "value": "*"
        }
      ]
    }
  ]
}
```

2. **Update API base URL for production:**

Edit `scripts/data.js`:

```javascript
window.CRICKHUB_CONFIG = Object.assign(
    {
        // Use environment variable or default to production backend
        apiBase: window.CRICKHUB_API_BASE || 'https://your-backend.railway.app/api',
        useMockData: false,
    },
    window.CRICKHUB_CONFIG || {}
);
```

3. **Create `.vercelignore` (optional):**

```
backend/
*.md
.env
node_modules/
```

### Step 2: Deploy Frontend to Vercel

**Method A: Using Vercel CLI**

```bash
# Install Vercel CLI
npm i -g vercel

# Login to Vercel
vercel login

# Deploy (from project root)
vercel

# Follow prompts:
# - Set up and deploy? Yes
# - Which scope? (select your account)
# - Link to existing project? No
# - Project name? crickhub-frontend
# - Directory? ./
# - Override settings? No

# Deploy to production
vercel --prod
```

**Method B: Using Vercel Dashboard**

1. Go to [vercel.com](https://vercel.com) and sign in
2. Click "Add New Project"
3. Import your Git repository (GitHub/GitLab/Bitbucket)
4. Configure:
   - **Framework Preset**: Other
   - **Root Directory**: `./` (root)
   - **Build Command**: (leave empty)
   - **Output Directory**: `./` (root)
5. Add Environment Variables (if needed):
   - `CRICKHUB_API_BASE` = `https://your-backend.railway.app/api`
6. Click "Deploy"

### Step 3: Deploy Backend to Railway

**Why Railway?**
- ✅ Native PHP support
- ✅ MySQL database included
- ✅ Easy environment variables
- ✅ Free tier available

**Steps:**

1. **Sign up at [railway.app](https://railway.app)**

2. **Create a new project:**
   - Click "New Project"
   - Select "Deploy from GitHub repo" (or "Empty Project)

2. **Add MySQL Database:**
   - Click "+ New" → "Database" → "MySQL"
   - Railway will create a MySQL instance
   - Note the connection details

3. **Deploy Backend:**
   - Click "+ New" → "GitHub Repo" → Select your repo
   - Or "Empty Project" → "Deploy from GitHub"
   - Set **Root Directory**: `backend`
   - Set **Start Command**: `php -S 0.0.0.0:$PORT public/index.php`

4. **Configure Environment Variables:**
   - Go to your service → "Variables"
   - Add these variables:

```
CRICKHUB_DB_HOST=<railway-mysql-host>
CRICKHUB_DB_PORT=3306
CRICKHUB_DB_NAME=railway
CRICKHUB_DB_USER=root
CRICKHUB_DB_PASSWORD=<railway-mysql-password>
CRICKHUB_DEBUG=false
CRICKHUB_ALLOWED_ORIGINS=https://your-frontend.vercel.app
```

5. **Run Database Migration:**
   - Go to Railway MySQL service → "Connect" → "MySQL"
   - Or use Railway CLI:
   ```bash
   railway connect mysql
   mysql -h <host> -u root -p < backend/schema-mysql.sql
   ```

6. **Get Backend URL:**
   - Railway will provide a URL like: `https://crickhub-backend.railway.app`
   - Update your frontend's `apiBase` to: `https://crickhub-backend.railway.app/api`

### Step 4: Update Frontend API URL

1. **Update `scripts/data.js` with production backend URL:**

```javascript
window.CRICKHUB_CONFIG = Object.assign(
    {
        apiBase: 'https://your-backend.railway.app/api',
        useMockData: false,
    },
    window.CRICKHUB_CONFIG || {}
);
```

2. **Redeploy frontend:**
```bash
vercel --prod
```

---

## 📦 Option 2: Everything on Vercel (PHP Runtime)

### Step 1: Create Vercel Configuration

**Create `vercel.json` in project root:**

```json
{
  "version": 2,
  "builds": [
    {
      "src": "backend/public/index.php",
      "use": "@vercel/php"
    },
    {
      "src": "index.html",
      "use": "@vercel/static"
    }
  ],
  "routes": [
    {
      "src": "/api/(.*)",
      "dest": "/backend/public/index.php"
    },
    {
      "src": "/(.*)",
      "dest": "/$1"
    }
  ],
  "env": {
    "CRICKHUB_DB_HOST": "@crickhub_db_host",
    "CRICKHUB_DB_PORT": "@crickhub_db_port",
    "CRICKHUB_DB_NAME": "@crickhub_db_name",
    "CRICKHUB_DB_USER": "@crickhub_db_user",
    "CRICKHUB_DB_PASSWORD": "@crickhub_db_password"
  }
}
```

### Step 2: Adjust Backend for Vercel

**Update `backend/public/index.php` to handle Vercel's routing:**

```php
<?php
// At the top of index.php, add:
$_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? '/';
if (strpos($_SERVER['REQUEST_URI'], '/api/') === 0) {
    $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 4);
}
// ... rest of your code
```

### Step 3: Set Up MySQL Database

**Option A: Use PlanetScale (Recommended for Vercel)**
1. Sign up at [planetscale.com](https://planetscale.com)
2. Create a database
3. Get connection string
4. Add to Vercel environment variables

**Option B: Use Railway MySQL (External)**
1. Create MySQL on Railway (as in Option 1)
2. Add connection details to Vercel

### Step 4: Configure Environment Variables in Vercel

1. Go to Vercel Dashboard → Your Project → Settings → Environment Variables
2. Add:

```
CRICKHUB_DB_HOST=your-mysql-host
CRICKHUB_DB_PORT=3306
CRICKHUB_DB_NAME=your-database
CRICKHUB_DB_USER=your-username
CRICKHUB_DB_PASSWORD=your-password
CRICKHUB_DEBUG=false
CRICKHUB_ALLOWED_ORIGINS=https://your-project.vercel.app
```

### Step 5: Deploy

```bash
vercel --prod
```

**Note:** Vercel's PHP runtime has limitations. If you encounter issues, use Option 1.

---

## 📦 Option 3: Convert Backend to Node.js (Advanced)

This requires rewriting the PHP backend in Node.js. This is a significant change and not recommended unless you're comfortable with Node.js.

---

## 🔧 Post-Deployment Steps

### 1. Update CORS Settings

**In `backend/config.php` or via environment variables:**

```php
'allowed_origins' => [
    'https://your-frontend.vercel.app',
    'https://your-project.vercel.app'
]
```

### 2. Run Database Migration

**On Railway:**
```bash
railway connect mysql
mysql -h <host> -u root -p < backend/schema-mysql.sql
```

**Or via Railway Dashboard:**
- Go to MySQL service → "Connect" → Copy connection string
- Use MySQL client to import schema

### 3. Seed Initial Data (Optional)

```bash
# On Railway, connect and run:
railway run php backend/seed-data.php
```

### 4. Test Deployment

1. **Frontend:** `https://your-project.vercel.app`
2. **Backend Health:** `https://your-backend.railway.app/api/health`
3. **Test Login:** Use admin credentials

---

## 🔐 Security Checklist

- [ ] Set `CRICKHUB_DEBUG=false` in production
- [ ] Use strong database passwords
- [ ] Configure CORS to only allow your frontend domain
- [ ] Enable HTTPS (automatic on Vercel/Railway)
- [ ] Use environment variables for all secrets
- [ ] Don't commit `.env` files to Git

---

## 🐛 Troubleshooting

### Issue: CORS Errors

**Solution:**
- Update `CRICKHUB_ALLOWED_ORIGINS` with your Vercel domain
- Check backend CORS headers in `backend/public/index.php`

### Issue: Database Connection Failed

**Solution:**
- Verify environment variables are set correctly
- Check database host allows external connections
- For Railway: Ensure MySQL service is running
- Check firewall/security groups

### Issue: API Routes Not Working

**Solution:**
- Verify `vercel.json` routing configuration
- Check backend URL in frontend `scripts/data.js`
- Review Vercel function logs

### Issue: Session Not Persisting

**Solution:**
- Vercel uses serverless functions (stateless)
- Consider using JWT tokens instead of sessions
- Or use external session storage (Redis)

---

## 📚 Additional Resources

- [Vercel Documentation](https://vercel.com/docs)
- [Railway Documentation](https://docs.railway.app)
- [Vercel PHP Runtime](https://vercel.com/docs/runtimes/php)
- [PlanetScale MySQL](https://planetscale.com/docs)

---

## ✅ Recommended Setup Summary

**For Production:**
1. ✅ Frontend → Vercel (static hosting)
2. ✅ Backend → Railway (PHP + MySQL)
3. ✅ Database → Railway MySQL (or PlanetScale)
4. ✅ Environment Variables → Set in both platforms
5. ✅ CORS → Configure for your frontend domain

**Quick Deploy Commands:**

```bash
# Frontend
vercel --prod

# Backend (on Railway, via dashboard or CLI)
railway up
```

---

**Made by: Arpit Yadav**

