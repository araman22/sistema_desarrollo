<?php

namespace Database\Seeders;

use App\Models\Actividad;
use App\Models\Alerta;
use App\Models\Arma;
use App\Models\Auditoria;
use App\Models\CajaChicaMovimiento;
use App\Models\Capacitacion;
use App\Models\CapacitacionPersonal;
use App\Models\Documento;
use App\Models\EquipamientoTecnologico;
use App\Models\Funcion;
use App\Models\IncidenciaInfraestructura;
use App\Models\Inventario;
use App\Models\Jerarquia;
use App\Models\Licencia;
use App\Models\Mantenimiento;
use App\Models\MovimientoEconomico;
use App\Models\Oficina;
use App\Models\Persona;
use App\Models\Policia;
use App\Models\Proyecto;
use App\Models\ProyectoIntegrante;
use App\Models\TipoDocumento;
use App\Models\TipoLicencia;
use App\Models\TipoVehiculo;
use App\Models\Usuario;
use App\Models\Vehiculo;
use App\Models\VehiculoMantenimiento;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Cinco registros de prueba por modulo. Solo para entorno local: requiere
 * los catalogos, el administrador y las armas que cargan los demas seeders.
 * Es idempotente: cada registro se identifica por un campo unico.
 */
class DatosPruebaSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $oficinas = $this->oficinas();
            $policias = $this->policias($oficinas);
            $admin = Usuario::where('nombre_usuario', 'admin')->firstOrFail();

            $this->documentos($admin);
            $this->licencias($policias);
            $this->capacitaciones($policias);
            $actividades = $this->actividades($oficinas);
            $inventarios = $this->inventarios($oficinas, $policias);
            $equipamientos = $this->equipamientos($oficinas, $policias);
            $vehiculos = $this->vehiculos($policias);
            $proyectos = $this->proyectos($oficinas, $policias);
            $this->incidencias($oficinas, $policias);
            $this->movimientosEconomicos($policias, $proyectos);
            $this->cajaChica($admin);
            $this->alertas($admin, $vehiculos, $equipamientos, $inventarios, $actividades, $proyectos);
            $this->auditorias($admin, $policias);
        });

        $this->call(MovilidadDemoSeeder::class);
    }

    /** @return array<int, Oficina> */
    private function oficinas(): array
    {
        $datos = [
            ['Division Informatica', 'Soporte tecnico y redes.', 'Edificio Central, 2do piso'],
            ['Division Movilidad', 'Gestion de la flota de vehiculos.', 'Playon Norte'],
            ['Division Administrativa', 'Tramites, compras y caja chica.', 'Edificio Central, PB'],
            ['Division Capacitacion', 'Formacion y entrenamiento del personal.', 'Anexo Sur'],
            ['Division Operaciones Especiales', 'Drones, robots y operativos.', 'Base Aerea Este'],
        ];

        return array_map(fn (array $d, int $i) => Oficina::updateOrCreate(
            ['nombre' => $d[0]],
            [
                'descripcion' => $d[1],
                'ubicacion' => $d[2],
                'telefono' => '0381-42000'.($i + 1),
                'correo_electronico' => 'oficina'.($i + 1).'@sigdet.local',
                'estado' => $i === 4 ? 'inactiva' : 'activa',
            ],
        ), $datos, array_keys($datos));
    }

    /**
     * @param  array<int, Oficina>  $oficinas
     * @return array<int, Policia>
     */
    private function policias(array $oficinas): array
    {
        $datos = [
            ['30111222', 'Juan', 'Perez', 'Masculino', 'Oficial Principal', 'Jefe de oficina', 'SN-0002'],
            ['31222333', 'Maria', 'Gomez', 'Femenino', 'Sargento', 'Analista', 'SN-0003'],
            ['32333444', 'Luis', 'Rodriguez', 'Masculino', 'Cabo Primero', 'Tecnico de soporte', null],
            ['33444555', 'Ana', 'Fernandez', 'Femenino', 'Agente', 'Operador de drones', null],
            ['34555666', 'Carlos', 'Martinez', 'Masculino', 'Cabo', 'Chofer', null],
        ];

        $policias = [];

        foreach ($datos as $i => [$dni, $nombre, $apellido, $sexo, $jerarquia, $funcion, $serieArma]) {
            $persona = Persona::updateOrCreate(
                ['dni' => $dni],
                [
                    'nombre' => $nombre,
                    'apellido' => $apellido,
                    'sexo' => $sexo,
                    'fecha_nacimiento' => Carbon::create(1985 + $i, $i + 1, 10)->toDateString(),
                    'direccion' => 'Calle Falsa '.(100 + $i * 10),
                    'telefono' => '381-55500'.($i + 1),
                    'correo_electronico' => strtolower($nombre.'.'.$apellido).'@sigdet.local',
                ],
            );

            $policias[] = $policia = Policia::updateOrCreate(
                ['persona_id' => $persona->id],
                [
                    'jerarquia_id' => Jerarquia::where('nombre', $jerarquia)->firstOrFail()->id,
                    'funcion_id' => Funcion::where('nombre', $funcion)->firstOrFail()->id,
                    'arma_id' => $serieArma ? Arma::where('numero_serie', $serieArma)->firstOrFail()->id : null,
                    'oficina_id' => $oficinas[$i]->id,
                    'numero_legajo' => sprintf('P-%04d', $i + 2),
                    'fecha_ingreso' => now()->subYears(10 - $i)->toDateString(),
                    'estado' => 'activo',
                ],
            );

            $oficinas[$i]->update(['responsable_id' => $policia->id]);
        }

        return $policias;
    }

    private function documentos(Usuario $admin): void
    {
        $tipos = ['Acta', 'Informe', 'Resolucion', 'Nota', 'Memorando'];

        foreach ($tipos as $i => $tipo) {
            Documento::updateOrCreate(
                ['tipo_documento_id' => TipoDocumento::where('nombre', $tipo)->firstOrFail()->id, 'numero' => 1000 + $i],
                [
                    'nombre' => $tipo.' de prueba '.($i + 1),
                    'descripcion' => 'Documento de prueba generado por el seeder.',
                    'usuario_id' => $admin->id,
                ],
            );
        }
    }

    /** @param  array<int, Policia>  $policias */
    private function licencias(array $policias): void
    {
        $datos = [
            ['Licencia anual', 'autorizada', 15],
            ['Licencia medica', 'autorizada', 5],
            ['Permiso', 'pendiente', 1],
            ['Licencia por estudio', 'pendiente', 3],
            ['Comision', 'rechazada', 10],
        ];

        foreach ($datos as $i => [$tipo, $estado, $dias]) {
            $inicio = today()->addDays($i * 7);

            Licencia::updateOrCreate(
                ['policia_id' => $policias[$i]->id, 'fecha_inicio' => $inicio->toDateString()],
                [
                    'tipo_licencia_id' => TipoLicencia::where('nombre', $tipo)->firstOrFail()->id,
                    'fecha_fin' => $inicio->copy()->addDays($dias - 1)->toDateString(),
                    'cantidad_dias' => $dias,
                    'estado' => $estado,
                    'motivo' => $tipo.' de prueba.',
                ],
            );
        }
    }

    /** @param  array<int, Policia>  $policias */
    private function capacitaciones(array $policias): void
    {
        $datos = [
            ['Primeros auxilios', 'Cruz Roja', 20],
            ['Manejo defensivo', 'Escuela de Policia', 16],
            ['Ciberseguridad basica', 'Universidad Nacional', 40],
            ['Pilotaje de drones', 'ANAC', 30],
            ['Tiro policial', 'Escuela de Policia', 12],
        ];

        foreach ($datos as $i => [$nombre, $institucion, $horas]) {
            $capacitacion = Capacitacion::updateOrCreate(
                ['nombre' => $nombre],
                [
                    'descripcion' => 'Capacitacion de prueba.',
                    'institucion' => $institucion,
                    'fecha_inicio' => today()->subMonths(5 - $i)->toDateString(),
                    'fecha_fin' => today()->subMonths(5 - $i)->addDays(5)->toDateString(),
                    'cantidad_horas' => $horas,
                    'certificado' => $i % 2 === 0,
                ],
            );

            CapacitacionPersonal::updateOrCreate(
                ['capacitacion_id' => $capacitacion->id, 'policia_id' => $policias[$i]->id],
                ['resultado' => $i === 4 ? 'En curso' : 'Aprobado'],
            );
        }
    }

    /**
     * @param  array<int, Oficina>  $oficinas
     * @return array<int, Actividad>
     */
    private function actividades(array $oficinas): array
    {
        $datos = [
            ['Reunion de coordinacion', 'reunion', 'media', 'pendiente'],
            ['Relevamiento de equipos', 'relevamiento', 'alta', 'en_curso'],
            ['Inspeccion de flota', 'inspeccion', 'alta', 'pendiente'],
            ['Jornada de capacitacion', 'capacitacion', 'baja', 'finalizada'],
            ['Operativo con drones', 'operativo', 'alta', 'pendiente'],
        ];

        return array_map(fn (array $d, int $i) => Actividad::updateOrCreate(
            ['titulo' => $d[0]],
            [
                'oficina_id' => $oficinas[$i]->id,
                'descripcion' => 'Actividad de prueba.',
                'tipo' => $d[1],
                'fecha_inicio' => now()->addDays($i * 3)->setTime(9, 0),
                'fecha_fin' => now()->addDays($i * 3)->setTime(13, 0),
                'prioridad' => $d[2],
                'estado' => $d[3],
            ],
        ), $datos, array_keys($datos));
    }

    /**
     * @param  array<int, Oficina>  $oficinas
     * @param  array<int, Policia>  $policias
     * @return array<int, Inventario>
     */
    private function inventarios(array $oficinas, array $policias): array
    {
        $datos = [
            ['Escritorio de oficina', 'Mobiliario', null, null, 'en_uso', 85000],
            ['Silla ergonomica', 'Mobiliario', null, null, 'en_uso', 60000],
            ['Proyector', 'Electronica', 'Epson', 'PowerLite X49', 'disponible', 450000],
            ['Radio portatil', 'Comunicaciones', 'Motorola', 'DEP450', 'en_mantenimiento', 320000],
            ['Chaleco antibalas', 'Equipamiento', 'Kevlar', 'Nivel III', 'disponible', 700000],
        ];

        return array_map(fn (array $d, int $i) => Inventario::updateOrCreate(
            ['codigo' => sprintf('INV-%04d', $i + 1)],
            [
                'descripcion' => $d[0],
                'categoria' => $d[1],
                'marca' => $d[2],
                'modelo' => $d[3],
                'numero_serie' => sprintf('INV-NS-%04d', $i + 1),
                'estado' => $d[4],
                'ubicacion' => $oficinas[$i]->ubicacion,
                'oficina_id' => $oficinas[$i]->id,
                'responsable_id' => $policias[$i]->id,
                'fecha_adquisicion' => today()->subMonths(6 + $i)->toDateString(),
                'valor_adquisicion' => $d[5],
            ],
        ), $datos, array_keys($datos));
    }

    /**
     * @param  array<int, Oficina>  $oficinas
     * @param  array<int, Policia>  $policias
     * @return array<int, EquipamientoTecnologico>
     */
    private function equipamientos(array $oficinas, array $policias): array
    {
        $datos = [
            ['PC', 'PC Administracion 1', 'Dell', 'OptiPlex 7090', 'Intel Core i5', '16 GB', '512 GB SSD', 'Windows 11', 'activo'],
            ['Notebook', 'Notebook Soporte', 'Lenovo', 'ThinkPad E14', 'AMD Ryzen 5', '16 GB', '512 GB SSD', 'Ubuntu 24.04', 'activo'],
            ['Servidor', 'Servidor de archivos', 'HP', 'ProLiant DL380', 'Intel Xeon Silver', '64 GB', '4 TB RAID', 'Windows Server 2022', 'activo'],
            ['Impresora', 'Impresora Mesa de Entradas', 'Brother', 'HL-L5100DN', null, null, null, null, 'en_mantenimiento'],
            ['Switch', 'Switch principal', 'Cisco', 'Catalyst 2960', null, null, null, null, 'inactivo'],
        ];

        $equipamientos = [];

        foreach ($datos as $i => $d) {
            $equipamientos[] = $equipo = EquipamientoTecnologico::updateOrCreate(
                ['codigo' => sprintf('EQ-%04d', $i + 1)],
                [
                    'tipo' => $d[0],
                    'nombre' => $d[1],
                    'marca' => $d[2],
                    'modelo' => $d[3],
                    'numero_serie' => sprintf('EQ-NS-%04d', $i + 1),
                    'procesador' => $d[4],
                    'memoria_ram' => $d[5],
                    'almacenamiento' => $d[6],
                    'sistema_operativo' => $d[7],
                    'direccion_ip' => '192.168.1.'.(10 + $i),
                    'direccion_mac' => sprintf('00:1A:2B:3C:4D:%02X', $i + 1),
                    'oficina_id' => $oficinas[$i]->id,
                    'estado' => $d[8],
                    'fecha_adquisicion' => today()->subYears($i + 1)->toDateString(),
                ],
            );

            Mantenimiento::updateOrCreate(
                [
                    'mantenible_tipo' => $equipo->getMorphClass(),
                    'mantenible_id' => $equipo->id,
                    'descripcion' => 'Mantenimiento de prueba de '.$equipo->nombre,
                ],
                [
                    'tipo' => $i % 2 === 0 ? 'preventivo' : 'correctivo',
                    'fecha' => today()->subDays(10 * ($i + 1))->toDateString(),
                    'responsable_id' => $policias[2]->id,
                    'costo' => 15000 * ($i + 1),
                    'estado' => 'realizado',
                    'resultado' => 'Sin novedades.',
                ],
            );
        }

        return $equipamientos;
    }

    /**
     * @param  array<int, Policia>  $policias
     * @return array<int, Vehiculo>
     */
    private function vehiculos(array $policias): array
    {
        $datos = [
            ['AB123CD', 'Camioneta', 2021, 45000, 'disponible'],
            ['AC456EF', 'Automovil', 2019, 98000, 'en_uso'],
            ['AD789GH', 'Moto', 2022, 12000, 'disponible'],
            ['AE012IJ', 'Utilitario', 2018, 150000, 'mantenimiento'],
            ['AF345KL', 'Ambulancia', 2020, 67000, 'fuera_servicio'],
        ];

        $vehiculos = [];

        foreach ($datos as $i => [$dominio, $tipo, $anio, $km, $estado]) {
            $vehiculos[] = $vehiculo = Vehiculo::updateOrCreate(
                ['dominio' => $dominio],
                [
                    'tipo_vehiculo_id' => TipoVehiculo::where('nombre', $tipo)->firstOrFail()->id,
                    'anio' => $anio,
                    'numero_chasis' => 'CH-'.$dominio,
                    'numero_motor' => 'MT-'.$dominio,
                    'kilometraje' => $km,
                    'responsable_id' => $policias[4]->id,
                    'estado' => $estado,
                    'compania_seguro' => 'Seguros del Estado',
                    'numero_poliza' => 'POL-'.$dominio,
                    'vencimiento_seguro' => today()->addMonths($i * 3 - 1)->toDateString(),
                    'ultima_revision' => today()->subMonths(3)->toDateString(),
                    'proxima_revision' => today()->addMonths($i + 1)->toDateString(),
                    'fecha_adquisicion' => Carbon::create($anio, 3, 1)->toDateString(),
                ],
            );

            VehiculoMantenimiento::updateOrCreate(
                ['vehiculo_id' => $vehiculo->id, 'fecha' => today()->subMonths(3)->toDateString()],
                [
                    'tipo' => ['preventivo', 'correctivo', 'revision'][$i % 3],
                    'descripcion' => 'Service de prueba.',
                    'kilometraje' => $km - 5000,
                    'costo' => 80000 + $i * 20000,
                    'proveedor' => 'Taller Central',
                    'responsable_id' => $policias[4]->id,
                    'proxima_fecha' => today()->addMonths(3)->toDateString(),
                    'proximo_kilometraje' => $km + 10000,
                ],
            );
        }

        return $vehiculos;
    }

    /**
     * @param  array<int, Oficina>  $oficinas
     * @param  array<int, Policia>  $policias
     * @return array<int, Proyecto>
     */
    private function proyectos(array $oficinas, array $policias): array
    {
        $datos = [
            ['Digitalizacion de legajos', 'alta', 'en_curso', 45],
            ['Renovacion de flota', 'media', 'planificado', 0],
            ['Red de fibra optica', 'alta', 'en_curso', 70],
            ['Plan anual de capacitacion', 'baja', 'finalizado', 100],
            ['Vigilancia con drones', 'media', 'planificado', 10],
        ];

        $proyectos = [];

        foreach ($datos as $i => [$nombre, $prioridad, $estado, $avance]) {
            $proyectos[] = $proyecto = Proyecto::updateOrCreate(
                ['codigo' => sprintf('PRY-%04d', $i + 1)],
                [
                    'nombre' => $nombre,
                    'descripcion' => 'Proyecto de prueba.',
                    'objetivo' => 'Objetivo de prueba de '.strtolower($nombre).'.',
                    'oficina_id' => $oficinas[$i]->id,
                    'responsable_id' => $policias[$i]->id,
                    'fecha_inicio' => today()->subMonths(4)->toDateString(),
                    'fecha_fin' => today()->addMonths(8)->toDateString(),
                    'prioridad' => $prioridad,
                    'estado' => $estado,
                    'porcentaje_avance' => $avance,
                ],
            );

            ProyectoIntegrante::updateOrCreate(
                ['proyecto_id' => $proyecto->id, 'policia_id' => $policias[($i + 1) % 5]->id],
                ['fecha_ingreso' => today()->subMonths(4)->toDateString()],
            );
        }

        return $proyectos;
    }

    /**
     * @param  array<int, Oficina>  $oficinas
     * @param  array<int, Policia>  $policias
     */
    private function incidencias(array $oficinas, array $policias): void
    {
        $datos = [
            ['Electricidad', 'Corte de luz en el 2do piso', 'alta', 'pendiente'],
            ['Plomeria', 'Perdida de agua en banos', 'media', 'en_proceso'],
            ['Climatizacion', 'Aire acondicionado sin funcionar', 'media', 'resuelta'],
            ['Edilicio', 'Humedad en la pared del deposito', 'baja', 'pendiente'],
            ['Red', 'Caida de la red interna', 'alta', 'resuelta'],
        ];

        foreach ($datos as $i => [$tipo, $titulo, $prioridad, $estado]) {
            IncidenciaInfraestructura::updateOrCreate(
                ['titulo' => $titulo],
                [
                    'oficina_id' => $oficinas[$i]->id,
                    'tipo' => $tipo,
                    'descripcion' => 'Incidencia de prueba.',
                    'fecha_reporte' => now()->subDays(5 - $i),
                    'prioridad' => $prioridad,
                    'responsable_id' => $policias[2]->id,
                    'estado' => $estado,
                    'costo' => $estado === 'resuelta' ? 25000 : null,
                    'fecha_resolucion' => $estado === 'resuelta' ? now()->subDay() : null,
                ],
            );
        }
    }

    /**
     * @param  array<int, Policia>  $policias
     * @param  array<int, Proyecto>  $proyectos
     */
    private function movimientosEconomicos(array $policias, array $proyectos): void
    {
        $datos = [
            ['ingreso', 'Presupuesto', 'Partida presupuestaria trimestral', null, 5000000],
            ['egreso', 'Combustible', 'Carga de combustible de la flota', 'YPF', 350000],
            ['egreso', 'Insumos', 'Compra de toner e insumos', 'Libreria Central', 120000],
            ['egreso', 'Servicios', 'Mantenimiento de servidores', 'TecnoServicios SRL', 480000],
            ['ingreso', 'Donacion', 'Donacion de equipamiento', 'Municipalidad', 900000],
        ];

        foreach ($datos as $i => [$tipo, $categoria, $concepto, $proveedor, $monto]) {
            MovimientoEconomico::updateOrCreate(
                ['comprobante' => sprintf('CMP-%04d', $i + 1)],
                [
                    'tipo' => $tipo,
                    'categoria' => $categoria,
                    'fecha' => today()->subDays(20 - $i * 4)->toDateString(),
                    'concepto' => $concepto,
                    'descripcion' => 'Movimiento de prueba.',
                    'proveedor' => $proveedor,
                    'monto' => $monto,
                    'responsable_id' => $policias[$i]->id,
                    'proyecto_id' => $proyectos[$i]->id,
                    'estado' => 'registrado',
                ],
            );
        }
    }

    private function cajaChica(Usuario $admin): void
    {
        $datos = [
            ['ingreso', 50000, 'Tesoreria', null, 'Fondo inicial de caja chica'],
            ['egreso', 4500, null, 'Kiosco', 'Cafe y azucar para la oficina'],
            ['egreso', 12000, null, 'Ferreteria', 'Lamparas y cinta aisladora'],
            ['egreso', 8000, null, 'Correo', 'Envio de documentacion'],
            ['ingreso', 20000, 'Tesoreria', null, 'Reposicion de caja chica'],
        ];

        foreach ($datos as $i => [$tipo, $monto, $origen, $destino, $descripcion]) {
            CajaChicaMovimiento::updateOrCreate(
                ['descripcion' => $descripcion],
                [
                    'tipo' => $tipo,
                    'monto' => $monto,
                    'origen' => $origen,
                    'destino' => $destino,
                    'fecha' => today()->subDays(10 - $i * 2)->toDateString(),
                    'usuario_id' => $admin->id,
                ],
            );
        }
    }

    /**
     * @param  array<int, Vehiculo>  $vehiculos
     * @param  array<int, EquipamientoTecnologico>  $equipamientos
     * @param  array<int, Inventario>  $inventarios
     * @param  array<int, Actividad>  $actividades
     * @param  array<int, Proyecto>  $proyectos
     */
    private function alertas(Usuario $admin, array $vehiculos, array $equipamientos, array $inventarios, array $actividades, array $proyectos): void
    {
        $datos = [
            [$vehiculos[0], 'seguro_vencimiento', 'Seguro proximo a vencer', 'alta'],
            [$equipamientos[3], 'mantenimiento', 'Impresora en mantenimiento', 'media'],
            [$inventarios[3], 'mantenimiento', 'Radio pendiente de reparacion', 'media'],
            [$actividades[0], 'recordatorio', 'Reunion de coordinacion pendiente', 'baja'],
            [$proyectos[1], 'vencimiento', 'Proyecto sin iniciar', 'media'],
        ];

        foreach ($datos as $i => [$modelo, $tipo, $titulo, $prioridad]) {
            Alerta::updateOrCreate(
                ['alertable_tipo' => $modelo->getMorphClass(), 'alertable_id' => $modelo->id, 'titulo' => $titulo],
                [
                    'tipo' => $tipo,
                    'descripcion' => 'Alerta de prueba.',
                    'usuario_id' => $admin->id,
                    'prioridad' => $prioridad,
                    'fecha_generacion' => now()->subDays($i),
                    'fecha_vencimiento' => now()->addDays(7 + $i),
                    'estado' => 'pendiente',
                ],
            );
        }
    }

    /** @param  array<int, Policia>  $policias */
    private function auditorias(Usuario $admin, array $policias): void
    {
        $acciones = ['crear', 'editar', 'editar', 'crear', 'eliminar'];

        foreach ($acciones as $i => $accion) {
            Auditoria::updateOrCreate(
                ['tabla_afectada' => 'policias', 'registro_id' => $policias[$i]->id, 'accion' => $accion],
                [
                    'usuario_id' => $admin->id,
                    'valor_anterior' => $accion === 'crear' ? null : ['estado' => 'activo'],
                    'valor_nuevo' => $accion === 'eliminar' ? null : ['estado' => 'activo'],
                    'direccion_ip' => '127.0.0.1',
                ],
            );
        }
    }
}
