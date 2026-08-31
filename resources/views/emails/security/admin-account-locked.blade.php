<p>Se bloqueó temporalmente la cuenta administrativa <strong>{{ $lockedUser->name }}</strong> ({{ $lockedUser->email }}) tras varios intentos fallidos de {{ $reason }}.</p>
<p>El bloqueo vencerá a las {{ $lockedUser->login_locked_until?->timezone('America/Panama')->format('d/m/Y h:i:s a') }}.</p>
