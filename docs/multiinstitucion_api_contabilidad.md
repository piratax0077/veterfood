# Multiinstitucion y API contable

## Objetivo

El sistema esta preparado para operar con varios centros, instituciones o comercios. Cada centro mantiene su informacion contable separada y los usuarios externos, como contadores, solo pueden acceder a los centros donde esten autorizados.

## Tablas principales

- `centros_medicos`: institucion, centro, comercio o entidad contable.
- `centro_medico_user`: vincula usuarios con centros y define rol, permisos y estado activo.
- Tablas contables con `centro_medico_id`: trabajadores, terceros, documentos tributarios, movimientos, convenios y otras tablas del modulo contable.

## Permisos

Los permisos disponibles para API son:

- `contabilidad:read`: consulta de datos.
- `contabilidad:write`: creacion y edicion.
- `contabilidad:pay`: pagos, liquidaciones y cierres.

Roles sugeridos:

- `administrador`: lectura, escritura y pagos.
- `contador`: lectura, escritura y pagos para el centro autorizado.
- `rrhh`: lectura y escritura sin pagos.
- `consulta`: solo lectura.

## Enrolamiento API de un contador externo

1. Crear usuario con rol general `contabilidad`.
2. Crear o seleccionar el centro en `centros_medicos`.
3. Vincularlo en `centro_medico_user` con `activo = true`.
4. Asignar permisos en JSON, por ejemplo:

```json
["contabilidad:read", "contabilidad:write", "contabilidad:pay"]
```

## Flujo de token

Endpoint:

```http
POST /api/auth/token
```

Body:

```json
{
  "email": "contador@institucion.cl",
  "password": "clave_segura",
  "device_name": "oficina-contador"
}
```

Respuesta:

```json
{
  "token_type": "Bearer",
  "access_token": "...",
  "user": {
    "id": 10,
    "name": "Contador Externo",
    "email": "contador@institucion.cl"
  },
  "centros": [
    {
      "id": 1,
      "rut": "76.000.000-0",
      "nombre": "Comercializadora Alimentos",
      "rol": "contador",
      "permisos": ["contabilidad:read", "contabilidad:write", "contabilidad:pay"]
    }
  ]
}
```

## Aislamiento de datos

Todas las rutas contables usan el ID del centro en la URL:

```http
GET /api/centros-medicos/{centroMedico}/contabilidad/movimientos
```

Antes de responder, el middleware valida:

- token Sanctum valido,
- centro activo,
- usuario vinculado al centro,
- vinculo activo,
- permiso requerido para la accion.

Si el contador intenta cambiar el ID del centro en la URL y no tiene acceso, recibe `403`.

## Vista web

El escritorio web contable usa:

```http
/centros-medicos/{centroMedico}/contabilidad
```

Los administradores internos pueden operar el modulo desde el panel general. Usuarios con rol `contabilidad` deben estar vinculados al centro en `centro_medico_user`.
