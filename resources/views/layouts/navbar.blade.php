<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm m-0 p-2">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
            <img src="{{ asset('imagenes/mail.png') }}" alt="" class="d-inline-block me-2" style="width: 30px; height: auto;">
            Email Marketing
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('correos') ? 'active fw-bold' : '' }}" href="{{ url('/correos') }}">Correos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('estadisticas') ? 'active fw-bold' : '' }}" href="{{ url('/estadisticas') }}">Estadísticas</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
