<?php

/**
 * API Structure Guide - Example Template
 * 
 * This folder will contain API endpoints for frontend consumption
 * Future API implementation for mobile apps and external integrations
 * 
 * Example endpoints to implement:
 */

/*
==============================================================================
USER AUTHENTICATION ENDPOINTS
==============================================================================

POST /api/users/login.php
{
    "username": "admin1",
    "password": "Admin@123"
}
Response:
{
    "success": true,
    "user": {
        "id": 1,
        "username": "admin1",
        "fullname": "Senior Administrator",
        "role": "admin"
    }
}

POST /api/users/logout.php
Response: { "success": true }

POST /api/users/register.php
{
    "username": "newuser",
    "email": "user@example.com",
    "password": "SecurePass123",
    "fullname": "New User",
    "role": "staff"
}
Response: { "success": true, "user_id": 10 }

==============================================================================
OSY PROFILE ENDPOINTS
==============================================================================

GET /api/osy/list.php?page=1&limit=10&status=Active&skill=Welding
Response: { "success": true, "data": [...], "total": 12 }

GET /api/osy/detail.php?id=1
Response: { "success": true, "data": {...} }

POST /api/osy/create.php
{
    "first_name": "Juan",
    "last_name": "Dela Cruz",
    "age": 20,
    "gender": "Male",
    "education_level": "High School Graduate",
    "barangay": "Barangay Poblacion",
    "primary_skill": "Welding",
    "status": "Active"
}
Response: { "success": true, "id": 13 }

PUT /api/osy/update.php?id=1
{ "status": "Employed" }
Response: { "success": true }

DELETE /api/osy/delete.php?id=1
Response: { "success": true }

==============================================================================
OPPORTUNITY ENDPOINTS
==============================================================================

GET /api/opportunities/list.php?type=Job Opening&status=Open
Response: { "success": true, "data": [...] }

GET /api/opportunities/detail.php?id=1
Response: { "success": true, "data": {...} }

POST /api/opportunities/create.php
{
    "title": "Welding Specialist",
    "type": "Vocational Training",
    "location": "TESDA Hub",
    "total_slots": 15,
    "deadline": "2024-12-31"
}
Response: { "success": true, "id": 9 }

PUT /api/opportunities/update.php?id=1
{ "status": "Closed" }
Response: { "success": true }

DELETE /api/opportunities/delete.php?id=1
Response: { "success": true }

==============================================================================
MATCHING ENDPOINTS
==============================================================================

GET /api/matches/for-osy.php?osy_id=1&min_score=75
Response: { "success": true, "data": [...] }

GET /api/matches/for-opportunity.php?opportunity_id=5&min_score=75
Response: { "success": true, "data": [...] }

POST /api/matches/calculate.php
{
    "osy_id": 1,
    "opportunity_id": 5
}
Response: { "success": true, "score": 88 }

POST /api/matches/generate.php?opportunity_id=5
Response: { "success": true, "created": 8 }

PUT /api/matches/update-status.php?match_id=1
{ "status": "Accepted" }
Response: { "success": true }

==============================================================================
NOTIFICATION ENDPOINTS
==============================================================================

GET /api/notifications/list.php?limit=20
Response: { "success": true, "data": [...] }

GET /api/notifications/detail.php?id=1
Response: { "success": true, "data": {...} }

POST /api/notifications/create.php
{
    "title": "New Opportunity",
    "message": "A new welding job is available",
    "type": "Opportunity",
    "recipient_type": "OSY"
}
Response: { "success": true, "id": 7 }

POST /api/notifications/broadcast.php
{
    "opportunity_id": 5,
    "title": "Matched Opportunity",
    "message": "You have been matched!"
}
Response: { "success": true }

DELETE /api/notifications/delete.php?id=1
Response: { "success": true }

==============================================================================
REPORT ENDPOINTS
==============================================================================

GET /api/reports/dashboard.php
Response:
{
    "success": true,
    "stats": {
        "total_osy": 12,
        "total_opportunities": 8,
        "total_matches": 48,
        "employment_rate": 33.33
    }
}

GET /api/reports/osy.php?filters=status:Active,skill:Welding
Response: { "success": true, "data": [...] }

GET /api/reports/opportunities.php
Response: { "success": true, "data": [...] }

GET /api/reports/matching-stats.php
Response:
{
    "success": true,
    "stats": {
        "total_matches": 48,
        "pending_matches": 25,
        "average_score": 82,
        "highest_score": 94
    }
}

POST /api/reports/export.php
{
    "type": "osy",
    "format": "csv"
}
Response: { "success": true, "filename": "osy_2024-04-06.csv" }

==============================================================================
DASHBOARD ENDPOINTS
==============================================================================

GET /api/dashboard/stats.php
Response:
{
    "success": true,
    "data": {
        "total_osy": 12,
        "active_osy": 8,
        "employed_osy": 4,
        "total_opportunities": 8,
        "total_matches": 48,
        "accepted_matches": 20,
        "pending_matches": 28
    }
}

GET /api/dashboard/recent.php
Response: { "success": true, "registrations": [...], "opportunities": [...] }

GET /api/dashboard/skills.php
Response: { "success": true, "data": [...] }

==============================================================================
ERROR RESPONSES
==============================================================================

401 Unauthorized
{ "success": false, "message": "Please log in first" }

403 Forbidden
{ "success": false, "message": "You don't have permission for this action" }

404 Not Found
{ "success": false, "message": "Resource not found" }

400 Bad Request
{ "success": false, "message": "Invalid input parameters" }

500 Server Error
{ "success": false, "message": "An error occurred on the server" }

==============================================================================
AUTHENTICATION
==============================================================================

All API endpoints should verify authentication via session:
- Check if $_SESSION['user_id'] exists
- Verify user role for authorization
- Return 401 if not authenticated
- Return 403 if lacking permissions

Sample Auth Check:
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

==============================================================================
RESPONSE FORMAT
==============================================================================

All API responses should follow this format:

Success:
{
    "success": true,
    "data": {...},      // optional
    "message": "..."    // optional
}

Error:
{
    "success": false,
    "message": "Error description"
}

Pagination:
{
    "success": true,
    "data": [...],
    "pagination": {
        "page": 1,
        "limit": 10,
        "total": 50,
        "pages": 5
    }
}

*/

// Future API Implementation Structure
class APIController
{
    protected $database;
    protected $user;

    public function __construct()
    {
        require_once '../init.php';
        global $database, $user;
        $this->database = $database;
        $this->user = $user;
    }

    protected function requireAuth()
    {
        if (!$this->user->isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
    }

    protected function requireRole($role)
    {
        if (!$this->user->hasRole($role)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Forbidden']);
            exit;
        }
    }

    protected function respond($success, $data = null, $message = null, $statusCode = 200)
    {
        http_response_code($statusCode);
        echo json_encode([
            'success' => $success,
            'data' => $data,
            'message' => $message
        ]);
        exit;
    }
}
