@extends('layouts.admin')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">

    <h2>Rentas</h2>

    <a href="{{ route('rentas.create') }}" class="btn btn-primary">
        Nueva renta
    </a>

</div>

<div class="card">

    <div class="card-body">

        <table class="table table-bordered table-hover">

            <thead>

                <tr>

                    <th>Folio</th>

                    <th>Cliente</th>

                    <th>Fecha inicio</th>

                    <th>Fecha fin</th>

                    <th>Estado</th>

                    <th>Acciones</th>

                </tr>

            </thead>

            <tbody>

                <tr>

                    <td>R-0001</td>

                    <td>Juan Pérez</td>

                    <td>09/06/2026</td>

                    <td>15/06/2026</td>

                    <td>

                        <span class="badge bg-success">

                            Activa

                        </span>

                    </td>

                    <td>

                        <button class="btn btn-sm btn-info">

                            Ver

                        </button>

                        <button class="btn btn-sm btn-warning">

                            Editar

                        </button>

                    </td>

                </tr>

            </tbody>

        </table>

    </div>

</div>

@endsection