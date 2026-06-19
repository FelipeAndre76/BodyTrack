<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Criar Conta - BodyTrack</title>

    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <link href="{{ asset('css/pages/auth.css') }}" rel="stylesheet">
    <link href="{{ asset('css/pages/register.css') }}" rel="stylesheet">
</head>

<body class="auth-body">

<div class="auth-wrapper">

    <section class="auth-brand-side">
        <img
            src="{{ asset('assets/images/logo.png') }}"
            alt="BodyTrack"
            class="auth-logo">

        <h1 class="auth-title">
            Comece sua transformação hoje.
        </h1>

        <p class="auth-subtitle">
            Crie sua conta e acompanhe peso, proteína, água, treinos e evolução em um único lugar.
        </p>

        <div class="auth-benefits">
            <div class="auth-benefit">
                <i class="bi bi-check-circle-fill"></i>
                Cadastro rápido e seguro
            </div>

            <div class="auth-benefit">
                <i class="bi bi-check-circle-fill"></i>
                Histórico de pesagens
            </div>

            <div class="auth-benefit">
                <i class="bi bi-check-circle-fill"></i>
                Metas inteligentes e dashboard premium
            </div>
        </div>
    </section>

    <section class="auth-form-side">
        <div class="auth-card register-card">
            <h2>Criar conta</h2>

            <p>Preencha seus dados para começar</p>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="form-group">
                    <label for="name" class="auth-label">
                        Nome completo
                    </label>

                    <input
                        id="name"
                        class="auth-input"
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        required
                        autofocus
                        autocomplete="name">

                    @error('name')
                        <div class="auth-error">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email" class="auth-label">
                        Email
                    </label>

                    <input
                        id="email"
                        class="auth-input"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autocomplete="username">

                    @error('email')
                        <div class="auth-error">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password" class="auth-label">
                        Senha
                    </label>

                    <input
                        id="password"
                        class="auth-input"
                        type="password"
                        name="password"
                        required
                        autocomplete="new-password">

                    @error('password')
                        <div class="auth-error">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="auth-label">
                        Confirmar senha
                    </label>

                    <input
                        id="password_confirmation"
                        class="auth-input"
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password">

                    @error('password_confirmation')
                        <div class="auth-error">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <button type="submit" class="auth-button">
                    Criar conta
                </button>

                <div class="register-footer">
                    Já possui conta?

                    <a href="{{ route('login') }}">
                        Entrar
                    </a>
                </div>
            </form>
        </div>
    </section>

</div>

</body>
</html>
