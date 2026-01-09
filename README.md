# TaskFlow

<div align="center">

![TaskFlow Logo](assets/images/taskflow_icon(readme).png)

### **Modern Team Collaboration & Task Management Platform**

*A full-stack web application built with PHP, MySQL, and Vanilla JavaScript showcasing production-ready development practices*

[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![TiDB Cloud](https://img.shields.io/badge/TiDB_Cloud-Free-FF3E00?style=for-the-badge&logo=mysql&logoColor=white)](https://tidbcloud.com)
[![Docker](https://img.shields.io/badge/Docker-Enabled-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://www.docker.com)
[![Render](https://img.shields.io/badge/Deployed_on-Render-46E3B7?style=for-the-badge&logo=render&logoColor=white)](https://render.com)

[🚀 Live Demo](#) • [Screenshots](#-screenshots) • [Deployment Guide](#-deployment) • [Documentation](#-documentation)

</div>

---

## 🎯 About TaskFlow

TaskFlow is a **comprehensive project management system** designed to help teams organize, track, and collaborate on projects efficiently. Built from scratch without frameworks to demonstrate **full-stack development expertise**, security-first architecture, and modern web development practices.

### **Why TaskFlow Stands Out**

- 🎨 **Intuitive Interface** - Clean, responsive design inspired by Linear and Asana
- 🔒 **Security-First** - CSRF protection, prepared statements, XSS prevention, bcrypt hashing
- 🚀 **Real-time Interactions** - AJAX-powered operations without page reloads
- 📱 **Fully Responsive** - Seamless experience across all devices
- 🌙 **Smart Dark Mode** - System-aware theme with smooth transitions
- 🤖 **Automated Maintenance** - Native cron jobs on Render.com (every 2 hours)
- 📊 **Production Ready** - Docker deployment, environment variables, demo data protection
- ☁️ **Cloud-Native** - Deployed on Render.com with TiDB Cloud (5GB free forever)

---

## ✨ Core Features

### **Project & Task Management**
- ✅ Create and organize unlimited projects with status tracking
- ✅ Kanban board with drag-and-drop across three columns (TODO, In Progress, Completed)
- ✅ Priority levels (Low, Medium, High) with color coding
- ✅ Due dates with overdue detection
- ✅ Task assignments and team collaboration
- ✅ File attachments (10MB max, multiple formats supported)
- ✅ Comments and activity logging for audit trails

### **User Experience**
- ✅ Role-based access control (Admin, Member, Owner)
- ✅ Dark mode with localStorage persistence
- ✅ Toast notifications for all actions
- ✅ Form validation with inline error messages
- ✅ Auto-save functionality to prevent data loss
- ✅ Mobile-optimized navigation
- ✅ Password strength validator with real-time feedback

### **Developer Features**
- ✅ RESTful AJAX API (14 endpoints)
- ✅ Docker containerization with PHP 8.2 + Apache
- ✅ Automated user cleanup with native Render cron jobs
- ✅ Demo data protection for portfolio stability
- ✅ Modular JavaScript (10 ES6+ modules)
- ✅ Normalized database schema (8 tables, 3NF)
- ✅ Environment variables for cloud deployment
- ✅ Comprehensive error handling and logging

---

## 📸 Screenshots

### Desktop Experience

<div align="center">

**Dashboard Overview**
![Dashboard](screenshots/desktop-dashboard.png)

**Kanban Board with Drag & Drop**
![Kanban Board](screenshots/desktop-kanban.png)

**Task Management Modal**
![Task Modal](screenshots/desktop-task-modal.png)

**Projects Overview**
![Projects](screenshots/desktop-projects.png)

**Dark Mode**
![Dark Mode](screenshots/desktop-dark-mode.png)

**Team Management**
![Teams](screenshots/desktop-teams.png)

</div>

### Authentication

<div align="center">

**Login Screen** | **Registration Screen**
:-------------------------:|:-------------------------:
![Login](screenshots/auth-login.png) | ![Register](screenshots/auth-register.png)

</div>

### Mobile Experience

<div align="center">

**Mobile Dashboard** | **Mobile Navigation** | **Mobile Task View**
:-------------------------:|:-------------------------:|:-------------------------:
![Mobile Dashboard](screenshots/mobile-dashboard.png) | ![Mobile Menu](screenshots/mobile-menu.png) | ![Mobile Task](screenshots/mobile-task.png)

</div>

---

## 🎮 Try It Out

### **🚀 Live Demo** - Coming soon on Render.com!

TaskFlow comes with pre-configured demo accounts and rich sample data:

**Demo Accounts** (all passwords: `password123`)

| Email | Role | What You Can Test |
|-------|------|-------------------|
| `admin@taskflow.com` | **Admin** | Full system access, user management, delete permissions |
| `john@taskflow.com` | **Project Owner** | Create projects, invite members, manage teams |
| `sarah@taskflow.com` | **Designer** | Task editing, file uploads, kanban workflow |
| `mike@taskflow.com` | **Developer** | Multi-project collaboration, commenting |

**Pre-loaded Demo Data:**
- 8 users with different roles
- 7 projects across various stages
- 119 realistic tasks with descriptions
- 35+ team collaboration comments
- Complete activity audit trail
- Sample file attachments

> **Note:** Demo users (1-8), projects (1-7), and tasks (1-119) are protected from deletion to maintain portfolio integrity. Create your own test data—it will be automatically cleaned up after 1 hour!

---

## 🛠️ Tech Stack

**Backend:**
- PHP 8.2+ (pure, no frameworks)
- TiDB Cloud (MySQL-compatible distributed SQL)
- PDO for secure database operations
- Session-based authentication

**Frontend:**
- Vanilla JavaScript (ES6+)
- CSS3 with Grid and Flexbox
- HTML5 Drag & Drop API
- AJAX for seamless interactions

**DevOps & Deployment:**
- Docker containerization
- Render.com hosting (free tier)
- Native cron jobs for automation
- Git-based auto-deployment
- Environment variables for configuration
- TiDB Cloud (5GB free forever)

---

## 🚀 Deployment

### **Cloud Deployment (Recommended)**

TaskFlow is designed for **free cloud deployment** using:
- **Render.com** - Free web hosting with Docker support
- **TiDB Cloud** - 5GB MySQL-compatible database (free forever)

**Quick Start:** Follow [QUICK_START_RENDER_TIDB.md](QUICK_START_RENDER_TIDB.md) for 20-minute deployment

**Detailed Guide:** See [RENDER_DEPLOYMENT.md](RENDER_DEPLOYMENT.md) for complete step-by-step instructions

**What You Get:**
- ✅ Professional live URL (e.g., taskflow-xxx.onrender.com)
- ✅ Automatic HTTPS/SSL certificates
- ✅ Git-based auto-deployment (push to deploy)
- ✅ Native cron jobs for automated cleanup
- ✅ Environment variables for configuration
- ✅ Free forever (no credit card required)

---

### **Local Development**

**Prerequisites:**
- PHP 8.2+
- MySQL 5.7+ (or MariaDB)
- Apache/Nginx (or PHP built-in server)

**Quick Setup:**
```bash
# 1. Clone repository
git clone https://github.com/yourusername/TaskFlow.git
cd TaskFlow

# 2. Create database
mysql -u root -p -e "CREATE DATABASE taskflow CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 3. Import schema and data
mysql -u root -p taskflow < sql/database-localhost.sql
mysql -u root -p taskflow < sql/sample-data-localhost.sql

# 4. Configure database connection
cp config/database.example.php config/database.php
# Edit config/database.php with your credentials

# 5. Launch local server
php -S localhost:8000
```

Visit `http://localhost:8000` and login with `admin@taskflow.com` / `password123`

---

## 📚 Documentation

### **📖 [TECHNICAL.md](TECHNICAL.md)** - Technical Deep Dive
Comprehensive technical documentation covering:
- Complete architecture breakdown
- Database schema (8 tables with relationships)
- All 14 RESTful API endpoints
- Security implementations (OWASP Top 10 protection)
- JavaScript module system
- Performance optimizations
- What interviewers will notice

### **🚀 [RENDER_DEPLOYMENT.md](RENDER_DEPLOYMENT.md)** - Full Deployment Guide
Complete cloud deployment instructions:
- TiDB Cloud setup (free 5GB database)
- Render.com deployment with Docker
- Environment variables configuration
- Cron job setup for automated cleanup
- Testing and troubleshooting
- Monitoring and logs

### **⚡ [QUICK_START_RENDER_TIDB.md](QUICK_START_RENDER_TIDB.md)** - Quick Start
Condensed 20-minute deployment checklist:
- Step-by-step deployment tasks
- Pre-configured environment templates
- Common issues and quick fixes
- Success indicators

---

## 🔒 Security

TaskFlow implements production-grade security:

- ✅ **Password Security** - Bcrypt hashing with proper cost factors
- ✅ **SQL Injection Protection** - 100% prepared statements via PDO
- ✅ **CSRF Protection** - Token validation on all forms
- ✅ **XSS Prevention** - Proper output escaping
- ✅ **File Upload Security** - MIME type verification, size limits
- ✅ **Session Security** - Regeneration, timeout, secure cookies
- ✅ **Access Control** - Role-based permissions throughout

**For detailed security implementation, see [TECHNICAL.md](TECHNICAL.md#-security-features)**

---

## 🎯 What Makes This Project Special

### **No Framework Bloat**
Built with pure PHP and Vanilla JavaScript to demonstrate fundamental understanding rather than framework dependency.

### **Production Thinking**
- Docker containerization for consistent deployment
- Environment variables for cloud configuration
- Demo data protection for portfolio stability
- Automated maintenance with Render cron jobs
- Comprehensive error logging
- Security-first development approach

### **Real-World Features**
- Drag-and-drop kanban board
- File attachment system
- Activity logging for compliance
- Role-based access control
- Mobile-first responsive design

### **Modern Development Practices**
- RESTful API design
- MVC-inspired architecture
- Git version control
- Docker containerization
- CI/CD with Git-based auto-deployment
- Cloud-native architecture (Render + TiDB)
- Comprehensive documentation

---

## 📊 Project Stats

| Metric | Value |
|--------|-------|
| **Lines of Code** | 6,500+ |
| **Database Tables** | 8 (normalized to 3NF) |
| **API Endpoints** | 14 RESTful endpoints |
| **JavaScript Modules** | 10 ES6+ modules |
| **CSS Files** | 21 modular stylesheets |
| **Security Measures** | 15+ implemented |
| **Demo Data** | 8 users, 7 projects, 119 tasks |
| **File Support** | 10 types, 10MB limit |

---

## 🤝 Contributing

This is a portfolio project, but feedback is welcome!

**Found a bug?** Open an issue with reproduction steps.

**Have a suggestion?** Open an issue with the `enhancement` label.

---

## 📄 License

This project is licensed under the **MIT License** - see the [LICENSE](LICENSE) file for details.

---

<div align="center">

## ⭐ If TaskFlow Impressed You, Star the Repo!

**Built with precision, deployed with confidence**

---
### **Connect with Me**
[![LinkedIn](https://img.shields.io/badge/LinkedIn-0077B5?style=for-the-badge&logo=linkedin&logoColor=white)](https://www.linkedin.com/in/mohitrajguru)

---

**TaskFlow v1.0** - Professional Task Management Platform

*Demonstrating full-stack development expertise, one feature at a time*

</div>
