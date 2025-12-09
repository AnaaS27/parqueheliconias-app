<?php
session_start();
include('../includes/verificar_sesion.php');
include('../includes/supabase.php');
include('../includes/email_api.php'); // ← tu nuevo sistema de correos

// ============================================
// 1️⃣ VALIDAR SESIÓN
// ============================================
if (!isset($_SESSION['usuario_id'])) {
    echo "<script>alert('Debes iniciar sesión.'); window.location='../login.php';</script>";
    exit;
}

$id_usuario = intval($_SESSION['usuario_id']);
$mensaje = "";

// ============================================
// 2️⃣ OBTENER DATOS DEL USUARIO DESDE SUPABASE
// ============================================
list($codeUser, $dataUser) = supabase_get("usuarios?id_usuario=eq.$id_usuario&select=*");

if ($codeUser !== 200 || empty($dataUser)) {
    echo "<script>alert('Error cargando datos.'); window.location='inicio.php';</script>";
    exit;
}

$usuario = $dataUser[0];

// ============================================
// 3️⃣ ACTUALIZAR DATOS DEL PERFIL
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_perfil'])) {

    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $correo = trim($_POST['correo']);
    $telefono = trim($_POST['telefono']);

    $actualizacion = [
        "nombre" => $nombre,
        "apellido" => $apellido,
        "correo" => $correo,
        "telefono" => $telefono
    ];

    list($updCode, $updData) = supabase_update("usuarios?id_usuario=eq.$id_usuario", $actualizacion);

    if ($updCode === 200) {

        // 📨 Enviar correo de cambios
        $infoCambios = "
            <p>Nombre: <b>$nombre</b></p>
            <p>Apellido: <b>$apellido</b></p>
            <p>Correo: <b>$correo</b></p>
            <p>Teléfono: <b>$telefono</b></p>
        ";

        enviarCorreoCambioDatos($correo, $nombre, $infoCambios);

        $mensaje = "✅ Datos actualizados correctamente.";
        $_SESSION['nombre'] = $nombre;

    } else {
        $mensaje = "❌ Error al actualizar los datos.";
    }
}

// ============================================
// 4️⃣ CAMBIO DE CONTRASEÑA
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_contrasena'])) {

    $actual = trim($_POST['contrasena_actual']);
    $nueva = trim($_POST['nueva_contrasena']);
    $confirmar = trim($_POST['confirmar_contrasena']);

    if (empty($actual) || empty($nueva) || empty($confirmar)) {
        $mensaje = "⚠️ Todos los campos son obligatorios.";
    } elseif (!password_verify($actual, $usuario['contrasena'])) {
        $mensaje = "❌ La contraseña actual no es correcta.";
    } elseif ($nueva !== $confirmar) {
        $mensaje = "⚠️ Las contraseñas nuevas no coinciden.";
    } elseif (strlen($nueva) < 6) {
        $mensaje = "⚠️ La nueva contraseña debe tener al menos 6 caracteres.";
    } else {

        $hash = password_hash($nueva, PASSWORD_DEFAULT);

        list($passCode, $passData) = supabase_update(
            "usuarios?id_usuario=eq.$id_usuario",
            ["contrasena" => $hash]
        );

        if ($passCode === 200) {

            // 📨 Enviar correo de seguridad
            enviarCorreoPassword($usuario["correo"], $usuario["nombre"]);

            $mensaje = "✅ Contraseña actualizada correctamente.";
        } else {
            $mensaje = "❌ Error al actualizar la contraseña.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Parque de las Heliconias</title>
    <link rel="stylesheet" href="../assets/css/estilos.css">
    <link rel="stylesheet" href="../assets/css/modal.css">

    <style>
        /* 🌿 Estilos del perfil */
        main.perfil-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 10px;
        }

        .perfil-card {
            width: 100%;
            max-width: 500px;
            background: #fff;
            border-radius: 12px;
            padding: 25px 30px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            border: 1px solid #ccc;
            margin-top: 15px;
        }

        .titulo-bienvenida {
            text-align: center;
            font-size: 1.8rem;
            color: #1b5e20;
        }

        .texto-subtitulo {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .form-perfil .campo {
            position: relative;
            display: flex;
            flex-direction: column;
            margin-bottom: 15px;
        }

        .form-perfil label {
            font-weight: 600;
            color: #2e7d32;
            margin-bottom: 5px;
        }

        .form-perfil input {
            width: 100%;
            padding: 10px 40px 10px 12px;
            border: 1px solid #2e8b57;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.2s ease-in-out;
        }

        .form-perfil input:focus {
            border-color: #388e3c;
            box-shadow: 0 0 4px rgba(56,142,60,0.3);
            outline: none;
        }

        /* 👁️ Icono mostrar/ocultar contraseña */
        .toggle-pass {
            position: absolute;
            right: 10px;
            top: 37px;
            cursor: pointer;
            color: #388e3c;
            font-size: 18px;
            user-select: none;
        }

        .acciones-perfil {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 25px;
        }

        .acciones-perfil .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 15px;
        }

        .mensaje-alerta {
            background-color: #e8f5e9;
            color: #1b5e20;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            text-align: center;
            width: 100%;
            max-width: 500px;
        }

        /* 🔒 Cambio de contraseña */
        .cambiar-pass {
            margin-top: 30px;
            border-top: 2px solid #ccc;
            padding-top: 20px;
        }

        .cambiar-pass h3 {
            color: #2e7d32;
            text-align: center;
            margin-bottom: 15px;
        }

        .mensaje-validacion {
            font-size: 0.9em;
            margin-top: 3px;
            height: 18px;
        }

        .valido {
            color: #2e7d32;
        }

        .invalido {
            color: #c62828;
        }
    </style>

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>

<body>
    <?php include('../includes/header.php'); ?>

    <main class="perfil-wrapper">
        <h2 class="titulo-bienvenida">👤 Mi Perfil</h2>
        <p class="texto-subtitulo">Consulta y actualiza tu información personal o cambia tu contraseña.</p>

        <?php if (!empty($mensaje)): ?>
            <div class="mensaje-alerta">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <!-- 🌿 Datos personales -->
        <section class="perfil-card">
            <form action="" method="POST" class="form-perfil">
                <input type="hidden" name="actualizar_perfil" value="1">

                <div class="campo">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" required 
                           value="<?php echo htmlspecialchars($usuario['nombre']); ?>">
                </div>

                <div class="campo">
                    <label for="apellido">Apellido:</label>
                    <input type="text" id="apellido" name="apellido" required 
                           value="<?php echo htmlspecialchars($usuario['apellido']); ?>">
                </div>

                <div class="campo">
                    <label for="correo">Correo electrónico:</label>
                    <input type="email" id="correo" name="correo" required 
                           value="<?php echo htmlspecialchars($usuario['correo']); ?>">
                </div>

                <div class="campo">
                    <label for="telefono">Teléfono:</label>
                    <input type="text" id="telefono" name="telefono" 
                           value="<?php echo htmlspecialchars($usuario['telefono']); ?>">
                </div>

                <div class="acciones-perfil">
                    <button type="submit" class="btn boton-verde">💾 Guardar cambios</button>
                    <a href="inicio.php" class="btn boton-azul">⬅ Volver</a>
                </div>
            </form>

            <!-- 🔐 Cambio de contraseña -->
            <div class="cambiar-pass">
                <h3>🔒 Cambiar Contraseña</h3>
                <form action="" method="POST" class="form-perfil" id="formCambioPass">
                    <input type="hidden" name="cambiar_contrasena" value="1">

                    <div class="campo">
                        <label for="contrasena_actual">Contraseña actual:</label>
                        <input type="password" id="contrasena_actual" name="contrasena_actual" required>
                        <span class="toggle-pass" onclick="togglePassword('contrasena_actual', this)">👁️</span>
                    </div>

                    <div class="campo">
                        <label for="nueva_contrasena">Nueva contraseña:</label>
                        <input type="password" id="nueva_contrasena" name="nueva_contrasena" required>
                        <span class="toggle-pass" onclick="togglePassword('nueva_contrasena', this)">👁️</span>
                        <div id="msgLongitud" class="mensaje-validacion"></div>
                    </div>

                    <div class="campo">
                        <label for="confirmar_contrasena">Confirmar nueva contraseña:</label>
                        <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" required>
                        <span class="toggle-pass" onclick="togglePassword('confirmar_contrasena', this)">👁️</span>
                        <div id="msgCoincidencia" class="mensaje-validacion"></div>
                    </div>

                    <div class="acciones-perfil">
                        <button type="submit" class="btn boton-verde" id="btnCambiarPass">🔄 Actualizar Contraseña</button>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <?php include('../includes/footer.php'); ?>

    <!-- 🌟 Validación visual + Mostrar/Ocultar -->
    <script>
        const nueva = document.getElementById("nueva_contrasena");
        const confirmar = document.getElementById("confirmar_contrasena");
        const msgLongitud = document.getElementById("msgLongitud");
        const msgCoincidencia = document.getElementById("msgCoincidencia");
        const btnCambiar = document.getElementById("btnCambiarPass");

        function validarCampos() {
            let valido = true;

            // Validar longitud
            if (nueva.value.length < 6) {
                msgLongitud.textContent = "⚠️ La contraseña debe tener al menos 6 caracteres.";
                msgLongitud.className = "mensaje-validacion invalido";
                nueva.style.borderColor = "#c62828";
                valido = false;
            } else {
                msgLongitud.textContent = "✅ Longitud correcta.";
                msgLongitud.className = "mensaje-validacion valido";
                nueva.style.borderColor = "#2e7d32";
            }

            // Validar coincidencia
            if (confirmar.value !== nueva.value || confirmar.value === "") {
                msgCoincidencia.textContent = "❌ Las contraseñas no coinciden.";
                msgCoincidencia.className = "mensaje-validacion invalido";
                confirmar.style.borderColor = "#c62828";
                valido = false;
            } else {
                msgCoincidencia.textContent = "✅ Las contraseñas coinciden.";
                msgCoincidencia.className = "mensaje-validacion valido";
                confirmar.style.borderColor = "#2e7d32";
            }

            btnCambiar.disabled = !valido;
        }

        nueva.addEventListener("input", validarCampos);
        confirmar.addEventListener("input", validarCampos);

        // 👁️ Mostrar / Ocultar contraseñas
        function togglePassword(id, icon) {
            const input = document.getElementById(id);
            if (input.type === "password") {
                input.type = "text";
                icon.textContent = "🙈";
            } else {
                input.type = "password";
                icon.textContent = "👁️";
            }
        }
    </script>
</body>
</html>

