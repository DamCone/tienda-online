# TiendaDAW - Gestión Integral de Tienda Online

Aplicación web modular desarrollada en **PHP puro (sin frameworks)** para la gestión completa de un catálogo de productos, categorías y control de usuarios por roles. Proyecto práctico para el módulo de Desarrollo Web en Entorno Servidor (DWES) de 2º DAW Semipresencial - IES Juan de Garay.

---

## Descripción del Proyecto

El sistema implementa una arquitectura desacoplada basada en el patrón de capas:
* **Modelos (`src/models/`):** Representación de entidades del negocio (`Producto`, `Categoria`, `User`).
* **Servicios (`src/services/`):** Centralización de la lógica y consultas preparadas con PDO (`ProductosService`, `CategoriasService`, `UsersService`, `SessionService`).
* **Patrón Singleton:** Utilizado en `Config` y `SessionService` para asegurar una única instancia compartida durante el ciclo de vida de la petición.
* **Seguridad y Roles (RBAC):** Control de acceso según el tipo de usuario (Invitado, USER y ADMIN).
* **Gestión Multimedia:** Subida, validación y renombrado de imágenes físicas enlazadas al identificador UUID del producto.

---

## Tecnologías Utilizadas

* **Lenguaje:** PHP 8.2 (Vanilla / Puro, sin frameworks).
* **Servidor Web:** Apache con módulo `rewrite` activo.
* **Base de Datos:** PostgreSQL 12.
* **Gestor de BBDD Web:** Adminer.
* **Contenedores:** Docker & Docker Compose.
* **Frontend:** HTML5, CSS3, Bootstrap 5.3 y Bootstrap Icons.
* **Gestor de Dependencias:** Composer (PSR-4 Autoloading).
* **Librerías principales:**
  * `vlucas/phpdotenv` (^5.6): Carga de credenciales y variables de entorno (`.env`).
  * `ramsey/uuid` (^4.7): Generación de identificadores universales únicos (UUID v4).
  * `phpunit/phpunit` (^9.0 dev): Entorno para pruebas automatizadas.

---

## Requisitos Previos

Para ejecutar este proyecto en local solo necesitas:
* **Git** instalado.
* **Docker Desktop** (con Docker Compose v2+) activo en tu sistema.

*(No es obligatorio tener PHP, Composer o PostgreSQL instalados en tu sistema anfitrión; todo se ejecuta dentro de los contenedores).*

---

## Estructura del Proyecto

```text
tienda/
├── .env                      # Variables de entorno y credenciales
├── .gitignore                # Ficheros excluidos de Git (/vendor, /uploads)
├── composer.json             # Dependencias y configuración PSR-4
├── docker-compose.yml        # Orquestación de servicios (PHP, PostgreSQL, Adminer)
├── Dockerfile                # Imagen PHP 8.2 + Apache + extensiones pdo_pgsql, gd
├── README.md                 # Documentación técnica obligatoria
├── database/
│   └── init.sql              # Script DDL/DML de creación de tablas y datos semilla
├── public/                   # DocumentRoot accesible desde el navegador
│   ├── favicon.ico           # Icono de la aplicación
│   ├── header.php            # Barra de navegación común y comprobación de sesión
│   ├── footer.php            # Pie de página institucional
│   ├── index.php             # Catálogo principal con buscador y listado
│   ├── details.php           # Vista detallada de producto
│   ├── login.php             # Formulario de autenticación
│   ├── logout.php            # Cierre y destrucción de sesión
│   ├── create.php            # Formulario de alta de productos (ADMIN)
│   ├── update.php            # Formulario de edición de producto (ADMIN)
│   ├── delete.php            # Eliminación de registro e imagen física (ADMIN)
│   ├── update-image.php      # Formulario de subida de imagen (ADMIN)
│   ├── update_image_file.php # Procesamiento y validación física de archivos
│   └── uploads/              # Directorio de almacenamiento de imágenes subidas
├── src/                      # Código fuente privado fuera del DocumentRoot
│   ├── config/
│   │   └── Config.php        # Singleton de configuración y conexión PDO
│   ├── models/
│   │   ├── Categoria.php     # Modelo de datos de categoría
│   │   ├── Producto.php      # Modelo de producto con UUID
│   │   └── User.php          # Modelo de usuario y métodos de verificación de rol
│   └── services/
│       ├── CategoriasService.php # Consultas PDO para categorías
│       ├── ProductosService.php  # Operaciones CRUD completas para productos
│       ├── SessionService.php    # Singleton para gestión de sesión y roles
│       └── UsersService.php      # Autenticación y hash seguro con bcrypt
└── tests/                    # Espacio preparado para pruebas unitarias
```

---

## Instrucciones de Instalación y Ejecución

Sigue estos pasos desde una terminal para poner la aplicación en funcionamiento:

### 1. Clonar el repositorio
```bash
git clone https://github.com/DamCone/tienda-online.git
cd tienda-online
```

### 2. Configurar variables de entorno
Verifica que existe el archivo `.env` en la raíz del proyecto con la siguiente configuración:
```env
POSTGRES_DB=tienda
POSTGRES_USER=admin
POSTGRES_PASSWORD=adminPassword123
POSTGRES_HOST=postgres-db
POSTGRES_PORT=5432
APP_PORT=8080
```

### 3. Levantar los contenedores Docker
```bash
docker-compose up -d --build
```
> *Nota: Docker ejecutará automáticamente el script `database/init.sql` al iniciar el contenedor de base de datos por primera vez.*

### 4. Instalar dependencias con Composer
```bash
docker exec -it php_app composer install
```

### 5. Permisos para la subida de imágenes
Concede permisos de lectura y escritura para que el usuario web del contenedor (`www-data`) pueda guardar archivos en `public/uploads/`:
```bash
docker exec -it php_app chown -R www-data:www-data /var/www/html/public/uploads
docker exec -it php_app chmod -R 777 /var/www/html/public/uploads
```

---

## Uso Básico de la Aplicación

Una vez levantados los servicios, accede desde tu navegador web:

* **Aplicación Principal:** [http://localhost:8080](http://localhost:8080)
* **Gestor de BBDD (Adminer):** [http://localhost:8081](http://localhost:8081)  
  * *Sistema:* PostgreSQL  
  * *Servidor:* `postgres-db`  
  * *Usuario:* `admin`  
  * *Contraseña:* `adminPassword123`  
  * *Base de datos:* `tienda`

---

### Usuarios de Prueba y Matriz de Permisos

| Usuario | Contraseña | Rol asignado |
| :--- | :--- | :--- |
| *(Sin login)* | — | **Invitado** |
| `user` | `User1234` | **USER** |
| `admin` | `Admin1` | **ADMIN** (y USER) |

#### Matriz de Seguridad (RBAC):
| Operación | Invitado | USER | ADMIN |
| :--- | :---: | :---: | :---: |
| Ver catálogo y buscar por texto | Sí | Sí | Sí |
| Ver detalles de un producto (`details.php`) | ❌ No | Sí | Sí |
| Crear nuevo producto (`create.php`) | ❌ No | ❌ No | Sí |
| Modificar datos del producto (`update.php`) | ❌ No | ❌ No | Sí |
| Cambiar imagen (`update-image.php`) | ❌ No | ❌ No | Sí |
| Eliminar producto y su imagen (`delete.php`) | ❌ No | ❌ No | Sí |

---

## Fases del Desarrollo

El proyecto fue planificado y ejecutado según el siguiente cronograma:

1. **Fase 1 (02/11 - 03/11): Modelos y servicios base**
   * Configuración de la conexión mediante el patrón Singleton en `Config.php`.
   * Implementación de los modelos `Producto`, `Categoria` y `User`.
   * Desarrollo de los servicios base con sentencias preparadas PDO.
   * *Resultado:* Estructura funcional de `/src` y conexión correcta con PostgreSQL.

2. **Fase 2 (05/11 - 06/11): CRUD de productos (Parte 1)**
   * Implementación de los métodos de búsqueda y lectura (`findAllWithCategoryName`, `findById`, `save`).
   * Creación de la plantilla común (`header.php` y `footer.php`) con Bootstrap 5.
   * Vistas principales `index.php` (listado y buscador) y `create.php`.
   * *Resultado:* Listado y creación de productos disponibles desde la interfaz.

3. **Fase 3 (09/11 - 10/11): CRUD de productos (Parte 2)**
   * Métodos `update` y `deleteById` en `ProductosService`.
   * Implementación de `update.php`, `details.php` y `delete.php`.
   * Integración de alertas de retroalimentación (mensajes de éxito y avisos de error).
   * *Resultado:* CRUD de productos completo, validado y operativo.

4. **Fase 4 (12/11 - 13/11): Seguridad y gestión de sesiones**
   * Implementación de `SessionService` (Singleton) con control de inactividad (1 hora) y contador de visitas.
   * Vistas de autenticación `login.php` y `logout.php`.
   * Protección de rutas administrativas y comprobación estricta de roles.
   * *Resultado:* Sistema de autenticación, sesiones y permisos funcionando correctamente.

5. **Fase 5 (16/11): Multimedia, documentación y revisión final**
   * Creación del subsistema de imágenes (`update-image.php` y `update_image_file.php`).
   * Validación MIME y extensión (JPG, JPEG, PNG) y vinculación por UUID.
   * Eliminación física de archivos huérfanos al borrar productos.
   * Configuración de favicon y redacción de la documentación `README.md`.
   * *Resultado:* Proyecto completo, probado, funcional y documentado.

---

## Autores y Créditos

* **Desarrollador:** Damián Conejos Monzó
* **GitHub:** [https://github.com/DamCone](https://github.com/DamCone)
* **Curso:** 2º DAW Semipresencial - Desarrollo Web en Entorno Servidor
* **Centro Educativo:** IES Juan de Garay (Valencia)
* **Docente:** Eva María Gómez Abad

---

## Licencia

Este proyecto ha sido desarrollado exclusivamente con fines académicos y de **Uso Educativo**.