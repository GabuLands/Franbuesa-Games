<?php
// 1. Cargamos el autoloader de Composer (esto activa todas las librerías)
require 'vendor/autoload.php';
require 'conexion.php';

use Dompdf\Dompdf;

// 2. Obtenemos los datos de la base de datos
try {
    $sentencia = $pdo->query("SELECT * FROM USUARIO");
    $usuarios = $sentencia->fetchAll();
} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}

// 3. Diseño del HTML para el PDF
$html = '
<h1 style="text-align: center; color: #7d2ae8; font-family: sans-serif;">Franbuesa-Games</h1>
<h2 style="text-align: center; font-family: sans-serif;">Reporte General de Usuarios registrados</h2>
<table border="1" width="100%" style="border-collapse: collapse; font-family: sans-serif;">
    <thead style="background-color: #7d2ae8; color: white;">
        <tr>
            <th style="padding: 10px;">ID</th>
            <th style="padding: 10px;">Nombre</th>
            <th style="padding: 10px;">Correo</th>
            <th style="padding: 10px;">Teléfono</th>
        </tr>
    </thead>
    <tbody>';

foreach ($usuarios as $u) {
    $html .= '
        <tr>
            <td style="padding: 8px; text-align: center;">' . $u['ID_Usuario'] . '</td>
            <td style="padding: 8px;">' . htmlspecialchars($u['Nombre_Completo']) . '</td>
            <td style="padding: 8px;">' . htmlspecialchars($u['Correo_Electronico']) . '</td>
            <td style="padding: 8px; text-align: center;">' . $u['Telefono'] . '</td>
        </tr>';
}

$html .= '
    </tbody>
</table>
<p style="text-align: right; font-family: sans-serif; font-size: 12px; margin-top: 20px;">
    Reporte generado el: ' . date("d/m/Y H:i:s") . '
</p>';

// 4. Configuración de Dompdf
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// 5. Salida al navegador (attachment false para que se abra en el navegador)
$dompdf->stream("Reporte_Usuarios_Franbuesa.pdf", array("Attachment" => false));
?>
