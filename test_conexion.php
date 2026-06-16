<?php
// Incluir el puente de conexión que programamos en el Paso 2
require_once 'conexion.php';

try {
// Definir la consulta SQL para traer los productos
$query = "SELECT p.nombre_producto, p.precio, p.stock FROM productos p";

// Ejecutar la consulta en la base de datos a través del objeto $conn
$result = $conn->query($query);

echo "<h1>Enlace Exitoso: Conexión y Consulta Verificadas</h1>";
echo "<p>A continuación se listan los productos recuperados desde MySQL con
MySQLi:</p>";
echo "<ul>";

// Recuperar los registros fila por fila como un arreglo asociativo
while ($prod = $result->fetch_assoc()) {
echo "<li><strong>" . $prod['nombre_producto'] . "</strong> - Precio: $" . $prod['precio'] . " |
Stock: " . $prod['stock'] . " unidades.</li>";
}
echo "</ul>";

// Liberar la memoria del resultado
$result->free();

} catch (mysqli_sql_exception $e) {
echo "Error al consultar los datos: " . $e->getMessage();
}
?>