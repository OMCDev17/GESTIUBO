# Rutas (GestIUBO)

Estas rutas están configuradas en `/.htaccess`.

## Acceso y paneles
- `/acceso` -> `Loggin.php`
- `/login` -> `Loggin.php`
- `/usuario` -> `empleado.php`
- `/empleado` -> `empleado.php`
- `/admin` -> `admin.php`
- `/seguridad` -> `seguridad.php`
- `/coordinador` -> `supervisor.php`
- `/supervisor` -> `supervisor.php`

## Registro y estancias
- `/registro` -> `Formulario.php`
- `/nueva-estancia` -> `Formulario.php?mode=newstay`
- `/registro-exitoso` -> `registro_exitoso.php`

## Contraseñas
- `/recuperar` -> `Recuperacion.html`
- `/cambiar-password` -> `change_password.php`
- `/restablecer-password` -> `resetear_contraseña.php`

## Sesión y solicitudes
- `/logout` -> `api/logout.php`
- `/aprobar-solicitud` -> `approve_group_request.php`

## Notas
- Requiere Apache con `mod_rewrite` habilitado y `AllowOverride All` en el VirtualHost/directorio.
- Las rutas existentes antiguas siguen funcionando.
