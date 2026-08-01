<?php
/**
 * Línea de tiempo de la relación (hitos, recuerdos en orden cronológico).
 */
class TimelineController
{
    private RecuerdoModel $recuerdoModel;

    public function __construct()
    {
        $this->recuerdoModel = new RecuerdoModel();
    }

    public function index(): void
    {
        $recuerdos = $this->recuerdoModel->obtenerTodosOrdenados();

        View::render('timeline', [
            'recuerdos' => $recuerdos,
        ]);
    }
}
