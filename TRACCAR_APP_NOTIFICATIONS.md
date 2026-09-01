# Traccar Mobile App Notification System

## Overview

A new notification system has been implemented to send in-app notifications directly to the Traccar mobile app. This complements the existing SMS and Email notification channels.

## Features

### Who Can Send Notifications

- **LYDO** (Youth Profiling Admin)
- **SK (Sangguniang Kabataan)** - SK Chairmen
- **Opportunity Providers** (Employers, Training Providers)

### Notification Types

- `general` - General system notification (default)
- `opportunity` - Job/training opportunity notification
- `alert` - System alert notification
- `reminder` - Reminder notification

## API Endpoints

### 1. Send Traccar App Notification

**Endpoint:** `POST /api/send_traccar_notification.php`

**Parameters:**

```php
POST /api/send_traccar_notification.php
{
    "recipient_ids[]": [1, 2, 3],  // Array of OSY profile IDs
    "title": "New Job Opportunity",
    "message": "A new welding position is available in your area",
    "type": "opportunity",  // optional, default: general
    "form_nonce": "CSRF_TOKEN"
}
```

**Response:**

```json
{
  "success": true,
  "message": "Notifications sent to 3 recipients",
  "sent": 3,
  "failed": 0
}
```

### 2. Get Traccar App Notifications

**Endpoint:** `GET /api/get_traccar_notifications.php`

**Parameters:**

```
GET /api/get_traccar_notifications.php?recipient_id=5&unread_only=1&limit=50
```

**Query String Parameters:**

- `recipient_id` (required): OSY profile ID
- `unread_only` (optional): Set to `1` to get only unread notifications
- `limit` (optional): Max number of notifications to return (default: 50, max: 100)

**Response:**

```json
{
  "success": true,
  "message": "Notifications retrieved successfully",
  "data": [
    {
      "id": 1,
      "sender_id": 42,
      "sender_role": "lydo",
      "sender_name": "John Doe",
      "recipient_id": 5,
      "title": "New Job Opportunity",
      "message": "A new welding position is available",
      "notification_type": "opportunity",
      "is_read": false,
      "read_at": null,
      "created_at": "2026-09-01 14:30:45"
    }
  ],
  "count": 1,
  "unread_count": 3
}
```

### 3. Mark Notification as Read

**Endpoint:** `POST /api/mark_traccar_notification_read.php`

**Parameters (mark single notification):**

```php
POST /api/mark_traccar_notification_read.php
{
    "notification_id": 1
}
```

**Parameters (mark all as read):**

```php
POST /api/mark_traccar_notification_read.php
{
    "recipient_id": 5,
    "mark_all": 1
}
```

**Response:**

```json
{
  "success": true,
  "message": "Notification marked as read."
}
```

## Integration with Existing Notification System

When sending notifications through the standard notification interface (Notifications > Send New Notification), if:

1. The "In-system notification" checkbox is enabled
2. The sender is LYDO, SK Chairman, or Provider
3. Recipients are selected

Then notifications will **automatically** be sent to:

- System database (in-app notifications)
- Traccar app notifications (if available)
- SMS/Email (if those options are selected)

## Traccar Mobile App Integration

For the Traccar mobile app to display these notifications, add a notification panel that:

1. **Fetches notifications** every 30 seconds using:

   ```
   GET /api/get_traccar_notifications.php?recipient_id={USER_OSY_ID}&unread_only=1
   ```

2. **Marks as read** when user views:

   ```
   POST /api/mark_traccar_notification_read.php
   {"notification_id": {ID}}
   ```

3. **Displays** notification with:
   - Title
   - Message
   - Sender name and role
   - Creation timestamp
   - Read/Unread status

## Database Schema

```sql
CREATE TABLE traccar_notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sender_id INT NOT NULL,
    sender_role VARCHAR(50) NOT NULL,
    sender_name VARCHAR(255),
    recipient_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message LONGTEXT NOT NULL,
    notification_type VARCHAR(50) DEFAULT 'general',
    is_read BOOLEAN DEFAULT FALSE,
    read_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_recipient (recipient_id),
    INDEX idx_created (created_at),
    INDEX idx_read_status (is_read),
    FOREIGN KEY (recipient_id) REFERENCES osy_profiles(id) ON DELETE CASCADE
);
```

## Sample Usage

### Send notification from LYDO to specific youth

```php
<?php
require_once 'init.php';
require_once 'Classes/TraccarNotificationService.php';

$traccarNotif = new TraccarNotificationService($database);

// Send from LYDO (user ID 42)
$result = $traccarNotif->sendFromLydo(
    42,                              // Sender ID
    [1, 2, 3],                      // Recipient IDs
    "Job Placement Update",          // Title
    "Your profile has been matched with an employer", // Message
    "opportunity"                    // Type
);

if ($result['success']) {
    echo "Sent to {$result['sent']} recipients";
} else {
    echo "Error: {$result['message']}";
}
?>
```

### Get unread notifications for a youth

```php
<?php
require_once 'init.php';
require_once 'Classes/TraccarNotificationService.php';

$traccarNotif = new TraccarNotificationService($database);

// Get unread notifications for youth ID 5
$notifications = $traccarNotif->getUnreadNotifications(5, 50);

foreach ($notifications as $notif) {
    echo "From: {$notif['sender_name']} ({$notif['sender_role']})\n";
    echo "Title: {$notif['title']}\n";
    echo "Message: {$notif['message']}\n";
    echo "Time: {$notif['created_at']}\n\n";
}
?>
```

## Notes

- Notifications are stored indefinitely (but can be cleared manually with `clearOldNotifications()`)
- Notifications are NOT deleted when marked as read, only flagged as read
- Each API endpoint creates the database table automatically on first run
- SMS and email still work independently and can be combined with Traccar notifications
