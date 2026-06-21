<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login - BodyTrack</title>

    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <link href="{{ asset('css/pages/auth.css') }}" rel="stylesheet">
</head>

<body class="auth-body">

<div class="auth-wrapper">

    <section class="auth-brand-side">
        <img
            src="{{ asset('assets/images/logo.png') }}"
            alt="BodyTrack"
            class="auth-logo">

        <h1 class="auth-title">
            Acompanhe sua evolução corporal com inteligência.
        </h1>

        <p class="auth-subtitle">
            Controle peso, proteína, água, treinos e metas em um dashboard premium feito para sua jornada.
        </p>

        <div class="auth-benefits">
            <div class="auth-benefit">
                <i class="bi bi-check-circle-fill"></i>
                Dashboard de evolução corporal
            </div>

            <div class="auth-benefit">
                <i class="bi bi-check-circle-fill"></i>
                Controle de pesagens e metas
            </div>

            <div class="auth-benefit">
                <i class="bi bi-check-circle-fill"></i>
                Acompanhamento de proteína, água e treinos
            </div>
        </div>
    </section>

    <section class="auth-form-side">
        <div class="auth-card">
            <h2>Entrar</h2>

            <p>Acesse sua conta BodyTrack</p>

            @if (session('status'))
                <div class="mb-3 text-success">
                    {{ session('status') }}
                </div>
            @endif
                @if(session('blocked'))

<div class="alert alert-danger mb-4">
    <i class="bi bi-shield-lock me-2"></i>

    {{ session('blocked') }}
</div>

@endif
<br>
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="auth-label">Email</label>

                    <input
                        id="email"
                        class="auth-input"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username">

                    @error('email')
                        <div class="auth-error">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="auth-label">Senha</label>

                    <input
                        id="password"
                        class="auth-input"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password">

                    @error('password')
                        <div class="auth-error">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <label class="auth-check">
                        <input type="checkbox" name="remember">
                        Lembrar-me
                    </label>

                    @if (Route::has('password.request'))
                        <a class="auth-link" href="{{ route('password.request') }}">
                            Esqueci a senha
                        </a>
                    @endif
                </div>

                <button type="submit" class="auth-button">
                    Entrar
                </button>

                <div class="auth-register">
                    <span class="text-secondary">
                        Ainda não tem conta?
                    </span>

                    <a href="{{ route('register') }}" class="auth-link">
                        Criar conta
                    </a>
                </div>
            </form>
        </div>
    </section>

</div>

</body>
</html>
