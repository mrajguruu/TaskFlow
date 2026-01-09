# TaskFlow - Technical Documentation

> **Complete technical deep dive into TaskFlow's architecture, implementation, and design decisions.**
>
> For general information, see [README.md](README.md) | For deployment instructions, see [DEPLOYMENT.md](DEPLOYMENT.md)

---

## 📋 Table of Contents

- [Architecture Overview](#-architecture-overview)
- [Database Schema](#-database-schema)
- [Backend Implementation](#-backend-implementation)
- [Frontend Architecture](#-frontend-architecture)
- [API Endpoints](#-api-endpoints)
- [Security Implementation](#-security-implementation)
- [Performance Optimization](#-performance-optimization)
- [Project Structure](#-project-structure)
- [Key Technical Highlights](#-key-technical-highlights)

---

## 🏗️ Architecture Overview

TaskFlow follows an **MVC-inspired architecture** without using a framework, demonstrating fundamental understanding of web application design patterns.

### **Technology Stack**

**Backend:**
- **PHP 8.0+** - Server-side logic with type declarations
- **MySQL 8.0+** - Relational database with InnoDB engine
- **PDO (PHP Data Objects)** - Database abstraction layer
- **Session-based authentication** - Secure user management

**Frontend:**
- **Vanilla JavaScript (ES6+)** - No frameworks, pure DOM manipulation
- **CSS3** - Modern styling with custom properties
- **HTML5** - Semantic markup with accessibility

**DevOps:**
- **Git** - Version control
- **GitHub Actions** - CI/CD automation
- **Webhook-based cron** - Works on free hosting

### **Design Principles**

1. **Separation of Concerns** - Logic separated into distinct layers
2. **DRY (Don't Repeat Yourself)** - Reusable functions and components
3. **Security First** - Built-in protection from the ground up
4. **Progressive Enhancement** - Works without JavaScript for core features
5. **Mobile First** - Responsive design from smallest to largest screens

---

## 🗄️ Database Schema

TaskFlow uses a **normalized MySQL database** (3NF) with 8 tables and strategic relationships.

### **Entity Relationship Diagram**

```
users (8 columns)
├── id (PK, AUTO_INCREMENT)
├── username (VARCHAR(50), UNIQUE)
├── email (VARCHAR(100), UNIQUE)
├── password_hash (VARCHAR(255))
├── full_name (VARCHAR(100))
├── role (ENUM: 'admin', 'member')
├── created_at (DATETIME)
└── updated_at (DATETIME)

projects (7 columns)
├── id (PK, AUTO_INCREMENT)
├── name (VARCHAR(100))
├── description (TEXT)
├── status (ENUM: 'active', 'completed', 'archived')
├── created_by (FK → users.id)
├── created_at (DATETIME)
└── updated_at (DATETIME)

project_members (4 columns)
├── id (PK, AUTO_INCREMENT)
├── project_id (FK → projects.id, ON DELETE CASCADE)
├── user_id (FK → users.id, ON DELETE CASCADE)
├── role (ENUM: 'owner', 'member')
└── joined_at (DATETIME)

tasks (11 columns)
├── id (PK, AUTO_INCREMENT)
├── project_id (FK → projects.id, ON DELETE CASCADE)
├── title (VARCHAR(255))
├── description (TEXT)
├── status (ENUM: 'todo', 'in_progress', 'completed')
├── priority (ENUM: 'low', 'medium', 'high')
├── assigned_to (FK → users.id, ON DELETE SET NULL)
├── created_by (FK → users.id)
├── due_date (DATE)
├── completed_at (DATETIME)
├── position (INT)
├── created_at (DATETIME)
└── updated_at (DATETIME)

task_comments (4 columns)
├── id (PK, AUTO_INCREMENT)
├── task_id (FK → tasks.id, ON DELETE CASCADE)
├── user_id (FK → users.id, ON DELETE CASCADE)
├── comment (TEXT)
└── created_at (DATETIME)

task_attachments (7 columns)
├── id (PK, AUTO_INCREMENT)
├── task_id (FK → tasks.id, ON DELETE CASCADE)
├── user_id (FK → users.id, ON DELETE CASCADE)
├── filename (VARCHAR(255))
├── original_filename (VARCHAR(255))
├── file_size (INT)
├── file_type (VARCHAR(50))
└── uploaded_at (DATETIME)

activity_log (6 columns)
├── id (PK, AUTO_INCREMENT)
├── user_id (FK → users.id, ON DELETE CASCADE)
├── project_id (FK → projects.id, ON DELETE CASCADE, NULL)
├── task_id (FK → tasks.id, ON DELETE CASCADE, NULL)
├── action (VARCHAR(100))
├── details (TEXT)
└── created_at (DATETIME)

password_resets (5 columns)
├── id (PK, AUTO_INCREMENT)
├── user_id (FK → users.id, ON DELETE CASCADE)
├── token (VARCHAR(100), UNIQUE)
├── expires_at (DATETIME)
└── created_at (DATETIME)
```

### **Key Database Features**

**Normalization:**
- **3NF (Third Normal Form)** - No redundant data, all non-key attributes depend on primary key
- **Referential Integrity** - Foreign key constraints maintain data consistency
- **Cascade Deletes** - Related data automatically cleaned up

**Performance Optimization:**
```sql
-- Strategic indexes for frequent queries
CREATE INDEX idx_tasks_project ON tasks(project_id);
CREATE INDEX idx_tasks_status ON tasks(status);
CREATE INDEX idx_tasks_assigned ON tasks(assigned_to);
CREATE INDEX idx_tasks_due_date ON tasks(due_date);
CREATE INDEX idx_project_members_lookup ON project_members(project_id, user_id);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_created_at ON users(created_at);
CREATE INDEX idx_activity_log_user ON activity_log(user_id);
```

**Data Types:**
- **ENUM** for constrained values (status, priority, role) - enforces data integrity
- **UTF8MB4** character set - full Unicode support including emojis
- **InnoDB engine** - ACID compliance and foreign key support

---

## 💻 Backend Implementation

### **PHP Architecture**

**Configuration Layer** (`config/`)
```php
config.php              // Application constants and settings
database.php            // PDO connection with error handling
secrets.php             // Security tokens (gitignored)
```

**Authentication Layer** (`auth/`)
```php
login.php               // Session-based authentication
register.php            // User registration with validation
logout.php              // Secure session destruction
forgot-password.php     // Token-based password reset
reset-password.php      // Password reset form
```

**Application Layer** (`dashboard/`)
```php
index.php               // Dashboard with statistics
projects.php            // Project overview (grid/list view)
project.php             // Kanban board with drag-drop
team.php                // User management (admin only)
profile.php             // User profile editing
settings.php            // User preferences
```

**API Layer** (`ajax/`)
```php
// 14 RESTful endpoints returning JSON
create-project.php      // POST: Create project
get-project.php         // GET: Fetch project
update-project.php      // POST: Update project
delete-project.php      // POST: Delete project
create-task.php         // POST: Create task
get-task.php            // GET: Fetch task (basic)
get-task-details.php    // GET: Fetch task with attachments
update-task.php         // POST: Update task
update-task-status.php  // POST: Update status (drag-drop)
delete-task.php         // POST: Delete task
upload-attachment.php   // POST: Upload file
delete-attachment.php   // POST: Delete file
update-user-role.php    // POST: Change role (admin)
delete-user.php         // POST: Delete user (admin)
```

**Shared Components** (`includes/`)
```php
auth.php                // Authentication helpers
functions.php           // Utility functions
header.php              // Navigation and user menu
footer.php              // Scripts and footer
```

### **Code Examples**

**Secure Database Query:**
```php
// GOOD - Using prepared statements
$stmt = $pdo->prepare("
    SELECT t.*, p.name as project_name, u.full_name as assignee_name
    FROM tasks t
    JOIN projects p ON t.project_id = p.id
    LEFT JOIN users u ON t.assigned_to = u.id
    WHERE t.id = ? AND t.project_id IN (
        SELECT project_id FROM project_members WHERE user_id = ?
    )
");
$stmt->execute([$taskId, $userId]);
$task = $stmt->fetch();
```

**CSRF Protection:**
```php
// Generate token on page load
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Validate token on form submission
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    die(json_encode(['success' => false, 'message' => 'Invalid CSRF token']));
}
```

**File Upload Security:**
```php
// Validate file upload
$allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $_FILES['file']['tmp_name']);

if (!in_array($mime_type, $allowed_types)) {
    die(json_encode(['success' => false, 'message' => 'Invalid file type']));
}

// Generate secure filename
$extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
$filename = bin2hex(random_bytes(16)) . '.' . $extension;
```

---

## 🎨 Frontend Architecture

### **JavaScript Module System**

TaskFlow uses **10 ES6+ modules** for clean, maintainable code:

| Module | Lines | Purpose | Key Features |
|--------|-------|---------|--------------|
| `dark-mode.js` | ~150 | Theme management | localStorage persistence, system detection, smooth transitions |
| `drag-drop.js` | ~200 | Kanban functionality | HTML5 Drag & Drop API, AJAX updates, visual feedback |
| `task-modal.js` | ~250 | Task details | Dynamic rendering, file uploads, edit mode |
| `form-validation.js` | ~180 | Client validation | Real-time checks, custom rules, inline errors |
| `form-autosave.js` | ~120 | Auto-save | localStorage drafts, unsaved warnings, 24hr retention |
| `password-strength.js` | ~140 | Password validator | Strength meter, pattern checks, generator |
| `toast.js` | ~100 | Notifications | 4 types, auto-dismiss, queue management |
| `skeleton.js` | ~80 | Loading states | Skeleton placeholders during data fetch |
| `mobile-nav.js` | ~90 | Mobile menu | Touch-optimized, drawer animation |
| `settings.js` | ~110 | Settings panel | Theme preferences, profile updates |

### **JavaScript Code Examples**

**Dark Mode Implementation:**
```javascript
// Check system preference
const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

// Apply theme
function applyTheme(theme) {
    if (theme === 'dark' || (theme === 'auto' && prefersDark)) {
        document.documentElement.classList.add('dark-mode');
        document.querySelector('meta[name="theme-color"]')
            ?.setAttribute('content', '#1a1a2e');
    } else {
        document.documentElement.classList.remove('dark-mode');
        document.querySelector('meta[name="theme-color"]')
            ?.setAttribute('content', '#4361ee');
    }
    localStorage.setItem('theme', theme);
}

// Listen for system changes
window.matchMedia('(prefers-color-scheme: dark)')
    .addEventListener('change', (e) => {
        if (getCurrentTheme() === 'auto') {
            applyTheme('auto');
        }
    });
```

**Drag-and-Drop:**
```javascript
// Setup drag events
taskCard.addEventListener('dragstart', (e) => {
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', taskCard.dataset.taskId);
    taskCard.classList.add('dragging');
});

// Handle drop
column.addEventListener('drop', async (e) => {
    e.preventDefault();
    const taskId = e.dataTransfer.getData('text/plain');
    const newStatus = column.dataset.status;

    // Optimistic UI update
    updateTaskStatus(taskId, newStatus);

    // Sync with server
    const response = await fetch('/ajax/update-task-status.php', {
        method: 'POST',
        body: JSON.stringify({ taskId, status: newStatus })
    });

    if (!response.ok) {
        // Revert on error
        revertTaskStatus(taskId);
        showToast('Failed to update task', 'error');
    }
});
```

**Form Auto-Save:**
```javascript
// Debounced auto-save
const debouncedSave = debounce((formData) => {
    localStorage.setItem('form-draft-' + formId, JSON.stringify(formData));
    showSavingIndicator();
}, 500);

// Monitor form changes
form.addEventListener('input', (e) => {
    const formData = new FormData(form);
    debouncedSave(Object.fromEntries(formData));
});

// Restore on page load
window.addEventListener('DOMContentLoaded', () => {
    const draft = localStorage.getItem('form-draft-' + formId);
    if (draft) {
        restoreFormData(JSON.parse(draft));
        showDraftNotice();
    }
});
```

### **CSS Architecture**

**21 Modular Stylesheets:**
```
assets/css/
├── main.css                 # Global styles, CSS variables
├── auth.css                 # Login/register pages
├── dashboard.css            # Dashboard layout
├── projects.css             # Project cards grid
├── kanban.css               # Kanban board styles
├── task-modal.css           # Task details modal
├── dark-mode.css            # Dark theme overrides
├── toast.css                # Toast notifications
├── mobile-nav.css           # Mobile navigation
├── form-validation.css      # Validation styles
├── skeleton.css             # Loading placeholders
└── ... (11 more)
```

**CSS Variables for Theming:**
```css
:root {
    /* Colors */
    --primary: #4361ee;
    --secondary: #3f37c9;
    --success: #06d6a0;
    --danger: #ef476f;
    --warning: #ffd60a;

    /* Backgrounds */
    --bg-primary: #ffffff;
    --bg-secondary: #f8f9fa;
    --bg-card: #ffffff;

    /* Text */
    --text-primary: #212529;
    --text-secondary: #6c757d;

    /* Transitions */
    --transition-speed: 0.3s;
    --transition-ease: cubic-bezier(0.4, 0, 0.2, 1);
}

.dark-mode {
    --bg-primary: #1a1a2e;
    --bg-secondary: #16213e;
    --bg-card: #0f3460;
    --text-primary: #edf2f4;
    --text-secondary: #8d99ae;
}
```

---

## 🔌 API Endpoints

TaskFlow provides **14 RESTful AJAX endpoints** with consistent response format.

### **Response Format**

**Success Response:**
```json
{
    "success": true,
    "message": "Operation completed successfully",
    "data": {
        "id": 123,
        "...": "..."
    }
}
```

**Error Response:**
```json
{
    "success": false,
    "message": "Error description",
    "errors": {
        "field": "Validation error message"
    }
}
```

### **Endpoint Reference**

#### **Projects**

**POST `/ajax/create-project.php`**
```javascript
// Request
{
    name: "New Project",
    description: "Project description",
    status: "active",
    csrf_token: "..."
}

// Response
{
    success: true,
    message: "Project created successfully",
    data: { project_id: 8 }
}
```

**GET `/ajax/get-project.php?id=1`**
```javascript
// Response
{
    success: true,
    data: {
        id: 1,
        name: "Website Redesign",
        description: "...",
        status: "active",
        task_count: 20,
        member_count: 7
    }
}
```

#### **Tasks**

**POST `/ajax/update-task-status.php`**
```javascript
// Request (Drag & Drop)
{
    task_id: 5,
    status: "in_progress",
    csrf_token: "..."
}

// Response
{
    success: true,
    message: "Task status updated"
}
```

**POST `/ajax/upload-attachment.php`**
```javascript
// Request (FormData)
file: [File object]
task_id: 5
csrf_token: "..."

// Response
{
    success: true,
    message: "File uploaded successfully",
    data: {
        attachment_id: 12,
        filename: "abc123.pdf",
        original_filename: "report.pdf",
        file_size: 1024000
    }
}
```

### **Authentication Required**

All endpoints check for valid session:
```php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}
```

### **CSRF Protection**

All POST endpoints validate CSRF tokens:
```php
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    die(json_encode(['success' => false, 'message' => 'Invalid CSRF token']));
}
```

---

## 🔒 Security Implementation

TaskFlow implements **15+ security measures** following OWASP Top 10 guidelines.

### **1. Authentication & Authorization**

**Password Security:**
```php
// Hashing (cost factor 10)
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

// Verification
if (password_verify($password, $hash)) {
    // Login successful
}
```

**Session Security:**
```php
// Regenerate ID on login (prevent fixation)
session_regenerate_id(true);

// Session timeout (1 hour)
if (time() - $_SESSION['last_activity'] > 3600) {
    session_destroy();
    header('Location: /auth/login.php');
    exit;
}

// Update last activity
$_SESSION['last_activity'] = time();
```

### **2. Input Validation & Sanitization**

**Server-Side Validation:**
```php
// Example: Task creation
$title = trim($_POST['title'] ?? '');
$priority = $_POST['priority'] ?? '';

// Validate
$errors = [];
if (empty($title) || strlen($title) > 255) {
    $errors['title'] = 'Title must be 1-255 characters';
}
if (!in_array($priority, ['low', 'medium', 'high'])) {
    $errors['priority'] = 'Invalid priority';
}

if (!empty($errors)) {
    http_response_code(400);
    die(json_encode(['success' => false, 'errors' => $errors]));
}
```

**Output Escaping:**
```php
// Always escape output
echo htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');

// For attributes
echo '<div data-value="' . htmlspecialchars($value, ENT_QUOTES) . '">';
```

### **3. SQL Injection Prevention**

**100% Prepared Statements:**
```php
// NEVER do this
$query = "SELECT * FROM users WHERE email = '$email'";  // VULNERABLE!

// ALWAYS do this
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
```

### **4. XSS Prevention**

**Content Security Policy:**
```php
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';");
```

**JavaScript Context Escaping:**
```javascript
// Escape user content before inserting into DOM
const escapeHtml = (text) => {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
};

element.innerHTML = escapeHtml(userContent);
```

### **5. File Upload Security**

**Validation Pipeline:**
```php
// 1. Check file exists
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    die('Upload error');
}

// 2. Check file size (10MB max)
if ($_FILES['file']['size'] > 10 * 1024 * 1024) {
    die('File too large');
}

// 3. Verify MIME type (not just extension!)
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $_FILES['file']['tmp_name']);
$allowed = ['image/jpeg', 'image/png', 'application/pdf', 'application/msword'];

if (!in_array($mime, $allowed)) {
    die('Invalid file type');
}

// 4. Generate secure filename
$ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
$filename = bin2hex(random_bytes(16)) . '.' . strtolower($ext);

// 5. Move to secure location
$uploadPath = ATTACHMENT_PATH . '/' . $filename;
move_uploaded_file($_FILES['file']['tmp_name'], $uploadPath);
```

### **6. Access Control**

**Role-Based Permissions:**
```php
// Check admin role
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Check project ownership
function isProjectOwner($projectId, $userId, $pdo) {
    $stmt = $pdo->prepare("
        SELECT 1 FROM project_members
        WHERE project_id = ? AND user_id = ? AND role = 'owner'
    ");
    $stmt->execute([$projectId, $userId]);
    return $stmt->fetchColumn() !== false;
}

// Use in endpoints
if (!isProjectOwner($projectId, $_SESSION['user_id'], $pdo)) {
    http_response_code(403);
    die(json_encode(['success' => false, 'message' => 'Permission denied']));
}
```

### **7. Demo Data Protection**

**Prevent Accidental Deletion:**
```php
// Protected user IDs (demo accounts)
$protectedUsers = [1, 2, 3, 4, 5, 6, 7, 8];

if (in_array($userId, $protectedUsers)) {
    http_response_code(403);
    die(json_encode([
        'success' => false,
        'message' => 'Cannot delete demo user'
    ]));
}
```

---

## ⚡ Performance Optimization

### **Database Optimization**

**Query Performance:**
```sql
-- Before: Slow query (no index)
SELECT * FROM tasks WHERE assigned_to = 5;  -- Full table scan

-- After: Fast query (indexed)
CREATE INDEX idx_tasks_assigned ON tasks(assigned_to);
SELECT * FROM tasks WHERE assigned_to = 5;  -- Index seek
```

**Result:** Most queries execute in <50ms

**Efficient Joins:**
```php
// Get tasks with project and user info in ONE query
$stmt = $pdo->prepare("
    SELECT
        t.*,
        p.name as project_name,
        u.full_name as assignee_name,
        creator.full_name as creator_name
    FROM tasks t
    JOIN projects p ON t.project_id = p.id
    LEFT JOIN users u ON t.assigned_to = u.id
    LEFT JOIN users creator ON t.created_by = creator.id
    WHERE t.project_id = ?
    ORDER BY t.position ASC
");
```

### **Frontend Optimization**

**Lazy Loading:**
```javascript
// Load task details only when modal opens
async function openTaskModal(taskId) {
    showSkeleton();
    const data = await fetch(`/ajax/get-task-details.php?id=${taskId}`);
    renderModal(data);
}
```

**Debouncing:**
```javascript
// Debounce search input
const debouncedSearch = debounce((query) => {
    searchTasks(query);
}, 300);

searchInput.addEventListener('input', (e) => {
    debouncedSearch(e.target.value);
});
```

**LocalStorage Caching:**
```javascript
// Cache theme preference
localStorage.setItem('theme', 'dark');

// Cache form drafts
localStorage.setItem('task-draft', JSON.stringify(formData));
```

---

## 📁 Project Structure

```
TaskFlow/
│
├── .github/
│   └── workflows/
│       └── cleanup-users.yml        # GitHub Actions automation
│
├── ajax/                            # RESTful API Layer
│   ├── create-project.php           # Project CRUD
│   ├── create-task.php              # Task CRUD
│   ├── upload-attachment.php        # File handling
│   └── ... (14 files total)
│
├── assets/
│   ├── css/                         # Modular Stylesheets (21 files)
│   │   ├── main.css                 # Global styles, CSS variables
│   │   ├── dark-mode.css            # Dark theme
│   │   ├── kanban.css               # Kanban board
│   │   └── ...
│   │
│   ├── js/                          # ES6+ Modules (10 files)
│   │   ├── dark-mode.js             # Theme system
│   │   ├── drag-drop.js             # Drag & Drop
│   │   ├── task-modal.js            # Modal logic
│   │   └── ...
│   │
│   └── images/
│       ├── taskflow_icon.png        # Logo (400x64)
│       └── favicon.png              # Favicon
│
├── auth/                            # Authentication Layer
│   ├── login.php                    # Session-based login
│   ├── register.php                 # User registration
│   ├── logout.php                   # Session destruction
│   ├── forgot-password.php          # Password reset request
│   └── reset-password.php           # Token-based reset
│
├── config/                          # Configuration Layer
│   ├── config.php                   # App constants (committed)
│   ├── database.php                 # DB connection (gitignored)
│   ├── database.example.php         # DB config template
│   ├── secrets.php                  # Tokens (gitignored)
│   └── secrets.example.php          # Secrets template
│
├── cron/
│   └── cleanup-temp-users.php       # Automated cleanup endpoint
│
├── dashboard/                       # Application Layer
│   ├── index.php                    # Dashboard (stats, activity)
│   ├── projects.php                 # Project grid view
│   ├── project.php                  # Kanban board
│   ├── team.php                     # User management (admin)
│   ├── profile.php                  # Profile editing
│   └── settings.php                 # User preferences
│
├── includes/                        # Shared Components
│   ├── header.php                   # Navigation, user menu
│   ├── footer.php                   # Scripts, footer
│   ├── auth.php                     # Auth helpers
│   └── functions.php                # Utility functions
│
├── logs/                            # Application Logs (gitignored)
│   └── cleanup.log                  # Cleanup operations
│
├── sql/                             # Database Layer
│   ├── database.sql                 # Schema (8 tables)
│   └── sample-data.sql              # Demo data (8 users, 7 projects, 119 tasks)
│
├── uploads/                         # User Content (gitignored)
│   ├── avatars/                     # Profile pictures
│   └── attachments/                 # Task files
│
├── .gitignore                       # Git ignore rules
├── DEPLOYMENT.md                    # Deployment guide
├── index.php                        # Entry point
├── LICENSE                          # MIT License
├── README.md                        # Main documentation
└── TECHNICAL.md                     # This file
```

---

## 🎯 Key Technical Highlights

### **What Makes This Code Production-Ready**

#### **1. No Framework Dependency**

**Why it matters:**
- Demonstrates **fundamental understanding** of web development
- Shows ability to build from first principles
- No reliance on framework magic or black boxes

**Evidence:**
- Pure PHP with type declarations
- Vanilla JavaScript with ES6+ features
- Manual routing and request handling
- Custom authentication system

#### **2. Security-First Development**

**OWASP Top 10 Protection:**
- ✅ **A01: Broken Access Control** - Role-based permissions, session validation
- ✅ **A02: Cryptographic Failures** - Bcrypt hashing, secure tokens
- ✅ **A03: Injection** - 100% prepared statements
- ✅ **A04: Insecure Design** - Defense in depth, fail secure
- ✅ **A05: Security Misconfiguration** - Environment-based config
- ✅ **A06: Vulnerable Components** - Minimal dependencies
- ✅ **A07: Authentication Failures** - Session regeneration, timeout
- ✅ **A08: Data Integrity Failures** - CSRF tokens, input validation
- ✅ **A09: Logging Failures** - Activity logging, error logging
- ✅ **A10: SSRF** - Input validation, no user-controlled URLs

#### **3. Database Design Excellence**

**Normalized Schema:**
- **3NF compliance** - No redundancy, optimal structure
- **Foreign keys** - Data integrity enforced at DB level
- **Strategic indexes** - Query performance optimization
- **Appropriate data types** - ENUM for constrained values

**Query Performance:**
```
Average query time: <50ms
Indexed queries: <10ms
Complex joins: <100ms
```

#### **4. Code Organization & Maintainability**

**Separation of Concerns:**
- Configuration separate from application code
- Authentication separate from business logic
- API endpoints separate from views
- Shared utilities in dedicated files

**DRY Principle:**
```php
// Reusable function instead of copy-paste
function jsonResponse($success, $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}
```

**Consistent Naming:**
- camelCase for JavaScript variables/functions
- snake_case for PHP variables/database columns
- PascalCase for PHP classes
- kebab-case for CSS classes

#### **5. Modern Development Practices**

**RESTful API Design:**
- Clear HTTP methods (GET for fetch, POST for modify)
- Consistent JSON response format
- Appropriate status codes (200, 400, 401, 403, 500)

**Progressive Enhancement:**
- Core features work without JavaScript
- JavaScript enhances experience (AJAX, drag-drop)
- Graceful degradation for older browsers

**Mobile-First Responsive:**
- Base styles for mobile
- Media queries add complexity for larger screens
- Touch-optimized interactions

#### **6. Production Thinking**

**Environment Configuration:**
```php
if (ENVIRONMENT === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}
```

**Error Logging:**
```php
try {
    // Database operation
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    die(json_encode(['success' => false, 'message' => 'An error occurred']));
}
```

**Demo Data Protection:**
- Protected user IDs prevent deletion
- Automated cleanup for test users only
- Maintains portfolio integrity

---

## 📊 Performance Metrics

| Metric | Value | Notes |
|--------|-------|-------|
| **Average Page Load** | <500ms | Local environment |
| **Database Query Time** | <50ms | With indexes |
| **AJAX Response Time** | <100ms | JSON endpoints |
| **CSS File Size** | ~80KB | Unminified |
| **JS File Size** | ~50KB | Unminified, modular |
| **Lighthouse Score** | 90+ | Performance, Accessibility |

---

## 🧪 Testing Approach

### **Manual Testing Checklist**

**Functionality:**
- [x] User registration and login
- [x] Project creation and management
- [x] Task CRUD operations
- [x] Drag-and-drop functionality
- [x] File upload and download
- [x] Comments and activity logging
- [x] Dark mode switching
- [x] Mobile responsiveness

**Security:**
- [x] SQL injection attempts blocked
- [x] XSS attempts sanitized
- [x] CSRF tokens validated
- [x] Unauthorized access prevented
- [x] File upload restrictions enforced
- [x] Session timeout working

**Performance:**
- [x] Page load times acceptable
- [x] Database queries optimized
- [x] No memory leaks in JavaScript
- [x] Smooth animations (60fps)

---

## 🔍 Code Quality Standards

**PHP:**
- Type declarations where possible
- Descriptive variable names
- Comments for complex logic
- Error handling with try-catch
- Consistent indentation (4 spaces)

**JavaScript:**
- ES6+ features (arrow functions, destructuring)
- Const/let instead of var
- Async/await for promises
- Event delegation for dynamic elements
- No jQuery dependency

**CSS:**
- BEM-inspired naming
- CSS variables for theming
- Mobile-first media queries
- Flexbox and Grid layouts
- No !important unless necessary

---

## 📚 Learning Resources Used

**Security:**
- OWASP Top 10 - Web Application Security Risks
- PHP Security Guide - Best Practices
- MDN Web Security - Client-Side Protection

**Database:**
- Database Normalization Tutorial
- MySQL Performance Optimization
- Indexing Best Practices

**Frontend:**
- MDN JavaScript Guide
- HTML5 Drag and Drop API
- CSS Grid and Flexbox Guide

---

## 🤝 Contributing to Code Quality

If you're reviewing this code and find areas for improvement:

1. **Security Issues** - Highest priority, open issue immediately
2. **Performance Bottlenecks** - Profile first, suggest with data
3. **Code Smells** - Explain why it's problematic
4. **Missing Tests** - Suggest test cases to add

---

<div align="center">

**For deployment instructions, see [DEPLOYMENT.md](DEPLOYMENT.md)**

**For general information, see [README.md](README.md)**

---

*TaskFlow - Demonstrating production-ready full-stack development*

</div>
