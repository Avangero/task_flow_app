<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #f8f9fa; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
        .header h2 { margin: 0; font-size: 24px; font-weight: 600; }
        .content { padding: 30px; }
        .task-info { background: #f8f9fa; padding: 20px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #667eea; }
        .task-title { font-weight: 600; color: #2c3e50; margin-bottom: 8px; }
        .footer { color: #6c757d; font-size: 14px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>{{ __('mail.task_assigned.title') }}</h2>
        </div>
        <div class="content">
            <p>{{ __('mail.hello') }}, {{ $task->assignee->first_name }} {{ $task->assignee->last_name }}!</p>

            <div class="task-info">
                <div class="task-title">{{ $task->title }}</div>
                <p>{{ __('mail.task_assigned.text', ['title' => $task->title]) }}</p>
            </div>

            @if ($previousAssignee)
                <p style="color: #6c757d; font-size: 14px;">{{ __('mail.task_assigned.previous_assignee', ['name' => $previousAssignee->first_name . ' ' . $previousAssignee->last_name]) }}</p>
            @endif

            <div class="footer">
                <p>{{ __('mail.thanks') }}</p>
            </div>
        </div>
    </div>
</body>
</html>
