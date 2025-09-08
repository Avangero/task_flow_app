<?php

return [
    // JWT ошибки
    'jwt' => [
        'token_expired' => 'Токен истек',
        'token_invalid' => 'Токен недействителен',
        'token_refresh_failed' => 'Не удалось обновить токен',
        'token_error' => 'Токен отсутствует',
        'token_missing' => 'Токен отсутствует',
    ],

    // HTTP ошибки
    'http' => [
        'not_found' => 'Ресурс не найден',
        'method_not_allowed' => 'Метод не разрешен',
        'validation_error' => 'Ошибка валидации данных',
        'unauthorized' => 'Неавторизованный доступ',
        'internal_server_error' => 'Внутренняя ошибка сервера',
    ],

    // Аутентификация
    'auth' => [
        'invalid_credentials' => 'Неверные учетные данные',
        'user_not_found' => 'Пользователь не найден',
        'registration_failed' => 'Ошибка при регистрации',
        'logout_success' => 'Успешный выход из системы',
        'login_success' => 'Успешная авторизация',
        'registration_success' => 'Успешная регистрация',
        'profile_retrieved' => 'Профиль получен',
        'token_refreshed' => 'Токен обновлен',
    ],

    // Общие сообщения
    'success' => 'Операция выполнена успешно',
    'error' => 'Произошла ошибка',
    'access_denied' => 'Доступ запрещен',
    'unauthorized' => 'Неавторизованный доступ',
];
