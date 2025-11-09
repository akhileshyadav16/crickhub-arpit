# CrickHub - Cricket Analytics Portal

CrickHub is a PHP/MySQL-powered cricket analytics portal with an HTML/CSS/JS frontend. This project provides:

- **Public Dashboard** (`index.html`) - Player insights, match summaries, and interactive Chart.js visualizations
- **Admin Console** (`admin.html`) - AJAX-driven CRUD management for players, teams, and matches
- **REST API** (`backend/public/index.php`) - PHP backend with session-based authentication (admin and viewer roles)

## 🚀 Quick Start Guide

Follow these steps to get CrickHub up and running:

## 📋 Prerequisites

Before starting, ensure you have:

- **PHP 8.1+** with `pdo_mysql` extension enabled
- **MySQL 5.7+** / MariaDB 10.3+ / **TiDB Cloud** (cloud MySQL)
- **Web Server** (XAMPP, WAMP, or standalone PHP)
- **Node.js** (optional, for `live-server` or similar static server)

### Verify MySQL Extension

**Check if MySQL driver is available:**
```bash
php -m | findstr mysql
```

You should see `pdo_mysql` and `mysqli` in the output.

**If not available:**
1. Open `C:\xampp\php\php.ini` in a text editor (as Administrator)
2. Find and uncomment: `extension=pdo_mysql` and `extension=mysqli`
3. Save the file and restart PHP server

## 📦 Step-by-Step Setup

### Step 1: Verify PHP MySQL Extension

**Windows (XAMPP):**
```bash
php -m | findstr mysql
```

You should see `pdo_mysql` in the output.

**If not available:**
1. Open `C:\xampp\php\php.ini` (as Administrator)
2. Find and uncomment: `extension=pdo_mysql`
3. Save and restart your web server

### Step 2: Database Setup

**Option A: Local MySQL**

1. **Create database:**
```sql
mysql -u root -p
CREATE DATABASE crickhub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

2. **Import schema:**
```bash
mysql -u root -p crickhub < backend/schema-mysql.sql
```

**Option B: TiDB Cloud (Cloud MySQL)**

1. Sign up at [TiDB Cloud](https://tidbcloud.com)
2. Create a cluster and get your connection string
3. Skip to Step 3 (schema will be imported automatically)

### Step 3: Configure Environment

Create `backend/.env` file:

**For TiDB Cloud (Recommended):**
```bash
DATABASE_URL=mysql://username:password@host.tidbcloud.com:4000/database
CRICKHUB_DEBUG=true
CRICKHUB_ALLOWED_ORIGINS=*
```

**For Local MySQL:**
```bash
CRICKHUB_DB_HOST=127.0.0.1
CRICKHUB_DB_PORT=3306
CRICKHUB_DB_NAME=crickhub
CRICKHUB_DB_USER=root
CRICKHUB_DB_PASSWORD=
CRICKHUB_DEBUG=true
CRICKHUB_ALLOWED_ORIGINS=*
```

### Step 4: Run Database Migration

**Automated Migration (Recommended):**
```bash
cd backend
php migrate-schema.php
```

This will:
- ✅ Connect to your database
- ✅ Create all tables (users, teams, players, matches)
- ✅ Create indexes
- ✅ Seed default admin and viewer users

**Manual Migration:**
```bash
mysql -u root -p crickhub < backend/schema-mysql.sql
```

### Step 5: Seed Sample Data (Optional)

To populate the database with sample teams, players, and matches:

```bash
cd backend
php seed-data.php
```

This adds:
- 10 IPL teams
- 20 star players with statistics
- 15 matches (10 completed + 5 scheduled)

### Step 6: Start Backend Server

**Option 1: With Connection Test (Recommended):**
```bash
cd backend
php start-server.php
```

This tests your database connection before starting the server.

**Option 2: Direct Start:**
```bash
cd backend
php -S localhost:8000 public/index.php
```

**Verify Backend is running:**
- Open browser: `http://localhost:8000/api/health`
- Should see: `{"status":"ok","database":"connected",...}`

### Step 7: Start Frontend Server

**Option 1: Using live-server (Recommended):**
```bash
npx live-server --port=8001
```

**Option 2: Using Python:**
```bash
python -m http.server 8001
```

**Option 3: Using PHP:**
```bash
php -S localhost:8001
```

**Option 4: Using VS Code:**
- Install "Live Server" extension
- Right-click `index.html` → "Open with Live Server"

### Step 8: Access the Application

**Public Dashboard:**
- URL: `http://localhost:8001/index.html`
- Features: View players, matches, and performance charts

**Admin Panel:**
- URL: `http://localhost:8001/admin.html`
- Login: `admin@crickhub.local` / `admin123`
- Features: Manage players, teams, and matches

**Viewer Access:**
- Login: `viewer@crickhub.local` / `viewer123`
- Can view data but cannot edit/delete

## 🔧 Configuration Details

### Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `DATABASE_URL` | MySQL connection string (takes priority) | - |
| `CRICKHUB_DB_HOST` | Database host | `127.0.0.1` |
| `CRICKHUB_DB_PORT` | Database port | `3306` |
| `CRICKHUB_DB_NAME` | Database name | `crickhub` |
| `CRICKHUB_DB_USER` | Database username | `root` |
| `CRICKHUB_DB_PASSWORD` | Database password | (empty) |
| `CRICKHUB_DEBUG` | Enable debug mode | `true` |
| `CRICKHUB_ALLOWED_ORIGINS` | CORS allowed origins (comma-separated) | `*` |

### Testing Database Connection

**Test your database connection:**
```bash
cd backend
php test-db.php
```

This will:
- ✅ Check if MySQL PDO driver is available
- ✅ Test connection to your database
- ✅ Verify all required tables exist
- ✅ Show connection details

### Frontend Configuration

Edit `scripts/data.js` to configure API endpoint:

```javascript
window.CRICKHUB_CONFIG = {
    apiBase: 'http://localhost:8000/api',  // Backend API URL
    useMockData: false                     // Set to true to use mock data
};
```

## 🎯 Complete Startup Sequence

### Quick Start (All Steps)

```bash
# 1. Navigate to project directory
cd "A:\Code\Web devlopment\Bakwas\cricbuzz"

# 2. Configure database (create backend/.env file)
# See Step 3 above for .env content

# 3. Run database migration
cd backend
php migrate-schema.php

# 4. (Optional) Seed sample data
php seed-data.php

# 5. Start backend server (in one terminal)
php start-server.php
# Server runs at: http://localhost:8000

# 6. Start frontend server (in another terminal)
cd ..
npx live-server --port=8001
# Frontend runs at: http://localhost:8001
```

### Verification Checklist

After starting both servers, verify:

- [ ] Backend health check: `http://localhost:8000/api/health` returns `{"status":"ok","database":"connected"}`
- [ ] Frontend loads: `http://localhost:8001/index.html` shows the dashboard
- [ ] Admin panel loads: `http://localhost:8001/admin.html` shows login option
- [ ] Can login: Use `admin@crickhub.local` / `admin123`
- [ ] Data displays: Players, teams, and matches are visible
- [ ] Charts work: Performance Insights section shows charts

## 👤 Default User Accounts

After running the migration, these accounts are available:

| Email | Password | Role | Permissions |
|-------|----------|------|-------------|
| `admin@crickhub.local` | `admin123` | Admin | Full CRUD access |
| `viewer@crickhub.local` | `viewer123` | Viewer | Read-only access |

> ⚠️ **Security Note:** Change these passwords in production!

## 🎨 Application Features

### Public Dashboard (`index.html`)

- **Player Statistics**: Browse players with filters by team and search
- **Match Information**: View recent matches and results
- **Performance Insights**: Interactive charts showing:
  - Runs vs. Batting Average (line chart)
  - Centuries and Fifties (bar chart)
- **Player Details**: Click "View Details" to see full player profile

### Admin Panel (`admin.html`)

- **Authentication**: Login/logout with role-based access
- **Players Management**: Add, edit, delete players with full statistics
- **Teams Management**: Manage team information (name, city, coach, captain)
- **Matches Management**: Schedule and update match information
- **Empty States**: Helpful messages when no data exists
- **Login Prompts**: Guidance for unauthenticated users

## 🔍 Troubleshooting

### Backend Issues

**"Database connection failed"**
- Check `.env` file exists and has correct credentials
- Verify database server is running
- Test connection: `php backend/test-db.php`

**"MySQL PDO driver not found"**
- Enable `extension=pdo_mysql` in `php.ini`
- Restart PHP server

**"Network error: Unable to reach the server"**
- Ensure backend is running: `php -S localhost:8000 public/index.php`
- Check firewall isn't blocking port 8000
- Verify frontend is served via HTTP (not `file://`)

### Frontend Issues

**"API base URL not configured"**
- Check `scripts/data.js` has `apiBase: 'http://localhost:8000/api'`
- Ensure backend server is running

**"Charts not displaying"**
- Check browser console for Chart.js errors
- Verify Chart.js CDN is accessible
- Check that player data exists

**"Empty states showing when data exists"**
- Check browser console for API errors
- Verify CORS is configured correctly
- Check network tab for failed requests

### Common Solutions

1. **Clear browser cache**: `Ctrl+Shift+R` (Windows) or `Cmd+Shift+R` (Mac)
2. **Check both servers are running**: Backend (8000) and Frontend (8001)
3. **Verify database connection**: Run `php backend/test-db.php`
4. **Check console errors**: Open browser DevTools (F12) for detailed errors

## 📡 API Endpoints

### Authentication
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|----------------|
| `POST` | `/api/auth/login` | Login with email/password | No |
| `POST` | `/api/auth/logout` | Logout current session | No |
| `GET` | `/api/auth/me` | Get current user info | No |

### Players
| Method | Endpoint | Description | Role |
|--------|----------|-------------|------|
| `GET` | `/api/players` | List all players | Any |
| `GET` | `/api/players/{id}` | Get player details | Any |
| `POST` | `/api/players` | Create new player | Admin |
| `PUT` | `/api/players/{id}` | Update player | Admin |
| `PATCH` | `/api/players/{id}` | Partial update player | Admin |
| `DELETE` | `/api/players/{id}` | Delete player | Admin |

### Teams
| Method | Endpoint | Description | Role |
|--------|----------|-------------|------|
| `GET` | `/api/teams` | List all teams | Any |
| `GET` | `/api/teams/{id}` | Get team details | Any |
| `POST` | `/api/teams` | Create new team | Admin |
| `PUT` | `/api/teams/{id}` | Update team | Admin |
| `PATCH` | `/api/teams/{id}` | Partial update team | Admin |
| `DELETE` | `/api/teams/{id}` | Delete team | Admin |

### Matches
| Method | Endpoint | Description | Role |
|--------|----------|-------------|------|
| `GET` | `/api/matches` | List all matches | Any |
| `GET` | `/api/matches/{id}` | Get match details | Any |
| `POST` | `/api/matches` | Create new match | Admin |
| `PUT` | `/api/matches/{id}` | Update match | Admin |
| `PATCH` | `/api/matches/{id}` | Partial update match | Admin |
| `DELETE` | `/api/matches/{id}` | Delete match | Admin |

### Health Check
| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/health` | Check API and database status |

**All responses are JSON.** POST/PUT bodies accept JSON payloads matching the database schema.

## 📝 Project Structure

```
cricbuzz/
├── index.html              # Public dashboard
├── admin.html              # Admin panel
├── scripts/
│   ├── data.js            # Configuration & mock data
│   ├── main.js            # Public dashboard logic
│   └── admin.js           # Admin panel logic
├── styles/
│   ├── main.css           # Public dashboard styles
│   └── admin.css         # Admin panel styles
├── backend/
│   ├── .env               # Environment variables (create this)
│   ├── config.php         # Application configuration
│   ├── bootstrap.php      # Application initialization
│   ├── db.php             # Database connection
│   ├── auth.php           # Authentication helpers
│   ├── helpers.php        # Utility functions
│   ├── schema-mysql.sql   # Database schema
│   ├── migrate-schema.php # Automated migration script
│   ├── seed-data.php      # Seed sample data
│   ├── test-db.php        # Test database connection
│   ├── start-server.php   # Start server with connection test
│   └── controllers/
│       ├── AuthController.php
│       ├── PlayerController.php
│       ├── TeamController.php
│       └── MatchController.php
└── README.md              # This file
```

## 🛠️ Development Workflow

### Making Changes

1. **Backend Changes**: Edit PHP files in `backend/` directory
2. **Frontend Changes**: Edit HTML/JS/CSS files in root directory
3. **Database Changes**: Update `backend/schema-mysql.sql` and re-run migration

### Testing

**Test Database Connection:**
```bash
cd backend
php test-db.php
```

**Test API Endpoints:**
```bash
# Health check
curl http://localhost:8000/api/health

# Get players
curl http://localhost:8000/api/players

# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@crickhub.local","password":"admin123"}' \
  -c cookies.txt
```

## 🚀 Deployment

### Production Checklist

- [ ] Change default passwords in database
- [ ] Set `CRICKHUB_DEBUG=false` in `.env`
- [ ] Configure `CRICKHUB_ALLOWED_ORIGINS` with specific domains
- [ ] Use HTTPS for both frontend and backend
- [ ] Set up proper database backups
- [ ] Configure PHP error logging
- [ ] Enable PHP opcache for performance

### Environment Variables for Production

```bash
DATABASE_URL=mysql://user:password@host:port/database
CRICKHUB_DEBUG=false
CRICKHUB_ALLOWED_ORIGINS=https://yourdomain.com,https://www.yourdomain.com
```

## 📚 Additional Resources

- **Database Migration**: See `backend/MIGRATE-SCHEMA.md` for detailed migration guide
- **API Testing**: Use `test-api.html` to test API endpoints
- **Connection Issues**: Run `php backend/test-db.php` for diagnostics

## 🎯 Next Steps

- Replace mock data with live integrations or scheduled ETL scripts
- Add pagination and richer filtering for high-volume datasets
- Integrate with Laravel or another framework if you need advanced routing or ORM features
- Add more chart types and analytics
- Implement data export functionality
- Add user management features

## 📞 Support

If you encounter issues:

1. Check the **Troubleshooting** section above
2. Review browser console (F12) for errors
3. Check backend logs (PHP error log)
4. Verify database connection with `php backend/test-db.php`

---

## 🚀 Deployment

### Deploy to Vercel (Frontend) + Railway (Backend)

**Quick Start:**
- See **[DEPLOY-QUICK-START.md](./DEPLOY-QUICK-START.md)** for step-by-step deployment guide
- See **[VERCEL-DEPLOYMENT.md](./VERCEL-DEPLOYMENT.md)** for detailed deployment options

**Summary:**
1. **Frontend:** Deploy static files to Vercel (free tier available)
2. **Backend:** Deploy PHP API to Railway (free tier available)
3. **Database:** Use Railway MySQL or external MySQL service
4. **Configuration:** Update `scripts/data.js` with production API URL

**Files Created:**
- `vercel.json` - Vercel configuration for frontend
- `.vercelignore` - Files to exclude from Vercel deployment
- `backend/railway.json` - Railway configuration for backend

---

**Made by : Arpit Yadav**




