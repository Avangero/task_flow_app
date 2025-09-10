<?php

return [
    'hello' => 'Здравствуйте',
    'thanks' => 'Спасибо!',

    'task_assigned' => [
        'subject' => 'Назначена задача: :title',
        'title' => 'Назначение задачи',
        'text' => 'Вы назначены исполнителем задачи ":title".',
        'previous_assignee' => 'Предыдущий исполнитель: :name',
    ],

    'task_status_changed' => [
        'subject' => 'Статус задачи изменен: :title',
        'title' => 'Изменение статуса задачи',
        'text' => 'Статус задачи ":title" изменен с ":old" на ":new".',
    ],

    'project_status_changed' => [
        'subject' => 'Статус проекта изменен: :name',
        'title' => 'Изменение статуса проекта',
        'text' => 'Статус проекта ":name" изменен с ":old" на ":new".',
    ],
];
