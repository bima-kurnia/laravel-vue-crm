<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: sans-serif; color: #333; padding: 32px; }
    .btn {
      display: inline-block;
      padding: 12px 24px;
      background: #6366f1;
      color: #fff;
      text-decoration: none;
      border-radius: 8px;
      margin-top: 24px;
    }
    .footer { margin-top: 32px; font-size: 12px; color: #888; }
  </style>
</head>
<body>
  <h2>You have been invited</h2>
  <p>
    <strong>{{ $invitation->invitedBy->name }}</strong> has invited you to join
    <strong>{{ $invitation->tenant->name }}</strong> on CRM as a
    <strong>{{ $invitation->role }}</strong>.
  </p>
  
  <p>Click the button below to accept the invitation and create your account.</p>
  <a href="{{ $acceptUrl }}" class="btn">Accept invitation</a>

  <p class="footer">
    This invitation expires on {{ $invitation->expires_at->toFormattedDateString() }}.
    If you were not expecting this, you can safely ignore this email.
  </p>
</body>
</html>