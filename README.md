# Sistema de Bienes Públicos de Hidrofalcón

## Introducción
El sistema de bienes públicos de Hidrofalcón es un recurso administrativo diseñado para la gestión de inventario interno de una sede de hidrofalcón. Su arquitectura separa el cliente del servidor utilizando un patrón Modelo-Vista-Controlador (MVC), facilitando la escalabilidad mediante endpoints y una API REST[cite: 1].

## Guía de Instalación

### Requisitos del Sistema
* **Servidor Local:** WAMP (Windows) o XAMPP (Windows, macOS, Linux)[cite: 1, 2].
* **Hardware:** Mínimo 4 GB de RAM y 500 MB de espacio libre[cite: 1].
* **Navegador:** Chrome, Firefox, Brave o Edge[cite: 1, 2].
* **Herramientas:** Visual Studio Code (recomendado) y Git[cite: 1].

### Proceso de Montaje
1. **Instalación de Servidor:** Instalar WAMP/XAMPP y verificar que los servicios (Apache y MySQL) estén activos (ícono verde)[cite: 1, 2].
2. **Configuración de BD:**
   * Acceder a `phpMyAdmin` (usualmente `http://localhost/phpmyadmin`)[cite: 1].
   * Crear la base de datos `bienes_publicos` e importar el archivo SQL correspondiente (`bienes_publicos(4).sql`)[cite: 1].
3. **Despliegue:** Copiar la carpeta del proyecto en el directorio raíz del servidor (`www` para WAMP o `htdocs` para XAMPP) y acceder mediante `localhost/SISTEMA-HIDROFALCON`[cite: 2].

## Estructura Técnica

### Frontend
Enfocado en una experiencia minimalista mediante el uso de **modales** para evitar la fragmentación de interfaces[cite: 1].
* **Librerías clave:** `Micromodal.js` (estilos nativos aplicados) e `Izitoast.js`[cite: 1].
* **Enrutamiento:** Gestionado mediante `.htaccess` para URLs amigables[cite: 1].

### Backend
Estructurado para ser independiente del frontend, orientado a objetos con atributos `action` para procesar solicitudes GET/POST[cite: 1].

### Base de Datos (13 Tablas principales)
* **Usuarios:** Incluye sistema de bloqueo por intentos fallidos y niveles de acceso[cite: 1].
* **Activos:** Módulo principal que gestiona descripciones, marcas, estatus y valores decimales para ajustes anuales[cite: 1].
* **Otros:** Tablas complementarias como `cargo`, `gerencia`, `sede_adm`, `estatus_fisico/administrativo`, `bitacora`, `rango` y `configuracion`[cite: 1].

## Módulos y Roles

### Roles de Usuario
1. **Administrador:** Acceso total (Administración, Configuración, Activos)[cite: 2].
2. **Asociado:** Gestión de Configuración y Activos[cite: 2].
3. **Usuario:** Acceso limitado al módulo de Mural[cite: 2].

### Funcionalidades destacadas
* **Módulo de Activos:** Soporta relación individual y masiva (vía Excel), incorporación de custodios, desincorporación y reasignación (movilización)[cite: 2].
* **Módulo de Administración:** Monitoreo de usuarios (bloqueo/eliminación) y consulta de bitácora de movimientos[cite: 2].
* **Seguridad:** Registro con validaciones estrictas y preguntas de seguridad (respuestas cortas de 4 letras para memorización)[cite: 2].
