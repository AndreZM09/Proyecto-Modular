@extends('layouts.app') {{-- Asegúrate que layouts/app.blade.php incluya @include('layouts.navbar') --}}

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Campañas Enviadas</h2>

    <div class="row">
        @foreach ($imagenes as $imagen)
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-img-container">
                        <img src="{{ asset('storage/email_images/' . $imagen->filename) }}" class="card-img-top" alt="{{ $imagen->original_name }}">
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">{{ $imagen->subject }}</h5>
                        <p class="card-text">{{ $imagen->description }}</p>
                        <p class="text-muted small">Enviado: {{ \Carbon\Carbon::parse($imagen->created_at)->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<style>
.card-img-container {
    height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    overflow: hidden;
    border-radius: 15px 15px 0 0;
}

.card-img-top {
    max-width: 100%;
    max-height: 180px;
    width: auto;
    height: auto;
    object-fit: contain;
    border-radius: 8px;
    background: white;
    padding: 0.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.card-img-top:hover {
    transform: scale(1.02);
}

.card {
    border-radius: 15px;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.card-body {
    padding: 1.5rem;
}

.card-title {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.8rem;
}

.card-text {
    color: #4a5568;
    line-height: 1.5;
}

.text-muted {
    font-size: 0.85rem;
}
</style>
@endsection
