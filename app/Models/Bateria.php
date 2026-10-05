<?php

namespace App\Models;

use App\Models\Concerns\RegistraAuditoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bateria extends Model
{
    use RegistraAuditoria;

    protected $table = 'baterias';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'dron_robot_id',
        'codigo',
        'marca',
        'modelo',
        'numero_serie',
        'capacidad',
        'ciclos',
        'estado',
        'fecha_adquisicion',
        'ultima_revision',
        'observaciones',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ciclos' => 'integer',
            'fecha_adquisicion' => 'date',
            'ultima_revision' => 'date',
        ];
    }

    public function droneRobot(): BelongsTo
    {
        return $this->belongsTo(DroneRobot::class, 'dron_robot_id');
    }
}
