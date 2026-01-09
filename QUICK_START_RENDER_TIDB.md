# TaskFlow - Quick Start Guide (Render.com + TiDB Cloud)

> **Get TaskFlow running in production in 30 minutes!**

This is a condensed version of the full deployment guide. For detailed instructions, see [RENDER_DEPLOYMENT.md](RENDER_DEPLOYMENT.md).

---

## ⚡ Quick Setup Checklist

### **Part 1: TiDB Cloud (Free Database - Already Done!)**

You've already completed this:
- [x] 1. Created TiDB Cloud account
- [x] 2. Created `taskflow-db` cluster
- [x] 3. Got connection credentials
- [x] 4. Connected with DBeaver
- [x] 5. Imported database schema (8 tables)
- [x] 6. Imported sample data (8 users, 7 projects, 119 tasks)

Your TiDB credentials:
```
Host: gateway01.ap-southeast-1.prod.aws.tidbcloud.com
Port: 4000
User: z9aNiT3drjF8UeQ.root
Database: taskflow
```

### **Part 2: Prepare Repository for Docker**

- [ ] 7. Update `render.yaml` with your GitHub username
- [ ] 8. Generate cleanup token: `openssl rand -hex 32`
- [ ] 9. Commit and push to GitHub:
   ```bash
   git add .
   git commit -m "Add Docker configuration for Render.com"
   git push origin main
   ```

### **Part 3: Deploy to Render.com**

- [ ] 10. Go to https://render.com → Sign up with GitHub
- [ ] 11. New + → Web Service → Connect TaskFlow repository
- [ ] 12. Configure:
   - Name: `taskflow`
   - Runtime: **Docker** (NOT PHP!)
   - Dockerfile Path: `./Dockerfile`
   - Instance: **Free**
- [ ] 13. Add **7 Environment Variables**:
   ```env
   DB_HOST=gateway01.ap-southeast-1.prod.aws.tidbcloud.com
   DB_PORT=4000
   DB_NAME=taskflow
   DB_USER=z9aNiT3drjF8UeQ.root
   DB_PASS=your_tidb_password_here
   CLEANUP_TOKEN=your_generated_64_char_token
   ```
- [ ] 14. Click "Create Web Service" → Wait 3-5 minutes
- [ ] 15. Visit: `https://taskflow-xxxx.onrender.com`
- [ ] 16. Login: `admin@taskflow.com` / `password123`

### **Part 4: Setup Cron Job**

- [ ] 17. Render Dashboard → New + → Cron Job
- [ ] 18. Connect TaskFlow repository again
- [ ] 19. Configure:
   - Name: `taskflow-cleanup`
   - Runtime: **Docker**
   - Dockerfile Path: `./Dockerfile`
   - Docker Command: `php /var/www/html/cron/cleanup-temp-users.php`
   - Schedule: `0 */2 * * *` (every 2 hours)
- [ ] 20. Add same **7 environment variables** as web service
- [ ] 21. Click "Create Cron Job" → Wait 2-3 minutes

### **Part 5: Test Everything**

- [ ] 22. Test login at your Render URL
- [ ] 23. Check dashboard loads with projects and tasks
- [ ] 24. Test cleanup manually:
   ```
   https://taskflow-xxxx.onrender.com/cron/cleanup-temp-users.php?token=YOUR_TOKEN
   ```
- [ ] 25. Should see: `{"success":true,"deleted_count":0,...}`
- [ ] 26. Check cron job logs in Render dashboard
- [ ] 27. Update README.md with live URL
- [ ] 28. Commit and push final changes

---

## 🎯 Environment Variables Template

Copy this and fill in your actual values:

```env
# Database (from TiDB Cloud)
DB_HOST=gateway01.ap-southeast-1.prod.aws.tidbcloud.com
DB_PORT=4000
DB_NAME=taskflow
DB_USER=z9aNiT3drjF8UeQ.root
DB_PASS=6BIaE6Ie3wdqBpLg

# Security (generate new token!)
CLEANUP_TOKEN=a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6a7b8c9d0e1f2
```

Generate cleanup token:
```bash
openssl rand -hex 32
```

---

## 🐳 Docker Files (Already Created!)

These files enable PHP deployment on Render:

1. **Dockerfile** - PHP 8.2 + Apache + PDO MySQL
2. **.dockerignore** - Excludes unnecessary files
3. **render.yaml** - Automated deployment configuration

You don't need to understand Docker - just push to GitHub!

---

## 🚨 Common Issues & Fixes

### Issue: "Runtime dropdown doesn't show PHP"
- ✅ **Solution:** Select **Docker** instead! We're using Docker to run PHP.

### Issue: "Database connection failed"
- ✅ Check DB_HOST, DB_PORT (4000), DB_NAME, DB_USER, DB_PASS
- ✅ Verify TiDB password is correct (no spaces/typos!)
- ✅ Test connection in DBeaver first

### Issue: "Build failed in Render"
- ✅ Ensure `Dockerfile` is in repository root
- ✅ Verify you selected "Docker" runtime (not PHP)
- ✅ Check Render logs for specific error
- ✅ Make sure you pushed latest changes to GitHub

### Issue: "Cleanup returns 403 Forbidden"
- ✅ Verify CLEANUP_TOKEN matches in both web service and cron job
- ✅ Regenerate token: `openssl rand -hex 32`
- ✅ Update in both services

### Issue: "Site loads slow (30+ seconds)"
- ✅ This is normal for Render free tier!
- ✅ Service spins down after 15 minutes of inactivity
- ✅ First request wakes it up (takes 30-60 seconds)
- ✅ Subsequent requests are fast

---

## ✅ Success Indicators

You're done when:
- ✅ Can access your site: `https://taskflow-xxxx.onrender.com`
- ✅ Can login with `admin@taskflow.com` / `password123`
- ✅ Dashboard shows 7 projects and tasks load
- ✅ Cleanup script returns success JSON
- ✅ Cron job appears in Render dashboard
- ✅ TiDB shows 119 tasks in database

---

## 📊 What You've Built

**Infrastructure:**
- 🐳 **Docker Container:** PHP 8.2 + Apache (modern deployment)
- 🗄️ **TiDB Cloud:** Distributed SQL database (5GB free)
- 🚀 **Render.com:** Professional hosting (512MB RAM)
- ⏰ **Cron Jobs:** Automated cleanup every 2 hours
- 🔒 **SSL/HTTPS:** Automatic (included free)

**Tech Stack:**
- Backend: PHP 8.2 with PDO
- Database: MySQL-compatible TiDB Cloud
- Deployment: Docker on Render.com
- Automation: Native Render cron jobs

**Resume-Worthy:**
- ✅ Cloud-native deployment
- ✅ Container orchestration (Docker)
- ✅ Distributed database (TiDB)
- ✅ CI/CD workflow (Git push to deploy)
- ✅ Production environment variables

---

## 📚 Next Steps

1. **Update README:** Add live demo badge and URL
2. **Test Features:** Create project, add tasks, invite members
3. **Verify Cleanup:** Create test user, wait 1 hour, check if deleted
4. **Portfolio:** Add to resume, LinkedIn, GitHub profile
5. **Custom Domain:** (Optional) Connect your own domain in Render settings
6. **Monitoring:** Check logs regularly in Render dashboard
7. **Interview Prep:** Practice explaining your deployment architecture

---

## 🆘 Need Help?

- **Full Guide:** [RENDER_DEPLOYMENT.md](RENDER_DEPLOYMENT.md) (detailed steps)
- **Render Docs:** https://render.com/docs
- **TiDB Docs:** https://docs.pingcap.com/tidbcloud/
- **Docker Issues:** Check Render logs for build errors

---

## 💡 Pro Tips

1. **Keep Service Alive:** Use UptimeRobot (free) to ping every 10 minutes
2. **Monitor Free Tier:** 750 hours/month limit (enough for 1 service 24/7)
3. **Cold Starts:** Warn users about 30-second initial load
4. **Environment Variables:** Never commit passwords to GitHub!
5. **TiDB Limits:** 5GB storage, 50M Request Units/month (plenty for portfolio)

---

**Estimated Total Time:** 20 minutes (database already done!)
**Cost:** $0 (100% Free Forever)
**Result:** Production-ready portfolio project with Docker deployment! 🎉
