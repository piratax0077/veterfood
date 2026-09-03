# Contabilidad integrada

El modulo contable quedo integrado dentro de este proyecto:

- Proyecto: `alimentos-laravel13`
- Laravel: `13.17.0`
- PHP runtime local: `8.4.22`
- API segura: Laravel Sanctum `^4.3`
- Base local: las tablas contables fueron recreadas limpias y compatibles con el sistema nuevo.

## Accesos

- Usuario web contabilidad: `contabilidad@alimentos.local`
- Clave inicial local: `12345678`
- Rol: `contabilidad`

## Rutas principales

- Panel de integracion: `/contabilidad`
- Escritorio contable interno: `/centros-medicos/1/contabilidad`
- Token API: `POST /api/auth/token`
- API contable: `/api/centros-medicos/1/contabilidad/...`

## Seguridad

Las vistas web de contabilidad usan:

- `auth`
- `role:admin,contabilidad`
- `2fa`
- `secure.session`

La API usa:

- Token Bearer de Sanctum
- Relacion activa en `centro_medico_user`
- Permisos `contabilidad:read`, `contabilidad:write`, `contabilidad:pay`

