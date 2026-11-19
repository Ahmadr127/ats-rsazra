<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; color: #333; background: #f5f5f5; margin: 0; padding: 0; }
        .wrapper { max-width: 600px; margin: 0 auto; background: #fff; }
        .header { background: #007774; padding: 20px 30px; }
        .header .logo-box { background: #ffffff; border-radius: 10px; }
        .header .brand { color: #fff; font-size: 18px; font-weight: bold; }
        .content a { color: #007774; word-break: break-all; }
        .content { padding: 30px; white-space: pre-line; line-height: 1.7; }
        .footer { background: #f5f5f5; padding: 16px 30px; text-align: center; font-size: 12px; color: #999; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td class="logo-box" style="background:#ffffff;border-radius:10px;padding:5px 7px;">
                        <img
                            src="{{ $message->embed(public_path('images/logo.png')) }}"
                            alt="Logo RS Azra"
                            width="30"
                            height="33"
                            style="display:block;width:30px;height:33px;border:0;outline:none;text-decoration:none;"
                        >
                    </td>
                    <td style="padding-left:12px;color:#ffffff;font-size:18px;font-weight:bold;">
                        RS Azra
                    </td>
                </tr>
            </table>
        </div>
        <div class="content">{!! $bodyHtml ?? nl2br(e($body)) !!}</div>
        <div class="footer">
            Email ini dikirim otomatis oleh Sistem Rekrutmen RS Azra. Mohon tidak membalas email ini.
        </div>
    </div>
</body>
</html>
