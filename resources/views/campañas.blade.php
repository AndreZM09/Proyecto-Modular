@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Campañas Enviadas</h2>

    {{-- Toast de notificación (éxito/error) --}}
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;">
        <div id="campaignToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto" id="campaignToastTitle">Aviso</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body" id="campaignToastBody"></div>
        </div>
    </div>

    <div class="row" id="campaignsGrid">
        @forelse ($imagenes as $imagen)
            <div class="col-md-4 mb-4" data-campaign-id="{{ $imagen->id }}">
                <div class="card h-100 shadow-sm">
                    <div class="card-img-container">
                        <a href="{{ route('estadisticas.campaign', ['id' => $imagen->id]) }}" title="Ver estadísticas de esta campaña">
                            <img src="{{ route('campaign.image', ['filename' => $imagen->filename]) }}" class="card-img-top" alt="{{ $imagen->original_name }}">
                        </a>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">{{ $imagen->subject }}</h5>
                        <p class="card-text">{{ $imagen->description }}</p>
                        <p class="text-muted small">Enviado: {{ \Carbon\Carbon::parse($imagen->created_at)->format('d/m/Y H:i') }}</p>
                        <div class="d-flex justify-content-between align-items-center mt-2 gap-2">
                            <a href="{{ route('estadisticas.campaign', ['id' => $imagen->id]) }}" class="btn btn-sm btn-primary flex-grow-1">
                                <i class="bi bi-bar-chart-fill"></i> Ver estadísticas
                            </a>
                            <button type="button"
                                    class="btn btn-sm btn-danger btn-delete-campaign"
                                    data-id="{{ $imagen->id }}"
                                    data-subject="{{ $imagen->subject }}"
                                    title="Eliminar campaña">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info text-center" id="emptyState">
                    No hay campañas enviadas todavía.
                </div>
            </div>
        @endforelse
    </div>
</div>

{{-- Modal de confirmación de borrado --}}
<div class="modal fade" id="deleteCampaignModal" tabindex="-1" aria-labelledby="deleteCampaignModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteCampaignModalLabel">
                    <i class="bi bi-exclamation-triangle-fill"></i> Eliminar campaña
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Seguro que quieres eliminar la campaña <strong id="deleteCampaignName">—</strong>?</p>
                <div class="alert alert-warning small mb-0">
                    <i class="bi bi-info-circle"></i>
                    Esta acción <strong>no se puede deshacer</strong>. Se borrarán:
                    <ul class="mb-0 mt-1">
                        <li>El registro de la campaña en la base de datos.</li>
                        <li>Todas las estadísticas asociadas (aperturas y clics).</li>
                        <li>La imagen del correo del servidor.</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <span id="deleteSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                    <span id="deleteBtnText">Sí, eliminar</span>
                </button>
            </div>
        </div>
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

.card-img-top:hover { transform: scale(1.02); }

.card {
    border-radius: 15px;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.card-body { padding: 1.5rem; }

.card-title {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.8rem;
}

.card-text {
    color: #4a5568;
    line-height: 1.5;
}

.text-muted { font-size: 0.85rem; }

/* Animación al borrar la card */
.col-md-4.removing {
    opacity: 0;
    transform: scale(0.85);
    transition: opacity 0.3s ease, transform 0.3s ease;
}

</style>

@endsection

{{-- IMPORTANTE: el script va en @push('scripts') que se renderiza DESPUÉS de
     cargar Bootstrap en el layout. Si lo dejamos dentro de @section('content'),
     se ejecuta antes y `bootstrap.Modal` no existe todavía → el botón muere. --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Verificar que Bootstrap está cargado (debería estarlo en este punto)
    if (typeof bootstrap === 'undefined') {
        console.error('[Campañas] Bootstrap no está cargado. El botón eliminar no funcionará.');
        return;
    }
    console.log('[Campañas] Inicializando handler de eliminar...');

    const grid = document.getElementById('campaignsGrid');
    const modalEl = document.getElementById('deleteCampaignModal');
    const modal = new bootstrap.Modal(modalEl);
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    const spinner = document.getElementById('deleteSpinner');
    const btnText = document.getElementById('deleteBtnText');
    const nameEl = document.getElementById('deleteCampaignName');
    const toastEl = document.getElementById('campaignToast');
    const toast = new bootstrap.Toast(toastEl, { delay: 4000 });

    let pendingId = null;

    // Delegación de eventos: un solo listener para todos los botones de borrar.
    grid.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-delete-campaign');
        if (!btn) return;
        pendingId = btn.dataset.id;
        nameEl.textContent = btn.dataset.subject || '(sin asunto)';
        console.log('[Campañas] Solicitud de eliminar campaña ID=' + pendingId);
        modal.show();
    });

    confirmBtn.addEventListener('click', async function () {
        if (!pendingId) return;

        confirmBtn.disabled = true;
        spinner.classList.remove('d-none');
        btnText.textContent = 'Eliminando...';

        try {
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (!csrfMeta) {
                throw new Error('Falta meta[name=csrf-token] en el layout');
            }
            const csrf = csrfMeta.getAttribute('content');

            // Usamos /campaigns/{id} (URL ASCII) para evitar problemas con la ñ.
            // El navegador puede codificarla diferente en distintos contextos
            // y Laravel a veces no encuentra el match.
            const url = '/campaigns/' + encodeURIComponent(pendingId);
            console.log('[Campañas] DELETE ' + url);

            const response = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin'
            });

            console.log('[Campañas] Status:', response.status);

            // Si el servidor devuelve HTML (ej: página de login redirigida o 419 CSRF),
            // intentamos leerlo como texto para mostrar un error claro.
            const contentType = response.headers.get('content-type') || '';
            if (!contentType.includes('application/json')) {
                const text = await response.text();
                console.error('[Campañas] Respuesta no-JSON:', text.substring(0, 500));
                showToast('Error', 'El servidor devolvió HTML en vez de JSON. Probable: sesión expirada (419) o ruta inexistente (404). Revisa la consola.', 'danger');
                return;
            }

            const data = await response.json();

            if (response.ok && data.success) {
                const card = grid.querySelector('[data-campaign-id="' + pendingId + '"]');
                if (card) {
                    card.classList.add('removing');
                    setTimeout(function () {
                        card.remove();
                        if (!grid.querySelector('[data-campaign-id]')) {
                            grid.innerHTML = '<div class="col-12"><div class="alert alert-info text-center">No hay campañas enviadas todavía.</div></div>';
                        }
                    }, 300);
                }
                showToast('Éxito', data.message || 'Campaña eliminada.', 'success');
            } else {
                showToast('Error', data.message || 'No se pudo eliminar la campaña.', 'danger');
            }
        } catch (err) {
            console.error('[Campañas] Error al eliminar:', err);
            showToast('Error', 'Error de conexión: ' + err.message, 'danger');
        } finally {
            confirmBtn.disabled = false;
            spinner.classList.add('d-none');
            btnText.textContent = 'Sí, eliminar';
            pendingId = null;
            modal.hide();
        }
    });

    function showToast(title, body, type) {
        document.getElementById('campaignToastTitle').textContent = title;
        document.getElementById('campaignToastBody').textContent = body;
        toastEl.classList.remove('text-bg-success', 'text-bg-danger');
        toastEl.classList.add(type === 'success' ? 'text-bg-success' : 'text-bg-danger');
        toast.show();
    }

    console.log('[Campañas] Handler eliminar listo. Botones encontrados:',
                document.querySelectorAll('.btn-delete-campaign').length);
});
</script>
@endpush
