<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #f8f9fa; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 30px; text-align: center; }
        .header h2 { margin: 0; font-size: 24px; font-weight: 600; }
        .content { padding: 30px; }
        .status-change { background: #f8f9fa; padding: 20px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #28a745; }
        .task-title { font-weight: 600; color: #2c3e50; margin-bottom: 8px; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
        .status-old { background: #ffc107; color: #856404; }
        .status-new { background: #28a745; color: white; }
        .footer { color: #6c757d; font-size: 14px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>{{ __('mail.task_status_changed.title') }}</h2>
        </div>
        <div class="content">
            <p>{{ __('mail.hello') }}, {{ $recipient->first_name }} {{ $recipient->last_name }}!</p>

            <div class="status-change">
                <div class="task-title">{{ $task->title }}</div>
                <p>{{ __('mail.task_status_changed.text', ['title' => $task->title, 'old' => $oldStatus->value, 'new' => $newStatus->value]) }}</p>
                <div style="margin-top: 15px;">
                    <span class="status-badge status-old">{{ $oldStatus->value }}</span>
                    <span style="margin: 0 10px;">→</span>
                    <span class="status-badge status-new">{{ $newStatus->value }}</span>
                </div>
            </div>

            <div class="footer">
                <p>{{ __('mail.thanks') }}</p>
            </div>
        </div>
    </div>
</body>
</html>
