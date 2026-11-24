# Izipay Mi Cuenta Web: integración de prueba

Se utiliza la API REST V4 de Mi Cuenta Web, no el SDK de Punto Web. Credenciales privadas solo en `.env`. El modo de producción está bloqueado. No se garantiza la disponibilidad de QR/Yape: depende de los medios habilitados en la tienda.

El botón ciudadano abre el formulario oficial con un formToken generado en el servidor por S/ 10.30 (1030 céntimos), PEN y una referencia persistente. Nunca se reciben datos de tarjeta en nuestros controladores. No se cambia una orden preexistente de TAYPI automáticamente.

La vista usa un contenedor `kr-embedded` vacío para que Krypton renderice el formulario según los medios habilitados para la tienda. No agregar manualmente campos como `kr-pan`, `kr-expiry` o `kr-security-code`: eso fija los controles de tarjeta y no habilita Yape. La disponibilidad de Yape en pruebas debe confirmarse con Izipay para las credenciales y tienda de prueba configuradas.

El retorno del navegador se valida con HMAC de test; la IPN se valida con contraseña REST de test. Ambos comprueban firma, tienda, modo TEST, importe, moneda, referencia y estado PAID/CLOSED. La actualización es transaccional e idempotente. La confirmación deja la solicitud pendiente de revisión humana; no acredita abono bancario real.

Configurar en el Back Office la notificación REST a `/pagos/izipay/notificacion` sobre una URL pública HTTPS del proyecto. Desde la laptop, el retorno del navegador puede procesarse, pero Izipay no puede acceder a `127.0.0.1` para entregar IPN. Si el navegador se cierra antes del retorno, la confirmación requiere IPN accesible. Producción, conciliación bancaria y reembolsos aún no habilitados.

Referencias oficiales:
- https://github.com/izipay-pe/Server-PaymentForm-NodeJS
- https://github.com/izipay-pe/Embedded-PaymentForm-Laravel

Pruebas automatizadas usan datos ficticios y respuestas simuladas. Falta completar una operación interactiva en el formulario de la tienda para validar los medios realmente habilitados.
