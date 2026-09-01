<?php
session_start();

// 1. Candado de seguridad: Solo usuarios logueados
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $proveedor_id     = $_POST['proveedor_id'];
    $producto_id      = $_POST['producto_id'];
    $cantidad         = (int)$_POST['cantidad'];
    $precio_unitario  = (float)$_POST['precio_unitario'];
    $total_compra     = $cantidad * $precio_unitario;

    // Iniciar transacción de base de datos
    $conn->begin_transaction();

    try {
        // --- FASE 1: INSERTAR CABECERA DE LA COMPRA ---
        $sql_cabecera = "INSERT INTO compras (proveedor_id, total, fecha) VALUES (?, ?, NOW())";
        $stmt1 = $conn->prepare($sql_cabecera);
        $stmt1->bind_param("id", $proveedor_id, $total_compra);
        $stmt1->execute();
        
        // Obtener el ID generado para la compra recién creada
        $compra_id = $conn->insert_id;
        $stmt1->close();

        // --- FASE 2: INSERTAR DETALLE DE LA COMPRA ---
        $sql_detalle = "INSERT INTO detalle_compras (compra_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)";
        $stmt2 = $conn->prepare($sql_detalle);
        $stmt2->bind_param("iiid", $compra_id, $producto_id, $cantidad, $precio_unitario);
        $stmt2->execute();
        $stmt2->close();

        // --- FASE 3: ACTUALIZAR EL INVENTARIO FÍSICO (Actualización Relativa) ---
        // Le ordenamos a MySQL que sume la cantidad comprada al stock actual del producto
        $sql_stock = "UPDATE productos SET stock = stock + ? WHERE id = ?";
        $stmt3 = $conn->prepare($sql_stock);
        // Vinculamos la cantidad comprada y el ID del producto (ambos enteros "ii")
        $stmt3->bind_param("ii", $cantidad, $producto_id);
        $stmt3->execute();
        $stmt3->close();

        // Confirmar la transacción
        $conn->commit();

        // Redirigir al dashboard tras completar la compra con éxito
        header("Location: dashboard.php");
        exit();

    } catch (Exception $e) {
        // En caso de error, revertir todos los cambios
        $conn->rollback();
        echo "Error al procesar la compra: " . $e->getMessage();
    }
} else {
    header("Location: dashboard.php");
    exit();
}
?>