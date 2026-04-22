@extends('adminlte::page')

@section('title', 'Gestión de Carreras')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1><i class="fas fa-graduation-cap"></i> Gestión de Carreras</h1>
        <div>
            <a href="{{ route('carreras.cuotas') }}" class="btn btn-info">
                <i class="fas fa-dollar-sign"></i> Gestionar Cuotas
            </a>
            <a href="{{ route('carreras.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Carrera
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lista de Carreras</h3>
        </div>

        <div class="card-body">
            <table class="table table-striped table-hover" id="carrerasTable">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Modalidad</th>
                        <th>Duración</th>
                        <th>Cuota Mensual</th>
                        <th>Estudiantes</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($carreras as $carrera)
                    <tr>
                        <td>
                            <strong>{{ $carrera->nombre_carrera }}</strong>
                        </td>
                        <td>
                            <span class="badge badge-{{ $carrera->tipo_carrera === 'Tecnicatura' ? 'info' : ($carrera->tipo_carrera === 'Grado' ? 'primary' : 'success') }}">
                                {{ $carrera->tipo_carrera }}
                            </span>
                        </td>
                        <td>{{ $carrera->modalidad }}</td>
                        <td>{{ $carrera->duracion_anios }} año{{ $carrera->duracion_anios > 1 ? 's' : '' }}</td>
                        <td>
                            <strong>${{ number_format($carrera->cuota_mensual, 2) }}</strong>
                            @if($carrera->cuota_inscripcion)
                                <br><small class="text-muted">Inscripción: ${{ number_format($carrera->cuota_inscripcion, 2) }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-secondary">
                                {{ $carrera->estudiantes->count() }} estudiante{{ $carrera->estudiantes->count() !== 1 ? 's' : '' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-{{ $carrera->activa ? 'success' : 'danger' }}">
                                {{ $carrera->activa ? 'Activa' : 'Inactiva' }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('carreras.show', $carrera) }}" class="btn btn-sm btn-info" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('carreras.edit', $carrera) }}" class="btn btn-sm btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('carreras.toggle-activa', $carrera) }}" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-{{ $carrera->activa ? 'secondary' : 'success' }}"
                                            title="{{ $carrera->activa ? 'Desactivar' : 'Activar' }}">
                                        <i class="fas fa-{{ $carrera->activa ? 'times' : 'check' }}"></i>
                                    </button>
                                </form>
                                @if($carrera->estudiantes->count() === 0)
                                <button type="button" class="btn btn-sm btn-danger" title="Eliminar"
                                        onclick="confirmarEliminacion({{ $carrera->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($carreras->hasPages())
        <div class="card-footer clearfix">
            {{ $carreras->links() }}
        </div>
        @endif
    </div>

    <!-- Modal de confirmación de eliminación -->
    <div class="modal fade" id="confirmarEliminacionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Eliminación</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro de que desea eliminar esta carrera? Esta acción no se puede deshacer.</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Solo se pueden eliminar carreras que no tengan estudiantes asociados.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <form id="formEliminar" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap4.min.css">
<style>
.table td {
    vertical-align: middle;
}
.badge {
    font-size: 0.85em;
}
</style>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.colVis.min.js"></script>

<script>
// Función de búsqueda personalizada - busca desde el inicio de los campos
$.fn.dataTable.ext.search.push(
    function(settings, data, dataIndex) {
        var searchTerm = $('.dataTables_filter input').val();
        if (!searchTerm) return true;
        
        // Buscar solo al inicio de cualquier columna (excluyendo HTML)
        return data.some(function(cellData) {
            // Remover HTML y espacios extra
            var cleanData = cellData.replace(/<[^>]*>/g, '').trim();
            return cleanData.toLowerCase().indexOf(searchTerm.toLowerCase()) === 0;
        });
    }
);

$(document).ready(function() {
    $('#carrerasTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "order": [[ 0, "asc" ]], // Ordenar por nombre (columna 0)
        "columnDefs": [
            {
                "targets": [7], // Columna de acciones
                "orderable": false,
                "searchable": false
            }
        ],
        "pageLength": 20,
        "lengthMenu": [[20, 50, 100, -1], [20, 50, 100, "Todos"]],
        "responsive": true,
        "dom": 'Bfrtip',
        "buttons": [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        "searching": true,
        "paging": true,
        "info": true
    });
});

function confirmarEliminacion(id) {
    $('#formEliminar').attr('action', '/carreras/' + id);
    $('#confirmarEliminacionModal').modal('show');
}
</script>
@stop
