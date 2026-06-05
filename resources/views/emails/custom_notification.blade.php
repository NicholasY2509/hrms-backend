<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<style>
  body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #F3F4F6; margin: 0; padding: 0; }
  .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
  .header { background-color: #3730A3; padding: 40px; color: #ffffff; text-align: left; }
  .header h1 { margin: 0; font-size: 26px; font-weight: bold; color: #ffffff; }
  .header p { margin: 8px 0 0 0; font-size: 15px; color: #E0E7FF; }
  .content { padding: 40px; color: #374151; }
  .greeting-container { margin-bottom: 20px; }
  .avatar { width: 48px; height: 48px; background-color: #EEF2FF; border-radius: 50%; text-align: center; line-height: 48px; color: #3730A3; font-weight: bold; font-size: 20px; }
  .message { font-size: 15px; line-height: 1.6; color: #4B5563; margin-top: 20px; margin-bottom: 30px; }
  .panel { background-color: #F9FAFB; border-radius: 8px; padding: 20px; margin-bottom: 30px; border: 1px solid #E5E7EB; }
  .button-container { text-align: center; margin-top: 35px; margin-bottom: 35px; }
  .button { background-color: #3730A3; color: #ffffff; padding: 14px 32px; border-radius: 8px; text-decoration: none; font-weight: bold; display: inline-block; font-size: 15px; }
  .subcopy { text-align: center; font-size: 12px; color: #6B7280; margin-top: 30px; border-top: 1px solid #E5E7EB; padding-top: 20px; line-height: 1.5; }
  .subcopy a { color: #3730A3; word-break: break-all; }
  .footer { text-align: center; padding: 25px; color: #9CA3AF; font-size: 12px; line-height: 1.5; }
</style>
</head>
<body style="background-color: #F3F4F6; padding: 20px;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #F3F4F6;">
    <tr>
      <td align="center">
        <!-- Main Card -->
        <table class="container" width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; margin: 20px auto; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); text-align: left;">
          
          <!-- Header Banner -->
          <tr>
            <td class="header" style="background-color: #3730A3; padding: 40px;">
              <table width="100%">
                <tr>
                  <td>
                    <!-- Add your logo here if needed -->
                    <h1 style="margin: 0; font-size: 26px; font-weight: bold; color: #ffffff;">
                      {{ $data['title'] ?? 'Notification' }}
                    </h1>
                    @if(isset($data['type']) && str_contains($data['type'], 'approval'))
                      <p style="margin: 8px 0 0 0; font-size: 15px; color: #E0E7FF;">A new request requires your attention.</p>
                    @endif
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          
          <!-- Body Content -->
          <tr>
            <td class="content" style="padding: 40px;">
              
              <!-- Greeting & Avatar -->
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td width="60" valign="middle">
                    <div class="avatar" style="width: 48px; height: 48px; background-color: #EEF2FF; border-radius: 50%; text-align: center; line-height: 48px; color: #3730A3; font-weight: bold; font-size: 20px;">
                      {{ strtoupper(substr($name, 0, 1)) }}
                    </div>
                  </td>
                  <td valign="middle">
                    <h2 style="margin: 0; font-size: 20px; font-weight: bold; color: #111827;">Halo, {{ $name }} 👋</h2>
                  </td>
                </tr>
              </table>

              <!-- Message Details -->
              <div class="panel" style="background-color: #F9FAFB; border-radius: 8px; padding: 20px; margin-top: 30px; margin-bottom: 30px; border: 1px solid #E5E7EB;">
                <p class="message" style="margin: 0; font-size: 15px; line-height: 1.6; color: #4B5563;">
                  {!! nl2br(e($data['message'] ?? 'Anda memiliki pemberitahuan baru.')) !!}
                </p>
              </div>

              <!-- Call to Action -->
              <div class="button-container" style="text-align: center; margin-top: 35px; margin-bottom: 35px;">
                <a href="{{ $detailUrl }}" class="button" style="background-color: #3730A3; color: #ffffff; padding: 14px 32px; border-radius: 8px; text-decoration: none; font-weight: bold; display: inline-block; font-size: 15px;">
                  Lihat Detail &rarr;
                </a>
              </div>

              <!-- Subcopy Link -->
              <div class="subcopy" style="text-align: center; font-size: 12px; color: #6B7280; margin-top: 30px; border-top: 1px solid #E5E7EB; padding-top: 20px; line-height: 1.5;">
                If the button doesn't work, copy and paste this link in your browser:<br>
                <a href="{{ $detailUrl }}" style="color: #3730A3;">{{ $detailUrl }}</a>
              </div>

            </td>
          </tr>
        </table>
        
        <!-- Footer -->
        <table width="600" cellpadding="0" cellspacing="0" style="margin: 0 auto;">
          <tr>
            <td class="footer" align="center" style="padding: 25px; color: #9CA3AF; font-size: 12px; line-height: 1.5;">
              &copy; {{ date('Y') }} Deltamas HRMS. All rights reserved.<br>
              This is an automated email. Please do not reply.
            </td>
          </tr>
        </table>
        
      </td>
    </tr>
  </table>
</body>
</html>
