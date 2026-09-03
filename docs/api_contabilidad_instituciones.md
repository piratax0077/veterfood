# API contabilidad por instituciones

El acceso contable se resuelve por token y por contratos/permisos en `centro_medico_user`.

## Tipos de acceso

- `contador`: puede tener muchos centros/clientes. Permisos: lectura, escritura contable y pagos.
- `central_alimentos`: representa al sistema de alimentos. Permisos: lectura, subir facturas emitidas y formularios autorizados.
- `institucion`: puede consultar y enviar documentos propios, sin operar pagos contables generales.
- `consulta`: solo lectura.

## Login API

`POST /api/auth/token`

```json
{
  "email": "central.alimentos.api@alimentos.local",
  "password": "12345678",
  "device_name": "alimentos-backend"
}
```

La respuesta incluye el usuario, su tipo de acceso y los centros autorizados. Si es contador, puede recibir varios centros.

## Subir factura emitida desde alimentos

`POST /api/centros-medicos/{centroMedico}/contabilidad/facturas-emitidas`

Requiere permiso `contabilidad:invoice-upload`.

```json
{
  "tipo_documento": "factura",
  "folio": "F-000123",
  "fecha_emision": "2026-06-29",
  "receptor": {
    "rut": "11.111.111-1",
    "razon_social": "Cliente Demo",
    "giro": "Persona natural",
    "email": "cliente@example.com",
    "direccion": "Santiago Centro",
    "comuna": "Santiago"
  },
  "detalles": [
    {
      "codigo": "ALIM-001",
      "descripcion": "Alimento mascota adulto",
      "cantidad": 2,
      "precio_unitario": 34990,
      "descuento_porcentaje": 0
    }
  ],
  "observaciones": "Factura enviada desde central de alimentos"
}
```

El endpoint crea o actualiza el receptor como tercero del centro y registra el documento como venta emitida.
