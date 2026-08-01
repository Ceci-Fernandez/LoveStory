<?php
/**
 * Cápsulas del tiempo, easter eggs y sorpresas varias.
 */
class SurpriseController
{
    private MensajeModel $mensajeModel;

    public function __construct()
    {
        $this->mensajeModel = new MensajeModel();
    }

    public function capsulas(): void
    {
        $mensajes = $this->mensajeModel->obtenerDisponibles(new DateTime());
        View::render('sorpresa', ['mensajes' => $mensajes]);
    }

    public function desbloquear(): void
    {
        // Lógica de easter eggs: contar clicks, validar respuesta, etc.
        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'mensaje' => 'Ahora sí... tengo otra sorpresa para vos.']);
    }
}
