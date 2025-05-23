@extends('layouts.app') {{-- Asegúrate que layouts/app.blade.php incluya @include('layouts.navbar') --}}

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Campañas Enviadas</h2>

    <div class="row">
        @foreach ($imagenes as $imagen)
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <img src="{{ asset('storage/images/' . $imagen->filename) }}" class="card-img-top" alt="{{ $imagen->original_name }}">
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
@endsection
