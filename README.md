# Listings Manager

Plugin de WordPress para generar automáticamente fichas de negocios (listings) utilizando Google Places API y OpenAI. Diseñado específicamente para integrarse con el tema **Listeo**.

## 🚀 Características Ultra-Pro

- **Motor Multi-IA**: Elige entre **OpenAI (GPT)** y **Google Gemini Pro** para generar tu contenido.
- **SEO Local Avanzado**: Configura una ubicación base para que la IA optimice el contenido para tu ciudad específica.
- **Plantillas por Nicho**: Prompts especializados para Restaurantes, Hoteles, Salud y Retail.
- **Auto-Sync Engine**: Sincronización automática vía WP-Cron para mantener horarios y teléfonos siempre actualizados.
- **Scraper de Logos**: Intenta obtener el logo oficial directamente desde el sitio web del negocio.
- **Integración con Google Places**: Datos completos incluyendo fotos y geolocalización.
- **Gestión por Lotes**: Sistema de cola robusto para importaciones masivas vía CSV.

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
