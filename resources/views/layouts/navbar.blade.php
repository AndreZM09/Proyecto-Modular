<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="{{ url('/campañas') }}">
            <img src="{{ asset('imagenes/logo.png') }}" alt="Logo" class="d-inline-block me-2">
            Campañas de Marketing
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('correos') ? 'active' : '' }}" href="{{ url('/correos') }}">Correos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('estadisticas') ? 'active' : '' }}" href="{{ url('/estadisticas') }}">Estadísticas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('campañas') ? 'active' : '' }}" href="{{ route('campañas') }}">Campañas</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
