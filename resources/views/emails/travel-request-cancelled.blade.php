<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitação de Viagem Cancelada</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #dc3545;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 20px;
            border: 1px solid #dee2e6;
        }
        .footer {
            background-color: #6c757d;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 0 0 5px 5px;
            font-size: 12px;
        }
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
            margin: 15px 0;
        }
        .details {
            background-color: white;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        .detail-label {
            font-weight: bold;
            color: #495057;
        }
        .detail-value {
            color: #6c757d;
        }
        .note {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>❌ Solicitação de Viagem Cancelada</h1>
    </div>
    
    <div class="content">
        <p>Olá <strong>{{ $user->name }}</strong>,</p>
        
        <p>Sua solicitação de viagem foi <strong>cancelada</strong>.</p>
        
        <div class="status-cancelled">
            🚫 STATUS: CANCELADA
        </div>
        
        <div class="details">
            <h3>Detalhes da Viagem:</h3>
            
            <div class="detail-row">
                <span class="detail-label">ID da Solicitação:</span>
                <span class="detail-value">#{{ $travelRequest->id }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Destino da Viagem:</span>
                <span class="detail-value">{{ $travelRequest->name }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">País:</span>
                <span class="detail-value">{{ $travelRequest->country }}</span>
            </div>
            
            @if($travelRequest->town)
            <div class="detail-row">
                <span class="detail-label">Cidade:</span>
                <span class="detail-value">{{ $travelRequest->town }}</span>
            </div>
            @endif
            
            @if($travelRequest->state)
            <div class="detail-row">
                <span class="detail-label">Estado:</span>
                <span class="detail-value">{{ $travelRequest->state }}</span>
            </div>
            @endif
            
            @if($travelRequest->region)
            <div class="detail-row">
                <span class="detail-label">Região:</span>
                <span class="detail-value">{{ $travelRequest->region }}</span>
            </div>
            @endif
            
            <div class="detail-row">
                <span class="detail-label">Data de Partida:</span>
                <span class="detail-value">{{ \Carbon\Carbon::parse($travelRequest->departure_date)->format('d/m/Y') }}</span>
            </div>
            
            @if($travelRequest->return_date)
            <div class="detail-row">
                <span class="detail-label">Data de Retorno:</span>
                <span class="detail-value">{{ \Carbon\Carbon::parse($travelRequest->return_date)->format('d/m/Y') }}</span>
            </div>
            @endif
            
            <div class="detail-row">
                <span class="detail-label">Data de Cancelamento:</span>
                <span class="detail-value">{{ now()->format('d/m/Y H:i') }}</span>
            </div>
        </div>
        
        <div class="note">
            <strong>Nota:</strong> Se você acredita que houve um erro ou gostaria de solicitar uma nova viagem, 
            entre em contato com a equipe de recursos humanos para mais informações.
        </div>
        
        <p>Lamentamos que sua solicitação tenha sido cancelada. Se precisar de ajuda ou tiver dúvidas, 
        não hesite em nos contatar.</p>
    </div>
    
    <div class="footer">
        <p>Este é um email automático do sistema Corporate Travel Manager.</p>
        <p>© {{ date('Y') }} {{ config('app.name') }}. Todos os direitos reservados.</p>
    </div>
</body>
</html>
