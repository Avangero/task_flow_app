<?php

return [
    'hello' => 'Hello',
    'thanks' => 'Thank you!',

    'task_assigned' => [
        'subject' => 'Task assigned: :title',
        'title' => 'Task Assignment',
        'text' => 'You have been assigned to task ":title".',
        'previous_assignee' => 'Previous assignee: :name',
    ],

    'task_status_changed' => [
        'subject' => 'Task status changed: :title',
        'title' => 'Task Status Changed',
        'text' => 'Task ":title" status changed from ":old" to ":new".',
    ],

    'project_status_changed' => [
        'subject' => 'Project status changed: :name',
        'title' => 'Project Status Changed',
        'text' => 'Project ":name" status changed from ":old" to ":new".',
    ],
];
