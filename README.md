# Listings Manager

Plugin de WordPress para generar automáticamente fichas de negocios (listings) utilizando Google Places API y OpenAI. Diseñado específicamente para integrarse con el tema **Listeo**.

## 🚀 Características

- **Integración con Google Places**: Obtiene datos reales de negocios (dirección, teléfono, web, horarios, fotos).
- **Contenido IA (OpenAI)**: Genera descripciones optimizadas para SEO y meta etiquetas automáticamente.
- **Gestión por Lotes**: Sistema de cola para procesar múltiples negocios sin saturar el servidor.
- **Importación CSV**: Carga masiva de Place IDs desde archivos CSV.
- **Panel de Administración**: Dashboard completo para monitorear el estado de las APIs y los listings creados.

## 📋 Requisitos

- WordPress 6.0 o superior.
- PHP 7.4 o superior.
- Tema **Listeo** instalado y activo (para los custom fields y taxonomías).
- Google Places API Key.
- OpenAI API Key.

## 🛠️ Instalación

1. Sube la carpeta `listings-manager` al directorio `/wp-content/plugins/`.
2. Activa el plugin desde el menú 'Plugins' en WordPress.
3. Ve a **Listings Manager > Configuración** e introduce tus API Keys.

## 📖 Uso

1. **Generación Individual**: Ve a 'Generar Fichas', introduce un Google Place ID y haz clic en 'Generar'.
2. **Importación Masiva**: Sube un CSV con una columna `place_id` para añadir múltiples negocios a la cola de procesamiento.

## 🛡️ Seguridad

El plugin sigue los estándares de seguridad de WordPress:
- Validación de nonces en todos los formularios.
- Sanitización de entradas y escapado de salidas.
- Verificación de capacidades de usuario (`manage_options`).
- Protección de directorio de logs.

## 📄 Licencia

GPL v2 or later.
