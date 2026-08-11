<?php
// 1. Iniciar sesión y aplicar seguridad
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// 2. Conexión a la base de datos
require_once 'conexion.php';

// 1. Verificamos si el usuario envió algo por la barra de búsqueda
$busqueda = isset($_GET['buscar']) ? $_GET['buscar'] : '';

if ($busqueda != '') {
    // 2. Si hay búsqueda, preparamos la consulta con LIKE para nombre o categoría
    $sql = "SELECT p.id, p.nombre_producto, c.nombre_categoria, p.stock, p.precio 
            FROM productos p 
            INNER JOIN categorias c ON p.categoria_id = c.id 
            WHERE p.nombre_producto LIKE ? OR c.nombre_categoria LIKE ? 
            ORDER BY p.id ASC";
            
    $stmt = $conn->prepare($sql);
    // Le pegamos los comodines % al texto del usuario
    $param_busqueda = "%" . $busqueda . "%";
    // Vinculamos el parámetro dos veces (una para el nombre, otra para la categoría)
    $stmt->bind_param("ss", $param_busqueda, $param_busqueda);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $stmt->close();
} else {
    // 3. Si la barra de búsqueda está vacía, mostramos el inventario normal completo
    $sql = "SELECT p.id, p.nombre_producto, c.nombre_categoria, p.stock, p.precio 
            FROM productos p 
            INNER JOIN categorias c ON p.categoria_id = c.id 
            ORDER BY p.id ASC";
    $resultado = $conn->query($sql);
}

// Validar errores de consulta generales si aplica
if (!$resultado) {
    die("Error en la consulta: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inventario - Sistema de Ventas</title>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f8fafc;
    padding: 20px;
}

.container {
    max-width: 1000px;
    margin: 0 auto;
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid #e2e8f0;
    padding-bottom: 10px;
    margin-bottom: 20px;
}

h2 {
    color: #0f172a;
    margin: 0;
}

.btn-nuevo {
    background: #f63b92;
    color: white;
    padding: 10px 15px;
    text-decoration: none;
    border-radius: 5px;
    font-weight: bold;
}

.btn-salir {
    background-color: #ef4444;
    color: white;
    text-decoration: none;
    padding: 8px 15px;
    border-radius: 5px;
    font-weight: bold;
    margin-left: 10px;
}

.btn-salir:hover {
    background-color: #dc2626;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

th, td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
}

th {
    background-color: #f1f5f9;
    color: #334155;
}

tr:hover {
    background-color: #f8fafc;
}

.stock-bajo {
    color: #dc2626;
    font-weight: bold;
}

/* --- ESTILOS AGREGADOS --- */
.btn-editar { 
    background-color: #f59e0b; 
    color: white; 
    padding: 6px 12px; 
    text-decoration: none; 
    border-radius: 4px; 
    font-weight: bold; 
    margin-right: 5px; 
    font-size: 13px;
    display: inline-block;
}

.btn-eliminar {
    background-color: #ef4444;
    color: white;
    padding: 6px 12px;
    text-decoration: none;
    border-radius: 4px;
    font-size: 13px;
    font-weight: bold;
    display: inline-block;
}

.btn-eliminar:hover {
    background-color: #b91c1c;
}
</style>

</head>

<body>

<div class="container">

    <div class="header">
        <h2>Catálogo de Inventario</h2>

        <div>
            Usuario:
            <strong>
                <?php echo $_SESSION['nombre']; ?>
            </strong>

            <a href="logout.php" class="btn-salir">
                Cerrar Sesión
            </a>
        </div>
    </div>

    <!-- Formulario de Búsqueda y Botón Nuevo Producto -->
    <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
        <a href="nuevo_producto.php" style="background: #3b82f6; color: white; padding: 10px; text-decoration: none; border-radius: 5px; font-weight: bold;">+ Nuevo Producto</a>
        
        <!-- Formulario de Búsqueda -->
        <form method="GET" style="display: flex; gap: 10px;">
            <input type="text" name="buscar" placeholder="Buscar producto o categoría..." 
                   value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>" 
                   style="padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; width: 250px;">
            <button type="submit" style="background: #10b981; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; font-weight: bold;">🔍 Buscar</button>
            <a href="inventario.php" style="background: #64748b; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; display: flex; align-items: center;">Limpiar</a>
        </form>
    </div>

    <table>

        <thead>
            <tr>
                <th>Código</th>
                <th>Nombre del Producto</th>
                <th>Categoría</th>
                <th>Stock</th>
                <th>Precio Unitario</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>

        <?php
        if ($resultado->num_rows > 0) {

            while ($fila = $resultado->fetch_assoc()) {

                $claseStock = ($fila['stock'] < 10)
                    ? "stock-bajo"
                    : "";
        ?>

            <tr>
                <td>
                    <?php echo $fila['id']; ?>
                </td>

                <td>
                    <?php echo $fila['nombre_producto']; ?>
                </td>

                <td>
                    <?php echo $fila['nombre_categoria']; ?>
                </td>

                <td class="<?php echo $claseStock; ?>">
                    <?php echo $fila['stock']; ?> unds.
                </td>

                <td>
                    $<?php echo number_format($fila['precio'], 2); ?>
                </td>

                <td>
                    <a href="editar_producto.php?id=<?php echo $fila['id']; ?>" class="btn-editar">
                        ✏️ Editar
                    </a>

                    <a href="eliminar_producto.php?id=<?php echo $fila['id']; ?>"
                       class="btn-eliminar"
                       onclick="return confirm('¿Seguro que deseas eliminar el producto: <?php echo $fila['nombre_producto']; ?>?');">
                       🗑️ Eliminar
                    </a>
                </td>

            </tr>

        <?php
            }

        } else {
        ?>

            <tr>
                <td colspan="6" style="text-align:center;">
                    No hay productos registrados en el sistema o que coincidan con la búsqueda.
                </td>
            </tr>

        <?php
        }
        ?>

        </tbody>

    </table>

</div>

<?php
// Liberar memoria
$resultado->free();

// Cerrar conexión
$conn->close();
?>

</body>
</html>