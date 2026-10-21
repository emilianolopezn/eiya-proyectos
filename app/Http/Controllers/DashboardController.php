<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): View
    {
        $projects = [
            ['id' => 'EI-001', 'name' => 'Hoja de ruta estratégica', 'client' => 'Grupo Horizonte', 'line' => 'Advisory', 'stage' => 'Estructurar', 'progress' => 65, 'status' => 'En curso', 'tone' => 'sage', 'initials' => 'GH', 'owner' => 'Ana Martínez', 'team' => ['AM', 'CR'], 'due' => '28 oct', 'description' => 'Diagnóstico organizacional y definición de prioridades para construir una hoja de ruta de transformación con resultados verificables.'],
            ['id' => 'EI-002', 'name' => 'Gobierno de datos e IA', 'client' => 'Norte Industrial', 'line' => 'Framework', 'stage' => 'Comprender', 'progress' => 30, 'status' => 'En curso', 'tone' => 'blue', 'initials' => 'NI', 'owner' => 'Carlos Rivera', 'team' => ['CR', 'LS'], 'due' => '04 nov', 'description' => 'Evaluación de capacidades, procesos y riesgos para estructurar un marco de gobierno de datos e inteligencia artificial.'],
            ['id' => 'EI-003', 'name' => 'Asistente de conocimiento', 'client' => 'Arboleda', 'line' => 'Labs', 'stage' => 'Implementar', 'progress' => 82, 'status' => 'Por revisar', 'tone' => 'ochre', 'initials' => 'AR', 'owner' => 'Lucía Salazar', 'team' => ['LS', 'AM'], 'due' => '24 oct', 'description' => 'Piloto de un asistente para consultar conocimiento interno, con validación técnica, criterios de calidad y seguimiento de adopción.'],
            ['id' => 'EI-004', 'name' => 'Liderazgo para la transformación', 'client' => 'Grupo Horizonte', 'line' => 'Academy', 'stage' => 'Evolucionar', 'progress' => 100, 'status' => 'Completado', 'tone' => 'stone', 'initials' => 'GH', 'owner' => 'Ana Martínez', 'team' => ['AM', 'LS'], 'due' => '19 oct', 'description' => 'Programa de desarrollo de capacidades para equipos directivos, con evaluación de aprendizaje y plan de aplicación.'],
        ];

        return view('dashboard', [
            'projects' => $projects,
            'activeProjects' => collect($projects)->where('progress', '<', 100)->count(),
            'averageProgress' => (int) round(collect($projects)->avg('progress')),
            'completedProjects' => collect($projects)->where('progress', 100)->count(),
        ]);
    }
}
