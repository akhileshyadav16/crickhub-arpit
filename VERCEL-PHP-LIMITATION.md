# ⚠️ Vercel PHP Limitation

## Problem

Vercel **does not support PHP** as a serverless function runtime. The `@vercel/php` package doesn't exist.

## ✅ Solution: Use Railway for Backend

Since you want both frontend and backend deployed, here's the recommended setup:

### Frontend → Vercel ✅
- Static HTML/CSS/JS files
- Works perfectly on Vercel

### Backend → Railway ✅  
- PHP API with MySQL
- Native PHP support
- Free tier available

## Quick Setup

### 1. Deploy Backend to Railway

1. Go to [railway.app](https://railway.app)
2. Create new project → Add MySQL database
3. Add backend service:
   - Root Directory: `backend`
   - Start Command: `php -S 0.0.0.0:$PORT public/index.php`
4. Set environment variables (see Railway setup guide)
5. Get your Railway backend URL: `https://your-backend.railway.app`

### 2. Update Frontend API URL

**Option A: Set in Vercel Environment Variables**

1. Vercel Dashboard → Settings → Environment Variables
2. Add: `CRICKHUB_API_BASE = https://your-backend.railway.app/api`
3. The build script will inject this into HTML

**Option B: Update `scripts/data.js` directly**

```javascript
// In scripts/data.js, update the fallback:
apiBase = 'https://your-backend.railway.app/api';
```

### 3. Redeploy Frontend

```bash
git push
```

Vercel will auto-deploy with the Railway backend URL.

---

## Why This Works Better

- ✅ **Railway**: Full PHP support, MySQL included, better performance
- ✅ **Vercel**: Perfect for static frontend, fast CDN
- ✅ **Separation**: Backend and frontend can scale independently

---

**Made by: Arpit Yadav**

