# Sistema de Gestión de Ventas (GDB)

Un sistema completo de gestión de ventas desarrollado en PHP con MySQL, que incluye gestión de clientes, productos, ventas, usuarios y reportes.

## 🚀 Características Principales

- **Gestión de Clientes**: CRUD completo de clientes con validación de datos
- **Gestión de Productos**: Control de inventario con alertas de stock bajo
- **Sistema de Ventas**: Registro de ventas con actualización automática de stock
- **Gestión de Usuarios**: Sistema de roles (admin/vendedor) con autenticación segura
- **Reportes**: Estadísticas de ventas, productos más vendidos e inventario
- **Dashboard**: Interfaz moderna con gráficos y métricas en tiempo real
- **Seguridad**: Protección CSRF, hash de contraseñas, validación de entrada

## 📋 Requisitos del Sistema

- **PHP**: 7.4 o superior
- **MySQL**: 5.7 o superior
- **Extensiones PHP**: PDO, PDO_MySQL, mbstring, json
- **Servidor Web**: Apache/Nginx (XAMPP recomendado para desarrollo)

## 🛠️ Instalación

### 1. Descargar el Proyecto
```bash
git clone [URL_DEL_REPOSITORIO]
cd gdb
```

### 2. Configurar el Servidor Web
- Colocar el proyecto en el directorio `htdocs` de XAMPP
- Asegurarse de que Apache y MySQL estén ejecutándose

### 3. Instalar el Sistema
- Abrir el navegador y visitar: `http://localhost/gdb/install.php`
- Seguir las instrucciones del instalador automático
- El sistema creará automáticamente:
  - Base de datos `gestion_ventas`
  - Todas las tablas necesarias
  - Usuario administrador por defecto

### 4. Credenciales por Defecto
- **Email**: admin@sistema.com
- **Contraseña**: admin123
- **⚠️ IMPORTANTE**: Cambiar la contraseña después del primer inicio de sesión

## 🗄️ Estructura de la Base de Datos

### Tablas Principales

#### `usuarios`
- `id_usuario` (PK)
- `nombre`, `correo`, `contraseña`
- `rol` (admin/vendedor)
- `fecha_creacion`

#### `clientes`
- `id_cliente` (PK)
- `nombre`, `apellido`, `correo`
- `telefono`, `direccion`
- `fecha_registro`

#### `productos`
- `id_producto` (PK)
- `nombre`, `descripcion`, `precio`
- `stock`, `fecha_creacion`

#### `ventas`
- `id_venta` (PK)
- `id_cliente` (FK), `id_usuario` (FK)
- `fecha_venta`, `total`

#### `detalle_ventas`
- `id_detalle` (PK)
- `id_venta` (FK), `id_producto` (FK)
- `cantidad`, `precio_unitario`, `subtotal`

## 📁 Estructura del Proyecto

```
gdb/
├── config/
│   ├── config.php          # Configuración principal
│   ├── database.php        # Clase de conexión a BD
│   └── installed.txt       # Marcador de instalación
├── models/
│   ├── Cliente.php         # Modelo de clientes
│   ├── Producto.php        # Modelo de productos
│   ├── Usuario.php         # Modelo de usuarios
│   └── Venta.php           # Modelo de ventas
├── includes/
│   └── functions.php       # Funciones utilitarias
├── install.php             # Instalador del sistema
├── login.php               # Página de autenticación
├── dashboard.php           # Dashboard principal
├── logout.php              # Cierre de sesión
├── index.php               # Página principal
└── README.md               # Este archivo
```

## 🔐 Seguridad

### Características de Seguridad Implementadas
- **Hash de Contraseñas**: Uso de `password_hash()` y `password_verify()`
- **Protección CSRF**: Tokens únicos para formularios
- **Preparación de Consultas**: Uso de PDO con prepared statements
- **Sanitización de Entrada**: Limpieza de datos de usuario
- **Validación de Sesiones**: Control de autenticación y permisos
- **Headers de Seguridad**: Protección XSS, clickjacking, etc.

### Roles y Permisos
- **Administrador**: Acceso completo al sistema
- **Vendedor**: Gestión de ventas, clientes y productos
- **Sistema de Permisos**: Control granular por funcionalidad

## 📊 Funcionalidades del Dashboard

### Métricas Principales
- Total de clientes registrados
- Total de productos en inventario
- Total de ventas realizadas
- Valor total del inventario

### Gráficos y Reportes
- Gráfico de ventas mensuales
- Alertas de stock bajo
- Ventas recientes
- Productos más vendidos

### Acciones Rápidas
- Nueva venta
- Nuevo cliente
- Nuevo producto
- Acceso a reportes

## 🚀 Uso del Sistema

### 1. Inicio de Sesión
- Acceder a `http://localhost/gdb`
- Usar las credenciales proporcionadas
- El sistema redirigirá automáticamente al dashboard

### 2. Navegación
- **Sidebar**: Menú principal con todas las secciones
- **Dashboard**: Vista general del negocio
- **Clientes**: Gestión de base de clientes
- **Productos**: Control de inventario
- **Ventas**: Registro y consulta de ventas
- **Reportes**: Análisis y estadísticas

### 3. Gestión de Ventas
- Seleccionar cliente existente o crear uno nuevo
- Agregar productos con cantidades
- El sistema calcula automáticamente totales
- Actualización automática del stock
- Generación de comprobantes

## 🔧 Personalización

### Configuración de Base de Datos
Editar `config/database.php`:
```php
private $host = 'localhost';
private $db_name = 'gestion_ventas';
private $username = 'root';
private $password = '';
```

### Configuración de la Aplicación
Editar `config/config.php`:
```php
define('APP_NAME', 'Sistema de Gestión de Ventas');
define('APP_URL', 'http://localhost/gdb');
define('SESSION_LIFETIME', 3600);
```

## 🐛 Solución de Problemas

### Error de Conexión a Base de Datos
- Verificar que MySQL esté ejecutándose
- Confirmar credenciales en `config/database.php`
- Verificar que la base de datos exista

### Error de Permisos
- Verificar permisos de escritura en el directorio
- Asegurar que PHP pueda crear archivos

### Problemas de Sesión
- Verificar configuración de cookies
- Limpiar caché del navegador
- Verificar configuración de PHP

## 📈 Mejoras Futuras

### Funcionalidades Planificadas
- [ ] Sistema de facturación electrónica
- [ ] Integración con pasarelas de pago
- [ ] App móvil para vendedores
- [ ] Sistema de notificaciones
- [ ] Backup automático de base de datos
- [ ] Múltiples monedas
- [ ] Sistema de descuentos y promociones

### Optimizaciones Técnicas
- [ ] Caché de consultas
- [ ] Compresión de respuestas
- [ ] Lazy loading de componentes
- [ ] API REST para integraciones

## 🤝 Contribución

### Cómo Contribuir
1. Fork del proyecto
2. Crear rama para nueva funcionalidad
3. Implementar cambios
4. Crear Pull Request
5. Esperar revisión y aprobación

### Estándares de Código
- Seguir PSR-12 para estilo de código
- Documentar funciones y clases
- Incluir pruebas unitarias
- Mantener compatibilidad con PHP 7.4+

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver archivo `LICENSE` para más detalles.

## 📞 Soporte

### Canales de Soporte
- **Issues**: Reportar bugs y solicitar funcionalidades
- **Documentación**: Este README y comentarios en el código
- **Comunidad**: Foros y grupos de usuarios

### Información de Contacto
- **Desarrollador**: [Tu Nombre]
- **Email**: [tu-email@ejemplo.com]
- **Sitio Web**: [tu-sitio.com]

---

**¡Gracias por usar el Sistema de Gestión de Ventas GDB!**

Si este proyecto te ha sido útil, considera darle una ⭐ en GitHub.
