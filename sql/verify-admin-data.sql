-- ============================================================================
-- TaskFlow Database Verification Query
-- Run this in phpMyAdmin to verify admin user has dashboard data
-- ============================================================================

-- Check 1: Verify admin user exists and details
SELECT 'Admin User Details' as check_name;
SELECT id, username, email, full_name
FROM users
WHERE id = 1;

-- Check 2: Verify admin's project memberships
SELECT 'Admin Project Memberships' as check_name;
SELECT pm.project_id, p.name as project_name, p.status, pm.role, pm.joined_at
FROM project_members pm
JOIN projects p ON pm.project_id = p.id
WHERE pm.user_id = 1
ORDER BY pm.project_id;

-- Check 3: Verify tasks assigned to admin
SELECT 'Tasks Assigned to Admin' as check_name;
SELECT t.id, t.title, t.status, t.priority, t.project_id, p.name as project_name
FROM tasks t
JOIN projects p ON t.project_id = p.id
WHERE t.assigned_to = 1
ORDER BY t.id;

-- Check 4: Count active projects for admin (should be 2)
SELECT 'Active Projects Count' as check_name;
SELECT COUNT(*) as active_projects
FROM projects p
JOIN project_members pm ON p.id = pm.project_id
WHERE pm.user_id = 1 AND p.status = 'active';

-- Check 5: Count active tasks for admin (should be 3)
SELECT 'Active Tasks Count' as check_name;
SELECT COUNT(*) as active_tasks
FROM tasks
WHERE assigned_to = 1 AND status != 'completed';

-- Check 6: Verify total data counts
SELECT 'Total Data Counts' as check_name;
SELECT
    (SELECT COUNT(*) FROM users) as total_users,
    (SELECT COUNT(*) FROM projects) as total_projects,
    (SELECT COUNT(*) FROM tasks) as total_tasks,
    (SELECT COUNT(*) FROM project_members) as total_memberships,
    (SELECT COUNT(*) FROM task_comments) as total_comments,
    (SELECT COUNT(*) FROM activity_log) as total_activities;

-- ============================================================================
-- EXPECTED RESULTS:
-- ============================================================================
-- Admin User: id=1, username=admin, email=admin@taskflow.com
-- Project Memberships: Should show at least Projects 1, 3, and 7
-- Tasks Assigned: Should show tasks 3, 8, and 39
-- Active Projects Count: Should be 2 (Projects 1 and 3)
-- Active Tasks Count: Should be 3 (tasks 3, 8, 39)
-- Total Counts: 8 users, 7 projects, 119 tasks
-- ============================================================================
