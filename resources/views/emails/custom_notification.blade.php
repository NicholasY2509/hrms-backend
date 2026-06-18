<!DOCTYPE html>
<html>

<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title>{{ $data['title'] ?? 'Notification' }}</title>
</head>

<body style="
  margin:0;
  padding:0;
  background-color:#F3F4F6;
  font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;
">

  <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#F3F4F6; padding:30px 15px;">
    <tr>
      <td align="center">

        <!-- Main Container -->
        <table width="600" cellpadding="0" cellspacing="0" style="
          width:100%;
          max-width:600px;
          background-color:#ffffff;
          border-radius:16px;
          overflow:hidden;
          box-shadow:0 8px 24px rgba(15,23,42,0.12);
        ">

          <!-- Banner Image -->
          <tr>
            <td>
              <img src="{{ $message->embed(public_path('images/email-banner.png')) }}" alt="Banner" width="600" style="
                  width:100%;
                  max-width:600px;
                  display:block;
                  border:0;
                ">
            </td>
          </tr>

          <!-- Header Content -->
          <tr>
            <td style="padding:40px 36px 20px 36px;">

              <!-- Logo -->
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td align="left">

                    <img src="{{ $message->embed(public_path('images/logo.png')) }}" alt="Deltamas HRMS" width="56" style="
                        display:block;
                        margin-bottom:24px;
                        border-radius:12px;
                      ">

                  </td>
                </tr>
              </table>

              <!-- Title -->
              <h1 style="
                margin:0;
                font-size:32px;
                line-height:1.2;
                font-weight:700;
                color:#111827;
              ">
                {{ $data['title'] ?? 'Notification' }}
              </h1>

              <!-- Subtitle -->
              @if(isset($data['subtitle']))
                <p
                  style="
                                                                                                                                                                                                                                                                                                      margin:14px 0 0 0;
                                                                                                                                                                                                                                                                                                      font-size:16px;
                                                                                                                                                                                                                                                                                                      line-height:1.7;
                                                                                                                                                                                                                                                                                                      color:#6B7280;
                                                                                                                                                                                                                                                                                                    ">
                  {{ $data['subtitle'] }}
                </p>
              @endif

            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding:0 36px 36px 36px;">

              <!-- Greeting -->
              <h2 style="
                margin:0 0 18px 0;
                font-size:20px;
                font-weight:700;
                color:#111827;
              ">
                Halo, <span style="color:#023e8a;">{{ $name }}</span>
              </h2>

              <!-- Message Panel -->
              <table width="100%" cellpadding="0" cellspacing="0" style="
                background-color:#F9FAFB;
                border:1px solid #E5E7EB;
                border-radius:12px;
              ">
                <tr>
                  <td style="padding:24px;">

                    <p style="
                      margin:0;
                      font-size:15px;
                      line-height:1.8;
                      color:#374151;
                    ">
                      {!! nl2br(e($data['message'] ?? 'Anda memiliki pemberitahuan baru.')) !!}
                    </p>

                  </td>
                </tr>
              </table>

              <!-- CTA Button -->
              <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:34px;">
                <tr>
                  <td align="center">

                    <a href="{{ $detailUrl }}" style="
                        background:linear-gradient(135deg,#023e8a,#2563EB);
                        color:#ffffff;
                        text-decoration:none;
                        padding:16px 34px;
                        border-radius:10px;
                        font-size:15px;
                        font-weight:700;
                        display:inline-block;
                      ">
                      View Details →
                    </a>

                  </td>
                </tr>
              </table>

              <!-- Divider -->
              <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:36px;">
                <tr>
                  <td style="
                    border-top:1px solid #E5E7EB;
                    font-size:0;
                    line-height:0;
                  ">
                    &nbsp;
                  </td>
                </tr>
              </table>

              <!-- Fallback URL -->
              <p style="
                margin:24px 0 0 0;
                text-align:center;
                font-size:12px;
                line-height:1.7;
                color:#6B7280;
              ">
                If the button above doesn’t work, copy and paste this link into your browser:
                <br>

                <a href="{{ $detailUrl }}" style="
                    color:#2563EB;
                    word-break:break-all;
                    text-decoration:none;
                  ">
                  {{ $detailUrl }}
                </a>
              </p>

            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="
              background-color:#F8FAFC;
              border-top:1px solid #E5E7EB;
              padding:28px 36px;
              text-align:center;
            ">

              <p style="
                margin:0;
                font-size:14px;
                font-weight:700;
                color:#111827;
              ">
                Deltamas HRMS
              </p>

              <p style="
                margin:10px 0 0 0;
                font-size:12px;
                line-height:1.7;
                color:#9CA3AF;
              ">
                © {{ date('Y') }} Deltamas HRMS. All rights reserved.
                <br>
                This is an automated email. Please do not reply.
              </p>

            </td>
          </tr>

        </table>

      </td>
    </tr>
  </table>

</body>

</html>