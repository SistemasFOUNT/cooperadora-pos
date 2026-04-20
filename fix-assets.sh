#!/bin/bash

echo "🎨 CONFIGURANDO ASSETS PARA PRODUCCIÓN"
echo "======================================"

cd /var/www/cooperadora

# Crear directorio build si no existe
sudo mkdir -p public/build

# Crear manifest.json temporal
sudo tee public/build/manifest.json > /dev/null << 'EOF'
{
  "resources/css/app.css": {
    "file": "assets/app.css",
    "isEntry": true,
    "src": "resources/css/app.css"
  },
  "resources/js/app.js": {
    "file": "assets/app.js",
    "isEntry": true,
    "src": "resources/js/app.js"
  }
}
EOF

# Crear directorio assets
sudo mkdir -p public/build/assets

# Crear archivo CSS temporal con AdminLTE via CDN
sudo tee public/build/assets/app.css > /dev/null << 'EOF'
/* Styles cargados via CDN en el layout */
@import url('https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css');
@import url('https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.0.0/css/all.min.css');
@import url('https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
EOF

# Crear archivo JS temporal con AdminLTE via CDN
sudo tee public/build/assets/app.js > /dev/null << 'EOF'
// JavaScript cargado via CDN
console.log('AdminLTE assets loaded via CDN');
EOF

# Corregir permisos
sudo chown -R www-data:www-data public/build

echo "✅ Assets temporales configurados"
echo ""
echo "🌐 Probar sitio: http://$(hostname -I | awk '{print $1}')"
echo ""
echo "Para assets completos, ejecutar:"
echo "1. sudo apt install nodejs npm"
echo "2. sudo -u www-data npm install"
echo "3. sudo -u www-data npm run build"
