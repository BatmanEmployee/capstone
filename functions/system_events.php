<?php

function generateSystemEvents($conn) {
    $today = date("Y-m-d");

    $system_events = [
        ['title' => 'Iftar', 'anchor' => 'maghrib'],
        ['title' => 'Taraweeh', 'anchor' => 'isha'],
        ['title' => 'Suhoor Reminder', 'anchor' => 'suhoor']
    ];

    $anchors = [
        'maghrib' => '18:00:00',
        'isha'    => '19:30:00',
        'suhoor'  => '04:30:00'
    ];

    foreach ($system_events as $event) {

        $title = $conn->real_escape_string($event['title']);
        $time = $anchors[$event['anchor']];

        // Check if already exists (GLOBAL)
        $check = $conn->query("
            SELECT id FROM events 
            WHERE title = '$title' 
            AND event_date = '$today' 
            AND event_type = 'system'
        ");

        if ($check->num_rows == 0) {

            // Insert GLOBAL event (community_id = NULL)
            $conn->query("
                INSERT INTO events 
                (title, description, event_date, event_time, venue, user_id, event_type, visibility, community_id)
                VALUES 
                ('$title', 'Auto-generated event', '$today', '$time', 'Mosque', 0, 'system', 'community', NULL)
            ");
        }
    }
}