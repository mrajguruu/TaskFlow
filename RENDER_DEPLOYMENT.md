# TaskFlow - Render.com + TiDB Cloud Deployment Guide

> **Complete guide for deploying TaskFlow to Render.com with Docker + TiDB Cloud Database**

[Back to README](README.md)

---

## 📋 Table of Contents

- [Why Render.com + TiDB Cloud?](#-why-rendercom--tidb-cloud)
- [Prerequisites](#-prerequisites)
- [Part 1: Setup TiDB Cloud Database](#-part-1-setup-tidb-cloud-database)
- [Part 2: Deploy to Render.com](#-part-2-deploy-to-rendercom)
- [Part 3: Setup Cron Job](#-part-3-setup-cron-job)
- [Testing Your Deployment](#-testing-your-deployment)
- [Troubleshooting](#-troubleshooting)

---

## 🌟 Why Render.com + TiDB Cloud?

### **Render.com (Hosting)**
✅ **Free Tier:**
- 512 MB RAM, 0.1 CPU
- Docker support (no PHP runtime needed!)
- Built-in SSL certificates
- Git-based auto-deployment
- Native cron job support
- Professional URL (no ads)

⚠️ **Important:** Free tier spins down after 15 minutes of inactivity (first request takes 30-60 seconds to wake up)

### **TiDB Cloud (Database)**
✅ **Free Forever:**
- 5 GB storage permanently free
- 50 million Request Units/month
- MySQL compatible (no code changes!)
- No credit card required
- Used by Square, Shopee, etc.
- Great for resume!

---

## 📦 Prerequisites

Before starting, ensure you have:

- [x] GitHub account
- [x] TaskFlow repository pushed to GitHub
- [x] Basic understanding of environment variables
- [x] 30 minutes of time

**No credit card required for either service!**

---

## 💾 Part 1: Setup TiDB Cloud Database

### **Step 1.1: Create TiDB Cloud Account**

1. Go to **https://tidbcloud.com**
2. Click **"Sign up free"**
3. Sign up with **GitHub** (recommended)
4. Complete email verification

### **Step 1.2: Create Free Serverless Cluster**

1. Click **"Create a Cluster"**
2. Select **"Serverless"** (free tier!)
3. Configure:
   - **Name:** `taskflow-db`
   - **Cloud Provider:** AWS
   - **Region:** Choose closest to you:
     - US: `us-west-2` (Oregon)
     - Asia: `ap-southeast-1` (Singapore)
     - Europe: `eu-central-1` (Frankfurt)
   - **Spending Limit:** Keep at **Free**
4. Click **"Create"**
5. Wait 1-2 minutes for cluster to become "Available"

### **Step 1.3: Get Connection Credentials**

1. Click on your cluster **"taskflow-db"**
2. Click **"Connect"** button (top right)
3. Click **"Generate Password"**
4. ⚠️ **CRITICAL:** Copy password immediately (you won't see it again!)
5. Save these details in a text file:

```
Host: gateway01.ap-southeast-1.prod.aws.tidbcloud.com
Port: 4000
Username: <your-prefix>.root (e.g., z9aNiT3drjF8UeQ.root)
Password: <generated-password>
Database: test (we'll create taskflow next)
```

### **Step 1.4: Import Database Using DBeaver**

#### **Install DBeaver (Free)**

1. Download: https://dbeaver.io/download/
2. Install and open DBeaver

#### **Connect to TiDB Cloud**

1. Click **"New Database Connection"** (plug icon)
2. Select **"MySQL"** → **"Next"**
3. Enter TiDB credentials:
   ```
   Host: gateway01.{your-region}.prod.aws.tidbcloud.com
   Port: 4000
   Username: {your-prefix}.root
   Password: {your-generated-password}
   Database: test
   ```
4. Click **"SSL"** tab:
   - ✅ Check **"Require SSL"**
   - ❌ **UNCHECK** "Verify server certificate"
5. Click **"Test Connection"**
   - If prompted, download MySQL drivers
6. Click **"Finish"**

#### **Create TaskFlow Database**

1. Right-click connection → **"SQL Editor"** → **"New SQL Script"**
2. Paste this:
   ```sql
   CREATE DATABASE taskflow CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Click **"Execute"** (or press Ctrl+Enter)
4. Press **F5** to refresh - you should see `taskflow` database

#### **Import Schema and Data**

**Option A: Use TiDB Cloud Web Interface (Recommended)**

1. Go to TiDB Cloud dashboard
2. Click your cluster → **"Chat2Query"** tab
3. Select **`taskflow`** database from dropdown
4. You'll import your data in chunks:

**Step 1: Import Schema (Tables)**
- Open `TaskFlow/sql/database-localhost.sql` in a text editor
- Copy the CREATE TABLE statements one by one
- Paste into Chat2Query and execute each
- Verify all 8 tables created

**Step 2: Import Sample Data**
- Open `TaskFlow/sql/sample-data-localhost.sql`
- Copy INSERT statements in smaller batches (users, projects, tasks, etc.)
- Execute each batch
- Verify data imported:
  ```sql
  SELECT COUNT(*) FROM users;    -- Should show 8
  SELECT COUNT(*) FROM projects; -- Should show 7
  SELECT COUNT(*) FROM tasks;    -- Should show 119
  ```

**Option B: Use DBeaver (Alternative)**

1. In DBeaver, right-click `taskflow` database
2. **"SQL Editor"** → **"Open SQL Script"**
3. Navigate to `TaskFlow/sql/database-localhost.sql`
4. Execute entire script
5. Repeat for `TaskFlow/sql/sample-data-localhost.sql`
6. Verify tables and data

### **Step 1.5: Verify Database**

Run this query in Chat2Query or DBeaver:

```sql
SELECT
    (SELECT COUNT(*) FROM users) as users_count,
    (SELECT COUNT(*) FROM projects) as projects_count,
    (SELECT COUNT(*) FROM tasks) as tasks_count;
```

Expected result:
```
users_count: 8
projects_count: 7
tasks_count: 119
```

✅ **Database setup complete!**

---

## 🚀 Part 2: Deploy to Render.com

### **Step 2.1: Prepare Your Repository**

#### **Update render.yaml**

Open `render.yaml` in your project and update the repo URL:

```yaml
repo: https://github.com/YOUR_USERNAME/TaskFlow
```

Replace `YOUR_USERNAME` with your actual GitHub username.

#### **Commit and Push**

```bash
cd /path/to/TaskFlow
git add .
git commit -m "Add Docker configuration for Render.com deployment"
git push origin main
```

### **Step 2.2: Create Render Account**

1. Go to **https://render.com**
2. Click **"Get Started"**
3. Sign up with **GitHub** (easiest)
4. Authorize Render to access your repositories
5. Complete email verification

### **Step 2.3: Deploy Web Service**

#### **Create New Web Service**

1. From Render Dashboard, click **"New +"** → **"Web Service"**
2. Click **"Connect account"** (if not already connected)
3. Find and select your **"TaskFlow"** repository
4. Click **"Connect"**

#### **Configure Web Service**

Fill in these settings:

| Setting | Value |
|---------|-------|
| **Name** | `taskflow` (or your preferred name) |
| **Region** | Choose closest to you |
| **Branch** | `main` |
| **Root Directory** | Leave empty |
| **Runtime** | **Docker** ⚠️ (not PHP!) |
| **Dockerfile Path** | `./Dockerfile` |
| **Docker Command** | Leave empty (uses Dockerfile CMD) |
| **Instance Type** | **Free** ($0/month, 512 MB RAM) |

### **Step 2.4: Add Environment Variables**

Scroll down to **"Environment Variables"** section.

Click **"Add Environment Variable"** and add these **7 variables**:

```env
# Database Configuration (from TiDB Cloud)
DB_HOST=gateway01.ap-southeast-1.prod.aws.tidbcloud.com
DB_PORT=4000
DB_NAME=taskflow
DB_USER=z9aNiT3drjF8UeQ.root
DB_PASS=your_tidb_generated_password

# Security Token (generate new one below)
CLEANUP_TOKEN=generate_a_64_character_random_token_here
```

**Replace these values:**
- `DB_HOST`: Your TiDB cluster host (from Step 1.3)
- `DB_USER`: Your TiDB username (e.g., `z9aNiT3drjF8UeQ.root`)
- `DB_PASS`: Your TiDB generated password
- `CLEANUP_TOKEN`: Generate with:
  ```bash
  openssl rand -hex 32
  ```
  Or use online generator: https://www.random.org/strings/

### **Step 2.5: Create Web Service**

1. Click **"Create Web Service"** at the bottom
2. Render will start building your Docker image
3. Wait 3-5 minutes for deployment
4. Watch the **"Logs"** tab for progress

You'll see logs like:
```
Building Docker image...
Step 1/8 : FROM php:8.2-apache
Step 2/8 : RUN docker-php-ext-install pdo pdo_mysql
...
Build successful!
Starting service...
Service is live at https://taskflow-xxxx.onrender.com
```

### **Step 2.6: Verify Deployment**

1. Once you see **"Service is live"**, click the URL at the top
2. You should see the TaskFlow login page
3. Try logging in:
   - Email: `admin@taskflow.com`
   - Password: `password123`

✅ **Web service deployed successfully!**

---

## 📅 Part 3: Setup Cron Job

### **Step 3.1: Create Cron Job Service**

1. From Render Dashboard, click **"New +"** → **"Cron Job"**
2. Connect to your **"TaskFlow"** repository again
3. Click **"Connect"**

### **Step 3.2: Configure Cron Job**

Fill in these settings:

| Setting | Value |
|---------|-------|
| **Name** | `taskflow-cleanup` |
| **Region** | Same as your web service |
| **Branch** | `main` |
| **Runtime** | **Docker** |
| **Dockerfile Path** | `./Dockerfile` |
| **Docker Command** | `php /var/www/html/cron/cleanup-temp-users.php` |
| **Schedule** | `0 */2 * * *` (every 2 hours) |
| **Instance Type** | **Free** |

**Schedule Options:**
- Every 2 hours: `0 */2 * * *`
- Every 6 hours: `0 */6 * * *`
- Daily at midnight: `0 0 * * *`
- Every hour: `0 * * * *`

### **Step 3.3: Add Environment Variables**

Add the same **7 environment variables** as web service:

```env
DB_HOST=gateway01.ap-southeast-1.prod.aws.tidbcloud.com
DB_PORT=4000
DB_NAME=taskflow
DB_USER=z9aNiT3drjF8UeQ.root
DB_PASS=your_tidb_generated_password
CLEANUP_TOKEN=same_token_as_web_service
```

⚠️ **Important:** Use the EXACT same values as your web service!

### **Step 3.4: Create Cron Job**

1. Click **"Create Cron Job"**
2. Wait for initial build (2-3 minutes)
3. Check **"Logs"** tab to see cron job execution

Expected log output:
```
Cleanup script started...
Checking for users older than 1 hour...
Protected users: 8
Deleted 0 temporary users
Cleanup completed successfully
```

✅ **Cron job setup complete!**

---

## 🧪 Testing Your Deployment

### **Test 1: Access Live Site**

Visit your Render URL: `https://taskflow-xxxx.onrender.com`

✅ Should see TaskFlow login page

### **Test 2: Login**

- Email: `admin@taskflow.com`
- Password: `password123`

✅ Should successfully login and see dashboard

### **Test 3: Database Connection**

After login, check if projects and tasks load:
- Dashboard shows project count
- Kanban board displays tasks
- Activity feed shows recent actions

✅ All data loaded from TiDB Cloud

### **Test 4: Test Cleanup Script Manually**

Visit (replace with your URL and token):
```
https://taskflow-xxxx.onrender.com/cron/cleanup-temp-users.php?token=YOUR_CLEANUP_TOKEN
```

Expected JSON response:
```json
{
  "success": true,
  "deleted_count": 0,
  "cleanup_age_hours": 1,
  "protected_users": 8,
  "timestamp": "2026-01-10 14:30:00"
}
```

✅ Cleanup script works!

### **Test 5: Verify Cron Job Logs**

1. Render Dashboard → **taskflow-cleanup** cron job
2. Click **"Logs"** tab
3. Wait for next scheduled run (or trigger manually)
4. Should see successful execution logs

✅ Automated cleanup working!

---

## 🐛 Troubleshooting

### **Issue 1: "Build Failed" Error**

**Cause:** Docker build errors

**Solution:**
1. Check Render **"Logs"** for specific error
2. Ensure `Dockerfile` exists in repository root
3. Verify Docker runtime selected (not PHP)
4. Push latest changes to GitHub

### **Issue 2: "Database Connection Failed"**

**Cause:** Wrong TiDB credentials or SSL issue

**Solution:**
1. Verify environment variables in Render:
   - DB_HOST, DB_PORT (4000), DB_NAME, DB_USER, DB_PASS
2. Test TiDB connection in DBeaver first
3. Check TiDB cluster status (should be "Available")
4. Ensure port is `4000` (not 3306)

### **Issue 3: Site Takes 30+ Seconds to Load**

**Cause:** Free tier cold start (service was asleep)

**Solution:**
- This is normal for Render free tier
- Service spins down after 15 min inactivity
- First request wakes it up (30-60 seconds)
- Subsequent requests are fast
- Upgrade to paid plan ($7/mo) for always-on

### **Issue 4: Cleanup Script Returns 403 Forbidden**

**Cause:** Wrong or missing CLEANUP_TOKEN

**Solution:**
1. Verify `CLEANUP_TOKEN` matches in:
   - Web service environment variables
   - Cron job environment variables
   - URL parameter when testing manually
2. Regenerate if needed: `openssl rand -hex 32`
3. Update in both services

### **Issue 5: Cron Job Not Executing**

**Cause:** Wrong Docker command or missing variables

**Solution:**
1. Check cron job **"Logs"** tab for errors
2. Verify Docker Command is:
   ```
   php /var/www/html/cron/cleanup-temp-users.php
   ```
3. Ensure all 7 environment variables are set
4. Check schedule syntax (cron format)

### **Issue 6: File Uploads Not Working**

**Cause:** Render uses ephemeral filesystem (resets on deploy)

**Solution:**
- Uploads work but are lost on redeploy
- For production, integrate cloud storage:
  - AWS S3
  - Cloudinary
  - Cloudinary has free tier (10GB)
- Or disable uploads for demo purposes

---

## 📊 Monitoring & Logs

### **View Web Service Logs**

1. Render Dashboard → **taskflow** web service
2. Click **"Logs"** tab
3. See real-time application logs
4. Filter by error/warning/info

### **View Cron Job Logs**

1. Render Dashboard → **taskflow-cleanup** cron job
2. Click **"Logs"** tab
3. See execution history with timestamps

### **Check TiDB Metrics**

1. TiDB Cloud Dashboard → **taskflow-db** cluster
2. Click **"Monitoring"** tab
3. View:
   - Request Units usage
   - Storage usage
   - Query performance
   - Connection count

---

## ✅ Final Checklist

- [ ] TiDB Cloud cluster created and active
- [ ] Database schema imported (8 tables)
- [ ] Sample data imported (8 users, 7 projects, 119 tasks)
- [ ] Dockerfile and render.yaml in repository
- [ ] Repository pushed to GitHub
- [ ] Render web service deployed successfully
- [ ] All 7 environment variables configured
- [ ] Can access site and login
- [ ] Cron job created and running
- [ ] Cleanup script tested manually
- [ ] Logs show no errors

---

## 🎯 Post-Deployment

### **Update README with Live Demo**

Add this badge to your README.md:

```markdown
[![Live Demo](https://img.shields.io/badge/Live-Demo-success?style=for-the-badge&logo=render&logoColor=white)](https://taskflow-xxxx.onrender.com)

## Live Demo
[🚀 Click here to view live demo](https://taskflow-xxxx.onrender.com)

**Demo Credentials:**
- Email: `admin@taskflow.com`
- Password: `password123`

⚠️ Note: First load may take 30-60 seconds (free tier cold start)
```

### **Add Custom Domain (Optional)**

1. Render Dashboard → **taskflow** → **"Settings"**
2. Click **"Custom Domains"**
3. Add your domain: `taskflow.yourdomain.com`
4. Update DNS records as instructed by Render
5. SSL certificate auto-generated (free!)

---

## 💡 Important Notes

### **Free Tier Limitations**

**Render.com:**
- ⏱️ Service spins down after 15 minutes of inactivity
- 🐌 First request after spin-down takes 30-60 seconds
- 💾 Ephemeral filesystem (uploads lost on redeploy)
- 🔄 750 hours/month limit (enough for 1 service 24/7)

**TiDB Cloud:**
- 💾 5 GB storage max
- 📊 50 million Request Units/month
- 🔌 Connection limits (enough for demo apps)

### **Cost to Upgrade**

If your project gets traction:
- **Render Pro:** $7/month (always-on, more RAM)
- **TiDB Paid:** $0.01/GiB-month storage beyond 5GB
- **Total:** ~$10-15/month for small production app

---

## 🚀 Next Steps

1. ✅ Test all features on live site
2. ✅ Update portfolio with live link
3. ✅ Add to resume and LinkedIn
4. ✅ Share with potential employers
5. ✅ Consider custom domain for extra professionalism

**Congratulations! TaskFlow is now live on professional cloud infrastructure!** 🎉

---

**Need Help?**
- Render Docs: https://render.com/docs
- TiDB Cloud Docs: https://docs.pingcap.com/tidbcloud/
- TaskFlow Issues: https://github.com/YOUR_USERNAME/TaskFlow/issues

**Deployed by:** [Your Name]
**Stack:** PHP 8.2 + MySQL (TiDB Cloud) + Docker + Render.com
**Status:** Production-Ready ✅
