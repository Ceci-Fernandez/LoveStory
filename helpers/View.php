<?php
/**
 * Helper mínimo para renderizar vistas.
 * Pensado para funcionar con Mustache (requiere `composer require mustache/mustache`)
 * pero incluye un modo "plano" con PHP puro por si todavía no se instaló la librería.
 */

class View
{
    public static function render(string $template, array $data = []): void
    {
        $mustacheFile = __DIR__ . '/../view/' . $template . '.mustache';

        if (class_exists('Mustache_Engine') && file_exists($mustacheFile)) {
            $m = new Mustache_Engine([
                'loader' => new Mustache_Loader_FilesystemLoader(__DIR__ . '/../view'),
            ]);
            echo $m->render($template, $data);
            return;
        }

        // Fallback simple si todavía no se instaló Mustache vía Composer
        extract($data);
        $phpFile = __DIR__ . '/../view/' . $template . '.php';
        if (file_exists($phpFile)) {
            include $phpFile;
        } else {
            echo "Vista '$template' no encontrada (ni .mustache ni .php).";
        }
    }
}
