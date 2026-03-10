# asttrolok_lms

LMS (Learning Management System) with Support Dashboard

## Features

### Support Dashboard (Triage.AI)
- Real-time dashboard metrics (open tickets, resolved today, response time, satisfaction)
- Interactive charts (ticket volume, scenario split)
- Pending tickets table with bulk actions
- Ticket workflow management (New, In Progress, On Hold)
- AJAX-based real-time updates
- Responsive design with modern CSS

### Technical Implementation
- Laravel Framework
- Chart.js for data visualization
- Support role authentication and permissions
- Bulk ticket operations
- Interactive ticket management interface

## Installation

1. Clone the repository
2. Install dependencies: `composer install`
3. Configure environment file
4. Run migrations: `php artisan migrate`
5. Start development server: `php artisan serve`

## Support Dashboard Access

Login URL: `http://localhost:8000/admin/support-dashboard`

Default Support Credentials:
- Email: support@gmail.com
- Password: 123456

## Recent Updates

- Added comprehensive support dashboard with Triage.AI UI design
- Implemented real-time metrics and charts
- Created interactive ticket management system
- Added bulk operations for ticket management
- Enhanced support role functionality
