<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Código de verificação</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f8fafc; margin: 0; padding: 0; }
        .wrapper { max-width: 520px; margin: 40px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .header { background: #ffffff; padding: 28px 40px; text-align: center; border-bottom: 4px solid #7c3aed; }
        .header img { max-width: 200px; height: auto; display: block; margin: 0 auto; }
        .body { padding: 36px 40px; }
        .greeting { font-size: 15px; color: #374151; margin-bottom: 16px; }
        .code-box { background: #f3f4f6; border-radius: 10px; padding: 24px; text-align: center; margin: 24px 0; }
        .code { font-size: 40px; font-weight: 900; letter-spacing: 10px; color: #7c3aed; font-family: 'Courier New', monospace; }
        .expiry { font-size: 13px; color: #6b7280; margin-top: 8px; }
        .info { font-size: 13px; color: #6b7280; line-height: 1.6; margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb; }
        .footer { background: #f9fafb; padding: 20px 40px; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <img src="{{ $message->embed(public_path('logo/logo.png')) }}" alt="Guia Comercial" width="200" height="44">
        </div>
        <div class="body">
            <p class="greeting">Olá, <strong>{{ $otp->gestor_name }}</strong>!</p>
            <p style="font-size:15px;color:#374151;">
                Você está cadastrando a empresa <strong>{{ $otp->company_name }}</strong> no Guia Comercial.
                Use o código abaixo para confirmar seu cadastro:
            </p>

            <div class="code-box">
                <div class="code">{{ $otp->code }}</div>
                <div class="expiry">Válido por <strong>10 minutos</strong></div>
            </div>

            <p style="font-size:14px;color:#374151;">
                O mesmo código foi enviado para o seu WhatsApp. Você pode usar qualquer um deles para confirmar.
            </p>

            <div class="info">
                <strong>Não solicitou este cadastro?</strong><br>
                Ignore este e-mail. Nenhuma conta será criada sem a verificação.
                Não compartilhe este código com ninguém.
            </div>
        </div>
        <div class="footer">
            © {{ date('Y') }} Guia Comercial · Este é um e-mail automático, não responda.
        </div>
    </div>
</body>
</html>
