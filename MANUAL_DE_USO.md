# Manual explicativo y de uso — Librerías Paulinas

## 1. Propósito de este documento

Este manual explica el sitio web de Librerías Paulinas tal como funciona actualmente. Está pensado para:

- conocer qué puede hacer hoy una persona visitante;
- aprender a recorrer el catálogo y encontrar un libro;
- comprender la ficha de cada producto;
- probar el carrito y la consulta por WhatsApp;
- identificar qué botones son reales y cuáles son demostrativos;
- levantar el proyecto en un computador para observarlo;
- orientar pruebas funcionales y futuras entregas.

El sitio está en desarrollo incremental. La parte implementada y operativa comprende la portada, el catálogo, las páginas de categorías y autores, la ficha de libro, la consulta por WhatsApp y un carrito básico. Las páginas institucionales completas, el checkout, el pago, los pedidos, el despacho, las cuentas y los favoritos todavía no están terminados.

> Estado de este manual: describe la aplicación completada hasta el Prompt 6. El Prompt 7 quedó pausado antes de conectar sus páginas y formulario.

## 2. Resumen ejecutivo del sitio

El proyecto es una librería en línea orientada inicialmente a presentar el catálogo de Paulinas Chile y facilitar la consulta de títulos. Su comportamiento principal es el siguiente:

1. La portada presenta novedades, categorías, destacados, recursos y librerías demostrativas.
2. El buscador conduce al catálogo y busca en varios datos del libro.
3. El catálogo permite filtrar, ordenar y paginar sin recargar toda la aplicación.
4. Cada ficha muestra información editorial, disponibilidad, galería y títulos relacionados.
5. Una persona puede agregar libros a un carrito guardado temporalmente en su sesión.
6. La ficha puede abrir WhatsApp con un mensaje previamente construido para ese libro.
7. El carrito permite modificar cantidades, eliminar líneas y ver subtotales.
8. La compra no se puede finalizar todavía: no hay checkout, pedido, despacho ni pago.

La interfaz es responsive: cambia su distribución para escritorio, tablet y teléfono. La navegación entre páginas utiliza Inertia, por lo que se comporta como una aplicación fluida aunque las decisiones y datos sigan controlados por Laravel en el servidor.

## 3. Qué está operativo y qué está pendiente

### 3.1 Funciones operativas

- Portada pública en `/`.
- Catálogo completo en `/libros`.
- Búsqueda por texto o ISBN.
- Filtros por categoría, autor, editorial, colección, disponibilidad y precio.
- Orden por relevancia, fecha, precio o título.
- Paginación de resultados.
- Página de categoría en `/categorias/{slug}`.
- Página de autor en `/autores/{slug}`.
- Ficha de libro en `/libros/{slug}`.
- Galería de imágenes cuando el producto tiene más de una imagen.
- Estados de disponibilidad: Disponible, Últimas unidades y Agotado.
- Productos relacionados por categoría, colección o autor.
- Consulta del libro por WhatsApp.
- Carrito temporal en `/carrito`.
- Agregar, acumular, actualizar y eliminar productos del carrito.
- Cálculo de subtotal por línea y subtotal general.
- Contador de unidades del carrito en el encabezado.
- Validación de stock, cantidades y productos activos.
- Mensajes globales de confirmación o error.
- Indicador visual durante las navegaciones.

### 3.2 Elementos visibles pero demostrativos o pendientes

- Los productos creados por los seeders son datos ficticios de demostración.
- Las portadas se dibujan automáticamente cuando no existe una imagen real.
- Las librerías de la portada todavía usan datos demostrativos del estado anterior al Prompt 7.
- El formulario de newsletter solo simula una suscripción en el navegador; no guarda ni envía datos.
- “Mi cuenta” todavía no abre una cuenta de usuario.
- “Favoritos” todavía no guarda productos.
- Varias secciones del menú muestran una página “en preparación”.
- `/librerias`, `/quienes-somos` y `/contacto` todavía son páginas transitorias.
- No existe panel administrativo operativo.

### 3.3 Funciones expresamente no implementadas

- Webpay u otra pasarela de pago.
- Creación de pedidos.
- Checkout definitivo.
- Cálculo o selección de despacho.
- Cobro y comprobantes.
- Persistencia del carrito por cuenta registrada.
- Inicio de sesión y registro de clientes.
- Favoritos reales.
- Envío real del newsletter.

## 4. Cómo abrir el sitio en un computador

### 4.1 Requisitos técnicos

El entorno necesita:

- PHP 8.3 o superior; el proyecto está preparado para PHP 8.4.
- Composer.
- Node.js y npm.
- MySQL para el entorno local configurado por defecto.
- Las extensiones habituales requeridas por Laravel.

El archivo `.env.example` propone una base de datos llamada `librerias_paulinas`, con usuario `root` y sin contraseña. Estos datos se deben adaptar al entorno real si MySQL utiliza otras credenciales.

### 4.2 Primera instalación

Desde una terminal abierta en la raíz del proyecto:

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
npm install
```

Antes de migrar, se debe crear en MySQL la base de datos configurada en `.env`. Luego:

```powershell
php artisan migrate --seed
```

Este comando crea las tablas y carga el catálogo de demostración.

### 4.3 Inicio habitual del entorno

Cuando el proyecto ya está instalado:

```powershell
composer run dev
```

El proceso de desarrollo mantiene activos Laravel y Vite. La dirección habitual, según `.env.example`, es:

```text
http://localhost:8000
```

La terminal debe permanecer abierta mientras se observa el sitio.

### 4.4 Alternativa con procesos separados

Si el comando combinado no se adapta al entorno, se pueden usar dos terminales:

Terminal 1:

```powershell
php artisan serve
```

Terminal 2:

```powershell
npm run dev
```

### 4.5 Carga o reconstrucción de datos demostrativos

Para ejecutar nuevamente los seeders sin borrar la base:

```powershell
php artisan db:seed
```

Para reconstruir completamente la base:

```powershell
php artisan migrate:fresh --seed
```

> Advertencia: `migrate:fresh` elimina todas las tablas y sus datos. Solo debe usarse en desarrollo cuando se acepta perder la información existente.

## 5. Estructura general de la pantalla

Todas las páginas públicas comparten una estructura persistente:

1. Encabezado institucional.
2. Navegación principal.
3. Contenido propio de la página.
4. Pie de página.
5. Capa de carga durante las navegaciones.
6. Mensajes de confirmación o error cuando una acción los produce.

### 5.1 Encabezado en escritorio

En pantallas grandes se observa:

- una franja superior con el lema institucional y teléfono;
- el logotipo, que regresa a la portada;
- un buscador central;
- accesos a cuenta, favoritos y carrito;
- una barra inferior con las principales categorías o secciones.

El icono del carrito es funcional. Si hay productos, muestra una insignia roja con la cantidad total de unidades, no solo la cantidad de títulos distintos.

Los accesos “Mi cuenta” y “Favoritos” son visuales y todavía no tienen flujo funcional.

### 5.2 Encabezado en teléfono o tablet

En pantallas pequeñas:

- el buscador se abre con el botón de lupa;
- el menú se abre con el botón de tres líneas;
- el carrito continúa disponible desde su icono en el encabezado;
- el panel lateral se puede cerrar con la X, tocando fuera o pulsando Escape;
- la página de fondo no se desplaza mientras el panel está abierto.

El menú lateral contiene accesos visuales a cuenta, favoritos y carrito en su parte inferior. Actualmente se recomienda usar el icono de carrito del encabezado, porque es el acceso conectado a `/carrito`.

### 5.3 Pie de página

El pie muestra:

- identidad y lema de Paulinas;
- enlaces agrupados por área;
- dirección y datos institucionales;
- accesos de contacto y redes configuradas;
- año actual calculado automáticamente.

Los datos institucionales no están repetidos manualmente en cada componente. Se obtienen desde `config/paulinas.php`.

### 5.4 Indicador de carga

Al cambiar de página mediante Inertia aparece una capa semitransparente con la marca Paulinas y el texto “Cargando”. Desaparece cuando finaliza la navegación.

Si el sistema operativo tiene activada la preferencia de movimiento reducido, las animaciones se reducen para mejorar la accesibilidad.

## 6. Portada

Ruta:

```text
/
```

### 6.1 Campaña principal

La parte superior presenta un banner editorial. El servidor puede entregar varias campañas, pero solo se muestra la que está marcada como activa.

El contenido actual es demostrativo y dirige a una sección relacionada del sitio.

### 6.2 Novedades

La portada muestra hasta ocho productos marcados como novedades. Cada tarjeta contiene:

- portada real o portada gráfica generada;
- categoría;
- título;
- autor o autores;
- precio en CLP;
- disponibilidad;
- enlace “Ver detalle”.

Pulsar la portada, el título o “Ver detalle” abre la ficha del producto.

### 6.3 Categorías

La sección “Encuentra lo que necesitas” muestra las categorías principales y el número de productos activos de cada una.

El conteo de una categoría padre incluye los productos de sus subcategorías. Por ejemplo, una categoría general puede sumar títulos de varias secciones hijas.

### 6.4 Destacados

La sección “Destacados” presenta una selección editorial de hasta cuatro productos en la portada, aunque el catálogo puede contener más productos marcados como destacados.

### 6.5 Recursos

La sección para catequistas y profesores presenta accesos a:

- planificaciones;
- material descargable;
- recursos pastorales;
- formación;
- programas de religión;
- recursos bíblicos.

Actualmente estos enlaces conducen a páginas transitorias “en preparación”.

### 6.6 Librerías

La portada muestra tres tarjetas demostrativas de librerías. Sus datos todavía no deben tomarse como información oficial. Esta sección será reemplazada por la base de datos y las páginas del Prompt 7.

### 6.7 Newsletter

El formulario permite escribir un correo y muestra una confirmación local. En la versión actual:

- el navegador valida que tenga formato de correo;
- no se llama a un endpoint;
- el correo no se guarda en la base de datos;
- no se envía una suscripción real.

## 7. Catálogo de libros

Ruta:

```text
/libros
```

El catálogo muestra únicamente productos activos y entrega 24 resultados por página.

### 7.1 Buscador

El buscador está disponible en el encabezado y dentro del menú móvil. Al buscar, se navega a una URL como:

```text
/libros?q=esperanza
```

La búsqueda consulta:

- título;
- descripción breve;
- descripción extensa;
- autor;
- colección;
- ISBN, incluso si se escribe sin guiones.

Si se escriben varias palabras, cada palabra debe aparecer en alguna parte de la ficha. El sistema considera hasta seis palabras relevantes y limita términos excesivamente largos para proteger la consulta.

Ejemplos:

```text
/libros?q=biblia
/libros?q=oración+familia
/libros?q=9789569900014
```

### 7.2 Filtros disponibles

El catálogo permite filtrar por:

- categoría;
- autor;
- editorial;
- colección;
- disponibilidad;
- precio mínimo y máximo.

En escritorio, los filtros aparecen en una columna lateral fija. En móvil, se abren desde el botón “Filtros”.

Cada opción muestra cuántos resultados produciría. Las opciones sin resultados se ocultan, salvo que sean la opción seleccionada.

Los filtros de edad recomendada, sacramento y temática aparecen como “Próximamente” porque todavía no existen esos datos en el esquema.

### 7.3 Disponibilidad en filtros

El filtro ofrece dos intenciones principales:

- Disponible: productos con al menos una unidad.
- Bajo pedido: productos sin stock.

En las tarjetas y fichas, el segundo estado se presenta actualmente como “Agotado”.

### 7.4 Filtro de precio

Se puede indicar un precio mínimo, máximo o ambos. Si el mínimo se escribe accidentalmente por encima del máximo, el servidor intercambia los extremos para producir un rango válido.

Los valores negativos o no numéricos se descartan.

### 7.5 Orden de resultados

Las opciones son:

- Relevancia.
- Más recientes.
- Precio: menor a mayor.
- Precio: mayor a menor.
- Título: A-Z.

Sin una búsqueda, “Relevancia” prioriza destacados, novedades, fecha y título. Con una búsqueda, prioriza ISBN y coincidencias más directas en el título.

### 7.6 Estado en la URL

La búsqueda, los filtros, el orden y la página actual quedan escritos en la URL. Ejemplo:

```text
/libros?categoria=biblias&autor=nombre-del-autor&orden=precio-asc&page=2
```

Esto permite:

- copiar y compartir una búsqueda;
- guardar una combinación en favoritos del navegador;
- usar Atrás y Adelante correctamente;
- recargar sin perder filtros.

### 7.7 Filtros activos

Sobre la cuadrícula se muestran chips con los filtros vigentes. Cada chip puede quitarse individualmente. Si hay varios, aparece la opción para limpiarlos todos.

Cambiar un filtro siempre devuelve a la primera página para evitar una página vacía por haber quedado en un número fuera de rango.

### 7.8 Resultados vacíos

Si no hay coincidencias:

- se muestra un mensaje claro;
- se puede limpiar la búsqueda;
- se proponen categorías activas con productos para continuar navegando.

### 7.9 Paginación

El paginador aparece únicamente cuando existe más de una página. Conserva todos los filtros en la URL.

## 8. Páginas de categoría

Ruta general:

```text
/categorias/{slug}
```

Ejemplo:

```text
/categorias/biblias
```

Estas páginas muestran:

- nombre de la categoría;
- descripción;
- categoría padre, cuando existe;
- accesos a subcategorías;
- productos activos de la categoría;
- productos activos de todas sus subcategorías;
- paginación.

Una categoría inactiva responde como una página inexistente y no queda expuesta públicamente.

## 9. Páginas de autor

Ruta general:

```text
/autores/{slug}
```

La página muestra:

- nombre del autor;
- biografía, si existe;
- productos activos asociados;
- paginación.

Se puede llegar a esta página pulsando el nombre del autor desde una ficha de libro.

## 10. Ficha de libro

Ruta general:

```text
/libros/{slug}
```

La URL utiliza un slug legible y no el identificador numérico.

### 10.1 Información presentada

Según los datos disponibles, la ficha muestra:

- portada;
- galería;
- título;
- autor o autores;
- descripción breve;
- precio en pesos chilenos;
- disponibilidad;
- editorial;
- ISBN;
- número de páginas;
- dimensiones;
- colección;
- categoría;
- descripción extensa.

Los campos editoriales opcionales se omiten cuando no tienen valor, en lugar de mostrar texto vacío.

### 10.2 Portada y galería

Si hay imágenes reales:

- la primera imagen es la portada inicial;
- la imagen principal se presenta completa;
- si hay más de una, aparecen miniaturas;
- pulsar una miniatura cambia la imagen principal;
- cada control indica cuál imagen está seleccionada.

Si no hay imágenes, el sitio genera una portada gráfica con título, autor, categoría y una paleta estable derivada del título. Esto evita imágenes rotas durante la etapa demostrativa.

### 10.3 Estados de stock

La lógica está centralizada y no muestra necesariamente la cantidad exacta:

- Disponible: stock superior al umbral de pocas unidades.
- Últimas unidades: stock entre 1 y 5 unidades, inclusive.
- Agotado: stock igual a 0.

El umbral de “Últimas unidades” se configura en `config/paulinas.php` y actualmente es 5.

### 10.4 Agregar al carrito

Para agregar un libro:

1. Abrir una ficha.
2. Pulsar “Agregar al carrito”.
3. Esperar el mensaje de confirmación.
4. Observar que el contador del encabezado aumenta.

Cada pulsación agrega una unidad. Si el producto ya existe en el carrito, la cantidad se acumula.

Si el libro está agotado, el botón aparece deshabilitado.

El servidor rechaza una acumulación que supere el stock, aunque la interfaz se manipule manualmente.

### 10.5 Consultar por WhatsApp

El botón “Consultar por WhatsApp” abre una pestaña externa con un mensaje como:

```text
Hola, quisiera consultar disponibilidad del libro "Título del libro", ISBN 978-....
```

El título y el ISBN se generan dinámicamente desde la ficha. Si el producto no tiene ISBN, la consulta se construye sin esa parte.

El número receptor se obtiene de `config/paulinas.php`; no está escrito dentro del componente visual.

En un computador, WhatsApp puede pedir abrir WhatsApp Web o la aplicación instalada. En un teléfono, normalmente abre la aplicación.

### 10.6 Productos relacionados

Al final de la ficha se muestran hasta ocho productos activos que compartan al menos uno de estos criterios:

- categoría;
- colección;
- autor.

El producto actual nunca se repite en esta sección.

## 11. Carrito

Ruta:

```text
/carrito
```

### 11.1 Naturaleza del carrito actual

El carrito se almacena en la sesión de Laravel. No pertenece todavía a una cuenta registrada.

La sesión guarda únicamente:

- identificador del producto;
- cantidad elegida.

El nombre, precio, portada y disponibilidad se vuelven a leer desde la base de datos. De esta forma, el carrito no conserva una copia obsoleta del precio.

En la configuración local de ejemplo, la sesión dura 120 minutos de inactividad. El comportamiento real puede cambiar si se modifica `SESSION_LIFETIME`.

### 11.2 Contenido de la página

Cada línea muestra:

- portada;
- título enlazado a la ficha;
- autor;
- precio unitario;
- cantidad;
- subtotal de la línea;
- botón Actualizar;
- botón Eliminar.

El resumen lateral muestra el subtotal de todas las líneas.

### 11.3 Cambiar una cantidad

1. Escribir un número entero mayor o igual a 1.
2. Pulsar “Actualizar”.
3. El servidor valida la cantidad.
4. El subtotal de la línea, el total y el contador se recalculan.

No se acepta:

- cero;
- números negativos;
- decimales;
- una cantidad superior al stock actual;
- un producto inactivo.

Para quitar una línea no se debe escribir cero; se debe usar “Eliminar”.

### 11.4 Eliminar un producto

Pulsar “Eliminar” retira completamente la línea, independientemente de su cantidad. El contador y los subtotales se actualizan después de la respuesta.

### 11.5 Carrito vacío

Cuando no hay productos, la página muestra un estado vacío y ofrece volver al catálogo.

### 11.6 Límite actual del proceso de compra

La página informa:

```text
El sistema de compra en línea estará disponible próximamente
```

No existe botón para pagar ni datos de despacho. Esto es intencional: el carrito funciona como selección temporal, pero todavía no genera un pedido.

## 12. Páginas transitorias

Las siguientes rutas existen para evitar enlaces rotos, pero muestran una pantalla “Esta sección está en preparación”:

- `/biblias`
- `/catequesis`
- `/ninos`
- `/espiritualidad`
- `/liturgia`
- `/novedades`
- `/recursos`
- `/recursos/profesores`
- `/recursos/catequistas`
- `/recursos/descargas`
- `/recursos/formacion`
- otros accesos demostrativos de recursos;
- `/librerias`
- `/quienes-somos`
- `/nuestra-mision`
- `/contacto`

El sistema registra estas rutas de forma explícita a partir de la configuración. Una URL realmente desconocida sigue devolviendo 404.

## 13. Datos demostrativos

### 13.1 Catálogo

Los seeders crean contenido ficticio para probar la aplicación:

- categorías y subcategorías;
- autores;
- editoriales;
- colecciones;
- productos;
- distintos precios y estados de stock;
- novedades y destacados;
- productos inactivos para probar su ocultamiento.

Cada producto ficticio incluye un aviso en su descripción. Los ISBN usan un rango de demostración y no deben atribuirse a publicaciones oficiales.

### 13.2 Imágenes

El seeder no crea rutas hacia fotografías inexistentes. La portada gráfica es el reemplazo deliberado hasta cargar archivos reales.

### 13.3 Librerías

Las tarjetas de librerías que se ven en la portada provienen todavía de `DemoContent` y contienen marcadores de posición. No se deben publicar como datos oficiales.

## 14. Comportamiento responsive

### 14.1 Teléfono

- Menú principal dentro de un panel lateral.
- Buscador desplegable.
- Filtros del catálogo dentro de un cajón.
- Tarjetas de producto en dos columnas cuando el ancho lo permite.
- Ficha y carrito apilados verticalmente.
- Botones con áreas táctiles amplias.

### 14.2 Tablet

- Mayor espacio entre contenidos.
- Cuadrículas de dos o tres columnas según la sección.
- Buscador todavía adaptable según el ancho.

### 14.3 Escritorio

- Buscador visible en el encabezado.
- Navegación principal horizontal.
- Filtros del catálogo en columna lateral.
- Cuadrículas de hasta cuatro productos.
- Ficha de libro dividida entre galería e información.
- Resumen del carrito en una columna lateral.

### 14.4 Cómo simular otros dispositivos

En Chrome o Edge:

1. Abrir las herramientas de desarrollo con `F12`.
2. Activar la barra de dispositivos con `Ctrl+Shift+M`.
3. Seleccionar un teléfono o indicar un ancho manual.
4. Probar el menú, buscador, filtros, ficha y carrito.

## 15. Accesibilidad incorporada

La interfaz incluye:

- enlace “Saltar al contenido” para teclado;
- etiquetas accesibles en campos y botones de icono;
- jerarquía de encabezados;
- foco visible;
- navegación por teclado;
- cierre con Escape en paneles;
- manejo del foco en menús y cajones;
- texto alternativo de imágenes;
- estados anunciados mediante `aria-live`;
- respeto por movimiento reducido;
- contraste basado en la paleta institucional;
- controles reales de enlace para destinos navegables.

Para una revisión formal de accesibilidad todavía sería recomendable realizar pruebas manuales con lector de pantalla y auditorías automáticas en una fase posterior.

## 16. Seguridad y validaciones actuales

- Las operaciones del carrito pasan por rutas POST, PATCH o DELETE protegidas por CSRF.
- La cantidad se valida en Laravel, no solo en el campo HTML.
- Un producto inactivo no se puede comprar ni abrir públicamente.
- Un slug de libro inexistente devuelve 404.
- Un slug de categoría inactiva devuelve 404.
- Los límites de stock se comprueban en el servidor.
- Las consultas usan Eloquent y parámetros enlazados.
- Los textos se renderizan con interpolación segura de Vue.
- La interfaz no recibe la cantidad exacta de stock en la ficha.
- El número de WhatsApp se toma desde configuración.

El sitio todavía no procesa pagos ni datos de tarjetas, por lo que no debe utilizarse como checkout hasta implementar y auditar esa fase.

## 17. Configuración funcional importante

### 17.1 Datos institucionales

Archivo:

```text
config/paulinas.php
```

Contiene:

- nombre legal y nombre breve;
- lema;
- dirección;
- teléfono;
- WhatsApp;
- correos;
- redes sociales;
- moneda y localización;
- umbral de pocas unidades;
- productos por página.

### 17.2 Navegación

Archivo:

```text
config/navigation.php
```

Define el menú principal y las columnas del pie. Los mismos datos se comparten con todos los componentes para evitar menús contradictorios.

### 17.3 Moneda

La moneda es CLP con formato `es-CL` y sin decimales visibles. Los precios se mantienen de forma exacta en el servidor y llegan al frontend como enteros.

### 17.4 Sesión

El carrito depende de la sesión Laravel. En `.env.example`:

```text
SESSION_DRIVER=database
SESSION_LIFETIME=120
```

La tabla `sessions` ya forma parte de las migraciones base.

## 18. Arquitectura explicada en términos simples

### 18.1 Laravel

Laravel controla:

- rutas;
- búsqueda;
- filtros;
- consultas a base de datos;
- reglas de stock;
- sesión del carrito;
- validaciones;
- generación de enlaces internos y WhatsApp;
- contenido enviado a cada página.

### 18.2 Inertia

Inertia conecta Laravel con Vue. No existe una API REST separada para estas páginas. Laravel decide los datos y Vue presenta la interfaz.

### 18.3 Vue

Vue controla:

- componentes visuales;
- paneles y menús;
- selección de imágenes de la galería;
- formularios Inertia;
- interacción responsive;
- presentación de filtros y resultados.

### 18.4 Tailwind CSS

Tailwind define la distribución, colores, tamaños, estados responsive y accesibilidad visual. La paleta institucional vive en `resources/css/app.css`.

### 18.5 Base de datos

La parte funcional del catálogo usa, entre otras, estas entidades:

- `products`;
- `product_images`;
- `categories`;
- `authors`;
- `publishers`;
- `collections`;
- tabla intermedia de autores y productos;
- `sessions` para el carrito local configurado.

### 18.6 Arquitectura del carrito

El carrito utiliza un contrato de almacenamiento. Hoy ese contrato se implementa mediante sesión. En una fase posterior se puede crear otra implementación respaldada por base de datos para usuarios autenticados sin reemplazar toda la lógica de controladores y páginas.

## 19. Mapa de rutas operativas

| Método | Ruta | Estado | Uso |
| --- | --- | --- | --- |
| GET | `/` | Operativa | Portada |
| GET | `/libros` | Operativa | Catálogo, búsqueda y filtros |
| GET | `/libros/{producto}` | Operativa | Ficha del libro |
| GET | `/categorias/{categoria}` | Operativa | Títulos de una categoría |
| GET | `/autores/{autor}` | Operativa | Títulos de un autor |
| GET | `/carrito` | Operativa | Ver el carrito |
| POST | `/carrito/items/{producto}` | Operativa | Agregar al carrito |
| PATCH | `/carrito/items/{producto}` | Operativa | Cambiar cantidad |
| DELETE | `/carrito/items/{producto}` | Operativa | Eliminar del carrito |
| GET | `/librerias` | Transitoria | Pendiente del Prompt 7 |
| GET | `/quienes-somos` | Transitoria | Pendiente del Prompt 7 |
| GET | `/contacto` | Transitoria | Pendiente del Prompt 7 |

Los valores entre llaves son slugs, por ejemplo `la-palabra-que-nos-reune`, no identificadores numéricos.

## 20. Recorrido recomendado para conocer el sitio

### Recorrido 1: portada y navegación

1. Abrir `/`.
2. Pulsar el logo y comprobar que conduce al inicio.
3. Revisar novedades, categorías, destacados, recursos y librerías.
4. Probar el menú principal.
5. Reducir la ventana y probar el menú móvil.

### Recorrido 2: búsqueda

1. Escribir una palabra del título en el buscador.
2. Confirmar que la URL cambia a `/libros?q=...`.
3. Abrir otra página y usar Atrás.
4. Probar un ISBN con y sin guiones.
5. Probar dos palabras y observar que ambas restringen los resultados.

### Recorrido 3: filtros

1. Abrir `/libros`.
2. Seleccionar una categoría.
3. Seleccionar un autor o editorial.
4. Aplicar un rango de precio.
5. Cambiar el orden.
6. Quitar un chip.
7. Limpiar todos los filtros.
8. Repetir el recorrido desde un teléfono simulado.

### Recorrido 4: ficha

1. Abrir un producto.
2. Revisar datos editoriales y disponibilidad.
3. Si tiene galería, cambiar la imagen principal.
4. Pulsar un autor.
5. Regresar a la ficha.
6. Revisar productos relacionados.
7. Probar “Consultar por WhatsApp” sin enviar el mensaje.

### Recorrido 5: carrito

1. Agregar un producto disponible.
2. Observar el contador del encabezado.
3. Agregar el mismo producto otra vez.
4. Abrir `/carrito`.
5. Cambiar la cantidad y pulsar Actualizar.
6. Confirmar subtotales.
7. Intentar ingresar cero y observar la validación.
8. Eliminar el producto.
9. Confirmar el estado vacío.

### Recorrido 6: límites actuales

1. Abrir `/quienes-somos`.
2. Abrir `/contacto`.
3. Abrir `/librerias`.
4. Confirmar que estas páginas indican que están en preparación.
5. Volver al catálogo mediante los enlaces ofrecidos.

## 21. Pruebas automatizadas y controles de calidad

Para ejecutar toda la suite Laravel:

```powershell
php artisan test --compact
```

Para los tests frontend actuales:

```powershell
node --test tests/Frontend/LoadingState.test.js
```

Para verificar estilo JavaScript/Vue:

```powershell
npm run lint
npm run format:check
```

Para formatear PHP:

```powershell
vendor/bin/pint --format agent
```

Para generar el frontend de producción:

```powershell
npm run build
```

La suite cubre catálogo, búsqueda, filtros, relaciones, páginas, navegación, carrito, dinero, carga y seeders.

## 22. Solución de problemas frecuentes

### 22.1 La página no abre

- Confirmar que el proceso `composer run dev` sigue activo.
- Revisar `APP_URL` en `.env`.
- Probar la URL indicada por `php artisan serve`.

### 22.2 Error de conexión a base de datos

- Confirmar que MySQL está iniciado.
- Confirmar que la base configurada existe.
- Revisar `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD`.
- Ejecutar `php artisan migrate --seed`.

### 22.3 No aparecen productos

- Ejecutar `php artisan db:seed`.
- Confirmar que los productos están activos.
- Limpiar filtros desde `/libros`.

### 22.4 Un cambio visual no aparece

Durante desarrollo:

```powershell
npm run dev
```

Para reconstruir recursos:

```powershell
npm run build
```

También puede ser necesario forzar una recarga del navegador con `Ctrl+F5`.

### 22.5 Error de archivo ausente en el manifiesto de Vite

Ejecutar:

```powershell
npm run build
```

### 22.6 El carrito se vació

El carrito actual depende de la sesión. Puede perderse si:

- vence la sesión;
- se borran cookies;
- se cambia de navegador;
- se cambia el dominio o puerto;
- se reinicia o limpia el almacenamiento de sesiones;
- se ejecuta `migrate:fresh`.

### 22.7 WhatsApp no abre

- Confirmar que el número de `config/paulinas.php` sea válido.
- Limpiar caché de configuración si se modificó:

```powershell
php artisan config:clear
```

- En escritorio, comprobar que el navegador permite abrir WhatsApp Web o la aplicación.

## 23. Criterios antes de publicar en producción

Antes de tratar esta aplicación como sitio definitivo se debe:

- reemplazar todos los productos demostrativos por datos oficiales;
- cargar portadas e imágenes reales con textos alternativos;
- completar y verificar las páginas institucionales;
- sustituir las librerías demostrativas por sucursales oficiales;
- implementar y proteger el formulario de contacto;
- decidir si el newsletter se integra con un proveedor;
- implementar cuentas y favoritos o retirar esos accesos temporales;
- definir checkout, pedidos, despacho y pagos;
- revisar políticas de privacidad y tratamiento de datos;
- verificar teléfonos, correos, direcciones y redes;
- ejecutar pruebas completas y revisión de accesibilidad;
- configurar base de datos, sesión, correo, caché y colas de producción;
- desactivar `APP_DEBUG` en producción;
- construir los assets con `npm run build`.

## 24. Glosario breve

- **Slug:** texto legible utilizado en una URL, por ejemplo `santiago-centro`.
- **Seeder:** proceso que carga datos iniciales o demostrativos.
- **Sesión:** almacenamiento temporal asociado al navegador actual.
- **Inertia:** capa que conecta respuestas Laravel con páginas Vue.
- **Responsive:** interfaz que se adapta a distintos tamaños de pantalla.
- **CLP:** peso chileno.
- **Subtotal de línea:** precio unitario multiplicado por cantidad.
- **Checkout:** flujo final donde se confirman datos, despacho y pago.
- **Producto activo:** producto autorizado para aparecer públicamente.
- **Stock:** cantidad disponible en inventario.

## 25. Conclusión

La aplicación actual ya permite demostrar de principio a fin el descubrimiento de libros: portada, búsqueda, filtros, ficha, disponibilidad, consulta por WhatsApp y carrito temporal. Su límite está claramente antes de la compra: todavía no crea pedidos ni recibe pagos.

Para observarla de forma representativa, el recorrido más importante es:

```text
Portada → búsqueda o categoría → ficha → WhatsApp o agregar al carrito → carrito
```

El siguiente bloque funcional previsto es el institucional: librerías físicas, quiénes somos y contacto. Hasta que ese bloque se complete, esas rutas deben considerarse transitorias.
