@php
    $cuenta = $cuentaItem['cuenta'];
    $hijos = $cuentaItem['hijos'] ?? [];
    $tieneHijos = count($hijos) > 0;
@endphp

<tr class="cuenta-row nivel-{{ $cuenta->nivel }}" data-cuenta-id="{{ $cuenta->id }}">
    <td>
        @if($tieneHijos)
            <i class="fas fa-plus cuenta-toggle" id="toggle-{{ $cuenta->id }}" onclick="toggleCuenta({{ $cuenta->id }})"></i>
        @else
            <span style="margin-left: 20px;"></span>
        @endif
        <code>{{ $cuenta->codigo }}</code>
    </td>
    <td class="nivel-{{ $cuenta->nivel }}">
        {{ $cuenta->nombre }}
    </td>
    <td>
        @if($cuenta->tipo)
            @php
                $colores = [
                    'activo' => 'success',
                    'pasivo' => 'danger',
                    'patrimonio' => 'info',
                    'ingreso' => 'primary',
                    'gasto' => 'warning'
                ];
                $color = $colores[$cuenta->tipo] ?? 'secondary';
            @endphp
            <span class="badge badge-{{ $color }} badge-tipo">
                {{ ucfirst($cuenta->tipo) }}
            </span>
        @endif
    </td>
    <td>
        @if($cuenta->naturaleza)
            <span class="badge badge-{{ $cuenta->naturaleza === 'deudor' ? 'success' : 'info' }} badge-tipo">
                {{ ucfirst($cuenta->naturaleza) }}
            </span>
        @endif
    </td>
    <td>
        <span class="badge badge-light">{{ $cuenta->nivel }}</span>
    </td>
    <td>
        @if($cuenta->es_imputable)
            <i class="fas fa-check text-success" title="{{ __('Permite movimientos') }}"></i>
        @else
            <i class="fas fa-times text-muted" title="{{ __('Solo resumen') }}"></i>
        @endif
    </td>
</tr>

@if($tieneHijos)
    <tbody id="hijos-{{ $cuenta->id }}" class="cuenta-hijos" style="display: none;">
        @foreach($hijos as $hijo)
            @include('contabilidad.partials.cuenta-row', ['cuentaItem' => $hijo, 'nivel' => $nivel + 1])
        @endforeach
    </tbody>
@endif
