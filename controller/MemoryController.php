<?php
/**
 * "Frasco de recuerdos": muestra un recuerdo aleatorio + permite agregar nuevos.
 */
class MemoryController
{
    private RecuerdoModel $recuerdoModel;

    public function __construct()
    {
        $this->recuerdoModel = new RecuerdoModel();
    }

    public function aleatorio(): void
    {
        $recuerdo = $this->recuerdoModel->obtenerAleatorio();
        header('Content-Type: application/json');
        echo json_encode($recuerdo);
    }

    public function agregar(): void
    {
        if (!Auth::esAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'No autorizado']);
            return;
        }

        $texto = trim($_POST['texto'] ?? '');
        $foto = $_FILES['foto'] ?? null;

        // TODO: procesar subida de foto (validar tipo/tamaño, mover a public/uploads)

        $id = $this->recuerdoModel->crear($texto, null);
        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'id' => $id]);
    }
}
