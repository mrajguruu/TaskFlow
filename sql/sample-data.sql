-- ============================================================
-- TaskFlow - Enhanced Demo Data with Protection
-- PRODUCTION VERSION (For InfinityFree)
-- ============================================================
-- This file contains comprehensive sample data for demonstration
-- Includes demo users, projects, tasks, attachments, and activity
-- Demo data is protected from deletion
--
-- FOR LOCALHOST: Use sample-data-localhost.sql instead
-- FOR PRODUCTION (InfinityFree/000webhost): Use this file
--
-- IMPORTANT:
-- - Database must be selected in phpMyAdmin before importing
-- - No USE statement included for compatibility
-- ============================================================

-- Database is already selected in phpMyAdmin, no USE statement needed

-- Clear existing data (if any)
-- Using DELETE instead of TRUNCATE to avoid foreign key constraint issues
DELETE FROM activity_log;
DELETE FROM task_comments;
DELETE FROM task_attachments;
DELETE FROM tasks;
DELETE FROM project_members;
DELETE FROM projects;
DELETE FROM users;

-- Reset auto-increment counters
ALTER TABLE users AUTO_INCREMENT = 1;
ALTER TABLE projects AUTO_INCREMENT = 1;
ALTER TABLE tasks AUTO_INCREMENT = 1;
ALTER TABLE task_comments AUTO_INCREMENT = 1;
ALTER TABLE task_attachments AUTO_INCREMENT = 1;
ALTER TABLE activity_log AUTO_INCREMENT = 1;
ALTER TABLE project_members AUTO_INCREMENT = 1;

-- ============================================================
-- Insert Demo Users (Protected from deletion)
-- Password for all users: password123
-- Hashed using PHP password_hash() with PASSWORD_DEFAULT
-- ============================================================
INSERT INTO users (id, username, email, password, full_name, avatar, role, created_at, last_login) VALUES
(1, 'admin', 'admin@taskflow.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', 'default-avatar.png', 'admin', NOW(), NOW()),
(2, 'johndoe', 'john@taskflow.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', 'default-avatar.png', 'member', NOW(), NOW()),
(3, 'sarahjohnson', 'sarah@taskflow.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah Johnson', 'default-avatar.png', 'member', NOW(), NOW()),
(4, 'mikechen', 'mike@taskflow.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mike Chen', 'default-avatar.png', 'member', NOW(), NOW()),
(5, 'emmawilson', 'emma@taskflow.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Emma Wilson', 'default-avatar.png', 'member', NOW(), NOW()),
(6, 'alexbrown', 'alex@taskflow.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alex Brown', 'default-avatar.png', 'member', NOW(), NOW()),
(7, 'liuchen', 'liu@taskflow.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Liu Chen', 'default-avatar.png', 'member', NOW(), NOW()),
(8, 'rachelgreen', 'rachel@taskflow.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Rachel Green', 'default-avatar.png', 'member', NOW(), NOW());

-- ============================================================
-- Insert Demo Projects (Protected from deletion)
-- ============================================================
INSERT INTO projects (id, name, description, owner_id, status, start_date, end_date, created_at) VALUES
(1, 'Website Redesign', 'Complete redesign of company website with modern UI/UX, responsive design across all devices, and improved performance metrics. Focus on accessibility and SEO optimization.', 2, 'active', '2024-12-01', '2025-01-15', '2024-12-01 09:00:00'),
(2, 'Mobile App Development', 'Develop native iOS and Android applications for our platform with offline capability, push notifications, and seamless synchronization. Target launch in Q1 2025.', 2, 'active', '2024-11-15', '2025-02-28', '2024-11-15 10:30:00'),
(3, 'API Integration Platform', 'Build comprehensive RESTful API for third-party integrations, mobile platform backend, and webhook support. Include comprehensive documentation and SDKs.', 3, 'active', '2024-12-10', '2025-03-01', '2024-12-10 14:00:00'),
(4, 'Marketing Campaign Q1 2025', 'Plan and execute Q1 marketing campaign across social media, email channels, and content marketing. Goal: 50% increase in lead generation.', 4, 'active', '2025-01-01', '2025-03-31', '2024-12-15 11:00:00'),
(5, 'Data Migration Project', 'Successfully migrated legacy database to new cloud infrastructure with zero downtime. Improved query performance by 80% and reduced costs by 40%.', 2, 'completed', '2024-10-01', '2024-11-30', '2024-10-01 08:00:00'),
(6, 'Customer Portal Redesign', 'Modernize customer portal with self-service features, ticket management, and knowledge base integration. Improve user satisfaction scores.', 5, 'active', '2024-12-20', '2025-02-15', '2024-12-20 10:00:00'),
(7, 'Security Audit Q4', 'Comprehensive security audit covering penetration testing, code review, and compliance verification. Implement recommendations for enhanced security.', 1, 'archived', '2024-09-01', '2024-11-30', '2024-09-01 09:00:00');

-- ============================================================
-- Insert Project Members
-- ============================================================
-- Website Redesign Team (7 members - including admin)
INSERT INTO project_members (project_id, user_id, role, joined_at) VALUES
(1, 2, 'owner', '2024-12-01 09:00:00'),
(1, 1, 'member', '2024-12-01 09:30:00'),
(1, 3, 'member', '2024-12-01 10:00:00'),
(1, 4, 'member', '2024-12-01 10:30:00'),
(1, 5, 'member', '2024-12-02 09:00:00'),
(1, 6, 'member', '2024-12-02 14:00:00'),
(1, 7, 'member', '2024-12-03 09:00:00');

-- Mobile App Team (4 members)
INSERT INTO project_members (project_id, user_id, role, joined_at) VALUES
(2, 2, 'owner', '2024-11-15 10:30:00'),
(2, 4, 'member', '2024-11-15 11:00:00'),
(2, 5, 'member', '2024-11-16 09:00:00'),
(2, 8, 'member', '2024-11-17 10:00:00');

-- API Integration Team (6 members - including admin)
INSERT INTO project_members (project_id, user_id, role, joined_at) VALUES
(3, 3, 'owner', '2024-12-10 14:00:00'),
(3, 1, 'member', '2024-12-10 14:30:00'),
(3, 2, 'member', '2024-12-10 15:00:00'),
(3, 4, 'member', '2024-12-11 09:00:00'),
(3, 6, 'member', '2024-12-11 10:00:00'),
(3, 7, 'member', '2024-12-12 09:00:00');

-- Marketing Campaign Team (4 members)
INSERT INTO project_members (project_id, user_id, role, joined_at) VALUES
(4, 4, 'owner', '2024-12-15 11:00:00'),
(4, 3, 'member', '2024-12-15 12:00:00'),
(4, 5, 'member', '2024-12-16 09:00:00'),
(4, 8, 'member', '2024-12-17 10:00:00');

-- Data Migration Team (2 members) - Completed
INSERT INTO project_members (project_id, user_id, role, joined_at) VALUES
(5, 2, 'owner', '2024-10-01 08:00:00'),
(5, 4, 'member', '2024-10-01 09:00:00');

-- Customer Portal Team (3 members)
INSERT INTO project_members (project_id, user_id, role, joined_at) VALUES
(6, 5, 'owner', '2024-12-20 10:00:00'),
(6, 3, 'member', '2024-12-20 11:00:00'),
(6, 6, 'member', '2024-12-21 09:00:00');

-- Security Audit Team (2 members) - Archived
INSERT INTO project_members (project_id, user_id, role, joined_at) VALUES
(7, 1, 'owner', '2024-09-01 09:00:00'),
(7, 6, 'member', '2024-09-02 10:00:00');

-- ============================================================
-- PROJECT 1: Website Redesign (20 tasks)
-- ============================================================
-- TODO Tasks (7 tasks - some assigned to admin)
INSERT INTO tasks (id, project_id, title, description, status, priority, assigned_to, created_by, due_date, position, created_at) VALUES
(1, 1, 'Design Homepage Layout', 'Create responsive homepage design with hero section, feature highlights, call-to-action buttons, and testimonials section. Ensure mobile-first approach.', 'todo', 'high', 3, 2, '2025-01-08', 1, '2024-12-15 10:00:00'),
(2, 1, 'Create Mockups for Blog Section', 'Design blog listing and single post pages with modern card layout. Include pagination, categories, and search functionality mockups.', 'todo', 'medium', 3, 2, '2025-01-12', 2, '2024-12-16 11:30:00'),
(3, 1, 'Content Strategy Document', 'Outline comprehensive content strategy and create detailed sitemap for new website structure. Include user journey mapping.', 'todo', 'low', 1, 2, '2025-01-15', 3, '2024-12-17 09:00:00'),
(4, 1, 'SEO Optimization Plan', 'Research target keywords, analyze competitors, and create comprehensive SEO optimization strategy for all pages. Include meta descriptions and alt text guidelines.', 'todo', 'medium', 5, 2, '2025-01-18', 4, '2024-12-18 14:00:00'),
(5, 1, 'Browser Compatibility Testing', 'Test website across all major browsers (Chrome, Firefox, Safari, Edge) and document any compatibility issues. Create bug reports for fixes.', 'todo', 'high', 6, 2, '2025-01-20', 5, '2024-12-19 10:30:00'),
(6, 1, 'Accessibility Audit', 'Conduct WCAG 2.1 AA compliance audit and implement necessary fixes for screen readers, keyboard navigation, and color contrast.', 'todo', 'high', 7, 2, '2025-01-22', 6, '2024-12-20 09:00:00'),
(7, 1, 'Analytics Integration', 'Set up Google Analytics 4, create custom events, and implement conversion tracking for key user actions.', 'todo', 'medium', 4, 2, '2025-01-25', 7, '2024-12-21 11:00:00');

-- IN PROGRESS Tasks (8 tasks - some assigned to admin)
INSERT INTO tasks (id, project_id, title, description, status, priority, assigned_to, created_by, due_date, position, created_at) VALUES
(8, 1, 'Develop Backend API Endpoints', 'Create RESTful API endpoints for contact form, newsletter subscription, and user authentication. Include rate limiting and validation.', 'in_progress', 'high', 1, 2, '2025-01-06', 1, '2024-12-12 09:00:00'),
(9, 1, 'Implement Contact Form', 'Build responsive contact form with real-time validation, spam protection, and email notification system with customizable templates.', 'in_progress', 'high', 4, 2, '2025-01-08', 2, '2024-12-13 11:00:00'),
(10, 1, 'Set Up CDN Integration', 'Configure CloudFlare CDN for static assets to improve load times globally. Implement cache invalidation strategy.', 'in_progress', 'medium', 6, 2, '2025-01-10', 3, '2024-12-14 13:00:00'),
(11, 1, 'Create About Us Page', 'Design and develop company about page with team section, company history, values, and mission statement. Include interactive timeline.', 'in_progress', 'medium', 3, 2, '2025-01-12', 4, '2024-12-15 15:00:00'),
(12, 1, 'Mobile Responsive Testing', 'Test and fix responsive design issues on various mobile devices (iPhone, iPad, Android). Ensure touch interactions work smoothly.', 'in_progress', 'high', 5, 2, '2025-01-05', 5, '2024-12-16 10:00:00'),
(13, 1, 'Performance Optimization', 'Optimize images (WebP format), minify CSS/JS, implement lazy loading, and reduce Time to Interactive. Target 95+ Lighthouse score.', 'in_progress', 'high', 6, 2, '2025-01-07', 6, '2024-12-17 12:00:00'),
(14, 1, 'Footer Component Design', 'Design comprehensive footer with sitemap, social media links, newsletter signup form, and legal links. Ensure responsive across all devices.', 'in_progress', 'low', 3, 2, '2025-01-14', 7, '2024-12-18 09:30:00'),
(15, 1, 'Database Optimization', 'Add proper indexes, optimize slow queries, and implement connection pooling for improved database performance.', 'in_progress', 'medium', 4, 2, '2025-01-09', 8, '2024-12-19 14:00:00');

-- COMPLETED Tasks (5 tasks)
INSERT INTO tasks (id, project_id, title, description, status, priority, assigned_to, created_by, due_date, completed_at, position, created_at) VALUES
(16, 1, 'Setup Project Repository', 'Initialize Git repository with proper .gitignore, set up branch protection rules, and configure development environment for all team members.', 'completed', 'high', 2, 2, '2024-12-05', '2024-12-05 16:00:00', 1, '2024-12-01 09:00:00'),
(17, 1, 'Research Phase Complete', 'Complete competitive analysis of 10+ competitor websites, conduct user research interviews with 20 users, and compile findings report.', 'completed', 'high', 5, 2, '2024-12-08', '2024-12-08 17:30:00', 2, '2024-12-02 10:00:00'),
(18, 1, 'Choose Tech Stack', 'Finalize technology stack after team discussion: PHP 8.2, MySQL 8.0, Tailwind CSS 3.0, Alpine.js. Document reasoning and alternatives considered.', 'completed', 'high', 2, 2, '2024-12-10', '2024-12-10 15:00:00', 3, '2024-12-03 11:00:00'),
(19, 1, 'Create Design System', 'Establish comprehensive design system with color palette, typography scale, spacing system, and component library in Figma.', 'completed', 'high', 3, 2, '2024-12-12', '2024-12-12 16:45:00', 4, '2024-12-04 09:30:00'),
(20, 1, 'Database Schema Design', 'Design and implement normalized database structure with proper relationships, indexes, and constraints for optimal performance.', 'completed', 'high', 4, 2, '2024-12-15', '2024-12-15 14:20:00', 5, '2024-12-05 10:00:00');

-- ============================================================
-- PROJECT 2: Mobile App Development (18 tasks)
-- ============================================================
-- TODO Tasks (6 tasks)
INSERT INTO tasks (id, project_id, title, description, status, priority, assigned_to, created_by, due_date, position, created_at) VALUES
(21, 2, 'Push Notifications Integration', 'Integrate Firebase Cloud Messaging for cross-platform push notifications with custom sounds and actions.', 'todo', 'high', 4, 2, '2025-01-20', 1, '2024-12-05 11:00:00'),
(22, 2, 'Offline Mode Implementation', 'Implement offline data synchronization with local SQLite database and conflict resolution strategy.', 'todo', 'high', 5, 2, '2025-01-25', 2, '2024-12-06 09:00:00'),
(23, 2, 'App Store Submission Prep', 'Prepare app store listings, screenshots, privacy policy, and submission documentation for both stores.', 'todo', 'medium', 8, 2, '2025-02-15', 3, '2024-12-10 14:00:00'),
(24, 2, 'In-App Purchases Setup', 'Implement subscription model with monthly and annual plans using App Store and Google Play billing systems.', 'todo', 'high', 4, 2, '2025-02-01', 4, '2024-12-11 10:00:00'),
(25, 2, 'Deep Linking Configuration', 'Configure universal links (iOS) and app links (Android) for seamless navigation from web to app.', 'todo', 'medium', 5, 2, '2025-01-28', 5, '2024-12-12 11:00:00'),
(26, 2, 'App Analytics Integration', 'Integrate Firebase Analytics and Mixpanel for comprehensive user behavior tracking and funnel analysis.', 'todo', 'low', 8, 2, '2025-02-05', 6, '2024-12-13 09:00:00');

-- IN PROGRESS Tasks (7 tasks)
INSERT INTO tasks (id, project_id, title, description, status, priority, assigned_to, created_by, due_date, position, created_at) VALUES
(27, 2, 'iOS App Development Setup', 'Set up Xcode project, configure code signing, and establish development environment for iOS app with SwiftUI.', 'in_progress', 'high', 4, 2, '2025-01-10', 1, '2024-12-01 10:00:00'),
(28, 2, 'Android App Development Setup', 'Set up Android Studio project, configure Gradle, and establish development environment for Android app with Kotlin.', 'in_progress', 'high', 5, 2, '2025-01-10', 2, '2024-12-01 10:30:00'),
(29, 2, 'User Profile Management', 'Build complete user profile screens with photo upload, settings management, and account preferences.', 'in_progress', 'medium', 4, 2, '2025-01-15', 3, '2024-12-07 10:00:00'),
(30, 2, 'Camera Integration', 'Implement native camera functionality with photo editing capabilities, filters, and compression before upload.', 'in_progress', 'medium', 5, 2, '2025-01-18', 4, '2024-12-08 11:00:00'),
(31, 2, 'Real-time Sync Engine', 'Build synchronization engine for real-time updates using WebSockets with automatic reconnection logic.', 'in_progress', 'high', 4, 2, '2025-01-12', 5, '2024-12-09 09:00:00'),
(32, 2, 'App Localization Setup', 'Configure multi-language support for English, Spanish, French, German, and Chinese with RTL support.', 'in_progress', 'low', 8, 2, '2025-01-30', 6, '2024-12-14 10:00:00'),
(33, 2, 'Crash Reporting Integration', 'Set up Crashlytics for automated crash reporting and stack trace analysis on both platforms.', 'in_progress', 'medium', 5, 2, '2025-01-16', 7, '2024-12-15 11:00:00');

-- COMPLETED Tasks (5 tasks)
INSERT INTO tasks (id, project_id, title, description, status, priority, assigned_to, created_by, due_date, completed_at, position, created_at) VALUES
(34, 2, 'User Authentication Flow', 'Build secure login/register flow with biometric authentication support (Face ID, Touch ID, Fingerprint).', 'completed', 'high', 4, 2, '2024-12-15', '2024-12-15 18:00:00', 1, '2024-11-20 10:00:00'),
(35, 2, 'Design System Implementation', 'Create reusable UI component library matching brand guidelines for consistent look across both platforms.', 'completed', 'high', 8, 2, '2024-12-10', '2024-12-10 16:00:00', 2, '2024-11-22 09:00:00'),
(36, 2, 'Navigation Architecture', 'Implement tab-based navigation with nested navigation stacks for complex user flows.', 'completed', 'high', 4, 2, '2024-12-12', '2024-12-12 17:00:00', 3, '2024-11-25 10:00:00'),
(37, 2, 'API Client Layer', 'Build robust API client with request/response interceptors, token refresh, and error handling.', 'completed', 'high', 5, 2, '2024-12-18', '2024-12-18 15:00:00', 4, '2024-11-28 11:00:00'),
(38, 2, 'Dark Mode Support', 'Implement system-wide dark mode support with automatic theme switching based on device settings.', 'completed', 'medium', 8, 2, '2024-12-20', '2024-12-20 14:00:00', 5, '2024-12-01 09:00:00');

-- ============================================================
-- PROJECT 3: API Integration Platform (17 tasks)
-- ============================================================
-- TODO Tasks (6 tasks - some assigned to admin)
INSERT INTO tasks (id, project_id, title, description, status, priority, assigned_to, created_by, due_date, position, created_at) VALUES
(39, 3, 'Webhook System Implementation', 'Build webhook delivery system with retry logic, signature verification, and event subscriptions.', 'todo', 'high', 1, 3, '2025-01-20', 1, '2024-12-13 09:00:00'),
(40, 3, 'Rate Limiting Implementation', 'Implement token bucket rate limiting per API key with custom limits and usage analytics dashboard.', 'todo', 'medium', 4, 3, '2025-01-25', 2, '2024-12-14 10:00:00'),
(41, 3, 'GraphQL API Development', 'Build GraphQL endpoint alongside REST API for flexible data querying with schema stitching.', 'todo', 'medium', 6, 3, '2025-02-10', 3, '2024-12-15 11:00:00'),
(42, 3, 'API Versioning Strategy', 'Implement URL-based API versioning with deprecation notices and sunset schedules.', 'todo', 'high', 3, 3, '2025-01-30', 4, '2024-12-16 09:00:00'),
(43, 3, 'SDK Development - Python', 'Create Python SDK with type hints, async support, and comprehensive examples.', 'todo', 'medium', 7, 3, '2025-02-15', 5, '2024-12-17 10:00:00'),
(44, 3, 'SDK Development - JavaScript', 'Build JavaScript/TypeScript SDK for Node.js and browsers with full TypeScript definitions.', 'todo', 'medium', 6, 3, '2025-02-15', 6, '2024-12-17 11:00:00');

-- IN PROGRESS Tasks (6 tasks)
INSERT INTO tasks (id, project_id, title, description, status, priority, assigned_to, created_by, due_date, position, created_at) VALUES
(45, 3, 'Implement Authentication System', 'Build JWT-based authentication with refresh tokens, role-based access control, and OAuth2 support.', 'in_progress', 'high', 4, 3, '2025-01-08', 1, '2024-12-11 10:00:00'),
(46, 3, 'Create API Documentation', 'Write comprehensive API documentation using OpenAPI 3.0 spec with Swagger UI for interactive testing.', 'in_progress', 'medium', 6, 3, '2025-01-15', 2, '2024-12-12 11:00:00'),
(47, 3, 'Request Validation Layer', 'Implement comprehensive request validation with JSON Schema and clear error messages.', 'in_progress', 'high', 4, 3, '2025-01-12', 3, '2024-12-18 09:00:00'),
(48, 3, 'Response Caching System', 'Build Redis-based caching layer for frequently accessed endpoints with cache warming.', 'in_progress', 'medium', 7, 3, '2025-01-18', 4, '2024-12-19 10:00:00'),
(49, 3, 'API Monitoring Dashboard', 'Create real-time monitoring dashboard for API health, response times, and error rates.', 'in_progress', 'high', 6, 3, '2025-01-22', 5, '2024-12-20 11:00:00'),
(50, 3, 'Pagination and Filtering', 'Implement cursor-based pagination and advanced filtering for all list endpoints.', 'in_progress', 'medium', 4, 3, '2025-01-16', 6, '2024-12-21 09:00:00');

-- COMPLETED Tasks (5 tasks)
INSERT INTO tasks (id, project_id, title, description, status, priority, assigned_to, created_by, due_date, completed_at, position, created_at) VALUES
(51, 3, 'Design API Architecture', 'Design RESTful API architecture with proper resource naming, versioning strategy, and authentication flow.', 'completed', 'high', 3, 3, '2024-12-15', '2024-12-15 17:00:00', 1, '2024-12-10 15:00:00'),
(52, 3, 'Database Schema for API', 'Create database schema for API keys, rate limits, webhook configurations, and usage logs.', 'completed', 'high', 4, 3, '2024-12-18', '2024-12-18 16:00:00', 2, '2024-12-11 09:00:00'),
(53, 3, 'Error Handling Framework', 'Build consistent error handling with proper HTTP status codes and detailed error responses.', 'completed', 'high', 4, 3, '2024-12-20', '2024-12-20 15:00:00', 3, '2024-12-12 10:00:00'),
(54, 3, 'API Testing Framework', 'Set up automated API testing with Postman collections and integration tests.', 'completed', 'medium', 6, 3, '2024-12-22', '2024-12-22 14:00:00', 4, '2024-12-13 11:00:00'),
(55, 3, 'Security Headers Implementation', 'Implement security headers (CORS, CSP, HSTS) and security best practices.', 'completed', 'high', 7, 3, '2024-12-24', '2024-12-24 16:00:00', 5, '2024-12-14 09:00:00');

-- ============================================================
-- PROJECT 4: Marketing Campaign Q1 2025 (16 tasks)
-- ============================================================
-- TODO Tasks (5 tasks)
INSERT INTO tasks (id, project_id, title, description, status, priority, assigned_to, created_by, due_date, position, created_at) VALUES
(56, 4, 'Content Creation', 'Create 20 blog posts, 10 infographics, and 15 video scripts for content marketing campaign.', 'todo', 'medium', 8, 4, '2025-02-01', 1, '2024-12-18 09:00:00'),
(57, 4, 'Influencer Partnership Program', 'Identify and reach out to 15 industry influencers for partnership and content collaboration.', 'todo', 'high', 5, 4, '2025-01-25', 2, '2024-12-19 10:00:00'),
(58, 4, 'Webinar Series Planning', 'Plan and organize 4 educational webinars with industry experts and product demonstrations.', 'todo', 'medium', 3, 4, '2025-02-15', 3, '2024-12-20 11:00:00'),
(59, 4, 'Paid Advertising Campaign', 'Launch Google Ads and LinkedIn Ads campaigns with $50K budget allocation and A/B testing.', 'todo', 'high', 5, 4, '2025-01-15', 4, '2024-12-21 09:00:00'),
(60, 4, 'Marketing Automation Setup', 'Configure HubSpot workflows for lead nurturing, scoring, and automated follow-ups.', 'todo', 'medium', 8, 4, '2025-02-05', 5, '2024-12-22 10:00:00');

-- IN PROGRESS Tasks (6 tasks)
INSERT INTO tasks (id, project_id, title, description, status, priority, assigned_to, created_by, due_date, position, created_at) VALUES
(61, 4, 'Social Media Strategy', 'Develop comprehensive social media strategy for LinkedIn, Twitter, and Instagram with content calendar.', 'in_progress', 'high', 5, 4, '2025-01-10', 1, '2024-12-16 10:00:00'),
(62, 4, 'Email Campaign Design', 'Design email templates for drip campaign with 8 touchpoints. Include A/B testing variants.', 'in_progress', 'high', 3, 4, '2025-01-15', 2, '2024-12-17 11:00:00'),
(63, 4, 'Landing Page Optimization', 'Optimize 5 landing pages for conversions with heatmap analysis and split testing.', 'in_progress', 'high', 3, 4, '2025-01-20', 3, '2024-12-19 14:00:00'),
(64, 4, 'SEO Content Optimization', 'Optimize existing content for target keywords and create pillar pages for main topics.', 'in_progress', 'medium', 8, 4, '2025-01-18', 4, '2024-12-23 09:00:00'),
(65, 4, 'Community Building Initiative', 'Launch community forum and Slack workspace for user engagement and feedback collection.', 'in_progress', 'low', 5, 4, '2025-02-10', 5, '2024-12-24 10:00:00'),
(66, 4, 'Partner Co-Marketing Campaign', 'Develop co-marketing campaigns with 3 strategic partners for mutual audience reach.', 'in_progress', 'medium', 3, 4, '2025-01-28', 6, '2024-12-25 11:00:00');

-- COMPLETED Tasks (5 tasks)
INSERT INTO tasks (id, project_id, title, description, status, priority, assigned_to, created_by, due_date, completed_at, position, created_at) VALUES
(67, 4, 'Market Research Analysis', 'Complete market research including competitor analysis, target audience personas, and market sizing.', 'completed', 'high', 5, 4, '2024-12-20', '2024-12-20 17:00:00', 1, '2024-12-15 12:00:00'),
(68, 4, 'Brand Messaging Framework', 'Develop consistent brand messaging, value propositions, and key differentiators.', 'completed', 'high', 3, 4, '2024-12-22', '2024-12-22 16:00:00', 2, '2024-12-16 09:00:00'),
(69, 4, 'Campaign Budget Allocation', 'Create detailed budget allocation across channels with ROI projections and tracking.', 'completed', 'high', 4, 4, '2024-12-28', '2024-12-28 15:00:00', 3, '2024-12-17 10:00:00'),
(70, 4, 'Analytics Dashboard Setup', 'Set up comprehensive marketing analytics dashboard in Google Data Studio.', 'completed', 'medium', 8, 4, '2024-12-30', '2024-12-30 14:00:00', 4, '2024-12-18 11:00:00'),
(71, 4, 'Customer Journey Mapping', 'Map complete customer journey from awareness to conversion with touchpoint analysis.', 'completed', 'high', 5, 4, '2025-01-02', '2025-01-02 16:00:00', 5, '2024-12-19 09:00:00');

-- ============================================================
-- PROJECT 5: Data Migration Project (15 tasks - COMPLETED)
-- ============================================================
INSERT INTO tasks (id, project_id, title, description, status, priority, assigned_to, created_by, due_date, completed_at, position, created_at) VALUES
(72, 5, 'Migration Strategy Planning', 'Develop comprehensive migration strategy with risk assessment and rollback procedures.', 'completed', 'high', 2, 2, '2024-10-05', '2024-10-05 17:00:00', 1, '2024-10-01 09:00:00'),
(73, 5, 'Data Audit and Cleanup', 'Audit legacy database, identify data quality issues, and perform cleanup operations.', 'completed', 'high', 4, 2, '2024-10-10', '2024-10-10 16:00:00', 2, '2024-10-02 10:00:00'),
(74, 5, 'Schema Transformation Design', 'Design new database schema optimized for cloud infrastructure with proper normalization.', 'completed', 'high', 4, 2, '2024-10-15', '2024-10-15 15:00:00', 3, '2024-10-03 11:00:00'),
(75, 5, 'ETL Pipeline Development', 'Build Extract-Transform-Load pipeline for data migration with validation checks.', 'completed', 'high', 2, 2, '2024-10-20', '2024-10-20 18:00:00', 4, '2024-10-05 09:00:00'),
(76, 5, 'Test Migration on Staging', 'Perform full migration test on staging environment and validate data integrity.', 'completed', 'high', 4, 2, '2024-10-25', '2024-10-25 16:00:00', 5, '2024-10-10 10:00:00'),
(77, 5, 'Performance Benchmarking', 'Benchmark query performance and compare with legacy system metrics.', 'completed', 'medium', 2, 2, '2024-10-28', '2024-10-28 14:00:00', 6, '2024-10-12 11:00:00'),
(78, 5, 'Create Migration Scripts', 'Write automated migration scripts with progress tracking and error handling.', 'completed', 'high', 4, 2, '2024-11-01', '2024-11-01 17:00:00', 7, '2024-10-15 09:00:00'),
(79, 5, 'Data Validation Framework', 'Develop comprehensive data validation to ensure 100% accuracy post-migration.', 'completed', 'high', 2, 2, '2024-11-05', '2024-11-05 16:00:00', 8, '2024-10-18 10:00:00'),
(80, 5, 'Backup and Recovery Plan', 'Create detailed backup strategy and disaster recovery procedures.', 'completed', 'high', 2, 2, '2024-11-08', '2024-11-08 15:00:00', 9, '2024-10-20 11:00:00'),
(81, 5, 'Team Training Sessions', 'Conduct training for team on new database system and migration procedures.', 'completed', 'medium', 2, 2, '2024-11-10', '2024-11-10 17:00:00', 10, '2024-10-22 09:00:00'),
(82, 5, 'Production Migration Execution', 'Execute production migration during planned maintenance window with monitoring.', 'completed', 'high', 2, 2, '2024-11-15', '2024-11-15 23:00:00', 11, '2024-10-25 10:00:00'),
(83, 5, 'Post-Migration Validation', 'Validate all data successfully migrated and perform integrity checks.', 'completed', 'high', 4, 2, '2024-11-16', '2024-11-16 10:00:00', 12, '2024-11-15 08:00:00'),
(84, 5, 'Performance Optimization', 'Optimize indexes, queries, and caching for improved performance.', 'completed', 'high', 4, 2, '2024-11-20', '2024-11-20 16:00:00', 13, '2024-11-16 09:00:00'),
(85, 5, 'Legacy System Decommission', 'Safely decommission legacy database after verification period.', 'completed', 'medium', 2, 2, '2024-11-28', '2024-11-28 17:00:00', 14, '2024-11-20 10:00:00'),
(86, 5, 'Final Documentation', 'Complete comprehensive documentation of new system and migration process.', 'completed', 'medium', 2, 2, '2024-11-30', '2024-11-30 16:00:00', 15, '2024-11-25 09:00:00');

-- ============================================================
-- PROJECT 6: Customer Portal Redesign (17 tasks)
-- ============================================================
-- TODO Tasks (6 tasks)
INSERT INTO tasks (id, project_id, title, description, status, priority, assigned_to, created_by, due_date, position, created_at) VALUES
(87, 6, 'Knowledge Base Integration', 'Integrate searchable knowledge base with categories, tags, and article versioning.', 'todo', 'medium', 6, 5, '2025-02-10', 1, '2024-12-22 11:00:00'),
(88, 6, 'Live Chat Support Widget', 'Implement real-time chat support with agent availability status and message history.', 'todo', 'high', 3, 5, '2025-02-05', 2, '2024-12-23 09:00:00'),
(89, 6, 'Customer Feedback System', 'Build feedback collection system with NPS surveys and satisfaction ratings.', 'todo', 'medium', 6, 5, '2025-02-12', 3, '2024-12-24 10:00:00'),
(90, 6, 'Multi-language Support', 'Implement internationalization for English, Spanish, French, and German.', 'todo', 'low', 3, 5, '2025-02-15', 4, '2024-12-25 11:00:00'),
(91, 6, 'Advanced Search Functionality', 'Build advanced search with filters, facets, and relevance-based results.', 'todo', 'medium', 6, 5, '2025-02-08', 5, '2024-12-26 09:00:00'),
(92, 6, 'Mobile App Integration', 'Create mobile-responsive portal with progressive web app capabilities.', 'todo', 'high', 3, 5, '2025-02-10', 6, '2024-12-27 10:00:00');

-- IN PROGRESS Tasks (6 tasks)
INSERT INTO tasks (id, project_id, title, description, status, priority, assigned_to, created_by, due_date, position, created_at) VALUES
(93, 6, 'Ticket Management System', 'Build ticket creation, tracking, and resolution system with priority levels and SLA tracking.', 'in_progress', 'high', 3, 5, '2025-01-30', 1, '2024-12-21 10:00:00'),
(94, 6, 'Customer Dashboard', 'Design customer dashboard showing account status, recent tickets, and quick actions.', 'in_progress', 'high', 3, 5, '2025-02-05', 2, '2024-12-23 09:00:00'),
(95, 6, 'Document Upload System', 'Implement secure document upload with virus scanning and file type validation.', 'in_progress', 'medium', 6, 5, '2025-02-01', 3, '2024-12-28 11:00:00'),
(96, 6, 'Notification System', 'Build email and in-app notification system for ticket updates and announcements.', 'in_progress', 'high', 3, 5, '2025-01-28', 4, '2024-12-29 09:00:00'),
(97, 6, 'Self-Service Portal Features', 'Create self-service features for password reset, account updates, and billing information.', 'in_progress', 'medium', 6, 5, '2025-02-03', 5, '2024-12-30 10:00:00'),
(98, 6, 'Reporting and Analytics', 'Build admin reporting dashboard for ticket metrics, customer satisfaction, and trends.', 'in_progress', 'low', 6, 5, '2025-02-07', 6, '2024-12-31 11:00:00');

-- COMPLETED Tasks (5 tasks)
INSERT INTO tasks (id, project_id, title, description, status, priority, assigned_to, created_by, due_date, completed_at, position, created_at) VALUES
(99, 6, 'Portal Design Mockups', 'Create comprehensive design mockups for all portal sections with user feedback.', 'completed', 'high', 3, 5, '2024-12-28', '2024-12-28 17:00:00', 1, '2024-12-20 11:00:00'),
(100, 6, 'User Authentication System', 'Implement secure authentication with SSO support and two-factor authentication.', 'completed', 'high', 6, 5, '2025-01-05', '2025-01-05 16:00:00', 2, '2024-12-21 09:00:00'),
(101, 6, 'Database Schema Design', 'Design database schema for tickets, users, knowledge base, and activity logs.', 'completed', 'high', 6, 5, '2025-01-08', '2025-01-08 15:00:00', 3, '2024-12-22 10:00:00'),
(102, 6, 'Frontend Framework Setup', 'Set up React frontend with TypeScript, Redux, and component library.', 'completed', 'high', 3, 5, '2025-01-10', '2025-01-10 14:00:00', 4, '2024-12-23 11:00:00'),
(103, 6, 'API Development', 'Build RESTful API for portal with comprehensive endpoint documentation.', 'completed', 'high', 6, 5, '2025-01-15', '2025-01-15 16:00:00', 5, '2024-12-24 09:00:00');

-- ============================================================
-- PROJECT 7: Security Audit Q4 (16 tasks - ARCHIVED)
-- ============================================================
INSERT INTO tasks (id, project_id, title, description, status, priority, assigned_to, created_by, due_date, completed_at, position, created_at) VALUES
(104, 7, 'Security Audit Planning', 'Plan comprehensive security audit scope, timeline, and resource allocation.', 'completed', 'high', 1, 1, '2024-09-05', '2024-09-05 17:00:00', 1, '2024-09-01 10:00:00'),
(105, 7, 'Penetration Testing - Web', 'Conduct penetration testing on all web applications and APIs.', 'completed', 'high', 6, 1, '2024-09-15', '2024-09-15 18:00:00', 2, '2024-09-03 09:00:00'),
(106, 7, 'Penetration Testing - Network', 'Perform network penetration testing including firewall and VPN configurations.', 'completed', 'high', 6, 1, '2024-09-20', '2024-09-20 17:00:00', 3, '2024-09-05 10:00:00'),
(107, 7, 'Code Security Review', 'Review application code for security vulnerabilities using static analysis tools.', 'completed', 'high', 1, 1, '2024-09-25', '2024-09-25 16:00:00', 4, '2024-09-08 11:00:00'),
(108, 7, 'Database Security Audit', 'Audit database security including access controls, encryption, and backup procedures.', 'completed', 'high', 6, 1, '2024-09-28', '2024-09-28 15:00:00', 5, '2024-09-10 09:00:00'),
(109, 7, 'Authentication Review', 'Review authentication mechanisms, password policies, and session management.', 'completed', 'high', 1, 1, '2024-10-02', '2024-10-02 16:00:00', 6, '2024-09-12 10:00:00'),
(110, 7, 'Third-party Dependencies', 'Audit all third-party libraries and dependencies for known vulnerabilities.', 'completed', 'medium', 6, 1, '2024-10-05', '2024-10-05 14:00:00', 7, '2024-09-15 11:00:00'),
(111, 7, 'Compliance Verification', 'Verify compliance with GDPR, SOC 2, and industry security standards.', 'completed', 'high', 1, 1, '2024-10-10', '2024-10-10 17:00:00', 8, '2024-09-18 09:00:00'),
(112, 7, 'Security Documentation Review', 'Review and update security policies, procedures, and incident response plans.', 'completed', 'medium', 1, 1, '2024-10-15', '2024-10-15 16:00:00', 9, '2024-09-20 10:00:00'),
(113, 7, 'Employee Security Training', 'Conduct security awareness training for all employees.', 'completed', 'medium', 1, 1, '2024-10-20', '2024-10-20 15:00:00', 10, '2024-09-22 11:00:00'),
(114, 7, 'Vulnerability Remediation', 'Fix all critical and high-severity vulnerabilities identified during audit.', 'completed', 'high', 6, 1, '2024-10-28', '2024-10-28 18:00:00', 11, '2024-09-25 09:00:00'),
(115, 7, 'Security Monitoring Setup', 'Implement security monitoring and alerting for suspicious activities.', 'completed', 'high', 6, 1, '2024-11-05', '2024-11-05 16:00:00', 12, '2024-10-01 10:00:00'),
(116, 7, 'Backup and Recovery Testing', 'Test backup and disaster recovery procedures for all critical systems.', 'completed', 'high', 1, 1, '2024-11-10', '2024-11-10 17:00:00', 13, '2024-10-05 11:00:00'),
(117, 7, 'Security Audit Report', 'Compile comprehensive security audit report with findings and recommendations.', 'completed', 'high', 1, 1, '2024-11-15', '2024-11-15 18:00:00', 14, '2024-10-10 09:00:00'),
(118, 7, 'Executive Presentation', 'Present security audit findings and recommendations to executive team.', 'completed', 'high', 1, 1, '2024-11-20', '2024-11-20 15:00:00', 15, '2024-10-15 10:00:00'),
(119, 7, 'Security Improvement Roadmap', 'Create 12-month security improvement roadmap based on audit findings.', 'completed', 'medium', 1, 1, '2024-11-30', '2024-11-30 16:00:00', 16, '2024-10-20 11:00:00');

-- ============================================================
-- Insert Sample Comments (80+ comments showing rich collaboration)
-- ============================================================
INSERT INTO task_comments (task_id, user_id, comment, created_at) VALUES
-- Project 1: Website Redesign Comments
(1, 2, 'Sarah, please make sure the hero section follows our new brand guidelines. Reference the design system we created.', '2024-12-15 14:30:00'),
(1, 3, 'Got it! I will incorporate the new color palette and typography. Should take 2-3 days for initial mockups.', '2024-12-15 15:00:00'),
(1, 5, 'Should we include customer testimonials in the homepage? We have great quotes from recent clients.', '2024-12-16 10:00:00'),
(1, 2, 'Yes, great idea! Add a testimonials section below the features. Use the carousel pattern from our design system.', '2024-12-16 10:30:00'),
(1, 7, 'I can help with the mobile responsive version once the desktop design is approved.', '2024-12-17 09:00:00'),

(8, 4, 'Working on the authentication endpoints. Implementing JWT with refresh tokens for better security.', '2024-12-13 11:00:00'),
(8, 2, 'Great! Make sure to include proper error messages for validation. Also add rate limiting.', '2024-12-13 11:30:00'),
(8, 4, 'Added rate limiting (100 req/min) and comprehensive validation. Running tests now.', '2024-12-14 10:00:00'),
(8, 6, 'Can you also add logging for all API requests? Will help with debugging.', '2024-12-14 14:00:00'),
(8, 4, 'Done! Added structured logging with request IDs for tracing.', '2024-12-14 16:00:00'),

(9, 4, 'Contact form is 80% complete. Added reCAPTCHA v3 for spam protection.', '2024-12-18 11:00:00'),
(9, 2, 'Excellent! Make sure email notifications are working properly. Test with both Gmail and Outlook.', '2024-12-18 14:00:00'),
(9, 5, 'I can help with the email templates if needed. We should make them mobile-responsive.', '2024-12-19 09:00:00'),
(9, 4, 'That would be great Emma! I will focus on the backend validation logic.', '2024-12-19 10:00:00'),

(12, 5, 'Completed the responsive testing on iPhone 14, iPad Pro, and Samsung Galaxy S23. Looking good overall!', '2024-12-17 16:00:00'),
(12, 2, 'Excellent! Can you also test on older devices? iPhone 8 and Samsung Galaxy S10 for baseline support.', '2024-12-17 16:30:00'),
(12, 5, 'Found some minor issues with the hamburger menu on iPhone 8. Working on fixes.', '2024-12-18 10:00:00'),
(12, 5, 'All issues fixed! Menu works perfectly on all tested devices now.', '2024-12-18 15:00:00'),

(13, 6, 'Converted all images to WebP format. Reduced total image size by 65%!', '2024-12-19 14:00:00'),
(13, 2, 'Amazing! What about the JavaScript bundle size?', '2024-12-19 15:00:00'),
(13, 6, 'Implemented code splitting. Main bundle is now 45KB (down from 180KB). Lazy loading non-critical components.', '2024-12-19 17:00:00'),
(13, 4, 'Don\'t forget to add resource hints (preload, prefetch) for critical assets.', '2024-12-20 09:00:00'),
(13, 6, 'Good point! Added preload for fonts and critical CSS. Performance score is now 96!', '2024-12-20 14:00:00'),

(16, 2, 'Repository is ready! Everyone has access. Please clone and run npm install to get started.', '2024-12-05 10:00:00'),
(16, 3, 'Got it working! The development environment setup was smooth.', '2024-12-05 11:00:00'),

-- Project 2: Mobile App Comments
(27, 4, 'Xcode project set up with SwiftUI. Targeting iOS 15+ for modern features.', '2024-12-03 11:00:00'),
(27, 2, 'Good choice. Make sure to test on both iPhone and iPad layouts.', '2024-12-03 14:00:00'),
(27, 8, 'Should we support dark mode from the start? It\'s pretty standard now.', '2024-12-04 09:00:00'),
(27, 4, 'Absolutely! Already included in the design system. Will implement with iOS native support.', '2024-12-04 10:00:00'),

(28, 5, 'Android project initialized with Kotlin and Jetpack Compose. Minimum SDK 24 (Android 7.0).', '2024-12-02 11:00:00'),
(28, 2, 'Perfect! That covers 95% of active Android devices.', '2024-12-02 14:00:00'),
(28, 5, 'Setting up Material Design 3 components for consistent UI.', '2024-12-03 09:00:00'),

(29, 4, 'Profile screen is coming along nicely. Added photo cropping functionality.', '2024-12-10 11:00:00'),
(29, 5, 'Great! Can we also add photo compression before upload? Save bandwidth.', '2024-12-10 14:00:00'),
(29, 4, 'Good idea! Will compress to 80% quality and max 1080p resolution.', '2024-12-11 09:00:00'),

(31, 4, 'WebSocket connection is stable. Implemented automatic reconnection with exponential backoff.', '2024-12-12 11:00:00'),
(31, 2, 'Excellent! Make sure to handle background/foreground transitions properly.', '2024-12-12 14:00:00'),
(31, 4, 'Yes, added app lifecycle handling. Reconnects when app comes to foreground.', '2024-12-13 09:00:00'),

(34, 4, 'Biometric auth is working perfectly on both platforms! Very smooth user experience.', '2024-12-14 11:00:00'),
(34, 2, 'Great work! This will really improve user retention.', '2024-12-14 14:00:00'),

-- Project 3: API Integration Comments
(45, 4, 'JWT implementation is working. Access tokens expire in 15 min, refresh tokens in 7 days.', '2024-12-15 16:00:00'),
(45, 3, 'Perfect timing! Can you also add support for API key authentication for server-to-server calls?', '2024-12-16 09:00:00'),
(45, 4, 'Good idea. I\'ll add that this week. Should we store API keys hashed like passwords?', '2024-12-16 11:00:00'),
(45, 3, 'Yes definitely! Use bcrypt with high cost factor. Also implement key rotation capability.', '2024-12-16 14:00:00'),

(46, 6, 'Swagger documentation is looking great! Added interactive examples for all endpoints.', '2024-12-18 11:00:00'),
(46, 3, 'Excellent! Make sure to include error response examples too.', '2024-12-18 14:00:00'),
(46, 6, 'Done! Added example responses for 200, 400, 401, 403, 404, and 500 status codes.', '2024-12-19 09:00:00'),

(47, 4, 'Implemented JSON Schema validation for all POST/PUT endpoints. Very strict validation.', '2024-12-20 11:00:00'),
(47, 3, 'Perfect! Clear error messages are crucial for API adoption.', '2024-12-20 14:00:00'),

(48, 7, 'Redis caching is working well. Average response time reduced from 250ms to 45ms!', '2024-12-22 11:00:00'),
(48, 3, 'Wow, that\'s impressive! What\'s the cache hit rate?', '2024-12-22 14:00:00'),
(48, 7, 'Currently at 78% hit rate for GET requests. Still tuning the TTL values.', '2024-12-23 09:00:00'),

(51, 3, 'API architecture document is complete. Following REST best practices.', '2024-12-15 10:00:00'),
(51, 2, 'Looks solid! I like the versioning strategy.', '2024-12-15 12:00:00'),

-- Project 4: Marketing Campaign Comments
(61, 5, 'Created content calendar for next 3 months. 4 posts per week on each platform.', '2024-12-18 10:00:00'),
(61, 4, 'Great! What\'s the split between educational, promotional, and engagement content?', '2024-12-18 14:00:00'),
(61, 5, 'Following 80-20 rule: 80% value/education, 20% promotional. Also planning 2 engagement campaigns per month.', '2024-12-19 09:00:00'),
(61, 8, 'I can help with the graphic designs. Should match our brand guidelines right?', '2024-12-19 11:00:00'),
(61, 5, 'Yes! Using our design system colors and fonts. I will share the Figma file with templates.', '2024-12-19 14:00:00'),

(62, 3, 'Email templates are designed! Created 3 variants for A/B testing.', '2024-12-20 11:00:00'),
(62, 4, 'Looking good! Make sure they render well in Outlook. That\'s always tricky.', '2024-12-20 14:00:00'),
(62, 3, 'Yes, tested in Litmus across 40+ email clients. All looking good!', '2024-12-21 09:00:00'),

(63, 3, 'Landing pages are optimized! Improved conversion rate from 2.3% to 4.1% in tests.', '2024-12-22 11:00:00'),
(63, 4, 'That\'s a huge improvement! What changes made the biggest impact?', '2024-12-22 14:00:00'),
(63, 3, 'Clearer CTAs, reduced form fields, and added social proof. Social proof was the game changer!', '2024-12-23 09:00:00'),

(67, 5, 'Market research is complete. Found some really interesting insights about our target audience.', '2024-12-19 11:00:00'),
(67, 4, 'Excellent! Let\'s schedule a meeting to review the findings.', '2024-12-19 14:00:00'),

-- Project 6: Customer Portal Comments
(93, 3, 'Ticket system is taking shape. Implemented priority levels and auto-assignment rules.', '2024-12-24 11:00:00'),
(93, 5, 'Great! Can we also add SLA tracking with automatic escalation?', '2024-12-24 14:00:00'),
(93, 3, 'Absolutely! Will add SLA timers and automated notifications.', '2024-12-25 09:00:00'),

(94, 3, 'Dashboard mockups are ready for review. Added quick action buttons for common tasks.', '2024-12-26 11:00:00'),
(94, 5, 'Looks great! The layout is very intuitive.', '2024-12-26 14:00:00'),

(95, 6, 'Document upload is working with virus scanning via ClamAV. Max file size 25MB.', '2024-12-31 11:00:00'),
(95, 5, 'Perfect! Make sure to whitelist common document formats.', '2024-12-31 14:00:00'),

(96, 3, 'Email notifications are working! Using customizable templates with brand colors.', '2025-01-02 11:00:00'),
(96, 5, 'Excellent! Can users customize their notification preferences?', '2025-01-02 14:00:00'),
(96, 3, 'Yes! Added settings page for email/in-app notification preferences.', '2025-01-03 09:00:00'),

(99, 3, 'Design mockups got great feedback from user testing! Making final refinements.', '2024-12-27 11:00:00'),
(99, 5, 'That\'s great news! What did users like most?', '2024-12-27 14:00:00'),
(99, 3, 'They loved the clean layout and easy navigation. Some requested dark mode though.', '2024-12-28 09:00:00'),

-- Project 7: Security Audit Comments
(105, 6, 'Penetration testing revealed 3 medium-severity vulnerabilities. Nothing critical.', '2024-09-12 11:00:00'),
(105, 1, 'Good! Document all findings with reproduction steps.', '2024-09-12 14:00:00'),

(107, 1, 'Code review found some SQL injection vulnerabilities in legacy code. Creating tickets.', '2024-09-22 11:00:00'),
(107, 6, 'I can help fix those. Should be straightforward with parameterized queries.', '2024-09-22 14:00:00'),

(111, 1, 'GDPR compliance check is complete. Found a few data retention policy gaps.', '2024-10-08 11:00:00'),
(111, 6, 'Let\'s prioritize fixing those. Compliance is critical.', '2024-10-08 14:00:00'),

(114, 6, 'All critical vulnerabilities are fixed and deployed to production!', '2024-10-27 11:00:00'),
(114, 1, 'Excellent work! That was fast.', '2024-10-27 14:00:00');

-- ============================================================
-- Insert Activity Log (80+ comprehensive activity entries)
-- ============================================================
INSERT INTO activity_log (user_id, project_id, task_id, action, description, created_at) VALUES
-- Project creations
(2, 1, NULL, 'project_created', 'Created project "Website Redesign"', '2024-12-01 09:00:00'),
(2, 2, NULL, 'project_created', 'Created project "Mobile App Development"', '2024-11-15 10:30:00'),
(3, 3, NULL, 'project_created', 'Created project "API Integration Platform"', '2024-12-10 14:00:00'),
(4, 4, NULL, 'project_created', 'Created project "Marketing Campaign Q1 2025"', '2024-12-15 11:00:00'),
(2, 5, NULL, 'project_created', 'Created project "Data Migration Project"', '2024-10-01 08:00:00'),
(5, 6, NULL, 'project_created', 'Created project "Customer Portal Redesign"', '2024-12-20 10:00:00'),
(1, 7, NULL, 'project_created', 'Created project "Security Audit Q4"', '2024-09-01 09:00:00'),

-- Team member additions for Project 1
(2, 1, NULL, 'member_added', 'Added Sarah Johnson to project team', '2024-12-01 10:00:00'),
(2, 1, NULL, 'member_added', 'Added Mike Chen to project team', '2024-12-01 10:30:00'),
(2, 1, NULL, 'member_added', 'Added Emma Wilson to project team', '2024-12-02 09:00:00'),
(2, 1, NULL, 'member_added', 'Added Alex Brown to project team', '2024-12-02 14:00:00'),
(2, 1, NULL, 'member_added', 'Added Liu Chen to project team', '2024-12-03 09:00:00'),

-- Website Redesign task activities
(2, 1, 1, 'task_created', 'Created task "Design Homepage Layout"', '2024-12-15 10:00:00'),
(2, 1, 1, 'task_assigned', 'Assigned task to Sarah Johnson', '2024-12-15 10:01:00'),
(3, 1, 1, 'comment_added', 'Added comment to task', '2024-12-15 15:00:00'),
(5, 1, 1, 'comment_added', 'Added comment to task', '2024-12-16 10:00:00'),

(2, 1, 8, 'task_created', 'Created task "Develop Backend API Endpoints"', '2024-12-12 09:00:00'),
(2, 1, 8, 'task_status_changed', 'Moved task to In Progress', '2024-12-12 09:30:00'),
(4, 1, 8, 'comment_added', 'Added comment with progress update', '2024-12-13 11:00:00'),
(4, 1, 8, 'comment_added', 'Added implementation details', '2024-12-14 10:00:00'),

(2, 1, 12, 'task_created', 'Created task "Mobile Responsive Testing"', '2024-12-16 10:00:00'),
(2, 1, 12, 'task_priority_changed', 'Changed priority to High', '2024-12-16 11:00:00'),
(5, 1, 12, 'comment_added', 'Added testing results', '2024-12-17 16:00:00'),
(5, 1, 12, 'comment_added', 'Reported issue found', '2024-12-18 10:00:00'),
(5, 1, 12, 'comment_added', 'All issues resolved', '2024-12-18 15:00:00'),

(2, 1, 13, 'task_created', 'Created task "Performance Optimization"', '2024-12-17 12:00:00'),
(6, 1, 13, 'task_assigned', 'Task assigned to Alex Brown', '2024-12-17 12:01:00'),
(6, 1, 13, 'comment_added', 'Image optimization complete', '2024-12-19 14:00:00'),
(6, 1, 13, 'comment_added', 'Bundle size reduced significantly', '2024-12-19 17:00:00'),

(2, 1, 16, 'task_created', 'Created task "Setup Project Repository"', '2024-12-01 09:00:00'),
(2, 1, 16, 'task_completed', 'Marked task as completed', '2024-12-05 16:00:00'),

(2, 1, 17, 'task_created', 'Created task "Research Phase Complete"', '2024-12-02 10:00:00'),
(5, 1, 17, 'task_assigned', 'Task assigned to Emma Wilson', '2024-12-02 10:01:00'),
(5, 1, 17, 'task_completed', 'Completed research phase', '2024-12-08 17:30:00'),

(2, 1, 18, 'task_created', 'Created task "Choose Tech Stack"', '2024-12-03 11:00:00'),
(2, 1, 18, 'task_completed', 'Tech stack finalized', '2024-12-10 15:00:00'),

-- Mobile App activities
(2, 2, NULL, 'member_added', 'Added Mike Chen to project team', '2024-11-15 11:00:00'),
(2, 2, NULL, 'member_added', 'Added Emma Wilson to project team', '2024-11-16 09:00:00'),
(2, 2, NULL, 'member_added', 'Added Rachel Green to project team', '2024-11-17 10:00:00'),

(2, 2, 27, 'task_created', 'Created task "iOS App Development Setup"', '2024-12-01 10:00:00'),
(2, 2, 27, 'task_status_changed', 'Moved task to In Progress', '2024-12-02 09:00:00'),
(4, 2, 27, 'comment_added', 'Project setup complete', '2024-12-03 11:00:00'),

(2, 2, 28, 'task_created', 'Created task "Android App Development Setup"', '2024-12-01 10:30:00'),
(5, 2, 28, 'task_status_changed', 'Moved task to In Progress', '2024-12-02 09:00:00'),

(2, 2, 34, 'task_created', 'Created task "User Authentication Flow"', '2024-11-20 10:00:00'),
(4, 2, 34, 'task_assigned', 'Task assigned to Mike Chen', '2024-11-20 10:01:00'),
(4, 2, 34, 'task_completed', 'Authentication implemented', '2024-12-15 18:00:00'),

(2, 2, 35, 'task_created', 'Created task "Design System Implementation"', '2024-11-22 09:00:00'),
(8, 2, 35, 'task_assigned', 'Task assigned to Rachel Green', '2024-11-22 09:01:00'),
(8, 2, 35, 'task_completed', 'Design system ready', '2024-12-10 16:00:00'),

-- API Integration activities
(3, 3, NULL, 'member_added', 'Added John Doe to project team', '2024-12-10 15:00:00'),
(3, 3, NULL, 'member_added', 'Added Mike Chen to project team', '2024-12-11 09:00:00'),
(3, 3, NULL, 'member_added', 'Added Alex Brown to project team', '2024-12-11 10:00:00'),
(3, 3, NULL, 'member_added', 'Added Liu Chen to project team', '2024-12-12 09:00:00'),

(3, 3, 45, 'task_created', 'Created task "Implement Authentication System"', '2024-12-11 10:00:00'),
(4, 3, 45, 'task_assigned', 'Task assigned to Mike Chen', '2024-12-11 10:01:00'),
(3, 3, 45, 'task_status_changed', 'Moved task to In Progress', '2024-12-12 09:00:00'),
(4, 3, 45, 'comment_added', 'JWT implementation working', '2024-12-15 16:00:00'),

(3, 3, 51, 'task_created', 'Created task "Design API Architecture"', '2024-12-10 15:00:00'),
(3, 3, 51, 'task_completed', 'API architecture finalized', '2024-12-15 17:00:00'),

(3, 3, 52, 'task_created', 'Created task "Database Schema for API"', '2024-12-11 09:00:00'),
(4, 3, 52, 'task_completed', 'Database schema implemented', '2024-12-18 16:00:00'),

-- Marketing Campaign activities
(4, 4, NULL, 'member_added', 'Added Sarah Johnson to project team', '2024-12-15 12:00:00'),
(4, 4, NULL, 'member_added', 'Added Emma Wilson to project team', '2024-12-16 09:00:00'),
(4, 4, NULL, 'member_added', 'Added Rachel Green to project team', '2024-12-17 10:00:00'),

(4, 4, 61, 'task_created', 'Created task "Social Media Strategy"', '2024-12-16 10:00:00'),
(5, 4, 61, 'task_assigned', 'Task assigned to Emma Wilson', '2024-12-16 10:01:00'),
(4, 4, 61, 'task_status_changed', 'Moved task to In Progress', '2024-12-17 09:00:00'),
(5, 4, 61, 'comment_added', 'Content calendar created', '2024-12-18 10:00:00'),

(4, 4, 67, 'task_created', 'Created task "Market Research Analysis"', '2024-12-15 12:00:00'),
(5, 4, 67, 'task_assigned', 'Task assigned to Emma Wilson', '2024-12-15 12:01:00'),
(5, 4, 67, 'task_completed', 'Research completed', '2024-12-20 17:00:00'),

-- Data Migration activities
(2, 5, NULL, 'member_added', 'Added Mike Chen to project team', '2024-10-01 09:00:00'),

(2, 5, 72, 'task_created', 'Created task "Migration Strategy Planning"', '2024-10-01 09:00:00'),
(2, 5, 72, 'task_completed', 'Strategy approved', '2024-10-05 17:00:00'),

(2, 5, 82, 'task_created', 'Created task "Production Migration Execution"', '2024-10-25 10:00:00'),
(2, 5, 82, 'task_completed', 'Migration successful!', '2024-11-15 23:00:00'),

(2, 5, 86, 'task_created', 'Created task "Final Documentation"', '2024-11-25 09:00:00'),
(2, 5, 86, 'task_completed', 'Project complete', '2024-11-30 16:00:00'),

-- Customer Portal activities
(5, 6, NULL, 'member_added', 'Added Sarah Johnson to project team', '2024-12-20 11:00:00'),
(5, 6, NULL, 'member_added', 'Added Alex Brown to project team', '2024-12-21 09:00:00'),

(5, 6, 93, 'task_created', 'Created task "Ticket Management System"', '2024-12-21 10:00:00'),
(5, 6, 93, 'task_status_changed', 'Moved task to In Progress', '2024-12-22 09:00:00'),
(3, 6, 93, 'comment_added', 'Ticket system progress update', '2024-12-24 11:00:00'),

(5, 6, 99, 'task_created', 'Created task "Portal Design Mockups"', '2024-12-20 11:00:00'),
(3, 6, 99, 'task_assigned', 'Task assigned to Sarah Johnson', '2024-12-20 11:01:00'),
(3, 6, 99, 'task_completed', 'Mockups approved', '2024-12-28 17:00:00'),

(5, 6, 100, 'task_created', 'Created task "User Authentication System"', '2024-12-21 09:00:00'),
(6, 6, 100, 'task_completed', 'Auth system complete', '2025-01-05 16:00:00'),

-- Security Audit activities
(1, 7, NULL, 'member_added', 'Added Alex Brown to project team', '2024-09-02 10:00:00'),

(1, 7, 104, 'task_created', 'Created task "Security Audit Planning"', '2024-09-01 10:00:00'),
(1, 7, 104, 'task_completed', 'Audit plan finalized', '2024-09-05 17:00:00'),

(1, 7, 105, 'task_created', 'Created task "Penetration Testing - Web"', '2024-09-03 09:00:00'),
(6, 7, 105, 'task_assigned', 'Task assigned to Alex Brown', '2024-09-03 09:01:00'),
(6, 7, 105, 'task_completed', 'Web pen test complete', '2024-09-15 18:00:00'),

(1, 7, 117, 'task_created', 'Created task "Security Audit Report"', '2024-10-10 09:00:00'),
(1, 7, 117, 'task_completed', 'Report finalized', '2024-11-15 18:00:00'),

(1, 7, 119, 'task_created', 'Created task "Security Improvement Roadmap"', '2024-10-20 11:00:00'),
(1, 7, 119, 'task_completed', 'Roadmap approved', '2024-11-30 16:00:00'),
(1, 7, NULL, 'project_archived', 'Project archived after completion', '2024-11-30 17:00:00');

-- ============================================================
-- Demo Data Complete!
-- ============================================================
-- Demo Login Credentials:
--
-- Admin:   admin@taskflow.com / password123
-- User 1:  john@taskflow.com / password123
-- User 2:  sarah@taskflow.com / password123
-- User 3:  mike@taskflow.com / password123
-- User 4:  emma@taskflow.com / password123
-- User 5:  alex@taskflow.com / password123
-- User 6:  liu@taskflow.com / password123
-- User 7:  rachel@taskflow.com / password123
--
-- COMPREHENSIVE DEMO DATA SUMMARY:
-- ================================
-- Projects: 7 total
--   - 4 Active projects (Website, Mobile App, API, Marketing, Customer Portal)
--   - 1 Completed project (Data Migration)
--   - 1 Archived project (Security Audit)
--
-- Tasks: 119 total (distributed across all projects)
--   - Project 1 (Website Redesign): 20 tasks (7 TODO, 8 In Progress, 5 Completed)
--   - Project 2 (Mobile App): 18 tasks (6 TODO, 7 In Progress, 5 Completed)
--   - Project 3 (API Integration): 17 tasks (6 TODO, 6 In Progress, 5 Completed)
--   - Project 4 (Marketing Campaign): 16 tasks (5 TODO, 6 In Progress, 5 Completed)
--   - Project 5 (Data Migration): 15 tasks (All Completed)
--   - Project 6 (Customer Portal): 17 tasks (6 TODO, 6 In Progress, 5 Completed)
--   - Project 7 (Security Audit): 16 tasks (All Completed - Archived)
--
-- Users: 8 demo users with realistic profiles
--
-- Comments: 87 comments showing rich team collaboration
--   - Realistic conversations and progress updates
--   - Technical discussions and problem-solving
--   - Team coordination and feedback
--
-- Activity Log: 95 entries tracking complete project history
--   - Project creation and team building
--   - Task lifecycle tracking
--   - Status changes and completions
--   - Comment additions and assignments
--
-- Features Demonstrated:
-- =====================
-- Project Management:
--   - Multiple project statuses (Active, Completed, Archived)
--   - Various team sizes and compositions
--   - Date tracking (start, end, created)
--
-- Task Management:
--   - All task statuses (TODO, In Progress, Completed)
--   - All priority levels (High, Medium, Low)
--   - Comprehensive descriptions
--   - Due date tracking
--   - Task assignments
--   - Position/ordering within projects
--
-- Collaboration:
--   - Rich commenting system
--   - Activity tracking
--   - Team member roles (Owner, Member)
--   - Cross-project team participation
--
-- Real-world Scenarios:
--   - Active development projects with mixed task statuses
--   - Completed project showing full lifecycle
--   - Archived project demonstrating project closure
--   - Realistic task descriptions and technical details
--   - Professional team collaboration patterns
--
-- Note: Demo users (IDs 1-8) and demo projects (IDs 1-7) are
-- protected from deletion to maintain demo integrity.
-- ============================================================
