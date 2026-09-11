# PRD - Modulos Pendientes para Adecuacion Operativa y Facturacion Electronica

## 1. Contexto

Este proyecto ya cuenta con una base importante avanzada para facturacion electronica sobre Peru, tomando como referencia `ventas-up2026` y aterrizandolo a este sistema no SaaS.

Ya quedaron encaminados estos bloques:

- configuracion de empresa (`/business`) orientada a emisor real
- clientes con tipo de documento, consulta RENIEC/SUNAT y ubigeo
- catalogo de productos y servicios preparado para codigo SUNAT y afectacion IGV
- base tecnica de facturacion electronica
- seeders y soporte inicial de ubigeo

Antes de integrar completamente el flujo final de emision desde ventas, todavia faltan varios modulos operativos por acomodar para que todo quede consistente y no se mezcle logica antigua con la nueva estructura.

## 2. Objetivo General

Adecuar los modulos operativos restantes del sistema para que trabajen de forma consistente con:

- la configuracion de empresa emisora MYTEMS E.I.R.L.
- la base de facturacion electronica
- el nuevo esquema de clientes
- el catalogo de productos y servicios
- la gestion de stock por almacen
- la futura emision de nota de venta, boleta y factura

## 3. Resultado Esperado

Al finalizar esta etapa, el sistema debe permitir comprar, vender, cotizar, controlar stock, manejar caja y emitir comprobantes sin dobles logicas ni datos incompletos.

Debe existir coherencia entre:

- productos y servicios
- stock por almacen
- clientes y proveedores fiscales
- series y tipos de documento
- caja y medios de pago
- reportes
- nota de venta versus comprobante electronico

## 4. Modulos Pendientes

Los modulos pendientes de adecuacion son:

1. Productos por almacen
2. Proveedores
3. Compras
4. POS
5. Comprobantes
6. Notas de venta
7. Arqueo de cajas
8. Cotizaciones
9. Reportes


## 5. Detalle por Modulo

### 5.1 Proveedores

#### Objetivo

Formalizar el registro de proveedores para que compras trabaje con informacion fiscal real y no con un catalogo basico.

#### Alcance

- tipo de documento
- numero de documento
- razon social o nombre
- direccion fiscal
- ubigeo
- telefono
- correo
- validacion de duplicados por documento
- consulta de RUC si se reutiliza la misma logica de clientes

#### Criterios de aceptacion

- no se pueden duplicar proveedores por documento
- compras puede seleccionar proveedores con datos completos
- el modulo queda visualmente alineado al template actual

### 5.2 Compras

#### Objetivo

Modernizar el flujo de compras para que alimente stock, costos y trazabilidad por almacen de forma segura.

#### Alcance

- seleccionar proveedor formal
- seleccionar almacen destino
- registrar documento de compra
- registrar productos o servicios
- actualizar stock solo cuando el item sea producto
- actualizar costo de compra segun reglas definidas
- controlar duplicados por documento de compra cuando corresponda

#### Criterios de aceptacion

- una compra de productos incrementa stock en el almacen correcto
- una compra de servicios no toca stock
- el costo queda reflejado correctamente

### 5.3 POS

#### Objetivo

Convertir el POS en el modulo central de venta compatible con nota de venta, boleta electronica y factura electronica.

#### Alcance

- selector de tipo de comprobante
- selector de serie segun tipo
- validacion de cliente por documento
- reglas distintas para nota, boleta y factura
- uso correcto de productos y servicios
- calculo de IGV segun afectacion
- control de stock por almacen
- registro de medios de pago
- integracion con `sale_notes` y `billings` segun corresponda

#### Criterios de aceptacion

- una venta puede terminar como nota de venta, boleta o factura
- los montos cuadran con el catalogo tributario
- el stock solo baja para productos

### 5.4 Comprobantes

#### Objetivo

Cerrar el flujo funcional del modulo de facturacion electronica ya integrado en backend.

#### Alcance

- listado de boletas y facturas
- estados de emision y envio
- reintento de envio
- impresion o PDF
- visualizacion de informacion relevante del comprobante
- filtros por fecha, cliente, serie, correlativo y estado
- base para anulacion o notas de credito en fase posterior

#### Criterios de aceptacion

- el usuario puede listar, revisar, reenviar e imprimir comprobantes
- el comprobante usa la configuracion fiscal de `/business`

### 5.5 Notas de Venta

#### Objetivo

Separar claramente la logica interna de nota de venta respecto de la facturacion electronica.

#### Alcance

- mantener flujo simple para nota de venta
- permitir uso con cliente general o cliente formal
- descontar stock cuando corresponda
- imprimir comprobante interno
- evitar mezclar reglas de factura/boleta en este flujo

#### Criterios de aceptacion

- la nota de venta sigue operativa sin interferir con boleta/factura

### 5.6 Arqueo de Cajas

#### Objetivo

Alinear caja con las ventas reales del POS y los medios de pago registrados.

#### Alcance

- apertura de caja
- cierre de caja
- control de monto esperado versus monto real
- relacion con ventas, pagos y usuario
- filtros por fecha, caja, estado y usuario

#### Criterios de aceptacion

- el cierre de caja refleja lo realmente vendido
- los totales coinciden con ventas y medios de pago

### 5.7 Cotizaciones

#### Objetivo

Actualizar cotizaciones para que use el nuevo catalogo y clientes mas formales.

#### Alcance

- seleccionar cliente con documento
- usar productos y servicios
- calcular IGV de forma coherente con ventas
- permitir futura conversion a venta o POS

#### Criterios de aceptacion

- la cotizacion mantiene consistencia tributaria y comercial

### 5.8 Reportes

#### Objetivo

Reordenar reportes para que reflejen correctamente la nueva estructura operativa y tributaria.

#### Alcance

- ventas por fecha
- ventas por producto
- ventas por cliente
- ventas por tipo de comprobante
- ventas por medio de pago
- stock por almacen
- compras por proveedor
- caja diaria
- comprobantes emitidos y estados

#### Criterios de aceptacion

- los totales reportados cuadran con ventas, caja y comprobantes
- no se duplican montos entre `sale_notes` y `billings`

### 5.9 Productos por Almacen

#### Objetivo

Terminar de cerrar la administracion de stock por almacen como modulo transversal para compras, ventas y reportes.

#### Estado actual

Este modulo ya fue saneado en parte, pero todavia conviene cerrarlo mejor antes de tocar el flujo final de ventas.

#### Pendientes principales

- terminar de pulir UX
- revisar reglas de edicion de stock y precios
- reforzar coherencia con compras
- reforzar coherencia con POS
- revisar tratamiento de servicios dentro de este modulo
- validar importacion/exportacion si se mantendra

#### Criterios de aceptacion

- el stock por almacen se comporta de forma consistente en todos los modulos
- los servicios no se tratan como inventario fisico

## 6. Orden Recomendado de Implementacion

Se recomienda avanzar en este orden:

1. Productos por almacen
2. Proveedores
3. Compras
4. POS
5. Comprobantes
6. Notas de venta
7. Arqueo de cajas
8. Cotizaciones
9. Reportes

## 7. Dependencias

- POS depende de clientes, business, series, productos y stock por almacen
- Comprobantes depende de POS y configuracion fiscal lista
- Compras depende de proveedores y almacenes
- Reportes depende de que ventas, compras y caja ya esten estables

## 8. Riesgos a Controlar

- mezclar logica antigua de `sale_notes` con `billings`
- descontar stock desde modulos distintos sin reglas comunes
- reportar datos duplicados o inconsistentes
- emitir comprobantes con cliente o producto incompleto

## 9. Definicion de Listo

Cada modulo se considera listo cuando:

- valida correctamente sus datos
- conserva coherencia visual con el template actual
- respeta productos versus servicios
- respeta stock por almacen si aplica
- no rompe la facturacion electronica
- deja trazabilidad suficiente para caja y reportes

## 10. Texto Sugerido para Abrir el Siguiente Chat

Puedes iniciar la siguiente conversacion con algo como esto:

> Quiero continuar con la adecuacion del sistema segun el PRD de `docs/PRD_MODULOS_PENDIENTES_FE.md`. Empecemos por el modulo de [nombre del modulo]. Quiero que te guies de `ventas-up2026`, mantengas la linea visual del template actual y dejes el flujo listo para convivir con facturacion electronica.

## 11. Forma Recomendada de Pedirme Cada Modulo

Si quieres que avancemos ordenadamente, puedes escribirme con esta estructura:

```text
Continuemos con el modulo de [Compras / Proveedores / POS / etc.].
Objetivo: [que quieres dejar listo]
Prioridad: [alta / media]
Referencia: guiarnos de ventas-up2026
Restricciones: mantener template actual, no meter logica SaaS, respetar facturacion electronica
```
