<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>BodyTrack - Evolução Corporal</title>

    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/pages/home.css') }}" rel="stylesheet">
</head>

<body>

<header class="home-header">
    <img src="{{ asset('assets/images/logo.png') }}" alt="BodyTrack" class="home-logo">

    <nav>
        <a href="#features">Funcionalidades</a>
        <a href="#simulator">Simulador</a>
        <a href="{{ route('login') }}">Entrar</a>
        <a href="{{ route('register') }}" class="btn-nav">Criar conta</a>
    </nav>
</header>

<section class="home-hero">
    <div class="hero-content">
        <span class="badge">Seu painel fitness inteligente</span>

        <h1>
            Seu corpo, seus exames e sua evolução em um único lugar.
        </h1>

        <p>
            Controle peso, proteína, água, treinos, medicamentos e exames laboratoriais com dashboards inteligentes.
        </p>

        <div class="hero-actions">
            <a href="{{ route('register') }}" class="btn-primary">Começar agora</a>
            <a href="#simulator" class="btn-secondary">Testar simulador</a>
        </div>
    </div>
<div class="hero-preview">
    <div class="preview-top">
        <span>Prévia do BodyTrack</span>
        <strong>Demo</strong>
    </div>

    <div class="preview-progress">
        <div style="width: 68%"></div>
    </div>

    <div class="preview-grid">
        <div>
            <span>Peso</span>
            <strong>Em evolução</strong>
        </div>

        <div>
            <span>Exames</span>
            <strong>Organizados</strong>
        </div>
    </div>

    <div class="preview-alert">
        <i class="bi bi-stars"></i>
        Acompanhe seus dados de saúde em um só painel.
    </div>
</div>

</section>

<section id="simulator" class="simulator-section">
    <div class="section-heading">
        <span class="badge">Simulador inteligente</span>

        <h2>Projete sua meta corporal.</h2>

        <p>
            Informe seus dados e veja uma estimativa simples da sua evolução.
        </p>
    </div>

    <div class="simulator-box simulator-clean">
        <div class="simulator-form">
            <label>Altura (m)</label>
            <input type="number" id="simHeight" step="0.01" value="1.80">

            <label>Peso atual (kg)</label>
            <input type="number" id="simWeight" step="0.1" value="108.9">

            <label>Peso meta (kg)</label>
            <input type="number" id="simGoal" step="0.1" value="95">

            <label>Perda média por semana (kg)</label>
            <input type="number" id="simWeeklyLoss" step="0.1" value="1.0">
        </div>

        <div class="projection-card">
            <div class="projection-header">
                <i class="bi bi-stars"></i>
                <span>Análise estimada</span>
            </div>

            <h3 id="projectionTitle">
                Você precisa eliminar 13.9 kg
            </h3>

            <div class="projection-grid">
                <div>
                    <span>IMC Atual</span>
                    <strong id="currentBmiText">33.61</strong>
                </div>

                <div>
                    <span>IMC Meta</span>
                    <strong id="goalBmiText">29.32</strong>
                </div>

                <div>
                    <span>Diferença</span>
                    <strong id="differenceText">13.9 kg</strong>
                </div>

                <div>
                    <span>Previsão</span>
                    <strong id="weeksText">14 semanas</strong>
                </div>
            </div>

            <div class="projection-progress">
                <div id="projectionProgressBar"></div>
            </div>

            <p id="projectionMessage">
                Mantendo esse ritmo, você pode atingir sua meta em aproximadamente 14 semanas.
            </p>
        </div>
    </div>
</section>

<section id="features" class="features-section">
    <div class="section-heading">
        <span class="badge">Funcionalidades</span>
        <h2>Tudo que você precisa para acompanhar sua evolução.</h2>
    </div>

    <div class="features-grid">
        <div class="feature-card">
            <i class="bi bi-speedometer2"></i>
            <h3>Pesagens</h3>
            <p>Registre seu peso e acompanhe sua evolução com gráficos.</p>
        </div>

        <div class="feature-card">
            <i class="bi bi-egg-fried"></i>
            <h3>Proteína</h3>
            <p>Controle sua meta diária de proteína e consumo alimentar.</p>
        </div>

        <div class="feature-card">
            <i class="bi bi-droplet-half"></i>
            <h3>Água</h3>
            <p>Acompanhe sua hidratação diária com metas simples.</p>
        </div>

        <div class="feature-card">
            <i class="bi bi-file-earmark-medical"></i>
            <h3>Exames</h3>
            <p>Envie exames e organize seus marcadores laboratoriais.</p>
        </div>

        <div class="feature-card">
            <i class="bi bi-capsule"></i>
            <h3>Medicamentos</h3>
            <p>Registre medicações, doses e datas de início.</p>
        </div>

        <div class="feature-card">
            <i class="bi bi-images"></i>
            <h3>Antes e depois</h3>
            <p>Compare fotos de evolução ao longo da jornada.</p>
        </div>
    </div>
</section>

<section class="cta-section">
    <h2>Pronto para transformar sua evolução em dados?</h2>
    <p>Comece gratuitamente e acompanhe sua saúde em um dashboard premium.</p>
    <a href="{{ route('register') }}">Criar conta gratuita</a>
</section>

<script>
    function updateSimulator() {
        const height = parseFloat(document.getElementById('simHeight').value) || 1;
        const weight = parseFloat(document.getElementById('simWeight').value) || 0;
        const goal = parseFloat(document.getElementById('simGoal').value) || 0;
        const weeklyLoss = parseFloat(document.getElementById('simWeeklyLoss').value) || 1;

        const currentBmi = weight / (height * height);
        const goalBmi = goal / (height * height);
        const difference = weight - goal;
        const weeks = Math.ceil(Math.abs(difference) / weeklyLoss);

        let progress = 0;

        if (weight > goal) {
            const start = weight;
            const total = weight - goal;
            progress = total > 0 ? 100 - ((goal / start) * 100) : 0;
        }

        progress = Math.max(0, Math.min(100, progress));

        document.getElementById('projectionTitle').innerText =
            difference >= 0
                ? `Você precisa eliminar ${difference.toFixed(1)} kg`
                : `Você precisa ganhar ${Math.abs(difference).toFixed(1)} kg`;

        document.getElementById('currentBmiText').innerText = currentBmi.toFixed(2);
        document.getElementById('goalBmiText').innerText = goalBmi.toFixed(2);
        document.getElementById('differenceText').innerText = Math.abs(difference).toFixed(1) + ' kg';
        document.getElementById('weeksText').innerText = weeks + ' semanas';
        document.getElementById('projectionProgressBar').style.width = progress + '%';

        document.getElementById('projectionMessage').innerText =
            `Mantendo esse ritmo, você pode atingir sua meta em aproximadamente ${weeks} semanas.`;
    }

    document.querySelectorAll('#simHeight, #simWeight, #simGoal, #simWeeklyLoss')
        .forEach(input => input.addEventListener('input', updateSimulator));

    updateSimulator();
</script>

</body>
</html>
