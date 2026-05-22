@extends('adminlte::page')

@section('title', 'BOX - Pagos a Proveedores')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-hand-holding-usd text-danger"></i> Pagos a Proveedores</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('box.dashboard') }}">BOX Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#">Pagos</a></li>
                <li class="breadcrumb-item active">Proveedores</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> Se encontraron errores en el formulario.
            <ul class="mb-0 mt-2 pl-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-7">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-truck"></i> Alta Rápida de Proveedor</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('box.pagos.proveedores.proveedor.store') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Razón social</label>
                                    <input type="text" name="razon_social" class="form-control" value="{{ old('razon_social') }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>CUIT</label>
                                    <input type="text" name="cuit" class="form-control" value="{{ old('cuit') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Teléfono</label>
                                    <input type="text" name="telefono" class="form-control" value="{{ old('telefono') }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label>Email</label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label>Dirección</label>
                                    <input type="text" name="direccion" class="form-control" value="{{ old('direccion') }}">
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Guardar Proveedor
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-file-invoice-dollar"></i> Registro de Pago</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('box.pagos.proveedores.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Proveedor</label>
                                    <select name="proveedor_id" class="form-control" required>
                                        <option value="">Seleccionar proveedor...</option>
                                        @foreach($proveedores as $proveedor)
                                            <option value="{{ $proveedor->id }}" {{ (string) old('proveedor_id') === (string) $proveedor->id ? 'selected' : '' }}>
                                                {{ $proveedor->razon_social }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tipo de comprobante</label>
                                    <select name="tipo_comprobante" class="form-control" required>
                                        <option value="factura" {{ old('tipo_comprobante') === 'factura' ? 'selected' : '' }}>Factura</option>
                                        <option value="recibo" {{ old('tipo_comprobante') === 'recibo' ? 'selected' : '' }}>Recibo</option>
                                        <option value="boleta" {{ old('tipo_comprobante') === 'boleta' ? 'selected' : '' }}>Boleta</option>
                                        <option value="remito" {{ old('tipo_comprobante') === 'remito' ? 'selected' : '' }}>Remito</option>
                                        <option value="otro" {{ old('tipo_comprobante') === 'otro' ? 'selected' : '' }}>Otro</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Nro comprobante</label>
                                    <input type="text" name="numero_comprobante" class="form-control" placeholder="Ej: 0001-00001234" value="{{ old('numero_comprobante') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Fecha comprobante</label>
                                    <input type="date" name="fecha_comprobante" class="form-control" value="{{ old('fecha_comprobante', date('Y-m-d')) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Importe</label>
                                    <input type="number" name="monto" step="0.01" min="0.01" class="form-control" placeholder="0.00" value="{{ old('monto') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fecha de pago</label>
                                    <input type="date" name="fecha_pago" class="form-control" value="{{ old('fecha_pago', date('Y-m-d')) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Comprobante (PDF/JPG/PNG)</label>
                                    <input type="file" name="comprobante_archivo" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png,.webp">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Detalle / concepto</label>
                            <input type="text" name="concepto" class="form-control" placeholder="Descripcion del pago..." value="{{ old('concepto') }}" required>
                        </div>

                        <div class="form-group">
                            <label>Observaciones</label>
                            <textarea name="observaciones" class="form-control" rows="2" placeholder="Observaciones opcionales...">{{ old('observaciones') }}</textarea>
                        </div>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-check"></i> Registrar Pago
                            </button>
                            <button type="reset" class="btn btn-secondary ml-2">
                                <i class="fas fa-eraser"></i> Limpiar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-history"></i> Historial de Pagos</h3>
                </div>
                <div class="card-body">
                    @if($pagos->count() === 0)
                        <div class="text-muted text-center py-4">
                            <i class="fas fa-receipt fa-2x mb-2"></i>
                            <p class="mb-0">Aún no hay pagos registrados.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Proveedor</th>
                                        <th>Comprobante</th>
                                        <th>Pago</th>
                                        <th class="text-right">Monto</th>
                                        <th>Archivo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pagos as $pago)
                                        <tr>
                                            <td>{{ $pago->proveedor->razon_social }}</td>
                                            <td>
                                                <div>{{ ucfirst($pago->tipo_comprobante) }} {{ $pago->numero_comprobante }}</div>
                                                <small class="text-muted">
                                                    Fecha: {{ optional($pago->fecha_comprobante)->format('d/m/Y') ?? '-' }}
                                                </small>
                                            </td>
                                            <td>{{ optional($pago->fecha_pago)->format('d/m/Y') }}</td>
                                            <td class="text-right">$ {{ number_format((float) $pago->monto, 2, ',', '.') }}</td>
                                            <td>
                                                @if($pago->comprobante_path)
                                                    <a href="{{ route('box.pagos.proveedores.comprobante', $pago) }}" class="btn btn-xs btn-outline-primary">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2">
                            {{ $pagos->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="card card-secondary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-clipboard-check"></i> Control</h3>
                </div>
                <div class="card-body">
                    <ul class="mb-0 pl-3">
                        <li>Seleccione un proveedor registrado.</li>
                        <li>Cargue número y fecha de boleta/recibo.</li>
                        <li>Adjunte archivo para respaldo documental.</li>
                        <li>Use el historial para trazabilidad y auditoría.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@stop
